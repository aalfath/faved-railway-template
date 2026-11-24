<?php
// First-boot bootstrap for Faved on Railway.
// Faved has no login until a user exists, and its setup API is open, so whoever opens a
// fresh deploy first could take it over. This script creates the database (with upstream's
// welcome content) and the admin account before Apache starts. It never changes an
// existing account. Exit code 0 = ready, anything else stops the container.
declare(strict_types=1);

const ROOT_DIR = '/var/www/html';
require ROOT_DIR . '/vendor/autoload.php';
require_once ROOT_DIR . '/init.php';

use Framework\ServiceContainer;
use Models\Repository;
use function Framework\validatePassword;
use function Framework\validateUsername;
use function Utils\createWelcomeContent;

function out(string $msg): void { fwrite(STDOUT, "[faved-railway] $msg\n"); }
function fail(string $msg): never { fwrite(STDERR, "[faved-railway] ERROR: $msg\n"); exit(1); }

try {
	$db_path = Config::getDBPath();
	$pdo = new PDO("sqlite:{$db_path}");
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$repository = new Repository($pdo);

	if (!$repository->checkDatabaseExists()) {
		out('creating database ' . $db_path);
		if (!$repository->setupDatabase()) {
			fail('database setup failed');
		}
		createWelcomeContent();
	}
	// Same migrations upstream runs on each request (users table, tag index).
	$repository->migrate();

	if ($repository->userTableNotEmpty()) {
		out('admin account exists; leaving it unchanged');
		exit(0);
	}

	$username = trim((string)getenv('FAVED_ADMIN_USERNAME'));
	$password = (string)getenv('FAVED_ADMIN_PASSWORD');
	if ($username === '') {
		$username = 'admin';
	}
	if ($password === '') {
		fail('FAVED_ADMIN_PASSWORD is empty. Set it (the template generates one) and redeploy; refusing to start without a login.');
	}
	validateUsername($username);
	validatePassword($password);

	$id = $repository->createUser($username, password_hash($password, Config::getPasswordAlgo()));
	if (!$id || !$repository->userTableNotEmpty()) {
		fail('could not create the admin account');
	}
	out("created admin account '{$username}' (password from FAVED_ADMIN_PASSWORD)");
	exit(0);
} catch (Throwable $e) {
	fail(get_class($e) . ': ' . $e->getMessage());
}
