<?php
/*
 * (c) 2018: 975L <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\EventsBundle\Security;

use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use c975L\EventsBundle\Entity\Event;

class EventsVoter extends Voter
{
    private $decisionManager;
    private $roleNeeded;

    public const CREATE = 'create';
    public const DASHBOARD = 'dashboard';
    public const DELETE = 'delete';
    public const DUPLICATE = 'duplicate';
    public const HELP = 'help';
    public const MODIFY = 'modify';

    private const ATTRIBUTES = array(
        self::CREATE,
        self::DASHBOARD,
        self::DELETE,
        self::DUPLICATE,
        self::HELP,
        self::MODIFY,
    );

    public function __construct(AccessDecisionManagerInterface $decisionManager, string $roleNeeded)
    {
        $this->decisionManager = $decisionManager;
        $this->roleNeeded = $roleNeeded;
    }

    protected function supports($attribute, $subject)
    {
        if (null !== $subject) {
            return $subject instanceof Event && in_array($attribute, self::ATTRIBUTES);
        }

        return in_array($attribute, self::ATTRIBUTES);
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        //Defines access rights
        switch ($attribute) {
            case self::CREATE:
            case self::DASHBOARD:
            case self::DELETE:
            case self::DUPLICATE:
            case self::HELP:
            case self::MODIFY:
                return $this->decisionManager->decide($token, array($this->roleNeeded));
        }

        throw new \LogicException('Invalid attribute: ' . $attribute);
    }
}
