<?php
/*
 * (c) 2018: 975L <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\EventsBundle\Service\Image;

use c975L\EventsBundle\Entity\Event;

/**
 * Interface to be called for DI for Events Image related services
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2018 975L <contact@975l.com>
 */
interface EventsImageInterface
{
    /**
     * Defines the picture related to Event
     * @return ContactForm
     */
    public function define(Event $eventObject);

    /**
     * Deletes picture file
     * @return ContactForm
     */
    public function delete(Event $eventObject);

    /**
     * Gets the images folder
     * @return ContactForm
     */
    public function getFolder();

    /**
     * Gets the images web folder
     * @return ContactForm
     */
    public function getWebFolder();

    /**
     * Resizes picture
     */
    public function resize($file, string $finalFileName);
}
