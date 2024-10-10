<?php

namespace App\Repository;

use App\Models\ProgressReport;

class ProgressReportRepository
{
    private $model;

    public function __construct(ProgressReport $progressReport)
    {
        $this->model = $progressReport;
    }

    public function create($data)
    {
        return $this->model->create($data);
    }

    public function findById($id)
    {
        return $this->model->where('id', $id)->first();
    }

    public function findByStudentId($studentId, $offset, $limit)
    {
        $data = $this->model->where('student_id', $studentId)
            ->skip($offset)
            ->take($limit)
            ->get();

        $count = $this->studentReportsCount($studentId);

        return [
            'data' => $data,
            'count' => $count
        ];
    }

    public function studentReportsCount($studentId)
    {
        return $this->model->where('student_id', $studentId)->count();
    }

    public function remove($id)
    {
        return $this->model->where('id', $id)->delete();
    }

    public function getFilePathByReportId($reportId)
    {
        $filename = $this->model->where('id', $reportId)->value('file');
        // Adjust the base directory to the actual location of the files
        $baseDirectory = public_path('doc/progress_reports');
        
        // No need to check for redundant path since we're not using the storage path anymore
        $filePath = $baseDirectory . '/' . $filename;
        return $filePath;
    }
}