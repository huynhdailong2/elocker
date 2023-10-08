<?php

namespace App\Http\Services;

use App\Consts;
use App\Models\JobCard;
use App\Models\Vehicle;
use App\Models\VehicleType;
use App\Utils;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class JobCardService extends BaseService
{
    public function searchJobCard($params)
    {
        return JobCard::join('vehicles', 'vehicles.id', 'job_cards.vehicle_id')
            ->join('vehicle_types', 'vehicle_types.id', 'vehicles.vehicle_type_id')
            ->when(!empty($params['job_card_number']), function ($query) use ($params) {
                $searchKey = Utils::escapeLike($params['job_card_number']);
                $query->where(function ($subQuery) use ($searchKey) {
                    $subQuery->where('job_cards.card_num', 'LIKE', "%{$searchKey}%");
                });
            })
            ->orderBy('job_cards.updated_at', 'DESC')
            ->select(
                'job_cards.card_num as job_card_number',
                'job_cards.wo',
                'job_cards.platform',
                'vehicles.vehicle_num as veh',
                'vehicle_types.name as veh_type'
            )
            ->when(
                !empty($params['no_pagination']),
                function ($query) {
                    return $query->get();
                },
                function ($query) use ($params) {
                    return $query->paginate(Arr::get($params, 'limit', Consts::DEFAULT_PER_PAGE));
                }
            );
    }

    public function addRoomJobCard($params)
    {
        return DB::transaction(function () use ($params) {
            $vehicleType = VehicleType::where('name', '=', $params['veh_type'])->firstOrFail();
            $vehicle = Vehicle::where('vehicle_num', '=', $params['veh'])
                ->where('vehicle_type_id', '=', $vehicleType->id)
                ->firstOrFail();


            $jobCard = new JobCard;
            $params['card_num'] = $params['job_card_number'];
            $params['vehicle_id'] = $vehicle->id;
            $jobCard = $this->saveData($jobCard, $params);
            return $jobCard;
        });
    }
}
