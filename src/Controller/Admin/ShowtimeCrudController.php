<?php

namespace App\Controller\Admin;

use App\Entity\Showtime;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;

final class ShowtimeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Showtime::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Showtime')
            ->setEntityLabelInPlural('Showtimes')
            ->setSearchFields(['movie.title', 'screen.name', 'screen.theatre.name'])
            ->setDefaultSort(['startTime' => 'DESC']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield AssociationField::new('movie', 'Movie');
        yield AssociationField::new('screen', 'Screen');
        yield DateTimeField::new('startTime', 'Starts at');
        yield DateTimeField::new('endTime', 'Ends at');
        yield NumberField::new('price')
            ->setNumDecimals(2)
            ->setStoredAsString(true);
        yield AssociationField::new('bookings', 'Bookings')->onlyOnDetail();
    }
}
