<?php

namespace Tests\Feature;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test fetching paginated users.
     */
    public function testFetchUsers()
    {
        // Create a user using the factory
        $user = User::factory()->create();

        // Authenticate as the user and make the API request
        $response = $this->actingAs($user, 'api')
                        ->get('/api/users');
        //dd($response->json());
        // Assert the response status is 200 OK
        $response->assertStatus(200);

        // Assert the JSON structure of the response
        $response->assertJsonStructure([
            'code',
            'success',
            'message',
            'data' => [
                'data' => [
                    '*' => [
                        'id',
                        'first_name',
                        'last_name',
                        'email',
                        'address',
                        'created_at',
                        'updated_at'
                    ]
                ],
                'total',
                'per_page'
            ]
        ]);

        // Perform additional assertions as needed
    }

    /**
     * Test user login.
     */
    public function testUserLogin()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        // Test with valid credentials
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);
        //dd($response->json());
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'code',
                     'success',
                     'message',
                     'data' => [
                         'token',
                     ],
                     'message',
                 ]);

        // Test with invalid credentials
        $response = $this->postJson('/api/login', [
            'email' => 'invalid@example.com',
            'password' => 'invalidpassword',
        ]);
        //dd($response->json());
        $response->assertStatus(200)
        ->assertJson([
            'success' => false,
            'code' => 401,
            'message' => 'Unauthorized',
            'errors' => []
        ]);
    }

    /**
     * Test user creation.
     */
    public function testUserCreation()
    {
        $userData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'johndoe@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        // Test successful user creation
        $response = $this->postJson('/api/users', $userData);

        $response->assertStatus(201)
        ->assertJsonStructure([
            'code',
            'success',
            'message',
            'data' => [
                'user' => [
                    'id',
                    'first_name',
                    'last_name',
                    'email',
                    'address',
                'created_at',
                'updated_at'
                ],
                'token',
        ]
        ]);

        // Test user creation with invalid data (e.g., missing required fields)
        $invalidData = [
            'name' => 'Invalid User',
        ];

        $response = $this->postJson('/api/users', $invalidData);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email', 'password']);
    }

    /**
     * Test updating a user.
     */
    public function testUserUpdate()
    {
        $user = User::factory()->create();

        $updatedData = [
            'first_name' => 'update',
            'last_name' => 'Name',
            'email' => 'updated@example.com',
        ];

        // Test with valid user update
        $response = $this->actingAs($user, 'api')
                         ->putJson("/api/users/{$user->id}", $updatedData);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'code',
                     'success',
                     'message',
                     'data' => [
                         'user' => [
                             'id',
                             'first_name',
                             'last_name',
                             'email',
                             'address',
                            'created_at',
                            'updated_at'
                         ],
                     ],
                     'message',
                 ]);

        // Test user update with invalid data (e.g., invalid email)
        $invalidData = [
            'email' => 'invalidemail', // Invalid email format
        ];

        $response = $this->actingAs($user, 'api')
                         ->putJson("/api/users/{$user->id}", $invalidData);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test deleting a user.
     */
    public function testUserDeletion()
    {
        $user = User::factory()->create();

        // Test successful user deletion
        $response = $this->actingAs($user, 'api')
                         ->delete("/api/users/{$user->id}");

        $response->assertStatus(200)
                 ->assertJson([
                     'code' => 200,
                     'success' => true,
                     'message' => 'User deleted successfully',
                     'data' => null
                 ]);
        // Test deleting a non-existing user

        $nonExistingUserId = 99;

        // Use try-catch block to handle potential ModelNotFoundException
        try {
            $response = $this->actingAs($user, 'api')
                             ->delete("/api/users/{$nonExistingUserId}");
            // If no exception is thrown, assert that the response has status 404
            $response->assertStatus(404)
                     ->assertJson([
                         'status' => 'error',
                         'message' => 'User not found',
                     ]);
        } catch (ModelNotFoundException $e) {
            // Handle the ModelNotFoundException
            // Assert that the expected exception message is thrown
            $this->assertEquals('No query results for model [App\Models\User] ' . $nonExistingUserId, $e->getMessage());
        }

    }

}
