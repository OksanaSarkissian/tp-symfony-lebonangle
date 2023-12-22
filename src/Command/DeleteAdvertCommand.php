<?php
// src/Command/DeleteAdvertCommand.php
namespace App\Command;

use App\Entity\Advert;
use App\Repository\AdvertRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DeleteAdvertCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:delete-advert';

    protected function configure(): void
    {
        $this
        // If you don't like using the $defaultDescription static property,
        // you can also define the short description using this method:
        // ->setDescription('...')

        // the command help shown when running the command with the "--help" option
        ->setHelp('This command allows you to create a user...')
        ->addArgument("days", InputArgument::REQUIRED, "nombre de jours d'ancienneté de l'annonce");
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $advertRepository = static::getContainer()->get('doctrine')->getRepository(Advert::class);

        $advertRepository->findAll(['created_at' => "2023-11-28 15:10:10"]);
        
        $output->writeln($input->getArgument('days'));
        return Command::SUCCESS;

        // or return this if some error happened during the execution
        // (it's equivalent to returning int(1))
        // return Command::FAILURE;

        // or return this to indicate incorrect command usage; e.g. invalid options
        // or missing arguments (it's equivalent to returning int(2))
        // return Command::INVALID
    }
}