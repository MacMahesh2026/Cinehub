<?php

namespace App\Controller\Admin;

use App\Entity\Movie;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use Symfony\Component\Form\Extension\Core\Type\FileType;

final class MovieCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Movie::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Movie')
            ->setEntityLabelInPlural('Movies')
            ->setSearchFields(['title', 'genre', 'language'])
            ->setDefaultSort(['title' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('title');
        yield TextareaField::new('description')->hideOnIndex();
        yield IntegerField::new('durationMinutes', 'Duration (minutes)');
        yield DateField::new('releaseDate', 'Release date')->hideOnIndex();
        yield TextField::new('language');
        yield TextField::new('genre');
        yield ImageField::new('posterName', 'Poster')
            ->setBasePath($this->getParameter('app.movie_poster_uri_prefix'))
            ->hideOnForm();
        yield Field::new('posterFile', 'Upload poster')
            ->setFormType(FileType::class)
            ->setFormTypeOption('required', false)
            ->setHelp('JPEG, PNG or WebP up to 2 MB. Uploading replaces the current poster.')
            ->hideOnIndex()
            ->hideOnDetail();
        yield UrlField::new('posterUrl', 'External poster URL (optional)')->hideOnIndex();
        yield AssociationField::new('showtimes', 'Showtimes')->onlyOnDetail();
    }
}
