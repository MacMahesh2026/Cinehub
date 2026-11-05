<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Create an admin user or grant ROLE_ADMIN to an existing user',
)]
final class CreateAdminCommand extends Command
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'Email address of the admin user')
            ->addArgument('password', InputArgument::OPTIONAL, 'Password (prompted for when omitted)')
            ->addOption('name', null, InputOption::VALUE_REQUIRED, 'Display name, used only when creating a new user', 'Administrator');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $email = strtolower(trim((string) $input->getArgument('email')));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $io->error(sprintf('"%s" is not a valid email address.', $email));

            return Command::FAILURE;
        }

        $user = $this->users->findOneBy(['email' => $email]);
        $created = null === $user;

        $password = $input->getArgument('password');
        if (null === $password && $created) {
            $question = (new Question('Password: '))
                ->setHidden(true)
                ->setHiddenFallback(false);

            /** @var \Symfony\Component\Console\Helper\QuestionHelper $helper */
            $helper = $this->getHelper('question');
            $password = $helper->ask($input, $output, $question);
        }

        if (null !== $password) {
            $password = (string) $password;
            if (\strlen($password) < 8) {
                $io->error('The password must be at least 8 characters long.');

                return Command::FAILURE;
            }
        }

        if ($created) {
            $user = (new User())
                ->setName((string) $input->getOption('name'))
                ->setEmail($email);
        }

        if (null !== $password) {
            $user->setPassword($this->passwordHasher->hashPassword($user, $password));
        }

        // ROLE_USER is added automatically by User::getRoles(); store only the
        // explicit roles so the admin form does not receive an unknown choice.
        $roles = array_values(array_filter(
            $user->getRoles(),
            static fn (string $role): bool => 'ROLE_USER' !== $role,
        ));

        if (!\in_array('ROLE_ADMIN', $roles, true)) {
            $roles[] = 'ROLE_ADMIN';
        }

        $user->setRoles(array_values(array_unique($roles)));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success(sprintf(
            '%s admin "%s".',
            $created ? 'Created' : 'Promoted',
            $email,
        ));

        if (!$created && null === $password) {
            $io->note('The existing password was left unchanged.');
        }

        return Command::SUCCESS;
    }
}
