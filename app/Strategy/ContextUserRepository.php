<?php 
namespace App\Strategy;

define("TEACHER", 'TEACHER');
define("STUDENT", 'STUDENT');

class ContextUserRepository{
    private UserRepoStrategy $strategy;

    public function setStrategy(UserRepoStrategy $strategy){
        $this->strategy = $strategy;
    }

    public function executeCreate($request){
        $this->strategy->create($request);
    }

    public function executeUpdate($request){
        $this->strategy->update($request);
    }

    public function executeFind($id){
        return $this->strategy->find($id);
    }

    public function executeGetAll($data = []){
        return $this->strategy->getAll($data);
    }

    public function executeChangePassword($new_password){
        $this->strategy->changePassword($new_password);
    }
}