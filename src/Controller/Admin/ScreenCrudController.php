<?php

namespace App\Controller\Admin;

use App\Entity\Screen;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\HttpFoundation\Response;

final class ScreenCrudController extends AbstractCrudController
{
    use DeletionGuardTrait;

    public static function getEntityFqcn(): string
    {
        return Screen::class;
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
        /** @var Screen $screen */
        $screen = $entity;

        $reasons = [];

        if ($this->showtimesHaveBookings($screen->getShowtimes())) {
            $reasons[] = 'it has showtimes with bookings';
        }

        if ($this->seatsHaveBookings($screen->getSeats())) {
            $reasons[] = 'some of its seats are part of existing bookings';
        }

        return $reasons;
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
