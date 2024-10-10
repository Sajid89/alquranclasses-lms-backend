<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RegularClassRate extends Model
{
	use SoftDeletes;

	protected $fillable = [
		'teacher_id',
		'rate',
		'created_by',
	];

	protected $dates = ['created_at', 'deleted_at'];

	public $timestamps = true;

	public function teacher()
	{
		return $this->belongsTo(User::class, 'teacher_id');
	}

	public function creator()
	{
		return $this->belongsTo(User::class, 'created_by');
	}
    
}