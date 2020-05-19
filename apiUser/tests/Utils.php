<?php


namespace App\Tests;


use Firebase\JWT\JWT;

class Utils
{
	const TEST_DATABASE_PATH = __DIR__ . "/test_db";

	static public function resetDb(): void
	{
		$mongoUri = $_ENV['MONGODB_URL'].'/'.$_ENV['MONGODB_DB'];
		shell_exec("mongorestore --drop --uri $mongoUri ".self::TEST_DATABASE_PATH. ' 2>&1');
	}

	static public function generateToken(string $userId, string $tokenExpire, string $tokenKind, array $roles = [], string $signature = null): string
	{
		if(empty($signature))
			$signature = $_ENV['APP_SECRET'];

		$validToken = JWT::encode([
			'id' => '/api/users/'.$userId,
			'exp' => date_create($tokenExpire)->format('U'),
			"roles"=>array_merge($roles, ['USER_ROLE']),
			'kind'=>$tokenKind
		], $signature);

		return $validToken;
	}
}