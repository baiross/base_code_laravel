<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;

class CreateUserTest extends TestCase
{
    private $data;

    public function __construct()
    {
        parent::__construct();
        // test variables
        $this->data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'johndoe@mail.com',
            'password' => '!p4ssW0rd',
        ];
        $this->createApplication();
    }

    public function testCreateMissingParam()
    {
        $data = $this->data;
        unset($data['password']);

        $response = $this->json('POST', '/api/users', $data);

        $result = $response->getData();

        $this->assertEquals(500, $response->getStatusCode());

        $this->assertTrue(
            in_array('The password field is required.', $result->error->password)
        );
    }

    public function testCreate()
    {
        $response = $this->json('POST', '/api/users', $this->data);
        $result = $response->getData();
        $user = $result->data;

        foreach ($this->data as $key => $value) {
            if ($key === 'password') {
                continue;
            }

            $this->assertEquals($value, $user->{$key});
        }

        // remove test data
        (User::find($user->id))->delete();
    }
}
