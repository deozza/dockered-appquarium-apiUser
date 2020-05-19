<?php

namespace App\Command\User;

use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use App\Document\User;

class AddUserRoleCommand extends Command
{
	protected static $defaultName = 'appquarium:user:role:add';
	private $dm;

	public function __construct(DocumentManager $dm)
	{
		$this->dm = $dm;
		parent::__construct();
	}

	protected function configure()
	{
		$this->setDescription("Add a role to a user");
		$this->addArgument('username',InputArgument::REQUIRED, "username of the user to manage");
		$this->addArgument('role',InputArgument::REQUIRED, "user role to add");

	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$user = $this->dm->getRepository(User::class)->findOneBy(['username'=>$input->getArgument('username')]);

		if(empty($user))
		{
			$output->writeln("User with username ".$input->getArgument('username')." was not found.");
			return 1;
		}

		if(!in_array($input->getArgument('role'), $user->loadRoles()))
		{
			$output->writeln("Tried to add invalid role ".$input->getArgument('role')." to user ".$input->getArgument('username').".");
			return 1;
		}

		$user->setRoles(array_merge($user->getRoles(),[$input->getArgument('role')]));
		$this->dm->flush();

		$output->writeln("User ".$input->getArgument('username')." has now the role ".$input->getArgument('role').".");

		return 0;
	}
}