<?php
/*
 * (c) 2018: 975L <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\EventsBundle\Form;

use c975L\EventsBundle\Entity\Event;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * EventFormFactory class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2018 975L <contact@975l.com>
 */
class EventFormFactory implements EventFormFactoryInterface
{
    /**
     * Stores FormFactoryInterface
     * @var FormFactoryInterface
     */
    private $formFactory;

    public function __construct(FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function create(string $name, Event $eventObject)
    {
        switch ($name) {
            case 'create':
            case 'modify':
            case 'duplicate':
            case 'delete':
                $config = array('action' => $name);
                break;
            default:
                $config = array();
                break;
        }

        return $this->formFactory->create(EventType::class, $eventObject, array('config' => $config));
    }
}
