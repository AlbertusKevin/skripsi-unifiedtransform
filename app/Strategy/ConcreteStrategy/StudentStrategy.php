<?php 
namespace App\Strategy\ConcreteStrategy;

use App\Models\User;
use App\Repositories\PromotionRepository;
use App\Repositories\StudentAcademicInfoRepository;
use App\Repositories\StudentParentInfoRepository;
use App\UserRepoStrategy\UserRepoStrategy;
use App\Traits\Base64ToFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;


class StudentStrategy extends UserRepoStrategy{
    use Base64ToFile;

    public function create($request){
        try {
            DB::transaction(function () use ($request) {
                $student = User::create([
                    'first_name'    => $request['first_name'],
                    'last_name'     => $request['last_name'],
                    'email'         => $request['email'],
                    'gender'        => $request['gender'],
                    'nationality'   => $request['nationality'],
                    'phone'         => $request['phone'],
                    'address'       => $request['address'],
                    'address2'      => $request['address2'],
                    'city'          => $request['city'],
                    'zip'           => $request['zip'],
                    'photo'         => (!empty($request['photo']))?$this->convert($request['photo']):null,
                    'birthday'      => $request['birthday'],
                    'religion'      => $request['religion'],
                    'blood_type'    => $request['blood_type'],
                    'role'          => 'student',
                    'password'      => Hash::make($request['password']),
                ]);
                
                // Store Parents' information
                $studentParentInfoRepository = new StudentParentInfoRepository();
                $studentParentInfoRepository->store($request, $student->id);
                
                // Store Academic information
                $studentAcademicInfoRepository = new StudentAcademicInfoRepository();
                $studentAcademicInfoRepository->store($request, $student->id);

                // Assign student to a Class and a Section
                $promotionRepository = new PromotionRepository();
                $promotionRepository->assignClassSection($request, $student->id);

                $student->givePermissionTo(
                    'view attendances',
                    'view assignments',
                    'submit assignments',
                    'view exams',
                    'view marks',
                    'view users',
                    'view routines',
                    'view syllabi',
                    'view events',
                    'view notices',
                );
            });
        } catch (\Exception $e) {
            throw new \Exception('Failed to create Student. '.$e->getMessage());
        }
    }
    public function update($request){
        try {
            DB::transaction(function () use ($request) {
                User::where('id', $request['student_id'])->update([
                    'first_name'    => $request['first_name'],
                    'last_name'     => $request['last_name'],
                    'email'         => $request['email'],
                    'gender'        => $request['gender'],
                    'nationality'   => $request['nationality'],
                    'phone'         => $request['phone'],
                    'address'       => $request['address'],
                    'address2'      => $request['address2'],
                    'city'          => $request['city'],
                    'zip'           => $request['zip'],
                    'birthday'      => $request['birthday'],
                    'religion'      => $request['religion'],
                    'blood_type'    => $request['blood_type'],
                ]);

                // Update Parents' information
                $studentParentInfoRepository = new StudentParentInfoRepository();
                $studentParentInfoRepository->update($request, $request['student_id']);

                // Update Student's ID card number
                $promotionRepository = new PromotionRepository();
                $promotionRepository->update($request, $request['student_id']);
            });
        } catch (\Exception $e) {
            throw new \Exception('Failed to update Student. '.$e->getMessage());
        }
    }
    public function find($id){
        try {
            return User::with('parent_info', 'academic_info')->where('id', $id)->first();
        } catch (\Exception $e) {
            throw new \Exception('Failed to get Student. '.$e->getMessage());
        }
    }
}
?>