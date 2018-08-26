<?php
/*
 * (c) 2018: 975L <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\EventsBundle\Twig;

use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use c975L\EventsBundle\Service\EventsServiceInterface;
use c975L\EventsBundle\Entity\Event;

/**
 * Twig extension to display the carousel using `events_carousel($number)`
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2018 975L <contact@975l.com>
 */
class EventsCarousel extends \Twig_Extension
{
    /**
     * Stores EntityManagerInterface
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * Stores EventsServiceInterface
     * @var EventsServiceInterface
     */
    private $eventsService;

    public function __construct(
        EntityManagerInterface $em,
        EventsServiceInterface $eventsService
    )
    {
        $this->em = $em;
        $this->eventsService = $eventsService;
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction(
                'events_carousel',
                array($this, 'Carousel'),
                array(
                    'needs_environment' => true,
                    'is_safe' => array('html'),
                )
            ),
        );
    }

    /**
     * Returns the xhtml code for the Carousel with $number of Events
     * @return string
     */
    public function Carousel(\Twig_Environment $environment, int $number)
    {
        //Gets Events
        $events = $this->em
            ->getRepository('c975LEventsBundle:Event')
            ->findForCarousel($number)
        ;

        //Defines pictures
        foreach ($events as $event) {
            $this->eventsService->defineImage($event);
        }

        //Returns the carousel
        return $environment->render('@c975LEvents/pages/carousel.html.twig', array(
            'events' => $events,
        ));
    }
}