<?php
require 'includes/functions.php';
$count = (int) $conn->query('SELECT COUNT(*) FROM users')->fetchColumn();
$message = '';
if ($count > 0) {
    http_response_code(403);
    exit('Setup is locked because a user already exists. Delete this file after installation.');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = (string) ($_POST['password'] ?? '');
    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 10) {
        $message = 'Use a valid name, email and a password with at least 10 characters.';
    } else {
        $stmt = $conn->prepare("INSERT INTO users(FullName,Username,Email,Password,Role,Status) VALUES(?,?,?,?, 'Admin','Active')");
        $stmt->execute([$name, strstr($email, '@', true), $email, password_hash($password, PASSWORD_DEFAULT)]);
        header('Location: login.php?setup=success');
        exit;
    }
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Create Gym Administrator</title><link rel="stylesheet" href="assets/vendor/css/core.css"><link rel="stylesheet" href="assets/vendor/css/theme-default.css"></head><body><main class="container py-5" style="max-width:600px"><div class="card"><div class="card-body p-5"><h2>Create first administrator</h2><p class="text-muted">This page locks automatically after the first account is created.</p><?php if($message): ?><div class="alert alert-danger"><?= escape($message) ?></div><?php endif; ?><form method="post"><div class="mb-3"><label class="form-label">Full name</label><input name="name" class="form-control" required></div><div class="mb-3"><label class="form-label">Email</label><input name="email" type="email" class="form-control" required></div><div class="mb-3"><label class="form-label">Password</label><input name="password" type="password" minlength="10" class="form-control" required></div><button class="btn btn-primary w-100">Create administrator</button></form></div></div></main></body></html>
