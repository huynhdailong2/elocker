<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Validator;
use App;
use App\Models\Spare;
use App\Models\PolManagement;
use App\User;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);

        Validator::extend('unique_part_no', function($attribute, $value, $parameters, $validator) {
            $spareId = collect($parameters)->first();
            $spare = Spare::find($spareId);

            if ($spare) {
                return Spare::whereIn('part_no', [$value, $spare->part_no])->get()->count() <= 1;
            }

            return !Spare::where('part_no', $value)->exists();
        });

        Validator::extend('unique_material_no', function($attribute, $value, $parameters, $validator) {
            $spareId = collect($parameters)->first();
            $spare = Spare::find($spareId);

            if ($spare) {
                return Spare::whereIn('material_no', [$value, $spare->material_no])->get()->count() <= 1;
            }

            return !Spare::where('material_no', $value)
                ->exists();
        });

        Validator::extend('unique_pol_material_no', function($attribute, $value, $parameters, $validator) {
            $polId = collect($parameters)->first();
            $pol = $polId ? PolManagement::find($polId) : null;

            if ($pol) {
                return PolManagement::whereIn('material_number', [$value, $pol->material_number])
                    ->get()
                    ->count() <= 1;
            }

            return !PolManagement::where('material_number', $value)
                ->exists();
        });

        Validator::extend('unique_login_id', function($attribute, $value, $parameters, $validator) {
            $isExisted = User::where('login_name', $value)
                ->where('id', '<>', $parameters)
                ->exists();
            return !$isExisted;
        });

        Validator::extend('unique_card_id', function($attribute, $value, $parameters, $validator) {
            $isExisted = User::where('card_id', $value)
                ->where('id', '<>', $parameters)
                ->exists();
            return !$isExisted;
        });

        Validator::extend('gte_field', function($attribute, $value, $parameters, $validator) {
            $minField   = collect($parameters)->first();
            $data       = $validator->getData();
            $minValue   = $data[$minField];
            return $value >= $minValue;
        }); 

        View::composer('*', function ($view) {
             $dataVersion = 0;
             $view->with('dataVersion', $dataVersion)->with('userLocale', App::getLocale());
         });

        DB::enableQueryLog();
        DB::listen(function ($query) {
            $ignoreKeys = [
                'insert into `jobs`',
                'select * from `jobs`',
            ];
            foreach ($ignoreKeys as $key) {
               if (substr($query->sql, 0, strlen($key)) === $key) {
                   return;
               }
            }

            Log::debug('SQL', [
                'sql' => $query->sql,
                'bindings' => $query->bindings,
                'runtime' => $query->time
            ]);
        });
    }
}
