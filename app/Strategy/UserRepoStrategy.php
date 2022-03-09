<?php 
namespace App\Strategy;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

abstract class UserRepoStrategy{
    abstract public function create($request);
    abstract public function update($request);
    abstract public function find($id);
    abstract public function getAll($data);
    
    public function changePassword($new_password) {
        try {
            return User::where('id', auth()->user()->id)->update([
                'password'  => Hash::make($new_password)
            ]);
        } catch (\Exception $e) {
            throw new \Exception('Failed to change password. '.$e->getMessage());
        }
    }
}