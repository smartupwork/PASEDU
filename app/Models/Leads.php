<?php

namespace App\Models;

use App\EmailHelper;
use App\EmailRequest;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Leads extends Model
{
    use HasFactory;

    protected $table = 'pas_leads';

    public $timestamps = false;

    // Financing Needs Constants
    const FN_NA                                         = 'N/A';
    const FN_I_NEED_A_LOAN                              = 'I need a loan';
    const FN_I_ALREADY_HAVE_SECURED_FUNDING             = 'I already have secured funding';
    const FN_I_NEED_HELP_WITH_WIA                       = 'I need help with WIA';
    const FN_I_NEED_HELP_WITH_VOCATIONAL_REHABILITATION = 'I need help with Vocational Rehabilitation';
    const FN_I_NEED_HELP_WITH_MYCAA                     = 'I need help with MyCAA';
    const FN_I_NEED_HELP_WITH_TA                        = 'I need help with TA';
    const FN_I_NEED_HELP_WITH_AIR_FORCE_COOL            = 'I need help with Air Force COOL';
    const FN_I_NEED_HELP_WITH_GI_BILL                   = 'I need help with GI Bill';
    const FN_I_NEED_HELP_WITH_ANOTHER_FUNDING_SOURCE    = 'I need help with another funding source';

    // Category of Interest Constants
    const COI_NA = "N/A";
    const COI_BUSINESS_AND_PROFESSIONAL = "Business and Professional";
    const COI_HEALTH_CARE_AND_FITNESS = "Healthcare and Fitness";
    const COI_HOSPITALITY_AND_GAMING = "Hospitality and Gaming";
    const COI_IT_AND_SOFTWARE_DEVELOPMENT = "IT and Software Development";
    const COI_MANAGEMENT_AND_CORPORATE = "Management and Corporate";
    const COI_MEDIA_AND_DESIGN = "Media and Design";
    const COI_SKILLED_TRADES_AND_INDUSTRIAL = "Skilled Trades and Industrial";
    const COI_SUSTAINABILITY = "Sustainability";
    const COI_TOEFL_TEST_PREPARATION = "TOEFL Test Preparation";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'partner_institution',
        'name_of_requester',
        'email_of_requester',
        'firstname',
        'lastname',
        'email',
        'address',
        'phone',
        'country',
        'interested_program',
        'financing_needs',
        'category_of_interest',
        'time_zone',
        'inquiry_message',
        'added_by',
        'added_date',
    ];

    public static function getFinancingNeeds(){
        return [
            self::FN_NA,
            self::FN_I_NEED_A_LOAN,
            self::FN_I_ALREADY_HAVE_SECURED_FUNDING,
            self::FN_I_NEED_HELP_WITH_WIA,
            self::FN_I_NEED_HELP_WITH_VOCATIONAL_REHABILITATION,
            self::FN_I_NEED_HELP_WITH_MYCAA,
            self::FN_I_NEED_HELP_WITH_TA,
            self::FN_I_NEED_HELP_WITH_AIR_FORCE_COOL,
            self::FN_I_NEED_HELP_WITH_GI_BILL,
            self::FN_I_NEED_HELP_WITH_ANOTHER_FUNDING_SOURCE,
        ];
    }

    public static function getCategoryOfInterest(){
        return [
            self::COI_NA,
            self::COI_BUSINESS_AND_PROFESSIONAL,
            self::COI_HEALTH_CARE_AND_FITNESS,
            self::COI_HOSPITALITY_AND_GAMING,
            self::COI_IT_AND_SOFTWARE_DEVELOPMENT,
            self::COI_MANAGEMENT_AND_CORPORATE,
            self::COI_MEDIA_AND_DESIGN,
            self::COI_SKILLED_TRADES_AND_INDUSTRIAL,
            self::COI_SUSTAINABILITY,
            self::COI_TOEFL_TEST_PREPARATION,
        ];
    }

    public function getTableName(){
        return $this->table;
    }

}
