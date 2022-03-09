<?php

namespace App\Http\Controllers\Auth;

use App\Interfaces\UserInterface;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\PasswordChangeRequest;
use App\Strategy\ContextUserRepository;
use App\Traits\StrategyContext;

class UpdatePasswordController extends Controller
{
    use StrategyContext;
    private ContextUserRepository $context;

    public function __construct()
    {
        $this->context = new ContextUserRepository();
    }

    public function edit() {
        return view('auth.passwords.edit');
    }

    public function update(PasswordChangeRequest $request) {
        $request = $request->validated();
        if (Hash::check($request['old_password'], auth()->user()->password)) {
            // The passwords match...
            try{
                $this->context->executeChangePassword($request['new_password']);

                return back()->with('status', 'Changing password was successful!');
            } catch (\Exception $e) {
                return back()->withError($e->getMessage());
            }
        } else {
            return back()->withError('Password mismatched!');
        }
    }
}
