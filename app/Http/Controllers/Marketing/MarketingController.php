<?php
namespace App\Http\Controllers\Marketing;
use App\EmailHelper;
use App\EmailRequest;
use App\Http\Controllers\Controller;
use App\Models\Marketing;
use App\Models\MarketingCollateral;
use App\Models\Program;
use App\Models\UserNotification;
use App\Models\Student;
use App\Models\User;
use App\Models\UserAccess;
use App\UserActivityHelper;
use App\Utility;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Session;
use Config;
use Lang;
use Cookie;
use Illuminate\Support\Facades\Validator;
use Exception;
require base_path("vendor/autoload.php");

class MarketingController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        if(!UserAccess::hasAccess(UserAccess::PARTNER_ANNOUNCEMENT_ACCESS, 'add')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }
        $records = Marketing::get()->toArray();
        $data_news = [];
        $data_updates = [];
        $all_data = [];
        foreach ($records as $record) {
            if($record['marketing_type'] == 'news'){
                $data_news[$record['slug']] = $record;
            }else if($record['marketing_type'] == 'updates'){
                $data_updates[$record['slug']] = $record;
            }

            $all_data[$record['marketing_type']][] = $record;

        }

        if(!Session::has('marketing-active-tab')){
            Session::flash('marketing-active-tab', 'news');
        }

        return view('marketing.form', compact('data_news', 'all_data', 'data_updates'));
    }

    public function announce(Request $request)
    {
        if($request->announce_type == 'news' && !UserAccess::hasAccess(UserAccess::NEWS_ACCESS, 'view')){
            return response()->view(Utility::ERROR_PAGE_TEMPLATE, [], Response::HTTP_FORBIDDEN);
        }
        if($request->announce_type == 'announcements' && !UserAccess::hasAccess(UserAccess::ANNOUNCEMENTS_ACCESS, 'view')){
            return response()->view(Utility::ERROR_PAGE_TEMPLATE, [], Response::HTTP_FORBIDDEN);
        }
        if($request->announce_type == 'updates' && !UserAccess::hasAccess(UserAccess::UPDATES_ACCESS, 'view')){
            return response()->view(Utility::ERROR_PAGE_TEMPLATE, [], Response::HTTP_FORBIDDEN);
        }

        $heading = $request->announce_type;
        $results = Marketing::where([['marketing_type', '=', $request->announce_type],['status', '=', '1']])->orderBy('updated_at', 'DESC')->get();

        $marketing_ids = [];
        foreach ($results as $result) {
            $marketing_ids[] = $result->id;
        }
        //dd($marketing_ids);
        if(count($marketing_ids) > 0){
            UserNotification::where([
                ['relation_table', '=', 'pas_marketing'],
                ['user_id', '=', Auth::user()->id],
            ])
                ->whereIn('foreign_key_id', $marketing_ids)
                ->update([
                    'read_status' => UserNotification::READ
                ]);
        }

        return view('marketing.announcement', compact('results', 'heading'));
    }

    public function store(Request $request){
        $post_data = $request->all();

        /*if($post_data['market_type'] && !UserAccess::hasAccess(UserAccess::NEWS_ACCESS, 'add')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }*/

        $results = Marketing::where('marketing_type', '=', $post_data['market_type'])->get()->toArray();
        $id_desc_key = array_column($results, 'description', 'id');
        $id_title_key = array_column($results, 'title', 'id');
        $ids = [];
        $log_records = [];
        switch ($post_data['market_type']){
            case 'news':
                foreach ($post_data['news'] as $key => $news){
                    if(!empty($news['description'])) {
                        $data_to_insert = [];
                        $data_to_insert['marketing_type'] = $news['marketing_type'];
                        $data_to_insert['slug'] = $news['slug'];
                        $data_to_insert['title'] = $news['title'];
                        $data_to_insert['description'] = $news['description'];
                        $data_to_insert['status'] = isset($news['status']) ? 1 : 0;

                        if (!isset($news['id'])) {
                            $data_to_insert['created_by'] = Auth::user()->id;
                            $data_to_insert['created_at'] = date('Y-m-d H:i:s');
                            $data_to_insert['updated_at'] = date('Y-m-d H:i:s');
                        } else {
                            if(isset($id_desc_key[$news['id']]) && $id_desc_key[$news['id']] != $news['description']){
                                $data_to_insert['updated_at'] = date('Y-m-d H:i:s');
                                $data_to_insert['updated_by'] = Auth::user()->id;
                                $ids[] = $news['id'];
                                $log_records['update_ids'][] = end($ids);
                                $log_records['update'][] = $data_to_insert;
                            }
                        }
                        if (isset($news['id']) && !empty($news['id'])) {
                            Marketing::where([["id", '=', $news['id']]])->update($data_to_insert);
                        } else {
                            $log_records['insert'][] = $data_to_insert;
                            Marketing::insert($data_to_insert);
                            $ids[] = DB::getPdo()->lastInsertId();
                            $log_records['insert_ids'][] = end($ids);
                        }
                    }else if(empty($news['description']) && isset($news['id'])) {
                        Marketing::where([["id", '=', $news['id']]])->delete();
                    }
                    Session::flash('marketing-active-tab', 'news');
                }
                break;
            case 'announcements':
                foreach ($post_data['announcements'] as $announcement){
                    if(!empty($announcement['title']) && !empty($announcement['description'])){
                        $data_to_insert = [];
                        $data_to_insert['marketing_type'] = $announcement['marketing_type'];
                        $data_to_insert['title'] = $announcement['title'];
                        $data_to_insert['description'] = $announcement['description'];
                        $data_to_insert['status'] = isset($announcement['status']) ? 1:0;
                        if(!isset($announcement['id'])){
                            $data_to_insert['created_by'] = Auth::user()->id;
                            $data_to_insert['created_at'] = date('Y-m-d H:i:s');
                            $data_to_insert['updated_at'] = date('Y-m-d H:i:s');
                        }else{
                            if(isset($id_desc_key[$announcement['id']]) &&
                                ($id_title_key[$announcement['id']] != $announcement['title'] ||
                                    $id_desc_key[$announcement['id']] != $announcement['description']) ){
                                $data_to_insert['updated_at'] = date('Y-m-d H:i:s');
                                $data_to_insert['updated_by'] = Auth::user()->id;
                                $ids[] = $announcement['id'];
                                $log_records['update_ids'][] = end($ids);
                                $log_records['update'][] = $data_to_insert;
                            }
                        }
                        if(isset($announcement['id']) && !empty($announcement['id'])){
                            Marketing::where([["id", '=', $announcement['id']]])->update($data_to_insert);
                        }else{
                            $log_records['insert'][] = $data_to_insert;
                            Marketing::insert($data_to_insert);
                            $ids[] = DB::getPdo()->lastInsertId();
                            $log_records['insert_ids'][] = end($ids);
                        }
                        Session::flash('marketing-active-tab', 'announcements');
                    }else if((empty($announcement['title']) || empty($announcement['description'])) && isset($announcement['id'])) {
                        Marketing::where([["id", '=', $announcement['id']]])->delete();
                    }
                }
                break;
            case 'updates':
                foreach ($post_data['updates'] as $update){
                    if(!empty($update['description'])){
                        $data_to_insert = [];
                        $data_to_insert['marketing_type'] = $update['marketing_type'];
                        $data_to_insert['slug'] = $update['slug'];
                        $data_to_insert['title'] = $update['title'];
                        $data_to_insert['description'] = $update['description'];
                        $data_to_insert['status'] = isset($update['status']) ? 1:0;
                        if(!isset($update['id'])){
                            $data_to_insert['created_by'] = Auth::user()->id;
                            $data_to_insert['created_at'] = date('Y-m-d H:i:s');
                            $data_to_insert['updated_at'] = date('Y-m-d H:i:s');
                        }else{
                            if(isset($id_desc_key[$update['id']]) && $id_desc_key[$update['id']] != $update['description']){
                                $data_to_insert['updated_at'] = date('Y-m-d H:i:s');
                                $data_to_insert['updated_by'] = Auth::user()->id;
                                $ids[] = $update['id'];
                                $log_records['update_ids'][] = end($ids);
                                $log_records['update'][] = $data_to_insert;
                            }
                        }
                        if(isset($update['id']) && !empty($update['id'])){
                            Marketing::where([["id", '=', $update['id']]])->update($data_to_insert);
                        }else{
                            Marketing::insert($data_to_insert);
                            $ids[] = DB::getPdo()->lastInsertId();
                            $log_records['insert'][] = $data_to_insert;
                            $log_records['insert_ids'][] = end($ids);
                        }
                    }else if(empty($update['description']) && isset($update['id'])) {
                        Marketing::where([["id", '=', $update['id']]])->delete();
                    }
                    Session::flash('marketing-active-tab', 'updates');
                }
                break;
        }

        /*$old_data = DB::table('pas_email_templates')
            ->select(['from_name', 'from_email', 'type', 'subject', 'message'])
            ->where("id", '=', $id)
            ->first();*/

        if(isset($log_records['update_ids']) && count($log_records['update_ids']) > 0){
            $leeds_data['url'] = 'update-'.$post_data['market_type'];
            $leeds_data['action'] = 'update';
            //$leeds_data['old_data'] = json_encode($old_data);
            $leeds_data['new_data'] = json_encode($log_records['update']);
            $leeds_data['ref_ids'] = implode(',', $log_records['update_ids']);
            UserActivityHelper::getInstance()->save($request, $leeds_data);
        }


        if(isset($log_records['insert_ids']) && count($log_records['insert_ids']) > 0){
            $leeds_data['url'] = 'insert-'.$post_data['market_type'];
            $leeds_data['action'] = 'create';
            //$leeds_data['old_data'] = json_encode($old_data);
            $leeds_data['new_data'] = json_encode($log_records['insert']);
            $leeds_data['ref_ids'] = implode(',', $log_records['insert_ids']);
            UserActivityHelper::getInstance()->save($request, $leeds_data);
        }

        //$sql = [];
        $users = DB::table('pas_users')->get()->all();
        //dd($users);
        foreach ($users as $user){
            foreach ($ids as $id) {
                $partner_id = !empty($user->partner_id) ? $user->partner_id: null;
                UserNotification::updateOrCreate(
                    ['relation_table' => 'pas_marketing', 'foreign_key_id' => $id, 'partner_id' => $partner_id, 'user_id' => $user->id],
                    ['read_status' => UserNotification::UNREAD]
                );
                //$sql[] = "INSERT INTO `pas_marketing_partner_map` (`marketing_id`, `partner_id`, `read_status`) VALUES (".$id.", ".$partner['id'].", 'read') ON DUPLICATE KEY UPDATE `marketing_id`=".$id.", `partner_id`=".$partner['id'].", `read_status` = 'read'";
            }
        }
        /*if(count($sql) > 0){
            $response = DB::statement(implode(';', $sql).';');
            dd($response);
        }*/

        return response()->json(["status" => "success", "msg"=> ucwords($post_data['market_type'])." successfully updated."]);

    }

    /*public function search(Request $request) {
        if(!UserAccess::hasAccess(UserAccess::PARTNER_ANNOUNCEMENT_ACCESS, 'view')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }
        $result = $this->getSearchData($request, 'email');
        foreach($result as $key => $val){
            $result[$key]->id = pas_encrypt($val->id);
            $result[$key]->status = Student::getStatus($val->status);
        }
        echo json_encode(['total_record' => $this->getSearchData($request, '', true), 'result' => $result]);die;
    }*/

    /**
     * @param Request $request
     * @param string $groupBy
     * @param bool $totalCount
     * @return \Illuminate\Support\Collection|int
     */
    /*private function getSearchData(Request $request, $groupBy = '', $totalCount = false){
        //dd(DB::getQueryLog());die; // last query
        $query = DB::table('pas_program')->select(['id', 'name', 'code', 'unite_price', 'hours', 'retail_wholesale', 'program_type', 'status', 'description', 'certification_included']);

        if (isset($request->q)){
            $query->where(function ($query) use ($request) {
                $query->orwhere('name', 'like', '%'.$request->q.'%')
                    ->orwhere('code', 'like', '%'.$request->q.'%')
                    ->orwhere('code', 'like', '%'.$request->q.'%');
            });
        }

        if (!empty($request->program_name))
            $query->where('name', 'like', '%'.$request->program_name.'%');
        if (!empty($request->course_code))
            $query->where('code', 'like', '%'.$request->course_code.'%');
        if (!empty($request->status))
            $query->where('status', '=', $request->status);
        if (!empty($request->program_type))
            $query->where('program_type', '=', $request->program_type);
        if (!empty($request->certification_included))
            $query->where('certification_included', '=', $request->certification_included);


        if($totalCount){
            return $query->count();
        }
        return $query->get();
        //dd(DB::getQueryLog());die; // last query
    }*/


    public function collateralStore(Request $request){
        $mar_data = $request->all();
        $email_data = $mar_data;
        //unset($mar_data['program_name']);
        $mar_data['partner_id'] = User::getPartnerDetail('id');
        $validator = Validator::make($mar_data, MarketingCollateral::rules(), [], MarketingCollateral::attributeNames());

        if($validator->fails()){
            $get_errors = $validator->errors()->toArray();
            return response()->json(['status' => 'fail', 'errors' => $get_errors]);
        }

        $pr_data['partner_id'] = User::getPartnerDetail('id');
        $pr_data['request_type'] = 2;
        $pr_data['requested_by'] = Auth::user()->id;
        $pr_data['requested_date'] = date('Y-m-d H:i:s');
        $pr_data['updated_at'] = date("Y-m-d H:i:s");

        DB::table('student_progress_report')->insert($pr_data);
        $last_id = DB::getPdo()->lastInsertId();

        if(!empty($last_id)){
            unset($mar_data['_token'], $mar_data['remember_me'], $mar_data['agree_with'], $mar_data['partner_name']);

            $mar_data['event_date'] = !empty($mar_data['event_date']) ? Carbon::create($mar_data['event_date'])->format('Y-m-d'):null;
            $mar_data['due_date'] = !empty($mar_data['due_date']) ? Carbon::create($mar_data['due_date'])->format('Y-m-d'):null;

            $mar_data['desired_completion_date'] = !empty($mar_data['desired_completion_date']) ? Carbon::create($mar_data['desired_completion_date'])->format('Y-m-d'):null;
            $mar_data['meeting_proposed_date'] = !empty($mar_data['meeting_proposed_date']) ? Carbon::create($mar_data['meeting_proposed_date'])->format('Y-m-d'):null;
            $mar_data['progress_report_id'] = $last_id;
            $mar_data['created_by'] = Auth::user()->id;
            $mar_data['created_at'] = date("Y-m-d H:i:s");
            $mar_data['updated_at'] = date("Y-m-d H:i:s");
            MarketingCollateral::insert($mar_data);
            $mcol_last_id = DB::getPdo()->lastInsertId();
            if(!empty($mcol_last_id)){

                $leeds_data['action'] = 'create';
                //$leeds_data['old_data'] = json_encode($zoho_leads);
                $leeds_data['new_data'] = json_encode(['student_progress_report' => $pr_data, 'pas_marketing_collateral' => $mar_data]);
                $leeds_data['ref_ids'] = $mcol_last_id;
                UserActivityHelper::getInstance()->save($request, $leeds_data);

                $this->sendEmailNotification($email_data);
            }
        }

        $users = DB::table('pas_users')
            ->where('partner_id', '=', User::getPartnerDetail('id'))
            ->whereIn('user_type', [User::USER_TYPE_PARTNER, User::USER_TYPE_MY_USER])
            ->get()->all();
        //dd($users);
        foreach ($users as $user){
            $partner_id = !empty($user->partner_id) ? $user->partner_id: null;
            UserNotification::updateOrCreate(
                ['relation_table' => 'student_progress_report', 'foreign_key_id' => $last_id, 'partner_id' => $partner_id, 'user_id' => $user->id],
                ['read_status' => UserNotification::UNREAD]
            );
        }

        return response()->json(['status' => 'success', 'msg' => 'Data added successfully.']);
    }

    public function collateral()
    {
        if(!UserAccess::hasAccess(UserAccess::REQUEST_COLLATERAL_ACCESS, 'view')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }
        //$records = MarketingCollateral::get()->toArray();
        $programs = Program::orderBy('name', 'ASC')->get()->toArray();

        return view('marketing.collateral-form', compact('programs'));
    }

    private function sendEmailNotification($data){
        $program = '';
        if(!empty($data['program_id'])){
            $program = Program::where('id', '=', $data['program_id'])->value('name');
        }

        $project_types = \App\Models\MarketingCollateral::getProjectType();
        $branding_types = \App\Models\MarketingCollateral::getBrandingType();
        $placeholder = [
            'CONTACT_NAME' => $data['contact_name'],
            'CONTACT_EMAIL' => $data['contact_email'],
            'INSTITUTION_PARTNER_NAME' => $data['partner_name'],
            'IS_REQUESTED_MATERIAL' => isset($data['is_requested_material']) ? 'Yes':'No',
            'DATE_OF_EVENT' => isset($data['is_requested_material']) ? $data['event_date']:'No',
            'TARGET_AUDIENCE' => $data['target_audience'],
            'INTENDED_OUTCOME' => $data['intended_outcome'],
            'BRANDING' => isset($branding_types[$data['branding']]) ? $branding_types[$data['branding']]:'',
            'PROJECT_DUE_DATE' => $data['due_date'],
            'DESIRED_COMPLETION_DATE' => $data['desired_completion_date'],
            'MEETING_PROPOSED_DATE' => $data['meeting_proposed_date'],
            'PROJECT_TYPE' => isset($project_types[$data['project_type']]) ? $project_types[$data['project_type']]:'',
            'COURSE_BEING_PROMOTED' => $program,
            'DESCRIPTION_OF_PROJECT' => $data['description'],
            'ANY_ADDITIONAL_NOTES' => $data['additional_notes'],
            'PURPOSE' => $data['purpose'],
        ];

        $email_req = new EmailRequest();
        $email_req->setTemplate(EmailRequest::EMAIL_COLLATERAL_REQUEST)
            ->setPlaceholder($placeholder)
            //->setTo([['festus@worldeducation.net', 'PAS Admin'], ['khemraj.maurya@gmail.com', 'Khemraj']])
            ->setTo([
                [$_ENV['ADMIN_EMAIL_FIRST'], 'PAS Admin'],
                [$_ENV['ADMIN_EMAIL_SECOND'], 'PAS Admin']
            ])
            ->setLogSave(true);

        $email_helper = new EmailHelper($email_req);
        $email_helper->sendEmail();
    }

    public function topSellingPrograms(){
        if(!UserAccess::hasAccess(UserAccess::MY_TOP_SELLING_PROGRAMS_ACCESS, 'view')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }
        $top_selling_programs = [
            'Career Training Program' => [],
            'Professional Enrichment' => [],
            'Continuing Education' => [],
        ];
        $partner = DB::table('pas_partner')
            ->join('pas_enrollment', function($join){
                $join->on('partner_id', '=', 'pas_partner.id');
            })
            ->join('pas_program', function($join){
                $join->on('pas_enrollment.program_zoho_id', '=', 'pas_program.zoho_id');
            })
            ->where('price_book_id')
            ->inRandomOrder()
            ->get()->toArray();
        //dd($partner);
        if(!empty(User::getPartnerDetail('id'))){
            /*$available_programs = DB::table('pas_partner_selling_program_map AS pspm')
                //->select(['pas_price_book_program_map.program_id'])
                ->join('pas_program as pr', 'pspm.program_id', '=', 'pr.id')
                ->where('pspm.partner_id', '=', User::getPartnerDetail('id'))
                ->where('pspm.selling_count', '>', 0)
                ->orderBy('pspm.selling_count', 'DESC')
                ->get()->all();
//dd($available_programs);
            foreach ($available_programs as $available_program){
                $top_selling_programs[$available_program->program_type][] = [
                    'heading' => 'Top 5 '.$available_program->name,
                    'program_name' => $available_program->name,
                    'count' => $available_program->selling_count,
                ];
            }*/

            $available_programs_ctp = DB::table('pas_enrollment AS e')
                ->select(DB::raw('COUNT(e.id) AS total_ctp'), 'pr.name', 'pr.program_type')
                ->join('pas_program as pr', 'e.program_zoho_id', '=', 'pr.zoho_id')
                ->where('e.partner_id', '=', User::getPartnerDetail('id'))
                ->where('pr.program_type', '=', 'Career Training Program')
                ->groupBy('pr.id')
                ->orderBy('total_ctp', 'DESC')
                ->limit(5)
                ->get()
                ->all();

            foreach ($available_programs_ctp as $available_program_ctp){
                $top_selling_programs[$available_program_ctp->program_type][] = [
                    'heading' => 'Top 5 '.$available_program_ctp->name,
                    'program_name' => $available_program_ctp->name,
                    'count' => $available_program_ctp->total_ctp,
                ];
            }

            $available_programs_pe = DB::table('pas_enrollment AS e')
                ->select(DB::raw('COUNT(e.id) AS total_ctp'), 'pr.name', 'pr.program_type')
                ->join('pas_program as pr', 'e.program_zoho_id', '=', 'pr.zoho_id')
                ->where('e.partner_id', '=', User::getPartnerDetail('id'))
                ->where('pr.program_type', '=', 'Professional Enrichment')
                ->groupBy('pr.id')
                ->orderBy('total_ctp', 'DESC')
                ->limit(5)
                ->get()
                ->all();

            foreach ($available_programs_pe as $available_program_pe){
                $top_selling_programs[$available_program_pe->program_type][] = [
                    'heading' => 'Top 5 '.$available_program_pe->name,
                    'program_name' => $available_program_pe->name,
                    'count' => $available_program_pe->total_ctp,
                ];
            }

            $available_programs = DB::table('pas_enrollment AS e')
                ->select(DB::raw('COUNT(e.id) AS total_ctp'), 'pr.name', 'pr.program_type')
                ->join('pas_program as pr', 'e.program_zoho_id', '=', 'pr.zoho_id')
                ->where('e.partner_id', '=', User::getPartnerDetail('id'))
                ->where('pr.program_type', '=', 'Continuing Education')
                ->groupBy('pr.id')
                ->orderBy('total_ctp', 'DESC')
                ->limit(5)
                ->get()
                ->all();

            foreach ($available_programs as $available_program){
                $top_selling_programs[$available_program->program_type][] = [
                    'heading' => 'Top 5 '.$available_program->name,
                    'program_name' => $available_program->name,
                    'count' => $available_program->total_ctp,
                ];
            }

        }
        //dd($top_selling_programs);
        return view('marketing.top-selling-programs', compact('top_selling_programs'));
    }

    public function marketingCollateral()
    {
        if(!UserAccess::hasAccess(UserAccess::MARKETING_COLLATERAL_ACCESS, 'view')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }
        return view('marketing-collateral.index');
    }

    public function courseMarketing(){
        if(!UserAccess::hasAccess(UserAccess::MARKETING_COLLATERAL_ACCESS, 'view')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }
        $records = Db::table('pas_marketing_category')->where('category_type', '=', 'course-marketing')->get()->all();
        return view('marketing-collateral.category-marketing', compact('records'));
    }

    public function courseMarketingCategory(Request $request){
        if(!UserAccess::hasAccess(UserAccess::MARKETING_COLLATERAL_ACCESS, 'view')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }
        $parent_category = Db::table('pas_marketing_category')->where('slug', '=', $request->course_marketing)->get()->first();
        if(!$parent_category) {
            return redirect(route('dashboard'));
        }

        $categories = Db::table('pas_marketing_category')->where([
            ['parent_id', '=', DB::raw("(SELECT id FROM pas_marketing_category WHERE slug = '".$request->course_marketing."')")],
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
        //dd($media_files_group);

        return view('marketing-collateral.course-marketing-category', compact('categories', 'parent_category', 'media_files_group'));
    }

    public function fundingSources(){
        if(!UserAccess::hasAccess(UserAccess::MARKETING_COLLATERAL_ACCESS, 'view')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }

        $parent_category = Db::table('pas_marketing_category')->where('slug', '=', 'funding-sources')->get()->first();
        //dd($parent_category);
        /*if(!$parent_category) {
            return redirect(route('dashboard'));
        }*/

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
        }
        //dd($media_files_group);

        return view('marketing-collateral.funding-sources', compact('categories', 'parent_category', 'media_files_group'));
    }

    public function socialMedia(){
        if(!UserAccess::hasAccess(UserAccess::MARKETING_COLLATERAL_ACCESS, 'view')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }

        $parent_category = Db::table('pas_marketing_category')->where('slug', '=', 'social-media')->orderBy('slug', 'DESC')->get()->first();
        //dd($parent_category);
        /*if(!$parent_category) {
            return redirect(route('dashboard'));
        }*/

        $categories = Db::table('pas_marketing_category')->where([
            ['parent_id', '=', DB::raw("(SELECT id FROM pas_marketing_category WHERE slug = 'social-media')")],
        ])->get()->all();

        $media_files = Db::table('pas_marketing_template AS pmt')
            ->where('pmc.parent_id', '=', $parent_category->id)
            ->join('pas_marketing_category AS pmc', 'pmt.category_id', 'pmc.id')
            ->orderBy('pmt.id', 'DESC')
            ->get()->all();
        //dd($media_files);
        $media_files_group = [];
        foreach ($media_files as $media_file){
            $media_files_group[$media_file->group_type][] = $media_file;
        }
        //dd($media_files_group);
        return view('marketing-collateral.social-media', compact('categories', 'parent_category', 'media_files_group'));
    }

}