<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class ProfileTest extends WebTestCase
{
    private const PASSWORD = 'CurrentPass123';

    public function testProfileRequiresAuthentication(): void
    {
        $client = static::createClient();
        $client->request('GET', '/profile');

        self::assertResponseRedirects('/login');
    }

    public function testProfileShowsTheAuthenticatedUser(): void
    {
        $client = static::createClient();
        $this->prepareDatabase();
        $user = $this->persistUser('viewer@example.com', 'Viewer');

        $this->authenticate($client, $user);
        $client->request('GET', '/profile');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'My Profile');
        self::assertSelectorTextContains('.profile-name', 'Viewer');
        self::assertSelectorTextContains('.profile-email', 'viewer@example.com');
    }

    public function testUserCanUpdateNameAndPassword(): void
    {
        $client = static::createClient();
        $this->prepareDatabase();
        $user = $this->persistUser('editor@example.com', 'Editor');

        $this->authenticate($client, $user);
        $client->request('GET', '/profile');

        $client->submitForm('Save changes', [
            'profile[name]' => 'Updated Name',
            'profile[plainPassword][first]' => 'NewPassword123',
            'profile[plainPassword][second]' => 'NewPassword123',
        ]);

        self::assertResponseRedirects('/profile');

        $client->followRedirect();
        self::assertSelectorTextContains('.alert-success', 'updated');

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $entityManager->clear();
        $reloaded = $entityManager->find(User::class, $user->getId());

        self::assertInstanceOf(User::class, $reloaded);
        self::assertSame('Updated Name', $reloaded->getName());

        /** @var UserPasswordHasherInterface $passwordHasher */
        $passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        self::assertTrue($passwordHasher->isPasswordValid($reloaded, 'NewPassword123'));
    }

    public function testNameCannotBeBlank(): void
    {
        $client = static::createClient();
        $this->prepareDatabase();
        $user = $this->persistUser('blank@example.com', 'Blank');

        $this->authenticate($client, $user);
        $client->request('GET', '/profile');

        $client->submitForm('Save changes', ['profile[name]' => '']);

        // Symfony renders invalid form submissions with HTTP 422.
        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextContains('.form-error', 'Name is required.');
    }

    public function testNewPasswordMustBeAtLeastEightCharacters(): void
    {
        $client = static::createClient();
        $this->prepareDatabase();
        $user = $this->persistUser('short@example.com', 'Short');

        $this->authenticate($client, $user);
        $client->request('GET', '/profile');

        $client->submitForm('Save changes', [
            'profile[name]' => 'Short',
            'profile[plainPassword][first]' => 'short',
            'profile[plainPassword][second]' => 'short',
        ]);

        // Symfony renders invalid form submissions with HTTP 422.
        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextContains('.form-error', 'at least 8 characters');
    }

    private function authenticate(KernelBrowser $client, User $user): void
    {
        /** @var JWTTokenManagerInterface $jwtManager */
        $jwtManager = static::getContainer()->get('lexik_jwt_authentication.jwt_manager');

        $client->getCookieJar()->set(new Cookie('BEARER', $jwtManager->create($user)));
    }

    private function prepareDatabase(): void
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $metadata = $entityManager->getMetadataFactory()->getAllMetadata();

        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);
    }

    private function persistUser(string $email, string $name): User
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        /** @var UserPasswordHasherInterface $passwordHasher */
        $passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);

        $user = (new User())
            ->setName($name)
            ->setEmail($email)
            ->setRoles(['ROLE_USER']);
        $user->setPassword($passwordHasher->hashPassword($user, self::PASSWORD));

        $entityManager->persist($user);
        $entityManager->flush();

        return $user;
    }
}
