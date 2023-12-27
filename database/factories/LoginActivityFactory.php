<?php

namespace Database\Factories;

use App\Models\LoginActivity;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class LoginActivityFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = LoginActivity::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => Auth::user()->id,
            'logged_in_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'last_activity_time' => Carbon::now()->format('Y-m-d H:i:s'),
            'ip_address' => '127.0.0.1',
            'session_id' => Session::getId(),
            'user_agent' => 'Console'
        ];
    }
}
