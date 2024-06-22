<?php

// src/DataFixtures/UserFixtures.php
namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Faker\Factory;

class UserFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('fr_FR');

        for ($i = 0; $i < 10; $i++) {
            $user = new User();
            $user->setEmail($faker->email);
            $user->setLastname($faker->lastName);
            $user->setFirstname($faker->firstName);
            $user->setPassword($this->passwordHasher->hashPassword($user, 'password'));
            $manager->persist($user);

            $this->addReference('user_' . $i, $user);
        }

        $adminUser = new User();
        $adminUser->setEmail('admin@example.com');
        $adminUser->setLastname('Admin');
        $adminUser->setFirstname('Admin');
        $adminUser->setPassword($this->passwordHasher->hashPassword($adminUser, 'adminpassword'));
        $adminUser->setRoles(['ROLE_ADMIN']);
        $manager->persist($adminUser);

        $manager->flush();
    }
}
