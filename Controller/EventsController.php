<?php
/*
 * (c) 2017: 975L <contact@975l.com>
 * (c) 2017: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\EventsBundle\Controller;

use c975L\ConfigBundle\Service\ConfigServiceInterface;
use c975L\EventsBundle\Entity\Event;
use c975L\EventsBundle\Service\EventsServiceInterface;
use c975L\ServicesBundle\Service\ServiceSlugInterface;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\GoneHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Main Controller class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2017 975L <contact@975l.com>
 */
class EventsController extends AbstractController
{
    /**
     * Stores EventsServiceInterface
     * @var EventsServiceInterface
     */
    private $eventsService;

    /**
     * Stores ServiceSlugInterface
     * @var ServiceSlugInterface
     */
    private $serviceSlug;

    public function __construct(
        EventsServiceInterface $eventsService,
        ServiceSlugInterface $serviceSlug
    ) {
        $this->eventsService = $eventsService;
        $this->serviceSlug = $serviceSlug;
    }

//DASHBOARD
    /**
     * Displays the dashboard
     * @return Response
     * @throws AccessDeniedException
     *
     * @Route("/events/dashboard",
     *    name="events_dashboard",
     *    methods={"HEAD", "GET"})
     */
    public function dashboard(Request $request, PaginatorInterface $paginator)
    {
        $this->denyAccessUnlessGranted('c975LEvents-dashboard', null);

        //Renders the dashboard
        $events = $paginator->paginate(
            $this->eventsService->getEventsAll(),
            $request->query->getInt('p', 1),
            15
        );
        return $this->render(
            '@c975LEvents/pages/dashboard.html.twig',
            array(
                'events' => $events,
            ));
    }

//DISPLAY
    /**
     * Displays the Event using its unique id
     * @return Response
     * @throws GoneHttpException
     * @throws NotFoundHttpException
     *
     * @Route("/events/{slug}/{id}",
     *    name="events_display",
     *    requirements={
     *        "slug": "^(?!carousel|duplicate)([a-z0-9\-]+)",
     *        "id": "^([0-9]+)"
     *    },
     *    methods={"HEAD", "GET"})
     */
    public function display(Event $eventObject, $slug)
    {
        //Redirects to good slug
        $redirectUrl = $this->serviceSlug->match('events_display', $eventObject, $slug);
        if (null !== $redirectUrl) {
            return new RedirectResponse($redirectUrl);
        }

        //Deleted Event
        if ($eventObject->getSuppressed()) {
            throw new GoneHttpException();
        }

        //Renders the Event
        $this->eventsService->defineImage($eventObject);
        return $this->render(
            '@c975LEvents/pages/display.html.twig',
            array(
                'event' => $eventObject,
            ));
    }

//CREATE
    /**
     * Creates the Event
     * @return Response
     * @throws AccessDeniedException
     *
     * @Route("/events/create",
     *    name="events_create",
     *    methods={"HEAD", "GET", "POST"})
     */
    public function create(Request $request, ConfigServiceInterface $configService)
    {
        $eventObject = new Event();
        $this->denyAccessUnlessGranted('c975LEvents-create', $eventObject);

        //Defines form
        $form = $this->eventsService->createForm('create', $eventObject);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //Registers the Event
            $this->eventsService->register($eventObject);

            //Redirects to the Event
            return $this->redirectToRoute('events_display', array(
                'slug' => $eventObject->getSlug(),
                'id' => $eventObject->getId(),
            ));
        }

        //Renders the create form
        return $this->render(
            '@c975LEvents/forms/create.html.twig',
            array(
                'form' => $form->createView(),
                'tinymceApiKey' => $configService->getParameter('c975LCommon.tinymceApiKey'),
                'tinymceLanguage' => $configService->getParameter('c975LCommon.tinymceLanguage'),
            ));
    }

