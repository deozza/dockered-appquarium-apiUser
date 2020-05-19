<?php

namespace App\Command\User;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use App\Service\User\CreateUser;

class CreateUserCommand extends Command
{
	protected static $defaultName = 'appquarium:user:create';
	private $createUser;


	public function __construct(CreateUser $createUser)
	{
		$this->createUser = $createUser;

		parent::__construct();
	}

	protected function configure()
	{
		$this->setDescription("Create a new user");
		$this->addArgument('username',InputArgument::REQUIRED, "user username");
		$this->addArgument('email',InputArgument::REQUIRED, "user email");
		$this->addArgument('password',InputArgument::OPTIONAL, "user password");
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$password = $this->createUser->create($input->getArgument('username'), $input->getArgument('email'), $input->getArgument('password'));

		if(is_object($password))
		{
			$output->writeln("An error occured when creating the user : ".$input->getArgument('username'));
			$output->writeln((string) $password);
			return 1;
		}

		$output->writeln($input->getArgument('username')." has been created. His password is : ".$password);
		return 0;
	}
}