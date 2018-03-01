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
use c975L\EventsBundle\Service\EventsService;
use c975L\EventsBundle\Entity\Event;

class EventsCarousel extends \Twig_Extension
{
    private $em;
    private $service;

    public function __construct(
        \Doctrine\ORM\EntityManagerInterface $em,
        \c975L\EventsBundle\Service\EventsService $service
        )
    {
        $this->em = $em;
        $this->service = $service;
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

    public function Carousel(\Twig_Environment $environment, $number)
    {
        //Gets repository
        $repository = $this->em->getRepository('c975LEventsBundle:Event');

        //Loads from DB
        $events = $repository->findForCarousel($number);

        //Displays the carousel
        //Assigns picture
        foreach ($events as $event) {
            $this->service->setPicture($event);
        }

        //Returns the carousel
        return $environment->render('@c975LEvents/pages/carousel.html.twig', array(
            'events' => $events,
        ));
    }
}