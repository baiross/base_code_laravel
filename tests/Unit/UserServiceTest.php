<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use Hash;
use App\Services\UserService;

class UserServiceTest extends TestCase
{
    /** @var array */
    private $data;

    /** @var UserService */
    private $service;

    /**
     * UserServiceTest constructor.
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'johndoe@mail.com',
            'password' => '!p4ssW0rd',
        ];
        $this->service = new UserService(new User);
    }

    /**
     * @expectedException TypeError
     */
    public function testInvalidParamType()
    {
        $this->service->create('params');
    }

    /**
     * @expectedException Illuminate\Database\QueryException
     */
    public function testCreateMissingField()
    {
        $data = $this->data;
        unset($data['last_name']);

        $this->service->create($data);
    }

    /**
     * User Create
     * @return void
     */
    public function testCreate()
    {
        $user = $this->service->create($this->data);

        foreach ($this->data as $key => $value) {
            if ($key === 'password') {
                $this->assertTrue(Hash::check($this->data['password'], $user->password));
            } else {
                $this->assertEquals($value, $user->{$key});
            }
        }

        // test cleanup
        $user->delete();
    }
}
