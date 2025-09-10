<?php
// Handle Contact form submission: validate, insert into DB, and return JSON
header('X-Content-Type-Options: nosniff');

$respondJson = function(array $payload, int $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload);
    exit;
};

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $respondJson(['ok' => false, 'error' => 'Method not allowed'], 405);
    }

    // Basic rate limit via sleep on repeated rapid hits could be added; kept simple

    // Honeypot field (should be empty)
    $honeypot = isset($_POST['website']) ? trim((string)$_POST['website']) : '';
    if ($honeypot !== '') {
        // Pretend success to bots
        $respondJson(['ok' => true, 'message' => 'Thanks']);
    }

    // Collect and sanitize inputs
    $name = isset($_POST['name']) ? trim((string)$_POST['name']) : '';
    $email = isset($_POST['email']) ? trim((string)$_POST['email']) : '';
    $phone = isset($_POST['phone']) ? trim((string)$_POST['phone']) : '';
    $message = isset($_POST['message']) ? trim((string)$_POST['message']) : '';

    // Validate
    if ($name === '' || $email === '' || $phone === '' || $message === '') {
        $respondJson(['ok' => false, 'error' => 'All fields are required.'], 422);
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $respondJson(['ok' => false, 'error' => 'Invalid email address.'], 422);
    }
    if (strlen($name) > 150) { $respondJson(['ok' => false, 'error' => 'Name too long.'], 422); }
    if (strlen($email) > 190) { $respondJson(['ok' => false, 'error' => 'Email too long.'], 422); }
    if (strlen($phone) > 40) { $respondJson(['ok' => false, 'error' => 'Phone too long.'], 422); }

    // Capture meta
    $ip = $_SERVER['REMOTE_ADDR'] ?? null;
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;

    // Persist to DB
    require __DIR__ . '/config.php';
    $pdo = get_pdo();
    ensure_contacts_table($pdo);

    $stmt = $pdo->prepare('INSERT INTO contacts (name, email, phone, message, ip, user_agent) VALUES (?,?,?,?,?,?)');
    $stmt->execute([$name, $email, $phone, $message, $ip, $userAgent]);

    $respondJson(['ok' => true, 'message' => 'Saved']);
} catch (Throwable $e) {
    // Log error server-side if possible; avoid leaking details
    $respondJson(['ok' => false, 'error' => 'Server error. Please try again later.'], 500);
}
