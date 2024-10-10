<?php

namespace App\Repository;

use App\Jobs\SendFolderOrFileAssignedEmail;
use App\Models\Fileable;
use App\Models\LibraryFile;
use App\Models\Shareable as SharableFolder;
use App\Models\Shareable;
use App\Models\SharedLibrary;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Aws\S3\S3Client;
use Aws\Exception\AwsException;
use Aws\S3\Exception\S3Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PharIo\Manifest\Library;

class SharedLibraryRepository
{
    private $model;
    private $s3Client;
    private $bucket;
    public function __construct(SharedLibrary $model)
    {
        $this->model = $model;
        $this->s3Client = new S3Client([
            'version' => 'latest',
            'region'  => env('AWS_DEFAULT_REGION'),
            'credentials' => [
                'key'    => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY'),
            ],
        ]);
        $this->bucket = env('AWS_BUCKET');
    }

    /**
     * create a folder on aws
     * if succeeded, then create it locally,
     * 
     * 
     */
    
    public function createFolder($folderData) {
        $key = rtrim($folderData['title'], '/') . '/';
    
        $result = $this->s3Client->putObject([
            'Bucket' => $this->bucket,
            'Key'    => $key,
        ]);

        if ($result) {
            return $this->model->create($folderData);
        }

    }

    public function updateLibrary($requestData, $libraryId) {
        return $this->model->find($libraryId)->update($requestData);
    }

    /**
     * get all libraries 
     * with total files size
     */
    public function getLibraries($limit, $offset) 
    {
        $userId = Auth::id();

        return $this->model
        ->leftJoin('library_files', 'shared_libraries.id', '=', 'library_files.shared_library_id')
        ->select('shared_libraries.*', DB::raw('SUM(library_files.file_size) as total_size'))
        ->where('shared_libraries.created_by', $userId)
        ->groupBy('shared_libraries.id')
        ->orderBy('shared_libraries.id')
        ->with('user')
        ->limit($limit)
        ->offset($offset)
        ->get();
    }
    
    /**
     * Get all files assigned to a student
     * 
     * @param $studentId
     * @param $limit
     * @param $offset
     * @return array
     */
    public function getStudentLibraries($studentId, $limit, $offset) 
    {
        return DB::table('fileables')
            ->join('library_files', 'fileables.library_file_id', '=', 'library_files.id')
            ->join('shared_libraries', 'library_files.shared_library_id', '=', 'shared_libraries.id')
            ->join('courses', 'shared_libraries.course_id', '=', 'courses.id')
            ->select(
                'library_files.title as file',
                'library_files.file_size as size',
                'courses.title as course',
                'library_files.aws_file_link as file_url',
                'fileables.created_at'
            )
            ->where('fileables.fileable_id', $studentId)
            ->limit($limit)
            ->offset($offset)
            ->get();
    }

    /**
     * Get all files assigned to a teacher
     * 
     * @param $teacherId
     * @param $limit
     * @param $offset
     * @return array
     */
    public function getTeacherLibraries($teacherId, $limit, $offset) 
    {
        return DB::table('shareAbles')
            ->join('shared_libraries', 'shareAbles.shared_library_id', '=', 'shared_libraries.id')
            ->join('library_files', 'shared_libraries.id', '=', 'library_files.shared_library_id')
            ->join('courses', 'shared_libraries.course_id', '=', 'courses.id')
            ->select(
                'library_files.id as id',
                'library_files.title as file',
                'library_files.file_size as size',
                'courses.title as course',
                'library_files.aws_file_link as file_url',
                DB::raw('EXISTS(SELECT 1 FROM fileables WHERE fileables.library_file_id = library_files.id) as assigned')
            )
            ->where('shareAbles.shareable_id', $teacherId)
            ->where('shareAbles.shareable_type', 'App\Models\User')
            ->limit($limit)
            ->offset($offset)
            ->get();
    }

