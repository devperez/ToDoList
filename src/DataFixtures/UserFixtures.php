<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\User;

class UserFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        // Création de l'utilisateur admin
        $userAdmin = new User();
        $userAdmin->setUsername('userAdmin');
        $userAdmin->setEmail('userAdmin@example.com');
        $userAdmin->setRoles(['ROLE_ADMIN']);
        $userAdmin->setPassword(password_hash('123456', PASSWORD_BCRYPT));
        $manager->persist($userAdmin);

        // Création de l'utilisateur user
        $userUser = new User();
        $userUser->setUsername('userUser');
        $userUser->setEmail('userUser@example.com');
        $userUser->setRoles(['ROLE_USER']);
        $userUser->setPassword(password_hash('123456', PASSWORD_BCRYPT));
        $manager->persist($userUser);

        // Création de l'utilisateur anonyme
        $userAnonymous = new User();
        $userAnonymous->setUsername('userAnonymous');
        $userAnonymous->setEmail('userAnonymous@example.com');
        $userAnonymous->setRoles(['ROLE_USER']);
        $userAnonymous->setPassword(password_hash('123456', PASSWORD_BCRYPT));
        $manager->persist($userAnonymous);

        $manager->flush();
    }
}