<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SharedLibrary extends Model
{
    use HasFactory;
    
    protected $table = 'shared_libraries';

    protected $fillable = [
        'title',
        'slug',
        'status',
        'files_count',
        'created_by',
        'is_locked',
        'course_id',
        'description',
    ];

    public function user() {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function course() {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function files() {
        return $this->hasMany(LibraryFile::class, 'shared_library_id');
    }

    public function shareables() {
        return $this->hasMany(Shareable::class, 'shared_library_id');
    }

    public function folderFilesSizes() {
        return $this->hasMany(LibraryFile::class, 'shared_library_id')
        ->select('file_size', 'shared_library_id');
    }
}
