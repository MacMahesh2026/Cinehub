<?php

namespace App\Controller\Admin;

use App\Entity\Theatre;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\HttpFoundation\Response;

final class TheatreCrudController extends AbstractCrudController
{
    use DeletionGuardTrait;

    public static function getEntityFqcn(): string
    {
        return Theatre::class;
    }

    public function delete(AdminContext $context): KeyValueStore|Response
    {
        $reasons = $this->deletionBlockers($context->getEntity()->getInstance());

        if ([] !== $reasons) {
            return $this->redirectBecauseDeletionIsBlocked($context, $reasons);
        }

        return parent::delete($context);
    }

    protected function deletionBlockers(object $entity): array
    {
        /** @var Theatre $theatre */
        $theatre = $entity;

        foreach ($theatre->getScreens() as $screen) {
            if ($this->showtimesHaveBookings($screen->getShowtimes())) {
                return ['one of its screens has showtimes with bookings'];
            }

            if ($this->seatsHaveBookings($screen->getSeats())) {
                return ['one of its seats is part of an existing booking'];
            }
        }

        return [];
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
