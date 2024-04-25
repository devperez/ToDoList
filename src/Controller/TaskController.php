<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\TaskType;
use DateTimeImmutable;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @psalm-suppress UnusedClass
 */
class TaskController extends AbstractController
{
    #[Route('/tasks', name: 'task_list')]
    public function listAction(TaskRepository $taskRepository): Response
    {
        $tasks = $taskRepository->findAll();
        return $this->render('task/list.html.twig', compact('tasks'));
    }

    #[Route('/tasks/create', name: 'task_create')]
    public function createAction(Request $request, EntityManagerInterface $emi, UserRepository $userRepository): Response
    {
        $user = $this->getUser();
        if(!$user){
            $user = $userRepository->findOneByUsername('userAnonymous');
        }
        $task = new Task();
        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) 
        {
            $task->setCreatedAt(new DateTimeImmutable());
            $task->setIsDone(0);
            $task->setUser($user);
            $emi->persist($task);
            $emi->flush();

            $this->addFlash('success', 'La tâche a bien été ajoutée.');

            return $this->redirectToRoute('task_list');
        }

        return $this->render('task/create.html.twig', ['form' => $form->createView()]);
    }

    #[Route('/tasks/{id}/edit', name: 'task_edit')]
    public function editAction(Task $task, Request $request, EntityManagerInterface $emi): Response
    {
        $user = $this->getUser();
        $taskOwner = $task->getUser();

        if($taskOwner !== $user && !$this->isGranted('ROLE_ADMIN'))
        {
            throw $this->createAccessDeniedException('Vous n\'êtes pas le propriétaire de cette tâche.');
        }
        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $emi->flush();

            $this->addFlash('success', 'La tâche a bien été modifiée.');

            return $this->redirectToRoute('task_list');
        }

        return $this->render('task/edit.html.twig', [
            'form' => $form->createView(),
            'task' => $task,
        ]);
    }

    #[Route('/tasks/{id}/toggle', name: 'task_toggle')]
    public function toggleTaskAction(Task $task, EntityManagerInterface $emi): Response
    {
        $task->setIsDone(!$task->isDone());
        $emi->flush();

        $this->addFlash('success', 'La tâche a bien été mise à jour.');

        return $this->redirectToRoute('task_list');
    }

    #[Route('/tasks/{id}/delete', name: 'task_delete')]
    public function deleteTaskAction(Task $task, EntityManagerInterface $emi): Response
    {
        $emi->remove($task);
        $emi->flush();

        $this->addFlash('success', 'La tâche a bien été supprimée.');

        return $this->redirectToRoute('task_list');
    }
}
