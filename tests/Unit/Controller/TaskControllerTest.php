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
        //$userRepository = static::getContainer()->get(UserRepository::class);

        //$testUser = $userRepository->findOneByUsername('userAnonymous');
        //$client->loginUser($testUser);

        $crawler = $client->request('GET', '/tasks/create');

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
}