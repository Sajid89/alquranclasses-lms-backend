<?php

namespace App\Repository;

use App\Models\CreditHistory;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CreditHistoryRepository
{
    private $model;

    public function __construct(CreditHistory $model)
    {
        $this->model = $model;
    }

    public function create($data)
    {
        return $this->model->create($data);
    }

    public function getCreditHistoryCount($studentCourseId)
    {
        return $this->model->where('student_course_id', $studentCourseId)
            ->where('expired_at', '>', Carbon::now())->count();
    }

    public function getCreditHistory($studentCourseId)
    {
        return $this->model->where('student_course_id', $studentCourseId)->get();
    }
}