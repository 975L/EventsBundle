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
use Doctrine\ORM\QueryBuilder;
use c975L\EventsBundle\Entity\Event;

/**
 * Repository for Event Entity
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2017 975L <contact@975l.com>
 */
class EventRepository extends EntityRepository
{
    /**
     * Finds Events for Carousel
     * @return mixed
     */
    public function findForCarousel(int $number)
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

    /**
     * Finds all the Events NOT finished and NOT suppressed
     * @return mixed
     */
    public function findNotFinished()
    {
        $qb = $this->createQueryBuilder('e');
        $qb->select('e')
            ->where('e.startDate >= :currentDate OR e.endDate >= :currentDate')
            ->andwhere('e.suppressed = 0')
            ->setParameter('currentDate', new \Datetime())
            ->addOrderBy('e.startDate', 'ASC')
            ->addOrderBy('e.startTime', 'ASC')
            ;

        return $qb->getQuery()->getResult();
    }

    /**
     * Finds all the Events
     * @return mixed
     */
    public function findAll()
    {
        $qb = $this->createQueryBuilder('e');
        $qb->select('e')
            ->addOrderBy('e.startDate', 'ASC')
            ->addOrderBy('e.startTime', 'ASC')
            ;

        return $qb->getQuery()->getResult();
    }

    /**
     * Finds all the Events NOT suppressed
     * @return mixed
     */
    public function findNotSuppressed()
    {
        $qb = $this->createQueryBuilder('e');
        $qb->select('e')
            ->where('e.suppressed = 0')
            ->addOrderBy('e.startDate', 'ASC')
            ->addOrderBy('e.startTime', 'ASC')
            ;

        return $qb->getQuery()->getResult();
    }
}