<?php

namespace App\Http\Controllers;

use App\Helpers\GeneralHelper;
use App\Http\Requests\ProgressReportRequest;
use App\Http\Resources\ProgressReportResource;
use App\Models\Notification;
use App\Repository\ProgressReportRepository;
use App\Repository\StudentCoursesRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class ProgressReportController extends Controller
{
    private $progressReportRequest;
    private $progressReportRepository;
    private $studentCoursesRepository;

    public function __construct(
        ProgressReportRequest $progressReportRequest, 
        ProgressReportRepository $progressReportRepository,
        StudentCoursesRepository $studentCoursesRepository
    )
    {
        $this->progressReportRequest = $progressReportRequest;
        $this->progressReportRepository = $progressReportRepository;
        $this->studentCoursesRepository = $studentCoursesRepository;
    }

    /**
     * Create teacher payment method
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request)
    {
        $this->progressReportRequest->validateCreate(request());

        $data = $request->all();

        if ($request->hasFile('document')) {
            $file = $request->file('document');
            $path = 'doc/progress_reports';
            $data['file'] = GeneralHelper::uploadProfileImage($file, $path);
        }

        $report = $this->progressReportRepository->create($data);
        $studentCourse = $this->studentCoursesRepository->getStudentCourseByCourseId($data['student_id'], $data['course_id']);

        // Notify student that a progress report has been uploaded
        Notification::create([
            'user_id' => $report->student->user->id,
            'student_id' => $data['student_id'],
            'type' => 'progress_report',
            'read' => false,
            'message' => "A new progress report has been assigned for {$report->student->name} on {$report->created_at} by {$studentCourse->teacher->name}."
        ]);

        return $this->success($data, 'Progress report uploaded successfully', 201);
    }

    /**
     * Update teacher payment method
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete($id)
    {
        $this->progressReportRequest->validateDelete(request());

        $progressReport = $this->progressReportRepository->findById($id);

        if (!$progressReport) {
            return $this->error('Progress report not found', 404);
        }

        $this->progressReportRepository->remove($id);

        return $this->success([], 'Progress report removed successfully', 200);
    }

    /**
     * Get teacher payment method
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStudentReports(Request $request)
    {
        $this->progressReportRequest->validateGet(request());
        
        $student_id = $request->student_id;
        $page = $request->page;
        $limit = $request->limit;
        $offset = ($page - 1) * $limit;

        $progressReports = $this->progressReportRepository
            ->findByStudentId($student_id, $offset, $limit);

        if (empty($progressReports)) {
            return $this->error('Progress reports not found for this student', 404);
        }

        $data = ProgressReportResource::collection($progressReports['data']);
        $dataArray = [
            'reports' => $data,
            'count' => $progressReports['count']
        ];

        return $this->success($dataArray, 'Teacher payment method found', 200);
    }

    /**
     * Download a specific progress report.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function downloadReport(Request $request)
    {
        $this->progressReportRequest->validateDownload(request());

        $reportId = $request->id;
        $reportFilePath = $this->progressReportRepository->getFilePathByReportId($reportId);

        // Assuming getFilePathByReportId returns a path relative to the storage/app/public directory
        $storagePath = 'public/doc/progress_reports/' . basename($reportFilePath);

        if (!File::exists(public_path($storagePath))) {
            return $this->error('File not found', 404);
        }

        $reportDetails = $this->progressReportRepository->findById($reportId);
        $filename = sprintf('report_student_%d_report_%d.pdf', $reportDetails->student_id, $reportId);

        // Return the file directly from storage
        return response()->download(storage_path('app/public/doc/progress_reports/') . basename($reportFilePath), $filename, [
            'Content-Type' => 'application/pdf'
        ]);
    }
}