<?php
namespace Wallabag\Bundle\CliBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Wallabag\Schema;

/**
 * Application aware command
 *
 * Provide a silex application in CLI context.
 */
class CreateSchemaCommand extends Command
{

    protected function configure()
    {
        $this
            ->setName('db:create')
            ->setDescription('Create default schema')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $app = $this->getSilexApplication();
        $db = $app['db'];

        Schema::createTables($db);

        $output->writeln("<info>Schema created</info>");
    }
}

