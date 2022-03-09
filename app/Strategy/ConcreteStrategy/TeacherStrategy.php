<?php 
namespace App\Strategy\ConcreteStrategy;

use App\Models\User;
use App\Strategy\UserRepoStrategy;
use App\Traits\Base64ToFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TeacherStrategy extends UserRepoStrategy{
    use Base64ToFile;

    private function getData($request){
        return [
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
    }

    public function create($request){
        try {
            DB::transaction(function () use ($request) {
                $data = [
                    'photo'         => (!empty($request['photo']))?$this->convert($request['photo']):null,
                    'role'          => 'teacher',
                    'password'      => Hash::make($request['password']),
                ];

                $data = array_merge($data, $this->getData($request));
                $user = User::create($data);
                $user->givePermissionTo(
                    'create exams',
                    'view exams',
                    'create exams rule',
                    'view exams rule',
                    'edit exams rule',
                    'delete exams rule',
                    'take attendances',
                    'view attendances',
                    'create assignments',
                    'view assignments',
                    'save marks',
                    'view users',
                    'view routines',
                    'view syllabi',
                    'view events',
                    'view notices',
                );
            });
        } catch (\Exception $e) {
            throw new \Exception('Failed to create Teacher. '.$e->getMessage());
        }
    }
    public function update($request){
        try {
            DB::transaction(function () use ($request) {
                User::where('id', $request['teacher_id'])->update($this->getData($request));
            });
        } catch (\Exception $e) {
            throw new \Exception('Failed to update Teacher. '.$e->getMessage());
        }
    }
    public function find($id){
        try {
            return User::where('id', $id)->where('role', 'teacher')->first();
        } catch (\Exception $e) {
            throw new \Exception('Failed to get Teacher. '.$e->getMessage());
        }
    }
    public function getAll($data)
    {
        try {
            return User::where('role', 'teacher')->get();
        } catch (\Exception $e) {
            throw new \Exception('Failed to get all Teachers. '.$e->getMessage());
        }
    }
}

?>