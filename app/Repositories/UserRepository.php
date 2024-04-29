<?php

namespace App\Repositories;

use App\Interfaces\UserRepositoryInterface;
use App\Models\{User,UserDetails};
use Illuminate\Pagination\LengthAwarePaginator;
use App\Http\Resources\User\{
    UserCollection,
    UserResource
};
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Hash;

class UserRepository implements UserRepositoryInterface
{

    public function create(array $data): User
    {
        $data['password'] = Hash::make($data['password']);
        $user = User::create($data);
        if (isset($data['address'])) {
            $array = ['user_id' => $user->id , 'address' => $data['address']];
            $address = new UserDetails($array);
            $user->details()->save($address);
        }
        return $user;
    }

    public function update(User $user, array $data): User
    {
        $user->update($data);
        // Update the user details record if the address is provided
        if (isset($data['address'])) {
            $userDetails = $user->details;
            if (!$userDetails) {
                $array = ['user_id' => $user->id , 'address' => $data['address']];
                $address = new UserDetails($array);
                $user->details()->save($address);
            }else {
                $userDetails->fill(['address'=> $data['address']])->save();
            }

        }

        // Save the changes to the database
        //$user->save();
        return $user;
    }

    public function delete(User $user): void
    {
        //$user->delete();
        $existingUser = User::find($user->id);

        if ($existingUser) {
            // User exists, proceed with deletion
            $existingUser->delete();
        } else {
            // User not found, throw ModelNotFoundException
            throw new ModelNotFoundException('User not found');
        }
    }

    public function getAll(int $page = 1, int $perPage = 10)
    {
        return User::with('details')->paginate($perPage, ['*'], 'page', $page);
        // $users = User::with('details')->paginate(config('defaultsetting.pagination_limit'));

        // return $users;
    }
}
