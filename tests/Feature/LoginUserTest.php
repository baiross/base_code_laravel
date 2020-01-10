<?php

namespace Tests\Feature;

use Hash;
use App\Models\User;
use Tests\TestCase;

class LoginUserTest extends TestCase
{
    /** @var array */
    private $data;

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
     * LoginUserTest constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->createApplication();

        // test variables
        $this->data = [
            'client_id' => env('API_CLIENT_ID'),
            'client_secret' => env('API_CLIENT_SECRET'),
            'grant_type' => 'password',
            'username' => self::$user['email'],
            'password' => self::$password,
        ];
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
     * Invalid Client Id
     * @return void
     */
    public function testInvalidClientId()
    {
        $data = $this->data;
        $data['client_id'] = 1;
        $response = $this->json('POST', '/oauth/token', $data);
        $response->assertStatus(401);
        $result = json_decode((string) $response->getContent());
        $this->assertEquals('invalid_client', $result->error);
    }

    /**
     * Invalid Client Secret
     * @return void
     */
    public function testInvalidClientSecret()
    {
        $data = $this->data;
        $data['client_secret'] = uniqid();
        $response = $this->json('POST', '/oauth/token', $data);
        $response->assertStatus(401);
        $result = json_decode((string) $response->getContent());
        $this->assertEquals('invalid_client', $result->error);
    }

    /**
     * Invalid Client Secret
     * @return void
     */
    public function testInvalidPassword()
    {
        $data = $this->data;
        $data['password'] = uniqid();
        $response = $this->json('POST', '/oauth/token', $data);
        $response->assertStatus(401);
        $result = json_decode((string) $response->getContent());
        $this->assertEquals('invalid_credentials', $result->error);
    }

    /**
     * Invalid Email / User does not exist.
     * @return void
     */
    public function testInvalidEmail()
    {
        $data = $this->data;
        $data['username'] = uniqid() . '@mail.com';
        $response = $this->json('POST', '/oauth/token', $data);
        $response->assertStatus(401);
        $result = json_decode((string) $response->getContent());
        $this->assertEquals('invalid_credentials', $result->error);
    }

    /**
     * Missing Parameter(s).
     * @return void
     */
    public function testMissingParams()
    {
        $data = $this->data;
        unset($data['password']);
        $response = $this->json('POST', '/oauth/token', $data);
        $response->assertStatus(400);
        $result = json_decode((string) $response->getContent());
        $this->assertEquals('invalid_request', $result->error);
        $this->assertEquals('The request is missing a required parameter, includes an invalid parameter value, includes a parameter more than once, or is otherwise malformed.', $result->message);
    }

    /**
     * Successful Login
     * @return void
     */
    public function testLogin()
    {
        $response = $this->json('POST', '/oauth/token', $this->data);
        $response->assertStatus(200);
        $result = json_decode((string) $response->getContent());
        $this->assertTrue(strlen($result->access_token) > 0);
        $this->assertTrue(strlen($result->refresh_token) > 0);
    }
}
