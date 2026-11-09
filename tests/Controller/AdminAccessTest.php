<?php

namespace App\Tests\Controller;

use App\Entity\Movie;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class AdminAccessTest extends WebTestCase
{
    private const PASSWORD = 'AdminPass123';

    public function testAnonymousVisitorIsRedirectedToAdminLogin(): void
    {
        $client = static::createClient();
        $client->request('GET', '/admin');

        self::assertResponseRedirects('/admin/login');
    }

    public function testAdminLoginPageIsPublic(): void
    {
        $client = static::createClient();
        $client->request('GET', '/admin/login');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.brand', 'CineHub');
        self::assertSelectorExists('input[name="_csrf_token"]');
    }

    public function testAdminCanSignInThroughTheForm(): void
    {
        $client = static::createClient();
        $this->prepareDatabase();
        $this->persistUser('admin@example.com', ['ROLE_ADMIN']);

        $client->request('GET', '/admin/login');
        $client->submitForm('Sign in', [
            '_username' => 'admin@example.com',
            '_password' => self::PASSWORD,
        ]);

        self::assertResponseRedirects('/admin');

        $client->followRedirect();

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.admin-welcome h1', 'Admin');
    }

    public function testRegularUserIsForbidden(): void
    {
        $client = static::createClient();
        $this->prepareDatabase();
        $client->loginUser($this->persistUser('customer@example.com', ['ROLE_USER']), 'admin');

        $client->request('GET', '/admin');

        self::assertResponseStatusCodeSame(403);
    }

    public function testAdminCanOpenDashboard(): void
    {
        $client = static::createClient();
        $this->prepareDatabase();
        $client->loginUser($this->persistUser('admin@example.com', ['ROLE_ADMIN']), 'admin');

        $client->request('GET', '/admin');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.admin-welcome h1', 'Admin');
        self::assertSelectorExists('a[href="/admin/movie"]');
        self::assertSelectorExists('a[href="/admin/booking"]');
        self::assertSelectorExists('a[href="/admin/user"]');
    }

    public function testMovieFormExposesPosterUploadField(): void
    {
        $client = static::createClient();
        $this->prepareDatabase();
        $client->loginUser($this->persistUser('admin@example.com', ['ROLE_ADMIN']), 'admin');

        $client->request('GET', '/admin/movie/new');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('input[type="file"][name*="posterFile"]');
    }

    public function testMovieListRendersThePosterThumbnail(): void
    {
        $client = static::createClient();
        $this->prepareDatabase();
        $admin = $this->persistUser('admin@example.com', ['ROLE_ADMIN']);

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $entityManager->persist((new Movie())->setTitle('Poster Movie')->setPosterName('poster.jpg'));
        $entityManager->flush();

        $client->loginUser($admin, 'admin');
        $client->request('GET', '/admin/movie');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('img[src*="poster.jpg"]');
    }

    public function testAdminCanOpenEveryCrudList(): void
    {
        $client = static::createClient();
        $this->prepareDatabase();
        $client->loginUser($this->persistUser('admin@example.com', ['ROLE_ADMIN']), 'admin');

        $urls = [
            '/admin/movie',
            '/admin/theatre',
            '/admin/screen',
            '/admin/seat',
            '/admin/showtime',
            '/admin/booking',
            '/admin/user',
        ];

        foreach ($urls as $url) {
            $client->request('GET', $url);

            self::assertResponseIsSuccessful(sprintf('The admin page "%s" should render.', $url));
        }
    }

    private function prepareDatabase(): void
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $metadata = $entityManager->getMetadataFactory()->getAllMetadata();

        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);
    }

    /**
     * @param list<string> $roles
     */
    private function persistUser(string $email, array $roles): User
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        /** @var UserPasswordHasherInterface $passwordHasher */
        $passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);

        $user = (new User())
            ->setName('Admin')
            ->setEmail($email)
            ->setRoles($roles);

        $user->setPassword($passwordHasher->hashPassword($user, self::PASSWORD));

        $entityManager->persist($user);
        $entityManager->flush();

        return $user;
    }
}
