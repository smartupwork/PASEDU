<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class StudentProgressReport extends Model
{
    use HasFactory;

    protected $table = 'student_progress_report';

    public $timestamps = true;

    public function instituteRequest($data){
        if(isset($data['ids']) && count($data['ids']) > 0){
            $query = DB::table('student_progress_report AS r')
                ->leftJoin('pas_marketing_collateral as mc', function($join){
                    $join->on('r.id', '=', 'mc.progress_report_id');
                })
                ->select('r.id', DB::raw('IF(request_type = 1, "Student Enrollment-Report Request", "Marketing Collateral") AS request_type_label'), 'request_type', 'requested_date', 'r.status', 'is_typical', 'mc.purpose', 'mc.desired_completion_date', 'mc.meeting_proposed_date', DB::raw('CONCAT(firstname, " ", lastname) AS requested_by'), DB::raw('CASE
            WHEN r.status = 1 THEN "Open" WHEN r.status = 2 THEN "Cancelled" WHEN r.status = 3 THEN "Completed" END AS status_label'),'pas_enrollment.subject','pas_enrollment.program_name','pas_enrollment.username')
                ->leftJoin('pas_users', function($join){
                    $join->on('requested_by', '=', 'pas_users.id');
                }) ->leftJoin('pas_enrollment', function($join){
                    $join->on('r.student_id', '=', 'pas_enrollment.id');
                });
            $student_request = $query->whereIn('r.id', $data['ids'])->get()->all();
            //dd($student_request);
            return $student_request;
        }

    }

}
