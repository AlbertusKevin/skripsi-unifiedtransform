<?php 
namespace App\Strategy\ConcreteStrategy;

use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\User;
use App\Repositories\PromotionRepository;
use App\Repositories\StudentAcademicInfoRepository;
use App\Repositories\StudentParentInfoRepository;
use App\Strategy\UserRepoStrategy;
use App\Traits\Base64ToFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StudentStrategy extends UserRepoStrategy{
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
            'zip'           => $request['zip'],
            'birthday'      => $request['birthday'],
            'religion'      => $request['religion'],
            'blood_type'    => $request['blood_type'],
        ];
    }

    public function create($request){
        try {
            DB::transaction(function () use ($request) {
                $data = [
                    'photo'         => (!empty($request['photo']))?$this->convert($request['photo']):null,
                    'role'          => 'student',
                    'password'      => Hash::make($request['password']),
                ];

                array_merge($data, $this->getData($request));
                $student = User::create($data);
                
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
                User::where('id', $request['student_id'])->update($this->getData($request));

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

    public function getAll($data)
    {
        if(array_key_exists("by_session",$data)){
            return $this->getAllStudentsBySession($data["session_id"]);
        }

        if(array_key_exists("by_session_count",$data)){
            return $this->getAllStudentsBySessionCount($data["session_id"]);
        }
        
        return $this->getAllStudents($data["session_id"],$data["class_id"],$data["section_id"]);
    }

    private function getAllStudents($session_id, $class_id, $section_id) {
        if($class_id == 0 || $section_id == 0) {
            $schoolClass = SchoolClass::where('session_id', $session_id)->first();
            $section = Section::where('session_id', $session_id)->first();

            if($schoolClass == null || $section == null){
                throw new \Exception('There is no class and section');
            } else {
                $class_id = $schoolClass->id;
                $section_id = $section->id;
            }
        }

        try {
            $promotionRepository = new PromotionRepository();
            return $promotionRepository->getAll($session_id, $class_id, $section_id);
        } catch (\Exception $e) {
            throw new \Exception('Failed to get all Students. '.$e->getMessage());
        }
    }

    private function getAllStudentsBySession($session_id) {
        $promotionRepository = new PromotionRepository();
        return $promotionRepository->getAllStudentsBySession($session_id);
    }

    private function getAllStudentsBySessionCount($session_id) {
        $promotionRepository = new PromotionRepository();
        return $promotionRepository->getAllStudentsBySessionCount($session_id);
    }
}
?>