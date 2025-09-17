<?php
// src/Domain/Users/UserRepository.php
declare(strict_types=1);

namespace App\Domain\Users;

interface UserRepository
{
    public function byId(UserId $id): ?User;
    public function byEmail(string $email): ?User;
    public function save(User $u): void;
}
