<?php

namespace App\Command;

use App\Entity\AccessToken;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'clean-expired-token',
    description: 'Add a short description for your command',
    aliases:['app:clean-tokens']
)]
class CleanExpiredTokenCommand extends Command
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        parent::__construct();
    }

    protected function configure() : void
    {
        $this->setName('app:clean-expired-tokens')
            ->setDescription('Deletes expired tokens from the database');
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $expiredTokens = $this->entityManager->getRepository(AccessToken::class)->findExpiredTokens();
        
        foreach ($expiredTokens as $token) {
            $this->entityManager->remove($token);
        }

        $this->entityManager->flush();

        $output->writeln('Expired tokens have been deleted.');

        return Command::SUCCESS;
    }
}
