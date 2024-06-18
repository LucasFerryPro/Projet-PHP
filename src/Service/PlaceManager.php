<?php

namespace App\Service;

use App\Entity\Event;
use App\Entity\User;

class PlaceManager
{

    public function getNumberOfRegistration(Event $event): int
    {
        return count($event->getParticipants());
    }

    public function getRemainingPlaces(Event $event): int
    {
        return $event->getNumberParticipants() - count($event->getParticipants());
    }

    public function canRegister(Event $event): bool
    {
        return $this->getRemainingPlaces($event) > 0;
    }


}