    /**
     * get all folders assigned to a teacher
     * 
     * @param $teacherId
     * @return array
     */
    public function getTeacherFolders($teacherId) {
        
        return SharedLibrary::select(
            'shared_libraries.id', 
            'shared_libraries.description', 
            'shared_libraries.files_count',
            'shared_libraries.slug', 
            'shared_libraries.status', 
            'shared_libraries.title', 
            'shared_libraries.is_locked', 
            'shareables.shareable_id', 
            'shareables.shareable_type',
            'courses.id as course_id', 
            'courses.title as course_title'
        )
        ->join('shareables', 'shareables.shared_library_id', '=', 'shared_libraries.id')
        ->join('courses', 'shared_libraries.course_id', '=', 'courses.id')
        ->where('shareables.shareable_id', $teacherId)
        ->where('shareables.shareable_type', 'App\Models\User')
        ->orderBy('shared_libraries.id', 'asc')
        ->get();
    }

    /**
     * get files count for a student
     */
    public function getStudentLibrariesCount($studentId) {
        return Fileable::where('fileable_id', $studentId)->count();
    }

    /**
     * get files count for a teacher
     */
    public function getTeacherLibrariesCount($teacherId) {
        return Shareable::where('shareable_id', $teacherId)
        ->where('shareable_type', 'App\Models\User')->count();
    }

    /**
     * get all libraries count
     */
    public function getLibrariesCount() {
        $userId = auth()->user()->id;
        return $this->model->where('created_by', $userId)->count();
    }

    public function getLibraryById($libraryId) {
        return $this->model->find($libraryId);
    }

    public function getLibraryUsageCount($libraryId) {
        return LibraryFile::where('shared_library_id', $libraryId)->count();
    }
    

    /**
     * upload library file to aws
     */
    public function uploadLibraryFileToAWS($fileData, $file, $folderName) {
        // the file name at aws s3 should be unique, thats why we are appending the current time to the file name
        $key = $folderName . '/'.time().'_'. $fileData['title'];
        $fileContent = file_get_contents($file->getRealPath());

         $result = $this->s3Client->putObject([
            'Bucket' => $this->bucket,
            'Key'    => $key,
            'Body'   => $fileContent,
        ]);
        
        if ($result) {
            $fileData['file'] = $key;
            $fileData['aws_file_link'] = $result['ObjectURL'];
            $fileData['aws_file_name'] = $key;
            return $fileData;
        }
    }

    public function getAllTeachers($folderId) {
        $teachers = User::select(
            'users.id as teacher_id',
            'users.name as teacher_name',
            'users.profile_photo_path',
            'shareables.shared_library_id',
            'shareables.shareable_id',
            DB::raw('IF(shareables.shareable_id IS NOT NULL, 1, 0) as is_assigned')
        )
        ->leftJoin('shareables', function($join) use ($folderId) {
            $join->on('users.id', '=', 'shareables.shareable_id')
                 ->where('shareables.shared_library_id', '=', $folderId)
                 ->where('shareables.shareable_type', '=', 'App\Models\User');
        })
        ->where('users.user_type', 'teacher')
        ->orderBy('users.id', 'ASC')
        ->get();

        return $teachers;
    }

     /**
      * the below method will receive an array of files data,
      * i.e files which are uploaded to aws should be inserted in db
      */
    public function insertFilesData($filesData) {
        foreach ($filesData as $fileData) {
            LibraryFile::create($fileData);
        }
    }

    /**
     * assign folder to teachers
     * 
     * @param $folderId
     * @param $teachers
     * @param $folderName
     * return void
     */
    public function assignFolderToTeachers($folderId, $teachers, $folderName) {
        foreach ($teachers as $teacherId) {
            $assigned = SharableFolder::create([
                'shared_library_id' => $folderId,
                'shareable_id' => $teacherId,
                'shareable_type' => 'App\Models\User'
            ]);

            if ($assigned) {
                $teacher = User::find($teacherId);
                $data = [
                    'teacher_email' => $teacher->email,
                    'teacher_name' => $teacher->name,
                    'folder_name' => $folderName
                ];

                dispatch(new SendFolderOrFileAssignedEmail($data));
            }
        }
    }

