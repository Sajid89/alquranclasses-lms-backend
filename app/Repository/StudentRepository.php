<?php
namespace App\Repository;

use App\Models\Student;

class StudentRepository
{
    protected $model;

    public function __construct(Student $student)
    {
        $this->model = $student;
    }

    public function all()
    {
        return $this->model->all();
    }

    public function find($id)
    {
        return $this->model->find($id);
    }

    public function create(array $data)
    {
        $student = $this->model->firstOrCreate(
            ['name' => $data['name'], 'user_id' => $data['user_id']],
            $data
        );

        if ($student->wasRecentlyCreated) {
            return $student;
        } else {
            return ['error' => 'A student with this name already exists for this user.'];
        }
    }

    public function update(array $data, $id)
    {
        $record = $this->model->find($id);
        return $record->update($data);
    }

    public function delete($id)
    {
        return $this->model->destroy($id);
    }

    public function getStudentIdsForCustomer($customerId)
    {
        return $this->model->where('user_id', $customerId)->get()->pluck('id');
    }
}