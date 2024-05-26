<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column(length: 255)]
    private string $username;

    #[ORM\Column(length: 255)]
    private string $password;

    #[ORM\Column(length: 255)]
    private string $email;

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    #[ORM\OneToMany(targetEntity: Task::class, mappedBy: "user")]
    private Collection $tasks;

    public function __construct()
    {
        $this->roles = ['ROLE_USER'];
        $this->tasks = new ArrayCollection();
    }

    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function setRoles(array $role) : self
    {
         // Check if chosen role is ROLE_ADMIN or ROLE_USER
        $role = $role[0] ?? null; // Fetch the first role or null

        // Define the role
        $this->roles = [$role];

        return $this;
    }

    public function eraseCredentials(): void
    {
        
    }

    public function getUserIdentifier(): string
    {
        return $this->username;
    }

    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function getTasks(): Collection
    {
        return $this->tasks;
    }

    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function addTask(Task $task): self
    {
        /** @psalm-suppress RedundantConditionGivenDocblockType */
        if (!$this->tasks->contains($task)) {
            $this->tasks[] = $task;
            $task->setUser($this);
        }
        return $this;
    }
}
