<?php

namespace Database\Factories;

use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StudentFactory extends Factory
{
    const STATUS_ACTIVE     = 1;
    const STATUS_COMPLETE   = 2;
    const STATUS_REFUND     = 3;
    const STATUS_EXPIRED    = 4;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Student::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $state = DB::table('pas_state')->get()->first();
        return [
            'partner_id' => User::getPartnerDetail('id'),
            'zoho_id' => null,
            'first_name' => DB::raw('AES_ENCRYPT("'.$this->faker->firstName.'", "'.$_ENV['AES_ENCRYPT_KEY'].'")'),
            'last_name' => DB::raw('AES_ENCRYPT("'.$this->faker->lastName.'", "'.$_ENV['AES_ENCRYPT_KEY'].'")'),
            'email' => DB::raw('AES_ENCRYPT("'.$this->faker->unique()->safeEmail.'", "'.$_ENV['AES_ENCRYPT_KEY'].'")'),
            'phone' => DB::raw('AES_ENCRYPT("'.$this->faker->unique()->phoneNumber.'", "'.$_ENV['AES_ENCRYPT_KEY'].'")'),
            'program_id' => DB::table('pas_program')->inRandomOrder()->value('id'),
            'start_date' => Carbon::now()->addDays(5)->format('m/d/Y'),
            'end_date' => Carbon::now()->addDays(10)->format('m/d/Y'),
            'status' => self::getStatus(rand(1, 4)),
            'payment_type' => Student::PT_MyCAA,
            'created_by' => Auth::user()->id,
            'street' => null,
            'city' => null,
            'state' => $state->id,
            'country' => $state->country_id,
            'zip' => null,
        ];
    }

    /**
     * @return array
     */
    public static function getStatus($status_id = null, $flip = false, $default = null){
        $status = [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_COMPLETE => 'Complete',
            self::STATUS_REFUND => 'Refund',
            self::STATUS_EXPIRED => 'Expired',
        ];

        if($flip){
            $status = array_flip($status);
        }
        if(!empty($status_id)){
            return isset($status[$status_id]) ? $status[$status_id]: $default;
        }
        return $status;
    }


    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function unverified()
    {
        return $this->state(function (array $attributes) {
            return [
                'email_verified_at' => null,
            ];
        });
    }
}
