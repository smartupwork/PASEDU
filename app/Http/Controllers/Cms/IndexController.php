<?php
namespace App\Http\Controllers\Cms;
use App\Http\Controllers\Controller;
use App\Models\UserAccess;
use App\UserActivityHelper;
use App\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Session;
use Config;
use Lang;
use Cookie;

class IndexController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        if(!UserAccess::hasAccess(UserAccess::WE_TEMPLATE_ACCESS, 'view')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }
        $page_title = Route::is('we-templates') ? 'We Templates':'Marketing Collateral';
        return view('cms.index', compact('page_title'));
    }

    public function courseMarketing(){
        if(!UserAccess::hasAccess(UserAccess::WE_TEMPLATE_ACCESS, 'view')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }
        $records = Db::table('pas_marketing_category')->where('category_type', '=', 'course-marketing')->get()->all();
        return view('cms.category-marketing', compact('records'));
    }

    public function courseMarketingCategory(Request $request){
        if(!UserAccess::hasAccess(UserAccess::WE_TEMPLATE_ACCESS, 'view')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }
        //dd($request->course_marketing);
        $parent_category = Db::table('pas_marketing_category')->where('slug', '=', $request->course_marketing)->get()->first();
        //dd($parent_category);

        $categories = Db::table('pas_marketing_category')->where([
            ['parent_id', '=', DB::raw("(SELECT id FROM pas_marketing_category WHERE slug = '".$request->course_marketing."')")],
        ])->get()->all();

        $media_files = Db::table('pas_marketing_template AS pmt')
            ->where('pmc.parent_id', '=', $parent_category->id)
            ->join('pas_marketing_category AS pmc', 'pmt.category_id', 'pmc.id')->get()->all();//->orderBy('pmc.id', 'DESC')
        //dd($media_files);
        $media_files_group = [];
        foreach ($media_files as $media_file){
            $media_files_group[$media_file->group_type][] = $media_file;
        }
        //dd($media_files_group);

        return view('cms.course-marketing-category', compact('categories', 'parent_category', 'media_files_group'));
    }

    public function fundingSources(){
        if(!UserAccess::hasAccess(UserAccess::WE_TEMPLATE_ACCESS, 'view')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }
        /*$parent_category = Db::table('pas_marketing_category')->where('slug', '=', 'funding-sources')->get()->first();
        //dd($parent_category);

        $categories = Db::table('pas_marketing_category')->where([
            ['parent_id', '=', DB::raw("(SELECT id FROM pas_marketing_category WHERE slug = 'funding-sources')")],
        ])->get()->all();

        $media_files = Db::table('pas_marketing_template AS pmt')
            ->where('pmc.parent_id', '=', $parent_category->id)
            ->join('pas_marketing_category AS pmc', 'pmt.category_id', 'pmc.id')
            //->orderBy('pmt.id', 'DESC')
            ->get()->all();
        //dd($media_files);
        $media_files_group = [];
        foreach ($media_files as $media_file){
            $media_files_group[$media_file->group_type][] = $media_file;
        }*/
        //dd($media_files_group);

        list($parent_category, $categories, $media_files_group) = $this->getMarketingTemplates('funding-sources');

        return view('cms.funding-sources', compact('categories', 'parent_category', 'media_files_group'));
    }

    public function socialMedia(){
        if(!UserAccess::hasAccess(UserAccess::WE_TEMPLATE_ACCESS, 'view')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }
        /*$parent_category = Db::table('pas_marketing_category')->where('slug', '=', 'social-media')->get()->first();
        //dd($parent_category);

        $categories = Db::table('pas_marketing_category')->where([
            ['parent_id', '=', DB::raw("(SELECT id FROM pas_marketing_category WHERE slug = 'social-media')")],
        ])->get()->all();

        $media_files = Db::table('pas_marketing_template AS pmt')
            ->where('pmc.parent_id', '=', $parent_category->id)
            ->join('pas_marketing_category AS pmc', 'pmt.category_id', 'pmc.id')
            //->orderBy('pmt.id', 'DESC')
            ->get()->all();
        //dd($media_files);
        $media_files_group = [];
        foreach ($media_files as $media_file){
            $media_files_group[$media_file->group_type][] = $media_file;
        }*/

        list($parent_category, $categories, $media_files_group) = $this->getMarketingTemplates('social-media');
        return view('cms.social-media', compact('categories', 'parent_category', 'media_files_group'));
    }

    private function getMarketingTemplates($slug){
        $parent_category = Db::table('pas_marketing_category')->where('slug', '=', $slug)->get()->first();
        //dd($parent_category);

        $categories = Db::table('pas_marketing_category')->where([
            ['parent_id', '=', DB::raw("(SELECT id FROM pas_marketing_category WHERE slug = '".$slug."')")],
        ])->get()->all();

        $media_files = Db::table('pas_marketing_template AS pmt')
            ->where('pmc.parent_id', '=', $parent_category->id)
            ->join('pas_marketing_category AS pmc', 'pmt.category_id', 'pmc.id')
            //->orderBy('pmt.id', 'DESC')
            ->get()->all();
        //dd($media_files);
        $media_files_group = [];
        foreach ($media_files as $media_file){
            $media_files_group[$media_file->group_type][] = $media_file;
        }

        return [$parent_category, $categories, $media_files_group];
    }

    public function storeTemplate(Request $request){
        if(!UserAccess::hasAccess(UserAccess::WE_TEMPLATE_ACCESS, 'add')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }

        //try{
            $image = $request->media_file;
            if(empty($request->category)){
                return response()->json(["status"=> "fail", "msg"=>"Please select category."]);
            }else if(!$image){
                return response()->json(["status"=> "fail", "msg"=>"Please select template file."]);
            }else {
                $s3 = \Storage::disk('s3');
                $file_name = uniqid() .'.'. $image->getClientOriginalExtension();
                $s3filePath = '/we-templates/' . $file_name;
                $s3->put($s3filePath, file_get_contents($image), 'public');
                $data['category_id'] = $request->category;
                $data['mime_type'] = $image->getMimeType();
                $data['media_file'] = $file_name;
                //$data['group_type'] = '';
                $data['created_at'] = date('Y-m-d H:i:s');
                $data['created_by'] = Auth::user()->id;
                //dd($data);
                DB::table('pas_marketing_template')->insert($data);
                $last_id = DB::getPdo()->lastInsertId();
                if(!empty($last_id)){
                    $category_detail = DB::table('pas_marketing_category as mc')
                        ->select([DB::raw('(SELECT category_name FROM pas_marketing_category WHERE pas_marketing_category.id = mc.parent_id) AS parent_category_name'), 'category_name'])
                        ->where('id', '=', $request->category)->get()->first();

                    $data['parent_category_name'] = $category_detail ? $category_detail->parent_category_name:null;
                    $data['category_name'] = $category_detail ? $category_detail->category_name:null;

                    $leeds_data['action'] = 'create';
                    //$leeds_data['old_data'] = json_encode($old_data);
                    $leeds_data['new_data'] = json_encode($data);
                    $leeds_data['ref_ids'] = $last_id;
                    UserActivityHelper::getInstance()->save($request, $leeds_data);

                    return response()->json(["status"=>"success", "msg"=>"Template uploaded successfully."]);
                }
                return response()->json(["status"=> "fail", "msg"=> "Template upload failed."]);
            }
            //return response()->json(["status"=> "fail", "msg"=> "Template upload failed."]);
        /*}catch (\Exception $e){
            return response()->json(["status"=> "fail", "msg"=> 'Something went wrong.']);
        }*/

    }

}