//MODIFY
    /**
     * Modifies the Event using its unique id
     * @return Response
     * @throws AccessDeniedException
     * @throws NotFoundHttpException
     *
     * @Route("/events/modify/{slug}/{id}",
     *    name="events_modify",
     *    requirements={
     *        "slug": "^([a-z0-9\-]+)",
     *        "id": "^([0-9]+)"
     *    },
     *    methods={"HEAD", "GET", "POST"})
     * @ParamConverter("Event", options={"mapping": {"id": "id"}})
     */
    public function modify(Request $request, Event $eventObject, ConfigServiceInterface $configService, $slug)
    {
        $this->denyAccessUnlessGranted('c975LEvents-modify', $eventObject);

        //Redirects to good slug
        $redirectUrl = $this->serviceSlug->match('events_modify', $eventObject, $slug);
        if (null !== $redirectUrl) {
            return new RedirectResponse($redirectUrl);
        }

        //Defines form
        $this->eventsService->defineImage($eventObject);
        $form = $this->eventsService->createForm('modify', $eventObject);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //Registers the Event
            $this->eventsService->register($eventObject);

            //Redirects to the Event
            return $this->redirectToRoute('events_display', array(
                'slug' => $eventObject->getSlug(),
                'id' => $eventObject->getId(),
            ));
        }

        //Returns the modify form
        return $this->render(
            '@c975LEvents/forms/modify.html.twig',
            array(
                'form' => $form->createView(),
                'event' => $eventObject,
                'tinymceApiKey' => $configService->getParameter('c975LCommon.tinymceApiKey'),
                'tinymceLanguage' => $configService->getParameter('c975LCommon.tinymceLanguage'),
            ));
    }

//DUPLICATE
    /**
     * Duplicates the Event using its unique id
     * @return Response
     * @throws AccessDeniedException
     * @throws NotFoundHttpException
     *
     * @Route("/events/duplicate/{id}",
     *    name="events_duplicate",
     *    requirements={"id": "^[0-9]+$"},
     *    methods={"HEAD", "GET", "POST"})
     */
    public function duplicate(Request $request, Event $eventObject, ConfigServiceInterface $configService)
    {
        $this->denyAccessUnlessGranted('c975LEvents-duplicate', $eventObject);

        //Defines form
        $eventClone = $this->eventsService->cloneObject($eventObject);
        $form = $this->eventsService->createForm('duplicate', $eventClone);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //Registers the Event
            $this->eventsService->register($eventClone);

            //Redirects to the Event
            return $this->redirectToRoute('events_display', array(
                'slug' => $eventClone->getSlug(),
                'id' => $eventClone->getId(),
            ));
        }

        //Returns the duplicate form
        return $this->render(
            '@c975LEvents/forms/duplicate.html.twig',
            array(
                'form' => $form->createView(),
                'event' => $eventClone,
                'tinymceApiKey' => $configService->getParameter('c975LCommon.tinymceApiKey'),
                'tinymceLanguage' => $configService->getParameter('c975LCommon.tinymceLanguage'),
            ));
    }

//DELETE
    /**
     * Marks the Event as deleted using its unique id
     * @return Response
     * @throws AccessDeniedException
     * @throws NotFoundHttpException
     *
     * @Route("/events/delete/{slug}/{id}",
     *    name="events_delete",
     *    requirements={
     *        "slug": "^([a-z0-9\-]+)",
     *        "id": "^([0-9]+)"
     *    },
     *    methods={"HEAD", "GET", "POST"})
     * @ParamConverter("Event", options={"mapping": {"id": "id"}})
     */
    public function delete(Request $request, Event $eventObject, $slug)
    {
        $this->denyAccessUnlessGranted('c975LEvents-delete', $eventObject);

        //Redirects to good slug
        $redirectUrl = $this->serviceSlug->match('events_delete', $eventObject, $slug);
        if (null !== $redirectUrl) {
            return new RedirectResponse($redirectUrl);
        }

        //Defines form
        $this->eventsService->defineImage($eventObject);
        $form = $this->eventsService->createForm('delete', $eventObject);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //Deletes the Event
            $this->eventsService->delete($eventObject);

            //Redirects to the dashboard
            return $this->redirectToRoute('events_dashboard');
        }

        //Renders the delete form
        return $this->render(
            '@c975LEvents/forms/delete.html.twig',
            array(
                'form' => $form->createView(),
                'event' => $eventObject,
            ));
    }

