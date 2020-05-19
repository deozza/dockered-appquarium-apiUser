<?php


namespace App\Command\Fixtures;

use App\Service\Fixtures\FixturesLoader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class LoadFixturesCommand extends Command
{
	protected static $defaultName = 'test:fixtures:load';
	private $fixturesLoader;

	public function __construct(FixturesLoader $fixturesLoader)
	{
		$this->fixturesLoader = $fixturesLoader;
		parent::__construct();
	}

	protected function configure()
	{
		$this->setDescription("Load fixtures data from yaml files into a test database.");
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$env = [];
		foreach(glob(__DIR__."/../../../tests/Fixtures/*.yaml") as $filename)
		{
			$content = file_get_contents($filename);
			if(strlen($content) == 0) continue;

			try
			{
				$decoded = Yaml::parse($content);
			}
			catch(\Exception $e)
			{
				$output->writeln($filename." is not a valid yaml file. \n".$e->getMessage());
			}
			$newEnv = $this->fixturesLoader->executeFixturesAndReturnEnv($decoded);
			$env = array_merge($env, $newEnv);
		}

		$this->fixturesLoader->storeEnvInFile($env);

		$mongoUri = $_ENV['MONGODB_URL'].'/'.$_ENV['MONGODB_DB'];
		shell_exec("mongodump --uri $mongoUri -o ".__DIR__."/../../../tests/test_db/");
		return 0;
	}
}