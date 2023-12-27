<?php
namespace App\Http\Controllers\Prestashop;
use App\Http\Controllers\Controller;
use App\Models\Partner;
use App\Models\User;
use App\Models\UserAccess;
use App\Utility;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use PHPUnit\Exception;
use Psy\Util\Str;
use Session;
use Config;
use Lang;
require base_path("vendor/autoload.php");
use Cookie;
use Mpdf\Mpdf;

class PartnerController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getContactSettingApi(Request $request)
    {
        $partner = DB::table('pas_partner')->where('partner_name', '=', $request->partner_name)->get()->first();

        if(!$partner){
            return response()->json(['status' => false, 'message' => 'Partner does not exists.']);
        }

        $setting = DB::table('ps_configuration')
            ->select(['content', 'is_active'])
            ->where('partner_id', '=', $partner->id)
            ->where('type', '=', 'contact-setting')
            //->where('is_active', '=', 1)
            ->get()->first();

        if(!$setting){
            return response()->json(['status' => false, 'message' => 'Partner contact detail not found.']);
        }

        if(!empty($setting->content)){
            $result = json_decode($setting->content, true);
        }

        $result['contact_us_opt_in'] = (boolean) $setting->is_active;
        $result['phone'] = $partner->pi_phone;
        $result['email'] = $partner->pi_email;
        $result['department'] = $partner->department;
        $result['zoho_id'] = $partner->zoho_id;
        return response()->json(['status' => true, 'data' => $result]);
    }


    public function getPartnerDetailApi(Request $request)
    {
        $partner = DB::table('pas_partner')->where('partner_name', '=', $request->partner_name)->get()->first();

        if(!$partner){
            return response()->json(['status' => false, 'message' => 'Partner does not exists.']);
        }

        return response()->json(['status' => true, 'data' => $partner]);
    }

    public function getAffiliateDetailApi(Request $request)
    {
        $partner = DB::table('pas_affiliate')->where('affiliate_name', '=', $request->affiliate_name)->get()->first();

        if(!$partner){
            return response()->json(['status' => false, 'message' => 'Affiliate does not exists.']);
        }

        return response()->json(['status' => true, 'data' => $partner]);
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        if(!UserAccess::hasAccess(UserAccess::PARTNER_ADMIN_ACCESS, 'view')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }
        $edit_record = DB::table('ps_configuration')
            ->where('partner_id', '=', User::getPartnerDetail('id'))
            ->where('type', '=', 'contact-setting')
            ->get()->first();

        $states = DB::table('pas_state')->where('status', '=', 1)->get()->all();
        $timezone = DB::table('pas_timezone')
            ->where('country_code', '=', 'US')
            ->orderBy('display_order')->get()->all();

        $contact_detail = [];
        if($edit_record && !empty($edit_record->content)){
            $contact_detail = json_decode($edit_record->content, true);
        }

        return view('prestashop.partner.create', compact('edit_record', 'contact_detail','states', 'timezone'));
    }


    public function save(Request $request){
        if(!UserAccess::hasAccess(UserAccess::PARTNER_ADMIN_ACCESS, 'add')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }

        if($_POST){
            $post = $request->contact;
            $data['partner_id'] = User::getPartnerDetail('id');
            $data['content'] = json_encode($request->contact);
            $data['type'] = $post['type'];
            /*if(empty($post['content'])){
                return response()->json(["status"=> "fail", "message"=>"Please enter content."]);
            }*/
            $data['is_active'] = (isset($post['contact_us_opt_in']) && $post['contact_us_opt_in'] == 1) ? 1:0;

            if(!empty($request->id)){
                $data['updated_at'] = date('Y-m-d H:i:s');
                $data['updated_by'] = Auth::user()->id;
                DB::table('ps_configuration')->where('id', '=', $request->id)->update($data);
                $this->updateCache();
                return response()->json(['status' => 'success', 'message' => 'Contact setting successfully updated.']);
            }else{
                $data['created_at'] = date('Y-m-d H:i:s');
                $data['created_by'] = Auth::user()->id;
                DB::table('ps_configuration')->insert($data);
                $this->updateCache();
                return response()->json(['status' => 'success', 'message' => 'Contact setting successfully created.']);
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

    private function htaccessReGenerate(){
        $client = new Client();

        $response = $client->get($_ENV['PRESTASHOP_BASE_URL'].'/modules/pasapi/htaccess-regenerate.php', [
            'headers' => [
                //'Authorization' => 'Bearer '. $this->access_token,
                'Accept'        => 'application/json',
            ],
            'query_params' => [],
        ]);

        $response->getBody();
    }

    public function sitespromotions(Request $request){
        if(!UserAccess::hasAccess(UserAccess::PARTNER_ADMIN_ACCESS, 'view')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }
        $edit_record = DB::table('ps_configuration')
            ->where('partner_id', '=', User::getPartnerDetail('id'))
            ->where('type', '=', 'laptop-content')
            ->get()->first();

        $contact_detail = [];
        if($edit_record && !empty($edit_record->content)){
            $contact_detail = json_decode($edit_record->content, true);
        }
        if($request->ajax()){
            $post = $request->contact;
            $data['partner_id'] = User::getPartnerDetail('id');
            $data['type'] = $post['type'];
            $data['is_active'] = $request->is_active;

            $json_data['type'] = $post['type'];
            $json_data['title'] = $post['title'];
            $json_data['detail'] = $post['detail'];

            $image = $post['image'];
            if(empty($request->id) && !$image){
                return response()->json(["status"=> "fail", "message"=>"Please select image."]);
            }else{

                if($image){
                    $s3 = \Storage::disk('s3');
                    $file_name = uniqid() .'.'. $image->getClientOriginalExtension();
                    $s3filePath = '/ps-banner/' . $file_name;
                    $s3->put($s3filePath, file_get_contents($image), 'public');
                    $json_data['image'] = $file_name;
                }else{
                    $json_data['image'] = $request->old_image;
                }
                $data['content'] = json_encode($json_data);

                if(!empty($request->id)){
                    $data['updated_at'] = date('Y-m-d H:i:s');
                    $data['updated_by'] = Auth::user()->id;
                    DB::table('ps_configuration')->where('id', '=', $request->id)->update($data);
                    $this->updateCache();
                    return response()->json(['status' => 'success', 'message' => 'Laptop promotions successfully updated.']);
                }else{
                    $data['created_at'] = date('Y-m-d H:i:s');
                    $data['created_by'] = Auth::user()->id;
                    DB::table('ps_configuration')->insert($data);
                    $this->updateCache();
                    return response()->json(['status' => 'success', 'message' => 'Laptop promotions successfully created.']);
                }
            }

        }
        return view('prestashop.promotions.laptop', compact('edit_record', 'contact_detail'));
    }
    

    public function referfriend(Request $request){
        if(!UserAccess::hasAccess(UserAccess::PARTNER_ADMIN_ACCESS, 'view')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }
        $edit_record = DB::table('ps_configuration')
            ->where('partner_id', '=', User::getPartnerDetail('id'))
            ->where('type', '=', 'refer-friend')
            ->get()->first();

        $contact_detail = [];
        if($edit_record && !empty($edit_record->content)){
            $contact_detail = json_decode($edit_record->content, true);
        }
        if($request->ajax()){
            $post = $request->contact;
            $data['partner_id'] = User::getPartnerDetail('id');
            $data['type'] = $post['type'];
            $data['is_active'] = $request->is_active;

            $json_data['type'] = $post['type'];
            $json_data['title'] = $post['title'];
            $json_data['detail'] = $post['detail'];

            $image = $post['image'];
            if(empty($request->id) && !$image){
                return response()->json(["status"=> "fail", "message"=>"Please select image."]);
            }else{

                if($image){
                    $s3 = \Storage::disk('s3');
                    $file_name = uniqid() .'.'. $image->getClientOriginalExtension();
                    $s3filePath = '/ps-banner/' . $file_name;
                    $s3->put($s3filePath, file_get_contents($image), 'public');
                    $json_data['image'] = $file_name;
                }else{
                    $json_data['image'] = $request->old_image;
                }
                $data['content'] = json_encode($json_data);

                if(!empty($request->id)){
                    $data['updated_at'] = date('Y-m-d H:i:s');
                    $data['updated_by'] = Auth::user()->id;
                    DB::table('ps_configuration')->where('id', '=', $request->id)->update($data);
                    $this->updateCache();
                    return response()->json(['status' => 'success', 'message' => 'Refer a friend successfully updated.']);
                }else{
                    $data['created_at'] = date('Y-m-d H:i:s');
                    $data['created_by'] = Auth::user()->id;
                    DB::table('ps_configuration')->insert($data);
                    $this->updateCache();
                    return response()->json(['status' => 'success', 'message' => 'Refer a friend successfully created.']);
                }
            }

        }
        return view('prestashop.promotions.refer', compact('edit_record', 'contact_detail'));
    }  

    public function checkShopUrl(Request $request){
        if($request->ajax()){
            $query = DB::connection('we_shop')->table('ps_shop_url')
                ->where($request->field, '=', $request->shop['url'].'.'.env('CPANEL_ROOT_DOMAIN'));

            if($query->count() > 0){
                die('"Shop domain already exists."');
            }
            return 'true';
        }
    }

    public function checkShopName(Request $request){
        if($request->ajax()){
            $query = DB::connection('we_shop')->table('ps_shop')
                ->where($request->field, '=', $request->shop['name']);

            if($query->count() > 0){
                die('"Shop domain already exists."');
            }
            return 'true';
        }
    }

    public function shopCreator(Request $request){
        //Partner::deleteShopData(2022);die;
        if(!UserAccess::hasAccess(UserAccess::PARTNER_ADMIN_ACCESS, 'view')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }

        $partners = DB::table('pas_partner')
            ->select(['id', 'zoho_id', 'street', 'city', 'state', 'zip_code', DB::raw('partner_name AS name'), 'email', 'phone', DB::raw('"Partner" AS partner_type')])
            ->where('partner_type', '=', 'Active');

        $affiliates = DB::table('pas_affiliate')
            ->select(['id', 'zoho_id', 'address_1 AS street', 'city', 'state', 'zip_postal_code AS zip_code', DB::raw('affiliate_name AS name'), 'email', 'phone', DB::raw('"Affiliate" AS partner_type')])
            ->where('status', '=', 1);

        $partner_affiliates = $partners->union($affiliates)
            ->orderBy('name', 'ASC')->get();
        if($_POST){
            //Partner::uploadLogo(2008, 2, $request);

            $id_shop_group = ($request->partner_type == 'Affiliate' ? 3:2);
            //echo '<pre>';print_r($_POST);die;
            $contact = [
                'type' => 'contact-setting',
                'contact_us_opt_in' => 1,
                'street' => $request->contact['address'],
                'city' => $request->contact['city'],
                'state' => $request->contact['state'],
                'zip_code' => $request->contact['zip_code'],
                'time_zone' => DB::table('pas_timezone')->value('timezone'),
            ];

            $data['partner_id'] = $request->zoho['partner'];
            $data['content'] = json_encode($contact);
            $data['type'] = 'contact-setting';
            $data['is_active'] = 1;
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['created_by'] = Auth::user()->id;

            DB::beginTransaction();
            DB::connection('we_shop')->beginTransaction();

            $res_messages = [];
            try{
                if($id_shop_group){
                    DB::table('ps_configuration')->insert($data);
                    if(!empty(DB::getPdo()->lastInsertId())) {
                        $res_messages[] = 'Contact detail saved successfully.';
                    }

                    $shop_name = $request->shop['name'];
                    $shop_domain = $request->shop['url'].'.'.env('CPANEL_ROOT_DOMAIN');

                    if(env('APP_ENV') == 'prod'){
                        $cpanel_response = Partner::createSubDomainCPanle($request->shop['url']);
                    }

                    $route53_response = Partner::createSubDomainRoute53($request->shop['url']);
                    $shop_response = Partner::createShop($shop_name, $shop_domain, $id_shop_group, $request->all());

                    //print_r($shop_response);
                    //$cpanel_response['status'] && $route53_response['status'] &&
                    if($shop_response['status']){
                        $res_messages[] = Partner::copyModuleShop($shop_response['id_shop']);
                        $res_messages[] = Partner::copyModuleGroup($shop_response['id_shop']);
                        $res_messages[] = Partner::copyHookModuleShop($shop_response['id_shop']);
                        $res_messages[] = Partner::copyAttributeGroupShop($shop_response['id_shop']);
                        $res_messages[] = Partner::copyAttributeShop($shop_response['id_shop']);
                        $res_messages[] = Partner::copyTaxRuleGroupShop($shop_response['id_shop']);
                        $res_messages[] = Partner::copyCarrierTaxRuleGroupShop($shop_response['id_shop']);
                        $res_messages[] = Partner::copyCategoryLang($shop_response['id_shop']);
                        $res_messages[] = Partner::copyCategoryShop($shop_response['id_shop']);
                        $res_messages[] = Partner::copyCmsCategoryShop($shop_response['id_shop']);
                        //$res_messages[] = Partner::copyCmsShop($shop_response['id_shop']);
                        //$res_messages[] = Partner::copyCmsLangShop($shop_response['id_shop']);
                        $res_messages[] = Partner::copyCustomPayment($shop_response['id_shop']);
                        $res_messages[] = Partner::copyCountryShop($shop_response['id_shop']);
                        $res_messages[] = Partner::copyCurrencyShop($shop_response['id_shop']);
                        $res_messages[] = Partner::copyContactShop($shop_response['id_shop']);
                        $res_messages[] = Partner::copyEmployeeShop($shop_response['id_shop']);
                        $res_messages[] = Partner::copyFeatureShop($shop_response['id_shop']);
                        $res_messages[] = Partner::copyGroupShop($shop_response['id_shop']);
                        $res_messages[] = Partner::copyLangShop($shop_response['id_shop']);
                        $res_messages[] = Partner::copyWebserviceAccountShop($shop_response['id_shop']);
                        $res_messages[] = Partner::copyZoheShop($shop_response['id_shop']);

                        if($id_shop_group == 2) {
                            $res_messages[] = Partner::copyCMSPageShop($shop_response['id_shop']);
                            $res_messages[] = Partner::copyMainMenuShop($shop_response['id_shop'], $request->menu);

                            DB::table('pas_partner')
                                ->where('id', '=', $request->zoho['partner'])
                                ->update([
                                    'prestashop_menu' => json_encode($request->menu),
                                    'ps_shop_id' => $shop_response['id_shop'],
                                    'hosted_site' => 'https://'.$shop_domain,
                                ]);
                        }

                        $res_messages[] = Partner::copyConfigurationShop($shop_response['id_shop']);

                        DB::table(($id_shop_group == 2 ? 'pas_partner':'pas_affiliate'))
                            ->where('id', '=', $request->zoho['partner'])
                            ->update(['sync_ps_product' => 1]);
                    }

                    DB::commit();
                    DB::connection('we_shop')->commit();

                    $res_messages[] = Partner::uploadLogo($shop_response['id_shop'], $id_shop_group, $request);
                    $this->htaccessReGenerate();
                    $this->updateCache();

                    return [
                        'status' => true,
                        'message' => 'Shop created successfully.',
                        'response' => $res_messages,
                        'shop_url' => 'https://'.$shop_domain,
                    ];
                }

                return [
                    'status' => false,
                    'message' => 'Shop create failed.',
                    'response' => $res_messages,
                ];
            }catch (Exception $e){
                DB::rollBack();
                DB::connection('we_shop')->rollBack();
                return [
                    'status' => false,
                    'message' => $e->getMessage()
                ];
            }

        }

        return view('prestashop.shop.shop-creator', [
            'partner_affiliates' => collect($partner_affiliates)->groupBy('partner_type')
        ]);
    }

    public function shopDelete($id_shop){
        Partner::deleteShopData($id_shop);
        $this->htaccessReGenerate();
        $this->updateCache();
        die('Shop Deleted Sccessfully');
    }

    public function trainigplancreator(Request $request){
        if(!UserAccess::hasAccess(UserAccess::TRAINING_PLAN_CREATOR_ACCESS, 'view')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }
        $input = $request->all();
        $program = DB::table('pas_partner as p')
                    ->select('g.id','g.name')
                    ->join('pas_price_book_program_map as m', 'm.price_book_id','=','p.price_book_id')
                    ->join('pas_program as g', 'g.id','=','m.program_id')
                    ->where('p.id','=',User::getPartnerDetail('id'))
                    ->orderBy('g.name','asc')
                    ->get()->all();
        return view('prestashop.shop.training-plan', compact('program'));
    }

    public function downloadtraining(Request $request){
        $input = $request->all();
        $partner = DB::table('pas_partner')
                    ->where('id','=',User::getPartnerDetail('id'))
                    ->get()->first();
        $program = DB::table('pas_program as p')
                    ->select('p.*','m.program_list_price')
                    ->join('pas_price_book_program_map as m', 'm.program_id','=','p.id')
                    ->where('p.id','=',$input['training']['program'])
                    ->where('m.price_book_id','=',$partner->price_book_id)
                    ->get()->first();

        /*$logo_u = "https://info.worldeducation.net/modules/zoho/get-logo.php?name=".urlencode(trim($partner->partner_name));
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $logo_u,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ));
        $response = curl_exec($curl);  
        $arr = json_decode($response);
        if($arr->status == 'success'){
            $logo = "https://info.worldeducation.net/img/".$arr->logo;
        }else{
            $logo = "https://pas-contents.s3.amazonaws.com/dashboard/images/icon/logo.png";
        }*/
        $str = '';
        $str .= '<table autosize="0" border="0" cellspacing="0" cellpadding="1" style="width:100%;">';
        $str .= '<tr>';
        $str .= '<td>'.$partner->contact_name.'</td>';
        $str .= '</tr>';
        $str .= '<tr>';
        $str .= '<td>'.$partner->contact_title.'</td>';
        $str .= '</tr>';
        $str .= '<tr>';
        $str .= '<td>'.$partner->partner_name.'</td>';
        $str .= '</tr>';
        $str .= '<tr>';
        $str .= '<td>'.$partner->campus_name_if_applicable.'</td>';
        $str .= '</tr>';
        $str .= '<tr>';
        $str .= '<td>'.$partner->billing_street.'</td>';
        $str .= '</tr>';
        $str .= '<tr>';
        $str .= '<td>'.$partner->billing_city.', '.$partner->billing_state.', '.$partner->billing_code.'</td>';
        $str .= '</tr>';
        $str .= '<tr>';
        $str .= '<td>'.$partner->pi_phone.'</td>';
        $str .= '</tr>';
        $str .= '<tr>';
        $str .= '<td>'.$partner->tp_website.'</td>';
        $str .= '</tr>';
        $str .= '<tr>';
        $str .= '<td>&nbsp;</td>';
        $str .= '</tr>';
        $str .= '</table>';
        $str .= '<table autosize="0" border="0" cellspacing="3" cellpadding="1" style="width:100%;margin-top: 10px;">';
        $str .= '<tr>';
        $str .= '<td style="width: 30%;">Student Name:</td>';
        $str .= '<td style="background-color: #d4f4f5;">'.$input['training']['first_name'].' '.$input['training']['last_name'].'</td>';
        $str .= '</tr>';
        $str .= '<tr>';
        $str .= '<td>Start Date:</td>';
        $str .= '<td style="background-color: #d4f4f5;">'.date('m/d/Y', strtotime($input['training']['start_date'])).'</td>';
        $str .= '</tr>';
        $str .= '<tr>';
        $str .= '<td>End Date:</td>';
        $str .= '<td style="background-color: #d4f4f5;">'.date("m/d/Y", strtotime($input['training']['end_date'])).'</td>';
        $str .= '</tr>';
        $str .= '</table>';
        $str .= '<h1 style="text-align:center;">'.stripslashes($program->name).'<br> '.$program->code.'</h1>';                
        $str .= '<h1>Training Plan information</h1>';                
        $str .= '<table autosize="0" border="0" cellspacing="0" cellpadding="1" style="width:100%;margin-top: 10px;">';
        $str .= '<tr>';
        $str .= '<td><strong>Program Clock Hours:</strong> '.$program->hours.' </td>';
        $str .= '</tr>';
        $str .= '<tr>';
        $str .= '<td><strong>Duration:</strong> '.$program->duration_value.' '.$program->duration_type.' </td>';
        $str .= '</tr>';                
        $str .= '<tr>';
        $str .= '<td><strong>Tuition:</strong> $ '.number_format($program->program_list_price,2).' </td>';
        $str .= '</tr>';
        $str .= '<tr>';
        if($program->certification_inclusion != ''){
            $certificate_inclusion_data = json_decode($program->certification_inclusion, true);        
            $certification_inclusion = $certificate_inclusion_data[0]['Compliance_Certifying_Agency']['name'];
        }else{
            $certification_inclusion = '';
        }
        
        $str .= '<td><strong>Certification:</strong> '.$certification_inclusion.'</td>';
        $str .= '</tr>';
        $str .= '<tr>';
        $str .= '<td><strong>Optional Externship Included:</strong> '.$program->certification_included.'  </td>';
        $str .= '</tr>';
        $str .= '<tr>';
        $str .= '<td><strong>Program Type:</strong> '.$program->program_type.' </td>';
        $str .= '</tr>';
        $str .= '<tr>';
        $str .= '<td><strong>Course Delivery:</strong>'.@implode(json_decode($program->delivery_methods_available, true), ',').'  </td>';
        $str .= '</tr>';
        $str .= '<tr>';
        $str .= '<td><hr/></td>';
        $str .= '</tr>';
        $str .= '</table>';  
        $str .= '<h4>Description</h4>';
        $str .= '<p>'.nl2br(stripslashes($program->website_short_description)).'</p>'; 
        $str .= '<hr/>';  
        $str .= '<h4>Certification</h4>';
        $str .= '<p>'.nl2br(stripslashes($program->certification)).'</p>'; 
        $str .= '<hr/>';  
        $str .= '<h4>Outline</h4>';
        $str .= '<p>'.nl2br(stripslashes($program->outline)).'</p>';
        $str .= '<hr/>';  
        $str .= '<h4>Required Materials</h4>';
        $str .= '<p>'.nl2br(stripslashes($program->technical_requirements)).'</p>';
        $str .= '<hr/>';  
        $str .= '<h4>Instructor Description</h4>';
        $str .= '<p>'.nl2br(stripslashes($program->support_description)).'</p>';
            //echo $str;die;
        $mpdf = new mPDF(['mode' => 'utf-8','format' => 'A4','shrink_tables_to_fit'=>1,'setAutoTopMargin' => 'pad', 'setAutoBottomMargin' => 'pad', 'margin_left' => 10,'margin_right' => 10]/*[
                'mode' => 'utf-8',
                'format' => 'A4',
                
                'margin_right' => 5,
                'margin_top' => 5,
                'margin_bottom' => 5
        ]*/);
        //$mpdf->shrink_tables_to_fit=0;
        if(!empty($partner->logo)){
            $logo = env('S3_PATH_BUCKET_PATH').'partner/'.$partner->logo;
            $mpdf->SetHTMLHeader('<img src="' . $logo . '"  height="100"/>');
        }
        $ft = "<table border='0' cellspacing='0' cellpadding='0' style='width:100%;'>
                <tr>
                    <td width='70%'>This is a proposed training plan generated on ".date('m/d/Y')."</td>
                    <td  width='30%' style='text-align: right;'>".$partner->pi_phone."</td>
                </tr>
            </table>";
        $mpdf->SetHTMLFooter($ft."<div style='text-align:center'>{PAGENO}</div>");
        $mpdf->WriteHTML($str);
        $mpdf->Output(stripslashes($program->name)."_Training_Plan.pdf", 'D');
    }

    public function fetchprogram(Request $request){
        try{
            $id = $request->id;
            $program = DB::table('pas_program')->select('duration_type','duration_value')->where('id','=',$id)->get()->first();
            if($request->start_date != ''){
                $start_datear = explode('/',$request->start_date);
                $start_date = $start_datear[2].'-'.$start_datear[0].'-'.$start_datear[1];
            }else{
                $start_date = date("Y-m-d");
            }
            $end_date = date("m/d/Y",strtotime($start_date.'+'.$program->duration_value.' '.$program->duration_type));
            $data = array("start_date"=>date("m/d/Y", strtotime($start_date)), "end_date"=>$end_date);
            return response()->json(["status"=>"success", "data"=>$data]);
        }catch (Exception $e){
            return response()->json(['status' => 'success', 'message' => 'Appointment updated failed.', 'error' => $e->getMessage()]);
        }
    }

    public function mycaatrainigplancreator(Request $request){
        if(!UserAccess::hasAccess(UserAccess::MYCAA_TRAINING_PLAN_CREATOR_ACCESS, 'view')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }
        
        $program = DB::table('pas_partner as p')
                    ->select('g.id','g.name')
                    ->join('pas_price_book_program_map as m', 'm.price_book_id','=','p.price_book_id')
                    ->join('pas_program as g', 'g.id','=','m.program_id')
                    ->where('p.id','=',User::getPartnerDetail('id'))
                    ->orderBy('g.name','asc')
                    ->get()->all();
        return view('prestashop.shop.mycaa-training-plan', compact('program'));
    }

    

    public function downloadmycaatraining(Request $request){
        $input = $request->all();
        $partner = DB::table('pas_partner')
                    ->where('id','=',User::getPartnerDetail('id'))
                    ->get()->first();
        $program = DB::table('pas_program as p')
                    ->select('p.*','m.program_list_price')
                    ->join('pas_price_book_program_map as m', 'm.program_id','=','p.id')
                    ->where('p.id','=',$input['training']['program'])
                    ->where('m.price_book_id','=',$partner->price_book_id)
                    ->get()->first();
        $str = '';
        $str .= '<h2 style="color: #df0808;font-weight:bold;">MyCAA Education Training Plan (ETP)</h2>';
        $str .= '<div style="height:2px;width:100%;border:2px solid #134467;"></div>';
        $str .= '<table cellpadding="5" cellspacing="5" border="0" style="width: 100%;">';
        $str .= '<tr>';
        $str .= '<td style="font-weight: bold;">See ETP Guidance Sheet for full instructions.</td>';
        $str .= '</tr>';
        $str .= '<tr>';
        $str .= '<td style="font-weight: bold;text-align: right;color: red;">Required fields are in red.</td>';
        $str .= '</tr>';
        $str .= '</table>';
        $str .= '<span style="font-weight: bold;color:#1a578a;">School Contact Information:</span>';
        $str .= '<table cellpadding="5" cellspacing="5" border="1" style="width: 100%;border-collapse: collapse;">';
        $str .= '<tr>';
        $str .= '<td style="background-color: #113a5b;width: 40%;color: #fff;">School Name and Campus:</td>';
        $str .= '<td style="background-color: #d6e2ff;color: #000;">'.$partner->partner_name.'</td>';
        $str .= '</tr>';
        $str .= '<tr>';
        $str .= '<td style="background-color: #113a5b;width: 40%;color: #fff;">Campus Street Address:</td>';
        $str .= '<td style="background-color: #d6e2ff;color: #000;">'.$partner->billing_street.'</td>';
        $str .= '</tr>';
        $str .= '<tr>';
        $str .= '<td style="background-color: #113a5b;width: 40%;color: #fff;">Campus City, State, ZIP Code:</td>';
        $str .= '<td style="background-color: #d6e2ff;color: #000;">'.$partner->billing_city.', '.$partner->billing_state.', '.$partner->billing_code.'</td>';
        $str .= '</tr>';
        $str .= '<tr>';
        $str .= '<td style="background-color: #113a5b;width: 40%;color: #fff;">Campus Phone Number:</td>';
        $str .= '<td style="background-color: #d6e2ff;color: #000;">'.$partner->pi_phone.'</td>';
        $str .= '</tr>';
        $str .= '<tr>';
        $str .= '<td style="background-color: #113a5b;width: 40%;color: #fff;">School Website URL:</td>';
        $str .= '<td style="background-color: #d6e2ff;color: #000;">'.$partner->tp_website.'</td>';
        $str .= '</tr>';
        $str .= '</table>';
        $str .= '<p></p>';
        $str .= '<span style="font-weight: bold;color:#1a578a;">Student Information:</span>';
        $str .= '<table cellpadding="5" cellspacing="5" border="1" style="width: 100%;border-collapse: collapse;">';
        $str .= '<tr>';
        $str .= '<td style="background-color: #113a5b;width: 40%;color: #fff;">Student Name:</td>';
        $str .= '<td style="background-color: #d6e2ff;color: #000;">'.$input['training']['first_name'].' '.$input['training']['last_name'].'</td>';
        $str .= '</tr>';
        $str .= '<tr>';
        $str .= '<td style="background-color: #113a5b;width: 40%;color: #fff;">School Issued Student ID:</td>';
        $str .= '<td style="background-color: #d6e2ff;color: #000;">N/A</td>';
        $str .= '</tr>';
        $str .= '<tr>';        
        if(isset($program->name)){
            $name = $program->name;
        }else{
            $name = '';
        }
        $str .= '<td style="background-color: #113a5b;width: 40%;color: #fff;">Program/Degree Name:</td>';
        $str .= '<td style="background-color: #d6e2ff;color: #000;">'.stripslashes($name).'</td>';
        $str .= '</tr>';
        $str .= '<tr>';
        $str .= '<td style="background-color: #113a5b;width: 40%;color: #fff;">Program/Degree Type:</td>';
        if(isset($program->program_type)){
            $program_type = $program->program_type;
        }else{
            $program_type = '';
        }
        $str .= '<td style="background-color: #d6e2ff;color: #000;">Certification</td>';//'.$program_type.'
        $str .= '</tr>';
        $str .= '<tr>';
        $str .= '<td style="background-color: #113a5b;width: 40%;color: #fff;">Program/Degree Duration:</td>';
        if(isset($program->duration_value)){
            $duration_value = $program->duration_value;
        }else{
            $duration_value = '';
        }
        if(isset($program->duration_type)){
            $duration_type = $program->duration_type;
        }else{
            $duration_type = '';
        }
        $str .= '<td style="background-color: #d6e2ff;color: #000;">'.$duration_value.' '.$duration_type.'</td>';
        $str .= '</tr>';
        $str .= '<tr>';
        $str .= '<td style="background-color: #113a5b;width: 40%;color: #fff;">Scheduled Start Date:</td>';
        $str .= '<td style="background-color: #d6e2ff;color: #000;">'.date("m/d/Y", strtotime($input['training']['start_date'])).'</td>';
        $str .= '</tr>';
        $str .= '<tr>';
        $str .= '<td style="background-color: #113a5b;width: 40%;color: #fff;">Estimated Completion Date:</td>';
        $str .= '<td style="background-color: #d6e2ff;color: #000;">'.date("m/d/Y", strtotime($input['training']['end_date'])).'</td>';
        $str .= '</tr>';
        $str .= '<tr>';
        $str .= '<td style="background-color: #113a5b;width: 40%;color: #fff;">Course Delivery Format:</td>';
        $str .= '<td style="background-color: #d6e2ff;color: #000;">Online</td>';
        $str .= '</tr>';
        $str .= '</table>';
        $str .= '<p></p>';
        $str .= '<span style="font-weight: bold;color:#1a578a;">Program/Degree Overview:</span>';
        $str .= '<hr style="border-width: 2px;">';
        if(isset($program->website_short_description)){
            $website_short_description = $program->website_short_description;
        }else{
            $website_short_description = '';
        }
        if(isset($program->certification)){
            $certification = $program->certification;
        }else{
            $certification = '';
        }
        $str .= '<p style="background-color: #d6e2ff;">'.nl2br(stripslashes($website_short_description)).'</p>';
        $str .= '<span style="font-weight: bold;color:#1a578a;">Degree/Certification/Licensure Earned upon Completion:</span>';
        $str .= '<hr style="border-width: 2px;">';
        $str .= '<p style="background-color: #d6e2ff;">'.nl2br(stripslashes($certification)).'<br>
<span style="font-weight: bold;">Any applicable exam vouchers or course materials are at no additional cost to our students for this program. Stated included exam fees, books, and materials will be paid for by the school for '.$name.' program.';
        $str .= '</span></p>';

        $str .= '<hr style="border-width: 2px;">';

        $str .= '<span style="font-weight: bold;color:#1a578a;">Tuition Cost + Student Cost:</span>';
        $str .= '<table cellpadding="5" cellspacing="5" border="1" style="width: 100%;border-collapse: collapse;">';
        $str .= '<tr>';
        $str .= '<td style="background-color: #113a5b;width: 30%;color: #fff;text-align: center;">Tuition Cost</td>';
        $str .= '<td style="background-color: #113a5b;width: 30%;color: #fff;text-align: center;">Student Cost</td>';
        $str .= '<td style="background-color: #113a5b;width: 30%;color: #fff;text-align: center;">Total</td>';
        $str .= '</tr>';
        $str .= '<tr>';
        
        if(isset($program->program_list_price)){
            $program_list_price = $program->program_list_price;
        }else{
            $program_list_price = 0;
        }
        if(isset($program->code)){
            $code = $program->code;
        }else{
            $code = '';
        }
        $str .= '<td style="background-color: #d6e2ff;color: #000;text-align: center;">$'.number_format($program_list_price,2).'</td>';
        $str .= '<td style="background-color: #d6e2ff;color: #000;text-align: center;">$0.00</td>';
        $str .= '<td style="background-color: #d6e2ff;color: #000;text-align: center;">$'.number_format($program_list_price,2).'</td>';
        $str .= '</tr>';
        $str .= '</table>';
        $str .= '<span style="font-weight: bold;color:#1a578a;">Course Breakdown:</span>';
        $str .= '<hr style="border-width: 2px;">';
        $str .= '<p>
                List either program code/title or individual course codes and titles below for which MyCAA financial assistance is being
                requested. Insert additional rows as needed to accommodate all required coursework or a complete course listing can be
                attached in PDF form for associate degree programs';
        $str .= '</p>';
        $str .= '<table cellpadding="5" cellspacing="5" border="1" style="width: 100%;border-collapse: collapse;">';
        $str .= '<tr>';
        $str .= '<td style="background-color: #113a5b;width: 30%;color: #fff;text-align: center;">Course/Program Code</td>';
        $str .= '<td style="background-color: #113a5b;width: 30%;color: #fff;text-align: center;">Course/Program Title</td>';
        $str .= '<td style="background-color: #113a5b;width: 30%;color: #fff;text-align: center;">Course Credits (if applicable)</td>';
        $str .= '</tr>';
        $str .= '<tr>';
        $str .= '<td style="background-color: #d6e2ff;color: #000;text-align: center;">'.$code.'</td>';
        $str .= '<td style="background-color: #d6e2ff;color: #000;text-align: center;">'.$name.'</td>';
        $str .= '<td style="background-color: #d6e2ff;color: #000;text-align: center;">'.$duration_value.' '.$duration_type.'</td>';
        $str .= '</tr>';


        if(!empty($program->certification_inclusion)){
            $inc_data = json_decode($program->certification_inclusion, 'true');

            if(count($inc_data) > 0){
                foreach ($inc_data as $item){
                    if($item['Inclusion'] == 'World Ed Included' && $item['Exam_Type'] == 'Certification' && (isset($item['Certification_Exam']['name']) || isset($item['Compliance_Certifying_Body']['name'])) ){
                        if(isset($item['Certification_Exam']['name'])){
                            $program_title = $item['Certification_Exam']['name'];
                        }else{
                            $program_title = $item['Compliance_Certifying_Body']['name'];
                        }

                        $str .= '<tr>';
                        $str .= '<td style="background-color: #d6e2ff;color: #000;text-align: center;">N/A</td>';
                        $str .= '<td style="background-color: #d6e2ff;color: #000;text-align: center;">'.$program_title.'</td>';
                        $str .= '<td style="background-color: #d6e2ff;color: #000;text-align: center;">N/A</td>';
                        $str .= '</tr>';
                    }
                }

            }

        }
        $str .= '</table>';
        $str .= '<p></p>';
        $str .= '<span style="font-weight: bold;color:#1a578a;">School Official Certification:</span>';
        $str .= '<hr style="border-width: 2px;">';
        $str .= '<small style="color: red;">By my signature below, I certify the above information is true, accurate, complete, and being submitted on behalf of the institution named in this document</small>';
        //  print"<pre>";print_r($input['training']);die;
        $sign = '';
        $dt = '';
        if(!isset($input['training']['turn_off_sign']) && !empty($partner->hosted_site)){
            $host_url = parse_url($partner->hosted_site);
            if(isset($host_url['host'])){
                $sub_domain = explode('.', $host_url['host']);
                if(isset($sub_domain[0])){
                    $s3 = \Storage::disk('s3Sign');
                    $s3filePath = 'training-plan-signatures/' . $sub_domain[0].'.png';
                    if($s3->has($s3filePath)){
                        $sign = '<img style="height:50px;" src="'.$s3->url($s3filePath).'" />';
                    }else{
                        $sign = $partner->title;
                    }
                    $dt = date("m/d/Y");
                }
            }
        }
        $str .= '<table cellpadding="10" cellspacing="10" border="0" style="width: 100%;border-collapse: collapse;margin-top: 40px;page-break-after:always;page-break-inside: avoid;">';
        $str .= '<tr>';
        $str .= '<td style="font-weight:bold;width:40%;">'.$sign.'<br/>'.$partner->title.'</td>';
        $str .= '<td style="width: 20%;"></td>';
        $str .= '<td style="font-weight: bold;width: 40%;vertical-align: bottom;">'.$dt.'</td>';
        $str .= '</tr>';
        $str .= '<tr>';
        $str .= '<td style="font-weight: bold;border-top: 1px solid;width: 40%;">Signature/Title of Authorized School Official</td>';
        $str .= '<td style="width: 20%;"></td>';
        $str .= '<td style="font-weight: bold;border-top: 1px solid;width: 40%;">Date</td>';
        $str .= '</tr>';
        $str .= '<tr>';
        $str .= '<td colspan="3" style="height: 50px;"></td>';
        $str .= '</tr>';
        $str .= '<tr>';
        $str .= '<td style="font-weight:bold;width:40%;">'.$partner->tp_contact_name.'</td>';
        $str .= '<td style="width: 20%;"></td>';
        $str .= '<td style="font-weight: bold;width: 40%;">'.$partner->email.' '.$partner->phone.'</td>';
        $str .= '</tr>';
        $str .= '<tr>';
        $str .= '<td style="font-weight: bold;border-top: 1px solid;">School Official Printed First and Last Name</td>';
        $str .= '<td></td>';
        $str .= '<td style="font-weight: bold;border-top: 1px solid;">School Official E-mail and Phone Number</td>';
        $str .= '</tr>';
        $str .= '</table>';        
        $str .= '<table cellpadding="5" cellspacing="5" border="0" style="width: 100%;border-collapse: collapse;">';
        $str .= '<tr>';
        $str .= '<td style="text-align: center;"><img src="images/1.jpg" /></td>';
        $str .= '</tr>';
        $str .= '</table>';
               
        $str .= '<table cellpadding="5" cellspacing="5" border="0" style="width: 100%;border-collapse: collapse;">';
        $str .= '<tr>';
        $str .= '<td style="text-align: center;"><img src="images/2.jpg" /></td>';
        $str .= '</tr>';
        $str .= '</table>';
               
        $str .= '<table cellpadding="5" cellspacing="5" border="0" style="width: 100%;border-collapse: collapse;">';
        $str .= '<tr>';
        $str .= '<td style="text-align: center;"><img src="images/3.jpg" /></td>';
        $str .= '</tr>';
        $str .= '</table>';
            //echo $str;die;
        $mpdf = new mPDF([
                'mode' => 'utf-8',
                'format' => 'A4',
                'margin_left' => 5,
                'margin_right' => 5,
                'margin_top' => 5,
                'margin_bottom' => 11,
                'pagenumPrefix' => 'Page ',
                'nbpgPrefix' => ' of ',
                'nbpgSuffix' => ' pages'
        ]);
        
        //$mpdf->SetHTMLHeader('<img src="' . $logo . '"  width="172"/>');
        $mpdf->SetHTMLFooter("<div style='text-align:center'>{PAGENO}{nbpg}</div>");
        $mpdf->WriteHTML($str);
        $mpdf->Output("Training_Plan_".stripslashes($name).".pdf", 'D');
    }

    public function getLaptopApi(Request $request)
    {
        $partner = DB::table('pas_partner')->select('id')->where('partner_name', '=', $request->partner_name)->get()->first();

        if(!$partner){
            return response()->json(['status' => false, 'message' => 'Partner does not exists.']);
        }

        $setting = DB::table('ps_configuration')
            ->select(['content', 'is_active'])
            ->where('partner_id', '=', $partner->id)
            ->where('type', '=', 'laptop-content')
            //->where('is_active', '=', 1)
            ->get()->first();

        if(!$setting){
            $setting_default_shop = DB::table('ps_configuration AS con')
                ->join('pas_partner AS p', 'p.id', '=', 'con.partner_id')
                ->select(['con.content', 'con.is_active'])
                ->where('p.partner_name', '=', env('DEFAULT_PROMOTION_SHOP'))
                ->where('con.type', '=', 'laptop-content')
                //->where('is_active', '=', 1)
                ->get()->first();
            if(!$setting_default_shop){
                return response()->json(['status' => true, 'data' => []]);
            }
            $setting = $setting_default_shop;
        }

        if(!empty($setting->content)){
            $result = json_decode($setting->content, true);
            if(!empty($result['image'])){
                $result['image'] = env('S3_PATH').'ps-banner/'.$result['image'];
            }
            $result['is_active'] = (bool) $setting->is_active;
            return response()->json(['status' => true, 'data' => $result]);
        }
    }
    
    public function getReferApi(Request $request)
    {
        $partner = DB::table('pas_partner')->select('id')->where('partner_name', '=', $request->partner_name)->get()->first();

        if(!$partner){
            return response()->json(['status' => false, 'message' => 'Partner does not exists.']);
        }

        $setting = DB::table('ps_configuration')
            ->select(['content', 'is_active'])
            ->where('partner_id', '=', $partner->id)
            ->where('type', '=', 'refer-friend')
            //->where('is_active', '=', 1)
            ->get()->first();

        if(!$setting){
            $setting_default_shop = DB::table('ps_configuration AS con')
                ->join('pas_partner AS p', 'p.id', '=', 'con.partner_id')
                ->select(['con.content', 'con.is_active'])
                ->where('p.partner_name', '=', env('DEFAULT_PROMOTION_SHOP'))
                ->where('type', '=', 'refer-friend')
                //->where('is_active', '=', 1)
                ->get()->first();
            if(!$setting_default_shop){
                return response()->json(['status' => true, 'data' => []]);
            }
            $setting = $setting_default_shop;
        }

        if(!empty($setting->content)){
            $result = json_decode($setting->content, true);
            if(!empty($result['image'])){
                $result['image'] = env('S3_PATH').'ps-banner/'.$result['image'];
            }
            $result['is_active'] = (bool) $setting->is_active;
            return response()->json(['status' => true, 'data' => $result]);
        }
    }

    public function zipCodeLocator(Request $request){
        $url = env('PRESTASHOP_DEFAULT_SHOP');

        if(isset($request->outsideUS)){
            die($url);
        }

        if(!empty($request->zip_code)){
            $partners = DB::table('pas_partner')
                ->where('zip_code', 'LIKE', '%'.$request->zip_code.'%')->pluck('partner_name')->toArray();

            foreach ($partners as $partner) {
                $partner_url = DB::connection('we_shop')
                    ->table('ps_shop')
                    ->join('ps_shop_url', 'ps_shop_url.id_shop', '=', 'ps_shop.id_shop')
                    ->where('name', '=', $partner)
                    ->value('domain');

                if($partner_url){
                    $url = 'https://'.$partner_url;
                    break;
                }
            }
        }

        die($url);
    }

}
