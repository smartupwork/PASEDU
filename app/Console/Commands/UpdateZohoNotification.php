<?php

namespace App\Console\Commands;

use App\ZohoHelper;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UpdateZohoNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'updateZohoNotification:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update ZOHO notification webhook.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $modules = [
            //'Accounts.all', // Partners
            //'Deals.all',
            //'Price_Books.all',
            //'Products.all', // Programs
            //'Sales_Orders.all', // Enrollments
        ];

        $is_exists = ZohoHelper::getInstance()->notificationDetail('1000068001');
        if($is_exists){
            $channel_expiry = Carbon::now()->addHours(24)->format('c');
            $response = ZohoHelper::getInstance()->notificationDetailUpdate(  $modules, '1000068001', $channel_expiry);
            $this->info('Channel Updated.');
        }else{
            $response = ZohoHelper::getInstance()->notificationEnable(  $modules, '1000068001');
            $this->info('Channel Created/Enabled.');
        }
        dump($response);
        $this->info('Notification updated.');
    }
}
