<?php
// Railway health check: 200 once the database exists and has an admin account.
header('Content-Type: text/plain');
header('Cache-Control: no-store');
try {
	$db = '/var/www/html/storage/' . ($_SERVER['DB_NAME'] ?? 'faved') . '.db';
	if (!is_file($db)) {
		throw new RuntimeException('database missing');
	}
	$pdo = new PDO('sqlite:' . $db, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
	if ((int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn() < 1) {
		throw new RuntimeException('no admin account');
	}
	echo "ok\n";
} catch (Throwable $e) {
	http_response_code(503);
	echo "not ready\n";
}
