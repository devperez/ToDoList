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
        // Création des utilisateurs
        $this->createUsers();
        // On récupère la page
        $this->client->request('GET', '/users');
        // On vérifie que la réponse est bonne
        $this->assertResponseIsSuccessful();
        // On vérifie qu'on a bien une instance de Response
        $this->assertInstanceOf(Response::class, $this->client->getResponse());
        // On vérirfie que la vue 'user/list.html.twig' est bien rendue
        $this->assertSelectorTextContains('h1', 'Liste des utilisateurs');
    }

    public function testCreateAction()
    {
        $userRepository = static::getContainer()->get(UserRepository::class);
        //On fait une requête GET sur la page du formulaire
        $crawler = $this->client->request('GET', '/users/create');
        //On soumet le formulaire avec des données
        $form = $crawler->selectButton('Ajouter')->form();
        $form['user[username]'] = 'boby';
        $form['user[password][first]'] = 'azerty';
        $form['user[password][second]'] = 'azerty';
        $form['user[email]'] = 'newUser@example.org';

        $this->client->submit($form);
        // On vérifie que la réponse est ok
        $this->assertResponseIsSuccessful();
        // On vérifie l'affichage du message de succès
        $this->assertSelectorTextContains('div.alert-success', 'L\'utilisateur a bien été ajouté.');
        // On vérifie l'insertion en BDD
        $user = $userRepository->findOneBy(['email' => 'newUser@example.org']);
        $this->assertNotNull($user);
    }

    public function testEditAction()
    {
        // On crée des utilisateurs
        $this->createUsers();
        // On récupère l'utilisateur userAnonymous
        $userRepository = static::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneBy(['email' => 'userAnonymous@example.com']);
        if (!$user) {
            throw new Exception('Utilisateur non trouvé.');
        }
        // On fait une requête vers la page d'édition
        $this->client->request('GET', '/users/' . $user->getId() . '/edit');
        // On vérifie que la réponse est ok
        $this->assertResponseIsSuccessful();
        // On soumet le formulaire avec des données
        $this->client->submitForm('Modifier', [
            'user[email]' => 'nouveau@email.com',
            'user[password][first]' => '123456',
            'user[password][second]' => '123456',
        ]);
        // On vérifie qu'il y a bien une redirection
        $this->assertTrue($this->client->getResponse()->isRedirect());
        // On suit la redirection
        $this->client->followRedirect();
        // On vérifie que la route est bien 'user_list'
        $this->assertEquals('user_list', $this->client->getRequest()->attributes->get('_route'));
        // On récupère l'utilisateur mis à jour
        $userUpdated = $userRepository->find($user->getId());
        // On vérifie que son mail a bien été mis à jour
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