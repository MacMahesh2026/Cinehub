<?php

namespace App\Tests\Storage;

use App\Entity\Movie;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use League\Flysystem\FilesystemOperator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Verifies that a movie poster is written to, replaced in and removed from the
 * configured storage whenever the entity is added, updated or deleted.
 */
final class MoviePosterStorageTest extends KernelTestCase
{
    private const PNG = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';

    private EntityManagerInterface $entityManager;
    private FilesystemOperator $storage;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $this->storage = self::getContainer()->get('movie_posters.storage');

        $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool = new SchemaTool($this->entityManager);
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);
    }

    public function testPosterIsStoredReplacedAndDeletedWithTheMovie(): void
    {
        $movie = (new Movie())->setTitle('Storage Test');
        $movie->setPosterFile($this->uploadedFile('first.png'));

        $this->entityManager->persist($movie);
        $this->entityManager->flush();

        $firstName = $movie->getPosterName();

        self::assertNotNull($firstName);
        self::assertNotNull($movie->getPosterUpdatedAt());
        self::assertSame('image/png', $movie->getPosterMimeType());
        self::assertSame('first.png', $movie->getPosterOriginalName());
        self::assertTrue($this->storage->fileExists($firstName), 'The new poster should be written to storage.');

        // Replacing the poster must remove the previous object and store the new one.
        $movie->setPosterFile($this->uploadedFile('second.png'));
        $this->entityManager->flush();

        $secondName = $movie->getPosterName();

        self::assertNotNull($secondName);
        self::assertNotSame($firstName, $secondName);
        self::assertFalse($this->storage->fileExists($firstName), 'The replaced poster should be deleted from storage.');
        self::assertTrue($this->storage->fileExists($secondName), 'The replacement poster should be written to storage.');

        // The new file name must be persisted in the database.
        $id = $movie->getId();
        $this->entityManager->clear();
        $reloaded = $this->entityManager->find(Movie::class, $id);

        self::assertNotNull($reloaded);
        self::assertSame($secondName, $reloaded->getPosterName());

        // Deleting the movie must delete its poster object as well.
        $this->entityManager->remove($reloaded);
        $this->entityManager->flush();

        self::assertFalse($this->storage->fileExists($secondName), 'The poster should be deleted with the movie.');
    }

    private function uploadedFile(string $name): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'movie_poster_');
        file_put_contents($path, base64_decode(self::PNG, true));

        return new UploadedFile($path, $name, 'image/png', null, true);
    }
}