//CONFIG
    /**
     * Displays the configuration
     * @return Response
     * @throws AccessDeniedException
     *
     * @Route("/events/config",
     *    name="events_config",
     *    methods={"HEAD", "GET", "POST"})
     */
    public function config(Request $request, ConfigServiceInterface $configService)
    {
        $this->denyAccessUnlessGranted('c975LEvents-config', null);

        //Defines form
        $form = $configService->createForm('c975l/events-bundle');
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //Validates config
            $configService->setConfig($form);

            //Redirects
            return $this->redirectToRoute('events_dashboard');
        }

        //Renders the config form
        return $this->render(
            '@c975LConfig/forms/config.html.twig',
            array(
                'form' => $form->createView(),
                'toolbar' => '@c975LEvents',
            ));
    }

//ICAL
    /**
     * Returns the iCal version of the Event using its unique id
     * @return Response
     * @throws AccessDeniedException
     * @throws NotFoundHttpException
     *
     * @Route("/events/ical/{slug}/{id}",
     *    name="events_ical",
     *    requirements={
     *        "slug": "^([a-z0-9\-]+)",
     *        "id": "^([0-9]+)"
     *    },
     *    methods={"HEAD", "GET"})
     * @ParamConverter("Event", options={"mapping": {"id": "id"}})
     */
    public function iCal(Event $eventObject, $slug)
    {
        //Redirects to good slug
        $redirectUrl = $this->serviceSlug->match('events_ical', $eventObject, $slug);
        if (null !== $redirectUrl) {
            return new RedirectResponse($redirectUrl);
        }

        //Renders the iCal
        return new Response(
            $this->renderView(
                '@c975LEvents/eventIcal.ics.twig',
                    array(
                        'event' => $eventObject,
                    )),
            200,
            array('Content-Type' => 'text/calendar', 'Content-Disposition' => 'inline; filename=' . $eventObject->getSlug() . '.ics')
        );
    }

//ALL
    /**
     * Redirects to `events_all` Route
     *
     * @Route("/events",
     *    methods={"HEAD", "GET"})
     */
    public function redirectAll()
    {
        return $this->redirectToRoute('events_all');
    }

    /**
     * Displays all the Events NOT finished
     * @return Response
     *
     * @Route("/events/all",
     *    name="events_all",
     *    methods={"HEAD", "GET"})
     */
    public function all()
    {
        //Renders the list of events
        return $this->render(
            '@c975LEvents/pages/eventsAll.html.twig',
            array(
                'events' => $this->eventsService->getEventsNotFinished(),
            ));
    }

//SLUG

    /**
     * Returns the slug corresponding to the text provided
     * @return JsonResponse
     * @throws AccessDeniedException
     *
     * @Route("/events/slug/{text}",
     *    name="events_slug",
     *    methods={"POST"})
     */
    public function slug($text)
    {
        $this->denyAccessUnlessGranted('c975LEvents-slug', null);

        return $this->json(array('a' => $this->serviceSlug->slugify('c975LEventsBundle:Event', $text)));
    }

//HELP
    /**
     * Displays the help
     * @return Response
     * @throws AccessDeniedException
     *
     * @Route("/events/help",
     *    name="events_help",
     *    methods={"HEAD", "GET"})
     */
    public function help()
    {
        $this->denyAccessUnlessGranted('c975LEvents-help', null);

        //Renders the help
        return $this->render('@c975LEvents/pages/help.html.twig');
    }
}
