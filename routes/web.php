<?php

/** @var Router $router */

use Carbon\Carbon;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});


$router->post('/broadcasting/auth', function (Illuminate\Http\Request $request) {
    return Broadcast::auth($request);
});

$router->post('/broadcasting/socket', function (Illuminate\Http\Request $request) {
    return response()->json(['socket_id' => $request->input('socket_id')]);
});


$router->group(['prefix' => 'api', 'middleware' => 'country.restriction'], function () use ($router) {
    $router->post('/signup', 'AuthController@signup');
    $router->post('/signin', 'AuthController@signin');
    $router->post('/forgot-password', 'AuthController@forgotPassword');
});

$router->group(['prefix' => 'api'], function () use ($router) {
    $router->get('/verify-email/{token}', 'AuthController@verify');
    $router->post('/resend-verification-email', 'AuthController@resendVerificationEmail');
    $router->post('/reset-password/{token}/{email}', 'AuthController@resetPassword');

    $router->get('/login/{provider}', 'Auth\SocialLoginController@redirectToProvider');
    $router->get('/login/{provider}/callback', 'Auth\SocialLoginController@handleProviderCallback');

    // pusher
    $router->post('/pusher/auth', 'ChatController@pusherAuth');
});

$router->group(['prefix' => 'api', 'middleware' => 'check.token'], function () use ($router) {

    $router->post('/refresh-token', 'AuthController@refreshToken');
    $router->post('/logout', 'AuthController@logout');

    $router->post('/verify-phone', 'SMSController@sendSMS');
    $router->post('/check-phone-verification', 'SMSController@checkVerification');

    // Student Routes
    $router->post('/teacher-list', 'AvailabilityController@getTeachersForStudent');
    $router->post('/add-student', 'StudentController@addStudent');
    $router->post('/create-trial-class', 'TrialController@createTrialClass');
    $router->post('/get-student-course-class-schedules', 'ClassesController@getStudentClassSchedulesForCourse');
    $router->post('/get-student-course-attendance', 'AttendanceController@getAttendanceForCourse');
    $router->post('/cancel-student-course-subscription', 'SubscriptionController@scheduleSubscriptionCancellation');
    $router->post('/update-student-course-subscription', 'SubscriptionController@updateSubscription');

    $router->post('/student-classes-schedule', 'ClassesController@studentClassesSchedule');
    $router->post('/student-previous-classes-schedule', 'ClassesController@studentPreviousClassesSchedule');
    $router->post('/student-upcoming-classes-schedule', 'ClassesController@studentUpcomingClassesSchedule');
    $router->post('/student-cancel-class', 'ClassesController@cancelClass');
    $router->post('/course-activity', 'ClassesController@courseActivity');
    $router->post('/student-active-courses', 'StudentController@getStudentCourses');

    $router->post('student-create-makeup-request', 'StudentController@createMakeupRequest');
    $router->post('student-makeup-requests', 'StudentController@makeupRequests');
    $router->post('student-accept-reject-makeup-request', 'StudentController@acceptRejectMakeupRequest');

    $router->post('/student-course-activity-for-student', 'StudentController@getStudentCourseActivity');


    // Customer Routes
    $router->post('/add-card', 'StripeController@addCard');
    $router->post('/create-subscription', 'StripeController@createCustomerAndSubscription');
    $router->post('/validate-coupon', 'StripeController@validateCoupon');
    $router->get('/customer-profile', 'CustomerController@customerProfile');
    $router->post('/update-customer-profile', 'CustomerController@updateCustomerProfile');
    $router->get('/stripe-cards', 'StripeController@stripeCardList');
    $router->get('/get-all-students', 'StudentController@getStudentProfiles');
    $router->post('/get-all-class-schedules', 'ClassesController@getAllClassSchedulesForCustomer');   
    $router->post('/make-card-default', 'StripeController@makeCardDefault');
    $router->delete('/delete-stripe-card', 'StripeController@deleteStripeCard');
    $router->get('/student-profiles', 'CustomerController@studentProfiles');
    $router->post('/get-notifications', 'CustomerController@getCustomerNotifications');

    //Parental Control Routes
    $router->post('/latest-parental-token', 'CustomerController@getLatestParentalToken');
    $router->post('/reset-parental-pin', 'CustomerController@resetParentalPin');

    // Subscription Routes
    $router->get('/subscription-plans', 'SubscriptionController@subscriptionPlans');
    $router->get('/enrollment-plans', 'SubscriptionController@enrollmentPlans');
    $router->put('/apply-coupon', 'StripeController@applyCoupon');
    $router->get('/get-coupons', 'StripeController@getCoupons');
    $router->get('/get-coupon-cancel-subscription', 'StripeController@getCouponCancelSubscription');
    $router->post('/change-teacher', 'StudentController@changeTeacher');

    // subscription cancelation reasons
    $router->get('/cancelation-reasons', 'CancelationReasonsController@getAllReasons');
    $router->post('/update-cancelation-reasons', 'CancelationReasonsController@updateCancelationReason');
    $router->post('/delete-cancelation-reasons', 'CancelationReasonsController@deleteCancelationReason');

    //Not To Cancel Subscription Reasons
    $router->get('/not-to-cancel-reasons', 'NotToCancelReasonsController@getAllReasons');
    $router->post('/update-not-to-cancel-reasons', 'NotToCancelReasonsController@updateNotToCancelReason');
    $router->post('/delete-not-to-cancel-reasons', 'NotToCancelReasonsController@deleteNotToCancelReason');
    $router->get('/change-teacher-reasons', 'CancelationReasonsController@getAllChangeTeacherReasons');

    //Fresh Desk
    $router->post('/create-ticket', 'FreshDeskController@createTicket');
    $router->get('/show-tickets', 'FreshDeskController@showTickets');
    $router->post('/reply-ticket', 'FreshDeskController@replyTicket');
    $router->post('/close-ticket', 'FreshDeskController@closeTicket');
    $router->get('/view-ticket/{ticket_id}', 'FreshDeskController@viewTicket');

    // Agora Routes
    $router->post('/generate-agora-token', 'AgoraController@generateToken');
    $router->post('/screen-share/start', 'ScreenShareController@startScreenShare');
    $router->post('/screen-share/stop', 'ScreenShareController@stopScreenShare');
    $router->post('/drawing/start', 'DrawingController@startRemoteScreenShareDrawing');
    $router->post('/drawing/stop', 'DrawingController@stopRemoteScreenShareDrawing');

    $router->post('/whiteboard-share/start', 'WhiteboardShareController@startWhiteboardShare');
    $router->post('/whiteboard-share/stop', 'WhiteboardShareController@stopWhiteboardShare');

    // Reports Routes
    $router->post('/print-single-invoice', 'ReportsController@printSingleInvoice');
    $router->post('/print-transaction-history', 'ReportsController@printTransactionHistory');

    // Invoice Routes
    $router->get('/transaction-history', 'InvoiceController@transactionHistory');
    $router->post('/invoice-details-single', 'InvoiceController@singleInvoiceDetails');

    // Teacher Routes
    $router->post('/update-teacher-profile', 'TeacherController@updateProfile');
    $router->get('/get-teacher-profile', 'TeacherController@getProfile');
    $router->post('/update-teacher-password', 'TeacherController@updatePassword');
    
    // class report
    $router->post('/teacher-class-report', 'AttendanceController@getAttendanceForTeacher');

    // classes
    $router->post('/teacher-previous-classes', 'ClassesController@teacherPreviousClasses');
    $router->post('/teacher-upcoming-classes', 'ClassesController@teacherUpcomingClasses');
    
    // payroll
    $router->post('/teacher-payrolls', 'PayrollController@teacherPayrolls');
    $router->get('/teacher-payroll-stats', 'PayrollController@teacherPayrollStats');
    $router->post('/get-single-payroll', 'PayrollController@getSinglePayroll');

    // payment method
    $router->post('/create-payment-method', 'TeacherPaymentMethodController@create');
    $router->post('/update-payment-method', 'TeacherPaymentMethodController@update');
    $router->get('/get-payment-method', 'TeacherPaymentMethodController@getPaymentMethod');

    // progress reports
    $router->post('/upload-progress-report', 'ProgressReportController@create');
    $router->delete('/delete-progress-report/{id}', 'ProgressReportController@delete');
    $router->post('/get-student-progress-reports', 'ProgressReportController@getStudentReports');
    $router->post('/download-progress-report', 'ProgressReportController@downloadReport');

    // students activities
    $router->get('/get-teacher-students', 'StudentController@getStudentsForTeacher');
    $router->post('/get-teacher-active-students', 'TeacherController@getActiveStudents');
    $router->post('/student-activities', 'TeacherController@getStudentActivities');

    // makeup request
    $router->post('/teacher-create-makeup-request', 'MakeupRequestController@createMakeupRequestForTeacher');
    $router->get('/teacher-makeup-requests', 'MakeupRequestController@teacherMakeupRequests');
    $router->post('/withdraw-makeup-request', 'MakeupRequestController@withdrawMakeupRequest');

    // Chat
    $router->post('/send-message', 'ChatController@sendMessage');
    $router->post('/get-messages', 'ChatController@getMessages');
    $router->post('/get-latest-message', 'ChatController@getLatestMessage');
    $router->post('/mark-messages-as-read', 'ChatController@markMessagesAsRead');
    $router->get('/get-students-with-unread-messages-count', 'TeacherController@getUsersWithUnreadMessagesCount');
    $router->get('/get-teachers-for-customer-with-unread-messages-count', 'CustomerController@getTeachersWithUnreadMessagesCount');

    // availability
    $router->post('/teacher-availability', 'AvailabilityController@getTeacherAvailability');

    // attendance
    $router->post('/create-attendance-onjoin', 'AttendanceController@createAttendanceOnJoin');
    $router->post('/create-attendance-onleave', 'AttendanceController@createAttendanceOnLeave');
    $router->post('/get-class-attendance', 'AttendanceController@getClassAttendanceLogs');


    // Teacher Coordinator
    $router->post('/get-teachers-notifications', 'TeacherCoordinatorController@getTeachersNotifications');
    $router->post('/get-teachers-todays-upcoming-classes', 'ClassesController@getCoordinatedTeachersClassSchedule');
    //all teachers list with pagination
    $router->post('/get-all-teachers', 'TeacherCoordinatorController@getListOfTeachers');

    // Teacher Coordinator - courses
    $router->post('/get-all-courses', 'CoursesController@getAllCourses');
    $router->post('/add-update-course', 'CoursesController@addUpdateCourse');

    //teacher coordinator - previous and upcoming classes of all teachers
    $router->post('/upcoming-classes-for-all-teachers', 'ClassesController@coordinatedTeacherUpcomingClasses');
    $router->post('/previous-classes-for-all-teachers', 'ClassesController@coordinatedAllTeacherPreviousClasses');

    //teacher coordinator - makeup request
    $router->post('/get-all-teachers-makeup-requests', 'MakeupRequestController@getAllTeachersMakeupRequests');
    
    //payroll of all teachers with pagination in descending order
    $router->post('/get-all-teachers-payrolls', 'PayrollController@getAllTeachersPayrolls');
    
    //students of a particular teacher
    $router->post('/get-teacher-students', 'TeacherCoordinatorController@getTeacherStudents');

    $router->post('/courses-list-of-teacher', 'TeacherCoordinatorController@getTeacherCourses');
    $router->post('/teacher-profile-data', 'TeacherCoordinatorController@getTeacherProfile');
    $router->post('/assign-course-to-teacher', 'TeacherCoordinatorController@assignCourseToTeacher');
    $router->post('/remove-course-from-teacher', 'TeacherCoordinatorController@removeCourseFromTeacher');

    //shared library
    $router->post('/get-teachers-for-shared-library', 'SharedLibraryController@getAllTeachers');

    $router->post('/add-shared-library', 'SharedLibraryController@addSharedLibrary');
    $router->post('/update-shared-library', 'SharedLibraryController@updateSharedLibrary');
    $router->post('/get-teacher-folders', 'SharedLibraryController@getTeacherFolders');
    $router->post('/remove-teacher-folder', 'SharedLibraryController@removeTeacherFolder');
    $router->post('/delete-aws-folder', 'SharedLibraryController@deleteAwsFolder');

    $router->post('/get-shared-libraries', 'SharedLibraryController@getLibraries');
    $router->post('/get-library-details', 'SharedLibraryController@getLibraryDetails');
    $router->post('/delete-library-file', 'SharedLibraryController@deleteLibraryFile');
    $router->post('/get-student-library-files', 'SharedLibraryController@getStudentLibraries');
    $router->post('/get-teacher-library-files', 'SharedLibraryController@getTeacherLibraries');
    $router->post('/assign-library-file-student', 'SharedLibraryController@assignLibraryFileToStudent');
    $router->post('/unassign-library-file-student', 'SharedLibraryController@unassignLibraryFileFromStudent');


    //teacher availability
    $router->post('/get-teacher-availability', 'TeacherCoordinatorController@getTeacherAvailability');
    $router->post('/delete-teacher-availability', 'TeacherCoordinatorController@deleteTeacherAvailability');
    
    
    // chat
    $router->get('/get-all-teachers-with-unread-messages-count', 'TeacherCoordinatorController@getTeachersWithUnreadMessagesCount');
    $router->get('/get-monitored-users', 'ChatController@getChatUsers');
});


Route::get('/auth/redirect/{provider}', function ($provider) {
    return \Laravel\Socialite\Facades\Socialite::driver($provider)->redirect();
});

Route::get('/auth/callback/{provider}', 'SocialLoginController@handleProviderCallback');
