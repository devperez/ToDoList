<?php

namespace App\tests\Unit\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;


class UserControllerTest extends WebTestCase
{
    public function testCreateAction()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        $crawler = $client->request('GET', '/users/create');

        $form = $crawler->selectButton('Ajouter')->form();
        $form['user[username]'] = 'boby';
        $form['user[password][first]'] = 'azerty';
        $form['user[password][second]'] = 'azerty';
        $form['user[email]'] = 'newUser@example.org';

        $client->submit($form);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('div.alert-success', 'L\'utilisateur a bien été ajouté.');

        $user = $userRepository->findOneBy(['email' => 'newUser@example.org']);
        $this->assertNotNull($user);
    }

    protected function tearDown(): void
    {
        $em = self::$kernel->getContainer()->get('doctrine')->getManager();
        $em->getConnection()->executeQuery('DELETE FROM `task`');
        $em->getConnection()->executeQuery('DELETE FROM `user`');
        self::ensureKernelShutdown();
    }
}