<?php

namespace App\Controller\Admin;

use App\Entity\Seat;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

final class SeatCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Seat::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Seat')
            ->setEntityLabelInPlural('Seats')
            ->setSearchFields(['rowName', 'seatNumber', 'screen.name', 'screen.theatre.name'])
            ->setDefaultSort(['screen.name' => 'ASC', 'rowName' => 'ASC', 'seatNumber' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield AssociationField::new('screen', 'Screen');
        yield TextField::new('rowName', 'Row');
        yield TextField::new('seatNumber', 'Seat number');
        yield ChoiceField::new('seatType', 'Type')->setChoices([
            'Regular' => 'Regular',
            'Premium' => 'Premium',
            'Accessible' => 'Accessible',
        ]);
    }
}
