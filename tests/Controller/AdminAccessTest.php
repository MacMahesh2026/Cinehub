<?php

namespace App\Tests\Controller;

use App\Entity\Booking;
use App\Entity\BookingSeat;
use App\Entity\Movie;
use App\Entity\Screen;
use App\Entity\Seat;
use App\Entity\Showtime;
use App\Entity\Theatre;
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

    public function testAdminCanOpenEveryCrudEditForm(): void
    {
        $client = static::createClient();
        $this->prepareDatabase();
        $admin = $this->persistUser('admin@example.com', ['ROLE_ADMIN']);
        $entities = $this->persistScreeningGraph($admin);
        $client->loginUser($admin, 'admin');

        $urls = [
            sprintf('/admin/movie/%d/edit', $entities['movie']->getId()),
            sprintf('/admin/theatre/%d/edit', $entities['theatre']->getId()),
            sprintf('/admin/screen/%d/edit', $entities['screen']->getId()),
            sprintf('/admin/seat/%d/edit', $entities['seat']->getId()),
            sprintf('/admin/showtime/%d/edit', $entities['showtime']->getId()),
            sprintf('/admin/booking/%d/edit', $entities['booking']->getId()),
            sprintf('/admin/user/%d/edit', $admin->getId()),
        ];

        foreach ($urls as $url) {
            $client->request('GET', $url);

            self::assertResponseIsSuccessful(sprintf('The admin edit page "%s" should render.', $url));
        }
    }

    public function testDeletingTheatreWithBookingsIsBlocked(): void
    {
        $client = static::createClient();
        $this->prepareDatabase();
        $admin = $this->persistUser('admin@example.com', ['ROLE_ADMIN']);
        $entities = $this->persistScreeningGraph($admin);
        $client->loginUser($admin, 'admin');

        $this->deleteEntity($client, '/admin/theatre', $entities['theatre']->getId());

        self::assertSelectorTextContains('#flash-messages', 'cannot be deleted');

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        self::assertNotNull($entityManager->find(Theatre::class, $entities['theatre']->getId()));
    }

    public function testDeletingCatalogueEntitiesWithoutBookingsCascades(): void
    {
        $client = static::createClient();
        $this->prepareDatabase();
        $admin = $this->persistUser('admin@example.com', ['ROLE_ADMIN']);

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $theatre = (new Theatre())->setName('Empty Theatre')->setAddress('1 Main Street')->setCity('Delhi');
        $screen = (new Screen())->setName('Screen 1')->setTotalSeats(20)->setTheatre($theatre);
        $movie = (new Movie())->setTitle('No Bookings Movie');
        $seat = (new Seat())->setRowName('A')->setSeatNumber('1')->setSeatType('Regular')->setScreen($screen);
        $showtime = (new Showtime())
            ->setMovie($movie)
            ->setScreen($screen)
            ->setStartTime(new \DateTime('+1 day 18:00'))
            ->setEndTime(new \DateTime('+1 day 20:30'))
            ->setPrice('12.50');

        foreach ([$theatre, $screen, $movie, $seat, $showtime] as $entity) {
            $entityManager->persist($entity);
        }
        $entityManager->flush();

        $client->loginUser($admin, 'admin');

        $this->deleteEntity($client, '/admin/movie', $movie->getId());

        $entityManager->clear();
        self::assertNull($entityManager->find(Movie::class, $movie->getId()));
        self::assertNull($entityManager->find(Showtime::class, $showtime->getId()));
        self::assertNotNull($entityManager->find(Screen::class, $screen->getId()));

        $this->deleteEntity($client, '/admin/theatre', $theatre->getId());

        $entityManager->clear();
        self::assertNull($entityManager->find(Theatre::class, $theatre->getId()));
        self::assertNull($entityManager->find(Screen::class, $screen->getId()));
        self::assertNull($entityManager->find(Seat::class, $seat->getId()));
    }

    private function deleteEntity($client, string $indexUrl, int $id): void
    {
        $crawler = $client->request('GET', $indexUrl);
        $token = $crawler->filter('#action-confirmation-form input[name="token"]')->attr('value');

        $client->request('POST', sprintf('%s/%d/delete', $indexUrl, $id), ['token' => $token]);

        self::assertResponseRedirects();

        $client->followRedirect();
        self::assertResponseIsSuccessful();
    }

    /**
     * @return array{theatre: Theatre, screen: Screen, movie: Movie, seat: Seat, showtime: Showtime, booking: Booking}
     */
    private function persistScreeningGraph(User $customer): array
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $theatre = (new Theatre())->setName('CineHub Downtown')->setAddress('123 Main Street')->setCity('Delhi');
        $screen = (new Screen())->setName('Screen 1')->setTotalSeats(20)->setTheatre($theatre);
        $movie = (new Movie())->setTitle('Inception');
        $seat = (new Seat())->setRowName('A')->setSeatNumber('1')->setSeatType('Regular')->setScreen($screen);
        $showtime = (new Showtime())
            ->setMovie($movie)
            ->setScreen($screen)
            ->setStartTime(new \DateTime('+1 day 18:00'))
            ->setEndTime(new \DateTime('+1 day 20:30'))
            ->setPrice('12.50');
        $booking = (new Booking())
            ->setUser($customer)
            ->setShowtime($showtime)
            ->setBookingReference('BK-0001')
            ->setBookingDate(new \DateTime())
            ->setTotalAmount('12.50')
            ->setStatus('CONFIRMED');
        $bookingSeat = (new BookingSeat())->setBooking($booking)->setSeat($seat);

        foreach ([$theatre, $screen, $movie, $seat, $showtime, $booking, $bookingSeat] as $entity) {
            $entityManager->persist($entity);
        }
        $entityManager->flush();

        return compact('theatre', 'screen', 'movie', 'seat', 'showtime', 'booking');
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
