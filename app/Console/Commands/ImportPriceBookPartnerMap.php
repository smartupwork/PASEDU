<?php

namespace App\Console\Commands;

use App\Models\Partner;
use App\Models\PriceBook;
use App\ZohoHelper;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportPriceBookPartnerMap extends Command
{

    const OFF_SET = 0;
    const LIMIT = 200;

    private $off_set = self::OFF_SET;
    private $limit = self::LIMIT;
    private $page = 1;

    private $total = 1;
    private $our_db_price_books = [];
    private $our_db_price_books_arr = [];

    private $data = [
        'insert' => [],
        'update' => [],
    ];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'importPriceBookPartnerMap:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Price Book Program Map from ZOHO server.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle() {
        $this->our_db_price_books = PriceBook::all()->toArray();
        $this->our_db_price_books_arr = array_column($this->our_db_price_books, 'id', 'zoho_id');

        $this->total = DB::table('pas_partner')
            ->where('partner_type', '=', 'Active')
            ->count('id');

        $this->getPriceBookPartners();
        $this->info('Total records Inserted('.count($this->data['insert']).') and Updated('.count($this->data['update']).').');
    }

    private function getPriceBookPartners(){
        //dump([$this->total, $this->off_set, $this->limit]);
        $our_db_partners = DB::table('pas_partner')
            ->where('partner_type', '=', 'Active')
            ->whereNotNull('zoho_id')
            ->offset($this->off_set)
            ->limit($this->limit)
            ->get()
            ->toArray();
        //dd($our_db_partners);

        if(count($our_db_partners) > 0){
            foreach ($our_db_partners as $partner) {
                /*$date = \DateTime::createFromFormat("H:i:s", "07:00:00");
                $last_modified = $date->modify("-1 day")->format("c");*/
                $before_70_min = Carbon::now('GMT-5')->subMinutes(70)->format('c');

                $price_books = ZohoHelper::getInstance()->fetchRelatedRecords('Accounts/'.$partner->zoho_id, 'Price_Books8', 1,200, $before_70_min);
                //dd($price_books);

                if($price_books['status'] == 'success' && isset($price_books['data']['data']) && count($price_books['data']['data']) > 0 && isset($price_books['data']['data'][0]['PriceBooks']['id'])){
                    $price_book_zoho_id = $price_books['data']['data'][0]['PriceBooks']['id'];
                    if(isset($this->our_db_price_books_arr[$price_book_zoho_id])){
                        $this->data['update'][] = [
                            'id' => $partner->id,
                            'price_book_zoho_id' => $price_book_zoho_id,
                            'price_book_id' => $this->our_db_price_books_arr[$price_book_zoho_id],
                        ];
                    }
                }else{
                    $zoho_partners = ZohoHelper::getInstance()->fetchByIds('Accounts', [$partner->zoho_id], ['Account_Name', 'Price_Book']);
                    if (isset($zoho_partners['data']) && count($zoho_partners['data']) > 0) {
                        foreach ($zoho_partners['data'] as $zoho_partner) {
                            if($zoho_partner['Price_Book'] && isset($zoho_partner['Price_Book']['id'])){
                                $price_book_zoho_id = $zoho_partner['Price_Book']['id'];
                                $this->data['update'][] = [
                                    'id' => $partner->id,
                                    'price_book_zoho_id' => $price_book_zoho_id,
                                    'price_book_id' => $this->our_db_price_books_arr[$price_book_zoho_id],
                                ];
                            }else{
                                $this->data['update'][] = [
                                    'id' => $partner->id,
                                    'price_book_zoho_id' => null,
                                    'price_book_id' => null,
                                ];
                            }
                        }
                    }
                }
            }

            if($this->total > ($this->page * $this->limit)){
                $this->off_set = $this->off_set == 0 ? $this->limit : $this->off_set + $this->limit;
                $this->page += 1;
                //dump([$this->total, $this->off_set, $this->limit]);
                $this->getPriceBookPartners();
            }else{
                //dd($this->data);
                foreach ($this->data['update'] as $key => $price_book) {
                    //dump($price_book);
                    $partner_id = $price_book['id'];
                    unset($price_book['id']);
                    Partner::where('id', '=', $partner_id)->update($price_book);
                }
            }
        }else {
            $this->info('Data not found');
        }


    }

}
