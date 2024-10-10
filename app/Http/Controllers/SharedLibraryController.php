<?php

namespace App\Http\Controllers;

use App\Http\Requests\SharedLibraryRequest;
use App\Models\SharedLibrary;
use App\Services\SharedLibraryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SharedLibraryController extends Controller
{
    private $sharedLibraryRequest;
    private $sharedLibraryService;

    public function __construct(
        SharedLibraryRequest $sharedLibraryRequest,
        SharedLibraryService $sharedLibraryService
    )
    {
        $this->sharedLibraryRequest = $sharedLibraryRequest;
        $this->sharedLibraryService = $sharedLibraryService;
    }
    
    /**
     * the below method must receive a course_id, 
     * a list of teachers to whome the folder is assigned
     * and an initial file to upload to AWS
     */
    public function addSharedLibrary(Request $request)
    {
        $this->sharedLibraryRequest->validateAddRequest($request);
        $userId = Auth::user()->id;
        
        $folderDetails = array(
            'title' => $request->title,
            'description' => $request->description,
            'created_by' => $userId,
            'course_id' => $request->course_id,
        );

        $teachers = $request->teachers;
        $files = $request->file('files');

        $duplicatedFiles = $this->sharedLibraryService->checkForDuplicateFiles($files);

        $duplicationCount = count($duplicatedFiles);
        if($duplicationCount > 0) {
            return $this->success(['files_exist' => $duplicatedFiles]);
        }
        
        try {
            DB::beginTransaction();
            $data = $this->sharedLibraryService->addSharedLibrary($folderDetails, $teachers, $files, $userId);
            DB::commit();

            $data['files_exist'] = [];
            return $this->success($data, 'Shared library created successfully');
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->error($e->getMessage(), 500);
        }
    }

    /**
     * to get all shared libraries for a coordinator
     * the below method must receive a page number and limit
     * and return the shared libraries with pagination
     */
    public function getLibraries(Request $request)
    {
        $this->sharedLibraryRequest->validateGetLibrariesRequest($request);
        $page = $request->page;
        $limit = $request->limit;
        $offset = ($page - 1) * $limit;

        $libraries = $this->sharedLibraryService->getLibraries($limit, $offset);
        
        return $this->success($libraries, 'Shared libraries fetched successfully');
    }

    /**
     * to get all shared libraries for a student
     * 
     * @param Request $request: student_id, page, limit
     * @return JsonResponse
     */
    public function getStudentLibraries(Request $request)
    {
        $this->sharedLibraryRequest->validateGetStudentLibrariesRequest($request);
        $studentId = $request->student_id;
        $page = $request->page;
        $limit = $request->limit;
        $offset = ($page - 1) * $limit;

        $libraries = $this->sharedLibraryService->getStudentLibraries($studentId, $limit, $offset);
        return $this->success($libraries, 'Student libraries fetched successfully');
    }

    /**
     * to get all shared libraries for a teacher
     * 
     * @param Request $request: teacher_id, page, limit
     * @return JsonResponse
     */
    public function getTeacherLibraries(Request $request)
    {
        $this->sharedLibraryRequest->validateGetTeacherLibrariesRequest($request);
        $teacherId = Auth::user()->id;
        $page = $request->page;
        $limit = $request->limit;
        $offset = ($page - 1) * $limit;

        $libraries = $this->sharedLibraryService->getTeacherLibraries($teacherId, $limit, $offset);
        return $this->success($libraries, 'Teacher libraries fetched successfully');
    }

    /**
     * to get all teachers list with a flag
     * that the current folder is assigned to them or not
     */
    public function getAllTeachers(Request $request)
    {
        $this->sharedLibraryRequest->validateGetAllTeachersRequest($request);
        $folderId = $request->folder_id; 
        $teachers = $this->sharedLibraryService->getAllTeachers($folderId);
        return $this->success($teachers, 'Teachers with folder access status fetched successfully');
    }

    /**
     * to get a single folder details with files
     * the below method must receive a folder_id in the request
     * and return the folder details with files
     */
    public function getLibraryDetails(Request $request)
    {
        $this->sharedLibraryRequest->validateGetLibraryDetailsRequest($request);
        $libraryId = $request->library_id;
        $library = $this->sharedLibraryService->getLibraryDetails($libraryId);
        return $this->success($library, 'Library details fetched successfully');
    }

    /**
     * to delete a library file
     * the below method must receive a file_id in the request
     * and delete the file from the db and aws
     * RESTRICTION
     * the file can only be deleted if it is not assigned to any student
     */
    public function deleteLibraryFile(Request $request)
    {
        $this->sharedLibraryRequest->validateDeleteLibraryFileRequest($request);
        $fileId = $request->file_id;
        $this->sharedLibraryService->deleteLibraryFile($fileId);
        return $this->success([], 'Library file deleted successfully');
    }


    /**
     * the below method will update the shared library
     * it will receive the folder_id, slug, description, teachers, and new files to upload
     * it will update the folder details and assign the folder to the teachers
     * and upload the new files to the AWS
     * and update the files count in the shared library
     * and return the updated folder details
     * 
     * STEPS
     * 
     * 1. validate the request  -done
     * 2. check for duplicate files -done
     * 3. if folder name is changed, update the folder name in the AWS
     * 4. if folder name is not changed, we will not update the folder name in the AWS
     * 5. upload the new files to the AWS
     * 6. update the files count in the shared library
     * 7. remove all teachers from the folder
     * 7. assign the folder to the new teachers
     * 8. return the updated folder details
     */
    public function updateSharedLibrary(Request $request) {
    
        $this->sharedLibraryRequest->validateUpdateRequest($request);
        $newFolderName = $request->title;
        $folderDetails = array(
            'title' => $newFolderName,
            'description' => $request->description,
            'course_id' => $request->course_id,
        );
        $teachers = $request->teachers;
        $folderId = $request->folder_id;
        $files = $request->file('files');
        $oldFolderName = SharedLibrary::find($folderId)->title;

        $duplicatedFiles = $this->sharedLibraryService->checkForDuplicateFiles($files);

        $duplicationCount = count($duplicatedFiles);
        if($duplicationCount > 0) {
            return $this->errorWithData('Duplicate files found', 409, $duplicatedFiles);
        }
        $userId = Auth::user()->id;
        $data = $this->sharedLibraryService->updateSharedLibrary($folderDetails, $teachers, $files, $folderId, $oldFolderName, $newFolderName, $userId);
        
        return $this->success($data, 'Shared library updated successfully');
    }

    public function getTeacherFolders(Request $request)
    {
        $teacherId = Auth::user()->id;
        $folders = $this->sharedLibraryService->getTeacherFolders($teacherId);
        return $this->success($folders, 'Teacher folders fetched successfully');
    }

    public function removeTeacherFolder(Request $request)
    {
        $this->sharedLibraryRequest->validateRemoveTeacherFolderRequest($request);
        $folderId = $request->folder_id;
        $teacherId = $request->teacher_id;
        $message = $this->sharedLibraryService->removeTeacherFolder($folderId, $teacherId);
        return $this->success([], $message);
    }

    public function deleteAwsFolder(Request $request)
    {
        $this->sharedLibraryRequest->validateDeleteAwsFolderRequest($request);
        $folderId = $request->folder_id;
        $message = $this->sharedLibraryService->deleteAwsFolder($folderId);
        return $this->success([], $message);
    }

    /**
     * to assign a library file to a student
     * 
     * @param Request $request: file_id, student_id
     * @return JsonResponse
     */
    public function assignLibraryFileToStudent(Request $request) 
    {
        $this->sharedLibraryRequest->validateAssignLibraryFileToStudentRequest($request);
        
        try {
            DB::beginTransaction();

            $fileId = $request->file_id;
            $studentId = $request->student_id;
            $this->sharedLibraryService->assignLibraryFileToStudent($fileId, $studentId);
            
            DB::commit();

            return $this->success([], 'Library file assigned to student successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), 500);
        }
    }

    /**
     * to unassign a library file from a student
     * 
     * @param Request $request: file_id, student_id
     * @return JsonResponse
     */
    public function unassignLibraryFileFromStudent(Request $request)
    {
        $this->sharedLibraryRequest->validateAssignLibraryFileToStudentRequest($request);
        $fileId = $request->file_id;
        $studentId = $request->student_id;
        $this->sharedLibraryService->unassignLibraryFileFromStudent($fileId, $studentId);
        return $this->success([], 'Library file unassigned from student successfully');
    }
}