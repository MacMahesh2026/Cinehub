<?php

namespace App\Controller\Admin;

use App\Entity\Booking;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

final class BookingCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Booking::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Booking')
            ->setEntityLabelInPlural('Bookings')
            ->setSearchFields(['bookingReference', 'user.email', 'user.name', 'showtime.movie.title'])
            ->setDefaultSort(['bookingDate' => 'DESC']);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::NEW, Action::DELETE, Action::BATCH_DELETE);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('bookingReference', 'Reference');
        yield AssociationField::new('user', 'Customer');
        yield AssociationField::new('showtime', 'Showtime');
        yield DateTimeField::new('bookingDate', 'Booked at');
        yield NumberField::new('totalAmount', 'Amount')
            ->setNumDecimals(2)
            ->setStoredAsString(true);
        yield ChoiceField::new('status')->setChoices([
            'Confirmed' => 'CONFIRMED',
            'Cancelled' => 'CANCELLED',
        ])->renderAsBadges([
            'CONFIRMED' => 'success',
            'CANCELLED' => 'danger',
        ]);
        yield AssociationField::new('bookingSeats', 'Seats')->onlyOnDetail();
    }
}
