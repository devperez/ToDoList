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

    public function createUsersAndTasks(): array
    {
        $user = new User();
        $user->setUsername('testUser');
        $user->setEmail('userUser@example.com');
        $user->setRoles(['ROLE_USER']);
        $user->setPassword(password_hash('123456', PASSWORD_BCRYPT));
        $this->manager->persist($user);

        $user2 = new User();
        $user2->setUsername('testUser2');
        $user2->setEmail('userUser2@example.com');
        $user2->setRoles(['ROLE_USER']);
        $user2->setPassword(password_hash('123456', PASSWORD_BCRYPT));
        $this->manager->persist($user2);

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

        $task2 = new Task();
        $task2->setTitle('title2');
        $task2->setContent('content2');
        $task2->setUser($user);
        $task2->setCreatedAt(new DateTimeImmutable());
        $task2->setIsDone(0);
        $this->manager->persist($task2);

        $this->manager->flush();
        return [$user2, $task];
    }

    public function testCreateAction()
    {
        $taskRepository = static::getContainer()->get(TaskRepository::class);

        $this->client->request('GET', '/tasks/create');
        $this->createUsersAndTasks();
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
        $this->createUsersAndTasks();
        $user = $userRepository->findOneBy(['username' => 'testUser']);
        $this->client->loginUser($user);
        if (!$user) {
            throw new Exception('Aucun utilisateur connecté');
        }

        // Fetch an existing task to edit
        $task = $taskRepository->findOneBy(['user' => $user->getId()]);

        // Mock a GET request to display the edit form
        $this->client->request('GET', '/tasks/' . $task->getId() . '/edit');

        // Select the edit form and submits modified data
        $this->client->submitForm('Modifier', [
            'task[title]' => 'Nouveau titre',
            'task[content]' => 'Nouveau contenu',
        ]);
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();

        // Check the task has been modified in DB
        $modifiedTask = $taskRepository->find($task->getId());
        $this->assertEquals('Nouveau titre', $modifiedTask->getTitle());
        $this->assertEquals('Nouveau contenu', $modifiedTask->getContent());
    }

    public function testEditActionUnauthorized()
    {
        [$user2,$task] = $this->createUsersAndTasks();
        // Log in user2
        $this->client->loginUser($user2);
        // Mock a GET request to display the edit form
        $this->client->request('GET', '/tasks/' . $task->getId() . '/edit');
        // Assert we get a 403 response
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testToggleTaskAction()
    {
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        [,$task] = $this->createUsersAndTasks();

        // Mock a request on the toggleTaskAction URL with the task id
        $this->client->request('GET', '/tasks/' . $task->getId() . '/toggle');

        // Check the response is a redirection towards the task list
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();

        // Reload the task from the database
        $task = $entityManager->getRepository(Task::class)->find($task->getId());

        // Check the task is now set as done
        $this->assertTrue($task->isDone());

        $this->assertSelectorTextContains('div.alert-success', 'La tâche a bien été mise à jour.');
    }

    public function testDeleteTaskAction()
    {
        $taskRepository = static::getContainer()->get(TaskRepository::class);
        $this->createUsersAndTasks();
        $task = $taskRepository->findOneBy(['content' => 'content2']);
        if (!$task) {
            throw new Exception('Tâche non trouvée.');
        }
        $taskId = $task->getId();
        $this->client->request('DELETE', '/tasks/' . $taskId . '/delete');
        $this->client->followRedirect();

        $this->assertNull($taskRepository->find($taskId));
        $this->assertEquals('task_list', $this->client->getRequest()->attributes->get('_route'));
    }

    protected function tearDown(): void
    {
        $em = self::$kernel->getContainer()->get('doctrine')->getManager();
        $em->getConnection()->executeQuery('DELETE FROM `task`');
        $em->getConnection()->executeQuery('DELETE FROM `user`');
        self::ensureKernelShutdown();
    }
}
