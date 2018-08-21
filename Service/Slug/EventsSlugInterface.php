<?php
/*
 * (c) 2018: 975L <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\EventsBundle\Service\Slug;

use c975L\EventsBundle\Entity\Event;

/**
 * Interface to be called for DI for ContactForm Main related services
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2018 975L <contact@975l.com>
 */
interface EventsSlugInterface
{

    /**
     * Checks if slug already exists
     * @return bool
     */
    public function exists(string $slug);

    /**
     * Checks if url provided slug match Event slug or will provide redirect url
     * @return string|null
     */
    public function match(string $route, Event $eventObject, string $slug);

    /**
     * Slugify function - https://github.com/cocur/slugify
     * @return string
     */
    public function slugify(string $text);
}
