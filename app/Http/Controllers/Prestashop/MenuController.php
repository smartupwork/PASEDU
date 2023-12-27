<?php
namespace App\Http\Controllers\Prestashop;
use App\Http\Controllers\Controller;
use App\Models\Partner;
use App\Models\Program;
use App\Models\User;
use App\Models\UserAccess;
use App\Utility;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Session;
use Config;
use Lang;
require base_path("vendor/autoload.php");
use Cookie;

class MenuController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        if(!UserAccess::hasAccess(UserAccess::PARTNER_ADMIN_ACCESS, 'view')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }
        $menu = DB::table('pas_partner')->where('id', '=', User::getPartnerDetail('id'))->value('prestashop_menu');

        $menu_array = !empty($menu) ? json_decode($menu, true):[];
        return view('prestashop.menu.index', compact('menu_array'));
    }


    public function save(Request $request){
        if(!UserAccess::hasAccess(UserAccess::PARTNER_ADMIN_ACCESS, 'add')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }

        $partner_name = User::getPartnerDetail('partner_name');

        $res = Partner::updateMainMenu($partner_name, $request->menu_name);;

        if($res->status){
            DB::table('pas_partner')->where('id', '=', User::getPartnerDetail('id'))->update([
                'prestashop_menu' => $request->menu_name
            ]);
            return response()->json(['status' => 'success', 'message' => 'Menu updated successfully.']);
        }
        return response()->json(['status' => 'fail', 'message' => $res->message]);
    }

    /*private function updateMainMenu($request){
        $partner_name = User::getPartnerDetail('partner_name');
        //$partner_name = 'University of Texas at Arlington';

        $post_data = [
            'partner_name' => $partner_name,
            'active_menu' => $request->menu_name,
        ];

        $client = new Client();

        $response = $client->post($_ENV['PRESTASHOP_BASE_URL'].'/modules/pasapi/update-menu.php', [
            'headers' => [
                //'Authorization' => 'Bearer '. $this->access_token,
                'Accept'        => 'application/json',
            ],
            'form_params' => $post_data,
        ]);

        $body = $response->getBody();
        //echo '<pre>';print_r(json_decode((string) $body));die;
        return json_decode((string) $body);
    }*/

    public function migrateMenu($start, $limit){
        /*DB::connection('we_shop')->table('ps_linksmenutop_lang')->truncate();
        DB::connection('we_shop')->table('ps_linksmenutop')->truncate();*/

        $shops = DB::connection('we_shop')->table('ps_shop')
            ->where('active', '=', 1)
            ->where('deleted', '=', 0)
            ->skip($start)
            ->limit($limit)
            ->get()->all();
//dd($shops);
        $data = [];
        foreach ($shops as $shop) {

            if ($shop->id_shop_group == 1) {
                $data[] = [
                    'id_shop' => $shop->id_shop,
                    'id_lang' => 1,
                    'label' => 'School Locator',
                    'link' => null,
                    'position' => 1,
                ];
            } else {
                $data[] = [
                    'id_shop' => $shop->id_shop,
                    'id_lang' => 1,
                    'label' => 'Home',
                    'link' => '/',
                    'position' => 1,
                ];
                $data[] = [
                    'id_shop' => $shop->id_shop,
                    'id_lang' => 1,
                    'label' => 'Catalog',
                    'link' => '2-home',
                    'position' => 2,
                ];

                if($shop->id_shop_group == 2){
                    $data[] = [
                        'id_shop' => $shop->id_shop,
                        'id_lang' => 1,
                        'label' => 'Military',
                        'link' => '/military',
                        'position' => 3,
                    ];
                    $data[] = [
                        'id_shop' => $shop->id_shop,
                        'id_lang' => 1,
                        'label' => 'MyCAA',
                        'link' => '/mycaa',
                        'position' => 4,
                    ];
                    $data[] = [
                        'id_shop' => $shop->id_shop,
                        'id_lang' => 1,
                        'label' => 'Promotion',
                        'link' => '/promotions',
                        'position' => 5,
                    ];
                    $data[] = [
                        'id_shop' => $shop->id_shop,
                        'id_lang' => 1,
                        'label' => 'Vocational Rehab',
                        'link' => '/vocational-rehab',
                        'position' => 6,
                    ];
                    $data[] = [
                        'id_shop' => $shop->id_shop,
                        'id_lang' => 1,
                        'label' => 'Workforce',
                        'link' => '/workforce',
                        'position' => 7,
                    ];
                    $data[] = [
                        'id_shop' => $shop->id_shop,
                        'id_lang' => 1,
                        'label' => 'Contact Us',
                        'link' => '/contact-us',
                        'position' => 8,
                    ];
                }elseif($shop->id_shop_group == 3){
                    $data[] = [
                        'id_shop' => $shop->id_shop,
                        'id_lang' => 1,
                        'label' => 'Contact Us',
                        'link' => '/contact-us',
                        'position' => 3,
                    ];
                }


            }
        }
        //echo '<pre>';print_r($data);die;
        foreach ($data as $menu) {
            DB::connection('we_shop')->table('ps_linksmenutop')->insert([
                'id_shop' => $menu['id_shop'],
                'new_window' => 0,
                'position' => $menu['position'],
            ]);

            unset($menu['position']);

            $menu['id_linksmenutop'] = DB::connection('we_shop')->getPdo()->lastInsertId();

            DB::connection('we_shop')->table('ps_linksmenutop_lang')->insert($menu);
        }

        die('Menu Successfully imported.');
    }

    public function addPromotionMenuInPAS($start, $limit, $debug = 0){
        $partners = DB::table('pas_partner AS p')
            ->select(['id', 'partner_name', 'prestashop_menu'])
            ->where(function($query){
                $query->orWhere('p.prestashop_menu', 'NOT LIKE', '%Promotions%')
                    ->orWhereNull('p.prestashop_menu');
            })
            /*->where('s.active', '=', 1)
            ->where('s.deleted', '=', 0)*/
            ->skip($start)
            ->limit($limit)
            ->get()->all();
            //dd($partners);

        foreach ($partners as $partner) {
            $menu = [];
            if(!empty($partner->prestashop_menu)){
                $p_menu = json_decode($partner->prestashop_menu, true);
                //dd($p_menu);
                array_push($p_menu, "Promotions");
                $menu = $p_menu;
            }else{
                $menu[] = "Promotions";
            }

            if($debug == 1){
                dump([$partner->partner_name, $menu]);
            }else{
                DB::table('pas_partner')
                    ->where('id', '=', $partner->id)
                    ->update(['prestashop_menu' => json_encode($menu)]);
            }
        }

        if($debug == 0) {
            die(count($partners) . ' partner menu added/updated');
        }
    }

    public function addPromotionMenuInPS($start, $limit, $debug = 0){
        /*DB::connection('we_shop')->table('ps_linksmenutop_lang')->truncate();
        DB::connection('we_shop')->table('ps_linksmenutop')->truncate();*/

        $shops = DB::connection('we_shop')->table('ps_shop AS s')
            ->select(['id_shop', 'id_shop_group', 'name'])
            ->where('s.id_shop_group', '=', 2)
            /*->where('s.active', '=', 1)
            ->where('s.deleted', '=', 0)*/
            ->skip($start)
            ->limit($limit)
            ->get()->all();

        $data = [];
        foreach ($shops as $index => $shop) {

            $menu = DB::connection('we_shop')->table('ps_linksmenutop AS m')
                ->join('ps_linksmenutop_lang AS ml', 'ml.id_linksmenutop', '=', 'm.id_linksmenutop')
                ->where('ml.id_shop', '=', $shop->id_shop)
                ->where('ml.link', '=', '/promotions')
                ->skip($start)
                ->limit($limit)
                ->count('m.id_linksmenutop');

            if($menu == 0){
                $data[$index]['menu'] = [
                    'id_shop' => $shop->id_shop,
                    'new_window' => 0,
                    'position' => 5,
                ];

                $data[$index]['menu_lang'] = [
                    'id_linksmenutop' => null,
                    'id_lang' => 1,
                    'id_shop' => $shop->id_shop,
                    'label' => 'Promotions',
                    'link' => '/promotions',
                ];
            }

        }
        if($debug){
            echo '<pre>';print_r($data);die;
        }

        foreach ($data as $menu) {
            DB::connection('we_shop')->table('ps_linksmenutop')->insert([
                'id_shop' => $menu['menu']['id_shop'],
                'new_window' => 0,
                'position' => $menu['menu']['position'],
            ]);

            unset($menu['menu']['position']);

            $menu['menu_lang']['id_linksmenutop'] = DB::connection('we_shop')->getPdo()->lastInsertId();

            DB::connection('we_shop')->table('ps_linksmenutop_lang')->insert($menu['menu_lang']);
        }
        Program::cacheClear();
        die('Promotion Menu Successfully imported.');
    }

    public function updatePromotionMenuInPS($start, $limit, $debug = 0){
        /*DB::connection('we_shop')->table('ps_linksmenutop_lang')->truncate();
        DB::connection('we_shop')->table('ps_linksmenutop')->truncate();*/

        $shops = DB::connection('we_shop')->table('ps_shop AS s')
            ->select(['id_shop', 'id_shop_group', 'name'])
            ->where('s.id_shop_group', '=', 2)
            /*->where('s.active', '=', 1)
            ->where('s.deleted', '=', 0)*/
            ->skip($start)
            ->limit($limit)
            ->get()->all();

        foreach ($shops as $index => $shop) {

            $menu = DB::connection('we_shop')->table('ps_linksmenutop_lang AS ml')
                ->where('ml.id_shop', '=', $shop->id_shop)
                ->where('ml.label', '=', 'Promotion')
                ->skip($start)
                ->limit($limit)
                ->get()->first();

            if($menu){
                if($debug){
                    dump([$menu->id_shop, $menu->label]);
                }else{
                    DB::connection('we_shop')->table('ps_linksmenutop_lang AS ml')
                        ->where('ml.id_shop', '=', $shop->id_shop)
                        ->where('ml.label', '=', 'Promotion')
                        ->update(['label' => 'Promotions']);
                }

            }

        }

        Program::cacheClear();
        die('Promotion Menu Successfully imported.');
    }

}
