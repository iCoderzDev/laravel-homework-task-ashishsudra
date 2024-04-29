<?php

namespace App\Services;

use App\Interfaces\UserRepositoryInterface;
use App\Interfaces\UserServiceInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Http\Resources\User\{
    UserCollection,
    UserResource
};
class UserService implements UserServiceInterface
{
    private $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function createUser(array $data): User

    {
        return $this->userRepository->create($data);
    }

    public function updateUser(User $user, array $data): User
    {
        return $this->userRepository->update($user, $data);
    }

    public function deleteUser(User $user): void
    {
        //$this->userRepository->delete($user);
        try {
            // Attempt to delete the user via UserRepository
            $this->userRepository->delete($user);
        } catch (ModelNotFoundException $e) {
            // Catch ModelNotFoundException and convert to NotFoundHttpException
            throw new NotFoundHttpException('User not found', $e);
        }
    }

    public function getAllUsers(int $page = 1, int $perPage = 10): UserCollection
    {
        return new UserCollection($this->userRepository->getAll($page, $perPage));
    }
}