    /**
     * update files count in the folder
     * 
     * @param $folderId
     * @param $filesCount
     * return void
     */
    public function updateFilesCount($folderId, $filesCount) {
        return SharedLibrary::find($folderId)->update([
            'files_count' => $filesCount
        ]);
    }

    /**
     * delete a library folder
     * 
     * @param $folderId
     * return void
     */
    public function checkForDuplicateFiles($fileName) {
        return LibraryFile::where('title', $fileName)->count();
    }

    /**
     * get library details with files
     * 
     * @param $folderId
     * return void
     */
    public function getLibraryDetails($libraryId) {
        return $this->model->with('files')->find($libraryId);
    }

    /**
     * get library file usage count
     * 
     * @param $fileId
     * return void
     */
    public function getLibraryFileUsageCount($fileId) {
        return Fileable::where('library_file_id', $fileId)
        ->where('fileable_type', 'App\Models\Student')->count();  
    }

    /**
     * delete a library file
     * 
     * @param $fileId
     * return void
     */
    public function deleteLibraryFile($fileId) {
        $file = LibraryFile::where('id', $fileId)->get()->first();
        $folderId = $file->shared_library_id;
        $key = $file->file;
        $result = $this->s3Client->deleteObject([
            'Bucket' => $this->bucket,
            'Key'    => $key,
        ]);

        if ($result) {
            SharedLibrary::find($folderId)->decrement('files_count');
            return $file->delete();
        }
    }

    /**
     * get folder & files usage count
     * 
     * @param $folderId
     * @param $teacherId
     * return void
     */
    public function getFolderFilesUsageCount($folderId, $teacherId) 
    {
        $query = "select count(*) as `count` 
            from `fileables` as `f`, `shared_libraries` as `sl`, 
            `library_files` as `lf`, `shareables` as `s` 
            where `sl`.`id` = $folderId and 
            `sl`.`id` = `s`.`shared_library_id` and 
            s.shareable_id = $teacherId and 
            s.shareable_type = 'App\Models\User' and 
            `sl`.`id` = `lf`.`shared_library_id` and 
            `lf`.`id` = `f`.`library_file_id`;";
            $result = DB::select($query);
            return $result[0]->count;
    }

    /**
     * remove folder from the teacher
     * 
     * @param $folderId
     * @param $teacherId
     * return void
     */
    public function removeTeacherFolder($folderId, $teacherId) {
        return SharableFolder::where('shared_library_id', $folderId)
        ->where('shareable_id', $teacherId)
        ->where('shareable_type', 'App\Models\User')
        ->delete();
    }

    /**
     * get folder files usage overall count
     * 
     * @param $folderId
     * return void
     */
    public function getFolderFilesUsageOverallCount($folderId) {
        $query = "select count(*) as `count` 
            from `fileables` as `f`, `shared_libraries` as `sl`, 
            `library_files` as `lf`, `shareables` as `s` 
            where `sl`.`id` = $folderId and 
            `sl`.`id` = `s`.`shared_library_id` and 
            `sl`.`id` = `lf`.`shared_library_id` and 
            `f`.`library_file_id` = `lf`.`id`;";
            $result = DB::select($query);
            return $result[0]->count;
    }

