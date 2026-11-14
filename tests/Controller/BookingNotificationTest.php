<?php

namespace App\Tests\Controller;

use App\Entity\Booking;
use App\Entity\Movie;
use App\Entity\Screen;
use App\Entity\Seat;
use App\Entity\Showtime;
use App\Entity\Theatre;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class BookingNotificationTest extends WebTestCase
{
    private const PASSWORD = 'BookingPass123';

    public function testCreatingABookingSendsAConfirmationToTheCustomer(): void
    {
        $client = static::createClient();
        $this->prepareDatabase();

        $user = $this->persistUser('buyer@example.com', 'Buyer', '+919876543210');
        $graph = $this->persistScreeningGraph();

        $this->authenticate($client, $user);

        $client->request('POST', sprintf('/showtime/%d/book', $graph['showtime']->getId()), [
            'seat_ids' => [$graph['seat']->getId()],
        ]);

        self::assertResponseRedirects();

        $booking = static::getContainer()->get(EntityManagerInterface::class)
            ->getRepository(Booking::class)
            ->findOneBy(['user' => $user]);

        self::assertInstanceOf(Booking::class, $booking);

        // The confirmation is dispatched through Messenger; the test transport
        // runs it inline, so the email is observable right after the request.
        self::assertEmailCount(1);

        $email = self::getMailerMessage();
        self::assertEmailAddressContains($email, 'To', 'buyer@example.com');
        self::assertEmailSubjectContains($email, $booking->getBookingReference());
        self::assertEmailHtmlBodyContains($email, 'Inception');
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

    private function persistUser(string $email, string $name, ?string $phone): User
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        /** @var UserPasswordHasherInterface $passwordHasher */
        $passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);

        $user = (new User())
            ->setName($name)
            ->setEmail($email)
            ->setPhone($phone)
            ->setRoles(['ROLE_USER']);
        $user->setPassword($passwordHasher->hashPassword($user, self::PASSWORD));

        $entityManager->persist($user);
        $entityManager->flush();

        return $user;
    }

    /**
     * @return array{theatre: Theatre, screen: Screen, movie: Movie, seat: Seat, showtime: Showtime}
     */
    private function persistScreeningGraph(): array
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

        foreach ([$theatre, $screen, $movie, $seat, $showtime] as $entity) {
            $entityManager->persist($entity);
        }
        $entityManager->flush();

        return compact('theatre', 'screen', 'movie', 'seat', 'showtime');
    }
}
