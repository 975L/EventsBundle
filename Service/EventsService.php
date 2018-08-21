<?php
/*
 * (c) 2018: 975L <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\EventsBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use c975L\EventsBundle\Entity\Event;
use c975L\EventsBundle\Service\Image\EventsImageInterface;
use c975L\EventsBundle\Service\Slug\EventsSlugInterface;
use c975L\EventsBundle\Service\EventsServiceInterface;

/**
 * Main services related to Events
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2017 975L <contact@975l.com>
 */
class EventsService implements EventsServiceInterface
{
    /**
     * Stores EntityManager
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * Stores EventsImage Service
     * @var EventsImageInterface
     */
    private $eventsImage;

    /**
     * Stores EventsSlug Service
     * @var EventsSlugInterface
     */
    private $eventsSlug;

    public function __construct(
        EntityManagerInterface $em,
        EventsImageInterface $eventsImage,
        EventsSlugInterface $eventsSlug
    )
    {
        $this->em = $em;
        $this->eventsImage = $eventsImage;
        $this->eventsSlug = $eventsSlug;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Event $eventObject)
    {
        //Deletes picture file
        $this->eventsImage->delete($eventObject);

        //Persists data in DB
        $eventObject->setSuppressed(true);
        $this->em->persist($eventObject);
        $this->em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getEventsAll()
    {
        return $this->em
            ->getRepository('c975LEventsBundle:Event')
            ->findAll()
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getEventsNotFinished()
    {
        return $this->em
            ->getRepository('c975LEventsBundle:Event')
            ->findNotFinished()
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function register(Event $eventObject)
    {
        //Adjust slug in case of not accepted signs that has been added by user
        $eventObject->setSlug($this->eventsSlug->slugify($eventObject->getSlug()));
        $eventObject->setSuppressed(false);

        //Resizes and renames the picture
        $this->eventsImage->resize($eventObject->getPicture(), $eventObject->getSlug() . '-' . $eventObject->getId());
        $this->eventsImage->define($eventObject);

        //Persists data in DB
        $this->em->persist($eventObject);
        $this->em->flush();
    }
}
