<?php
/*
 * (c) 2017: 975L <contact@975l.com>
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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Cocur\Slugify\Slugify;
use c975L\EventsBundle\Entity\Event;
use c975L\EventsBundle\Service\EventsService;
use c975L\EventsBundle\Form\EventType;

class EventsController extends Controller
{
//DASHBOARD
    /**
     * @Route("/events/dashboard",
     *      name="events_dashboard")
     * @Method({"GET", "HEAD"})
     */
    public function dashboard(Request $request)
    {
        //Returns the dashboard content
        if (null !== $this->getUser() && $this->get('security.authorization_checker')->isGranted($this->getParameter('c975_l_events.roleNeeded'))) {
            //Gets the events
            $events = $this->getDoctrine()
                ->getManager()
                ->getRepository('c975LEventsBundle:Event')
                ->findAllEvents();

            //Pagination
            $paginator  = $this->get('knp_paginator');
            $pagination = $paginator->paginate(
                $events,
                $request->query->getInt('p', 1),
                10
            );

            //Returns the dashboard
            return $this->render('@c975LEvents/pages/dashboard.html.twig', array(
                'events' => $pagination,
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
     *          "slug": "^(?!carousel|duplicate)([a-z0-9\-]+)",
     *          "id": "^([0-9]+)"
     *      })
     * @Method({"GET", "HEAD"})
     */
    public function display(EventsService $eventsService, $id)
    {
        //Gets the event
        $event = $eventsService->load($id);
        $eventsService->defineImage($event);

        //Deleted event
        if (true === $event->getSuppressed()) {
            throw new HttpException(410);
        }

        return $this->render('@c975LEvents/pages/display.html.twig', array(
            'event' => $event,
        ));
    }

//NEW
    /**
     * @Route("/events/new",
     *      name="events_new")
     */
    public function new(Request $request, EventsService $eventsService)
    {
        //Defines the form
        if (null !== $this->getUser() && $this->get('security.authorization_checker')->isGranted($this->getParameter('c975_l_events.roleNeeded'))) {
            //Defines form
            $event = new Event();
            $eventConfig = array('action' => 'new');
            $form = $this->createForm(EventType::class, $event, array('eventConfig' => $eventConfig));
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                //Adjust slug in case of not accepted signs
                $event->setSlug($eventsService->slugify($form->getData()->getSlug()));

                //Persists data in DB
                $em = $this->getDoctrine()->getManager();
                $em->persist($event);
                $em->flush();

                //Resizes and renames the picture
                $eventsService->resizeImage($form->getData()->getPicture(), $event->getSlug() . '-' . $event->getId());

                //Redirects to the event
                return $this->redirectToRoute('events_display', array(
                    'slug' => $event->getSlug(),
                    'id' => $event->getId(),
                ));
            }

            //Returns the form to edit content
            return $this->render('@c975LEvents/forms/new.html.twig', array(
                'form' => $form->createView(),
                'tinymceApiKey' => $this->container->hasParameter('tinymceApiKey') ? $this->getParameter('tinymceApiKey') : null,
                'tinymceLanguage' => $this->getParameter('c975_l_events.tinymceLanguage'),
            ));
        }

        //Access is denied
        throw $this->createAccessDeniedException();
    }

//MODIFY
    /**
     * @Route("/events/modify/{slug}/{id}",
     *      name="events_modify",
     *      requirements={
     *          "slug": "^([a-z0-9\-]+)",
     *          "id": "^([0-9]+)"
     *      })
     */
    public function modify(Request $request, EventsService $eventsService, $slug, $id)
    {
        //Defines the form
        if (null !== $this->getUser() && $this->get('security.authorization_checker')->isGranted($this->getParameter('c975_l_events.roleNeeded'))) {
            //Gets the event
            $event = $eventsService->load($id);
            $originalSlug = $event->getSlug();

            //Gets the existing picture
            $eventsService->defineImage($event);

            //Defines form
            $eventConfig = array('action' => 'modify');
            $form = $this->createForm(EventType::class, $event, array('eventConfig' => $eventConfig));
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                //Adjust slug in case of not accepted signs
                $event->setSlug($eventsService->slugify($form->getData()->getSlug()));

                //Persists data in DB
                $em = $this->getDoctrine()->getManager();
                $em->persist($event);
                $em->flush();

                //Resizes and renames the picture
                $eventsService->resizeImage($form->getData()->getPicture(), $event->getSlug() . '-' . $event->getId());

                //Redirects to the event
                return $this->redirectToRoute('events_display', array(
                    'slug' => $event->getSlug(),
                    'id' => $event->getId(),
                ));
            }

            //Returns the form to modify content
            return $this->render('@c975LEvents/forms/modify.html.twig', array(
                'form' => $form->createView(),
                'event' => $event,
                'tinymceApiKey' => $this->container->hasParameter('tinymceApiKey') ? $this->getParameter('tinymceApiKey') : null,
                'tinymceLanguage' => $this->getParameter('c975_l_events.tinymceLanguage'),
            ));
        }

        //Access is denied
        throw $this->createAccessDeniedException();
    }

