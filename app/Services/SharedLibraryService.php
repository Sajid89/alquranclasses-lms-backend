<?php
namespace App\Services;

use App\Helpers\GeneralHelper;
use App\Jobs\SendFolderOrFileAssignedEmail;
use App\Models\Notification;
use App\Models\Student;
use App\Models\User;
use App\Repository\SharedLibraryRepository;
use App\Repository\StudentCourseActivityRepository;
use App\Repository\StudentCoursesRepository;
use App\Repository\StudentRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SharedLibraryService
{
    private $sharedLibraryRepository;
    private $folderSizes;
    private $studentRepository;
    private $studentCourseActivityRepository;
    private $studentCourseRepository;

    public function __construct(
        SharedLibraryRepository $sharedLibraryRepository,
        StudentRepository $studentRepository,
        StudentCourseActivityRepository $studentCourseActivityRepository,
        StudentCoursesRepository $studentCourseRepository
    )
    {
        $this->sharedLibraryRepository = $sharedLibraryRepository;
        $this->folderSizes = array();
        $this->studentRepository = $studentRepository;
        $this->studentCourseActivityRepository = $studentCourseActivityRepository;
        $this->studentCourseRepository = $studentCourseRepository;
    }


    /**
     * the below method must receive a course_id, 
     * a list of teachers to whome the folder is assigned
     * and an initial file to upload to AWS
     * @param array $folderData
     * @param int $courseId
     * @return array
     */
    public function addSharedLibrary($folderData, $teachers, $files, $userId) 
    {
        $folder = $this->sharedLibraryRepository->createFolder($folderData);
        $folderId = $folder->id;
        $folderName = $folder->title;

        $filesData = array();

        foreach ($files as $file) {
            $fileSizeInKB = $file->getSize() / 1024; // Get file size in KB
            $fileType = $file->getMimeType(); // Get file MIME type
            $slug = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)); // Generate slug from file name
        
            $fileData = array(
                'shared_library_id' => $folderId,
                'file' => $file->getClientOriginalName(),
                'title' => $file->getClientOriginalName(),
                'slug' => $slug,
                'file_size' => $fileSizeInKB,
                'file_type' => $fileType,
                'aws_file_link' => '',
                'aws_file_name' => '',
                'created_by' => $userId,
            );

            //upload the file to AWS
            $filesData[] = $this->sharedLibraryRepository->uploadLibraryFileToAWS($fileData, $file, $folderName);
        }

        //insert files data to the database
        $this->sharedLibraryRepository->insertFilesData($filesData);

        //update the files count in the shared library
        $this->sharedLibraryRepository->updateFilesCount($folderId, count($filesData));
        
        //assign the folder to the teachers and send email notification
        $data = $this->sharedLibraryRepository->assignFolderToTeachers($folderId, $teachers, $folderName);
        
        return $data;
    }

    /**
     * get the libraries
     * 
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getLibraries($limit, $offset) 
    {
        $libraryCount = $this->sharedLibraryRepository->getLibrariesCount();
        $libraries = $this->sharedLibraryRepository->getLibraries($limit, $offset);
        $data = array();

        foreach ($libraries as $library) {
            $user = $library->user;
            $timezone = $user->timezone;
            if($timezone == null) {
                $timezone = 'Asia/Karachi';
            }
            $createdAt = Carbon::parse($library->created_at)->timezone($timezone)->format('d-M, Y H:i:s');
            $data[] = [
                'id' => $library->id,
                'title' => $library->title,
                'slug' => $library->slug,
                'status' => $library->status,
                'files' => $library->files_count,
                'created_by' => $user->name,
                'is_locked' => $library->is_locked,
                'created_at' => $createdAt,
                'description' => $library->description == null ? '' : $library->description,
                'size' => $library->total_size == null ? 0 : $library->total_size,
                'course_id' => $library->course_id,
                'course' => $library->course->title,
            ];
        }

        return [
            'libraries' => $data,
            'total' => $libraryCount,
        ];
    }

    /**
     * get all libraries assigned to a student
     * 
     * @param int $studentId
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getStudentLibraries($studentId, $limit, $offset)
    {
        $studentTimezone = $this->studentRepository->find($studentId)->timezone;
        $libraryCount = $this->sharedLibraryRepository->getStudentLibrariesCount($studentId);
        $libraries = $this->sharedLibraryRepository->getStudentLibraries($studentId, $limit, $offset);
        $data = array();

        foreach ($libraries as $library) {
            $assignedAt = GeneralHelper::convertTimeToUserTimezone($library->created_at, $studentTimezone);
            $data[] = [
                'file' => $library->file,
                'size' => $library->size,
                'course' => $library->course,
                'assigned_at' => Carbon::parse($assignedAt)->format('M j, Y'),
                'file_url' => $library->file_url,
            ];
        }

        return [
            'files' => $data,
            'total' => $libraryCount,
        ];
    }

    /**
     * get all libraries assigned to a teacher
     * 
     * @param int $teacherId
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getTeacherLibraries($teacherId, $limit, $offset)
    {
        $libraryCount = $this->sharedLibraryRepository->getTeacherLibrariesCount($teacherId);
        $libraries = $this->sharedLibraryRepository->getTeacherLibraries($teacherId, $limit, $offset);
        $data = array();
        
        foreach ($libraries as $library) {
            $data[] = [
                'id' => $library->id,
                'file' => $library->file,
                'size' => $library->size,
                'course' => $library->course,
                'file_url' => $library->file_url,
                'is_assigned' => $library->assigned,
            ];
        }

        return [
            'files' => $data,
            'total' => $libraryCount,
        ];
    }

     /**
      * get all teachers with a status of is_assigned as true or false
      * of the provided folder
      */
    public function getAllTeachers($folderId) {
        $teachersWithFolder = $this->sharedLibraryRepository->getAllTeachers($folderId);
        $data = array();
        foreach ($teachersWithFolder as $teacher) {
            $data[] = [
                'folder_id' => $folderId,
                'teacher_id' => $teacher->teacher_id,
                'teacher_name' => $teacher->teacher_name,
                'profile_photo_path' => $teacher->profile_photo_path,
                'is_assigned' => $teacher->is_assigned,
            ];
        }
        return $data;
    }

    /**
     * check if the files are already uploaded
     * 
     * @param array $files
     * @return array
     */
    public function checkForDuplicateFiles($files) {
        $data = array();
        foreach ($files as $file) {
            $fileName = $file->getClientOriginalName();
            $count = $this->sharedLibraryRepository->checkForDuplicateFiles($fileName);
            
            if($count > 0) {
                $data[] = $fileName;
            }
        }
        return $data;
    }

    /**
     * get the folder details
     * 
     * @param array $fileData
     * @param object $file
     * @param string $folderName
     * @return array
     */
    public function getLibraryDetails($libraryId) {
        $library = $this->sharedLibraryRepository->getLibraryDetails($libraryId);
        $data = array();
        $files = array();
        $user = $library->user;
        $timezone = $user->timezone;
        if($timezone == null) {
            $timezone = 'Asia/Karachi';
        }
        $createdAt = Carbon::parse($library->created_at)->timezone($timezone)->format('d-M, Y H:i:s');
        $filesData = $library->files;
        $totalSize = 0;
        foreach ($filesData as $file) {
            $totalSize += $file->file_size;
            $files[] = [
                'id' => $file->id,
                'title' => $file->title,
                'slug' => $file->slug,
                'file_size' => $file->file_size,
                'file_type' => $file->file_type,
                'aws_file_link' => $file->aws_file_link,
                'aws_file_name' => $file->aws_file_name,
                'created_by' => $file->created_by,
                'created_at' => Carbon::parse($file->created_at)->timezone($timezone)->format('d-M, Y H:i:s'),
            ];
        }
        $data = [
            'id' => $library->id,
            'title' => $library->title,
            'slug' => $library->slug,
            'status' => $library->status,
            'files_count' => $library->files_count,
            'created_by' => $user->name,
            'is_locked' => $library->is_locked,
            'created_at' => $createdAt,
            'description' => $library->description == null ? '' : $library->description,
            'folder_size_in_kb' => $totalSize,
            'course_id' => $library->course_id,
            'course_title' => $library->course->title,
            'files' => $files,
        ];
        return $data;
    }

    /**
     * delete the library file
     * 
     * @param int $fileId
     * @return array
     */
    public function deleteLibraryFile($fileId) {
        //is file assigned to any student
        $usageCount = $this->sharedLibraryRepository->getLibraryFileUsageCount($fileId);
        if($usageCount > 0) {
            return 'File is assigned to students';
        }
        $file = $this->sharedLibraryRepository->deleteLibraryFile($fileId);
        return $file;
    }

     public function getTeacherFolders($teacherId) {
        $folders = $this->sharedLibraryRepository->getTeacherFolders($teacherId);
        $data = array();
        foreach ($folders as $folder) {
            $filesSizes = $folder->folderFilesSizes;
            $collection = collect($filesSizes);
            $folderSize = $collection->sum('file_size');

            $data[] = [
                'id' => $folder->id,
                'title' => $folder->title,
                'slug' => $folder->slug,
                'status' => $folder->status,
                'files_count' => $folder->files_count,
                'is_locked' => $folder->is_locked,
                'created_at' => Carbon::parse($folder->created_at)->format('d-M, Y H:i:s'),
                'description' => $folder->description == null ? '' : $folder->description,
                'folder_size_in_kb' => $folderSize == null ? 0 : $folderSize,
                'course_id' => $folder->course_id,
                'course_title' => $folder->course->title,
            ];
        }
        return $data;
     }

     public function removeTeacherFolder($folderId, $teacherId) {
        //to check whether folder files are shared with students
        $folderFilesUsageCount = $this->sharedLibraryRepository->getFolderFilesUsageCount($folderId, $teacherId);

        if($folderFilesUsageCount > 0) {
            return 'Folder files are shared with students';
        }

        $this->sharedLibraryRepository->removeTeacherFolder($folderId, $teacherId);
        return 'Shared folder removed successfully';
     }

     /**
      * delete a folder completely from the aws and db
      */
     public function deleteAwsFolder($folderId) {
        $folderFilesUsageCount = $this->sharedLibraryRepository->getFolderFilesUsageOverallCount($folderId);
        if($folderFilesUsageCount > 0) {
            return 'Folder files are shared with students';
        }
        return $this->sharedLibraryRepository->deleteAwsFolder($folderId);
     }

     public function updateSharedLibrary($folderDetails, $teachers, $files, $folderId, $oldFolderName, $newFolderName, $userId) {
        return $this->sharedLibraryRepository->updateSharedLibrary($folderDetails, $teachers, $files, $folderId, $oldFolderName, $newFolderName, $userId);
     }


    /**
     * assign a library file to a student
     * 
     * @param int $fileId
     * @param int $studentId
     * @return array
     */
    public function assignLibraryFileToStudent($fileId, $studentId) 
    {
        $file = $this->sharedLibraryRepository->assignFileToStudent($fileId, $studentId);
        $studentCourse = $this->studentCourseRepository->getStudentCourse($studentId);

        // create activity log
        $activityData = array(
            'student_course_id' => $studentCourse->id,
            'activity_type' => 'file',
            'description' => 'File has been assigned to ' .$studentCourse->student->name. ' for the course ' .$studentCourse->course->title,
            'file_size' => $file->file->file_size,
            'file_name' => $file->file->title,
        );

        $this->studentCourseActivityRepository->create($activityData);

        //send email notification
        $emaiData = [
            'customer_name' => $studentCourse->student->user->name,
            'customer_email' => $studentCourse->student->user->email,
            'student_name' => $studentCourse->student->name,
            'course_name' => $studentCourse->course->title,
        ];

        dispatch(new SendFolderOrFileAssignedEmail($emaiData));

        // send notification
        Notification::create([
            'user_id' => $studentCourse->teacher->id,
            'title' => 'File Assigned',
            'type' => 'file',
            'message' => 'You have assigned a file to ' .$studentCourse->student->name. ' for the course ' .$studentCourse->course->title,
        ]);
        
        return $file;
    }

    /**
     * unassign a library file from a student
     * 
     * @param int $fileId
     * @param int $studentId
     * @return array
     */
    public function unassignLibraryFileFromStudent($fileId, $studentId) {
        $file = $this->sharedLibraryRepository->unassignFileFromStudent($fileId, $studentId);
        return $file;
    }
}