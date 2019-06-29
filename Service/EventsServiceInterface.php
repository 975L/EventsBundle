<?php
/*
 * (c) 2018: 975L <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\EventsBundle\Service;

use c975L\EventsBundle\Entity\Event;
use Symfony\Component\Form\Form;

/**
 * Interface to be called for DI for Events Main related services
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2018 975L <contact@975l.com>
 */
interface EventsServiceInterface
{
    /**
     * Clones the object
     * @return Event
     */
    public function cloneObject(Event $eventObject);

    /**
     * Shortcut to call EventFormFactory to create Form
     * @return Form
     */
    public function createForm(string $name, Event $eventObject);

    /**
     * Defines the image for the Event
     */
    public function defineImage(Event $eventObject);

    /**
     * Deletes the Event
     */
    public function delete(Event $eventObject);

    /**
     * Gets pictures folder from config
     * @return string
     */
    public function getFolderPictures();

    /**
     * Get filename for the picture
     * @return string
     */
    public function getPictureName(Event $eventObject);

    /**
     * Get full picture path
     * @return string
     */
    public function getPicturePath(Event $eventObject);

    /**
     * Gets all the Events, even those suppressed
     * @return array
     */
    public function getEventsAll();

    /**
     * Gets all the Events not finished
     * @return array
     */
    public function getEventsNotFinished();

    /**
     * Registers the Event
     */
    public function register(Event $eventObject);
}
