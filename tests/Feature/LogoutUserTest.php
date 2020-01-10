<?php

namespace Tests\Feature;

use Hash;
use App\Models\User;
use Tests\TestCase;

class LogoutUserTest extends TestCase
{
    /** @var string */
    public $accessToken = null;

    /** @var array */
    public static $user = [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'johndoe@mail.com',
        'password' => '',
    ];

    /** @var string */
    private static $password = 'password';

    /**
     * Execute before each test
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        self::$user['password'] = Hash::make(self::$password);
        User::updateOrCreate(
            ['email' => self::$user['email']],
            self::$user
        );
    }

    /**
     * Run after every test function.
     * @return void
     */
    public function tearDown(): void
    {
        // delete user
        $user = User::where('email', self::$user['email'])->first();

        if ($user) {
            $user->delete();
        }

        parent::tearDown();
    }

    /**
     * LogoutUserTest constructor.
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Logout User
     * @return void
     */
    public function testLogout()
    {
        $response = $this->json(
            'POST',
            '/oauth/token',
            [
                'client_id' => env('API_CLIENT_ID'),
                'client_secret' => env('API_CLIENT_SECRET'),
                'grant_type' => 'password',
                'username' => self::$user['email'],
                'password' => self::$password,
            ]
        );
        $response->assertStatus(200);
        $result = json_decode((string) $response->getContent());
        $this->accessToken = $result->access_token;

        $response = $this->withHeaders([
                        'Authorization' => 'Bearer ' . $this->accessToken,
                    ])
                    ->json('DELETE', '/api/oauth/token');
        $response->assertStatus(200);
        $result = json_decode((string) $response->getContent());
        $this->assertFalse($result->authenticated);
    }
}
