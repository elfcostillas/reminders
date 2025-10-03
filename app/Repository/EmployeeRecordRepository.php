<?php

namespace App\Repository;

use Illuminate\Support\Facades\DB;

class EmployeeRecordRepository
{
    //
    public function getEmployeesWithContactNo()
    {
        $collection = DB::table('employees')
                    ->select(DB::raw("biometric_id,contact_no,
                        LPAD(REPLACE(REPLACE(contact_no,'-',''),' ',''),11,0) as format_contact,
                        concat('+63',RIGHT(LPAD(REPLACE(REPLACE(contact_no, '-', ''), ' ', ''), 11, 0),10)) as ph_format,
                        concat(lastname,', ',firstname) as emp_name"))
                    ->where('exit_status',1)
                    ->where('contact_no','!=','');
        return $collection;    
    }

    public function getEmployee($biometric_id)
    {
        $collection = $this->getEmployeesWithContactNo()
            ->where('biometric_id','=',$biometric_id);

        return $collection->first();    
    }




}
