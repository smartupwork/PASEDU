<?php
namespace App\Http\Controllers\Prestashop;
use App\Http\Controllers\Controller;
use App\Models\Program;
use Illuminate\Support\Facades\DB;
use Session;
use Config;
use Lang;
require base_path("vendor/autoload.php");
use Cookie;

class CourseNewController extends Controller
{

    public function updateAffiliatePrice($id_shop, $debug = 0)
    {
        Program::importShopCourse($id_shop, $debug);
        Program::cacheClear();
        Program::rebuildSearch();
    }

    public function updatePrestashopPrograms($start, $limit, $debug = 0)
    {
        $pas_programs = DB::table('pas_program AS p')
            ->select(['p.id', 'p.zoho_id', 'p.code', 'p.name', 'p.sub_title', 'p.occupation', 'p.hours', 'p.duration_value', 'p.duration_type', 'p.language', 'p.level', 'p.website_short_description', 'p.mycaa_description', 'p.outline', 'p.prerequisites', 'p.required_materials', 'p.certification', 'p.approved_offering', 'p.prepares_for_certification', 'p.general_features_and_benefits', 'p.career_description', 'p.description', 'p.category', 'p.program_type', 'p.feature_tag_line', 'p.median_salary', 'p.job_growth', 'p.right_career', 'p.learning_objectives', 'p.support_description', 'p.retail_wholesale', 'p.unite_price', 'p.service_item_not_program', 'p.certification_included', 'p.externship_included', 'p.ce_units', 'p.delivery_methods_available', 'p.avg_completion_time', 'p.technical_requirements', 'p.average_completion', 'p.accreditation', 'p.layout', 'p.tag_line', 'p.is_featured', 'p.is_best_seller', 'p.displayed_on', 'p.status'])
            ->where('p.status', '=', 'Active')
            ->where('p.displayed_on', '=', 'All')
            ->skip($start)
            ->limit($limit)
            ->get()->all();
//dd(count($pas_programs));
        foreach ($pas_programs as $pas_program) {
            $ps_product_ids = DB::connection('we_shop')->table('ps_product')
                ->where('reference', '=', $pas_program->code)
                /*->whereNull('zoho_id')
                ->whereNotNull('displayed_on')*/
                ->pluck('id_product')->toArray();

            if($debug == 1){
                dd($ps_product_ids);
            }

            if(count($ps_product_ids) > 0){
                DB::connection('we_shop')->table('ps_product')
                    ->whereIn('id_product', $ps_product_ids)
                    ->update([
                        'category' => $pas_program->category,
                        'ce_units' => $pas_program->ce_units,
                        'language' => $pas_program->language,
                        'is_featured' => $pas_program->is_featured,
                        'is_best_seller' => $pas_program->is_best_seller,
                        'displayed_on' => $pas_program->displayed_on,
                        'status' => $pas_program->status,
                        'zoho_id' => $pas_program->zoho_id,
                    ]);

                DB::connection('we_shop')->table('ps_product_lang')
                    ->whereIn('id_product', $ps_product_ids)
                    ->update([
                        'name' => $pas_program->name,
                        'description' => json_encode(Program::loadPrestaShopJsonData((array) $pas_program), JSON_UNESCAPED_SLASHES),
                        'description_short' => !empty($pas_program->tag_line) ? $pas_program->tag_line : '',
                    ]);
            }

        }
        die('Programs Updated successfully');

    }
}