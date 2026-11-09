<?php

namespace App\Tests\Controller;

use App\Entity\Movie;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class MoviePosterRenderingTest extends WebTestCase
{
    public function testHomeAndDetailsPagesRenderTheStoredPoster(): void
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $metadata = $entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);

        $movie = (new Movie())
            ->setTitle('Poster Movie')
            ->setPosterName('poster.jpg');

        $entityManager->persist($movie);
        $entityManager->flush();

        $client->request('GET', '/');
        self::assertResponseIsSuccessful();
        self::assertSelectorExists('img[src*="poster.jpg"][alt="Poster Movie poster"]');

        $client->request('GET', '/movie/'.$movie->getId());
        self::assertResponseIsSuccessful();
        self::assertSelectorExists('img[src*="poster.jpg"][alt="Poster Movie poster"]');
    }
}
