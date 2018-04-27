<?php
/*
 * (c) 2017: 975L <contact@975l.com>
 * (c) 2017: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\EventsBundle\Repository;

use Doctrine\ORM\EntityRepository;
use c975L\EventsBundle\Entity\Event;

class EventRepository extends EntityRepository
{
    //Finds next $number events
    public function findForCarousel($number)
    {
        $qb = $this->createQueryBuilder('e');
        $qb->select('e')
            ->where('e.startDate >= :currentDate OR e.endDate >= :currentDate')
            ->andwhere('e.suppressed = 0')
            ->setParameter('currentDate', new \Datetime())
            ->orderBy('e.startDate', 'ASC')
            ->orderBy('e.startTime', 'ASC')
            ->setMaxResults($number)
            ;

        return $qb->getQuery()->getResult();
    }

    //Finds all the events NOT finished and NOT suppressed
    public function findAllEvents()
    {
        $qb = $this->createQueryBuilder('e');
        $qb->select('e')
            ->where('e.startDate >= :currentDate OR e.endDate >= :currentDate')
            ->andwhere('e.suppressed = 0')
            ->setParameter('currentDate', new \Datetime())
            ->orderBy('e.startDate', 'ASC')
            ->orderBy('e.startTime', 'ASC')
            ;

        return $qb->getQuery()->getResult();
    }

    //Finds all the events finished and NOT suppressed
    public function findAllFinishedEvents()
    {
        $qb = $this->createQueryBuilder('e');
        $qb->select('e')
            ->where('e.endDate < :currentDate OR (e.endDate IS NULL AND e.startDate < :currentDate)')
            ->andwhere('e.suppressed = 0')
            ->setParameter('currentDate', new \Datetime())
            ->orderBy('e.startDate', 'DESC')
            ;

        return $qb;
    }
}