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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\GoneHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
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
        $this->denyAccessUnlessGranted('dashboard', null);

        //Gets the events
        $events = $this->getDoctrine()
            ->getManager()
            ->getRepository('c975LEventsBundle:Event')
            ->findAllEvents()
            ;

        //Pagination
        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $events,
            $request->query->getInt('p', 1),
            10
        );

        //Renders the dashboard
        return $this->render('@c975LEvents/pages/dashboard.html.twig', array(
            'events' => $pagination,
        ));
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
    public function display(EventsService $eventsService, Event $eventObject)
    {
        //Defines image
        $eventsService->defineImage($eventObject);

        //Deleted event
        if (true === $eventObject->getSuppressed()) {
            throw new GoneHttpException();
        }

        //Renders the event
        return $this->render('@c975LEvents/pages/display.html.twig', array(
            'event' => $eventObject,
        ));
    }

//CREATE
    /**
     * @Route("/events/create",
     *      name="events_create")
     * @Method({"GET", "HEAD", "POST"})
     */
    public function create(Request $request, EventsService $eventsService)
    {
        $eventObject = new Event();
        $this->denyAccessUnlessGranted('create', $eventObject);

        //Defines form
        $eventConfig = array('action' => 'create');
        $form = $this->createForm(EventType::class, $eventObject, array('eventConfig' => $eventConfig));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //Adjust slug in case of not accepted signs
            $eventObject->setSlug($eventsService->slugify($form->getData()->getSlug()));

            //Persists data in DB
            $em = $this->getDoctrine()->getManager();
            $em->persist($eventObject);
            $em->flush();

            //Resizes and renames the picture
            $eventsService->resizeImage($form->getData()->getPicture(), $eventObject->getSlug() . '-' . $eventObject->getId());

            //Redirects to the event
            return $this->redirectToRoute('events_display', array(
                'slug' => $eventObject->getSlug(),
                'id' => $eventObject->getId(),
            ));
        }

        //Renders the create form
        return $this->render('@c975LEvents/forms/create.html.twig', array(
            'form' => $form->createView(),
            'tinymceApiKey' => $this->container->hasParameter('tinymceApiKey') ? $this->getParameter('tinymceApiKey') : null,
            'tinymceLanguage' => $this->getParameter('c975_l_events.tinymceLanguage'),
        ));
    }

//MODIFY
    /**
     * @Route("/events/modify/{slug}/{id}",
     *      name="events_modify",
     *      requirements={
     *          "slug": "^([a-z0-9\-]+)",
     *          "id": "^([0-9]+)"
     *      })
     * @Method({"GET", "HEAD", "POST"})
     * @ParamConverter("Event", options={"mapping": {"id": "id"}})
     */
    public function modify(Request $request, EventsService $eventsService, Event $eventObject)
    {
        $this->denyAccessUnlessGranted('modify', $eventObject);

        //Defines form
        $originalSlug = $eventObject->getSlug();
        $eventsService->defineImage($eventObject);
        $eventConfig = array('action' => 'modify');
        $form = $this->createForm(EventType::class, $eventObject, array('eventConfig' => $eventConfig));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //Adjust slug in case of not accepted signs
            $eventObject->setSlug($eventsService->slugify($form->getData()->getSlug()));

            //Persists data in DB
            $em = $this->getDoctrine()->getManager();
            $em->persist($eventObject);
            $em->flush();

            //Resizes and renames the picture
            $eventsService->resizeImage($form->getData()->getPicture(), $eventObject->getSlug() . '-' . $eventObject->getId());

            //Redirects to the event
            return $this->redirectToRoute('events_display', array(
                'slug' => $eventObject->getSlug(),
                'id' => $eventObject->getId(),
            ));
        }

        //Returns the modify form
        return $this->render('@c975LEvents/forms/modify.html.twig', array(
            'form' => $form->createView(),
            'event' => $eventObject,
            'tinymceApiKey' => $this->container->hasParameter('tinymceApiKey') ? $this->getParameter('tinymceApiKey') : null,
            'tinymceLanguage' => $this->getParameter('c975_l_events.tinymceLanguage'),
        ));
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
    public function duplicate(Request $request, EventsService $eventsService, Event $eventObject)
    {
        $this->denyAccessUnlessGranted('duplicate', $eventObject);

        //Defines form
        $eventClone = clone $eventObject;
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

        //Returns the duplicate form
        return $this->render('@c975LEvents/forms/duplicate.html.twig', array(
            'form' => $form->createView(),
            'event' => $eventClone,
            'tinymceApiKey' => $this->container->hasParameter('tinymceApiKey') ? $this->getParameter('tinymceApiKey') : null,
            'tinymceLanguage' => $this->getParameter('c975_l_events.tinymceLanguage'),
        ));
    }

//DELETE
    /**
     * @Route("/events/delete/{slug}/{id}",
     *      name="events_delete",
     *      requirements={
     *          "slug": "^([a-z0-9\-]+)",
     *          "id": "^([0-9]+)"
     *      })
     * @Method({"GET", "HEAD", "POST"})
     * @ParamConverter("Event", options={"mapping": {"id": "id"}})
     */
    public function delete(Request $request, EventsService $eventsService, Event $eventObject)
    {
        $this->denyAccessUnlessGranted('delete', $eventObject);

        //Defines form
        $eventsService->defineImage($eventObject);
        $eventConfig = array('action' => 'delete');
        $form = $this->createForm(EventType::class, $eventObject, array('eventConfig' => $eventConfig));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //Deletes picture file
            $eventsService->deleteImage($eventObject);

            //Persists data in DB
            $eventObject->setSuppressed(true);
            $em = $this->getDoctrine()->getManager();
            $em->persist($eventObject);
            $em->flush();

            //Redirects to the dashboard
            return $this->redirectToRoute('events_dashboard');
        }

        //Renders the delete form
        return $this->render('@c975LEvents/forms/delete.html.twig', array(
            'form' => $form->createView(),
            'event' => $eventObject,
        ));
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
     * @ParamConverter("Event", options={"mapping": {"id": "id"}})
     */
    public function ical(EventsService $eventsService, Event $eventObject)
    {
        //Renders the ical
        return new Response(
            $this->renderView(
                '@c975LEvents/eventIcal.ics.twig', array(
                    'event' => $eventObject,
                )),
            200,
            array('Content-Type' => 'text/calendar', 'Content-Disposition' => 'inline; filename=' . $eventObject->getSlug() . '.ics')
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
            ->findAllEvents()
            ;

        //Renders the list of events
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
            ->findAllFinishedEvents()
            ;

        //Pagination
        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $events,
            $request->query->getInt('p', 1),
            10
        );

        //Renders the list of events
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
        $this->denyAccessUnlessGranted('help', null);

        //Renders the help
        return $this->render('@c975LEvents/pages/help.html.twig');
    }
}
