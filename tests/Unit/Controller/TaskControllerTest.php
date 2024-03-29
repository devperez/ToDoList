<?php

namespace App\tests\Unit\Controller;

use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TaskControllerTest extends WebTestCase
{
    public function testCreateAction()
    {
        $client = static::createClient();
        $taskRepository = static::getContainer()->get(TaskRepository::class);

        $client->request('GET', '/tasks/create');

        $client->submitForm('Ajouter', [
            'task[title]' => 'Title',
            'task[content]' => 'Content',
        ]);
        $client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('div.alert-success', 'La tâche a bien été ajoutée.');

        $task = $taskRepository->findOneBy(['content' => 'Content']);
        $this->assertNotNull($task);
    }

    public function testEditAction()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $taskRepository = static::getContainer()->get(TaskRepository::class);

        // Simulation d'un utilisateur connecté
        $testUser = $userRepository->findOneBy(['username' => 'userUser']);
        if (!$testUser) {
            throw new \Exception("L'utilisateur n'a pas été trouvé dans la base de données de test.");
        }
        $client->loginUser($testUser);

        // Récupère une tâche existante pour l'édition
        $task = $taskRepository->findOneBy(['user' => $testUser]);

        // Simulation d'une requête GET pour afficher le formulaire d'édition
        $client->request('GET', '/tasks/' . $task->getId() . '/edit');

        // Sélectionne le formulaire d'édition et soumet les données modifiées
        $client->submitForm('submit', [
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