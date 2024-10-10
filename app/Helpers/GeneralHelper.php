<?php

namespace App\Helpers;

use Carbon\Carbon;
use DateTime;
use DateTimeZone;
use Exception;

class GeneralHelper
{
    /**
     * Convert the given time to UTC timezone
     *
     * @param string $time
     * @param string $timezone
     * @return string
     * @throws Exception
     */
    public static function convertTimeToUTCzone($time, $timezone = 'Africa/Cairo')
    {
        if (!in_array($timezone, timezone_identifiers_list())) {
            throw new Exception("Invalid timezone: $timezone");
        }

        $date = new DateTime($time, new DateTimeZone($timezone));
        $date->setTimezone(new DateTimeZone('UTC'));
        return $date->format('Y-m-d H:i:s');
    }

    /**
     * Convert the given time to the user's timezone
     *
     * @param string $time
     * @param string $timezone
     * @return string
     * @throws Exception
     */
    public static function convertTimeToUserTimezone($time, $timezone)
    {
        if (!in_array($timezone, timezone_identifiers_list())) {
            throw new Exception("Invalid timezone: $timezone");
        }

        $date = new DateTime($time, new DateTimeZone('UTC'));
        $date->setTimezone(new DateTimeZone($timezone));
        return $date->format('Y-m-d H:i:s');
    }

    /**
     * Upload a image
     *
     * @param $file
     * @param $path
     * @return string
     */
    public static function uploadProfileImage($file, $path) {
        $filename = time() . '_' . $file->getClientOriginalName();
        $file->move(public_path($path), $filename);
        return $path . '/' . $filename;
    }

    /**
     * Delete an image
     *
     * @param string $path The relative path to the image within the public directory
     * @return bool True if the file was successfully deleted or does not exist, false on failure.
     */
    public static function deleteImage($path) {
        $fullPath = public_path($path);
        if (file_exists($fullPath)) {
            return unlink($fullPath);
        }
        return true;
    }

    /**
     * the below function will get current date time in UTC 
     * and will convert it to unix timestamp
     * the second responsibility is to convert the class date time to unix timestamp
     */
    public static function convertDateTimeToUnixTimestamp($classDateTime) {
        $classTimeUnix = strtotime($classDateTime);

        $currentDateTime = Carbon::now('UTC')->format('Y-m-d H:i:s');
        $currentDateTimeUnix = strtotime($currentDateTime);
        $data = array(
            'currentDateTimeUnix' => $currentDateTimeUnix,
            'classDateTimeUnix' => $classTimeUnix
        );
        return $data;
    }

    /**
     * the below function will receive a current datetime in utc and a created at datetime in utc
     * and will calculate the difference between them in number of days, hours and minutes
     */
    public static function calculateTimeDifference($currentDateTime, $createdAt) {
        // Convert strings to Carbon instances
        $start = Carbon::createFromFormat('Y-m-d H:i:s', $createdAt);
        $end = Carbon::createFromFormat('Y-m-d H:i:s', $currentDateTime);

        // Calculate the difference
        $diffInDays = $start->diffInDays($end);
        $diffInHours = $start->copy()->addDays($diffInDays)->diffInHours($end);
        $diffInMinutes = $start->copy()->addDays($diffInDays)->addHours($diffInHours)->diffInMinutes($end);

        // Construct the difference string
        $diffString = '';
        if($diffInDays > 0) {
            $diffString = "{$diffInDays} days, {$diffInHours} hours, and {$diffInMinutes} minutes ago.";
        } else {
            $diffString = "{$diffInHours} hours, and {$diffInMinutes} minutes ago.";
        }

        return $diffString;
    }

    public static function getAvailabilityTime($time)
    {
        $time = Carbon::parse($time);
        return $time->format('h:i A') . ' - ' . $time->addMinutes(30)->format('h:i A');
    }
}