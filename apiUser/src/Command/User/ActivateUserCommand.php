<?php

namespace App\Command\User;

use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use App\Document\User;

class ActivateUserCommand extends Command
{
	protected static $defaultName = 'appquarium:user:activation';
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
		$this->addArgument('activation',InputArgument::REQUIRED, "should activate or not");

	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$user = $this->dm->getRepository(User::class)->findOneBy(['username'=>$input->getArgument('username')]);

		if(empty($user))
		{
			$output->writeln("User with username ".$input->getArgument('username')." was not found.");
			return 1;
		}

		$user->setActive($input->getArgument('activation') === "true");
		$this->dm->flush();

		$output->writeln("User ".$input->getArgument('username')." is now active : ".$input->getArgument('activation').".");
		var_dump($user->getActive());

		return 0;
	}
}