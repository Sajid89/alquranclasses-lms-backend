<?php

namespace App\Models;

use App\Session;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Laravel\Lumen\Auth\Authorizable;
use Laravel\Passport\HasApiTokens;
use Laravel\Passport\PersonalAccessTokenFactory;

class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'gender',
        'password',
        'user_type',
        'phone',
        'secondary_phone',
        'is_locked',
        'ip',
        'country',
        'coordinated_by',
        'secondary_email',
        'verification_token',
        'verification_token_expires_at',
        'secondary_email_verified_at',
        'change_email_verify',
        'customer_pin',
        'social_type',
        'social_id',
        'email_verified_at',
        'timezone',
        'pf',
        'pn',
        'pin_check',
        'requester_id',
        'medium',
        'term',
        'campaign',
        'otp_created_at',
        'stripe_id'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // Mutators and Accessors for encryption and decryption
    // public function setNameAttribute($value)
    // {
    //     $this->attributes['name'] = Crypt::encryptString($value);
    // }

    // public function getNameAttribute($value)
    // {
    //     return $this->decryptIfNeeded($this->attributes['name']);
    // }

    // public function setEmailAttribute($value)
    // {
    //     $this->attributes['email'] = Crypt::encryptString($value);
    //     $this->attributes['email_hash'] = hash('sha256', strtolower(trim($value)));
    // }

    // public function getEmailAttribute($value)
    // {
    //     return $this->decryptIfNeeded($this->attributes['email']);
    // }

    // public function setSecondaryEmailAttribute($value)
    // {
    //     $this->attributes['secondary_email'] = Crypt::encryptString($value);
    // }

    // public function getSecondaryEmailAttribute($value)
    // {
    //     return $this->decryptIfNeeded($this->attributes['secondary_email']);
    // }

    // public function setPhoneAttribute($value)
    // {
    //     $this->attributes['phone'] = Crypt::encryptString($value);
    // }

    // public function getPhoneAttribute($value)
    // {
    //     return $this->decryptIfNeeded($this->attributes['phone']);
    // }

    // public function setSecondaryPhoneAttribute($value)
    // {
    //     $this->attributes['secondary_phone'] = Crypt::encryptString($value);
    // }

    // public function getSecondaryPhoneAttribute($value)
    // {
    //     return $this->decryptIfNeeded($this->attributes['secondary_phone']);
    // }

    // // Helper function to decrypt if needed
    // private function decryptIfNeeded($value)
    // {
    //     try {
    //         return Crypt::decryptString($value);
    //     } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
    //         return $value; // Return the original value if it's not encrypted
    //     }
    // }

    public function createToken($name, array $scopes = [])
    {
        $token = app(PersonalAccessTokenFactory::class)->make(
            $this->getKey(), $name, $scopes
        );

        // Comment out the following line to prevent revoking existing tokens
        // app(TokenRepository::class)->revokeOtherAccessTokens(
        //     $this, $token, false
        // );

        return $token;
    }

    // Relationships
    /**
     * Get the teacher's availability
     *
     * @return string
     */
    public function availability()
    {
        return $this->morphOne(Availability::class, 'available');
    }

    /**
     * Get the teacher's courses
     *
     * @return MorphToMany
     */
    public function courses()
    {
        return $this->morphToMany(Course::class, 'courseable');
    }

    /**
     * Get all of the students for a teacher
     *
     * @return HasMany
     */
    public function customerStudents()
    {
        return $this->hasMany(Student::class, 'user_id');
    }

    public function customerTrialClasses()
    {
        return $this->hasMany(TrialClass::class, 'customer_id');
    }

    /**
     * Get all of the students for a teacher
     *
     * @return HasMany
     */
    public function students()
    {
        return $this->hasMany(Student::class, 'teacher_id');
    }

    /**
     * Get all of the student courses for a teacher
     *
     * @return HasMany
     */
    public function studentCourses()
    {
        return $this->hasMany(StudentCourse::class, 'teacher_id');
    }

    /**
     * Get attendance for the User(teacher)
     * @return MorphMany
     */
    public function attendance()
    {
        return $this->morphMany(Attendance::class, 'person');
    }

    /**
     * Get all of the user's used coupons.
     *
     * @return MorphMany
     */
    public function usedCoupons()
    {
        return $this->belongsToMany(Coupon::class, 'coupon_user')->withTimestamps();
    }

    /**
     * Get all of the user's trial classes.
     *
     * @return HasMany
     */
    public function trialClasses()
    {
        return $this->hasMany(TrialClass::class, 'teacher_id');
    }

    /**
     * Get all of the user's weekly classes.
     *
     * @return HasMany
     */
    public function weeklyClasses()
    {
        return $this->hasMany(WeeklyClass::class, 'customer_id');
    }

    public function teacherWeeklyClasses()
    {
        return $this->hasMany(WeeklyClass::class, 'teacher_id');
    }

    public function stripeCards()
    {
        return $this->hasMany(StripeCard::class, 'customer_id');
    }

    public function sessions()
    {
        return $this->hasMany(DeviceSession::class, 'user_id');
    }

    public function parentalPinTokens()
    {
        return $this->hasMany(ParentalPinToken::class, 'user_id');
    }

    public function teacherPaymentMethod()
    {
        return $this->hasOne(TeacherPaymentMethod::class);
    }

    public function teacherPayrolls()
    {
        return $this->hasMany(TeacherPayroll::class, 'teacher_id');
    }

    public function payrollUpdatedBy()
    {
        return $this->hasMany(TeacherPayroll::class, 'updated_by');
    }

    public function trialClassRateUpdatedBy()
    {
        return $this->hasMany(TrialClassRate::class, 'updated_by');
    }

    public function regularClassRateUpdatedBy()
    {
        return $this->hasMany(RegularClassRate::class, 'updated_by');
    }

    public function teacherRegularClassRates()
    {
        return $this->hasMany(RegularClassRate::class, 'teacher_id');
    }

    public function sharedLibraries()
    {
        return $this->hasMany(SharedLibrary::class, 'created_by');
    }
    public function teacherCoordinator()
    {
        return $this->belongsTo(User::class, 'coordinated_by');
    }
}
