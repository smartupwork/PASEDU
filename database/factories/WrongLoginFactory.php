<?php

namespace Database\Factories;

use App\Models\WrongLogin;
use App\Utility;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Auth;

class WrongLoginFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = WrongLogin::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => Auth::user()->id,
            'attempt_time' => Carbon::now()->format('Y-m-d H:i:s'),
            'ip_address' => Utility::getClientIp(),
        ];
    }
}
