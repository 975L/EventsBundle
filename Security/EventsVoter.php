<?php
/*
 * (c) 2018: 975L <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\EventsBundle\Security;

use c975L\ConfigBundle\Service\ConfigServiceInterface;
use c975L\EventsBundle\Entity\Event;
use LogicException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Voter for Event access
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2018 975L <contact@975l.com>
 */
class EventsVoter extends Voter
{
    /**
     * Stores ConfigServiceInterface
     * @var ConfigServiceInterface
     */
    private $configService;

    /**
     * Stores AccessDecisionManagerInterface
     * @var AccessDecisionManagerInterface
     */
    private $decisionManager;

    /**
     * Used for access to config
     * @var string
     */
    public const CONFIG = 'c975LEvents-config';

    /**
     * Used for access to create
     * @var string
     */
    public const CREATE = 'c975LEvents-create';

    /**
     * Used for access to dashboard
     * @var string
     */
    public const DASHBOARD = 'c975LEvents-dashboard';

    /**
     * Used for access to delete
     * @var string
     */
    public const DELETE = 'c975LEvents-delete';

    /**
     * Used for access to duplicate
     * @var string
     */
    public const DUPLICATE = 'c975LEvents-duplicate';

    /**
     * Used for access to help
     * @var string
     */
    public const HELP = 'c975LEvents-help';

    /**
     * Used for access to modify
     * @var string
     */
    public const MODIFY = 'c975LEvents-modify';

    /**
     * Used for access to slug
     * @var string
     */
    public const SLUG = 'c975LEvents-slug';

    /**
     * Contains all the available attributes to check with in supports()
     * @var array
     */
    private const ATTRIBUTES = array(
        self::CONFIG,
        self::CREATE,
        self::DASHBOARD,
        self::DELETE,
        self::DUPLICATE,
        self::HELP,
        self::MODIFY,
        self::SLUG,
    );

    public function __construct(
        ConfigServiceInterface $configService,
        AccessDecisionManagerInterface $decisionManager
    ) {
        $this->configService = $configService;
        $this->decisionManager = $decisionManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        if (null !== $subject) {
            return $subject instanceof Event && in_array($attribute, self::ATTRIBUTES);
        }

        return in_array($attribute, self::ATTRIBUTES);
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        //Defines access rights
        switch ($attribute) {
            case self::CONFIG:
            case self::CREATE:
            case self::DASHBOARD:
            case self::DELETE:
            case self::DUPLICATE:
            case self::HELP:
            case self::MODIFY:
            case self::SLUG:
                return $this->decisionManager->decide($token, array($this->configService->getParameter('c975LEvents.roleNeeded', 'c975l/events-bundle')));
                break;
        }

        throw new LogicException('Invalid attribute: ' . $attribute);
    }
}
