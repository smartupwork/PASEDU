<?php
namespace App\Http\Controllers\Prestashop;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserAccess;
use App\Utility;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Session;
use Config;
use Lang;
require base_path("vendor/autoload.php");
use Cookie;
use Prestashop;

class BannerController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getBannerApi(Request $request)
    {
        $partner = DB::table('pas_partner')->where('partner_name', '=', $request->partner_name)->value('id');

        /*if(!$partner){
            return response()->json(['status' => false, 'message' => 'Partner does not exists.']);
        }*/

        $banner = DB::table('ps_banner')->select(['title', 'description', 'media_file', 'link', 'open_new_tab'])
            //->where('partner_id', '=', $partner)
            ->where('is_active', '=', 1)
            ->orderBy('id', 'DESC')
            ->get()->first();

        $banner_notification = [];

        if($partner){
            $banner_notification = DB::table('ps_configuration')
                ->select(['content'])
                ->where('partner_id', '=', $partner)
                ->where('type', '=', 'banner-notification')
                ->where('is_active', '=', 1)
                ->value('content');
        }


        if($banner && $banner->media_file){
            $banner->media_file = env('S3_PATH').'ps-banner/'.$banner->media_file;
        }

        return response()->json(['status' => true,
            'data' => [
                'banner' => $banner,
                'banner_notification' => $banner_notification
            ]
        ]);
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {

        /*$ps_shop_category = DB::connection('we_shop')
            ->table('ps_category_shop')
            ->join('ps_category', 'ps_category.id_category', '=', 'ps_category_shop.id_shop')
            ->join('ps_category_lang', 'ps_category_lang.id_category', '=', 'ps_category_shop.id_category')
            //->join('ps_shop', 'ps_shop.id_shop', '=', 'ps_category_shop.id_shop')
            //->where('ps_category_shop.id_shop', '=', 111)
            //->where('ps_category_lang.id_lang', '=', 1)
            //->where('ps_category_shop.position', '=', 2)
            ->limit(100)
            ->get()->all();
        dd($ps_shop_category);
        $group_data = collect($ps_shop_category)->groupBy('id_shop');
        dd($group_data);*/

        if(!UserAccess::hasAccess(UserAccess::PARTNER_ADMIN_ACCESS, 'view')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }
        $banners = DB::table('ps_banner')
            //->where('partner_id', '=', User::getPartnerDetail('id'))
            ->get()->all();

        return view('prestashop.banner.index', compact('banners'));
    }


    public function create()
    {
        if(!UserAccess::hasAccess(UserAccess::PARTNER_ADMIN_ACCESS, 'add')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }

        return view('prestashop.banner.create');
    }

    public function update(Request $request)
    {
        if(!UserAccess::hasAccess(UserAccess::PARTNER_ADMIN_ACCESS, 'update')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }
        $edit_record = DB::table('ps_banner')
            ->where('id', '=', pas_decrypt($request->id))
            //->where('partner_id', '=', User::getPartnerDetail('id'))
            ->get()->first();
        if(!$edit_record){
            return redirect(route('prestashop-banner'));
        }
        return view('prestashop.banner.update', compact('edit_record'));
    }


    public function save(Request $request){
        if(!UserAccess::hasAccess(UserAccess::PARTNER_ADMIN_ACCESS, 'add')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }

        if($_POST){
            $post = $request->banner;
            //$post['partner_id'] = User::getPartnerDetail('id');

            if(empty($post['title'])){
                return response()->json(["status"=> "fail", "message"=>"Please enter title."]);
            }else if(strlen($post['title']) > 100){
                return response()->json(["status"=> "fail", "message"=>"Please enter no more than 100 characters."]);
            }

            if(empty($post['description'])){
                return response()->json(["status"=> "fail", "message"=>"Please enter description."]);
            }else if(strlen($post['description']) > 100){
                return response()->json(["status"=> "fail", "message"=>"Please enter no more than 100 characters."]);
            }

            $image = $post['media_file'];
            if(empty($request->id) && !$image){
                return response()->json(["status"=> "fail", "message"=>"Please select banner image."]);
            }

            if($image){
                $s3 = \Storage::disk('s3');
                $file_name = uniqid() .'.'. $image->getClientOriginalExtension();
                $s3filePath = '/ps-banner/' . $file_name;
                $s3->put($s3filePath, file_get_contents($image), 'public');
                $post['media_file'] = $file_name;
            }else{
                $post['media_file'] = $request->old_media_file;
            }

            $post['open_new_tab'] = isset($post['open_new_tab']) ? 1:0;
            $post['is_active'] = (isset($post['is_active']) && $post['is_active'] == 1) ? 1:0;
            if(!empty($request->id)){
                $post['updated_at'] = date('Y-m-d H:i:s');
                $post['updated_by'] = Auth::user()->id;
                DB::table('ps_banner')->where('id', '=', $request->id)->update($post);
                $this->updateCache();
                return response()->json(['status' => 'success', 'message' => 'Banner successfully updated.']);
            }else{
                $post['created_at'] = date('Y-m-d H:i:s');
                $post['created_by'] = Auth::user()->id;
                DB::table('ps_banner')->insert($post);
                $this->updateCache();
                return response()->json(['status' => 'success', 'message' => 'Banner successfully created.']);
            }
        }
        return response()->json(['status' => 'fail', 'message' => 'Something went wrong.']);
    }

    private function updateCache(){
        $client = new Client();

        $response = $client->get($_ENV['PRESTASHOP_BASE_URL'].'/modules/pasapi/cache-clear.php', [
            'headers' => [
                //'Authorization' => 'Bearer '. $this->access_token,
                'Accept'        => 'application/json',
            ],
            'query_params' => [],
        ]);

        $response->getBody();
    }

}
