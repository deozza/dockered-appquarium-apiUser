<?php

namespace App\Service\User;

use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

use App\Document\User;

class CreateUser
{
	private $dm;
	private $validator;
	private $userPasswordEncoder;

	public function __construct(DocumentManager $dm, ValidatorInterface $validator, UserPasswordEncoderInterface $userPasswordEncoder)
	{
		$this->dm = $dm;
		$this->validator = $validator;
		$this->userPasswordEncoder = $userPasswordEncoder;
	}

	public function create(string $username, string $email, ?string $password)
	{
		$user = new User();
		$user->setUsername($username);
		$user->setEmail($email);

		if(empty($password))
		{
			$password = sha1(random_bytes(10));
		}

		$isValid = $this->validator->validate($user);

		if(count($isValid)>0)
		{
			return $isValid;
		}

		$user->setNewPassword($password);
		$user->setPassword($this->userPasswordEncoder->encodePassword($user, $user->getNewPassword()));
        $user->setActive(true);
		$user->eraseCredentials();
		$this->dm->persist($user);
		$this->dm->flush();

		return $password;
	}
}