<?php

namespace c975L\EventsBundle\Repository;

use Doctrine\ORM\EntityRepository;
use c975L\EventsBundle\Entity\Event;

class EventRepository extends EntityRepository
{
    //Finds next $number next events
    public function findForCarousel($number)
    {
        $qb = $this->createQueryBuilder('e');
        $qb->select('e')
            ->where('e.endDate >= :currentDate')
            ->andwhere('e.suppressed is NULL')
            ->setParameter('currentDate', new \Datetime())
            ->orderBy('e.startDate', 'ASC')
            ->orderBy('e.startTime', 'ASC')
            ->setMaxResults($number)
            ;

        return $qb->getQuery()->getResult();
    }
}
