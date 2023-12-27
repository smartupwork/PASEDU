<?php

namespace App\Models;

use Aws\Route53\Exception\Route53Exception;
use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use PHPUnit\Exception;
use WebReinvent\CPanel\CPanel;

class Partner extends Model
{
    use HasFactory;

    protected $table = 'pas_partner';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'zoho_id',
        'partner_name',
        'status',
    ];

    public $timestamps = true;


    public function priceBook() {
        return $this->hasOne('App\Models\PriceBook', 'zoho_id', 'price_book_zoho_id');
    }

    public static function updateMainMenu($partner_name, $menu){
        //$partner_name = 'University of Texas at Arlington';

        $post_data = [
            'partner_name' => $partner_name,
            'active_menu' => $menu,
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
    }

    public static function createSubDomainCPanle($subdomain){
        try{
            $cpanel = new CPanel(env('CPANEL_DOMAIN'), env('CPANEL_API_TOKEN'), env('CPANEL_USERNAME'), env('CPANEL_PROTOCOL'), env('CPANEL_PORT'));

            $Module = 'SubDomain';
            $function = 'addsubdomain';
            /*$parameters_array = [
                'user'=>'ftp_username',
                'pass'=>'ftp_password', //make sure you use strong password
                'quota'=>'42',
            ];*/

            $parameters_array = [
                'domain' => $subdomain,
                'rootdomain' => env('CPANEL_ROOT_DOMAIN'),
                'dir' => env('CPANEL_SUB_DOMAIN_DIR'),
            ];

            $response = $cpanel->callUAPI($Module, $function, $parameters_array);
            if($response['status'] == 'success'){
                return [
                    'status' => true,
                    'message' => sprintf('Sub domain %s created successfully', $subdomain),
                ];
            }
            return [
                'status' => false,
                'message' => isset($response['errors']) ? implode(', ', $response['errors']):'Something went wrong',
            ];
        }catch (Exception $e){
            return [
                'status' => false,
                'message' => $e->getMessage(),
            ];
        }

    }

    public static function createSubDomainRoute53($subdomain){
        try{
            $route53 = App::make('aws')->createClient('Route53');

            $dns_cname_data = array(
                'HostedZoneId' => env('AWS_HOSTED_ZONE_ID'),
                'ChangeBatch' => array(
                    'Comment' => 'Shop DNS CNAME record create',
                    'Changes' => array(
                        array(
                            'Action' => 'UPSERT', // CREATE, DELETE, UPSERT
                            'ResourceRecordSet' => array(
                                'Name' => $subdomain.'.'.env('CPANEL_ROOT_DOMAIN'),
                                'Type' => 'CNAME',
                                'TTL' => 3600,
                                'ResourceRecords' => array(
                                    array(
                                        'Value' => env('AWS_CNAME'),
                                    ),
                                ),
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
                                'Name' => $subdomain.'.'.env('CPANEL_ROOT_DOMAIN'),
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

            $route53->changeResourceRecordSets($dns_cname_data);

            return [
                'status' => true,
                'message' => sprintf('Sub domain %s created successfully', $subdomain),
            ];

        }catch (Route53Exception $e){
            return [
                'status' => false,
                'message' => $e->getMessage(),
            ];
        }

    }

    public static function createShop($name, $domain, $id_shop_group = 2, $shop_detail){
        try{
            $id_shop = DB::connection('we_shop')
                ->table('ps_shop')
                ->insertGetId([
                    'id_shop_group' => $id_shop_group,
                    'name' => $name,
                    'id_category' => 2,
                    'theme_name' => "partner",
                    'active' => 1,
                    'deleted' => 0,
                ]);

            DB::connection('we_shop')
                ->table('ps_shop_url')
                ->insertGetId([
                    'id_shop' => $id_shop,
                    'domain' => $domain,
                    'domain_ssl' => $domain,
                    'physical_uri' => '/',
                    'virtual_uri' => '',
                    'main' => 1,
                    'active' => 1,
                ]);

            $config_data = [
                [
                    'id_shop' => $id_shop,
                    'id_shop_group' => $id_shop_group,
                    'name' => 'PS_SHOP_NAME',
                    'value' => $shop_detail['shop']['name'],
                    'date_add' => date('Y-m-d H:i:s'),
                    'date_upd' => date('Y-m-d H:i:s'),
                ],
                [
                    'id_shop' => $id_shop,
                    'id_shop_group' => $id_shop_group,
                    'name' => 'PS_LOGO',
                    'value' => '',
                    'date_add' => date('Y-m-d H:i:s'),
                    'date_upd' => date('Y-m-d H:i:s'),
                ],
                [
                    'id_shop' => $id_shop,
                    'id_shop_group' => $id_shop_group,
                    'name' => 'PS_SHOP_STATE',
                    'value' => $shop_detail['contact']['state'],
                    'date_add' => date('Y-m-d H:i:s'),
                    'date_upd' => date('Y-m-d H:i:s'),
                ],
                [
                    'id_shop' => $id_shop,
                    'id_shop_group' => $id_shop_group,
                    'name' => 'PS_SHOP_STATE_ID',
                    'value' => '',
                    'date_add' => date('Y-m-d H:i:s'),
                    'date_upd' => date('Y-m-d H:i:s'),
                ],
                [
                    'id_shop' => $id_shop,
                    'id_shop_group' => $id_shop_group,
                    'name' => 'PS_SHOP_CITY',
                    'value' => $shop_detail['contact']['city'],
                    'date_add' => date('Y-m-d H:i:s'),
                    'date_upd' => date('Y-m-d H:i:s'),
                ],
                [
                    'id_shop' => $id_shop,
                    'id_shop_group' => $id_shop_group,
                    'name' => 'PS_SHOP_ADDR1',
                    'value' => $shop_detail['contact']['address'],
                    'date_add' => date('Y-m-d H:i:s'),
                    'date_upd' => date('Y-m-d H:i:s'),
                ],
                [
                    'id_shop' => $id_shop,
                    'id_shop_group' => $id_shop_group,
                    'name' => 'PS_SHOP_PHONE',
                    'value' => $shop_detail['contact']['phone'],
                    'date_add' => date('Y-m-d H:i:s'),
                    'date_upd' => date('Y-m-d H:i:s'),
                ],
                [
                    'id_shop' => $id_shop,
                    'id_shop_group' => $id_shop_group,
                    'name' => 'PS_SHOP_EMAIL',
                    'value' => $shop_detail['contact']['email'],
                    'date_add' => date('Y-m-d H:i:s'),
                    'date_upd' => date('Y-m-d H:i:s'),
                ]
            ];

            if($id_shop_group != 3){
                array_push($config_data, [
                        'id_shop' => $id_shop,
                        'id_shop_group' => $id_shop_group,
                        'name' => 'htmlbox_style',
                        'value' => $shop_detail['shop']['header'],
                        'date_add' => date('Y-m-d H:i:s'),
                        'date_upd' => date('Y-m-d H:i:s'),
                    ],
                    [
                        'id_shop' => $id_shop,
                        'id_shop_group' => $id_shop_group,
                        'name' => 'htmlbox_style_button',
                        'value' => $shop_detail['shop']['button'],
                        'date_add' => date('Y-m-d H:i:s'),
                        'date_upd' => date('Y-m-d H:i:s'),
                    ]);
            }

            DB::connection('we_shop')
                ->table('ps_configuration')
                ->insert($config_data);

            return [
                'status' => true,
                'id_shop' => $id_shop,
                'message' => 'Shop created successfully',
            ];
        }catch (Exception $e){
                return [
                    'status' => false,
                    'message' => $e->getMessage(),
                ];
        }

    }

    public static function copyModuleShop($id_shop){
        $default_shop_modules = DB::connection('we_shop')
            ->table('ps_module_shop')
            ->where('id_shop', '=', env('PRESTASHOP_DEFAULT_ID', 105))
            ->pluck('id_module')->toArray();

        if(count($default_shop_modules) > 0){
            $data = [];
            foreach ($default_shop_modules as $default_shop_module) {
                $data[] = [
                    'id_module' => $default_shop_module,
                    'id_shop' => $id_shop,
                ];
            }
            if(count($data) > 0){
                try{
                    DB::connection('we_shop')
                        ->table('ps_module_shop')
                        ->insert($data);
                    return [
                        'status' => true,
                        'message' => 'Module copied successfully.',
                    ];
                }catch (Exception $e){
                    return [
                        'status' => false,
                        'message' => $e->getMessage(),
                    ];
                }
            }
        }else{
            return [
                'status' => false,
                'message' => 'Module shop not found.',
            ];
        }
    }

    public static function copyModuleGroup($id_shop){
        $default_shop_data = DB::connection('we_shop')
            ->table('ps_module_group')
            ->where('id_shop', '=', env('PRESTASHOP_DEFAULT_ID', 105))
            ->get()->all();

        if(count($default_shop_data) > 0){
            $data = [];
            foreach ($default_shop_data as $shop_data) {
                $data[] = [
                    'id_module' => $shop_data->id_module,
                    'id_shop' => $id_shop,
                    'id_group' => $shop_data->id_group,
                ];
            }
            if(count($data) > 0){
                try {
                    DB::connection('we_shop')
                        ->table('ps_module_group')
                        ->insert($data);
                    return [
                        'status' => true,
                        'message' => 'Module Group shop copied successfully.',
                    ];
                }catch (Exception $e){
                    return [
                        'status' => false,
                        'message' => $e->getMessage(),
                    ];
                }
            }
        }else{
            return [
                'status' => false,
                'message' => 'Module Group shop not found.',
            ];
        }
    }

    public static function copyHookModuleShop($id_shop){
        $default_shop_modules = DB::connection('we_shop')
            ->table('ps_hook_module')
            ->where('id_shop', '=', env('PRESTASHOP_DEFAULT_ID', 105))
            ->get()->all();

        if(count($default_shop_modules) > 0){
            $data = [];
            foreach ($default_shop_modules as $default_shop_module) {
                $data[] = [
                    'id_module' => $default_shop_module->id_module,
                    'id_shop' => $id_shop,
                    'id_hook' => $default_shop_module->id_hook,
                    'position' => $default_shop_module->position,
                ];
            }
            if(count($data) > 0){
                try{
                    DB::connection('we_shop')
                        ->table('ps_hook_module')
                        ->insert($data);
                    return [
                        'status' => true,
                        'message' => 'Hook Module Shop copied successfully.',
                    ];
                }catch (Exception $e){
                    return [
                        'status' => false,
                        'message' => $e->getMessage(),
                    ];
                }
            }
        }else{
            return [
                'status' => false,
                'message' => 'Hook Module Shop not found.',
            ];
        }
    }

    public static function copyAttributeGroupShop($id_shop){
        $default_shop_modules = DB::connection('we_shop')
            ->table('ps_attribute_group_shop')
            ->where('id_shop', '=', env('PRESTASHOP_DEFAULT_ID', 105))
            ->get()->all();

        if(count($default_shop_modules) > 0){
            $data = [];
            foreach ($default_shop_modules as $default_shop_module) {
                $data[] = [
                    'id_attribute_group' => $default_shop_module->id_attribute_group,
                    'id_shop' => $id_shop
                ];
            }
            if(count($data) > 0){
                try{
                    DB::connection('we_shop')
                        ->table('ps_attribute_group_shop')
                        ->insert($data);
                    return [
                        'status' => true,
                        'message' => 'Attribute Group Shop copied successfully.',
                    ];
                }catch (Exception $e){
                    return [
                        'status' => false,
                        'message' => $e->getMessage(),
                    ];
                }
            }
        }else{
            return [
                'status' => false,
                'message' => 'Attribute Group Shop not found.',
            ];
        }
    }

    public static function copyAttributeShop($id_shop){
        $default_shop_modules = DB::connection('we_shop')
            ->table('ps_attribute_shop')
            ->where('id_shop', '=', env('PRESTASHOP_DEFAULT_ID', 105))
            ->get()->all();

        if(count($default_shop_modules) > 0){
            $data = [];
            foreach ($default_shop_modules as $default_shop_module) {
                $data[] = [
                    'id_attribute' => $default_shop_module->id_attribute,
                    'id_shop' => $id_shop
                ];
            }
            if(count($data) > 0){
                try{
                    DB::connection('we_shop')
                        ->table('ps_attribute_shop')
                        ->insert($data);
                    return [
                        'status' => true,
                        'message' => 'Attribute Shop copied successfully.',
                    ];
                }catch (Exception $e){
                    return [
                        'status' => false,
                        'message' => $e->getMessage(),
                    ];
                }
            }
        }else{
            return [
                'status' => false,
                'message' => 'Attribute Shop not found.',
            ];
        }
    }

    public static function copyTaxRuleGroupShop($id_shop){
        $default_shop_data = DB::connection('we_shop')
            ->table('ps_tax_rules_group_shop')
            ->where('id_shop', '=', env('PRESTASHOP_DEFAULT_ID', 105))
            ->get()->all();

        if(count($default_shop_data) > 0){
            $data = [];
            foreach ($default_shop_data as $default_data) {
                $data[] = [
                    'id_tax_rules_group' => $default_data->id_tax_rules_group,
                    'id_shop' => $id_shop
                ];
            }
            if(count($data) > 0){
                try{
                    DB::connection('we_shop')
                        ->table('ps_tax_rules_group_shop')
                        ->insert($data);
                    return [
                        'status' => true,
                        'message' => 'Tax Rules Shop copied successfully.',
                    ];
                }catch (Exception $e){
                    return [
                        'status' => false,
                        'message' => $e->getMessage(),
                    ];
                }
            }
        }else{
            return [
                'status' => false,
                'message' => 'Tax Rules Shop not found.',
            ];
        }
    }

    public static function copyCarrierTaxRuleGroupShop($id_shop){
        $default_shop_data = DB::connection('we_shop')
            ->table('ps_carrier_tax_rules_group_shop')
            ->where('id_shop', '=', env('PRESTASHOP_DEFAULT_ID', 105))
            ->get()->all();

        if(count($default_shop_data) > 0){
            $data = [];
            foreach ($default_shop_data as $default_data) {
                $data[] = [
                    'id_carrier' => $default_data->id_carrier,
                    'id_tax_rules_group' => $default_data->id_tax_rules_group,
                    'id_shop' => $id_shop
                ];
            }
            if(count($data) > 0){
                try{
                    DB::connection('we_shop')
                        ->table('ps_carrier_tax_rules_group_shop')
                        ->insert($data);
                    return [
                        'status' => true,
                        'message' => 'Carrier Tax Rules Shop copied successfully.',
                    ];
                }catch (Exception $e){
                    return [
                        'status' => false,
                        'message' => $e->getMessage(),
                    ];
                }
            }
        }else{
            return [
                'status' => false,
                'message' => 'Carrier Tax Rules Shop not found.',
            ];
        }
    }

    public static function copyCategoryLang($id_shop){
        $default_shop_data = DB::connection('we_shop')
            ->table('ps_category_lang')
            ->where('id_shop', '=', env('PRESTASHOP_DEFAULT_ID', 105))
            //->whereIn('id_category', [1,2])
            ->get()->all();

        if(count($default_shop_data) > 0){
            $data = [];
            foreach ($default_shop_data as $default_data) {
                $data[] = [
                    'id_category' => $default_data->id_category,
                    'id_shop' => $id_shop,
                    'id_lang' => $default_data->id_lang,
                    'name' => $default_data->name,
                    'description' => $default_data->description,
                    'link_rewrite' => $default_data->link_rewrite,
                    'meta_title' => $default_data->meta_title,
                    'meta_keywords' => $default_data->meta_keywords,
                    'meta_description' => $default_data->meta_description,
                ];
            }
            if(count($data) > 0){
                try{
                    DB::connection('we_shop')
                        ->table('ps_category_lang')
                        ->insert($data);
                    return [
                        'status' => true,
                        'message' => 'Category Language copied successfully.',
                    ];
                }catch (Exception $e){
                    return [
                        'status' => false,
                        'message' => $e->getMessage(),
                    ];
                }
            }
        }else{
            return [
                'status' => false,
                'message' => 'Category Language not found.',
            ];
        }
    }

    public static function copyCategoryShop($id_shop){
        $default_shop_data = DB::connection('we_shop')
            ->table('ps_category_shop')
            ->where('id_shop', '=', env('PRESTASHOP_DEFAULT_ID', 105))
            ->get()->all();

        if(count($default_shop_data) > 0){
            $data = [];
            foreach ($default_shop_data as $default_data) {
                $data[] = [
                    'id_category' => $default_data->id_category,
                    'id_shop' => $id_shop,
                    'position' => $default_data->position,
                ];
            }
            if(count($data) > 0){
                try{
                    DB::connection('we_shop')
                        ->table('ps_category_shop')
                        ->insert($data);
                    return [
                        'status' => true,
                        'message' => 'Category Shop copied successfully.',
                    ];
                }catch (Exception $e){
                    return [
                        'status' => false,
                        'message' => $e->getMessage(),
                    ];
                }
            }
        }else{
            return [
                'status' => false,
                'message' => 'Category Shop not found.',
            ];
        }
    }

    public static function copyCmsCategoryShop($id_shop){
        $default_shop_data = DB::connection('we_shop')
            ->table('ps_cms_category_shop')
            ->where('id_shop', '=', env('PRESTASHOP_DEFAULT_ID', 105))
            ->get()->all();

        if(count($default_shop_data) > 0){
            $data = [];
            foreach ($default_shop_data as $default_data) {
                $data[] = [
                    'id_cms_category' => $default_data->id_cms_category,
                    'id_shop' => $id_shop,
                ];
            }
            if(count($data) > 0){
                try{
                    DB::connection('we_shop')
                        ->table('ps_cms_category_shop')
                        ->insert($data);
                    return [
                        'status' => true,
                        'message' => 'CMS Category Shop copied successfully.',
                    ];
                }catch (Exception $e){
                    return [
                        'status' => false,
                        'message' => $e->getMessage(),
                    ];
                }
            }
        }else{
            return [
                'status' => false,
                'message' => 'CMS Category Shop not found.',
            ];
        }
    }

    public static function copyCmsShop($id_shop){
        $default_shop_data = DB::connection('we_shop')
            ->table('ps_cms_shop')
            ->where('id_shop', '=', env('PRESTASHOP_DEFAULT_ID', 105))
            ->get()->all();

        if(count($default_shop_data) > 0){
            $data = [];
            foreach ($default_shop_data as $default_data) {
                $data[] = [
                    'id_cms' => $default_data->id_cms,
                    'id_shop' => $id_shop,
                ];
            }
            if(count($data) > 0){
                try{
                    DB::connection('we_shop')
                        ->table('ps_cms_shop')
                        ->insert($data);
                    return [
                        'status' => true,
                        'message' => 'CMS Shop copied successfully.',
                    ];
                }catch (Exception $e){
                    return [
                        'status' => false,
                        'message' => $e->getMessage(),
                    ];
                }
            }
        }else{
            return [
                'status' => false,
                'message' => 'CMS Shop not found.',
            ];
        }
    }

    public static function copyCmsLangShop($id_shop){
        $default_shop_cms = DB::connection('we_shop')
            ->table('ps_cms_lang')
            ->where('id_shop', '=', env('PRESTASHOP_DEFAULT_ID', 105))
            ->get()->all();

        if(count($default_shop_cms) > 0){
            $data = [];
            foreach ($default_shop_cms as $cms) {
                $data[] = [
                    'id_cms' => $cms->id_cms,
                    'id_lang' => $cms->id_lang,
                    'id_shop' => $id_shop,
                    'meta_title' => $cms->meta_title,
                    'meta_description' => $cms->meta_description,
                    'meta_keywords' => $cms->meta_keywords,
                    'content' => $cms->content,
                    'link_rewrite' => $cms->link_rewrite,
                ];
            }

            if(count($data) > 0){
                try{
                    DB::connection('we_shop')
                        ->table('ps_cms_lang')
                        ->insert($data);
                    return [
                        'status' => true,
                        'message' => 'CMS Language shop copied successfully.',
                    ];
                }catch (Exception $e){
                    return [
                        'status' => false,
                        'message' => $e->getMessage(),
                    ];
                }
            }
        }else{
            return [
                'status' => false,
                'message' => 'CMS Language shop not found.',
            ];
        }
    }

    public static function copyCustomPayment($id_shop){
        try{
            DB::connection('we_shop')
                ->table('ps_custom_payment_method_shop')
                ->insert([
                    'id_custom_payment_method' => 3,
                    'id_shop' => $id_shop,
                ]);
            return [
                'status' => true,
                'message' => 'Custom Payment copied successfully.',
            ];
        }catch (Exception $e){
            return [
                'status' => false,
                'message' => $e->getMessage(),
            ];
        }

    }

    public static function copyCountryShop($id_shop){
        $default_shop_countries = DB::connection('we_shop')
            ->table('ps_country_shop')
            ->where('id_shop', '=', env('PRESTASHOP_DEFAULT_ID', 105))
            ->pluck('id_country')->toArray();

        if(count($default_shop_countries) > 0){
            $data = [];
            foreach ($default_shop_countries as $default_shop_country) {
                $data[] = [
                    'id_country' => $default_shop_country,
                    'id_shop' => $id_shop,
                ];
            }
            if(count($data) > 0){
                try{
                    DB::connection('we_shop')
                        ->table('ps_country_shop')
                        ->insert($data);
                    return [
                        'status' => true,
                        'message' => 'Countries copied successfully.',
                    ];
                }catch (Exception $e){
                    return [
                        'status' => false,
                        'message' => $e->getMessage(),
                    ];
                }
            }
        }else{
            return [
                'status' => false,
                'message' => 'Country shop not found.',
            ];
        }
    }

    public static function copyCurrencyShop($id_shop){
        try {
            DB::connection('we_shop')
                ->table('ps_currency_shop')
                ->insert([
                    'id_currency' => 1,
                    'id_shop' => $id_shop,
                    'conversion_rate' => 1.000000,
                ]);
            return [
                'status' => true,
                'message' => 'Currency copied successfully.',
            ];
        }catch (Exception $e){
            return [
                'status' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public static function copyContactShop($id_shop){
        try {
            DB::connection('we_shop')
                ->table('ps_contact_shop')
                ->insert([
                    [
                        'id_contact' => 1,
                        'id_shop' => $id_shop,
                    ],
                    [
                        'id_contact' => 2,
                        'id_shop' => $id_shop,
                    ]
                ]);
            return [
                'status' => true,
                'message' => 'Contact shop copied successfully.',
            ];
        }catch (Exception $e){
            return [
                'status' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public static function copyEmployeeShop($id_shop){
        $default_shop_employee = DB::connection('we_shop')
            ->table('ps_employee_shop')
            ->where('id_shop', '=', env('PRESTASHOP_DEFAULT_ID', 105))
            ->pluck('id_employee')->toArray();

        if(count($default_shop_employee) > 0){
            $data = [];
            foreach ($default_shop_employee as $default_shop_employ) {
                $data[] = [
                    'id_employee' => $default_shop_employ,
                    'id_shop' => $id_shop,
                ];
            }
            if(count($data) > 0){
                try {
                    DB::connection('we_shop')
                        ->table('ps_employee_shop')
                        ->insert($data);
                    return [
                        'status' => true,
                        'message' => 'Employee copied successfully.',
                    ];
                }catch (Exception $e){
                    return [
                        'status' => false,
                        'message' => $e->getMessage(),
                    ];
                }
            }
        }else{
            return [
                'status' => false,
                'message' => 'Employee shop not found.',
            ];
        }
    }

    public static function copyFeatureShop($id_shop){
        $default_shop_features = DB::connection('we_shop')
            ->table('ps_feature_shop')
            ->where('id_shop', '=', env('PRESTASHOP_DEFAULT_ID', 105))
            ->pluck('id_feature')->toArray();

        if(count($default_shop_features) > 0){
            $data = [];
            foreach ($default_shop_features as $default_shop_feature) {
                $data[] = [
                    'id_feature' => $default_shop_feature,
                    'id_shop' => $id_shop,
                ];
            }
            if(count($data) > 0){
                try{
                    DB::connection('we_shop')
                        ->table('ps_feature_shop')
                        ->insert($data);
                    return [
                        'status' => true,
                        'message' => 'Feature shop copied successfully.',
                    ];
                }catch (Exception $e){
                    return [
                        'status' => false,
                        'message' => $e->getMessage(),
                    ];
                }
            }else{
                return [
                    'status' => false,
                    'message' => 'Feature shop not found.',
                ];
            }
        }
    }

    public static function copyGroupShop($id_shop){
        $default_shop_groups = DB::connection('we_shop')
            ->table('ps_group_shop')
            ->where('id_shop', '=', env('PRESTASHOP_DEFAULT_ID', 105))
            ->pluck('id_group')->toArray();

        if(count($default_shop_groups) > 0){
            $data = [];
            foreach ($default_shop_groups as $default_shop_group) {
                $data[] = [
                    'id_group' => $default_shop_group,
                    'id_shop' => $id_shop,
                ];
            }
            if(count($data) > 0){
                try {
                    DB::connection('we_shop')
                        ->table('ps_group_shop')
                        ->insert($data);
                    return [
                        'status' => true,
                        'message' => 'Group shop copied successfully.',
                    ];
                }catch (Exception $e){
                    return [
                        'status' => false,
                        'message' => $e->getMessage(),
                    ];
                }
            }else{
                return [
                    'status' => false,
                    'message' => 'Group shop not found.',
                ];
            }
        }
    }

    public static function copyLangShop($id_shop){
        $default_shop_langs = DB::connection('we_shop')
            ->table('ps_lang_shop')
            ->where('id_shop', '=', env('PRESTASHOP_DEFAULT_ID', 105))
            ->pluck('id_lang')->toArray();

        if(count($default_shop_langs) > 0){
            $data = [];
            foreach ($default_shop_langs as $default_shop_lang) {
                $data[] = [
                    'id_lang' => $default_shop_lang,
                    'id_shop' => $id_shop,
                ];
            }
            if(count($data) > 0){
                try {
                    DB::connection('we_shop')
                        ->table('ps_lang_shop')
                        ->insert($data);
                    return [
                        'status' => true,
                        'message' => 'Language shop copied successfully.',
                    ];
                }catch (Exception $e){
                    return [
                        'status' => false,
                        'message' => $e->getMessage(),
                    ];
                }
            }
        }else{
            return [
                'status' => false,
                'message' => 'Language shop not found.',
            ];
        }
    }

    public static function copyWebserviceAccountShop($id_shop){
        $default_shop_data = DB::connection('we_shop')
            ->table('ps_webservice_account_shop')
            ->where('id_shop', '=', env('PRESTASHOP_DEFAULT_ID', 105))
            ->get()->all();

        if(count($default_shop_data) > 0){
            $data = [];
            foreach ($default_shop_data as $shop_data) {
                $data[] = [
                    'id_webservice_account' => $shop_data->id_webservice_account,
                    'id_shop' => $id_shop,
                ];
            }
            if(count($data) > 0){
                try {
                    DB::connection('we_shop')
                        ->table('ps_webservice_account_shop')
                        ->insert($data);
                    return [
                        'status' => true,
                        'message' => 'Webservice Account shop copied successfully.',
                    ];
                }catch (Exception $e){
                    return [
                        'status' => false,
                        'message' => $e->getMessage(),
                    ];
                }
            }
        }else{
            return [
                'status' => false,
                'message' => 'Webservice Account shop not found.',
            ];
        }
    }

    public static function copyZoheShop($id_shop){
        $default_shop_data = DB::connection('we_shop')
            ->table('ps_zone_shop')
            ->where('id_shop', '=', env('PRESTASHOP_DEFAULT_ID', 105))
            ->get()->all();

        if(count($default_shop_data) > 0){
            $data = [];
            foreach ($default_shop_data as $shop_data) {
                $data[] = [
                    'id_zone' => $shop_data->id_zone,
                    'id_shop' => $id_shop,
                ];
            }
            if(count($data) > 0){
                try {
                    DB::connection('we_shop')
                        ->table('ps_zone_shop')
                        ->insert($data);
                    return [
                        'status' => true,
                        'message' => 'Zone shop copied successfully.',
                    ];
                }catch (Exception $e){
                    return [
                        'status' => false,
                        'message' => $e->getMessage(),
                    ];
                }
            }
        }else{
            return [
                'status' => false,
                'message' => 'Zone shop not found.',
            ];
        }
    }

    public static function copyCMSPageShop($id_shop){
        $default_shop_data = DB::connection('we_shop')
            ->table('ps_cms_lang')
            ->where('id_shop', '=', env('PRESTASHOP_DEFAULT_ID', 105))
            ->get()->all();

        if(count($default_shop_data) > 0){
            $data_lang = [];
            $data_map = [];
            foreach ($default_shop_data as $shop_data) {
                $data_lang[] = [
                    'id_cms' => $shop_data->id_cms,
                    'id_lang' => $shop_data->id_lang,
                    'id_shop' => $id_shop,
                    'meta_title' => $shop_data->meta_title,
                    'meta_description' => $shop_data->meta_description,
                    'meta_keywords' => $shop_data->meta_keywords,
                    'content' => $shop_data->content,
                    'link_rewrite' => $shop_data->link_rewrite,
                ];

                $data_map[] = [
                    'id_cms' => $shop_data->id_cms,
                    'id_shop' => $id_shop,
                ];
            }
            if(count($data_lang) > 0){
                try {
                    DB::connection('we_shop')
                        ->table('ps_cms_shop')
                        ->insert($data_map);

                    DB::connection('we_shop')
                        ->table('ps_cms_lang')
                        ->insert($data_lang);

                    return [
                        'status' => true,
                        'message' => 'CMS Page shop copied successfully.',
                    ];
                }catch (Exception $e){
                    return [
                        'status' => false,
                        'message' => $e->getMessage(),
                    ];
                }
            }
        }else{
            return [
                'status' => false,
                'message' => 'CMS Page shop not found.',
            ];
        }
    }

    public static function copyMainMenuShop($id_shop, $active_menu){
        if(!is_array($active_menu) || count($active_menu) == 0){
            return ['status' => false, 'message' => 'Please select the menu.'];
        }

        $all_menu = [
            'military' => 'Military',
            'promotions' => 'Promotions',
            'vocational-rehab' => 'Vocational Rehab',
            'workforce' => 'Workforce',
            'mycaa' => 'MyCAA',
        ];

        try {
            foreach ($all_menu as $menu_label) {
                if (in_array($menu_label, $active_menu)) {
                    $menu_id = DB::connection('we_shop')
                        ->table('ps_linksmenutop')
                        ->insertGetId([
                            'id_shop' => $id_shop,
                            'new_window' => 0,
                        ]);

                    if (!empty($menu_id)) {
                        $link = array_keys($all_menu, $menu_label);
                        $page_link = $link ? '/' . current($link) : '';

                        DB::connection('we_shop')
                            ->table('ps_linksmenutop_lang')
                            ->insert([
                                'id_linksmenutop' => $menu_id,
                                'id_lang' => 1,
                                'id_shop' => $id_shop,
                                'label' => $menu_label,
                                'link' => $page_link,
                            ]);
                    }
                }
            }
            return ['status' => true, 'message' => 'Menu successfully updated.'];
        }catch (Exception $e){
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    public static function copyConfigurationShop($id_shop){
        $default_shop_data = DB::connection('we_shop')
            ->table('ps_configuration')
            ->where('id_shop', '=', env('PRESTASHOP_DEFAULT_ID', 105))
            ->whereNotIn('name', ['PS_SHOP_NAME', 'PS_LOGO', 'PS_SHOP_STATE', 'PS_SHOP_STATE_ID', 'PS_SHOP_CITY', 'PS_SHOP_ADDR1', 'PS_SHOP_EMAIL', 'PS_SHOP_PHONE', 'htmlbox_responseiq_widget', 'htmlbox_style', 'htmlbox_style_button'])
            ->get()->all();

        if(count($default_shop_data) > 0){
            $data = [];
            foreach ($default_shop_data as $shop_data) {
                $data[] = [
                    'id_shop_group' => $shop_data->id_shop_group,
                    'id_shop' => $id_shop,
                    'name' => $shop_data->name,
                    'value' => $shop_data->value,
                    'date_add' => date('Y-m-d H:i:s'),
                    'date_upd' => date('Y-m-d H:i:s'),
                ];
            }
            if(count($data) > 0){
                try {
                    DB::connection('we_shop')
                        ->table('ps_configuration')
                        ->insert($data);
                    return [
                        'status' => true,
                        'message' => 'Configuration shop copied successfully.',
                    ];
                }catch (Exception $e){
                    return [
                        'status' => false,
                        'message' => $e->getMessage(),
                    ];
                }
            }
        }else{
            return [
                'status' => false,
                'message' => 'Configuration shop not found.',
            ];
        }
    }

    public static function uploadLogo($id_shop, $id_shop_group, $request){
        $logo_ext  = $request->logo->getClientOriginalExtension();
        $new_logo_name = \Illuminate\Support\Str::slug($request->shop['name']).'-'.time();

        $logo_file = new \CURLFile($_FILES['logo']['tmp_name'], $_FILES['logo']['type'], $new_logo_name.'.'.$logo_ext);

        $post_data = [
            'logo' => $logo_file,
            'id_shop' => $id_shop,
            'id_shop_group' => $id_shop_group,
        ];

        // curl connection
        $ch = curl_init();
        // set curl url connection
        $curl_url = $_ENV['PRESTASHOP_BASE_URL'].'/modules/pasapi/upload-logo.php';
        // pass curl url
        curl_setopt($ch, CURLOPT_URL,$curl_url);
        curl_setopt($ch, CURLOPT_POST, 1);
        // image upload Post Fields
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        // set CURL RETURN TRANSFER type
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_result = curl_exec($ch);
        curl_close($ch);
        echo $server_result;
    }

    public static function deleteShopData($id_shop){
        DB::connection('we_shop')->beginTransaction();
        try{
            DB::connection('we_shop')->table('ps_module_shop')
                ->where('id_shop', '=', $id_shop)
                ->delete();

            DB::connection('we_shop')->table('ps_module_group')
                ->where('id_shop', '=', $id_shop)
                ->delete();

            DB::connection('we_shop')->table('ps_hook_module')
                ->where('id_shop', '=', $id_shop)
                ->delete();

            DB::connection('we_shop')->table('ps_attribute_group_shop')
                ->where('id_shop', '=', $id_shop)
                ->delete();

            DB::connection('we_shop')->table('ps_attribute_shop')
                ->where('id_shop', '=', $id_shop)
                ->delete();

            DB::connection('we_shop')->table('ps_tax_rules_group_shop')
                ->where('id_shop', '=', $id_shop)
                ->delete();

            DB::connection('we_shop')->table('ps_carrier_tax_rules_group_shop')
                ->where('id_shop', '=', $id_shop)
                ->delete();

            DB::connection('we_shop')->table('ps_category')
                ->where('id_shop_default', '=', $id_shop)
                ->delete();

            DB::connection('we_shop')->table('ps_category_lang')
                ->where('id_shop', '=', $id_shop)
                ->delete();

            DB::connection('we_shop')->table('ps_category_shop')
                ->where('id_shop', '=', $id_shop)
                ->delete();

            DB::connection('we_shop')->table('ps_cms_category_shop')
                ->where('id_shop', '=', $id_shop)
                ->delete();

            DB::connection('we_shop')->table('ps_cms_shop')
                ->where('id_shop', '=', $id_shop)
                ->delete();

            DB::connection('we_shop')->table('ps_cms_lang')
                ->where('id_shop', '=', $id_shop)
                ->delete();

            DB::connection('we_shop')->table('ps_custom_payment_method_shop')
                ->where('id_shop', '=', $id_shop)
                ->delete();

            DB::connection('we_shop')->table('ps_country_shop')
                ->where('id_shop', '=', $id_shop)
                ->delete();

            DB::connection('we_shop')->table('ps_currency_shop')
                ->where('id_shop', '=', $id_shop)
                ->delete();

            DB::connection('we_shop')->table('ps_contact_shop')
                ->where('id_shop', '=', $id_shop)
                ->delete();

            DB::connection('we_shop')->table('ps_employee_shop')
                ->where('id_shop', '=', $id_shop)
                ->delete();

            DB::connection('we_shop')->table('ps_feature_shop')
                ->where('id_shop', '=', $id_shop)
                ->delete();

            DB::connection('we_shop')->table('ps_group_shop')
                ->where('id_shop', '=', $id_shop)
                ->delete();

            DB::connection('we_shop')->table('ps_lang_shop')
                ->where('id_shop', '=', $id_shop)
                ->delete();

            DB::connection('we_shop')->table('ps_webservice_account_shop')
                ->where('id_shop', '=', $id_shop)
                ->delete();

            DB::connection('we_shop')->table('ps_zone_shop')
                ->where('id_shop', '=', $id_shop)
                ->delete();

            DB::connection('we_shop')->table('ps_configuration')
                ->where('id_shop', '=', $id_shop)
                ->delete();

            DB::connection('we_shop')->table('ps_product')
                ->where('id_shop_default', '=', $id_shop)
                ->delete();

            DB::connection('we_shop')->table('ps_linksmenutop_lang')
                ->where('id_shop', '=', $id_shop)
                ->delete();

            DB::connection('we_shop')->table('ps_linksmenutop')
                ->where('id_shop', '=', $id_shop)
                ->delete();


            $products_ids = DB::connection('we_shop')->table('ps_product_lang')
                ->where('id_shop', '=', $id_shop)->pluck('id_product')->toArray();

            DB::connection('we_shop')->table('ps_category_product')
                ->whereIn('id_product', $products_ids)
                ->delete();

            DB::connection('we_shop')->table('ps_product_lang')
                ->where('id_shop', '=', $id_shop)
                ->delete();

            DB::connection('we_shop')->table('ps_product_shop')
                ->where('id_shop', '=', $id_shop)
                ->delete();


            DB::connection('we_shop')->table('ps_shop_url')
                ->where('id_shop', '=', $id_shop)
                ->delete();

            DB::connection('we_shop')->table('ps_shop')
                ->where('id_shop', '=', $id_shop)
                ->delete();

            DB::connection('we_shop')->commit();

            return [
                'status' => true,
                'message' => 'Shop deleted successfully'
            ];
        }catch (Exception $e){
            DB::connection('we_shop')->rollBack();
            return [
                'status' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}
