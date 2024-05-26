<?php

namespace App\Tests\integration\Form;

use App\Entity\Task;
use App\Entity\User;
use PHPUnit\Framework\TestCase;
use Doctrine\Common\Collections\ArrayCollection;

class UserTypeTest extends TestCase
{
    public function testSetRolesWithValidRoleAdmin()
    {
        // Create user and give him ROLE_ADMIN
        $user = new User();
        $user->setRoles(['ROLE_ADMIN']);
        $this->assertEquals(['ROLE_ADMIN'], $user->getRoles());
    }

    public function testSetRolesWithValidRoleUser()
    {
        // Create a user and give him ROLE_USER
        $user = new User();
        $user->setRoles(['ROLE_USER']);
        $this->assertEquals(['ROLE_USER'], $user->getRoles());
    }

    public function testAddTask()
    {
        $user = new User();
        $task = new Task();

        // Initial test : User has no task
        $this->assertCount(0, $user->getTasks());

        // Adds a task
        $user->addTask($task);

        // Check a task has been added
        $this->assertCount(1, $user->getTasks());
        $this->assertSame($task, $user->getTasks()[0]);
        $this->assertSame($user, $task->getUser());
    }

    public function testAddTaskTwice()
    {
        $user = new User();
        $task = new Task();

        // Add a task
        $user->addTask($task);

        // Add  same task a second time
        $user->addTask($task);

        // Check task is not added twice
        $this->assertCount(1, $user->getTasks());
    }

    public function testGetTasksAfterAddingTask()
    {
        $user = new User();
        $task = new Task();
        $user->addTask($task);

        // Check if collection contains a task after adding one
        $this->assertCount(1, $user->getTasks());
        $this->assertSame($task, $user->getTasks()->first());
    }
}
