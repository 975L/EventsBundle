<?php
/*
 * (c) 2017: 975l <contact@975l.com>
 * (c) 2017: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\EventsBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Cocur\Slugify\Slugify;
use c975L\EventsBundle\Entity\Event;
use c975L\EventsBundle\Service\EventService;
use c975L\EventsBundle\Form\EventType;

class EventsController extends Controller
{
//DASHBOARD
    /**
     * @Route("/events/dashboard",
     *      name="events_dashboard")
     * @Method({"GET", "HEAD"})
     */
    public function dashboardAction(Request $request)
    {
        //Gets the user
        $user = $this->getUser();

        //Returns the dashboard content
        if ($user !== null && $this->get('security.authorization_checker')->isGranted($this->getParameter('c975_l_events.roleNeeded'))) {
            //Gets the manager
            $em = $this->getDoctrine()->getManager();

            //Gets repository
            $repository = $em->getRepository('c975LEventsBundle:Event');

            //Gets the events
            $events = $repository->findAllEvents();

            //Pagination
            $paginator  = $this->get('knp_paginator');
            $pagination = $paginator->paginate(
                $events,
                $request->query->getInt('p', 1),
                10
            );

            //Defines toolbar
            $tools  = $this->renderView('@c975LEvents/tools.html.twig', array(
                'type' => 'dashboard',
            ));
            $toolbar = $this->forward('c975L\ToolbarBundle\Controller\ToolbarController::displayAction', array(
                'tools'  => $tools,
                'dashboard'  => 'events',
            ))->getContent();

            //Returns the dashboard
            return $this->render('@c975LEvents/pages/dashboard.html.twig', array(
                'events' => $pagination,
                'toolbar' => $toolbar,
            ));
        }

        //Access is denied
        throw $this->createAccessDeniedException();
    }

//DISPLAY
    /**
     * @Route("/events/{slug}/{id}",
     *      name="events_display",
     *      requirements={
     *          "slug": "^([a-z0-9\-]+)",
     *          "id": "^([0-9]+)"
     *      })
     * @Method({"GET", "HEAD"})
     */
    public function displayAction($id)
    {
        //Gets the Service
        $eventsService = $this->get(\c975L\EventsBundle\Service\EventsService::class);

        //Gets the event
        $event = $eventsService->load($id);
        $eventsService->setPicture($event);

        //Deleted event
        if ($event->getSuppressed() === true) {
            throw new HttpException(410);
        }

        //Gets the user
        $user = $this->getUser();

        //Defines toolbar
        $toolbar = '';
        if ($user !== null && $this->get('security.authorization_checker')->isGranted($this->getParameter('c975_l_events.roleNeeded'))) {
            $tools  = $this->renderView('@c975LEvents/tools.html.twig', array(
                'type' => 'display',
                'event' => $event,
            ));
            $toolbar = $this->forward('c975L\ToolbarBundle\Controller\ToolbarController::displayAction', array(
                'tools'  => $tools,
                'dashboard'  => 'events',
            ))->getContent();
        }

        return $this->render('@c975LEvents/pages/display.html.twig', array(
            'toolbar' => $toolbar,
            'event' => $event,
        ));
    }

//NEW
    /**
     * @Route("/events/new",
     *      name="events_new")
     * )
     */
    public function newAction(Request $request)
    {
        //Gets the user
        $user = $this->getUser();

        //Defines the form
        if ($user !== null && $this->get('security.authorization_checker')->isGranted($this->getParameter('c975_l_events.roleNeeded'))) {
            //Defines form
            $event = new Event('new');
            $form = $this->createForm(EventType::class, $event);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                //Gets the manager
                $em = $this->getDoctrine()->getManager();

                //Gets the Service
                $eventsService = $this->get(\c975L\EventsBundle\Service\EventsService::class);

                //Adjust slug in case of not accepted signs
                $event->setSlug($eventsService->slugify($form->getData()->getSlug()));

                //Persists data in DB
                $em->persist($event);
                $em->flush();

                //Resizes and renames the picture
                if ($form->getData()->getPicture() !== null) {
                    $eventsService->resizeImage($form->getData()->getPicture(), $event->getSlug() . '-' . $event->getId());
                }

                //Redirects to the event
                return $this->redirectToRoute('events_display', array(
                    'slug' => $event->getSlug(),
                    'id' => $event->getId(),
                ));
            }

            //Defines toolbar
            $tools  = $this->renderView('@c975LEvents/tools.html.twig', array(
                'type' => 'new',
            ));
            $toolbar = $this->forward('c975L\ToolbarBundle\Controller\ToolbarController::displayAction', array(
                'tools'  => $tools,
                'dashboard'  => 'events',
            ))->getContent();

            //Returns the form to edit content
            return $this->render('@c975LEvents/forms/eventNew.html.twig', array(
                'form' => $form->createView(),
                'toolbar' => $toolbar,
                'tinymceApiKey' => $this->container->hasParameter('tinymceApiKey') ? $this->getParameter('tinymceApiKey') : null,
                'tinymceLanguage' => $this->getParameter('c975_l_events.tinymceLanguage'),
            ));
        }

        //Access is denied
        throw $this->createAccessDeniedException();
    }

