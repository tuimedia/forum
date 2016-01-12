<?php
namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class GenerateTokenCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('app:jwt')
            ->setDescription('Create a JWT for development use')
            ->addArgument('username', InputArgument::OPTIONAL, 'Username', null)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $jwt = $this->getContainer()->get('jwt_coder');

        $username = $input->getArgument('username');
        if (!$username) {
            $username = $io->ask('Username');
        }

        $io->text('Token: ' . $jwt->encode([
            'username' => $username,
        ]));
        $io->success('JWT created');
    }
}
