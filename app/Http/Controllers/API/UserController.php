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

/**
 * @OA\Info(
 *     title="User Management API",
 *     version="1.0.0",
 *     description="API endpoints for user management",
 *     @OA\Contact(
 *         email="admin@example.com",
 *         name="Admin"
 *     ),
 *     @OA\License(
 *         name="Apache 2.0",
 *         url="http://www.apache.org/licenses/LICENSE-2.0.html"
 *     )
 * )
 */

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
    /**
     * @OA\Get(
     *     path="/api/users",
     *     summary="Get all users",
     *     tags={"Users"},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Users per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="string", example="200"),
     *             @OA\Property(property="success", type="boolean", example="true"),
     *             @OA\Property(property="message", type="string", example="User get successfully"),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(ref="#/components/schemas/UserCollection")
     *             ),
     *             @OA\Property(property="total", type="integer", example="10"),
     *             @OA\Property(property="per_page", type="integer", example="10"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Unprocessable Entity",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
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
     /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="Authenticate user and generate token",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="User credentials",
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful login",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="user", type="object", ref="#/components/schemas/User"),
     *             @OA\Property(property="authorisation", type="object",
     *                 @OA\Property(property="token", type="string", example="Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiaWF0IjoxNTE2MjM5MDIyfQ.SflKxwRJSMeKKF2QT4fwpMeJf36POk6yJV_adQssw5c"),
     *                 @OA\Property(property="type", type="string", example="bearer")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
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

    /**
     * @OA\Post(
     *     path="/api/users",
     *     summary="Create a new user",
     *     tags={"Users"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="User data",
     *         @OA\JsonContent(
     *             required={"first_name", "last_name", "email", "password"},
     *             @OA\Property(property="first_name", type="string", example="John"),
     *             @OA\Property(property="last_name", type="string", example="Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="johndoe@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="user", ref="#/components/schemas/UserResource"),
     *             @OA\Property(property="token", type="string", example="bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Unprocessable Entity",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */

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

    /**
     * Update an existing user.
     *
     * @OA\Put(
     *     path="/api/users/{id}",
     *     summary="Update an existing user",
     *     tags={"Users"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the user to update",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="first_name", type="string", example="John"),
     *             @OA\Property(property="last_name", type="string", example="Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="johndoe@example.com"),
     *             @OA\Property(property="address", type="string", example="123 Main St")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="user", ref="#/components/schemas/UserResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Unprocessable Entity",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     *
     * @param UpdateUserRequest $request
     * @param User $user
     * @return JsonResponse
     */
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
    /**
     * Delete a user.
     *
     * @OA\Delete(
     *     path="/api/users/{id}",
     *     summary="Delete a user",
     *     tags={"Users"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the user to delete",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User not found")
     *         )
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     *
     * @param User $user
     * @return JsonResponse
     */
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
