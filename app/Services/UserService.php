<?php

namespace App\Services;

use DB;
use Mail;
use Hash;
use Exception;
use App\Models\User;
use App\Models\ActivationToken;
use App\Models\UserStatus;
use RuntimeException;
use App\Mail\UserSignUp;

class UserService
{

    /**
     * @var App\Models\User
     */
    protected $user;

    /**
     * UserService constructor.
     *
     * @param App\Models\User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Service function that stores a new user
     *
     * @param array $params User parameters
     * @return Object $user User
     */
    public function create(array $params)
    {
        DB::beginTransaction();

        try {
            $params['password'] = Hash::make($params['password']);
            $status = UserStatus::where('name', 'Pending')->first();

            if (!($status instanceof UserStatus)) {
                throw new RuntimeException('Unable to retrieve status.');
            }

            $params['user_status_id'] = $status->id;
            $user = $this->user->create($params);

            if (!($user instanceof User)) {
                throw new RuntimeException('Unable to create user.');
            }

            $token = Hash::make(time());

            $user->activationTokens()->save(new ActivationToken(['token' => $token]));

            // send email
            Mail::to($user)->send(new UserSignUp($user, $token));

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            throw new Exception($e->getMessage());
        }

        return $user;
    }

    /**
     * Service function that activates the user account.
     *
     * @param array $params User parameters
     * @return User $user
     */
    public function activateByToken($token)
    {
        $activationToken = ActivationToken::with('user.status')
                                            ->where('token', $token)
                                            ->where('revoked', false)
                                            ->first();

        if (!($activationToken instanceof ActivationToken)) {
            throw new Exception('Invalid activation token.');
        }

        $status = UserStatus::where('name', 'Active')->first();

        if (!($status instanceof UserStatus)) {
            throw new RuntimeException('Unable to retrieve status.');
        }

        $user = $activationToken->user;

        // change user status to active
        $user->update(['user_status_id' => $status->id]);

        // revoke the token
        $activationToken->revoked = true;
        $activationToken->save();

        // retrieve updated user details
        $user = User::with('status')->find($activationToken->user->id);

        return $user;
    }
}
