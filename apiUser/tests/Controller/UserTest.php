<?php

namespace App\Tests\Controller;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use App\Document\User;
use App\Tests\Utils;

class UserTest extends ApiTestCase
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
			['/api/users'        , 'PUT'],
			['/api/users'        , 'PATCH'],
			['/api/users'        , 'DELETE'],
			['/api/users/1'      , 'PUT'],
			['/api/users/1'      , 'POST'],
			['/api/users/1'      , 'DELETE'],
			['/api/users/current', 'PUT'],
			['/api/users/current', 'POST'],
			['/api/users/current', 'DELETE'],
			['/api/users/current/password', 'GET'],
			['/api/users/current/password', 'PUT'],
			['/api/users/current/password', 'POST'],
			['/api/users/current/password', 'DELETE'],
			['/api/users/activate/token', 'GET'],
			['/api/users/activate/token', 'PUT'],
			['/api/users/activate/token', 'POST'],
			['/api/users/activate/token', 'DELETE'],

		];
	}

	public function testGetCollection(): void
	{
		$response = static::createClient()->request('GET', '/api/users');
		$this->assertResponseStatusCodeSame(200);
		$this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
		$this->assertJsonContains(['hydra:totalItems'=>7]);

		$responseContent = $response->toArray();

		$this->assertCount(7, $responseContent['hydra:member']);
		$this->assertMatchesResourceCollectionJsonSchema(User::class);
	}

	/**
	 * @dataProvider dataProvider_getFilteredCollection
	 */
	public function testGetFilteredCollection(array $filters, int $numberOfItems): void
	{
		$response = static::createClient()->request('GET', '/api/users', [
			'query'=>$filters
		]);
		$this->assertResponseStatusCodeSame(200);
		$this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
		$this->assertJsonContains(['hydra:totalItems'=>$numberOfItems]);

		$responseContent = $response->toArray();

		$this->assertCount($numberOfItems, $responseContent['hydra:member']);
		$this->assertMatchesResourceCollectionJsonSchema(User::class);
	}

	public function dataProvider_getFilteredCollection(): array
	{
		return [
			[['username'=>'userActive'], 2],
			[['username'=>'userActive2'], 1],
			[['username'=> 'user', 'email'=>'Admin'], 1],
			[['unknown'=>'userActive2'], 7],
			[['username'=>'unknown'], 0],
		];
	}

	/**
	 * @dataProvider dataProvider_getOrderedCollection
	 */
	public function testGetOrderedCollection(array $order, string $expectedUsername): void
	{
		$response = static::createClient()->request('GET', '/api/users', [
			'query'=>array_merge($order, ['itemsPerPage'=>1]),
		]);

		$this->assertResponseStatusCodeSame(200);
		$this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
		$this->assertJsonContains(['hydra:member'=>[['username'=>$expectedUsername]]]);

		$this->assertMatchesResourceCollectionJsonSchema(User::class);
	}

	public function dataProvider_getOrderedCollection(): array
	{
		return [
			[['order'=>['username'=>"ASC"]], "userActive"],
			[['order'=>['username'=>"DESC"]], "userPlantEditor"]
		];
	}

	public function testGetFoundItem(): void
	{
		$response = static::createClient()->request('GET', '/api/users/1');
		$this->assertResponseStatusCodeSame(200);
		$this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
		$this->assertJsonContains(['username'=>'userActive']);

		$this->assertMatchesResourceItemJsonSchema(User::class);
	}

	public function testGetNotFoundItem(): void
	{
		$response = static::createClient()->request('GET', '/api/users/unkown');
		$this->assertResponseStatusCodeSame(404);
	}

	public function testGetProfileWhenNotAuthentitified(): void
	{
		$response = static::createClient()->request('GET', '/api/users/current');
		$this->assertResponseStatusCodeSame(401);
	}

	/**
	 * @dataProvider dataProvider_getProfileWithInvalidToken
	 */
	public function testGetProfileWithInvalidToken(string $invalidToken): void
	{
		$response = static::createClient()->request('GET', '/api/users/current',[
			'headers'=>['Authorization'=>$invalidToken]
		]);
		$this->assertResponseStatusCodeSame(401);
	}

	public function dataProvider_getProfileWithInvalidToken(): array
	{
		$invalidTokenSignature = Utils::generateToken('1', '+1 day', 'USER_AUTH', [], 'invalid');
		$invalidTokenKind = Utils::generateToken('1', '+1 day', 'invalid');
		$invalidTokenUserNotFound = Utils::generateToken('unknown', '+1 day', 'USER_AUTH');
		$invalidTokenUserNotActive = Utils::generateToken('3', '+1 day', 'USER_AUTH');
		$invalidTokenExpired = Utils::generateToken('1', 'now', 'USER_AUTH');
		$validToken = Utils::generateToken('1', '+1 day', 'USER_AUTH');

		return [
			['Invalid'],
			[$validToken],
			['Bearer invalid'],
			['Bearer '.$invalidTokenSignature],
			['Bearer '.$invalidTokenKind],
			['Bearer '.$invalidTokenUserNotFound],
			['Bearer '.$invalidTokenUserNotActive],
			['Bearer '.$invalidTokenExpired],
			['Bearer:'.$validToken],
			['Something '.$validToken]
		];
	}

	public function testGetProfileWithValidToken(): void
	{
		$validToken = Utils::generateToken('1', '+1 day', 'USER_AUTH');

		$response = static::createClient()->request('GET', '/api/users/current',[
			'headers'=>['Authorization'=>'Bearer '.$validToken]
		]);

		$this->assertResponseStatusCodeSame(200);
		$this->assertEquals('userActive', $response->toArray()['username']);
		$this->assertMatchesResourceItemJsonSchema(User::class);
	}

	/**
	 * @dataProvider dataProvider_postInvalidUser
	 */
	public function testPostInvalidUser(array $invalidPayload, string $expectedResponse = null): void
	{
		$response = static::createClient()->request('POST', '/api/users',[
			'json'=>$invalidPayload
		]);

		$this->assertResponseStatusCodeSame(400);
		if(!empty($expectedResponse))
			$this->assertEquals($expectedResponse, $response->toArray(false)['hydra:description']);

	}

	public function dataProvider_postInvalidUser(): array
	{
		return [
			[['username'=>"newUser", 'email'=>'newUser@gmail.com', 'password'=>"newUser", 'repeatPassword'=>'newUser', 'unknown'=>'value'], 'Extra attributes are not allowed ("unknown" are unknown).'],
			[['username'=>"newUser", 'password'=>"newUser", 'repeatPassword'=>'newUser' ], "email: This value should not be blank."],
			[['username'=>"newUser", 'email'=>'newUser@gmail.com', 'password'=>"newUser", 'repeatPassword'=>'invalid' ], "repeatPassword: This value should be equal to password field."],
			[['username'=>"userActive", 'email'=>'newUser@gmail.com', 'password'=>"newUser", 'repeatPassword'=>'newUser' ], 'username: The value "userActive" already exist.'],
			[['username'=>"newUser", 'email'=>'userActive@gmail.com', 'password'=>"newUser", 'repeatPassword'=>'newUser' ], 'email: The value "userActive@gmail.com" already exist.'],
		];
	}

	public function testPostValidUser(): void
	{
		$response = static::createClient()->request('POST', '/api/users',[
			'json'=>[
				'username'=>'newUser',
				'email'=>'newUser@gmail.com',
				'password'=>'newUser',
				'repeatPassword'=>'newUser'
			]
		]);

		$this->assertResponseStatusCodeSame(201);
		$this->assertEquals('newUser', $response->toArray()['username']);
		$this->assertMatchesResourceItemJsonSchema(User::class);

		$unableToLogin = static::createClient()->request('POST', '/api/token',[
			'json'=>[
				'login'=>'newUser',
				'password'=>'newUser',
			]
		]);

		$this->assertEquals(400, $unableToLogin->getStatusCode());

		Utils::resetDb();
	}

	public function testPatchCurrentUserUnauthorized(): void
	{
		$response = static::createClient()->request('PATCH', '/api/users/current',[
			'headers'=>['Content-Type'=>'application/ld+json'],
			'json'=>[]
		]);

		$this->assertResponseStatusCodeSame(401);
	}

	/**
	 * @dataProvider dataProvider_patchInvalidCurrentUser
	 */
	public function testPatchInvalidCurrentUser(array $invalidPayload, string $token, string $expectedResponse): void
	{
		$response = static::createClient()->request('PATCH', '/api/users/current',[
			'headers'=>[
				'Authorization'=>'Bearer '.$token,
				'Content-Type'=>'application/ld+json'
			],
			'json'=>$invalidPayload
		]);

		$this->assertResponseStatusCodeSame(400);
		$this->assertEquals($expectedResponse, $response->toArray(false)['hydra:description']);

	}

	public function dataProvider_patchInvalidCurrentUser(): array
	{
		$validToken = Utils::generateToken('1', '+1 day', 'USER_AUTH');

		return [
			[
				['username'=>"userActive", 'plainPassword'=>"userActive", 'unknown'=>'value'],
				$validToken,
				"ERROR: This form should not contain extra fields.\n"
			],
			[
				['username'=>"userActive"],
				$validToken,
				"plainPassword: This value is invalid."
			],
			[
				['username'=>"userActive", 'email'=>'userActive@gmail.com', 'plainPassword'=>"invalid"],
				$validToken,
				"plainPassword: This value is invalid."
			],
			[
				['username'=>"userActive2", 'plainPassword'=>"userActive"],
				$validToken,
				'username:'."\n".'    ERROR: The value "userActive2" already exist.'."\n"
			],
			[
				['username'=>"patchedUser", 'email'=>'userActive2@gmail.com', 'plainPassword'=>"userActive"],
				$validToken,
				'email:'."\n".'    ERROR: The value "userActive2@gmail.com" already exist.'."\n"
			],
		];
	}

	public function testPatchCurrentUser(): void
	{
		$validToken = Utils::generateToken('1', '+1 day', 'USER_AUTH');

		$getUserBeforePatch = static::createClient()->request('GET', '/api/users/current',[
			'headers'=>[
				'Authorization'=>'Bearer '.$validToken,
			]
		]);

		$this->assertEquals('userActive', $getUserBeforePatch->toArray()['username']);

		$response = static::createClient()->request('PATCH', '/api/users/current',[
			'headers'=>[
				'Authorization'=>'Bearer '.$validToken,
				'Content-Type'=>'application/ld+json'
			],
			'json'=>[
				'username'=>'patchedUser',
				'plainPassword' => 'userActive'
			]
		]);
		$this->assertResponseStatusCodeSame(200);

		$getUserAfterPatch = static::createClient()->request('GET', '/api/users/current',[
			'headers'=>[
				'Authorization'=>'Bearer '.$validToken,
			]
		]);

		$this->assertEquals('patchedUser', $getUserAfterPatch->toArray()['username']);

		Utils::resetDb();
	}


	/**
	 * @dataProvider dataProvider_patchUserInvalid
	 */
	public function testPatchUserInvalid(array $payload, string $expectedResponse): void
	{
		$response = static::createClient()->request('PATCH', '/api/users/1',[
			'headers'=>['Content-Type'=>'application/ld+json'],
			'json'=>$payload
		]);

		$this->assertResponseStatusCodeSame(400);
		$this->assertEquals($expectedResponse, $response->toArray(false)['hydra:description']);
	}

	public function dataProvider_patchUserInvalid(): array
	{
		return [
			[
				['username'=>"userActive", 'unknown'=>'value'],
				'Extra attributes are not allowed ("unknown" are unknown).'
			],
			[
				['username'=>"userActive2", 'plainPassword'=>"userActive"],
				'username: The value "userActive2" already exist.'
			],
			[
				['username'=>"patchedUser", 'email'=>'userActive2@gmail.com', 'plainPassword'=>"userActive"],
				'email: The value "userActive2@gmail.com" already exist.'
			],
		];
	}

	public function testPatchUser(): void
	{
		$getUserBeforePatch = static::createClient()->request('GET', '/api/users/1');
		$this->assertEquals('userActive', $getUserBeforePatch->toArray()['username']);

		$response = static::createClient()->request('PATCH', '/api/users/1',[
			'headers'=>['Content-Type'=>'application/ld+json'],
			'json'=> ['username'=>"patchedUser"]
		]);

		$this->assertResponseStatusCodeSame(200);

		$getUserAfterPatch = static::createClient()->request('GET', '/api/users/1');

		$this->assertEquals('patchedUser', $getUserAfterPatch->toArray()['username']);

		Utils::resetDb();
	}

	/**
	 *@dataProvider  dataProvider_getProfileWithInvalidToken
	 */
	public function testPatchCurrentPasswordUnauthorized(string $token): void
	{
		$response = static::createClient()->request('PATCH', '/api/users/current',[
			'headers'=>[
				'Content-Type'=>'application/ld+json',
				'Authorization'=>$token
			],
			'json'=>[]
		]);

		$this->assertResponseStatusCodeSame(401);
	}

	/**
	 *@dataProvider  dataProvider_patchUserCurentPasswordInvalid
	 */
	public function testPatchPatchUserCurentPasswordInvalid(array $payload, string $token, string $expectedResponse): void
	{
		$response = static::createClient()->request('PATCH', '/api/users/current/password',[
			'headers'=>[
				'Content-Type'=>'application/ld+json',
				'Authorization'=>'Bearer '.$token
			],
			'json'=>$payload
		]);

		$this->assertResponseStatusCodeSame(400);
		$this->assertEquals($expectedResponse, $response->toArray(false)['hydra:description']);
	}

	public function dataProvider_patchUserCurentPasswordInvalid(): array
	{
		$validToken = Utils::generateToken('1', '+1 day', 'USER_AUTH');

		return [
			[
				['newPassword'=>"newPassword", 'repeatPassword'=>"newPassword" , 'plainPassword'=>"userActive", 'unknown'=>'value'],
				$validToken,
				"ERROR: This form should not contain extra fields.\n"
			],
			[
				['newPassword'=>"newPassword", 'repeatPassword'=>"newPassword"],
				$validToken,
				"plainPassword:\n    ERROR: This value should not be blank.\n"
			],
			[
				['newPassword'=>"newPassword", 'repeatPassword'=>"newPassword", 'plainPassword'=>'invalid'],
				$validToken,
				"password: This value is invalid."
			],
			[
				['newPassword'=>"newPassword", 'repeatPassword'=>"invalid", 'plainPassword'=>'userActive'],
				$validToken,
				"repeatPassword:\n    ERROR: This value should be equal to password field.\n"
			],
		];
	}

	public function testPatchCurentUserPassword(): void
	{
		$token = Utils::generateToken('1', '+1 day', 'USER_AUTH');

		$loginBeforePatch = static::createClient()->request('POST', '/api/token', [
			'json'=>['login'=>'userActive', 'password'=>'userActive']
		]);
		$this->assertEquals(201, $loginBeforePatch->getStatusCode());

		$response = static::createClient()->request('PATCH', '/api/users/current/password',[
			'headers'=>[
				'Content-Type'=>'application/ld+json',
				'Authorization'=>'Bearer '.$token
			],
			'json'=> [
				'newPassword'=>"newPassword",
				'repeatPassword'=>"newPassword",
				'plainPassword'=>'userActive'
			],
		]);

		$this->assertResponseStatusCodeSame(200);

		$loginBeforePatch = static::createClient()->request('POST', '/api/token', [
			'json'=>['login'=>'userActive', 'password'=>'userActive']
		]);
		$this->assertEquals(400, $loginBeforePatch->getStatusCode());


		$loginBeforePatch = static::createClient()->request('POST', '/api/token', [
			'json'=>['login'=>'userActive', 'password'=>'newPassword']
		]);
		$this->assertEquals(201, $loginBeforePatch->getStatusCode());

		Utils::resetDb();
	}

	/**
	 * @dataProvider dataProvider_activateUserInvalidToken
	 */
	public function testActivateUserInvalidToken(string $token, int $expectedStatusCode): void
	{
		$response = static::createClient()->request('PATCH', '/api/users/activate/'.$token);
		$this->assertEquals($expectedStatusCode, $response->getStatusCode());
	}

	public function dataProvider_activateUserInvalidToken(): array
	{
		$invalidTokenSignature = Utils::generateToken('1', '+1 day', 'USER_ACTIVATION', [], 'invalid');
		$invalidTokenKind = Utils::generateToken('1', '+1 day', 'invalid');
		$invalidTokenUserNotFound = Utils::generateToken('unknown', '+1 day', 'USER_ACTIVATION');
		$invalidTokenUserAlreadyActive = Utils::generateToken('1', '+1 day', 'USER_ACTIVATION');
		$invalidTokenExpired = Utils::generateToken('1', 'now', 'USER_ACTIVATION');

		return [
			['Invalid', 401],
			[$invalidTokenSignature, 401],
			[$invalidTokenKind, 404],
			[$invalidTokenExpired, 401],
			[$invalidTokenUserNotFound, 404],
			[$invalidTokenUserAlreadyActive, 404],
		];
	}

	public function testActivateUserToken(): void
	{
		$loginBeforePatch = static::createClient()->request('POST', '/api/token', [
			'json'=>['login'=>'userInactive', 'password'=>'userInactive']
		]);
		$this->assertEquals(400, $loginBeforePatch->getStatusCode());

		$token = Utils::generateToken('3', '+1 day', 'USER_ACTIVATION');

		$response = static::createClient()->request('PATCH', '/api/users/activate/'.$token);
		$this->assertEquals(204, $response->getStatusCode());

		$loginBeforePatch = static::createClient()->request('POST', '/api/token', [
			'json'=>['login'=>'userInactive', 'password'=>'userInactive']
		]);
		$this->assertEquals(201, $loginBeforePatch->getStatusCode());

		Utils::resetDb();
	}
}