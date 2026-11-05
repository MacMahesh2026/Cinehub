<?php

namespace App\Controller\Admin;

use App\Entity\Theatre;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

final class TheatreCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Theatre::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Theatre')
            ->setEntityLabelInPlural('Theatres')
            ->setSearchFields(['name', 'address', 'city'])
            ->setDefaultSort(['name' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('name');
        yield TextField::new('address')->hideOnIndex();
        yield TextField::new('city');
        yield AssociationField::new('screens', 'Screens')->onlyOnDetail();
    }
}
