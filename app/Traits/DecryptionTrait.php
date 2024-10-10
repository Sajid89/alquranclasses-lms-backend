<?php
namespace App\Traits;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

trait DecryptionTrait
{
    /**
     * Decrypt the given value.
     *
     * @param  string  $value
     * @return string
     */
    public function decryptValue($value)
    {
        
        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            
            return $value;
        }
    }
}