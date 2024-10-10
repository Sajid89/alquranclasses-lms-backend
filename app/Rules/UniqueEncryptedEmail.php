<?php
namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;

class UniqueEncryptedEmail implements Rule
{
    public function passes($attribute, $value)
    {
        // Normalize and hash the email to check for uniqueness
        $hashedEmail = hash('sha256', strtolower(trim($value)));
        return !User::where('email_hash', $hashedEmail)->exists();
    }

    public function message()
    {
        return 'The :attribute has already been taken.';
    }
}