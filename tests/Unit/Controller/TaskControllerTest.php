<?php

namespace App\tests\Unit\Controller;

use Exception;
use App\Entity\Task;
use App\Entity\User;
use DateTimeImmutable;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TaskControllerTest extends WebTestCase
{
    private $client;
    private $manager;

    protected function setUp(): void
    {
        $this->client = self::createClient();
        $this->manager = static::getContainer()->get(EntityManagerInterface::class);
    }

    public function createUserAndTask(): void
    {
        $user = new User();
        $user->setUsername('testUser');
        $user->setEmail('userUser@example.com');
        $user->setRoles(['ROLE_USER']);
        $user->setPassword(password_hash('123456', PASSWORD_BCRYPT));
        $this->manager->persist($user);

        $userAnonymous = new User();
        $userAnonymous->setUsername('userAnonymous');
        $userAnonymous->setEmail('userAnonymous@example.com');
        $userAnonymous->setRoles(['ROLE_USER']);
        $userAnonymous->setPassword(password_hash('123456', PASSWORD_BCRYPT));
        $this->manager->persist($userAnonymous);

        $task = new Task();
        $task->setTitle('title');
        $task->setContent('content');
        $task->setUser($user);
        $task->setCreatedAt(new DateTimeImmutable());
        $task->setIsDone(0);
        $this->manager->persist($task);

        $this->manager->flush();
    }

    public function testCreateAction()
    {
        $taskRepository = static::getContainer()->get(TaskRepository::class);

        $this->client->request('GET', '/tasks/create');
        $this->createUserAndTask();
        $this->client->submitForm('Ajouter', [
            'task[title]' => 'Title',
            'task[content]' => 'Content',
        ]);
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('div.alert-success', 'La tâche a bien été ajoutée.');

        $task = $taskRepository->findOneBy(['content' => 'Content']);
        $this->assertNotNull($task);
    }

    public function testEditAction()
    {
        $taskRepository = static::getContainer()->get(TaskRepository::class);
        $userRepository = static::getContainer()->get(UserRepository::class);
        $this->createUserAndTask();
        $user = $userRepository->findOneBy(['username' => 'testUser']);
        $this->client->loginUser($user);
        if(!$user)
        {
            throw new Exception('Aucun utilisateur connecté');
        }

        // Récupère une tâche existante pour l'édition
        $task = $taskRepository->findOneBy(['user' => $user->getId()]);
        
        // Simulation d'une requête GET pour afficher le formulaire d'édition
        $this->client->request('GET', '/tasks/' . $task->getId() . '/edit');

        // Sélectionne le formulaire d'édition et soumet les données modifiées
        $this->client->submitForm('Modifier', [
            'task[title]' => 'Nouveau titre',
            'task[content]' => 'Nouveau contenu',
        ]);

        //$this->assertResponseIsSuccessful();

        // Vérifie que la tâche a été modifiée dans la base de données
        $modifiedTask = $taskRepository->find($task->getId());
        $this->assertEquals('Nouveau titre', $modifiedTask->getTitle());
        $this->assertEquals('Nouveau contenu', $modifiedTask->getContent());
    }

    protected function tearDown(): void
    {
        $em = self::$kernel->getContainer()->get('doctrine')->getManager();
        $em->getConnection()->executeQuery('DELETE FROM `task`');
        $em->getConnection()->executeQuery('DELETE FROM `user`');
        self::ensureKernelShutdown();
    }
}
