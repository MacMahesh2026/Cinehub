<?php

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
final class DashboardController extends AbstractDashboardController
{
    public function index(): Response
    {
        return $this->render('admin/dashboard.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('CineHub Admin');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');

        yield MenuItem::section('Catalogue');
        yield MenuItem::linkTo(MovieCrudController::class, 'Movies', 'fa fa-film');
        yield MenuItem::linkTo(TheatreCrudController::class, 'Theatres', 'fa fa-building');
        yield MenuItem::linkTo(ScreenCrudController::class, 'Screens', 'fa fa-desktop');
        yield MenuItem::linkTo(SeatCrudController::class, 'Seats', 'fa fa-th');
        yield MenuItem::linkTo(ShowtimeCrudController::class, 'Showtimes', 'fa fa-clock');

        yield MenuItem::section('Sales');
        yield MenuItem::linkTo(BookingCrudController::class, 'Bookings', 'fa fa-ticket');

        yield MenuItem::section('Customers');
        yield MenuItem::linkTo(UserCrudController::class, 'Users', 'fa fa-users');

        yield MenuItem::section();
        yield MenuItem::linkToRoute('View public site', 'fa fa-external-link-alt', 'home');
    }
}
