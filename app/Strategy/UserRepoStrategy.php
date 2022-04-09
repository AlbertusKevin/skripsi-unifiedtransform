<?php 
namespace App\Strategy;

use App\Models\User;
use App\Traits\Base64ToFile;
use Illuminate\Support\Facades\Hash;

abstract class UserRepoStrategy{
    use Base64ToFile;

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

    protected function getRequest($request, $role='', $create=false){
        $data = [
            'first_name'    => $request['first_name'],
            'last_name'     => $request['last_name'],
            'email'         => $request['email'],
            'gender'        => $request['gender'],
            'nationality'   => $request['nationality'],
            'phone'         => $request['phone'],
            'address'       => $request['address'],
            'address2'      => $request['address2'],
            'city'          => $request['city'],
            'zip'           => $request['zip']
        ];

        if($create){
            $data = array_merge($data, [
                'photo'         => (!empty($request['photo']))?$this->convert($request['photo']):null,
                'password'      => Hash::make($request['password']),
                'role'          => $role,
            ]);
        }

        return $data;
    }
}