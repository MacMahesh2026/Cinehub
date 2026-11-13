<?php

namespace App\Tests\MessageHandler;

use App\Entity\Booking;
use App\Entity\BookingSeat;
use App\Entity\Movie;
use App\Entity\Screen;
use App\Entity\Seat;
use App\Entity\Showtime;
use App\Entity\Theatre;
use App\Entity\User;
use App\Message\BookingConfirmedMessage;
use App\Notifier\WhatsAppClient;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Psr\Log\NullLogger;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class BookingConfirmedHandlerTest extends KernelTestCase
{
    private const PASSWORD = 'BookingPass123';

    /**
     * @var list<array{method: string, url: string, options: array}>
     */
    private array $requests = [];

    public function testItEmailsAndMessagesTheCustomerWhenABookingIsConfirmed(): void
    {
        self::bootKernel();
        $this->prepareDatabase();

        $user = $this->persistUser('buyer@example.com', 'Buyer', '+919876543210');
        $booking = $this->persistBooking($user);
        $this->installWhatsAppSpy();

        $this->dispatch($booking);

        self::assertEmailCount(1);

        $email = self::getMailerMessage();
        self::assertInstanceOf(TemplatedEmail::class, $email);
        self::assertEmailAddressContains($email, 'To', 'buyer@example.com');
        self::assertEmailSubjectContains($email, $booking->getBookingReference());
        self::assertEmailHtmlBodyContains($email, 'Inception');
        self::assertEmailHtmlBodyContains($email, $booking->getBookingReference());

        self::assertCount(1, $this->requests, 'The customer should receive a WhatsApp message.');
        self::assertSame('POST', $this->requests[0]['method']);
        self::assertStringEndsWith('/123456789/messages', $this->requests[0]['url']);

        $payload = json_decode($this->requests[0]['options']['body'], true, flags: \JSON_THROW_ON_ERROR);
        self::assertSame('+919876543210', $payload['to']);
        self::assertSame('booking_confirmation', $payload['template']['name']);

        $parameters = array_column($payload['template']['components'][0]['parameters'], 'text');
        self::assertSame(['Buyer', $booking->getBookingReference(), 'Inception'], \array_slice($parameters, 0, 3));
    }

    public function testItStillEmailsWhenTheCustomerHasNoPhoneNumber(): void
    {
        self::bootKernel();
        $this->prepareDatabase();

        $user = $this->persistUser('nophone@example.com', 'NoPhone', null);
        $booking = $this->persistBooking($user);
        $this->installWhatsAppSpy();

        $this->dispatch($booking);

        self::assertEmailCount(1);
        self::assertEmailAddressContains(self::getMailerMessage(), 'To', 'nophone@example.com');

        self::assertCount(0, $this->requests, 'No WhatsApp message should be sent without a phone number.');
    }

    public function testItIgnoresAnUnknownBooking(): void
    {
        self::bootKernel();
        $this->prepareDatabase();

        $this->dispatch(new BookingConfirmedMessage(999999));

        self::assertEmailCount(0);
        self::assertCount(0, $this->requests);
    }

    private function dispatch(Booking|BookingConfirmedMessage $bookingOrMessage): void
    {
        $message = $bookingOrMessage instanceof Booking
            ? new BookingConfirmedMessage($bookingOrMessage->getId())
            : $bookingOrMessage;

        /** @var MessageBusInterface $bus */
        $bus = static::getContainer()->get(MessageBusInterface::class);
        $bus->dispatch($message);
    }

    private function installWhatsAppSpy(): void
    {
        $this->requests = [];

        $httpClient = new MockHttpClient(function (string $method, string $url, array $options): MockResponse {
            $this->requests[] = ['method' => $method, 'url' => $url, 'options' => $options];

            return new MockResponse('{"messages":[{"id":"wamid.TEST"}]}', ['http_code' => 200]);
        });

        // Replace the configured client so the handler exercises a real Graph
        // API call against a mock HTTP transport instead of the network.
        static::getContainer()->set(WhatsAppClient::class, new WhatsAppClient(
            $httpClient,
            new NullLogger(),
            'v21.0',
            '123456789',
            'test-token',
            'booking_confirmation',
            'en',
        ));
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

    private function persistBooking(User $customer): Booking
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

        return $booking;
    }
}
