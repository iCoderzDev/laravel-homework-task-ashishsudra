<?php

namespace App\Interfaces;

use App\Models\User;


interface UserRepositoryInterface
{
    public function create(array $data): User;
    public function update(User $user, array $data): User;
    public function delete(User $user): void;
    public function getAll(int $page = 1, int $perPage = 10);

}
