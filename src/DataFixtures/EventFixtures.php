<?php

// src/DataFixtures/EventFixtures.php
namespace App\DataFixtures;

use App\Entity\Event;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Faker\Factory;

class EventFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('fr_FR');

        for ($i = 0; $i < rand(20, 50); $i++) {
            $event = new Event();
            $event->setTitle($faker->sentence(3));
            $event->setDescription($faker->paragraph);
            $event->setDate($faker->dateTimeBetween('+1 day', '+1 year'));
            $event->setNumberParticipants($faker->numberBetween(10, 100));
            $event->setPublic($faker->boolean);

            $userReference = $this->getReference('user_' . rand(0, 9));
            $event->setCreator($userReference);

            for ($j = 0; $j < rand(1, $event->getNumberParticipants()); $j++) {
                $participantReference = $this->getReference('user_' . rand(0, 9));
                $event->addParticipant($participantReference);
            }

            $manager->persist($event);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            UserFixtures::class,
        ];
    }
}
