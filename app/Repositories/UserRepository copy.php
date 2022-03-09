<?php

namespace App\Repositories;

use App\Models\User;
use App\Traits\Base64ToFile;
use App\Interfaces\UserInterface;
use App\Models\SchoolClass;
use App\Models\Section;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Repositories\PromotionRepository;
use App\Repositories\StudentParentInfoRepository;
use App\Repositories\StudentAcademicInfoRepository;

class UserRepository implements UserInterface {
    use Base64ToFile;

    public function createTeacher($request) {}
    public function createStudent($request) {}
    public function updateStudent($request) {}
    public function updateTeacher($request) {}
    public function findStudent($id) {}
    public function findTeacher($id) {}

    public function getAllStudents($session_id, $class_id, $section_id) {
        if($class_id == 0 || $section_id == 0) {
            $schoolClass = SchoolClass::where('session_id', $session_id)
                                    ->first();
            $section = Section::where('session_id', $session_id)
                                    ->first();
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

    public function getAllStudentsBySession($session_id) {}

    public function getAllStudentsBySessionCount($session_id) {}

    public function getAllTeachers() {
        try {
            return User::where('role', 'teacher')->get();
        } catch (\Exception $e) {
            throw new \Exception('Failed to get all Teachers. '.$e->getMessage());
        }
    }

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