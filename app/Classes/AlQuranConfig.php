<?php

namespace App\Classes;

use App\Classes\Enums\UserTypesEnum;

class  AlQuranConfig
{
    public const Locales = [
        'en' => 'English',
        'ur' => 'Urdu'
    ];
    public const DefaultLocale = 'en';
    public const MaxProfiles = 6;
    public const MaxSlots = 5;
    public const MinAge = 3;
    public const MaxAge = 100;
    public const TimeSlot = 30;
    public const SlotPrice = 35;
    public const UI_AVATAR_URL = 'https://ui-avatars.com/api/';
    public const DefaultZoomLink = 'https://zoom.us/j/4078332260?pwd=UkUvVmYwQjhpcU9EM25lWTNWTEV5dz09';
    public const MinSecondsForAttendedClass = 1200;   // 20 mins
    public const MinSecondsForAttendedStudent = 1200;   // 20 mins
    public const MinSecondsForAttendedTeacher = 1200;   // 20 mins

    public const MaxUnverifiedAccountsPerIP = 4;    // 4 unverified accounts per IP

    public const RescheduleTrialLimitCount = 2;    // 2 reschedule trial limit

    public const DaysMin = [
        1 => 'Mon',
        2 => 'Tue',
        3 => 'Wed',
        4 => 'Thu',
        5 => 'Fri',
        6 => 'Sat',
        7 => 'Sun',
    ];

    public const Days = [
        1 => 'Monday',
        2 => 'Tuesday',
        3 => 'Wednesday',
        4 => 'Thursday',
        5 => 'Friday',
        6 => 'Saturday',
        7 => 'Sunday',     // if you want to off sunday dont write here
    ];

    public const Shifts = [
        1 => '05:00 AM - 08:00 AM',
        2 => '08:00 AM - 11:00 AM',
        3 => '11:00 AM - 14:00 PM',
        4 => '14:00 PM - 17:00 PM',
        5 => '17:00 PM - 20:00 PM',
        6 => '20:00 PM - 23:00 PM',
    ];

    public static $File_Type  =
    [
        'png'    => 'primary',
        'jpg'  => 'primary',
        'jpeg'  => 'primary',
        'pdf'   => 'warning',
        'doc'   => 'success',
        'docx'   => 'success',
        ''  => 'info'
    ];

    public static $File_Type_Icon  =
    [
        'png'    => '-image',
        'jpg'  => '-image',
        'jpeg'  => '-image',
        'pdf'   => '-pdf',
        'doc'   => '-word',
        'docx'   => '-word',
        ''  => '-alt'
    ];

    public const ColorClasses = ['primary', 'success', 'info', 'warning', 'danger', 'default'];

    public const Add = 'add';
    public const Edit = 'edit';
    public const View = 'view';
    public const Delete = 'delete';
    public const Assign = 'assign';

    public const BASIC_ACTIONS = [self::Add, self::Edit, self::View, self::Delete];

    public static $Modules = [
        'Dashboard'                 => [self::View],
        'class-schedule'            => [self::View],
        'users'                     => self::BASIC_ACTIONS,
        'courses'                   => self::BASIC_ACTIONS,
        'subscription-plans'        => self::BASIC_ACTIONS,
        'shared-library'            => self::BASIC_ACTIONS,
        'roles'                     => [self::Add, self::Edit, self::Edit, self::Delete, self::Assign],
        'permissions'               => [self::Add, self::Edit, self::Edit, self::Delete, self::Assign],
        'settings'                  => self::BASIC_ACTIONS,
    ];

    public static $Can_access_admin = [
        UserTypesEnum::Admin,
        UserTypesEnum::CustomerSupport,
        UserTypesEnum::TC,
        UserTypesEnum::SalesSupport,
        // UserTypesEnum::Sales,
    ];

    public static $Can_Filters = [
        'tc' => [
            [
                '0' => 'user_type',
                '1' => '=',
                '2' => 'teacher'
            ]
        ],
        'customersupport' => [
            '0' => 'user_type',
            '1' => '=',
            '2' => 'customer'
        ]
    ];

}
