<?php

namespace App\Service;

use App\Repository\DailyTimeRecordRepository;
use App\Repository\EmployeeRecordRepository;
use App\Repository\ReminderRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReminderService
{
    //
    protected $employeeArray = [];

    public function __construct(
        protected ReminderRepository $repo,
        protected DailyTimeRecordRepository $dtr_repo,
        protected EmployeeRecordRepository $emp_repo
    ) 
    {
        
    }

    public function run()
    {
        $to_send = [];

        $yesterday = now()->subDay();

        if($yesterday->shortEnglishDayOfWeek != 'Sun')
        {
            $this->buildEmployeeArray();

            $employess = $this->emp_repo->getEmployeesWithContactNo()
                        ->pluck('biometric_id');

            $incoplete_logs = $this->dtr_repo->getIncompleteLogs($employess,$yesterday);

            foreach($incoplete_logs as $incoplete_log)
            {
               
                // dd($incoplete_log->dtr_date);

                $date = Carbon::createFromFormat('Y-m-d',$incoplete_log->dtr_date);

                
                $msg = $this->msg_factory($incoplete_log);
                $title = $this->title_factory($incoplete_log);
               
                $msg_object = [
                    'biometric_id' => $this->employeeArray[$incoplete_log->biometric_id]->biometric_id,
                    'employee_name' => $this->employeeArray[$incoplete_log->biometric_id]->emp_name,
                    'contact_no' => $this->employeeArray[$incoplete_log->biometric_id]->ph_format,
                    'msg' => $msg,
                    'created_on' => now(),
                    'work_date' => $incoplete_log->dtr_date,
                    'work_date_f' => $date->format('m/d/Y'),
                    'title' => $title
                ];

    //                 +"biometric_id": 158
    // +"contact_no": "0926-732-8311"
    // +"format_contact": "09267328311"
    // +"ph_format": "+639267328311"
    // +"emp_name": "Abalorio, Egllen"

                array_push($to_send,$msg_object);
            }

        
            if(count($to_send) > 0)
            {
                DB::table('sms')->insertOrIgnore($to_send);
            }
        }
     
       


        
    }

    public function buildEmployeeArray()
    {

        $employees = $this->emp_repo->getEmployeesWithContactNo()->get();

        foreach($employees as $employee)
        {
            $this->employeeArray[$employee->biometric_id] = $employee;
        }

        // if(!array_key_exists($biometric_id,$this->employeeArray))
        // {
            
        // }

    }

    public function title_factory($log_object)
    {
        $msg = "";

        if($this->noTimeIn($log_object) && $this->noTimeOut($log_object))
        {
            $msg = 'no time in and no time out';
        }
         
        if($this->noTimeIn($log_object) && !$this->noTimeOut($log_object))
        {
            $msg = 'no time in';
        }

        if(!$this->noTimeIn($log_object) && $this->noTimeOut($log_object))
        {
            $msg = 'no time timeout';
        }

        return $msg;
    }

    public function msg_factory($log_object)
    {
        $msg = "";
        $date = Carbon::createFromFormat('Y-m-d',$log_object->dtr_date);

        if($this->noTimeIn($log_object) && $this->noTimeOut($log_object))
        {
            // $msg = "This is to inform you that the system has detected that you have failed to clock-in and clock-out last {$date->format('m/d/Y')}.";
           
            $msg = "Subject: Missed Clock-In and Out Notification – Action Required

                This is to inform you that the system has detected a missed clock-in and out on 09/18/2025. Please submit FTP (Failure to Punch) or Leave form within 24 to 48 hours upon receiving this message, and forward it to the HR Office or your designated department representative.

                Note: This is a system-generated message. Please do not reply.";
        }
         
        if($this->noTimeIn($log_object) && !$this->noTimeOut($log_object))
        {
            // $msg = "This is to inform you that the system has detected that you have failed to clock-in last {$date->format('m/d/Y')}.";

            $msg = "Subject: Missed Clock-In Notification – Action Required

                    This is to inform you that the system has detected a missed clock-in on {$date->format('m/d/Y')}. Please submit FTP (Failure to Punch) form within 24 to 48 hours upon receiving this message, and forward it to the HR Office or your designated department representative.

                    Note: This is a system-generated message. Please do not reply.";
        }

        if(!$this->noTimeIn($log_object) && $this->noTimeOut($log_object))
        {
            // $msg = "This is to inform you that the system has detected that you have failed to clock-out last {$date->format('m/d/Y')}.";
            $msg = "Subject: Missed Clock-Out Notification – Action Required

                    This is to inform you that the system has detected a missed clock-out on {$date->format('m/d/Y')}. Please submit FTP (Failure to Punch) form within 24 to 48 hours upon receiving this message, and forward it to the HR Office or your designated department representative.

                    Note: This is a system-generated message. Please do not reply.";

        }

        // return $msg . " ** This is a system-generated message. Please don't reply. **";
        return $msg;
    }

    public function noTimeIn($log_object)
    {
        if($log_object->time_in == '' || is_null($log_object->time_in)){
            return true;   
        }else{
            return false;
        }
    }

    public function noTimeOut($log_object)
    {
        if($log_object->time_out == '' || is_null($log_object->time_out)){
            return true;   
        }else{
            return false;
        }
    }


}
