<?php

namespace App\DataFixtures;

use App\Entity\Movie;
use App\Entity\Theatre;
use App\Entity\Screen;
use App\Entity\Seat;
use App\Entity\Showtime;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setName('Test User');
        $user->setEmail('test@example.com');

        $user->setPassword(
            $this->passwordHasher->hashPassword(
                $user,
                'Test@12345'
            )
        );

        $manager->persist($user);
        /*
         * =========================
         * MOVIES
         * =========================
         */

        $movie1 = new Movie();
        $movie1->setTitle('Avengers: Endgame');
        $movie1->setDescription('The Avengers face their biggest challenge yet.');
        $movie1->setDurationMinutes(181);
        $movie1->setLanguage('English');
        $movie1->setGenre('Action');

        $movie2 = new Movie();
        $movie2->setTitle('Inception');
        $movie2->setDescription('A skilled team enters the dreams of others.');
        $movie2->setDurationMinutes(148);
        $movie2->setLanguage('English');
        $movie2->setGenre('Sci-Fi');

        $movie3 = new Movie();
        $movie3->setTitle('3 Idiots');
        $movie3->setDescription(
            'Three friends experience college life and discover the meaning of success.'
        );
        $movie3->setDurationMinutes(170);
        $movie3->setLanguage('Hindi');
        $movie3->setGenre('Comedy');

        $manager->persist($movie1);
        $manager->persist($movie2);
        $manager->persist($movie3);


        /*
         * =========================
         * THEATRES
         * =========================
         */

        $theatre1 = new Theatre();
        $theatre1->setName('CineHub Downtown');
        $theatre1->setAddress('123 Main Street');
        $theatre1->setCity('Delhi');

        $theatre2 = new Theatre();
        $theatre2->setName('CineHub Mall');
        $theatre2->setAddress('45 Mall Road');
        $theatre2->setCity('Noida');

        $manager->persist($theatre1);
        $manager->persist($theatre2);


        /*
         * =========================
         * SCREENS
         * =========================
         */

        $screen1 = new Screen();
        $screen1->setName('Screen 1');
        $screen1->setTotalSeats(20);
        $screen1->setTheatre($theatre1);

        $screen2 = new Screen();
        $screen2->setName('Screen 2');
        $screen2->setTotalSeats(20);
        $screen2->setTheatre($theatre1);

        $screen3 = new Screen();
        $screen3->setName('Screen 1');
        $screen3->setTotalSeats(20);
        $screen3->setTheatre($theatre2);

        $manager->persist($screen1);
        $manager->persist($screen2);
        $manager->persist($screen3);


        /*
         * =========================
         * SEATS
         * =========================
         */

        $screens = [$screen1, $screen2, $screen3];

        foreach ($screens as $screen) {

            foreach (['A', 'B', 'C', 'D'] as $row) {

                for ($number = 1; $number <= 5; $number++) {

                    $seat = new Seat();

                    $seat->setSeatNumber($row . $number);
                    $seat->setRowName($row);
                    $seat->setSeatType('Regular');
                    $seat->setScreen($screen);

                    $manager->persist($seat);
                }
            }
        }


        /*
         * =========================
         * SHOWTIMES
         * =========================
         */

        $showtimes = [
            [
                'movie' => $movie1,
                'screen' => $screen1,
                'start' => '2026-09-18 18:00:00',
                'end' => '2026-09-18 21:01:00',
                'price' => '250.00',
            ],
            [
                'movie' => $movie1,
                'screen' => $screen1,
                'start' => '2026-09-18 21:30:00',
                'end' => '2026-09-19 00:31:00',
                'price' => '300.00',
            ],
            [
                'movie' => $movie2,
                'screen' => $screen2,
                'start' => '2026-09-18 17:00:00',
                'end' => '2026-09-18 19:28:00',
                'price' => '220.00',
            ],
            [
                'movie' => $movie2,
                'screen' => $screen2,
                'start' => '2026-09-18 20:00:00',
                'end' => '2026-09-18 22:28:00',
                'price' => '280.00',
            ],
            [
                'movie' => $movie3,
                'screen' => $screen3,
                'start' => '2026-09-18 16:00:00',
                'end' => '2026-09-18 18:50:00',
                'price' => '180.00',
            ],
            [
                'movie' => $movie3,
                'screen' => $screen3,
                'start' => '2026-09-18 19:30:00',
                'end' => '2026-09-18 22:20:00',
                'price' => '220.00',
            ],
        ];

        foreach ($showtimes as $showtimeData) {

            $showtime = new Showtime();

            $showtime->setMovie($showtimeData['movie']);
            $showtime->setScreen($showtimeData['screen']);
            $showtime->setStartTime(
                new \DateTime($showtimeData['start'])
            );
            $showtime->setEndTime(
                new \DateTime($showtimeData['end'])
            );
            $showtime->setPrice($showtimeData['price']);

            $manager->persist($showtime);
        }

        $manager->flush();
    }
}