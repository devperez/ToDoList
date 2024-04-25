<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @psalm-suppress UnusedClass
 */
class UserController extends AbstractController
{
    #[Route('/users', name: 'user_list')]
    #[IsGranted('ROLE_ADMIN')]
    public function list_Action(Request $request, PaginatorInterface $paginator, UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();
        // Results pagination
        $pagination = $paginator->paginate(
            $users,
            $request->query->getInt('page', 1), // Page number
            10 // Number of elements per page
        );
        return $this->render('user/list.html.twig', compact('pagination'));
    }

    #[Route('/users/create', name: 'user_create')]
    public function createAction(Request $request, EntityManagerInterface $emi, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $password = $passwordHasher->hashPassword($user, $user->getPassword());
            $user->setPassword($password);

            $emi->persist($user);
            $emi->flush();

            $this->addFlash('success', "L'utilisateur a bien été ajouté.");
        }

        return $this->render('user/create.html.twig', ['form' => $form->createView()]);
    }

    #[Route('/users/{id}/edit', name: 'user_edit')]
    public function editAction(User $user, Request $request, EntityManagerInterface $emi, UserPasswordHasherInterface $passwordHasher): Response
    {
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $passwordHasher->hashPassword($user, $user->getPassword());
            $user->setPassword($password);

            $emi->flush();

            $this->addFlash('success', "L'utilisateur a bien été modifié");

            return $this->redirectToRoute('user_list');
        }

        return $this->render('user/edit.html.twig', ['form' => $form->createView(), 'user' => $user]);
    }
}
