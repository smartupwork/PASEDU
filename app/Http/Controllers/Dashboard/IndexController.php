<?php
namespace App\Http\Controllers\Dashboard;
use App\EmailHelper;
use App\EmailRequest;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserAccess;
use App\UserActivityHelper;
use App\Utility;
use App\ZohoHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PHPUnit\Exception;
use Session;
use Config;
use Lang;
use Cookie;
use WebReinvent\CPanel\CPanel;

class IndexController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function main()
    {
        if(!UserAccess::hasAccess(UserAccess::HOME_DASHBOARD_ACCESS, 'view')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }

        $highlight_reports = [];
        $highlight_report_selected = [];
        $top_sellers_ids = [];
        if(User::getPartnerDetail('zoho_id')){

            $top_sellers = DB::select('select partner_id, SUM(`selling_count`) as selling_count from `pas_partner_selling_program_map` where selling_count > 0 group by `partner_id` ORDER BY selling_count DESC LIMIT 10');
            $top_sellers_ids = array_column($top_sellers, 'selling_count', 'partner_id');
            //dd($top_sellers_ids);
            $dashboard_report = DB::table('pas_dashboard_report')->where('partner_id', '=', User::getPartnerDetail('id'))->get()->first();

            if(!$dashboard_report){
                DB::table('pas_dashboard_report')->insert([
                    'partner_id' => User::getPartnerDetail('id'),
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
                $dashboard_report = DB::table('pas_dashboard_report')->where('partner_id', '=', User::getPartnerDetail('id'))->get()->first();
            }

            $highlight_report_selected = Auth::user()->highlight_reports ? json_decode(Auth::user()->highlight_reports, true): array_keys(array_slice(User::getHighlightReports(), 0 ,4));

            foreach (User::getHighlightReports() as $key => $highlightReport) {
                switch ($key){
                    case 'cy-enrollments':
                        $highlight_reports[] = [
                            'slug' => $key,
                            'icon_class' => 'zmdi-calendar-note',
                            'label' => $highlightReport,
                            'value' => number_format($dashboard_report->current_year_enrollments)
                        ];
                        break;
                    case 'cy-revenue':
                        $highlight_reports[] = [
                            'slug' => $key,
                            'icon_class' => 'zmdi zmdi-money',
                            'label' => $highlightReport,
                            'value' => number_format($dashboard_report->current_year_revenue)
                        ];
                        break;
                    case 'active-enrollments':
                        $highlight_reports[] = [
                            'slug' => $key,
                            'icon_class' => 'zmdi zmdi-assignment-check',
                            'label' => $highlightReport,
                            'value' => number_format($dashboard_report->active_enrollments)
                        ];
                        break;
                    case 'lifetime-enrollments':
                        $highlight_reports[] = [
                            'slug' => $key,
                            'icon_class' => 'zmdi zmdi-shield-check',
                            'label' => $highlightReport,
                            'value' => number_format($dashboard_report->life_time_enrollments)
                        ];
                        break;
                    case 'completion-rate':
                        $highlight_reports[] = [
                            'slug' => $key,
                            'icon_class' => 'zmdi zmdi-assignment-check',
                            'label' => $highlightReport,
                            'value' => round(($dashboard_report->completion_rate * 100)).'%'
                        ];
                        break;
                    case 'retention-rate':
                        $highlight_reports[] = [
                            'slug' => $key,
                            'icon_class' => 'zmdi zmdi-assignment-check',
                            'label' => $highlightReport,
                            'value' => round(($dashboard_report->retention_rate * 100)).'%'
                        ];
                        break;
                    case 'lifetime-revenue':
                        $highlight_reports[] = [
                            'slug' => $key,
                            'icon_class' => 'zmdi zmdi-money',
                            'label' => $highlightReport,
                            'value' => number_format($dashboard_report->lifetime_revenue)
                        ];
                        break;
                }
            }

            if(count($highlight_reports) == 0){
                $highlight_reports = array_slice(User::getHighlightReports(), 0 ,4);
            }

        }

        return view('dashboard.index', compact('top_sellers_ids', 'highlight_reports', 'highlight_report_selected'));
    }

    public function change_dashboard(Request $request){
        if(!UserAccess::hasAccess(UserAccess::STATS_ACCESS, 'view')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }

        $highlight_report_selected = Auth::user()->highlight_reports ? json_decode(Auth::user()->highlight_reports, true): array_keys(array_slice(User::getHighlightReports(), 0 ,4));

        $data['action'] = 'update';
        $data['old_data'] = json_encode($highlight_report_selected);
        $data['new_data'] = json_encode($request->report);
        UserActivityHelper::getInstance()->save($request, $data);

        $active_reports = $request->get('report');
        DB::table('pas_users')->where([["id", '=', Auth::user()->id]])->update(['highlight_reports' => json_encode($active_reports)]);
        return response()->json(["status"=>"success", 'msg' => 'Dashboard report updated.']);
    }

    public function aws_api(){
        /*$s3 = App::make('aws')->createClient('s3');
        $s3response = $s3->createBucket(array(
            'Bucket'     => 'www.xwds.'.env('CPANEL_ROOT_DOMAIN'),
            //'ACL'    => 'public-read',
        ));*/

        //$s3response = $s3->listBuckets();
        //dd($s3response);

        $route53 = App::make('aws')->createClient('Route53');

        $dns_cname_data = array(
            // HostedZoneId is required
            'HostedZoneId' => env('AWS_HOSTED_ZONE_ID'),
            // ChangeBatch is required
            'ChangeBatch' => array(
                'Comment' => 'Shop DNS CNAME record create',
                // Changes is required
                'Changes' => array(
                    array(
                        // Action is required
                        'Action' => 'UPSERT', // CREATE, DELETE, UPSERT
                        // ResourceRecordSet is required
                        'ResourceRecordSet' => array(
                            // Name is required
                            'Name' => 'xwds.'.env('CPANEL_ROOT_DOMAIN'),
                            // Type is required
                            'Type' => 'CNAME',
                            'TTL' => 3600,
                            'ResourceRecords' => array(
                                array(
                                    // Value is required
                                    'Value' => env('AWS_CNAME'),
                                ),
                            ),
                            /*'AliasTarget' => array(
                                // HostedZoneId is required
                                'HostedZoneId' => env('AWS_HOSTED_ZONE_ID'),
                                // DNSName is required
                                'DNSName' => 'xwdstest',
                                // EvaluateTargetHealth is required
                                'EvaluateTargetHealth' => false,
                            ),
                            'HealthCheckId' => 'string',*/
                        ),
                    ),
                ),
            ),
        );

        /*$dns_a_data = array(
            // HostedZoneId is required
            'HostedZoneId' => env('AWS_HOSTED_ZONE_ID'),
            // ChangeBatch is required
            'ChangeBatch' => array(
                'Comment' => 'Shop DNS A record create',
                // Changes is required
                'Changes' => array(
                    array(
                        // Action is required
                        'Action' => 'CREATE', // CREATE, DELETE, UPSERT
                        // ResourceRecordSet is required
                        'ResourceRecordSet' => array(
                            // Name is required
                            'Name' => 'xwds.'.env('CPANEL_ROOT_DOMAIN'),
                            // Type is required
                            'Type' => 'A',
                            'TTL' => 300,
                            'ResourceRecords' => array(
                                array(
                                    'Value' => env('AWS_S3_WEBSITE'),
                                ),
                            ),
                            'AliasTarget' => array(
                                'HostedZoneId' => env('AWS_HOSTED_ZONE_ID'),
                                'DNSName' => 'ELB Classic Load Balancer',
                                'EvaluateTargetHealth' => true,
                            ),
                            //'HealthCheckId' => 'string'
                        ),
                    ),
                ),
            ),
        );

        dump($dns_a_data);*/

        try{
            $r53CNameresponse = $route53->changeResourceRecordSets($dns_cname_data);
            dd($r53CNameresponse);
        }catch (Exception $e){
            dd($e);
        }

//        $r53Aresponse = $route53->changeResourceRecordSets($dns_a_data);

        /*$r53response = $route53->listResourceRecordSets([
            'HostedZoneId' => env('AWS_HOSTED_ZONE_ID')
        ]);*/


    }

    public function cpanel_api(){
        $cpanel = new CPanel(env('CPANEL_DOMAIN'), env('CPANEL_API_TOKEN'), env('CPANEL_USERNAME'), env('CPANEL_PROTOCOL'), env('CPANEL_PORT'));

        $Module = 'SubDomain';
        $function = 'addsubdomain';
        /*$parameters_array = [
            'user'=>'ftp_username',
            'pass'=>'ftp_password', //make sure you use strong password
            'quota'=>'42',
        ];*/

        $parameters_array = [
            'domain' => 'xwds',
            'rootdomain' => env('CPANEL_ROOT_DOMAIN'),
            'dir' => env('CPANEL_SUB_DOMAIN_DIR'),
        ];

        $response = $cpanel->callUAPI($Module, $function, $parameters_array);
        echo '<pre>';print_r($response);die;
        //$cpanel = new CPanel();

        $response = $cpanel->listDatabases();
        dd($response);
        $cpanel = App::make('cpanel');
        $accounts = $cpanel->createSubdomain('xwds');
        dd($accounts);
        // passing parameters
        //$accounts = $cpanel->listaccts($searchtype, $search);

    }

    public function test_email(){

        $email_req = new EmailRequest();
        $email_req
            //->setSMTPDebug(2)
            /*->setFromName($_ENV['FROM_NAME'])
            ->setFromEmail($_ENV['FROM_EMAIL'])*/
            ->setSubject('Test Email SMTP')
            ->setBody('Test Email With SMTP')
            ->setTo([['rajneeshxwds@gmail.com', 'rajneeshxwds@gmail.com']])
            //->setTo([['rajneesh@xoomwebdevelopment.com', 'Rajneesh']])
            ->setLogSave(true);

        $email_helper = new EmailHelper($email_req);
        $email_res = $email_helper->sendEmail();
        dd($email_res);

        $to = "rajneeshxwds@gmail.com, xoomwebdevelopment@gmail.com ";
        $subject = "HTML email";

        $message = "
<html>
<head>
<title>HTML email</title>
</head>
<body>
<p>This email contains HTML Tags!</p>
<table>
<tr>
<th>Firstname</th>
<th>Lastname</th>
</tr>
<tr>
<td>John</td>
<td>Doe</td>
</tr>
</table>
</body>
</html>
";

// Always set content-type when sending HTML email
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

// More headers
        $headers .= 'From: <info@partner-worldeducation.net>' . "\r\n";

        dd(mail($to,$subject,$message,$headers));


       /* $servername = "54.189.235.186";
        $username = "worldedu_shops_new";
        $password = "+r[C&GxHy^4~";

// Create connection
        $conn = new \mysqli($servername, $username, $password);

// Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        echo "Connected successfully";
die;*/
        try{
            $shop = DB::connection('we_shop')->table('ps_product_lang')->limit(10)->get()->all();
            dd($shop);
        }catch(Exception $e){
            dump(DB::connection('we_shop'));
            dd($e);
        }



        $program = ZohoHelper::getInstance()->fetchByIds('Products', ['1066248000487703001']);
        echo '<pre>';print_r($program);die;
        $criteria = [
            ['Email', 'equals', 'rajneeshxwds@test.com'],
        ];
dump($criteria);
        $contact = ZohoHelper::getInstance()->fetchCriteria('Contacts', ['Owner'], 1, 1, $criteria);
        dd($contact);
    }
    public function session_refresh(){
        return response()->json(['msg' => 'Session Updated.'])->setStatusCode(200);
    }

}
