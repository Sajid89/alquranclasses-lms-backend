<?php
namespace App\Http\Resources;

use App\Traits\DecryptionTrait;
use Illuminate\Http\Resources\Json\JsonResource;
/**
 * CustomerResource class
 *
 * This class extends the JsonResource class provided by Laravel.
 * It is used to transform the customer data before sending it in the response.
 * The toArray method defines the specific data about the customer that should be included in the response.
 *
 * @author Babu Khan
 */
class CustomerResource extends JsonResource
{
    use DecryptionTrait;

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'                       => $this->id,
            'name'                     => $this->decryptValue($this->name),
            'phone'                    => $this->decryptValue($this->phone),
            'phone_number_verified_at' => $this->phone_number_verified_at,
            'profile_photo_path'       => $this->profile_photo_path ? env('APP_URL').'/'.$this->profile_photo_path : null, 
            'email'                    => $this->decryptValue($this->email),
            'gender'                   => $this->gender,
            'secondary_phone'          => $this->decryptValue($this->secondary_phone),
            'secondary_email'          => $this->decryptValue($this->secondary_email),
            'customer_pin'             => $this->customer_pin, 
            'user_type'                => $this->user_type,
            'status'                   => $this->status, 
            'country'                  => $this->country, 
            'social_type'              => $this->social_type, 
            'social_id'                => $this->social_id,
            'parental_lock'            => $this->parental_lock,
            'parental_lock_pin'        => $this->parental_lock_pin,
            'stripe_id'                => $this->stripe_id,
        ];
 
    }
}