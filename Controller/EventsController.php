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
use c975L\EventsBundle\Entity\Event;
use c975L\EventsBundle\Form\EventType;

class EventsController extends Controller
{
//DASHBOARD
    /**
     * @Route("/events/dashboard",
     *      name="events_dashboard")
     * @Method({"GET", "HEAD"})
     */
    public function dashboardAction()
    {
        //Gets the user
        $user = $this->getUser();

        //Returns the dashboard content
        if ($user !== null && $this->get('security.authorization_checker')->isGranted($this->getParameter('c975_l_events.roleNeeded'))) {
            //Gets the manager
            $em = $this->getDoctrine()->getManager();

            //Gets repository
            $repository = $em->getRepository('c975LEventsBundle:Event');

            //Loads from DB
            $events = $repository->findBySuppressed(null);

            //Returns the dashboard
            return $this->render('@c975LEvents/pages/dashboard.html.twig', array(
                'events' => $events,
                'title' => $this->get('translator')->trans('label.dashboard', array(), 'events'),
            ));
        }

        //Access is denied
        throw $this->createAccessDeniedException();
    }

//DISPLAY
    /**
     * @Route("/events/{event}/{id}",
     *      name="events_display",
     *      requirements={
     *          "event": "^([a-z0-9\-]+)",
     *          "id": "^([0-9]+)"
     *      })
     * @Method({"GET", "HEAD"})
     */
    public function displayAction($id)
    {
        //Gets the user
        $user = $this->getUser();

        //Gets the event
        $event = $this->loadEvent($id);
        $this->setPicture($event);

        //Deleted event
        if ($event->getSuppressed() === true) {
            throw new HttpException(410);
        }

        //Adds toolbar if rights are ok
        $toolbar = null;
        if ($user !== null && $this->get('security.authorization_checker')->isGranted($this->getParameter('c975_l_events.roleNeeded'))) {
            $toolbar = $this->renderView('@c975LEvents/toolbar.html.twig', array('event' => $event->getSlug(), 'id' => $event->getId()));
        }

        return $this->render('@c975LEvents/pages/eventDisplay.html.twig', array(
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

                //Adjust slug in case of not accepted signs
                $event->setSlug($this->slugify($form->getData()->getSlug()));

                //Persists data in DB
                $em->persist($event);
                $em->flush();

                //Resizes and renames the picture
                if ($form->getData()->getPicture() !== null) {
                    $this->resizeImage($form->getData()->getPicture(), $event->getSlug() . '-' . $event->getId());
                }

                //Redirects to the event
                return $this->redirectToRoute('events_display', array(
                    'event' => $event->getSlug(),
                    'id' => $event->getId(),
                ));
            }

            //Returns the form to edit content
            return $this->render('@c975LEvents/forms/eventNew.html.twig', array(
                'form' => $form->createView(),
                'title' => $this->get('translator')->trans('label.new_event', array(), 'events'),
            ));
        }

        //Access is denied
        throw $this->createAccessDeniedException();
    }

//EDIT
    /**
     * @Route("/events/edit/{event}/{id}",
     *      name="events_edit",
     *      requirements={
     *          "event": "^([a-z0-9\-]+)",
     *          "id": "^([0-9]+)"
     *      })
     * )
     */
    public function editAction(Request $request, $event, $id)
    {
        //Gets the user
        $user = $this->getUser();

        //Defines the form
        if ($user !== null && $this->get('security.authorization_checker')->isGranted($this->getParameter('c975_l_events.roleNeeded'))) {
            //Gets the event
            $event = $this->loadEvent($id);
            $originalSlug = $event->getSlug();

            //Gets the existing picture
            $this->setPicture($event);
            if ($event->getPicture() !== null) {
                $event->setPicture(new File($event->getPicture()));
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
                $event->setSlug($this->slugify($form->getData()->getSlug()));

                //Renames picture file if slug has changed
                if ($fs->exists($picture) && $originalSlug != $event->getSlug()) {
                    $fs->rename($picture, $folderPath . $event->getSlug() . '-' . $event->getId() . '.jpg');
                }

                //Resizes and renames the picture (that will erase existing one)
                if ($form->getData()->getPicture() !== null) {
                    $this->resizeImage($form->getData()->getPicture(), $event->getSlug() . '-' . $event->getId());
                }

                //Persists data in DB
                $em->persist($event);
                $em->flush();

                //Redirects to the event
                return $this->redirectToRoute('events_display', array(
                    'event' => $event->getSlug(),
                    'id' => $event->getId(),
                ));
            }

            //Returns the form to edit content
            return $this->render('@c975LEvents/forms/eventEdit.html.twig', array(
                'form' => $form->createView(),
                'event' => $event,
                'title' => $this->get('translator')->trans('label.modify', array(), 'events') . ' "' . $event->getTitle() . '"',
            ));
        }

        //Access is denied
        throw $this->createAccessDeniedException();
    }

//DELETE
    /**
     * @Route("/events/delete/{event}/{id}",
     *      name="events_delete",
     *      requirements={
     *          "event": "^([a-z0-9\-]+)",
     *          "id": "^([0-9]+)"
     *      })
     * )
     */
    public function deleteAction(Request $request, $event, $id)
    {
        //Gets the user
        $user = $this->getUser();

        //Defines the form
        if ($user !== null && $this->get('security.authorization_checker')->isGranted($this->getParameter('c975_l_events.roleNeeded'))) {
            //Gets the event
            $event = $this->loadEvent($id);
            $event->setAction('delete');
            $this->setPicture($event);

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
                return $this->redirectToRoute('events_display', array(
                    'event' => $event->getSlug(),
                    'id' => $event->getId(),
                ));
            }

            //Returns the form to edit content
            return $this->render('@c975LEvents/forms/eventDelete.html.twig', array(
                'form' => $form->createView(),
                'title' => $this->get('translator')->trans('label.delete', array(), 'events') . ' "' . $event->getTitle() . '"',
                'event' => $event,
            ));
        }

