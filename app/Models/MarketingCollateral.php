<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarketingCollateral extends Model
{
    protected $fillable = [
        'id',
        'progress_report_id',
        'contact_name',
        'contact_email',
        'partner_id',
        'is_requested_material',
        'event_date',
        'target_audience',
        'intended_outcome',
        'branding',
        'due_date',
        'project_type',
        'program_id',
        'description',
        'additional_notes',
        'purpose',
        'desired_completion_date',
        'meeting_proposed_date',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
    ];

    use HasFactory;

    protected $table = 'pas_marketing_collateral';

    public $timestamps = true;

    const REQUEST_TYPE_INST_REQUEST = 'Student Enrollment-Report Request';
    const REQUEST_TYPE_MARKETING_COLLATERAL = 'Marketing Collateral';

    const PROJECT_TYPE_BROCHURE = 'Brochure';
    const PROJECT_TYPE_CATALOG_INSERT = 'Catalog Insert';
    const PROJECT_TYPE_SOCIAL_MEDIA_POST = 'Social media post';
    const PROJECT_TYPE_POSTER = 'Poster';
    const PROJECT_TYPE_COURSE_FLIER = 'Course Flier';
    const PROJECT_TYPE_INFORMATIONAL_FLIER = 'Informational Flier';
    const PROJECT_TYPE_POSTCARD = 'Postcard';
    const PROJECT_TYPE_OTHER = 'Other';

    const BRANDING_BRANDED_WITH_WORLD_EDUCATION_LOGOS = 'Branded with World Education logos';
    const BRANDING_BRANDED_WITH_SCHOOL_LOGOS = 'Branded with School logos';
    const BRANDING_UNBRANDED = 'Unbranded';

    /**
     * @return array
     */
    public static function rules(){
        return [
            //'progress_report_id' => 'required|integer',
            'contact_name' => 'required|min:2|max:200',
            'contact_email' => 'required|min:2|max:180',
            'partner_name' => 'required|min:2|max:180',
            'partner_id' => 'required|integer',
            'is_requested_material' => 'required|integer',
            'event_date' => 'nullable|date_format:m/d/Y|after_or_equal:today',
            'due_date' => 'nullable|date_format:m/d/Y|after_or_equal:today',
            'target_audience' => 'nullable|min:2|max:255',
            'intended_outcome' => 'nullable|min:2',
            'branding' => 'nullable|integer',
            'project_type' => 'required|integer',
            'program_id' => 'nullable|integer',
            'description' => 'required|min:2|max:10000',
            'additional_notes' => 'nullable|min:2|max:10000',
            'desired_completion_date' => 'required|date_format:m/d/Y|after_or_equal:today',
            'meeting_proposed_date' => 'nullable|date_format:m/d/Y|after_or_equal:today',
            'purpose' => 'nullable|min:2|max:10000',
            'remember_me' => 'required|integer',
            'agree_with' => 'required|integer',
            //'photo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ];
    }

    /**
     * @return array
     */
    public static function attributeNames(){
        return [
            'program_id' => 'Program',
            'partner_id' => 'Partner',
        ];
    }

    public static function getProjectType(){
        return [
          1 => self::PROJECT_TYPE_BROCHURE,
          2 => self::PROJECT_TYPE_CATALOG_INSERT,
          3 => self::PROJECT_TYPE_SOCIAL_MEDIA_POST,
          4 => self::PROJECT_TYPE_POSTER,
          5 => self::PROJECT_TYPE_COURSE_FLIER,
          6 => self::PROJECT_TYPE_INFORMATIONAL_FLIER,
          7 => self::PROJECT_TYPE_POSTCARD,
          8 => self::PROJECT_TYPE_OTHER,
        ];
    }

    public static function getBrandingType(){
        return [
            1 => self::BRANDING_BRANDED_WITH_WORLD_EDUCATION_LOGOS,
            2 => self::BRANDING_BRANDED_WITH_SCHOOL_LOGOS,
            3 => self::BRANDING_UNBRANDED,
        ];
    }

    public static function getRequestType(){
        return [
            1 => strtolower(self::REQUEST_TYPE_INST_REQUEST),
            2 => strtolower(self::REQUEST_TYPE_MARKETING_COLLATERAL),
        ];
    }
}
