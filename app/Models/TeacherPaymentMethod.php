<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Crypt;
use Carbon\Exceptions\InvalidFormatException;

class TeacherPaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'bank_name',
        'account_title',
        'account_number',
        'id_card_no',
        'id_card_front_img',
        'id_card_back_img',
        'iban',
        'dob',
        'is_approved',
        'is_locked',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    // Mutators
    public function setBankNameAttribute($value)
    {
        $this->attributes['bank_name'] = Crypt::encryptString($value);
    }

    public function setAccountTitleAttribute($value)
    {
        $this->attributes['account_title'] = Crypt::encryptString($value);
    }

    public function setAccountNumberAttribute($value)
    {
        $this->attributes['account_number'] = Crypt::encryptString($value);
    }

    public function setIdCardNoAttribute($value)
    {
        $this->attributes['id_card_no'] = Crypt::encryptString($value);
    }

    public function setIbanAttribute($value)
    {
        $this->attributes['iban'] = Crypt::encryptString($value);
    }

    // Accessors
    public function getBankNameAttribute($value)
    {
        return Crypt::decryptString($value);
    }

    public function getAccountTitleAttribute($value)
    {
        return Crypt::decryptString($value);
    }

    public function getAccountNumberAttribute($value)
    {
        return Crypt::decryptString($value);
    }

    public function getIdCardNoAttribute($value)
    {
        return Crypt::decryptString($value);
    }
    
    public function getIdCardFrontImgAttribute($value)
    {
        return $value ? URL::asset($value) : null;
    }
    
    public function getIdCardBackImgAttribute($value)
    {
        return $value ? URL::asset($value) : null;
    }

    public function getIbanAttribute($value)
    {
        return Crypt::decryptString($value);
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}