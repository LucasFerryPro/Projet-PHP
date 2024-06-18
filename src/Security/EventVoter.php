<?php

namespace App\Security;

use App\Entity\Event;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class EventVoter extends Voter
{
    const VIEW = 'view';
    const CREATE = 'create';
    const EDIT_OR_DELETE = 'edit_or_delete';
//    const IS_PARTICIPANT = 'is_participant';


    protected function supports(string $attribute, mixed $subject): bool
    {

        if(!in_array($attribute, [self::VIEW, self::CREATE, self::EDIT_OR_DELETE /*, self::IS_PARTICIPANT*/])) {
            return false;
        }

        if(!$subject instanceof Event){
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        // TODO: Implement voteOnAttribute() method.
        $user = $token->getUser();

        /** @var Event $event */
        $event = $subject;

        return match ($attribute) {
            self::VIEW => $this->canView($event, $user),
            self::CREATE => $this->canCreate($user),
            self::EDIT_OR_DELETE => $this->canEditOrDelete($event, $user),
            default => throw new \LogicException('This code should not be reached!')
        };
    }

    private function canView(Event $event, ?User $user): bool
    {


        if (!$user instanceof User) {
            return $event->isPublic();
        }
        else
        {
            return true;
        }
    }

    private function canCreate(?User $user): bool
    {
        return $user instanceof User;
    }

    private function canEditOrDelete(Event $event, ?User $user): bool
    {
        if (!$user instanceof User) {
            return false;
        }else{
            return $event->getCreator() === $user;
        }
    }
}
