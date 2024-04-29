<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\{Request,Response};
use App\Http\Requests\{StoreUserRequest,UpdateUserRequest,LoginRequest};
use App\Services\{UserService,ResponseService};
use Illuminate\Pagination\LengthAwarePaginator;
use App\Http\Resources\User\{
    UserCollection,
    UserResource
};
use Illuminate\Support\Facades\{
    DB,
    Log,
    Auth,
    Password,
    Http
};
use App\Models\User;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    private $userService;
    private $responseService;

    public function __construct(UserService $userService, ResponseService $responseService)
    {
        $this->middleware('auth:api', ['except' => ['store','index','login']]);
        $this->userService = $userService;
        $this->responseService = $responseService;
    }

    public function index(Request $request): JsonResponse
    {
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 10);
        try {
            $users = $this->userService->getAllUsers($page, $perPage);
            return $this->responseService->success($users, Response::HTTP_OK, 'User get successfully');
        }
        catch (Throwable $th) {
            Log::error($th);
            return $this->responseService->error($th->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY, []);
        }
    }
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $credentials = $request->only('email', 'password');
            $token = Auth::attempt($credentials);
            if (!$token) {
                return $this->responseService->error('Unauthorized', Response::HTTP_UNAUTHORIZED, []);
            }
            return $this->responseService->success(['token' => 'bearer '.$token], Response::HTTP_OK, 'User Login successfully');
        } catch (Throwable $th) {
            Log::error($th);
            return $this->responseService->error($th->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, []);
        }
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);
        $credentials = $request->only('email', 'password');

        $token = Auth::attempt($credentials);
        if (!$token) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 401);
        }

        $user = Auth::user();
        return response()->json([
                'status' => 'success',
                'user' => $user,
                'authorisation' => [
                    'token' => $token,
                    'type' => 'bearer',
                ]
            ]);

    }
    public function store(StoreUserRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $validatedData = $request->validated();

            $user = $this->userService->createUser($validatedData);
            $token = Auth::login($user);
            DB::commit();
            return $this->responseService->success(['user' => new UserResource($user), 'token' => 'bearer '.$token], Response::HTTP_CREATED, 'User created successfully');


        } catch (Throwable $th) {
            DB::rollBack();
            Log::error($th);
            return $this->responseService->error($th->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY, []);
        }

    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        DB::beginTransaction();
        try {
            $validatedData = $request->validated();
            $user = $this->userService->updateUser($user, $validatedData);
            DB::commit();
            return $this->responseService->success(['user' => new UserResource($user)], Response::HTTP_OK, 'User updated successfully');
        } catch (Throwable $th) {
            DB::rollBack();
            Log::error($th);
            return $this->responseService->error($th->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY, []);
        }
    }

    public function destroy(User $user): object
    {
        {
            DB::beginTransaction();
            try {
                $this->userService->deleteUser($user);
                DB::commit();
                return $this->responseService->success(null, Response::HTTP_OK, 'User deleted successfully');
            } catch (Throwable $th) {
                DB::rollBack();
                Log::error($th);
                return $this->responseService->error($th->getMessage(), Response::HTTP_NOT_FOUND, [$th->getMessage()]);
            }
        }
    }
}
