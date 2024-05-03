<?php

namespace App\DataFixtures;

use App\Entity\Task;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

/**
 * @psalm-suppress UnusedClass
 */
class DataFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Création de l'utilisateur admin
        $userAdmin = new User();
        $userAdmin->setUsername('testAdmin');
        $userAdmin->setEmail('userAdmin@example.com');
        $userAdmin->setRoles(['ROLE_ADMIN']);
        $userAdmin->setPassword(password_hash('123456', PASSWORD_BCRYPT));
        $manager->persist($userAdmin);

        // Création de l'utilisateur user
        $userUser = new User();
        $userUser->setUsername('testUser');
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

        // Création de plusieurs tâches
        for ($i = 0; $i < 10; $i++)
        {
            $task = new Task();
            $task->setTitle('title'.$i);
            $task->setContent('content'.$i);
            $task->setUser($userUser);
            $task->setCreatedAt(new DateTimeImmutable());
            $task->setIsDone(false);
        
            $manager->persist($task);
            $manager->flush();
        }
    }
}