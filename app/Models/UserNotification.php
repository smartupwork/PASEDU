<?php

namespace App\Models;

use App\Utility;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserNotification extends Model
{
    use HasFactory;

    const READ      = 'read';
    const UNREAD    = 'unread';
    const TITLE_READ = 'title-read';

    protected $table = 'pas_user_notification';

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'relation_table',
        'foreign_key_id',
        'user_id',
        'partner_id',
        'read_status',
    ];

    public static function getUserNotifications($relation_table, $news_type = []){
        $notification = [
            'news' => [],
            'announcements' => [],
            'updates' => [],
        ];

        //if(Auth::user()->user_type == self::USER_TYPE_PARTNER || Auth::user()->user_type == self::USER_TYPE_MY_USER){

        $anouncement_for_partners = UserNotification::where([
            ['relation_table', '=', $relation_table],
            ['user_id', '=', Auth::user()->id],
            //['partner_id', '=', User::getPartnerDetail('id')],
            ['read_status', '=', UserNotification::UNREAD],
            ['status', '=', Utility::STATUS_ACTIVE],
        ])->select(['marketing_type', 'title', 'description', DB::raw('DATE_FORMAT(updated_at, "'.Utility::DEFAULT_DATE_TIME_FORMAT_MYSQL.'") AS updated_at')])
            ->leftJoin('pas_marketing', function($join){
                $join->on('foreign_key_id', '=', 'pas_marketing.id');
            })
            ->get()->toArray();

        foreach ($anouncement_for_partners as $anouncement_for_partner) {
            if(count($news_type) == 0){
                $notification[$anouncement_for_partner['marketing_type']][] = $anouncement_for_partner;
            }else if(in_array($anouncement_for_partner['marketing_type'], $news_type)){
                $notification[$anouncement_for_partner['marketing_type']][] = $anouncement_for_partner;
            }

        }
        //}
        return $notification;

    }

    public static function getStatusChangesNotifications($request_type = []){
        $notification = [
            'institute_request' => [],
            'collateral_request' => [],
        ];

        $query = UserNotification::where([
            ['relation_table', '=', 'student_progress_report'],
            ['pas_user_notification.read_status', '=', UserNotification::UNREAD],
        ])->select(['request_type', DB::raw('IF(request_type = 1, "Institution Request", "Marketing Collateral Request") AS title'), DB::raw('DATE_FORMAT(spr.updated_at, "'.Utility::DEFAULT_DATE_TIME_FORMAT_MYSQL.'") AS updated_at')]);

        if(User::isSuperAdmin() || User::isMyUser()){
            $query->where([
                ['pas_user_notification.read_status', '=', UserNotification::UNREAD],
            ])->groupBy('pas_user_notification.foreign_key_id');
        }else{
            $query->where([
                ['user_id', '=', Auth::user()->id],
                //['partner_id', '=', User::getPartnerDetail('id')],
                ['pas_user_notification.read_status', '=', UserNotification::UNREAD],
                ['status', '=', Utility::STATUS_ACTIVE],
            ]);
        }

        $query->leftJoin('student_progress_report AS spr', function($join){
            $join->on('spr.id', '=', 'pas_user_notification.foreign_key_id');
        });

        $status_changes = $query->get()->toArray();
        //print_r($status_changes);die;

        foreach ($status_changes as $status_change) {
            $request_type_key = '';
            if($status_change['request_type'] == '1'){
                $request_type_key = 'institute_request';
            }else if($status_change['request_type'] == '2'){
                $request_type_key = 'collateral_request';
            }

            if(count($request_type) == 0){
                $notification[$request_type_key][] = $status_change;
            }else if(in_array($status_change['request_type'], $request_type)){
                $notification[$request_type_key][] = $status_change;
            }

        }

        return $notification;
    }
}
