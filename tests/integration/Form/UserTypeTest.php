<?php
namespace App\Tests\integration\Form;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserTypeTest extends WebTestCase
{
    public function testSubmitForm(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/users/create');

        $form = $crawler->selectButton('Ajouter')->form();
        $form['user[email]'] = 'test@example.com';
        $form['user[password][first]'] = 'password';
        $form['user[password][second]'] = 'password';
        $form['user[username]'] = 'Test';

        $client->submit($form);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('div.alert-success', 'L\'utilisateur a bien été ajouté.');
    }

    protected function tearDown(): void
    {
        $em = self::$kernel->getContainer()->get('doctrine')->getManager();
        $em->getConnection()->executeQuery('DELETE FROM `task`');
        $em->getConnection()->executeQuery('DELETE FROM `user`');
        self::ensureKernelShutdown();
    }
}