<?php

namespace App\tests\Unit\Controller;

use Exception;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;



class UserControllerTest extends WebTestCase
{
    private $client;
    private $manager;

    protected function setUp(): void
    {
        $this->client = self::createClient();
        $this->manager = static::getContainer()->get(EntityManagerInterface::class);
    }

    public function createUsers()
    {
        $userAdmin = new User();
        $userAdmin->setUsername('userAdmin');
        $userAdmin->setEmail('userAdmin@exemple.com');
        $userAdmin->setRoles(['ROLE_ADMIN']);
        $userAdmin->setPassword(password_hash('123456', PASSWORD_BCRYPT));
        $this->manager->persist($userAdmin);

        $userAnonymous = new User();
        $userAnonymous->setUsername('userAnonymous');
        $userAnonymous->setEmail('userAnonymous@example.com');
        $userAnonymous->setRoles(['ROLE_USER']);
        $userAnonymous->setPassword(password_hash('123456', PASSWORD_BCRYPT));
        $this->manager->persist($userAnonymous);
        $this->manager->flush();
    }

    public function testListAction()
    {
        // Create users
        $this->createUsers();
        // Fetch the page
        $this->client->request('GET', '/users');
        // Check the response is ok
        $this->assertResponseIsSuccessful();
        // Check we have a response instance
        $this->assertInstanceOf(Response::class, $this->client->getResponse());
        // Check the view 'user/list.html.twig' is rendered
        $this->assertSelectorTextContains('h1', 'Liste des utilisateurs');
    }

    public function testCreateAction()
    {
        $userRepository = static::getContainer()->get(UserRepository::class);
        // Mock a GET request on the form page
        $crawler = $this->client->request('GET', '/users/create');
        // Submit the form with data
        $form = $crawler->selectButton('Ajouter')->form();
        $form['user[username]'] = 'boby';
        $form['user[password][first]'] = 'azerty';
        $form['user[password][second]'] = 'azerty';
        $form['user[email]'] = 'newUser@example.org';

        $this->client->submit($form);
        // Check the response is ok
        $this->assertResponseIsSuccessful();
        // Check the display of the flash message
        $this->assertSelectorTextContains('div.alert-success', 'L\'utilisateur a bien été ajouté.');
        // Check the DB storage
        $user = $userRepository->findOneBy(['email' => 'newUser@example.org']);
        $this->assertNotNull($user);
    }

    public function testEditAction()
    {
        // Create users
        $this->createUsers();
        // Fetch the userAnonymous user
        $userRepository = static::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneBy(['email' => 'userAnonymous@example.com']);
        if (!$user) {
            throw new Exception('Utilisateur non trouvé.');
        }
        // Mock a GET request on the edit page
        $this->client->request('GET', '/users/' . $user->getId() . '/edit');
        // Check the response is ok
        $this->assertResponseIsSuccessful();
        // Submit the form with data
        $this->client->submitForm('Modifier', [
            'user[email]' => 'nouveau@email.com',
            'user[password][first]' => '123456',
            'user[password][second]' => '123456',
        ]);
        // Check we are redirected
        $this->assertTrue($this->client->getResponse()->isRedirect());
        // Follow redirection
        $this->client->followRedirect();
        // Check the route is 'user_list'
        $this->assertEquals('user_list', $this->client->getRequest()->attributes->get('_route'));
        // Fetch the updated user
        $userUpdated = $userRepository->find($user->getId());
        // Check his mail was updated
        $this->assertEquals('nouveau@email.com', $userUpdated->getEmail());
    }

    protected function tearDown(): void
    {
        $em = self::$kernel->getContainer()->get('doctrine')->getManager();
        $em->getConnection()->executeQuery('DELETE FROM `task`');
        $em->getConnection()->executeQuery('DELETE FROM `user`');
        self::ensureKernelShutdown();
    }
}