//EDIT
    /**
     * @Route("/events/edit/{slug}/{id}",
     *      name="events_edit",
     *      requirements={
     *          "slug": "^([a-z0-9\-]+)",
     *          "id": "^([0-9]+)"
     *      })
     * )
     */
    public function editAction(Request $request, $slug, $id)
    {
        //Gets the user
        $user = $this->getUser();

        //Defines the form
        if ($user !== null && $this->get('security.authorization_checker')->isGranted($this->getParameter('c975_l_events.roleNeeded'))) {
            //Gets the Service
            $eventsService = $this->get(\c975L\EventsBundle\Service\EventsService::class);

            //Gets the event
            $event = $eventsService->load($id);
            $originalSlug = $event->getSlug();

            //Gets the existing picture
            $eventsService->setPicture($event);
            $eventPicture = $event->getPicture();
            if ($eventPicture !== null) {
                $event->setPicture(new File($eventPicture));
            }

            //Defines form
            $form = $this->createForm(EventType::class, $event);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                //Gets the manager
                $em = $this->getDoctrine()->getManager();

                //Gets the FileSystem
                $fs = new Filesystem();

                //Adjust slug in case of not accepted signs
                $event->setSlug($eventsService->slugify($form->getData()->getSlug()));

                //Renames picture file if slug has changed
                $folderPath = $this->getParameter('kernel.root_dir') . '/../web/images/' . $this->getParameter('c975_l_events.folderPictures') . '/';
                $picture = $folderPath . $event->getSlug() . '-' . $event->getId() . '.jpg';
                if ($fs->exists($picture) && $originalSlug != $event->getSlug()) {
                    $fs->rename($picture, $folderPath . $event->getSlug() . '-' . $event->getId() . '.jpg');
                }

                //Resizes and renames the picture (that will erase existing one)
                if ($form->getData()->getPicture() !== null) {
                    $eventsService->resizeImage($form->getData()->getPicture(), $event->getSlug() . '-' . $event->getId());
                }

                //Persists data in DB
                $em->persist($event);
                $em->flush();

                //Redirects to the event
                return $this->redirectToRoute('events_display', array(
                    'slug' => $event->getSlug(),
                    'id' => $event->getId(),
                ));
            }

            //Defines toolbar
            $tools  = $this->renderView('@c975LEvents/tools.html.twig', array(
                'type' => 'edit',
                'event' => $event,
            ));
            $toolbar = $this->forward('c975L\ToolbarBundle\Controller\ToolbarController::displayAction', array(
                'tools'  => $tools,
                'dashboard'  => 'events',
            ))->getContent();

            //Returns the form to edit content
            return $this->render('@c975LEvents/forms/eventEdit.html.twig', array(
                'form' => $form->createView(),
                'event' => $event,
                'eventPicture' => $eventPicture,
                'eventTitle' => $event->getTitle(),
                'toolbar' => $toolbar,
                'tinymceApiKey' => $this->container->hasParameter('tinymceApiKey') ? $this->getParameter('tinymceApiKey') : null,
                'tinymceLanguage' => $this->getParameter('c975_l_events.tinymceLanguage'),
            ));
        }

        //Access is denied
        throw $this->createAccessDeniedException();
    }

