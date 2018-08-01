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

    public const DASHBOARD = 'dashboard';
    public const ADD = 'add';
    public const MODIFY = 'modify';
    public const DUPLICATE = 'duplicate';
    public const DELETE = 'delete';
    public const HELP = 'help';

    private const ATTRIBUTES = array(
        self::DASHBOARD,
        self::ADD,
        self::MODIFY,
        self::DUPLICATE,
        self::DELETE,
        self::HELP,
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
        $user = $token->getUser();

        //Defines access rights
        switch ($attribute) {
            case self::DASHBOARD:
            case self::ADD:
            case self::MODIFY:
            case self::DUPLICATE:
            case self::DELETE:
            case self::HELP:
                return $this->decisionManager->decide($token, array($this->roleNeeded));
        }

        throw new \LogicException('Invalid attribute: ' . $attribute);
    }
}