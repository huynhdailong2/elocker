<?php

use Illuminate\Database\Seeder;
use App\Models\TorqueWrenchArea;
use Illuminate\Support\Str;

class TorqueAreaTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('torque_wrench_areas')->truncate();

        foreach ($this->getData() as $key => $value) {
            TorqueWrenchArea::create(array_merge($value, [
                'code' => Str::slug($value['area'])
            ]));
        }
    }

    private function getData()
    {
        return [
            ['area' => 'Wheel Nuts', 'torque_value' => 500],
            ['area' => 'Bonnet Bolts', 'torque_value' => 90],
            ['area' => 'Fire Extinguisher Screws', 'torque_value' => 20],
            ['area' => 'Fire Hose Connector', 'torque_value' => 100],
            ['area' => 'Fuel Filter Cover', 'torque_value' => 40],
            ['area' => 'Oil Filter Cover', 'torque_value' => 40],
            ['area' => 'Earthwire Mounting Nut', 'torque_value' => 25],
            ['area' => 'Alternator Cable Nut', 'torque_value' => 25],
            ['area' => 'Alternator Lower Bolt', 'torque_value' => 85],
            ['area' => 'Alternator Bolt Nut', 'torque_value' => 60],
            ['area' => 'Alternator Upper Screw', 'torque_value' => 60],
            ['area' => 'Engine Oil Drain Plug', 'torque_value' => 55],
            ['area' => 'Drive Axle Plugs', 'torque_value' => 30],
            ['area' => 'Fuel Filter Protective Housing', 'torque_value' => 40],
            ['area' => 'Transfer Box Plugs', 'torque_value' => 60],
            ['area' => 'Reduction Unit (HUB) Plugs', 'torque_value' => 27],
            ['area' => 'Reduction Unit (HUB) Level Plugs', 'torque_value' => 12],
            ['area' => 'Gearbox Drain Plug', 'torque_value' => 50],
            ['area' => 'Gearbox Filter Housing Bolts', 'torque_value' => 25],
            ['area' => 'Gearbox Access Hatch Mounting Screws', 'torque_value' => 23],
        ];
    }
}
