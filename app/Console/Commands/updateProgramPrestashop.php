<?php

namespace App\Console\Commands;

use App\Models\Program;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Prestashop;

class UpdateProgramPrestashop extends Command
{
    const OFF_SET = 0;
    const LIMIT = 200;

    private $off_set = self::OFF_SET;
    private $limit = self::LIMIT;
    private $page = 1;

    private $data = [
        'insert' => [],
        'update' => [],
    ];

    private $existing_programs = [];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'updateProgramPrestashop:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync prestashop from PAS server.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $shops = DB::connection('we_shop')
            ->table('ps_shop')
            ->where('active', '=', 1)->get()->all();

        //website_short_description, description, tag_line, prerequisites, outline, audience, certification
        $pas_programs = Program::with(['priceBookProgram'])
            ->where('status', '=', 'Active')
            ->where('displayed_on', '=', 'All')
            ->where('is_copy', '=', 1)
            ->limit(5)
            ->get()->toArray();
        //dump($pas_programs);die;
        if(count($pas_programs) > 0){
            foreach ($pas_programs as $pas_program) {
                //dump($pas_program['zoho_id']);
                (new Program())->savePrestaShopProduct($pas_program, $shops);
                DB::table('pas_program')->where('zoho_id', '=', $pas_program['zoho_id'])->update(['is_copy' => 2]);
            }

        }

        $client = new Client();
        $response = $client->get($_ENV['PRESTASHOP_BASE_URL'].'/modules/pasapi/cache-clear.php', [
            'headers' => [
                //'Authorization' => 'Bearer '. $this->access_token,
                'Accept'        => 'application/json',
            ],
            'query_params' => [],
        ]);
        $response->getBody();

        $this->info('Total records Inserted('.count($this->data['insert']).') and Updated('.count($this->data['update']).').');
    }

}
