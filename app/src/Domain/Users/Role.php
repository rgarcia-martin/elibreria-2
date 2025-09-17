<?php
// src/Domain/Users/Role.php
declare(strict_types=1);

namespace App\Domain\Users;

enum Role: string { case ADMIN='ROLE_ADMIN'; case CASHIER='ROLE_CASHIER'; case MANAGER='ROLE_MANAGER'; }
