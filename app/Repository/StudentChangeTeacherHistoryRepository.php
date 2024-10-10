<?php

namespace App\Repository;

use App\Models\Student;
use App\Models\User;
use App\Models\StudentChangeTeacherHistory;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class StudentChangeTeacherHistoryRepository
{
    private $model;

    public function __construct(StudentChangeTeacherHistory $model)
    {
        $this->model = $model;
    }

    public function store($data) {
        return $this->model::create($data);
    }
}