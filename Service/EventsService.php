<?php
/*
 * (c) 2018: 975l <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\EventsBundle\Service;

use Symfony\Component\Filesystem\Filesystem;
use Cocur\Slugify\Slugify;
use c975L\EventsBundle\Entity\Event;

class EventsService
{
    private $container;
    private $em;

    public function __construct(
        \Symfony\Component\DependencyInjection\ContainerInterface $container,
        \Doctrine\ORM\EntityManagerInterface $em
        )
    {
        $this->container = $container;
        $this->em = $em;
    }

//SET PICTURE
    //Defines the picture related to Event
    public function setPicture($event)
    {
        //Gets the FileSystem
        $fs = new Filesystem();

        $folderPath = $this->container->getParameter('kernel.root_dir') . '/../web/images/' . $this->container->getParameter('c975_l_events.folderPictures') . '/';
        $picture = $folderPath . $event->getSlug() . '-' . $event->getId() . '.jpg';
        if ($fs->exists($picture)) {
            $event->setPicture('images/' . $this->container->getParameter('c975_l_events.folderPictures') . '/' . $event->getSlug() . '-' . $event->getId() . '.jpg');
        }
    }

//LOADS EVENT
    public function load($id)
    {
        //Loads from DB
        $event = $this->em->getRepository('c975LEventsBundle:Event')->findOneById($id);

        //Not existing event
        if (!$event instanceof Event) {
            throw $this->createNotFoundException();
        }

        return $event;
    }

//RESIZES PICTURE
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
                if ($degree == 90 || $degree == 270) {
                    imagecopyresampled($newPicture, $fileSource, 0, 0, 0, 0, $newWidth, $newHeight, $height, $width);
                } else {
                    imagecopyresampled($newPicture, $fileSource, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                }

                //Saves the picture - JPEG format
                if ($format == 'jpg') {
                    imagejpeg($newPicture, str_replace('jpeg', 'jpg', $filename), $compressionJpg);
                }

                //Destroy picture
                imagedestroy($newPicture);

                //Gets the FileSystem
                $fs = new Filesystem();

                //Saves the file in the right place
                $folderPath = $this->container->getParameter('kernel.root_dir') . '/../web/images/' . $this->container->getParameter('c975_l_events.folderPictures');
                $fs->mkdir($folderPath, 0770);
                $file->move($folderPath, $finalFileName . '.jpg');
            }
        }
    }

//SLUGIFY
    public function slugify($text)
    {
        $slugify = new Slugify();
        return $slugify->slugify($text);
    }
}