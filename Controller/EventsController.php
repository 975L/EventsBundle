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
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\GoneHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Cocur\Slugify\Slugify;
use Knp\Component\Pager\PaginatorInterface;
use c975L\EventsBundle\Entity\Event;
use c975L\EventsBundle\Form\EventType;
use c975L\EventsBundle\Service\EventsServiceInterface;
use c975L\EventsBundle\Service\Image\EventsImageInterface;
use c975L\EventsBundle\Service\Slug\EventsSlugInterface;

/**
 * Main Controller class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2017 975L <contact@975l.com>
 */
class EventsController extends Controller
{
    /**
     * Stores EventsService
     * @var EventsServiceInterface
     */
    private $eventsService;

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
        EventsServiceInterface $eventsService,
        EventsImageInterface $eventsImage,
        EventsSlugInterface $eventsSlug
    )
    {
        $this->eventsService = $eventsService;
        $this->eventsImage = $eventsImage;
        $this->eventsSlug = $eventsSlug;
    }

//DASHBOARD
    /**
     * Displays the dashboard
     * @return Response
     * @throws AccessDeniedException
     *
     * @Route("/events/dashboard",
     *      name="events_dashboard")
     * @Method({"GET", "HEAD"})
     */
    public function dashboard(Request $request, PaginatorInterface $paginator)
    {
        $this->denyAccessUnlessGranted('dashboard', null);

        //Renders the dashboard
        $events = $paginator->paginate(
            $this->eventsService->getEventsAll(),
            $request->query->getInt('p', 1),
            15
        );
        return $this->render('@c975LEvents/pages/dashboard.html.twig', array(
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
     *      name="events_display",
     *      requirements={
     *          "slug": "^(?!carousel|duplicate)([a-z0-9\-]+)",
     *          "id": "^([0-9]+)"
     *      })
     * @Method({"GET", "HEAD"})
     */
    public function display(Event $eventObject, $slug)
    {
        //Defines image
        $this->eventsImage->define($eventObject);

        //Deleted event
        if (true === $eventObject->getSuppressed()) {
            throw new GoneHttpException();
        }

        //Redirects to good slug
        $redirectUrl = $this->eventsSlug->match('events_display', $eventObject, $slug);
        if (null !== $redirectUrl) {
            return new RedirectResponse($redirectUrl);
        }

        //Renders the event
        return $this->render('@c975LEvents/pages/display.html.twig', array(
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
     *      name="events_create")
     * @Method({"GET", "HEAD", "POST"})
     */
    public function create(Request $request)
    {
        $eventObject = new Event();
        $this->denyAccessUnlessGranted('create', $eventObject);

        //Defines form
        $eventConfig = array('action' => 'create');
        $form = $this->createForm(EventType::class, $eventObject, array('eventConfig' => $eventConfig));
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
        return $this->render('@c975LEvents/forms/create.html.twig', array(
            'form' => $form->createView(),
            'tinymceApiKey' => $this->container->hasParameter('tinymceApiKey') ? $this->getParameter('tinymceApiKey') : null,
            'tinymceLanguage' => $this->getParameter('c975_l_events.tinymceLanguage'),
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
     *      name="events_modify",
     *      requirements={
     *          "slug": "^([a-z0-9\-]+)",
     *          "id": "^([0-9]+)"
     *      })
     * @Method({"GET", "HEAD", "POST"})
     * @ParamConverter("Event", options={"mapping": {"id": "id"}})
     */
    public function modify(Request $request, Event $eventObject, $slug)
    {
        $this->denyAccessUnlessGranted('modify', $eventObject);

        //Redirects to good slug
        $redirectUrl = $this->eventsSlug->match('events_modify', $eventObject, $slug);
        if (null !== $redirectUrl) {
            return new RedirectResponse($redirectUrl);
        }

        //Defines form
        $originalSlug = $eventObject->getSlug();
        $this->eventsImage->define($eventObject);
        $eventConfig = array('action' => 'modify');

        $form = $this->createForm(EventType::class, $eventObject, array('eventConfig' => $eventConfig));
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
        return $this->render('@c975LEvents/forms/modify.html.twig', array(
            'form' => $form->createView(),
            'event' => $eventObject,
            'tinymceApiKey' => $this->container->hasParameter('tinymceApiKey') ? $this->getParameter('tinymceApiKey') : null,
            'tinymceLanguage' => $this->getParameter('c975_l_events.tinymceLanguage'),
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
     *      name="events_duplicate",
     *      requirements={
     *          "id": "^[0-9]+$"
     *      })
     * @Method({"GET", "HEAD", "POST"})
     */
    public function duplicate(Request $request, Event $eventObject)
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
            //Registers the Event
            $this->eventsService->register($eventClone);

            //Redirects to the Event
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
     * Marks the Event as deleted using its unique id
     * @return Response
     * @throws AccessDeniedException
     * @throws NotFoundHttpException
     *
     * @Route("/events/delete/{slug}/{id}",
     *      name="events_delete",
     *      requirements={
     *          "slug": "^([a-z0-9\-]+)",
     *          "id": "^([0-9]+)"
     *      })
     * @Method({"GET", "HEAD", "POST"})
     * @ParamConverter("Event", options={"mapping": {"id": "id"}})
     */
    public function delete(Request $request, Event $eventObject, $slug)
    {
        $this->denyAccessUnlessGranted('delete', $eventObject);

        //Redirects to good slug
        $redirectUrl = $this->eventsSlug->match('events_delete', $eventObject, $slug);
        if (null !== $redirectUrl) {
            return new RedirectResponse($redirectUrl);
        }

        //Defines form
        $this->eventsImage->define($eventObject);
        $eventConfig = array('action' => 'delete');
        $form = $this->createForm(EventType::class, $eventObject, array('eventConfig' => $eventConfig));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //Deletes the Event
            $this->eventsService->delete($eventObject);

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
     * Returns the iCal version of the Event using its unique id
     * @return Response
     * @throws AccessDeniedException
     * @throws NotFoundHttpException
     *
     * @Route("/events/ical/{slug}/{id}",
     *      name="events_ical",
     *      requirements={
     *          "slug": "^([a-z0-9\-]+)",
     *          "id": "^([0-9]+)"
     *      })
     * @Method({"GET", "HEAD"})
     * @ParamConverter("Event", options={"mapping": {"id": "id"}})
     */
    public function iCal(Event $eventObject)
    {
        //Renders the iCal
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
     * Redirects to `events_all` Route
     *
     * @Route("/events")
     * @Method({"GET", "HEAD"})
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
     *      name="events_all")
     * @Method({"GET", "HEAD"})
     */
    public function all(Request $request)
    {
        //Renders the list of events
        return $this->render('@c975LEvents/pages/eventsAll.html.twig', array(
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
     *      name="events_slug")
     * @Method({"POST"})
     */
    public function slug($text)
    {
        $this->denyAccessUnlessGranted('slug', null);

        return $this->json(array('a' => $this->eventsSlug->slugify($text)));
    }

//HELP
    /**
     * Displays the help
     * @return Response
     * @throws AccessDeniedException
     *
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
