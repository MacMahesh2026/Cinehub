<?php

namespace App\Controller\Admin;

use App\Entity\Screen;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

final class ScreenCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Screen::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Screen')
            ->setEntityLabelInPlural('Screens')
            ->setSearchFields(['name', 'theatre.name', 'theatre.city'])
            ->setDefaultSort(['theatre.name' => 'ASC', 'name' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('name');
        yield AssociationField::new('theatre', 'Theatre');
        yield IntegerField::new('totalSeats', 'Total seats');
        yield AssociationField::new('seats', 'Seats')->onlyOnDetail();
        yield AssociationField::new('showtimes', 'Showtimes')->onlyOnDetail();
    }
}
