<?php

namespace Database\Factories;

use App\Models\User;
use App\Utility;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_type' => User::USER_TYPE_ADMIN,
            'email' => $this->faker->unique()->safeEmail,
            'firstname' => $this->faker->firstName,
            'lastname' => $this->faker->lastName,
            'phone' => $this->faker->phoneNumber,
            'roleid' => 1,
            'status' => Utility::STATUS_ACTIVE,
            'partner_type' => null,
            'first_login' => 1,
            'password' => md5('Info@12345'),
        ];
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
