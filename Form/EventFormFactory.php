<?php
/*
 * (c) 2018: 975L <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\EventsBundle\Form;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use c975L\EventsBundle\Entity\Event;
use c975L\EventsBundle\Form\EventType;
use c975L\EventsBundle\Form\EventFormFactoryInterface;

/**
 * EventFormFactory class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2018 975L <contact@975l.com>
 */
class EventFormFactory implements EventFormFactoryInterface
{
    /**
     * Stores container
     * @var ContainerInterface
     */
    private $container;

    public function __construct(
        ContainerInterface $container,
        FormFactoryInterface $formFactory
    )
    {
        $this->container = $container;
        $this->formFactory = $formFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function create(string $name, Event $eventObject)
    {
        $config = array('action' => $name);

        return $this->formFactory->create(EventType::class, $eventObject, array('config' => $config));
    }
}
