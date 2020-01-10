<?php

namespace App\Http\Controllers;

use Exception;
use App\Http\Controllers\Controller;
use App\Services\UserService;
use App\Http\Requests\CreateUserRequest;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected $service;

    public function __construct(UserService $service)
    {
        parent::__construct();

        $this->service = $service;
    }

    /**
     *  Creates a new user.
     *
     *  @param App\Http\Requests\CreateUserRequest $request
     *  @return \Illuminate\Http\Response
     */
    public function create(CreateUserRequest $request)
    {
        $request->validated();

        try {
            $formData = [
                'first_name' => $request->getFirstName(),
                'last_name' => $request->getLastName(),
                'email' => $request->getEmail(),
                'password' => $request->getPassword(),
            ];

            $this->response['data'] = $this->service->create($formData);
            // @codeCoverageIgnoreStart
        } catch (Exception $e) {
            $this->response = [
                'error' => $e->getMessage(),
                'code' => 500,
            ];
        }
        // @codeCoverageIgnoreStart

        return response()->json($this->response, $this->response['code']);
    }

    /**
     *  Activate user account.
     *
     *  @param App\Http\Requests\CreateUserRequest $request
     *  @return \Illuminate\Http\Response
     */
    public function activate(Request $request)
    {
        try {
            $token = $request->get('token');
            $this->response['data'] = $this->service->activateByToken($token);
        } catch (Exception $e) {
            $this->response = [
                'error' => $e->getMessage(),
                'code' => 500,
            ];
        }

        return response()->json($this->response, $this->response['code']);
    }
}
