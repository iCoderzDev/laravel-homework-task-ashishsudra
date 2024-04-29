<?php

namespace App\Interfaces;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Http\Resources\User\{
    UserCollection,
    UserResource
};

interface UserServiceInterface
{
    public function createUser(array $data): User;
    public function updateUser(User $user, array $data): User;
    public function deleteUser(User $user): void;
    public function getAllUsers(int $page = 1, int $perPage = 10): UserCollection;

}
