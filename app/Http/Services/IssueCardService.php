<?php

namespace App\Http\Services;

use App\Consts;
use App\Models\IssueCard;
use App\Models\TorqueWrenchArea;
use App\Models\Vehicle;
use App\Models\VehicleType;
use App\Utils;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class IssueCardService extends BaseService
{
    public function getIssueItemData($params)
    {
        $userId = Arr::get($params, 'user_id', Auth::id());
        $issueCards = IssueCard::join('bins', 'bins.id', 'issue_cards.bin_id')
            ->join('bin_configures', 'bin_configures.bin_id', 'bins.id')
            ->join('shelfs', 'shelfs.id', 'bins.shelf_id')
            ->join('spares', 'spares.id', 'bins.spare_id')
            ->leftJoin('torque_wrench_areas', 'torque_wrench_areas.id', 'issue_cards.torque_wrench_area_id')
            ->where('issue_cards.taker_id', $userId)
            ->select(
                'spares.name as item_name',
                'spares.material_no as mpn',
                'spares.part_no as ssh',
                'spares.type as item_type',
                'bins.quantity_oh as qyt_oh',
                'bin_configures.serial_no as serial',
                'bin_configures.calibration_due as calibration_due',
                'bin_configures.charge_time as min_charge_time',
                'bin_configures.expiry_date as expire_date',
                'bins.max as max_count',
                'bins.bin as bin_name',
                'bins.drawer_name',
                'spares.url as image',
                'issue_cards.torque_wrench_area_id',
                'torque_wrench_areas.area as torque_area',
                'torque_wrench_areas.torque_value',
                DB::raw('(CASE WHEN bins.status = "' . Consts::BIN_STATUS_ASSIGNED . '" THEN 1 ELSE 0 END) AS available_state'),
                DB::raw('CONCAT(shelfs.name," - ",bins.row, " - ",bins.bin) as item_position'),
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

        $issueCards->getCollection()->transform(function ($record) {
            return $this->transformIssueItemData($record);
        });

        return $issueCards;
    }

    private function transformIssueItemData($record)
    {
        $typeMap = [
            Consts::SPARE_TYPE_EUC        => 1,
            Consts::SPARE_TYPE_DURABLE    => 2,
            Consts::SPARE_TYPE_PERISHABLE => 3,
            Consts::SPARE_TYPE_CONSUMABLE => 4,
        ];

        $data = $record->toArray();
        $data['item_type'] = Arr::get($typeMap, $data['item_type'], null);

        $area_id = Arr::pull($data, 'torque_wrench_area_id', null);
        $data['item_tool_type'] = is_null($area_id) ? 0 : 1;

        $torque_area = Arr::pull($data, 'torque_area', null);
        $torque_value = Arr::pull($data, 'torque_value', null);
        if ($torque_area) {
            $veh_p_area = new \stdClass();
            $veh_p_area->veh_p_area_name = $torque_area;
            $veh_p_area->torq = [$torque_value];

            $data['veh_p_area'] = [$veh_p_area];
        }

        return (object)$data;
    }
}
