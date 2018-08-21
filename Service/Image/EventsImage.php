<?php
/*
 * (c) 2018: 975L <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\EventsBundle\Service\Image;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\DependencyInjection\ContainerInterface;
use c975L\EventsBundle\Entity\Event;
use c975L\EventsBundle\Service\Image\EventsImageInterface;

/**
 * Services related to Events Image
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2017 975L <contact@975l.com>
 */
class EventsImage implements EventsImageInterface
{
    /**
     * Stores Container
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function define(Event $eventObject)
    {
        //Gets the FileSystem
        $fs = new Filesystem();

        $image = $this->getFolder() . $eventObject->getSlug() . '-' . $eventObject->getId() . '.jpg';
        if ($fs->exists($image)) {
            $eventObject->setPicture($this->getWebFolder() . $eventObject->getSlug() . '-' . $eventObject->getId() . '.jpg');
        } else {
            $eventObject->setPicture(null);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Event $eventObject)
    {
        $fs = new Filesystem();
        $image = $this->getFolder() . $eventObject->getSlug() . '-' . $eventObject->getId() . '.jpg';

        if ($fs->exists($image)) {
            $fs->remove($image);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getFolder()
    {
        $rootDir = $this->container->getParameter('kernel.root_dir');
        $folderPictures = $this->container->getParameter('c975_l_events.folderPictures');

        if (substr(Kernel::VERSION, 0, 1) == 4) {
            return $rootDir . '/../public/images/' . $folderPictures . '/';
        }

        return $rootDir . '/../web/images/' . $folderPictures . '/';
    }

    /**
     * {@inheritdoc}
     */
    public function getWebFolder()
    {
        return 'images/' . $this->container->getParameter('c975_l_events.folderPictures') . '/';
    }

    /**
     * {@inheritdoc}
     */
    public function resize($file, string $finalFileName)
    {
        if (null !== $file) {
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
                    $folderPath = $this->getFolder();
                    $fs->mkdir($folderPath, 0770);
                    $file->move($folderPath, $finalFileName . '.jpg');
                }
            }
        }
    }
}
