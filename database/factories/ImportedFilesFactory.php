<?php

namespace Database\Factories;

use App\Models\ImportedFiles;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Auth;


class ImportedFilesFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ImportedFiles::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'file' => Factory::create()->firstName,
            'date' => date('Y-m-d'),
            'partner_id' => User::getPartnerDetail('id'),
            'added_by' => Auth::user()->id,
            'records_imported' => 0,
            'records_skiped' => 0,
            'added_date' => date('Y-m-d H:i:s'),
        ];
    }
}
