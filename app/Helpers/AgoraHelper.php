<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;
use Willywes\AgoraSDK\RtcTokenBuilder;

class AgoraHelper
{
    public static function GetToken($user_id, $channelName)
    {
        $appID = env('AGORA_APP_ID');
        $appCertificate = env('AGORA_APP_CERTIFICATE');
        $uid = $user_id;
        $role = RtcTokenBuilder::RoleAttendee;
        $expireTimeInSeconds = 3600; // 1 hour
        $currentTimestamp = (new \DateTime("now", new \DateTimeZone('UTC')))->getTimestamp();
        $privilegeExpiredTs = $currentTimestamp + $expireTimeInSeconds;

        return RtcTokenBuilder::buildTokenWithUid($appID, $appCertificate, $channelName, $uid, $role, $privilegeExpiredTs);
    }
}