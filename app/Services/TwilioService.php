<?php

namespace App\Services;

use Twilio\Rest\Client;

class TwilioService
{
    protected $client;
    protected $service;

    /**
     * TwilioService constructor.
     * 
     * @param Client $client
     * @param $service
     */
    public function __construct()
    {
        $this->client = new Client(env('TWILIO_SID'), env('TWILIO_TOKEN'));
        $this->service = env('TWILIO_SERVICE_SID');
    }

    /**
     * Send SMS with verification code
     *
     * @param $to
     * @return mixed
     */
    public function sendSMS($to)
    {
        return $this->client->verify->v2->services($this->service)
            ->verifications
            ->create($to, "sms");
    }

    /**
     * Check verification
     * 
     * @param $to
     * @param $code
     */
    public function checkVerification($to, $code)
    {
        $verification_check = $this->client->verify->v2->services($this->service)
            ->verificationChecks
            ->create([
                    "to" => $to,
                    "code" => $code
                ]
            );

        return $verification_check->status;
    }
}
