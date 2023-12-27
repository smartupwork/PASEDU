<?php
namespace App\Http\Controllers\Prestashop;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Session;
use Config;
use Lang;
require base_path("vendor/autoload.php");
use Cookie;

class CategoryController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCategoryApi(Request $request)
    {
        $partner = DB::table('pas_partner')->where('partner_name', '=', $request->partner_name)->get()->first();

        if(!$partner){
            return response()->json(['status' => false, 'message' => 'Partner does not exists.']);
        }else if(empty($partner->price_book_zoho_id)){
            return response()->json(['status' => false, 'message' => 'Partner does not have price book.']);
        }

        $categories = DB::table('pas_price_book_program_map')
            //->select(['name'])
            ->join('pas_program', 'pas_program.zoho_id', '=', 'pas_price_book_program_map.program_zoho_id')
            ->where('pas_program.status', '=', 'Active')
            ->where('pas_program.displayed_on', '=', 'All')
            ->orderBy('id', 'DESC')
            ->pluck(DB::raw('DISTINCT pas_program.category'))->toArray();

        return response()->json(['status' => true, 'data' => $categories]);
    }

}