        //Access is denied
        throw $this->createAccessDeniedException();
    }

//ICAL
    /**
     * @Route("/events/ical/{event}/{id}",
     *      name="events_ical",
     *      requirements={
     *          "event": "^([a-z0-9\-]+)",
     *          "id": "^([0-9]+)"
     *      })
     * @Method({"GET", "HEAD"})
     */
    public function icalAction($id)
    {
        //Gets the event
        $event = $this->loadEvent($id);

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

        //Assigns picture
        foreach ($events as $event) {
            $this->setPicture($event);
        }

        //Returns the carousel
        return $this->render('@c975LEvents/pages/carousel.html.twig', array(
            'events' => $events,
            'title' => $this->get('translator')->trans('label.carousel', array(), 'events'),
        ));
    }

//ALL
    /**
     * @Route("/events/all",
     *      name="events_all")
     * @Method({"GET", "HEAD"})
     */
    public function allAction()
    {
        //Gets the manager
        $em = $this->getDoctrine()->getManager();

        //Gets repository
        $repository = $em->getRepository('c975LEventsBundle:Event');

        //Loads from DB
        $events = $repository->findBySuppressed(null);

        return $this->render('@c975LEvents/pages/eventsAll.html.twig', array(
            'events' => $events,
            'title' => $this->get('translator')->trans('label.all_events', array(), 'events'),
            ));
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
            //Returns the help
            return $this->render('@c975LEvents/pages/help.html.twig', array(
                'title' => $this->get('translator')->trans('label.help', array(), 'events'),
            ));
        }

        //Access is denied
        throw $this->createAccessDeniedException();
    }

//SLUG
    /**
     * @Route("/events/slug/{text}",
     *      name="events_slug")
     * @Method({"POST"})
     */
    public function slugAction($text)
    {
        return $this->json(array('a' => $this->slugify($text)));
    }


