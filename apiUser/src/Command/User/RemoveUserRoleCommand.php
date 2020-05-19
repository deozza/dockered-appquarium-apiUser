<?php

namespace App\Command\User;

use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use App\Document\User;

class RemoveUserRoleCommand extends Command
{
	protected static $defaultName = 'appquarium:user:role:remove';
	private $dm;

	public function __construct(DocumentManager $dm)
	{
		$this->dm = $dm;
		parent::__construct();
	}

	protected function configure()
	{
		$this->setDescription("Remove a role to a user");
		$this->addArgument('username',InputArgument::REQUIRED, "username of the user to manage");
		$this->addArgument('role',InputArgument::REQUIRED, "user role to remove");

	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$user = $this->dm->getRepository(User::class)->findOneBy(['username'=>$input->getArgument('username')]);

		if(empty($user))
		{
			$output->writeln("User with username ".$input->getArgument('username')." was not found.");
			return 1;
		}

		if($input->getArgument('role') === 'ROLE_USER')
		{
			$output->writeln("You can not remove the role ".$input->getArgument('role')." from a user.");
			return 1;
		}

		$roles = $user->getRoles();

		$roleToRemove = array_search($input->getArgument('role'), $roles);
		unset($roles[$roleToRemove]);


		$user->setRoles($roles);
		$this->dm->flush();

		$output->writeln("User ".$input->getArgument('username')." has not the role ".$input->getArgument('role')." anymore.");
		return 0;
	}
}