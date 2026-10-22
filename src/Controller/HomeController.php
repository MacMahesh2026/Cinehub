<?php

namespace App\Controller;

use App\Repository\MovieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home', methods: ['GET'])]
    public function index(MovieRepository $movieRepository): Response
    {
        $movies = $movieRepository->findAll();

        return $this->render('home/index.html.twig', [
            'movies' => $movies,
        ]);
    }

    #[Route('/movie/{id}', name: 'movie_details', methods: ['GET'])]
    public function details(int $id, MovieRepository $movieRepository): Response
    {
        $movie = $movieRepository->find($id);

        if ($movie === null) {
            throw $this->createNotFoundException('Movie not found.');
        }

        return $this->render('movie/details.html.twig', [
            'movie' => $movie,
        ]);
    }
}