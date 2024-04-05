<?php

namespace App\tests\Unit\Controller;

use Exception;
use App\Entity\Task;
use App\Entity\User;
use DateTimeImmutable;
use App\Controller\TaskController;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorage;

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
        return [$user, $task];
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
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();

        // Vérifie que la tâche a été modifiée dans la base de données
        $modifiedTask = $taskRepository->find($task->getId());
        $this->assertEquals('Nouveau titre', $modifiedTask->getTitle());
        $this->assertEquals('Nouveau contenu', $modifiedTask->getContent());
    }

    public function testToggleTaskAction()
    {
        $taskRepository = static::getContainer()->get(TaskRepository::class);
        $userRepository = static::getContainer()->get(UserRepository::class);

        [,$task] = $this->createUsersAndTasks();
        
        $user = $userRepository->findOneBy(['username' => 'testUser']);
        $this->client->loginUser($user);
        // On crée une instance du contrôleur
        $controller = static::getContainer()->get(TaskController::class);
        if(!$controller)
        {
            throw new Exception('Contrôleur non trouvé.');
        }
        // On récupère une tâche
        $task = $taskRepository->findOneBy(['content' => 'content2']);
        if(!$task)
        {
            throw new Exception('Tâche non trouvée.');
        }
        //On appelle la méthode
        $response = $controller->toggleTaskAction($task, $this->manager);
        if(!$response)
        {
            throw new Exception('Le contrôleur n\'est pas appelé correctement.');
        }
        // On vérifie si la tâche est marquée comme faite
        $this->assertTrue($task->isDone());

        // On vérifie la redirection vers la route 'task_list'

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('task_list', $this->client->getRequest()->attributes->get('_route'));
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