//DELETE
    /**
     * @Route("/events/delete/{slug}/{id}",
     *      name="events_delete",
     *      requirements={
     *          "slug": "^([a-z0-9\-]+)",
     *          "id": "^([0-9]+)"
     *      })
     * )
     */
    public function deleteAction(Request $request, $id)
    {
        //Gets the user
        $user = $this->getUser();

        //Defines the form
        if ($user !== null && $this->get('security.authorization_checker')->isGranted($this->getParameter('c975_l_events.roleNeeded'))) {
            //Gets the Service
            $eventsService = $this->get(\c975L\EventsBundle\Service\EventsService::class);

            //Gets the event
            $event = $eventsService->load($id);
            $event->setAction('delete');
            $eventsService->setPicture($event);

            //Defines form
            $form = $this->createForm(EventType::class, $event);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                //Gets the FileSystem
                $fs = new Filesystem();

                //Gets the manager
                $em = $this->getDoctrine()->getManager();

                //Deletes picture file
                $folderPath = $this->getParameter('kernel.root_dir') . '/../web/images/' . $this->getParameter('c975_l_events.folderPictures') . '/';
                $picture = $folderPath . $event->getSlug() . '-' . $event->getId() . '.jpg';
                if ($fs->exists($picture)) {
                    $fs->remove($picture);
                }

                //Persists data in DB
                $event->setSuppressed(true);

                $em->persist($event);
                $em->flush();

                //Redirects to the event
                return $this->redirectToRoute('events_dashboard');
            }

            //Defines toolbar
            $tools  = $this->renderView('@c975LEvents/tools.html.twig', array(
                'type' => 'delete',
                'event' => $event,
            ));
            $toolbar = $this->forward('c975L\ToolbarBundle\Controller\ToolbarController::displayAction', array(
                'tools'  => $tools,
                'dashboard'  => 'events',
            ))->getContent();

            //Returns the form to edit content
            return $this->render('@c975LEvents/forms/eventDelete.html.twig', array(
                'form' => $form->createView(),
                'eventTitle' => $event->getTitle(),
                'event' => $event,
                'toolbar' => $toolbar,
            ));
        }

        //Access is denied
        throw $this->createAccessDeniedException();
    }

//ICAL
    /**
     * @Route("/events/ical/{slug}/{id}",
     *      name="events_ical",
     *      requirements={
     *          "slug": "^([a-z0-9\-]+)",
     *          "id": "^([0-9]+)"
     *      })
     * @Method({"GET", "HEAD"})
     */
    public function icalAction($id)
    {
        //Gets the Service
        $eventsService = $this->get(\c975L\EventsBundle\Service\EventsService::class);

        //Gets the event
        $event = $eventsService->load($id);

        //Returns the ical
        return new Response(
            $this->renderView(
                '@c975LEvents/eventIcal.ics.twig',
                array(
                    'event' => $event,
                )),
            200,
            array('Content-Type' => 'text/calendar', 'Content-Disposition' => 'inline; filename=' . $event->getSlug() . '.ics')
        );
    }

//CAROUSEL
    /**
     * @Route("/events/carousel/{number}",
     *      name="events_carousel")
     * @Method({"GET", "HEAD"})
     */
    public function carouselAction($number)
    {
        //Gets the manager
        $em = $this->getDoctrine()->getManager();

        //Gets repository
        $repository = $em->getRepository('c975LEventsBundle:Event');

        //Loads from DB
        $events = $repository->findForCarousel($number);

        //Gets the Service
        $eventsService = $this->get(\c975L\EventsBundle\Service\EventsService::class);

        //Assigns picture
        foreach ($events as $event) {
            $eventsService->setPicture($event);
        }

        //Returns the carousel
        return $this->render('@c975LEvents/pages/carousel.html.twig', array(
            'events' => $events,
        ));
    }

//ALL
    /**
     * @Route("/events/all",
     *      name="events_all")
     * @Method({"GET", "HEAD"})
     */
    public function allAction(Request $request)
    {
        //Gets the manager
        $em = $this->getDoctrine()->getManager();

        //Gets repository
        $repository = $em->getRepository('c975LEventsBundle:Event');

        //Gets the events
        $events = $repository->findAllEvents();

        //Pagination
        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $events,
            $request->query->getInt('p', 1),
            10
        );

        return $this->render('@c975LEvents/pages/eventsAll.html.twig', array(
            'events' => $pagination,
            ));
    }

//SLUG
    /**
     * @Route("/events/slug/{text}",
     *      name="events_slug")
     * @Method({"POST"})
     */
    public function slugAction($text)
    {
        //Gets the Service
        $eventsService = $this->get(\c975L\EventsBundle\Service\EventsService::class);

        return $this->json(array('a' => $eventsService->slugify($text)));
    }

//HELP
    /**
     * @Route("/events/help",
     *      name="events_help")
     * @Method({"GET", "HEAD"})
     */
    public function helpAction()
    {
        //Gets the user
        $user = $this->getUser();

        //Returns the dashboard content
        if ($user !== null && $this->get('security.authorization_checker')->isGranted($this->getParameter('c975_l_events.roleNeeded'))) {
            //Defines toolbar
            $tools  = $this->renderView('@c975LEvents/tools.html.twig', array(
                'type' => 'help',
            ));
            $toolbar = $this->forward('c975L\ToolbarBundle\Controller\ToolbarController::displayAction', array(
                'tools'  => $tools,
                'dashboard'  => 'events',
            ))->getContent();

            //Returns the help
            return $this->render('@c975LEvents/pages/help.html.twig', array(
                'toolbar' => $toolbar,
            ));
        }

        //Access is denied
        throw $this->createAccessDeniedException();
    }
}