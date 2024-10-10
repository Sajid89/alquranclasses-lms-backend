<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LibraryFile extends Model
{
    use HasFactory;
    
    protected $table = 'library_files';

    protected $fillable = [
        'title',
        'slug',
        'file',
        'file_size',
        'file_type',
        'aws_file_link',
        'aws_file_name',
        'created_by',
        'shared_library_id',
    ];

    public function user() {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function sharedLibrary() {
        return $this->belongsTo(SharedLibrary::class, 'shared_library_id');
    }

    public function fileables() {
        return $this->hasMany(Fileable::class, 'library_file_id');
    }
}
