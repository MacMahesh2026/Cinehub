<?php

namespace App\Controller\Admin;

use App\Entity\Seat;
use App\Entity\Showtime;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Option\EA;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Dto\BatchActionDto;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGeneratorInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Prevents admin deletes that would destroy booking history.
 *
 * Catalogue entities (theatres, screens, movies, seats and showtimes) are
 * cascaded when their parent is removed, but bookings are financial records and
 * must never be removed implicitly. These helpers let the CRUD controllers stop
 * such a deletion with an explanatory flash message instead of letting the
 * database throw a foreign key constraint error.
 */
trait DeletionGuardTrait
{
    /**
     * Returns the reasons why the given entity must not be deleted.
     *
     * @return list<string>
     */
    abstract protected function deletionBlockers(object $entity): array;

    public function batchDelete(AdminContext $context, BatchActionDto $batchActionDto): Response
    {
        $entityManager = $this->container->get('doctrine')->getManagerForClass($context->getEntity()->getFqcn());
        $repository = $entityManager->getRepository($context->getEntity()->getFqcn());

        $reasons = [];

        foreach ($batchActionDto->getEntityIds() as $entityId) {
            $entity = $repository->find($entityId);

            if (null === $entity) {
                continue;
            }

            foreach ($this->deletionBlockers($entity) as $reason) {
                $reasons[$reason] = true;
            }
        }

        if ([] !== $reasons) {
            return $this->redirectBecauseDeletionIsBlocked($context, array_keys($reasons));
        }

        return parent::batchDelete($context, $batchActionDto);
    }

    /**
     * @param list<string> $reasons
     */
    private function redirectBecauseDeletionIsBlocked(AdminContext $context, array $reasons): RedirectResponse
    {
        $this->addFlash('danger', sprintf(
            '%s cannot be deleted because %s. Remove or reassign those records first.',
            $context->getEntity()->getName(),
            implode(' and ', $reasons),
        ));

        return $this->redirect($this->container->get(AdminUrlGeneratorInterface::class)
            ->setController($context->getCrud()->getControllerFqcn())
            ->setAction(Action::INDEX)
            ->unset(EA::ENTITY_ID)
            ->generateUrl());
    }

    /**
     * @param iterable<Showtime> $showtimes
     */
    private function showtimesHaveBookings(iterable $showtimes): bool
    {
        foreach ($showtimes as $showtime) {
            if ($showtime->getBookings()->count() > 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param iterable<Seat> $seats
     */
    private function seatsHaveBookings(iterable $seats): bool
    {
        foreach ($seats as $seat) {
            if ($seat->getBookingSeats()->count() > 0) {
                return true;
            }
        }

        return false;
    }
}
