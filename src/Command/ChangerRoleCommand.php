<?php

namespace App\Command;

use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Donne un rôle à un utilisateur existant.
 *
 * Exemples :
 *   php bin/console app:changer-role admin@boutique.fr ROLE_ADMIN
 *   php bin/console app:changer-role super@boutique.fr ROLE_SUPER_ADMIN
 *   php bin/console app:changer-role client@boutique.fr ROLE_USER   (retire les droits admin)
 */
#[AsCommand(name: 'app:changer-role', description: 'Attribue ROLE_USER, ROLE_ADMIN ou ROLE_SUPER_ADMIN à un utilisateur')]
class ChangerRoleCommand extends Command
{
    // Rôles autorisés par l'application
    private const ROLES = ['ROLE_USER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN'];

    public function __construct(
        private readonly UtilisateurRepository $utilisateurRepository,
        private readonly EntityManagerInterface $em,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, "E-mail de l'utilisateur")
            ->addArgument('role', InputArgument::REQUIRED, 'ROLE_USER, ROLE_ADMIN ou ROLE_SUPER_ADMIN');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = $input->getArgument('email');
        $role = strtoupper($input->getArgument('role'));

        if (!\in_array($role, self::ROLES, true)) {
            $io->error('Rôle inconnu. Valeurs possibles : '.implode(', ', self::ROLES));

            return Command::FAILURE;
        }

        $utilisateur = $this->utilisateurRepository->findOneBy(['email' => $email]);
        if (!$utilisateur) {
            $io->error("Aucun utilisateur avec l'e-mail $email. Inscris-le d'abord sur le site.");

            return Command::FAILURE;
        }

        // ROLE_USER est implicite : on ne stocke que le rôle "supérieur"
        $utilisateur->setRoles('ROLE_USER' === $role ? [] : [$role]);
        $this->em->flush();

        $io->success("$email a maintenant le rôle $role.");

        return Command::SUCCESS;
    }
}
