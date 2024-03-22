<?php

namespace App\tests\Unit\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Controller\UserController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Test\FormInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


class UserControllerTest extends KernelTestCase
{
    public function testCreateAction()
    {
        self::bootKernel();

        // Création d'un mock pour EntityManagerInterface
        $entityManager = $this->createMock(EntityManagerInterface::class);
        
        // Création d'un mock pour UserPasswordHasherInterface
        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        
        // Création d'un mock pour FormFactoryInterface
        $formFactory = $this->createMock(FormFactoryInterface::class);
        $form = $this->createMock(FormInterface::class);
        $form->method('handleRequest')->willReturnSelf();
        $form->method('isSubmitted')->willReturn(true);
        $form->method('isValid')->willReturn(true);
        $formFactory->method('create')->willReturn($form);
        
        // Création d'un mock pour FlashBagInterface
        $flashBag = $this->createMock(FlashBagInterface::class);
        
        // Création d'un mock pour Request
        $request = new Request();
        
        // Création d'un UserController avec les mocks en dépendances
        $userController = new UserController();
        $userController->setContainer(self::$kernel->getContainer());
        
        // Exécuter la fonction createAction avec les mocks
        $response = $userController->createAction($request, $entityManager, $passwordHasher, $formFactory, $flashBag);
        
        // Assertions
        $this->assertInstanceOf(Response::class, $response);
        $this->assertStringContainsString('user/create.html.twig', $response->getContent());
    }
}