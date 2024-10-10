<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call('UsersTableSeeder'); //teachers
        $this->call('CustomerUsersTableSeeder'); //customers
        $this->call('StudentsTableSeeder'); //students
        $this->call('TeacherAvailabilityTableSeeder');
        $this->call('StudentAvailabilityTableSeeder');
        
        $this->call('StudentAvailabilitySlotTableSeeder');
        $this->call('TeacherAvailabilitySlotTableSeeder');
        $this->call('SubscriptionTableSeeder');
        $this->call('StudentCourseTableSeeder');
        $this->call('CourseableTableSeeder');
        $this->call('TrialClassTableSeeder');
        $this->call('TrialRequestTableSeeder');
        
        $this->call('RoutineClassTableSeeder');
        $this->call('WeeklyClassTableSeeder');
        $this->call('StudentTeacherAttendanceTrialClassTableSeeder');
        $this->call('StudentTeacherAttendanceWeeklyClassTableSeeder');
        $this->call('CancelSubscriptionRequestTableSeeder');
        $this->call('CancelSubscriptionHistoryTableSeeder');
        $this->call('ChatTableSeeder');
        $this->call('CouponUserTableSeeder');
        $this->call('CreditHistoryTableSeeder');
        $this->call('InvoiceTableSeeder');
        $this->call('MakeupRequestTableSeeder');
        $this->call('NotificationTableSeeder');
        $this->call('StudentCourseActivityTableSeeder');


        


    }
}
