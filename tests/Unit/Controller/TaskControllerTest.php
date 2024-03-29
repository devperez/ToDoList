<?php

namespace App\tests\Unit\Controller;

use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use Exception;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TaskControllerTest extends WebTestCase
{
    private $client;

    protected function setUp(): void
    {
        $this->client = self::createClient();
    }

    public function loginWithUser(): void
    {
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Se connecter')->form();
        $this->client->submit($form, ['_username' => 'testUser', '_password' => '123456']);
    }

    public function testCreateAction()
    {
        $taskRepository = static::getContainer()->get(TaskRepository::class);

        $this->client->request('GET', '/tasks/create');

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
        $user = $this->loginWithUser();
        if(!$user)
        {
            throw new Exception('Aucun utilisateur connecté');
        }

        // Récupère une tâche existante pour l'édition
        $task = $taskRepository->findOneBy(['user_id' => $user->getId()]);
        
        // Simulation d'une requête GET pour afficher le formulaire d'édition
        $this->client->request('GET', '/tasks/' . $task->getId() . '/edit');

        // Sélectionne le formulaire d'édition et soumet les données modifiées
        $this->client->submitForm('submit', [
            'task[title]' => 'Nouveau titre',
            'task[content]' => 'Nouveau contenu',
        ]);

        $this->assertResponseIsSuccessful();

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
