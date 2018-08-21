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

/**
 * Interface to be called for DI for ContactForm Main related services
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2018 975L <contact@975l.com>
 */
interface EventsServiceInterface
{
    /**
     * Deletes the Event
     */
    public function delete(Event $eventObject);

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
