<?php

use Doctrine\ODM\MongoDB\DocumentManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

use App\Tests\Utils;
use App\Service\User\CreateUser;

class CreateUserTest extends TestCase
{
	public function testCreate_failOnAlreadyExistingLogin()
	{
		$mockedDM = $this->getMockedDocumentManager();
		$mockedValidator = $this->getMockedValidatorInterface();
		$mockedUserPasswordEncoder = $this->getMockedUserPasswordEncoderInterface();

		$createUserService = new CreateUser($mockedDM, $mockedValidator, $mockedUserPasswordEncoder);
		$providedUsername = 'userActive';
		$providedEmail = 'newUserEmail@email.com';
		$providedPassword = 'password';

		$mockedValidator->expects($this->once())->method('validate')->willReturn(['something1', 'something2']);
		$mockedDM->expects($this->never())->method('persist');
		$mockedDM->expects($this->never())->method('flush');

		$result = $createUserService->create($providedUsername, $providedEmail, $providedPassword);

		$this->assertEquals(['something1', 'something2'], $result);
	}

	public function testCreate_failOnAlreadyExistingEmail()
	{
		$mockedDM = $this->getMockedDocumentManager();
		$mockedValidator = $this->getMockedValidatorInterface();
		$mockedUserPasswordEncoder = $this->getMockedUserPasswordEncoderInterface();

		$createUserService = new CreateUser($mockedDM, $mockedValidator, $mockedUserPasswordEncoder);
		$providedUsername = 'newUsername';
		$providedEmail = 'userActive@email.com';
		$providedPassword = 'password';

		$mockedValidator->expects($this->once())->method('validate')->willReturn(['something1', 'something2']);
		$mockedDM->expects($this->never())->method('persist');
		$mockedDM->expects($this->never())->method('flush');

		$result = $createUserService->create($providedUsername, $providedEmail, $providedPassword);

		$this->assertEquals(['something1', 'something2'], $result);

	}

	public function testCreate_withoutPassword()
	{
		$mockedDM = $this->getMockedDocumentManager();
		$mockedValidator = $this->getMockedValidatorInterface();
		$mockedUserPasswordEncoder = $this->getMockedUserPasswordEncoderInterface();

		$createUserService = new CreateUser($mockedDM, $mockedValidator, $mockedUserPasswordEncoder);
		$providedUsername = 'newUser';
		$providedEmail = 'newUser@email.com';

		$mockedValidator->expects($this->once())->method('validate')->willReturn([]);
		$mockedUserPasswordEncoder->expects($this->once())->method('encodePassword')->willReturn('encodedGeneratedPassword');
		$mockedDM->expects($this->once())->method('persist');
		$mockedDM->expects($this->once())->method('flush');

		$result = $createUserService->create($providedUsername, $providedEmail, $providedPassword = null);

		$this->assertIsString($result);
		Utils::resetDb();
	}

	public function testCreate_withPassword()
	{
		$mockedDM = $this->getMockedDocumentManager();
		$mockedValidator = $this->getMockedValidatorInterface();
		$mockedUserPasswordEncoder = $this->getMockedUserPasswordEncoderInterface();

		$createUserService = new CreateUser($mockedDM, $mockedValidator, $mockedUserPasswordEncoder);
		$providedUsername = 'newUser';
		$providedEmail = 'newUser@email.com';
		$providedPassword = 'password';

		$mockedValidator->expects($this->once())->method('validate')->willReturn([]);
		$mockedUserPasswordEncoder->expects($this->once())->method('encodePassword')->willReturn('encodedGeneratedPassword');
		$mockedDM->expects($this->once())->method('persist');
		$mockedDM->expects($this->once())->method('flush');

		$result = $createUserService->create($providedUsername, $providedEmail, $providedPassword);

		$this->assertIsString($result);
		Utils::resetDb();
	}

	private function getMockedDocumentManager()
	{
		$mockedDocumentManager = $this->createMock(DocumentManager::class);

		return $mockedDocumentManager;
	}

	private function getMockedValidatorInterface()
	{
		$mockedValidatorInterface = $this->createMock(ValidatorInterface::class);
		return $mockedValidatorInterface;
	}

	private function getMockedUserPasswordEncoderInterface()
	{
		$mockedUserPasswordEncoderInterface = $this->createMock(UserPasswordEncoderInterface::class);
		return $mockedUserPasswordEncoderInterface;
	}
}