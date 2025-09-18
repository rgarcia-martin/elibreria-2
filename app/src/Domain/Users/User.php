<?php
// src/Domain/Users/User.php
declare(strict_types=1);

namespace App\Domain\Users;

class User
{
    /** @param Role[] $roles */
    public function __construct(
        private UserId $id,
        private string $email,
        private string $passwordHash,
        private array $roles = [Role::CASHIER]
    ) {}

    public function id(): UserId { return $this->id; }
    public function email(): string { return $this->email; }
    /** @return Role[] */ public function roles(): array { return $this->roles; }
    public function grant(Role $r): void { if(!in_array($r,$this->roles,true)) $this->roles[]=$r; }
    public function revoke(Role $r): void { $this->roles = array_values(array_filter($this->roles,fn($x)=>$x!==$r)); }
}
