<?php


namespace App\Tests\Controller;


use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;

class AuthentificatorTest extends ApiTestCase
{
	/**
	 * @dataProvider dataProvider_invalidMethod
	 */
	public function testInvalidMethod(string $url, string $method): void
	{
		$response = static::createClient()->request($method, $url);
		$this->assertResponseStatusCodeSame(405);
	}

	public function dataProvider_invalidMethod(): array
	{
		return [
			['/api/token', 'PUT'],
			['/api/token', 'PATCH'],
			['/api/token', 'DELETE'],
			['/api/token', 'GET'],
		];
	}

	/**
	 * @dataProvider dataProvider_postTokenInvalid
	 */
	public function testPostTokenInvalid(array $payload): void
	{
		$response = static::createClient()->request('POST', '/api/token', [
			'json'=>$payload
		]);
		$this->assertResponseStatusCodeSame(400);
	}

	public function dataProvider_postTokenInvalid(): array
	{
		return [
			[['login'=>'userActive', 'password'=>'userActive', 'extra'=>'field']],
			[['login'=>'userActive']],
			[['login'=>'userActive', 'password'=>'invalid']],
			[['login'=>'invalid', 'password'=>'userActive']],
			[['login'=>'userInactive', 'password'=>'userInactive']],
		];
	}

	public function testPostTokenIValid(): void
	{
		$response = static::createClient()->request('POST', '/api/token', [
			'json'=>[
				'login'=>'userActive',
				'password'=>'userActive'
			]
		]);
		$this->assertResponseStatusCodeSame(201);
	}
}