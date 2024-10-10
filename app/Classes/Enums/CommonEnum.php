<?php

namespace App\Classes\Enums;

class CommonEnum
{
    const RECORDS_LIMIT = 15;
    const SESSION_LIMIT = 5;

    const MAKEUP_REQUEST_BY_STUDENT = 'makeup_request_by_student';
    const MAKEUP_REQUEST_BY_TEACHER = 'makeup_request_by_teacher';
    const MAKEUP_REQUEST_PENDING = 'pending';
    const MAKEUP_REQUEST_APPROVED = 'approved';
    const MAKEUP_REQUEST_REJECTED = 'disapproved';
}
