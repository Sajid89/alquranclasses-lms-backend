<?php

namespace App\Repository;

use App\Models\TeacherPaymentMethod;

class TeacherPaymentMethodRepository
{
    private $model;

    public function __construct(TeacherPaymentMethod $teacherPaymentMethod)
    {
        $this->model = $teacherPaymentMethod;
    }

    public function create($data)
    {
        return $this->model->create($data);
    }

    public function findByUserId($userId)
    {
        return $this->model->where('user_id', $userId)->first();
    }

    public function update($id, $data)
    {
        return $this->model->where('id', $id)->update($data);
    }
}