//DUPLICATE
    /**
     * @Route("/events/duplicate/{id}",
     *      name="events_duplicate",
     *      requirements={
     *          "id": "^[0-9]+$"
     *      })
     * @Method({"GET", "HEAD", "POST"})
     */
    public function duplicate(Request $request, EventsService $eventsService, $id)
    {
        //Defines the form
        if (null !== $this->getUser() && $this->get('security.authorization_checker')->isGranted($this->getParameter('c975_l_events.roleNeeded'))) {
            //Gets the event
            $event = $eventsService->load($id);
            $originalSlug = $event->getSlug();

            //Defines form
            $eventClone = clone $event;
            $eventClone
                ->setTitle(null)
                ->setSlug(null)
            ;
            $eventConfig = array('action' => 'duplicate');
            $form = $this->createForm(EventType::class, $eventClone, array('eventConfig' => $eventConfig));
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                //Adjust slug in case of not accepted signs
                $eventClone->setSlug($eventsService->slugify($form->getData()->getSlug()));

                //Persists data in DB
                $em = $this->getDoctrine()->getManager();
                $em->persist($eventClone);
                $em->flush();

                //Resizes and renames the picture
                $eventsService->resizeImage($form->getData()->getPicture(), $eventClone->getSlug() . '-' . $eventClone->getId());

                //Redirects to the event
                return $this->redirectToRoute('events_display', array(
                    'slug' => $eventClone->getSlug(),
                    'id' => $eventClone->getId(),
                ));
            }

            //Returns the form to duplicate content
            return $this->render('@c975LEvents/forms/duplicate.html.twig', array(
                'form' => $form->createView(),
                'event' => $eventClone,
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
     */
    public function delete(Request $request, EventsService $eventsService, $id)
    {
        //Defines the form
        if (null !== $this->getUser() && $this->get('security.authorization_checker')->isGranted($this->getParameter('c975_l_events.roleNeeded'))) {
            //Gets the event
            $event = $eventsService->load($id);
            $eventsService->defineImage($event);

            //Defines form
            $eventConfig = array('action' => 'delete');
            $form = $this->createForm(EventType::class, $event, array('eventConfig' => $eventConfig));
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                //Deletes picture file
                $eventsService->deleteImage($event);

                //Persists data in DB
                $event->setSuppressed(true);
                $em = $this->getDoctrine()->getManager();
                $em->persist($event);
                $em->flush();

                //Redirects to the dashboard
                return $this->redirectToRoute('events_dashboard');
            }

            //Returns the form to delete content
            return $this->render('@c975LEvents/forms/delete.html.twig', array(
                'form' => $form->createView(),
                'event' => $event,
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
    public function ical(EventsService $eventsService, $id)
    {
        //Gets the event
        $event = $eventsService->load($id);

        //Returns the ical
        return new Response(
            $this->renderView(
                '@c975LEvents/eventIcal.ics.twig', array(
                    'event' => $event,
                )),
            200,
            array('Content-Type' => 'text/calendar', 'Content-Disposition' => 'inline; filename=' . $event->getSlug() . '.ics')
        );
    }

//ALL
    /**
     * @Route("/events")
     * @Method({"GET", "HEAD"})
     */
    public function redirectAll()
    {
        return $this->redirectToRoute('events_all');
    }
    /**
     * @Route("/events/all",
     *      name="events_all")
     * @Method({"GET", "HEAD"})
     */
    public function all(Request $request)
    {
        //Gets the events
        $events = $this->getDoctrine()
            ->getManager()
            ->getRepository('c975LEventsBundle:Event')
            ->findAllEvents();

        return $this->render('@c975LEvents/pages/eventsAll.html.twig', array(
            'events' => $events,
            ));
    }

//FINISHED
    /**
     * @Route("/events/finished",
     *      name="events_finished")
     * @Method({"GET", "HEAD"})
     */
    public function finished(Request $request)
    {
        //Gets the events
        $events = $this->getDoctrine()
            ->getManager()
            ->getRepository('c975LEventsBundle:Event')
            ->findAllFinishedEvents();

        //Pagination
        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $events,
            $request->query->getInt('p', 1),
            10
        );

        return $this->render('@c975LEvents/pages/eventsFinished.html.twig', array(
            'events' => $pagination,
            ));
    }

//SLUG
    /**
     * @Route("/events/slug/{text}",
     *      name="events_slug")
     * @Method({"POST"})
     */
    public function slug(EventsService $eventsService, $text)
    {
        return $this->json(array('a' => $eventsService->slugify($text)));
    }

//HELP
    /**
     * @Route("/events/help",
     *      name="events_help")
     * @Method({"GET", "HEAD"})
     */
    public function help()
    {
        //Returns the help
        if (null !== $this->getUser() && $this->get('security.authorization_checker')->isGranted($this->getParameter('c975_l_events.roleNeeded'))) {
            return $this->render('@c975LEvents/pages/help.html.twig');
        }

        //Access is denied
        throw $this->createAccessDeniedException();
    }
}