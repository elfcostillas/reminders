<?php

namespace App\Repository;

use Illuminate\Support\Facades\DB;

class DailyTimeRecordRepository
{
    //
    public function someFunction()
    {
        dd('here to');
    }

    public function getIncompleteLogs($employees,$yesterday)
    {
    
        $logs = DB::table('edtr_detailed')
            ->where('dtr_date','=',$yesterday->format('Y-m-d'))
            ->whereIn('biometric_id',$employees)
            ->where(function($query){
                $query->orWhere('time_in','=','');
                $query->orWhere('time_out','=','');
                $query->orWhereNull('time_out','=','');
                $query->orWhereNull('time_out','=','');
            });

        return $logs
        ->get();
    }
}
