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
use Symfony\Component\DependencyInjection\ContainerInterface;
use c975L\EventsBundle\Entity\Event;
use c975L\EventsBundle\Form\EventFormFactoryInterface;
use c975L\ServicesBundle\Service\ServiceImageInterface;
use c975L\ServicesBundle\Service\ServiceSlugInterface;
use c975L\EventsBundle\Service\EventsServiceInterface;

/**
 * Main services related to Events
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2017 975L <contact@975l.com>
 */
class EventsService implements EventsServiceInterface
{
    /**
     * Stores ContainerInterface
     * @var ContainerInterface
     */
    private $container;

    /**
     * Stores EntityManagerInterface
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * Stores EventFormFactoryInterface
     * @var EventFormFactoryInterface
     */
    private $eventFormFactory;

    /**
     * Stores ServiceImageInterface
     * @var ServiceImageInterface
     */
    private $serviceImage;

    /**
     * Stores ServiceSlugInterface
     * @var ServiceSlugInterface
     */
    private $serviceSlug;

    public function __construct(
        ContainerInterface $container,
        EntityManagerInterface $em,
        EventFormFactoryInterface $eventFormFactory,
        ServiceImageInterface $serviceImage,
        ServiceSlugInterface $serviceSlug
    )
    {
        $this->container = $container;
        $this->em = $em;
        $this->eventFormFactory = $eventFormFactory;
        $this->serviceImage = $serviceImage;
        $this->serviceSlug = $serviceSlug;
    }

    /**
     * {@inheritdoc}
     */
    public function cloneObject(Event $eventObject)
    {
        $eventClone = clone $eventObject;
        $eventClone
            ->setTitle(null)
            ->setSlug(null)
        ;

        return $eventClone;
    }

    /**
     * {@inheritdoc}
     */
    public function createForm(string $name, Event $eventObject)
    {
        return $this->eventFormFactory->create($name, $eventObject);
    }

    /**
     * {@inheritdoc}
     */
    public function defineImage(Event $eventObject)
    {
        if (is_file($this->getPicturePath($eventObject))) {
            $eventObject->setPicture('images/' . $this->getFolderPictures() . $eventObject->getSlug() . '-' . $eventObject->getId() . '.jpg');
        } else {
            $eventObject->setPicture(null);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Event $eventObject)
    {
        //Deletes picture file
        $this->serviceImage->delete($this->getPicturePath($eventObject));

        //Persists data in DB
        $eventObject->setSuppressed(true);
        $this->em->persist($eventObject);
        $this->em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getFolderPictures()
    {
        $folderPictures = $this->container->getParameter('c975_l_events.folderPictures');

        if (false === strrpos($folderPictures, '/')) {
            $folderPictures .= '/';
        }

        return $folderPictures;
    }

    /**
     * {@inheritdoc}
     */
    public function getPictureName(Event $eventObject)
    {
        return $eventObject->getSlug() . '-' . $eventObject->getId() . '.jpg';
    }

    /**
     * {@inheritdoc}
     */
    public function getPicturePath(Event $eventObject)
    {
        return $this->serviceImage->getFolder($this->getFolderPictures()) . $this->getPictureName($eventObject);
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
        $uow = $this->em->getUnitOfWork();
        $uow->computeChangeSets();
        if (isset($uow->getEntityChangeSet($eventObject)['slug'])) {
            $eventObject->setSlug($this->serviceSlug->slugify('c975LEventsBundle:Event', $eventObject->getSlug()));
        }

        $eventObject->setSuppressed(false);

        //Resizes and renames the picture
        $this->serviceImage->resize($eventObject->getPicture(), $this->serviceImage->getFolder($this->getFolderPictures()), $this->getPictureName($eventObject), 'jpg', 400, 75);
        $this->defineImage($eventObject);

        //Persists data in DB
        $this->em->persist($eventObject);
        $this->em->flush();
    }
}