//FUNCTIONS
    //
    public function setPicture($event)
    {
        //Gets the FileSystem
        $fs = new Filesystem();

        $folderPath = $this->getParameter('kernel.root_dir') . '/../web/images/' . $this->getParameter('c975_l_events.folderPictures') . '/';
        $picture = $folderPath . $event->getSlug() . '-' . $event->getId() . '.jpg';
        if ($fs->exists($picture)) {
            $event->setPicture('images/' . $this->getParameter('c975_l_events.folderPictures') . '/' . $event->getSlug() . '-' . $event->getId() . '.jpg');
        }
    }


    //Loads the event
    public function loadEvent($id)
    {
        //Gets the manager
        $em = $this->getDoctrine()->getManager();

        //Gets repository
        $repository = $em->getRepository('c975LEventsBundle:Event');

        //Loads from DB
        $event = $repository->findOneById($id);

        //Not existing event
        if (!$event instanceof Event) {
            throw $this->createNotFoundException();
        }

        return $event;
    }


    //Resizes the picture
    public function resizeImage($file, $finalFileName)
    {
        //Defines data
        $extension = is_object($file) ? strtolower($file->guessExtension()) : substr($file, strrpos($file, '.') + 1, 3);
        $finalHeight = 400;
        $format = 'jpg';

        //Rotates (if needed) and resizes
        if (in_array($extension, array('jpeg', 'jpg', 'png')) === true) {
            $fileData = getimagesize($file);
            //Also used to reduces poster issued from video
            $filename = is_object($file) ? $file->getRealPath() : $file;
            //Use of of @ avoids errors of type IFD bad offset...
            $exifData = @exif_read_data($filename, 0, true);

            //Creates the final picture
            if (is_array($fileData)) {
                //Defines data
                $compressionJpg = 75;
                $width = $fileData[0];
                $height = $fileData[1];

                //Resizes image
                $newHeight = $finalHeight;
                $newWidth = (int) round(($width * $newHeight) / $height);
                $degree = 0;

                //JPEG format
                if ($fileData[2] == 2) {
                    $fileSource = imagecreatefromjpeg($filename);
                    //Rotates (if needed)
                    if (isset($exifData['IFD0']['Orientation'])) {
                        switch ($exifData['IFD0']['Orientation']) {
                            case 1:
                                $degree = 0;
                                break;
                            case 3:
                                $degree = 180;
                                break;
                            case 6:
                                $degree = 270;
                                $newWidth = (int) round(($height * $newHeight) / $width);
                                break;
                            case 8:
                                $degree = 90;
                                $newWidth = (int) round(($height * $newHeight) / $width);
                                break;
                        }
                        $fileSource = imagerotate($fileSource, $degree, 0);
                    }
                }
                //PNG format
                elseif ($fileData[2] == 3) {
                    $fileSource = imagecreatefrompng($filename);
                }

                //Resizes
                $newPicture = imagecreatetruecolor($newWidth, $newHeight);
                if ($format == 'jpg') {
                    $whiteBackground = imagecolorallocate($newPicture, 255, 255, 255);
                    imagefill($newPicture, 0, 0, $whiteBackground);
                }
                if ($degree == 90 || $degree == 270) imagecopyresampled($newPicture, $fileSource, 0, 0, 0, 0, $newWidth, $newHeight, $height, $width);
                else imagecopyresampled($newPicture, $fileSource, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

                //Saves the picture - JPEG format
                if ($format == 'jpg') imagejpeg($newPicture, str_replace('jpeg', 'jpg', $filename), $compressionJpg);

                //Destroy picture
                imagedestroy($newPicture);

                //Gets the FileSystem
                $fs = new Filesystem();

                //Saves the file in the right place
                $folderPath = $this->getParameter('kernel.root_dir') . '/../web/images/' . $this->getParameter('c975_l_events.folderPictures');
                $fs->mkdir($folderPath, 0770);
                $file->move($folderPath, $finalFileName . '.jpg');
            }
        }
    }


    //Slugify function - https://gist.github.com/umidjons/9757010
    public function slugify($text)
    {
        $slug = preg_replace('/\s\s+/', ' ', trim(mb_strtolower($text)));
        $slug = str_replace(array(',',';','.',':','·','(',')','[',']','{','}','+','\\','/','#','~','&','$','£','µ','@','=','<','>','$','^','°','|'),'', $slug);
        $slug = str_replace(array('œ','Œ'), 'oe', $slug);
        $slug = str_replace(array('æ','Æ'), 'ae', $slug);
        $slug = str_replace(array(' '), '-', $slug);
        $search =  array('ª','à','á','â','ã','ä','å','ç','è','é','ê','ë','ì','í','î','ï','ñ','º','ò','ó','ô','õ','ö','ø','ù','ú','û','ü','ŭ','µ','ý','ÿ','ß','æ','œ','_','"',"'");
        $replace = array('a','a','a','a','a','a','a','c','e','e','e','e','i','i','i','i','n','o','o','o','o','o','o','o','u','u','u','u','u','u','y','y','s','a','o','-','-','-');
        $slug = str_replace($search, $replace, $slug);
        $slug = str_replace(array('--', '--', '--'), '-', $slug);
        return $slug;
    }
}