    /**
    * the below method will perform following steps
    * 1. delete a folder from aws 
    * 2. delete all library files of this folder
    * 3. delete from shareables table
    * 4. delete from shared libraries table
    */
    public function deleteAwsFolder($folderId) {
        $folder = SharedLibrary::find($folderId);
        $keyPrefix = $folder->title . '/';
    
        try {
            // List all objects with the specified prefix
            $objects = $this->s3Client->listObjectsV2([
                'Bucket' => $this->bucket,
                'Prefix' => $keyPrefix,
            ]);
    
            if (!empty($objects['Contents'])) {
                // Extract the keys of the objects to delete
                $keys = array_map(function ($object) {
                    return ['Key' => $object['Key']];
                }, $objects['Contents']);
    
                // Delete the objects
                $result = $this->s3Client->deleteObjects([
                    'Bucket' => $this->bucket,
                    'Delete' => [
                        'Objects' => $keys,
                    ],
                ]);
    
                return $result;
            } else {
                return 'No objects found in the specified folder.';
            }
        } catch (S3Exception $e) {
            return 'Error deleting folder: ' . $e->getMessage();
        }

        if ($result) {
            LibraryFile::where('shared_library_id', $folderId)->delete();
            SharableFolder::where('shared_library_id', $folderId)->delete();
            $folder->delete();
            return "Folder deleted successfully";

        }
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
     * 1. validate the request
     * 2. check for duplicate files
     * 3. if folder name is changed, update the folder name in the AWS
     * 4. if folder name is not changed, we will not update the folder name in the AWS
     * 4.1 if folder name is changed, update the folder name in the database in library files table
     * 5. upload the new files to the AWS
     * 6. update the files count in the shared library
     * 7. remove all teachers from the folder
     * 7. assign the folder to the new teachers
     * 8. return the updated folder details
    */
    public function updateSharedLibrary($folderDetails, $teachers, $files, $folderId, $oldFolderName, $newFolderName, $userId) {
        if($oldFolderName != $newFolderName) {
            $key = rtrim($newFolderName, '/') . '/';

            $result = $this->s3Client->putObject([
                'Bucket' => $this->bucket,
                'Key'    => $key,
            ]);

            // List all objects in the old folder
            $objects = $this->s3Client->listObjectsV2([
                'Bucket' => $this->bucket,
                'Prefix' => $oldFolderName,
            ]);
            $newKey = '';
            if (isset($objects['Contents'])) {
                foreach ($objects['Contents'] as $object) {
                    $oldKey = $object['Key'];
                    $newKey = str_replace($oldFolderName, $newFolderName, $oldKey);
            
                    // Copy each object to the new folder
                    $this->s3Client->copyObject([
                        'Bucket' => $this->bucket,
                        'CopySource' => "{$this->bucket}/{$oldKey}",
                        'Key' => $newKey,
                    ]);
                }
            }

            // Delete the objects in the old folder after copying
            foreach ($objects['Contents'] as $object) {
                $this->s3Client->deleteObject([
                    'Bucket' => $this->bucket,
                    'Key'    => $object['Key'],
                ]);
            }
        }
        
        //to update the aws key in library files table
        $libraryFiles = LibraryFile::where('shared_library_id', $folderId)->get();
        foreach ($libraryFiles as $libraryFile) {
             // Debugging: Print the old and new values
            $oldFile = $libraryFile->file;
            $newFile = str_replace($oldFolderName, $newFolderName, $oldFile);
            $oldAwsFileName = $libraryFile->aws_file_name;
            $newAwsFileName = str_replace($oldFolderName, $newFolderName, $oldAwsFileName);
            $oldAwsFileLink = $libraryFile->aws_file_link;
            
            $newFolderName20 = str_replace(' ', '%20', $newFolderName);
            $oldFolderName20 = str_replace(' ', '%20', $oldFolderName);

            $newAwsFileLink = str_replace($oldFolderName20, $newFolderName20, $oldAwsFileLink);

            // Update the fields
            $libraryFile->file = $newFile;
            $libraryFile->aws_file_name = $newAwsFileName;
            $libraryFile->aws_file_link = $newAwsFileLink;
            $libraryFile->save();

        }

        //to upload files to AWS
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
            $filesData[] = $this->uploadLibraryFileToAWS($fileData, $file, $newFolderName);
        }
        
        //to insert files data to the database
        $this->insertFilesData($filesData);
        $newFilesCount = count($filesData);

        //to update the files count in the shared library
        $oldFilesCount = $this->model->find($folderId)->files_count;
        $this->model::find($folderId)->update([
            'files_count' => $newFilesCount + $oldFilesCount
        ]);
        
        //to remove all teachers from the folder
        SharableFolder::where('shared_library_id', $folderId)->delete();
        
        $teachers = explode(',', $teachers);
        //to assign the folder to the new teachers
        $this->assignFolderToTeachers($folderId, $teachers);
        
        return $this->updateLibrary($folderDetails, $folderId);
    }
}