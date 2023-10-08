<?php

use Illuminate\Database\Seeder;
use App\Models\Setting;
use App\Consts;

class SettingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('settings')->truncate();

        foreach ($this->getData() as $key => $value) {
            Setting::create($value);
        }
    }

    private function getData()
    {
        return [
            ['key' => Consts::SITE_NAME_KEY, 'value' => 'Drk Inventory System']
        ];
    }
}
