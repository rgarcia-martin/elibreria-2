<?php
// src/Infrastructure/Persistence/Doctrine/Entity/DoctrineUser.php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'users')]
class DoctrineUser
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    public string $id;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    public string $email;

    #[ORM\Column(type: 'string', length: 255)]
    public string $password_hash;

    #[ORM\Column(type: 'json')]
    public array $roles = [];
}
