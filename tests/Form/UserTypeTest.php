<?php
namespace App\Tests\Form;

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
        $form['user[username]'] = 'UnitTest';

        $client->submit($form);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('div.alert-success', 'L\'utilisateur a bien été ajouté.');
    }
}