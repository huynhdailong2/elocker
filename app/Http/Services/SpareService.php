<?php

namespace App\Http\Services;

use App\Consts;
use App\Exports\CycleCountExport;
use App\Exports\InventoryCountExport;
use App\Exports\SpareExpiringExport;
use App\Exports\SparesByExpiredExport;
use App\Exports\SparesByLoanExport;
use App\Exports\SparesByReturnsExport;
use App\Exports\SparesByTnxExport;
use App\Exports\SparesByWoExport;
use App\Exports\SparesTorqueWrenchExport;
use App\Exports\SparesWriteOffExport;
use App\Exports\YetToReturSparesExport;
use App\Jobs\ReturningSpareJob;
use App\Mails\CycleCountReport;
use App\Mails\InventoryCountReport;
use App\Mails\SpareExpiringReport;
use App\Mails\SparesByExpiredReport;
use App\Mails\SparesByLoanReport;
use App\Mails\SparesByReturnsReport;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Mails\SparesByTnxReport;
use App\Mails\SparesByWoReport;
use App\Mails\SparesTorqueWrenchReport;
use App\Mails\SparesWriteOffReport;
use App\Mails\YetToReturnSparesReport;
use App\Models\Bin;
use App\Models\TakingTransactionDetail;
use App\Models\BinConfigure;
use App\Models\BinSpare;
use App\Models\Cluster;
use App\Models\CycleCount;
use App\Models\CycleCountSpare;
use App\Models\EucBoxSpare;
use App\Models\IssueCard;
use App\Models\JobCard;
use App\Models\TransactionSpare;
use App\Models\ReplenishEucBox;
use App\Models\Replenishment;
use App\Models\ReplenishmentSpare;
use App\Models\ReplenishmentSpareConfigure;
use App\Models\ReturnSpare;
use App\Models\Shelf;
use App\Models\Spare;
use App\Models\TakingTransaction;
use App\Models\TorqueWrenchArea;
use App\Models\TrackingMo;
use App\Models\Vehicle;
use App\Models\WeighingHistory;
use App\Models\WriteOff;
use App\User;
use App\Utils;
use App\Utils\BigNumber;
use App\Utils\SettingUtils;
use Auth;
use Carbon\Carbon;
use DB;
use Excel;
use Exception;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Mail;
use Storage;

class SpareService extends BaseService
{
    private $settingService;
    private $adminService;

    public function __construct()
    {
        $this->settingService = new SettingService;
        $this->adminService = new AdminService;
    }

    public function issueCard($params)
    {
        $spares = array_get($params, 'spares', []);
        $response = [];
        foreach ($spares as $value) {
            // $trackingMo = new TrackingMo;
            // $issueCard = new IssueCard;
            // $value['issuer_id'] = Auth::id();
            $spare = Spare::find($value['spares']['id']);

            $type = ($spare->type == Consts::SPARE_TYPE_EUC) ? Consts::SPARE_TYPE_EUC : '';

            // switch ($type) {
            switch ($type) {
                case Consts::SPARE_TYPE_EUC:
                    $issueCard = IssueCard::create([
                        'job_card_id' => $value['job_card_id'],
                        'bin_id' => null,
                        'spare_id' => $value['spares']['id'],
                        'quantity' => $value['spares']['pivot']['quantity'],
                        'torque_wrench_area_id' => isset($value['torque_wrench_area_id']) ? $value['torque_wrench_area_id'] : null,
                        'issuer_id' => Auth::id(),
                        'taker_id' => $value['taker_id'],
                    ]);
                    $trackingMo = TrackingMo::create(
                        [
                            'job_card_id' => $value['job_card_id'],
                            'issue_card_id' => $issueCard->id,
                            'bin_id' => null,
                            'spare_id' => $value['spares']['id'],
                            'quantity' => $value['spares']['pivot']['quantity'],
                            'issuer_id' => Auth::id(),
                            'torque_wrench_area_id' => isset($value['torque_wrench_area_id']) ? $value['torque_wrench_area_id'] : null,
                            'taker_id' => $value['taker_id'],
                        ]
                    );
                    break;
                default:
                    //lock bin
                    $this->adminService->checkProcessingBinSpare(['user_id' => Auth::id(), 'bin_id' => $value['configures'][0]['bin_id'], 'spare_id' => $value['spares']['id'], 'value' => 1]);
                    $issueCard = IssueCard::create([
                        'job_card_id' => $value['job_card_id'],
                        'bin_id' => $value['configures'][0]['bin_id'],
                        'spare_id' => $value['spares']['id'],
                        'quantity' => $value['spares']['pivot']['quantity'],
                        'torque_wrench_area_id' => isset($value['torque_wrench_area_id']) ? $value['torque_wrench_area_id'] : null,
                        'issuer_id' => Auth::id(),
                        'taker_id' => $value['taker_id'],
                        'euc_box_id' => null,
                    ]);
                    $trackingMo = TrackingMo::create(
                        [
                            'job_card_id' => $value['job_card_id'],
                            'issue_card_id' => $issueCard->id,
                            'bin_id' => $value['configures'][0]['bin_id'],
                            'spare_id' => $value['spares']['id'],
                            'quantity' => $value['spares']['pivot']['quantity'],
                            'torque_wrench_area_id' => isset($value['torque_wrench_area_id']) ? $value['torque_wrench_area_id'] : null,
                            'issuer_id' => Auth::id(),
                            'taker_id' => $value['taker_id'],
                            'euc_box_id' => null,
                        ]
                    );
                    // $this->saveData($issueCard, $value);
                    // $value['issue_card_id'] = $issueCard->id;
                    // $this->saveData($trackingMo, $value);
                    $this->updateQuantityInBinSpare($issueCard->bin_id, $issueCard->spare_id, -$issueCard->quantity);
                    $transaction_last = TakingTransaction::orderBy('created_at', 'desc')->limit(1)->first();
                    $binGet = Bin::find($value['configures'][0]['bin_id']);
                    $cluster = Cluster::find($binGet->cluster_id);
                    $shelf = Shelf::find($binGet->shelf_id);
                    // $transaction_last = $transaction_last->toArray();
                    $bub_num = 0;
                    if (!empty($transaction_last)) {
                        $bub_num = $transaction_last->id + 1;
                    } else {
                        $bub_num = 1;
                    }
                    $job_carddd = JobCard::find($value['job_card_id']);
                    $data_transaction = [
                        'user_id' => 1,
                        'type' => 'issue',
                        'satus' => 'done',
                        'job_card_id' => $value['job_card_id'],
                        'name' => 'trans#' . $bub_num,
                        'cabinet_id' => $binGet->shelf_id,
                        'bin_id' => $value['configures'][0]['bin_id'],
                        'request_qty' => $value['spares']['pivot']['quantity'],
                        'cluster_name' => $cluster->name,
                        'cabinet_name' => $shelf->name,
                        'bin_name' => $binGet->bin,
                        'total_qty' => 0,
                        'remain_qty' => 0,
                        'hardware_port' => 0,
                        'port_id' => 0,
                        'qty' => 0,
                        'pre_qty' => 0,
                        'changed_qty' => 0,
                        'part_number' => 0,
                    ];
                    $transaction_new = TakingTransaction::create($data_transaction);
                    $taking_transaction_detail = [
                        'taking_transaction_id' => $transaction_new->id,
                        'request_qty' => $value['spares']['pivot']['quantity'],
                        'spare_id' => $value['spares']['id'],
                        'platform' => $job_carddd->platform,
                        'job_name' =>  $job_carddd->wo,
                        'job_card_id' =>  $value['job_card_id'],
                        'listWO' => 'null',
                    ];
                    TransactionSpare::create($taking_transaction_detail);
                    break;
            }
            $response[] = (object)[
                'bin_id' => Arr::get($value, 'id'),
                'spare_id' => $value['spares']['id']
            ];
        }
        return $response;
    }

    public function createLinkMO($params)
    {
        $params = array_merge($params, [
            'issuer_id' => auth()->id(),
            'returned' => Consts::RETURNED_TYPE_LINK_MO,
            'returned_quantity' => Arr::get($params, 'quantity', 0),
        ]);

        // Save issue card
        $issueCard = new IssueCard();
        $issueCard = $this->saveData($issueCard, $params);

        // Save tracking mo
        $trackingMo = new TrackingMo();
        $params['issue_card_id'] = $issueCard->id;
        $this->saveData($trackingMo, $params);

        return $issueCard;
    }

    public function updateLinkMO($params)
    {
        $issueCard = IssueCard::where('id', $params['id'])
            ->where('returned', Consts::RETURNED_TYPE_LINK_MO)
            ->first();

        if ($issueCard) {
            // Update issue card
            $this->saveData($issueCard, [
                'torque_wrench_area_id' => $params['torque_wrench_area_id']
            ]);

            // Update tracking mo
            TrackingMo::where('issue_card_id', $params['id'])
                ->update(
                    [
                        'torque_wrench_area_id' => $params['torque_wrench_area_id']
                    ]
                );
        }

        return $issueCard;
    }

    public function deleteLinkMO($params)
    {
        //        $issueCard = IssueCard::where('id', $params['id'])
        //            ->where('returned', Consts::RETURNED_TYPE_LINK_MO)
        //            ->first();
        //
        //        if ($issueCard) {
        //            // Delete issue card
        //            $issueCard->delete();
        //
        //            // Delete tracking mo
        //            TrackingMo::where('issue_card_id', $params['id'])
        //                ->delete();
        //        }
        //
        //        return $issueCard;
        $trackingMo = TrackingMo::query()
            ->where('id', $params['id'])
            ->first();
        if ($trackingMo) {
            $trackingMo->delete();

            IssueCard::query()
                ->where('id', $trackingMo->issue_card_id)
                ->delete();
        }

        return $trackingMo;
    }

    public function getIssueCardHistories($params)
    {
        $userId = array_get($params, 'user_id', Auth::id());
        $ignoreEmpty = Arr::get($params, 'ignore_empty', false);
        $rawData = IssueCard::with('taker', 'bin', 'spare', 'torqueWrenchArea')
            //     // $rawData = IssueCard::join('bins', 'bins.id', 'issue_cards.bin_id')
            //     //     ->leftJoin('torque_wrench_areas', 'torque_wrench_areas.id', 'issue_cards.torque_wrench_area_id')
            //     //     ->join('shelfs', 'shelfs.id', 'bins.shelf_id')
            //     //     ->join('bin_spare', 'bin_spare.bin_id', '=', 'bins.id')
            //     //     ->leftJoin('clusters', 'clusters.id', 'bins.cluster_id')
            ->where('taker_id', $userId)
            ->where('quantity', '>', 0)
            ->where(function ($query) {
                $query->where('quantity', '>', DB::raw('IFNULL(returned_quantity, 0)'))
                    ->orWhereNull('returned_quantity');
            })
            ->orderBy('created_at', 'desc')->get();
        // var_dump($rawData);die();
        // // ->where('bins.is_failed', 0)->get();
        // //     ->when(!$ignoreEmpty, function ($query) use ($params) {
        // //         $query->where('bins.quantity', 0)
        // //             ->where('bins.quantity_oh', 0);
        // //     })
        // //     ->when(!empty($params['types']), function ($query) use ($params) {
        // //         $query->whereIn('spares.type', $params['types']);
        // //     })
        // //     ->when(!empty($params['cluster_id']), function ($query) use ($params) {
        // //         $query->where('bins.cluster_id', $params['cluster_id']);
        // //     })
        // //     ->when(!empty($params['search_key']), function ($query) use ($params) {
        // //         $searchKey = Utils::escapeLike($params['search_key']);

        //         $query->where(function ($subQuery) use ($searchKey) {
        //             $subQuery->where('spares.name', 'LIKE', "%{$searchKey}%")
        //                 ->orWhere('spares.part_no', 'LIKE', "%{$searchKey}%")
        //                 ->orWhere('spares.serial_no', 'LIKE', "%{$searchKey}%")
        //                 ->orWhere('spares.material_no', 'LIKE', "%{$searchKey}%");
        //         });
        //     })
        //     ->when(!empty($params['ignore_returned']), function ($query) use ($params) {
        //         $query->where(function ($subQuery) {
        //             $subQuery->whereNull('returned')
        //                 ->orWhere('returned', Consts::RETURNED_TYPE_PARTIAL);
        //         });
        //     })
        //     ->select(
        //         'issue_cards.*',
        //         'issue_cards.id as issue_card_id',
        //         'spares.name as spare_name',
        //         'spares.part_no',
        //         'spares.material_no',
        //         'spares.has_serial_no',
        //         'spares.url',
        //         'bins.row',
        //         'bins.bin as bin_name',
        //         'shelfs.name as shelf_name',
        //         'clusters.name as cluster_name',
        //         'torque_wrench_areas.area as veh_p_area',
        //         'torque_wrench_areas.torque_value',
        //         'spares.type as spare_type'
        //     )
        //     ->when(
        //         !empty($params['no_pagination']),
        //         function ($query) {
        //             return $query->get();
        //         },
        //         function ($query) use ($params) {
        //             return $query->paginate(array_get($params, 'limit', Consts::DEFAULT_PER_PAGE));
        //         }
        //     );

        // $data = Arr::get($params, 'no_pagination') ? $rawData : $rawData->getCollection();
        // $binIds = $data->pluck('bin_id')->toArray();
        // $configures = BinConfigure::whereIn('bin_id', $binIds)
        //     ->get()
        //     ->mapToGroups(function ($item) {
        //         return [$item['bin_id'] => $item];
        //     })
        //     ->toArray();

        // if (Arr::get($params, 'no_pagination')) {
        //     return $data->transform(function ($record) use ($configures) {
        //         return $this->transformReturnSpares($record, $configures);
        //     });
        // }

        // $rawData->getCollection()->transform(function ($record) use ($configures) {
        //     return $this->transformReturnSpares($record, $configures);
        // });
        // foreach ($rawData as $key => $item) {
        //     if( $item['returned_quantity']!=null){
        //         if ($item['quantity'] - $item['returned_quantity'] == 0) {
        //             unset($rawData[$key]);
        //         }
        //     }
        // }
        return $rawData;
    }

    private function transformReturnSpares($record, $configures)
    {
        $data = $record->toArray();
        $data['location'] = "{$record->cluster_name} - {$record->shelf_name} - {$record->row} - {$record->bin_name}";
        $binConfigs = Arr::get($configures, $record->bin_id, []);
        $data['configures'] = $binConfigs;

        return (object)$data;
    }

    public function replenishManual($params)
    {
        $rs = [];
        $replenishment = Replenishment::create([
            'uuid' => Utils::currentMilliseconds(),
            'type' => Consts::REPLENISHMENT_TYPE_MANUAL,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);

        $spares = array_get($params, 'spares', []);
        foreach ($spares as $value) {
            $replenishmentSpare = new ReplenishmentSpare;

            $value['replenishment_id'] = $replenishment->id;
            $replenishmentSpare = $this->saveData($replenishmentSpare, $value);

            $bin = Bin::where('id', $replenishmentSpare->bin_id)->first();
            if ($bin) {
                $bin->quantity_oh = BigNumber::new($bin->quantity_oh)->add($replenishmentSpare->quantity)->toString();
                $bin->save();
            }

            $this->updateRemainQtyInTransaction(
                Arr::get($params, 'user_id'),
                Consts::TAKING_TRANSACTION_TYPE_REPLENISH,
                $replenishmentSpare->quantity
            );

            $configures = [];
            if (!empty($value['configures'])) {
                $configures = array_get($value, 'configures', []);
            } else {
                $configures = BinConfigure::where('bin_id', $replenishmentSpare->bin_id)
                    ->get()
                    ->toArray();
            }
            if (!empty($configures)) {
                $this->saveReplenishmentSpareConfigures($configures, $replenishmentSpare);
            }

            $rs[] = $replenishmentSpare;
        }
        return $rs;
    }

    private function saveBinConfigures($data, $replenishmentSpare)
    {
        $binId = $replenishmentSpare->bin_id;
        $binConfigure = BinConfigure::query()->where('bin_id', $binId)->first();
        if ($binConfigure) {
            // hardcode only have 1 row
            $first = collect($data)->first();
            $data = [$first];

            foreach ($data as $index => $value) {
                $value['order'] = $index + 1;
                $value['bin_id'] = $binId;
                $this->saveData($binConfigure, $value);
            }
        }
    }

    private function saveReplenishmentSpareConfigures($data, $replenishmentSpare)
    {
        // hardcode only have 1 row
        $first = collect($data)->first();
        $data = [$first];

        foreach ($data as $index => $value) {
            $value['order'] = $index + 1;
            $value['replenishment_spare_id'] = $replenishmentSpare->id;
            $configure = new ReplenishmentSpareConfigure;
            $this->saveData($configure, $value);
        }
    }

    public function getReplenishAutoList($params)
    {
        return Replenishment::with('replenishSpares')
            ->when(!empty($params['type']), function ($query) use ($params) {
                $query->where('type', $params['type']);
            })
            ->when(
                !empty($params['sort']) && !empty($params['sort_type']),
                function ($query) use ($params) {
                    return $query->orderBy($params['sort'], $params['sort_type']);
                },
                function ($query) {
                    return $query->orderBy('updated_at', 'desc');
                }
            )
            ->when(
                !empty($params['no_pagination']),
                function ($query) {
                    return $query->get();
                },
                function ($query) use ($params) {
                    return $query->paginate(array_get($params, 'limit', Consts::DEFAULT_PER_PAGE));
                }
            );
    }

    public function getReplenishAutoByUuid($params)
    {
        $uuid = array_get($params, 'uuid', null);
        if (!$uuid) {
            return null;
        }

        return Replenishment::where('uuid', $uuid)
            ->with('replenishSpares')
            ->first();
    }

    public function replenishAuto($params)
    {
        $replenishment = Replenishment::create([
            'uuid' => Utils::currentMilliseconds(),
            'type' => Consts::REPLENISHMENT_TYPE_AUTO,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);

        $spares = array_get($params, 'spares', []);
        foreach ($spares as $value) {
            $replenishmentSpare = new ReplenishmentSpare;

            $value['replenishment_id'] = $replenishment->id;
            $this->saveData($replenishmentSpare, $value);
        }
    }

    public function confirmReplenishAuto($params)
    {
        $rs = [];
        $replenishId = array_get($params, 'replenish_id', null);
        $replenishment = Replenishment::find($replenishId);

        $replenishment->replenishSpares->each(function ($item) use (&$rs) {
            $bin = Bin::find($item->bin_id);
            if ($bin) {
                $bin->quantity_oh = BigNumber::new($bin->quantity_oh)->add($item->quantity)->toString();
                $bin->save();
                $shelfInfo = Shelf::find($bin->shelf_id);
                $rs[] = [
                    'cluster_id' => !empty($shelfInfo->cluster_id) ? $shelfInfo->cluster_id : 0,
                    'shelf_id' => $bin->shelf_id,
                    'row' => $bin->row,
                    'bin_id' => $item->bin_id,
                    'bin_name' => $bin->bin,
                    'drawer_name' => $bin->drawer_name,
                ];
            }
        });

        return $rs;
    }

    public function confirmReplenishAutoTablet($params)
    {
        $typeTransaction = Consts::TAKING_TRANSACTION_TYPE_REPLENISH_AUTO;
        $result = $resultReplenished = $resultSuggest = [];
        $replenishment = Replenishment::find(Arr::get($params, 'replenish_id'));
        $dryRun = Arr::get($params, 'dry_run', false);

        // Update total_quantity => When dry_run = 1
        $dryRun && $this->updateTotalQtyInTransaction(
            Arr::get($params, 'taker_id'),
            $typeTransaction,
            count($replenishment->replenishSpares)
        );

        $replenishment->replenishSpares->each(
            function ($item) use (&$result, &$resultReplenished, &$resultSuggest, $params, $typeTransaction) {
                $clusterIdCurrent = Arr::get($params, 'cluster_id');
                $dryRun = Arr::get($params, 'dry_run', false);
                $replenishId = Arr::get($params, 'replenish_id');
                $takerId = Arr::get($params, 'taker_id');

                $bin = Bin::query()
                    ->with(['cluster', 'shelf'])
                    ->where('id', $item->bin_id)
                    ->first();

                // Get remain_bins from transaction. Because from input only have replenish_id => You can not know spares was replenished
                $transaction = $this->getTransaction($takerId, $typeTransaction);
                $remainBinsTransaction = $transaction ? (array)json_decode($transaction->remain_bins ?? null) : [];
                $isContinue = false;
                foreach ($remainBinsTransaction as $remainBin) {
                    if ($remainBin->id == $bin->id) {
                        $isContinue = true;
                    }
                }

                if ($bin && ($isContinue || $dryRun)) {
                    $binData = array_merge($bin->toArray(), [
                        'location' => implode(' - ', [$bin->cluster->name, $bin->shelf->name, $bin->row, $bin->bin]),
                        'spare_name' => $bin->spare->name ?? '',
                        'cluster_name' => $bin->cluster->name ?? '',
                        'shelf_name' => $bin->shelf->name ?? '',
                        'bin_name' => $bin->bin,
                        'replenish_id' => $replenishId,
                        'taker_id' => $takerId,
                    ]);

                    $key = $bin->cluster_id . '-' . $bin->spare_id;
                    if (!isset($result[$key])) {
                        $result[$key] = [
                            'cluster_id' => $bin->cluster_id,
                            'spare_id' => $bin->spare_id,
                            'remained' => 0,
                            'bins' => []
                        ];
                    }

                    if ($bin->cluster_id == $clusterIdCurrent) {
                        // Update quantity
                        if (!$dryRun) {
                            // Quantity of spare per bin always 1
                            $bin->quantity_oh = 1;
                            $bin->quantity = 1;
                            //                        $bin->quantity_oh = BigNumber::new($bin->quantity_oh)->add($item->quantity)->toString();
                            //                        $bin->quantity = BigNumber::new($bin->quantity)->add($item->quantity)->toString();
                            $bin->save();

                            // Remove bin in remain bins
                            $this->removeRemainBinsInTransaction($takerId, $typeTransaction, $bin->id);
                        }

                        $resultReplenished[] = $binData;
                        $result[$key]['bins'][] = $binData;
                    } elseif ($bin->quantity <= 0) {
                        $resultSuggest[] = $binData;
                        $result[$key]['remained'] = $result[$key]['remained'] + 1;
                    }
                }
            }
        );

        $dryRun = Arr::get($params, 'dry_run', false);
        $takerId = Arr::get($params, 'taker_id');
        $currentClusterId = Arr::get($params, 'cluster_id');

        $dryRun || $this->updateRemainQtyInTransaction($takerId, $typeTransaction, count($resultReplenished));

        // Update bins_remain => When dry_run = 1
        $dryRun && $this->updateBinsRemainInTransaction(
            $takerId,
            $typeTransaction,
            array_merge($resultSuggest, $resultReplenished)
        );

        $result = $this->removeBinsEmpty($result);
        $suggestedBinFromTransaction = $this->getSuggestedBins($takerId, $typeTransaction, $currentClusterId);
        return [
            'data' => array_values($result),
            'suggested_bin' => count($suggestedBinFromTransaction) > 0 ? head($suggestedBinFromTransaction) : null
        ];
    }

    public function deleteReplenishAuto($replenishId)
    {
        $replenishment = Replenishment::find($replenishId);

        $replenishment->replenishSpares()->delete();
        // ReplenishmentSpare::where('replenishment_id', $replenishment->id)->delete();
        $replenishment->delete();

        return true;
    }

    public function replenishManualForEuc($params)
    {
        $spares = array_get($params, 'spares', []);

        foreach ($spares as $value) {
            if (empty($value['spare_id'])) {
                $spare = Spare::create([
                    'type' => Consts::SPARE_TYPE_EUC,
                    'part_no' => array_get($value, 'ssn', null),
                    'material_no' => array_get($value, 'mpn', null),
                    'name' => array_get($value, 'description', null),
                    'has_batch_no' => !empty($value['batch_no']),
                    'has_serial_no' => !empty($value['serial_no']),
                    'has_charge_time' => !empty($value['charge_time']),
                    'has_calibration_due' => !empty($value['calibration_due']),
                    'has_expiry_date' => !empty($value['expiry_date']),
                    'has_load_hydrostatic_test_due' => !empty($value['load_hydrostatic_test_due'])
                ]);
                $value['spare_id'] = $spare->id;
            }

            $eucBoxSpare = new EucBoxSpare;
            $eucBoxSpare->quantity_oh = 1;
            $this->saveData($eucBoxSpare, $value);

            $replenishEucBox = new ReplenishEucBox;
            $value['receiver_id'] = Auth::id();
            $this->saveData($replenishEucBox, $value);

            // $this->updateQuantityInEucBox(
            //     $issueCard->euc_box_id,
            //     $spare->id,
            //     -$issueCard->quantity
            // );
        }
    }

    public function generateCycleCount($params = [])
    {
        $cycleCount = CycleCount::create([
            'uuid' => Utils::currentMilliseconds()
        ]);

        $types = collect($params)->pluck('type')->toArray();
        $mapSpares = $this->getSpares($types)->groupBy('type');

        $result = collect([]);
        foreach ($params as $value) {
            $type = $value['type'];
            $limit = $value['number'];
            $data = empty($mapSpares[$type]) ? [] : $mapSpares[$type];

            $spares = collect($data)->shuffle()
                ->shuffle()
                ->take($limit)
                ->toArray();
            $result = $result->concat($spares);
        }

        collect($result)->each(function ($record) use ($cycleCount) {
            CycleCountSpare::create([
                'cycle_count_id' => $cycleCount->id,
                'spare_id' => $record['spare_id']
            ]);
        });

        return [
            'uuid' => $cycleCount->uuid,
            'spares' => $result
        ];
    }

    private function getSpares($types)
    {
        return Bin::join('spares', 'spares.id', 'bins.spare_id')
            ->join('shelfs', 'shelfs.id', 'bins.shelf_id')
            ->join('clusters', 'clusters.id', 'shelfs.cluster_id')
            ->where('bins.status', Consts::BIN_STATUS_ASSIGNED)
            ->whereIn('spares.type', $types)
            ->select(
                'bins.*',
                'spares.*',
                'shelfs.name as shelf_name',
                'bins.bin as bin_name',
                'clusters.name as cluster_name'
            )
            ->get();
    }

    public function sendCycleCountReport()
    {
        SettingUtils::validateMailSender();

        $currentTIme = now()->format('Y-m-d');

        $filename = "reports/cycle-count-{$currentTIme}.xlsx";
        Excel::store(new CycleCountExport(), $filename, 'public');
        $filePath = Storage::disk('public')->path($filename);

        $this->settingService->getEmailReceivers(Consts::RECEIVER_EMAIL_TYPE_CYCLE_COUNT)
            ->each(function ($receiver) use ($filePath) {
                Mail::send(new CycleCountReport($receiver, $filePath));
            });

        return true;
    }

    public function sendInventoryCountReport()
    {
        SettingUtils::validateMailSender();

        $currentTIme = now()->format('Y-m-d');

        $filename = "reports/inventory-count-{$currentTIme}.xlsx";
        Excel::store(new InventoryCountExport(), $filename, 'public');
        $filePath = Storage::disk('public')->path($filename);

        $this->settingService->getEmailReceivers(Consts::RECEIVER_EMAIL_TYPE_INVENTORY_COUNT)
            ->each(function ($receiver) use ($filePath) {
                Mail::send(new InventoryCountReport($receiver, $filePath));
            });

        return true;
    }

    public function getSparesReturn($params)
    {
        $userId = array_get($params, 'user_id', Auth::id());

        $types = [
            Consts::SPARE_TYPE_DURABLE,
            Consts::SPARE_TYPE_PERISHABLE,
            Consts::SPARE_TYPE_AFES,
            Consts::SPARE_TYPE_TORQUE_WRENCH,
            Consts::SPARE_TYPE_OTHERS,
            Consts::SPARE_TYPE_LIFTING_EQUIPMENT,
        ];
        $issueCardHistories = $this->getIssueCardHistories([
            'no_pagination' => Consts::TRUE,
            'ignore_returned' => Consts::TRUE,
            'types' => $types,
            'user_id' => $userId,
            'cluster_id' => Arr::get($params, 'cluster_id'),
        ]);

        //        $handedOverSpares = ReturnSpare::join('bins', 'bins.id', 'return_spares.bin_id')
        //            ->join('shelfs', 'shelfs.id', 'bins.shelf_id')
        //            ->join('spares', 'spares.id', 'bins.spare_id')
        //            ->when($types, function ($query) use ($types) {
        //                $query->whereIn('spares.type', $types);
        //            })
        //            ->when(!empty($params['cluster_id']), function ($query) use ($params) {
        //                $query->where('bins.cluster_id', $params['cluster_id']);
        //            })
        //            ->where('return_spares.type', Consts::HAND_OVER)
        //            ->where('return_spares.receiver_id', $userId)
        //            ->whereColumn('return_spares.quantity', '<>', 'return_spares.quantity_returned_store')
        //            ->select(
        //                'return_spares.*', 'return_spares.id as return_spare_id', 'spares.name as spare_name', 'spares.part_no',
        //                'spares.material_no', 'bins.row', 'bins.bin', 'shelfs.name as shelf_name', 'spares.type as spare_type'
        //            )
        //            ->addSelect('bins.bin as bin_name', 'bins.drawer_name')
        //            ->addSelect(DB::raw('0 as returned_quantity'))
        //            ->get();

        //        return $handedOverSpares->concat($issueCardHistories);
        return $issueCardHistories;
    }

    public function returnToStore($params)
    {
        $spares = array_get($params, 'spares', []);
        $userId = Arr::get($params, 'user_id');
        $spare_ids = [];
        $data = [];
        foreach ($spares as $item) {
            $return = new ReturnSpare();
            $return->type = Consts::RETURN_TO_STORE;
            $return->bin_id = $item['bin_id'];
            $return->spare_id = $item['spare_id'];
            $spare_ids[] = $item['spare_id'];
            $return->state = $item['state'];
            $return->quantity = $item['quantity'];
            $return->handover_id = Auth::id();
            $return->save();

            //unlock bin
            $binSpareCollection = BinSpare::where('bin_id', $return->bin_id)->where('spare_id', $return->spare_id)->get();
            if (!$binSpareCollection) {
                throw new Exception("Bin with bin id = {$return->bin_id} is invalid.");
            }
            foreach ($binSpareCollection as $bin) {
                $bin->is_processing = 0;
                $bin->process_time = null;
                $bin->process_by = null;
                $bin->save();
            }
            // $this->updateQuantityInBin($return->bin_id, $return->quantity);
            $this->updateQuantityInBinSpare($return->bin_id, $return->spare_id, $return->quantity);
            $this->updateRemainQtyInTransaction($userId, Consts::TAKING_TRANSACTION_TYPE_RETURN, $return->quantity);
            $data[] = (object)[
                'spare_id' => $item['spare_id'],
                'bin_id' => $item['bin_id'],
            ];
            $returnings = $this->getItemsHandover($return->bin_id, $return->spare_id, $userId);

            if (!empty($returnings)) {
                foreach ($returnings as $returns) {
                    $inputQuantity = $item['quantity'];
                    if (!$inputQuantity) {
                        continue;
                    }
                    $remainQuantityInCard = BigNumber::new($returns->quantity)
                        ->sub($returns->quantity_returned_store)
                        ->toString();

                    $returnedQuantity = BigNumber::new($returns->quantity_returned_store ?: 0)
                        ->add($inputQuantity)
                        ->toString();

                    // equal or bigger.
                    if (~BigNumber::new($inputQuantity)->comp($remainQuantityInCard)) {
                        $returnedQuantity = $returns->quantity;
                    }
                    $returns->quantity_returned_store = $returnedQuantity;
                    $returns->save();
                }
            }

            $cards = $this->getIssueCards($return->bin_id, $return->spare_id);
            if (!empty($cards)) {
                foreach ($cards as $card) {
                    $inputQuantity = $item['quantity'];
                    if (!$inputQuantity) {
                        continue;
                    }

                    $remainQuantityInCard = BigNumber::new($card->quantity)
                        ->sub($card->returned_quantity)
                        ->toString();

                    $state = Consts::RETURNED_TYPE_PARTIAL;
                    $returnedQuantity = BigNumber::new($card->returned_quantity ?: 0)
                        ->add($inputQuantity)
                        ->toString();

                    // equal or bigger.
                    if (~BigNumber::new($inputQuantity)->comp($remainQuantityInCard)) {
                        $state = Consts::RETURNED_TYPE_ALL;
                        $returnedQuantity = $card->quantity;
                    }
                    $card->returned = $state;
                    $card->returned_quantity = $returnedQuantity;
                    $card->save();
                }
            }

            TrackingMo::where('bin_id', $return->bin_id)->where('spare_id', $return->spare_id)
                ->delete();

            $transaction_last = TakingTransaction::orderBy('created_at', 'desc')->limit(1)->first();
            $binGet = Bin::find($item['bin_id']);
            $cluster = Cluster::find($binGet->cluster_id);
            $shelf = Shelf::find($binGet->shelf_id);
            // $transaction_last = $transaction_last->toArray();
            $bub_num = 0;
            if (!empty($transaction_last)) {
                $bub_num = $transaction_last->id + 1;
            } else {
                $bub_num = 1;
            }
            $data_transaction = [
                'user_id' => 1,
                'type' => 'issue',
                'satus' => 'done',
                'name' => 'trans#' . $bub_num,
                'cabinet_id' => $binGet->shelf_id,
                'bin_id' => $item['bin_id'],
                'request_qty' => $item['quantity'],
                'cluster_name' => $cluster->name,
                'cabinet_name' => $shelf->name,
                'bin_name' => $binGet->bin,
                'total_qty' => 0,
                'remain_qty' => 0,
                'part_number' => 0,
            ];
            $transaction_new = TakingTransaction::create($data_transaction);
            $taking_transaction_detail = [
                'taking_transaction_id' => $transaction_new->id,
                'request_qty' =>  $item['quantity'],
                'spare_id' => $item['spare_id'],
                'listWO' => 'null'
            ];
            TransactionSpare::create($taking_transaction_detail);
        }

        // ReturningSpareJob::dispatch(Auth::id(), $spares, $spare_ids);

        return $data;
    }
    private function getIssueCards($binIds, $spare)
    {
        return IssueCard::where('bin_id', $binIds)->where('spare_id', $spare)
            ->where(function ($query) {
                $query->whereNull('returned')
                    ->orWhere('returned', Consts::RETURNED_TYPE_PARTIAL);
            })
            ->orderBy('created_at')
            ->get();
    }
    private function getItemsHandover($binIds, $spare_id, $userId)
    {
        return ReturnSpare::where('bin_id', $binIds)->where('spare_id', $spare_id)
            ->where('type', Consts::HAND_OVER)
            ->where('receiver_id', $userId)
            ->whereColumn('quantity', '<>', 'quantity_returned_store')
            ->orderBy('created_at')
            ->get();
    }
    private function updateQuantityInBin($binId, $quantity)
    {
        $bin = Bin::find($binId);
        if (!$bin) {
            throw new Exception("Bin with bin id = {$binId} is invalid.");
        }

        $newQuantity = BigNumber::new($bin->quantity_oh)->add($quantity)->toString();
        $bin->quantity_oh = $newQuantity;
        $bin->quantity = $newQuantity;
        $bin->save();

        return $bin;
    }
    private function updateQuantityInBinSpare($binId, $spareId, $quantity)
    {
        $bin = BinSpare::where('bin_id', $binId)->where('spare_id', $spareId)->first();
        if (!$bin) {
            throw new Exception("Bin with bin id = {$binId} is invalid.");
        }
        // foreach ($binSpareCollection as $bin) {
        $newQuantity = BigNumber::new($bin->quantity_oh)->add($quantity)->toString();
        $bin->quantity_oh = $newQuantity;
        $bin->quantity = $newQuantity;
        $bin->save();

        return $bin;
        // }
    }

    private function updateQuantityInEucBox($eucBoxId, $spareId, $quantity)
    {
        $euc = EucBoxSpare::where('euc_box_id', $eucBoxId)
            ->where('spare_id', $spareId)
            ->first();
        if (!$euc) {
            throw new Exception('EUC is invalid.');
        }

        $euc->quantity_oh = BigNumber::new($euc->quantity_oh)->add($quantity)->toString();
        $euc->save();

        return $euc;
    }

    public function handOverSpares($params)
    {
        $receiverId = array_get($params, 'receiver_id');
        $spares = array_get($params, 'spares', []);

        foreach ($spares as $item) {
            $return = new ReturnSpare();
            $return->type = Consts::HAND_OVER;
            $return->bin_id = $item['bin_id'];
            $return->spare_id = $item['spare_id'];
            $return->state = $item['state'];
            $return->quantity = $item['quantity'];
            $return->quantity_returned_store = 0;
            $return->handover_id = Auth::id();
            $return->receiver_id = $receiverId;
            $return->save();
        }

        ReturningSpareJob::dispatch(Auth::id(), $spares);

        return true;
    }

    public function getSparesExpiring($params = [])
    {
        return $this->getSparesInBin($params);
    }

    private function getSparesInBin($params)
    {
        $expiredFrom = array_get($params, 'expiredFrom', null);
        $expiredTo = array_get($params, 'expiredTo', null);

        $data = BinConfigure::join('bins', 'bins.id', 'bin_configures.bin_id')
            ->join('spares', 'spares.id', 'bin_configures.spare_id')
            ->join('shelfs', 'shelfs.id', 'bins.shelf_id')
            ->leftJoin('clusters', 'clusters.id', 'bins.cluster_id')
            ->when(
                !empty($expiredFrom) && !empty($expiredTo),
                function ($query) use ($expiredFrom, $expiredTo) {
                    $query->whereBetween('bin_configures.expiry_date', [$expiredFrom, $expiredTo]);
                }
            )
            ->when(!empty($params['search_key']), function ($query) use ($params) {
                $searchKey = Utils::escapeLike($params['search_key']);

                $query->where(function ($subQuery) use ($searchKey) {
                    $subQuery->where('spares.part_no', 'LIKE', "%{$searchKey}%");
                });
            })
            ->select(
                'spares.*',
                'spares.type as item_type',
                'bins.*',
                'bin_configures.*',
                'bin_configures.id as bin_configure_id',
                'shelfs.name as shelf_name'
            )
            ->addSelect('bins.bin as bin_name', 'clusters.name as cluster_name')
            ->get();

        $sparesExpiredReturns = $this->getSparesExpiredForReturns($params);
        $data = $this->mergeSpareExpiring($data, $sparesExpiredReturns)
            ->when(
                !empty($params['sort']) && !empty($params['sort_type']),
                function ($query) use ($params) {
                    $sortType = array_get($params, 'sort_type');
                    return $sortType === Consts::SORT_TYPE_ASC
                        ? $query->sortBy($params['sort'])
                        : $query->sortByDesc($params['sort']);
                },
                function ($query) {
                    return $query->sortBy('point_all_date');
                }
            )
            ->values();

        $noPagination = array_get($params, 'no_pagination', Consts::FALSE);
        $limit = array_get($params, 'limit', Consts::DEFAULT_PER_PAGE);

        return $noPagination ? $data : Utils::convertArrayToPagination($data, $limit);
    }

    private function mergeSpareExpiring($data, $map)
    {
        $expiresState = [
            Consts::RETURN_SPARE_STATE_DAMAGE,
            Consts::RETURN_SPARE_STATE_EXPIRED,
            Consts::RETURN_SPARE_STATE_FINISHED
        ];

        $result = collect([]);
        foreach ($data as $key => $record) {
            // expected it doesn't occur
            if (!$record->bin_id) {
                continue;
            }

            //            if (empty($map[$record->bin_id])) {
            if (empty($map[$record->bin_id . "_" . $record->spare_id])) {
                $result->push($record);
                continue;
            }

            //            $returned = collect($map[$record->bin_id])->first();
            $returned = collect($map[$record->bin_id . "_" . $record->spare_id])->first();
            $lastReturnSpareState = Arr::get($returned, 'return_spares_state');
            if (in_array($lastReturnSpareState, $expiresState)) {
                $returned->has_expiry_date = 1;
                $returned->expiry_date = Carbon::now()->subDay();
            }
            $result->push($returned);
        }

        return $result->map(function ($record) {
            return $this->normalizeSpareExpiring($record);
        })
            ->sortBy('point')
            ->values();
    }

    /*
     * when a item is returning, the system will ask the user the item is return (1) working, (2) damage (3) expired (4) finish.
     * For 2, 3 and 4, the system will drop these items to the Expired Bin and when user write off,
     * user will have to input the reason to write off the item
     */
    private function getSparesExpiredForReturns($params)
    {
        $expiresState = [
            Consts::RETURN_SPARE_STATE_DAMAGE,
            Consts::RETURN_SPARE_STATE_EXPIRED,
            Consts::RETURN_SPARE_STATE_FINISHED
        ];
        $hasExpiryDateColumn = DB::raw('1 as has_expiry_date');
        $expiryDateColumn = DB::raw('DATE_SUB(NOW(), INTERVAL 1 DAY) as expiry_date');

        return ReturnSpare::join('bins', 'bins.id', 'return_spares.bin_id')
            ->join('bin_configures', 'bin_configures.bin_id', 'bins.id')
            ->join('spares', 'spares.id', 'return_spares.spare_id')
            ->join('shelfs', 'shelfs.id', 'bins.shelf_id')
            ->leftJoin('clusters', 'clusters.id', 'bins.cluster_id')
            //            ->whereIn('return_spares.state', $expiresState)
            ->when(!empty($params['search_key']), function ($query) use ($params) {
                $searchKey = Utils::escapeLike($params['search_key']);

                $query->where(function ($subQuery) use ($searchKey) {
                    $subQuery->where('spares.part_no', 'LIKE', "%{$searchKey}%");
                });
            })
            ->where(function ($subQuery) {
                /** @var Builder $subQuery */
                $subQuery->whereNull('return_spares.write_off')
                    ->orWhere('return_spares.write_off', Consts::FALSE);
            })
            ->select(
                'spares.*',
                'spares.type as item_type',
                'bins.*',
                'bin_configures.*',
                'bins.bin as bin_name',
                'return_spares.*',
                'return_spares.quantity as quantity_oh',
                'bin_configures.id as bin_configure_id',
                'shelfs.name as shelf_name',
                'clusters.name as cluster_name',
                'return_spares.state AS return_spares_state',
            )
            //            ->addSelect($hasExpiryDateColumn)
            //            ->addSelect($expiryDateColumn)
            ->orderBy('return_spares.id', 'DESC')
            ->get()
            //            ->groupBy('bin_id');
            ->groupBy(function ($item) {
                return $item['bin_id'] . "_" . $item['spare_id'];
            });
    }

    private function normalizeSpareExpiring($record)
    {
        $expiryDate = $record->has_expiry_date ? $record->expiry_date : null;
        $calibrationDue = $record->has_calibration_due ? $record->calibration_due : null;
        $loadHydrostaticTestDue = $record->has_load_hydrostatic_test_due ? $record->load_hydrostatic_test_due : null;

        return (object)[
            'bin_configure_id' => $record->bin_configure_id,
            'bin_id' => $record->bin_id,
            'spare_id' => $record->spare_id,
            'location' => "{$record->cluster_name} - {$record->shelf_name} - {$record->row} - {$record->bin}",
            'name' => $record->name,
            'part_no' => $record->part_no,
            'item_type' => $record->item_type,
            'quantity_oh' => $record->quantity_oh,
            'expiry_date' => $expiryDate,
            'calibration_due' => $calibrationDue,
            'load_hydrostatic_test_due' => $loadHydrostaticTestDue,
            'point' => $this->getSpareExpiringPoint($expiryDate),
            'point_all_date' => $this->getSpareExpiringPointByAllDueDate(
                $expiryDate,
                $calibrationDue,
                $loadHydrostaticTestDue
            ),
        ];
    }

    private function getSpareExpiringPointByAllDueDate($expiryDate, $calibrationDue, $loadHydrostaticTestDue)
    {
        $pointExpiryDate = $this->getSpareExpiringPoint($expiryDate);
        $pointCalibrationDue = $this->getSpareExpiringPoint($calibrationDue);
        $pointLoadHydrostaticTestDue = $this->getSpareExpiringPoint($loadHydrostaticTestDue);

        return min($pointExpiryDate, $pointCalibrationDue, $pointLoadHydrostaticTestDue);
    }

    private function getSpareExpiringPoint($expiryDate)
    {
        $timeList = collect([
            ['day' => 0, 'point' => 1, 'name' => 'Expired'],
            ['day' => 14, 'point' => 1, 'name' => 'Expired'],
            ['day' => 30, 'point' => 2, 'name' => 'In 30 days'],
            ['day' => 90, 'point' => 3, 'name' => 'In 60 Days'],
            ['day' => 100, 'point' => 4, 'name' => 'Refresh']
        ]);

        if (!$expiryDate) {
            return $timeList->last()['point'];
        }

        $now = now();
        $expiryDate = Carbon::parse($expiryDate);

        foreach ($timeList as $value) {
            $date = $expiryDate->copy()->subDays($value['day']);

            if ($date->lte($now)) {
                return $value['point'];
            }
        }

        return $timeList->last()['point'];
    }



    private function queryRange($query, $value, $property)
    {
        if (!is_array($value)) {
            $value = (array)json_decode($value, true);
        }

        $start = array_get($value, 'start');
        $end = array_get($value, 'end');

        if (empty($start) || empty($end)) {
            return $query;
        }

        return $query->where($property, '>=', $start)
            ->where($property, '<=', $end);
    }

    private function transformIssueSpare($record, $configures, $sparesExpiredReturns = null)
    {
        $now = now();
        $createdAt = Carbon::parse($record->created_at);
        $diffDays = $now->diffInDays($createdAt);

        $remaningQuantity = BigNumber::new($record->issued_quantity)
            ->sub($record->returned_quantity ?: 0)
            ->toString();

        $binConfigure = !empty($configures[$record->bin_id]) ? collect($configures[$record->bin_id])->first() : null;

        $expiresState = [
            Consts::RETURN_SPARE_STATE_DAMAGE,
            Consts::RETURN_SPARE_STATE_EXPIRED,
            Consts::RETURN_SPARE_STATE_FINISHED
        ];

        $returned = collect(Arr::get($sparesExpiredReturns, $record->bin_id . "_" . $record->spare_id))->first();
        $lastReturnSpareState = Arr::get($returned, 'return_spares_state');
        if (in_array($lastReturnSpareState, $expiresState)) {
            $returned->has_expiry_date = 1;
            $returned->expiry_date = Carbon::now()->subDay();
        }

        return (object)[
            'vehicle_num' => $record->vehicle_num,
            'wo' => $record->wo,
            'card_num' => $record->card_num,
            'platform' => $record->platform,
            'spare_name' => $record->spare_name,
            'part_no' => $record->part_no,
            'material_no' => $record->material_no,
            'fully_returned' => !$remaningQuantity,
            'issued_quantity' => $record->issued_quantity,
            'quantity' => $remaningQuantity,
            'issued_date' => $record->issued_date,
            'returned_quantity' => $record->returned_quantity,
            'issued_by' => $record->issuer_name,
            'issued_to' => $record->taker_name,
            'issued_id' => $record->issued_id,
            'yet_to_return' => $diffDays < 1 ? "{$diffDays} day" : "{$diffDays} days",
            'tnx' => $this->getTnxLabel($record),
            'torque_area' => $record->torque_area,
            'torque_value' => floatval($record->torque_value),
            'torque_id' => floatval($record->torque_id),
            'expiry_date' => $binConfigure ? $binConfigure['expiry_date'] : null,
            'calibration_due' => $binConfigure ? $binConfigure['calibration_due'] : null,
            'issued_update_date' => $record->issued_update_date,
            'load_hydrostatic_test_due' => $binConfigure ? $binConfigure['load_hydrostatic_test_due'] : null,
            'serial_no' => $record->serial_no,
            'euc_box_order' => $record->euc_box_order,
        ];
    }

    private function getTnxLabel($record)
    {
        switch ($record->spare_type) {
            case Consts::SPARE_TYPE_CONSUMABLE:
                return 'I';
            default:
                if ($record->issued_quantity - $record->returned_quantity == 0) {
                    return 'R';
                }

                return 'L';
        }

        return null;
    }

    // public function getYetToReturnSpares($params = [])
    // {
    //     $data = $this->getIssueCardsBuilder($params);

    //     $noPagination = array_get($params, 'no_pagination', Consts::FALSE);
    //     $limit = array_get($params, 'limit', Consts::DEFAULT_PER_PAGE);

    //     return $noPagination ? $data : Utils::convertArrayToPagination($data, $limit);
    // }

    public function getSparesForReport($params = [])
    {
        return $this->getIssueCardsBuilder($params);
    }

    // public function getSparesTorqueWrench($params = [])
    // {
    //     $params = array_merge([
    //         'torque_wrench' => Consts::TRUE,
    //         'types' => [
    //             Consts::SPARE_TYPE_TORQUE_WRENCH
    //         ],
    //         'returned_type' => [
    //             Consts::RETURNED_TYPE_PARTIAL,
    //             Consts::RETURNED_TYPE_ALL,
    //             Consts::RETURNED_TYPE_LINK_MO,
    //         ]
    //     ], $params);
    //     return $this->getIssueCardsBuilder($params);
    // }



    // public function getReportByLoan($params = [])
    // {
    //     $params = array_merge($params, [
    //         'types' => [
    //             Consts::SPARE_TYPE_DURABLE,
    //             Consts::SPARE_TYPE_PERISHABLE,
    //             Consts::SPARE_TYPE_AFES,
    //             Consts::SPARE_TYPE_OTHERS,
    //             Consts::SPARE_TYPE_TORQUE_WRENCH,
    //         ]
    //     ]);
    //     return $this->getIssueCardsBuilder($params);
    // }

    // public function getReportForReturns($params = [])
    // {
    //     $returnedDate = array_get($params, 'returned_date', null);
    //     $noPagination = array_get($params, 'no_pagination', Consts::FALSE);
    //     $limit = array_get($params, 'limit', Consts::DEFAULT_PER_PAGE);
    //     $sendMailAlert = Arr::get($params, 'send_mail_alert');

    //     $rawData = ReturnSpare::join('spares', 'spares.id', 'return_spares.spare_id')
    //         ->leftJoin('bins', 'bins.id', 'return_spares.bin_id')
    //         ->leftJoin('shelfs', 'shelfs.id', 'bins.shelf_id')
    //         ->leftJoin('clusters', 'clusters.id', 'bins.cluster_id')
    //         ->leftJoin('users', 'users.id', 'return_spares.handover_id')
    //         ->when($returnedDate, function ($query) use ($returnedDate) {
    //             return $this->queryRange($query, $returnedDate, 'return_spares.created_at');
    //         })
    //         ->when($sendMailAlert !== null, function ($query) use ($sendMailAlert) {
    //             $query->where('return_spares.send_mail_alert', $sendMailAlert);
    //         })
    //         ->where(function ($query) {
    //             $query->whereNull('return_spares.write_off')
    //                 ->orWhere('return_spares.write_off', Consts::FALSE);
    //         })
    //         ->when(!empty($params['search_key']), function ($query) use ($params) {
    //             $searchKey = Utils::escapeLike($params['search_key']);
    //             $query->where(function ($subQuery) use ($searchKey) {
    //                 $subQuery->where('spares.name', 'LIKE', "%{$searchKey}%")
    //                     ->orWhere('spares.part_no', 'LIKE', "%{$searchKey}%")
    //                     ->orWhere('spares.material_no', 'LIKE', "%{$searchKey}%");
    //             });
    //         })
    //         ->select(
    //             'spares.type as spare_type',
    //             'spares.name as spare_name',
    //             'spares.part_no',
    //             'return_spares.created_at as returned_date',
    //             'users.name as handover',
    //             'return_spares.bin_id',
    //             'return_spares.spare_id',
    //             'return_spares.quantity',
    //             'return_spares.quantity_returned_store',
    //             'return_spares.id as return_spare_id',
    //             'return_spares.state as return_state'
    //         )
    //         ->addSelect(DB::raw('CONCAT(clusters.name," - ",shelfs.name," - ",bins.row, " - ",bins.bin) as location'))
    //         ->orderBy('return_spares.state', 'asc')
    //         ->when(
    //             !empty($params['sort']) && !empty($params['sort_type']),
    //             function ($query) use ($params) {
    //                 return $query->orderBy($params['sort'], $params['sort_type']);
    //             },
    //             function ($query) {
    //                 return $query->orderBy('return_spares.updated_at', 'desc');
    //             }
    //         )
    //         ->when(
    //             empty($noPagination),
    //             function ($query) use ($limit) {
    //                 return $query->paginate($limit);
    //             },
    //             function ($query) {
    //                 return $query->get();
    //             }
    //         );

    //     $data = $noPagination ? $rawData : $rawData->getCollection();
    //     $binIds = $data->pluck('bin_id')->toArray();
    //     $configures = BinConfigure::whereIn('bin_id', $binIds)
    //         ->get()
    //         ->mapToGroups(function ($item) {
    //             return [$item['bin_id'] => $item];
    //         })
    //         ->toArray();


    //     if ($noPagination) {
    //         return $data->transform(function ($record) use ($configures) {
    //             return $this->transformReturnSpare($record, $configures);
    //         });
    //     }

    //     $rawData->getCollection()->transform(function ($record) use ($configures) {
    //         return $this->transformReturnSpare($record, $configures);
    //     });

    //     return $rawData;
    // }

    private function transformReturnSpare($record, $configures)
    {
        $binConfigure = !empty($configures[$record->bin_id]) ? collect($configures[$record->bin_id])->first() : null;

        $stateItems = [
            Consts::RETURN_SPARE_STATE_DAMAGE,
            Consts::RETURN_SPARE_STATE_EXPIRED,
            Consts::RETURN_SPARE_STATE_FINISHED,
            Consts::RETURN_SPARE_STATE_INCOMPLETE,
        ];

        return (object)[
            // 'vehicle_num' => $record->vehicle_num,
            // 'wo' => $record->wo,
            // 'platform' => $record->platform,
            'return_spare_id' => $record->return_spare_id,
            'bin_id' => $record->bin_id,
            'spare_id' => $record->spare_id,
            'spare_name' => $record->spare_name,
            'part_no' => $record->part_no,
            'quantity' => $record->quantity,
            'returned_date' => $record->returned_date,
            'returned_by' => $record->handover,
            'tnx' => 'R',
            'expiry_date' => $binConfigure ? $binConfigure['expiry_date'] : null,
            'calibration_due' => $binConfigure ? $binConfigure['calibration_due'] : null,
            'load_hydrostatic_test_due' => $binConfigure ? $binConfigure['load_hydrostatic_test_due'] : null,
            'not_use' => in_array($record->return_state, $stateItems),
            'return_state' => $record->return_state,
            'location' => $record->location,
            // 'torque_area' => $record->torque_area,
            // 'torque_value' => $record->torque_value,
        ];
    }

    public function getReportByExpired($params = [])
    {
        return $this->getSparesInBin($params);
    }

    // public function unwriteOffSpare($params = [])
    // {
    //     $writeOffId = array_get($params, 'id', null);
    //     $writeOff = WriteOff::find($writeOffId);

    //     $writeOff->eliminator_id = Auth::id();
    //     $writeOff->save();
    //     $writeOff->delete();

    //     //        ReturnSpare::where('id', $writeOff->return_spare_id)
    //     //            ->update([
    //     //                         'write_off' => Consts::FALSE
    //     //                     ]);
    //     //
    //     //        $bin = Bin::findOrFail($writeOff->bin_id);
    //     //        $bin->quantity_oh = BigNumber::new($bin->quantity_oh ?? 0)
    //     //            ->add($writeOff->quantity)
    //     //            ->toString();
    //     //        $bin->save();

    //     return $writeOff;
    // }

    private function updateQuantityOhInBin($binId, $quantity)
    {
        $bin = Bin::findOrFail($binId);
        $bin->quantity_oh = $quantity;
        $bin->save();
        return true;
    }

    // public function getSparesWriteOff($params = [])
    // {
    //     $dates = array_get($params, 'dates', null);

    //     $data = WriteOff::leftJoin('bins', 'bins.id', 'write_offs.bin_id')
    //         ->join('users', 'users.id', 'write_offs.user_id')
    //         ->join('spares', 'spares.id', 'bins.spare_id')
    //         ->join('shelfs', 'shelfs.id', 'bins.shelf_id')
    //         ->leftJoin('clusters', 'clusters.id', 'bins.cluster_id')
    //         ->when($dates, function ($query) use ($dates) {
    //             return $this->queryRange($query, $dates, 'write_offs.created_at');
    //         })
    //         ->when(!empty($params['search_key']), function ($query) use ($params) {
    //             $searchKey = Utils::escapeLike($params['search_key']);
    //             $query->where(function ($subQuery) use ($searchKey) {
    //                 $subQuery->where('spares.name', 'LIKE', "%{$searchKey}%")
    //                     ->orWhere('spares.part_no', 'LIKE', "%{$searchKey}%")
    //                     ->orWhere('spares.material_no', 'LIKE', "%{$searchKey}%");
    //             });
    //         })
    //         ->select('spares.*', 'bins.*', 'write_offs.*', 'write_offs.id as write_off_id', 'shelfs.name as shelf_name')
    //         ->addSelect('users.name as write_off_name', 'bins.bin as bin_name', 'clusters.name as cluster_name')
    //         ->get()
    //         ->map(function ($record) {
    //             return (object)[
    //                 'write_off_id' => $record->write_off_id,
    //                 'bin_id' => $record->bin_id,
    //                 'spare_id' => $record->spare_id,
    //                 'location' => "{$record->cluster_name} - {$record->shelf_name} - {$record->row} - {$record->bin}",
    //                 'name' => $record->name,
    //                 'part_no' => $record->part_no,
    //                 'quantity' => $record->quantity,
    //                 'reason' => $record->reason,
    //                 'write_off_name' => $record->write_off_name,
    //                 'created_at' => $record->created_at,
    //             ];
    //         });

    //     $noPagination = array_get($params, 'no_pagination', Consts::FALSE);
    //     $limit = array_get($params, 'limit', Consts::DEFAULT_PER_PAGE);

    //     return $noPagination ? $data : Utils::convertArrayToPagination($data, $limit);
    // }

    public function sendSparesExpiringReport($params = [])
    {
        SettingUtils::validateMailSender();

        $currentTIme = now()->format('Y-m-d');

        $filename = "reports/spares-expiring-{$currentTIme}.xlsx";
        Excel::store(new SpareExpiringExport(), $filename, 'public');
        $filePath = Storage::disk('public')->path($filename);

        $emails = array_get($params, 'emails', []);

        collect($emails)
            ->each(function ($receiver) use ($filePath) {
                Mail::queue(new SpareExpiringReport($receiver, $filePath));
            });

        return true;
    }

    public function sendYetToReturnSparesReport($params = [])
    {
        SettingUtils::validateMailSender();

        $currentTIme = now()->format('Y-m-d');

        $filename = "reports/yet-to-return-spares-{$currentTIme}.xlsx";
        Excel::store(new YetToReturSparesExport(), $filename, 'public');
        $filePath = Storage::disk('public')->path($filename);

        $emails = array_get($params, 'emails', []);

        collect($emails)
            ->each(function ($receiver) use ($filePath) {
                Mail::queue(new YetToReturnSparesReport($receiver, $filePath));
            });

        return true;
    }

    public function sendSparesReportByWo($params = [])
    {
        SettingUtils::validateMailSender();

        $currentTIme = now()->format('Y-m-d');

        $filename = "reports/spares-wo-{$currentTIme}.xlsx";
        Excel::store(new SparesByWoExport(), $filename, 'public');
        $filePath = Storage::disk('public')->path($filename);

        $emails = array_get($params, 'emails', []);

        collect($emails)
            ->each(function ($receiver) use ($filePath) {
                Mail::queue(new SparesByWoReport($receiver, $filePath));
            });

        return true;
    }

    public function sendSparesReportByExpired($params = [])
    {
        SettingUtils::validateMailSender();

        $currentTIme = now()->format('Y-m-d');

        $filename = "reports/spares-expired-{$currentTIme}.xlsx";
        Excel::store(new SparesByExpiredExport(), $filename, 'public');
        $filePath = Storage::disk('public')->path($filename);

        $emails = array_get($params, 'emails', []);

        collect($emails)
            ->each(function ($receiver) use ($filePath) {
                Mail::queue(new SparesByExpiredReport($receiver, $filePath));
            });

        return true;
    }

    public function sendSparesReportByLoan($params = [])
    {
        SettingUtils::validateMailSender();

        $currentTIme = now()->format('Y-m-d');

        $filename = "reports/spares-loan-{$currentTIme}.xlsx";
        Excel::store(new SparesByLoanExport(), $filename, 'public');
        $filePath = Storage::disk('public')->path($filename);

        $emails = array_get($params, 'emails', []);

        collect($emails)
            ->each(function ($receiver) use ($filePath) {
                Mail::queue(new SparesByLoanReport($receiver, $filePath));
            });

        return true;
    }

    public function sendSparesReportByTnx($params = [])
    {
        SettingUtils::validateMailSender();

        $currentTIme = now()->format('Y-m-d');

        $filename = "reports/spares-trans-{$currentTIme}.xlsx";
        Excel::store(new SparesByTnxExport($params), $filename, 'public');
        $filePath = Storage::disk('public')->path($filename);

        $emails = array_get($params, 'emails', []);

        collect($emails)
            ->each(function ($receiver) use ($filePath) {
                Mail::queue(new SparesByTnxReport($receiver, $filePath));
            });

        return true;
    }

    public function sendSparesReportByReturns($params = [])
    {
        SettingUtils::validateMailSender();

        $currentTIme = now()->format('Y-m-d');

        $filename = "reports/spares-returns-{$currentTIme}.xlsx";
        Excel::store(new SparesByReturnsExport(), $filename, 'public');
        $filePath = Storage::disk('public')->path($filename);

        $emails = array_get($params, 'emails', []);

        collect($emails)
            ->each(function ($receiver) use ($filePath) {
                Mail::queue(new SparesByReturnsReport($receiver, $filePath));
            });

        return true;
    }

    public function sendSparesWriteOffReport($params = [])
    {
        SettingUtils::validateMailSender();

        $currentTIme = now()->format('Y-m-d');

        $filename = "reports/spares-write-off-{$currentTIme}.xlsx";
        Excel::store(new SparesWriteOffExport(), $filename, 'public');
        $filePath = Storage::disk('public')->path($filename);

        $emails = array_get($params, 'emails', []);

        collect($emails)
            ->each(function ($receiver) use ($filePath) {
                Mail::queue(new SparesWriteOffReport($receiver, $filePath));
            });

        return true;
    }

    public function sendSparesTorqueWrenchReport($params = [])
    {
        SettingUtils::validateMailSender();

        $currentTIme = now()->format('Y-m-d');

        $filename = "reports/torque-wrench-{$currentTIme}.xlsx";
        Excel::store(new SparesTorqueWrenchExport($params), $filename, 'public');
        $filePath = Storage::disk('public')->path($filename);

        $emails = array_get($params, 'emails', []);

        collect($emails)
            ->each(function ($receiver) use ($filePath) {
                Mail::queue(new SparesTorqueWrenchReport($receiver, $filePath));
            });

        return true;
    }

    public function getEucItemHistories($params)
    {
        $dates = array_get($params, 'dates', null);

        $replenishHistories = ReplenishEucBox::join('spares', 'spares.id', 'replenish_euc_boxes.spare_id')
            ->join('users as requester', 'requester.id', 'replenish_euc_boxes.requester_id')
            ->join('users as receiver', 'receiver.id', 'replenish_euc_boxes.receiver_id')
            ->join('euc_box_spares', function ($join) {
                $join->on('replenish_euc_boxes.euc_box_id', '=', 'euc_box_spares.euc_box_id');
                $join->on('replenish_euc_boxes.spare_id', '=', 'euc_box_spares.spare_id');
            })
            ->join('euc_boxes', 'euc_boxes.id', 'replenish_euc_boxes.euc_box_id')
            ->where('spares.type', Consts::SPARE_TYPE_EUC)
            ->when($dates, function ($query) use ($dates) {
                return $this->queryRange($query, $dates, 'replenish_euc_boxes.created_at');
            })
            ->select(
                DB::raw('1 as quantity'),
                DB::raw("'R' as tnx"),
                'spares.name as spare_name',
                'spares.part_no',
                'spares.material_no',
                'requester.name as requester_name',
                'receiver.name as receiver_name',
                'replenish_euc_boxes.id',
                'replenish_euc_boxes.created_at',
                'replenish_euc_boxes.created_at as replenish_created_at',
                'euc_boxes.order AS euc_box_order',
                'euc_box_spares.serial_no'
            )
            ->get();

        $issueCardHistories = $this->getIssueCardsBuilder(
            array_merge($params, [
                'no_pagination' => Consts::TRUE,
                'types' => [Consts::SPARE_TYPE_EUC],
                'issued_date' => $dates,
                'only_type_euc' => true,
            ])
        )
            ->map(function ($record) {
                $record->quantity = $record->issued_quantity;
                $record->created_at = $record->issued_date;
                $record->tnx = 'I';
                return $record;
            });

        foreach ($replenishHistories as $replenishHistory) {
            foreach ($issueCardHistories as $index => $issueCardHistory) {
                if ($replenishHistory->serial_no == $issueCardHistory->serial_no) {
                    $replenishHistory['issue_item'] = $issueCardHistory;
                    unset($issueCardHistories[$index]);
                    break;
                }
            }
        }

        $data = $replenishHistories->concat($issueCardHistories)->sortByDesc('created_at');

        $noPagination = array_get($params, 'no_pagination', Consts::FALSE);
        $limit = array_get($params, 'limit', Consts::DEFAULT_PER_PAGE);

        return $noPagination ? $data : Utils::convertArrayToPagination($data, $limit);
    }

    public function searchSpare($params)
    {
        return Spare::when(!empty($params['item_pn']), function ($query) use ($params) {
            $searchKey = Utils::escapeLike($params['item_pn']);
            $query->where(function ($subQuery) use ($searchKey) {
                $subQuery->where('spares.part_no', 'LIKE', "%{$searchKey}%");
            });
        })
            ->orderBy('spares.updated_at', 'DESC')
            ->select('spares.*')
            ->when(
                !empty($params['no_pagination']),
                function ($query) {
                    return $query->get();
                },
                function ($query) use ($params) {
                    return $query->paginate(array_get($params, 'limit', Consts::DEFAULT_PER_PAGE));
                }
            );
    }

    public function getSpareInfo($params)
    {
        return Spare::leftJoin('bins', 'bins.spare_id', 'spares.id')
            ->leftJoin('shelfs', 'shelfs.id', 'bins.shelf_id')
            ->when(!empty($params['item_pn']), function ($query) use ($params) {
                $query->where('spares.part_no', $params['item_pn']);
            })
            ->select(
                'spares.name as item_name',
                DB::raw('CONCAT(shelfs.name," - ",bins.row, " - ",bins.bin) as item_position'),
            )
            ->get();
    }

    public function issueSpares($params)
    {
        $dryRun = Arr::get($params, 'dry_run', 0);
        $spares = Arr::get($params, 'spares', []);

        $result = [];
        $sparesInBin = [];
        $totalTakingTransaction = 0;
        $takerId = $currentClusterId = null;
        $mergeOptions = [];
        $typeTransaction = Consts::TAKING_TRANSACTION_TYPE_ISSUE;

        foreach ($spares as $value) {
            $value['issuer_id'] = Auth::id();
            $quantity = Arr::get($value, 'quantity');
            $spareId = Arr::get($value, 'spare_id');
            $clusterId = Arr::get($value, 'cluster_id');
            if ($quantity <= 0 || !$spareId || !$clusterId) {
                Log::error('[issueSpares] SKIP wrong item:', $value);
                continue; // Item wrong
            }

            if (empty($sparesInBin[$clusterId])) {
                $sparesInBin[$clusterId] = [];
            }

            if (empty($sparesInBin[$clusterId][$spareId])) {
                $sparesInBin[$clusterId][$spareId] = $value;
            } else {
                $sparesInBin[$clusterId][$spareId]['quantity'] += $quantity;
            }

            $totalTakingTransaction += $quantity;
            if (!$takerId) {
                $takerId = Arr::get($value, 'taker_id');
            }
            if (!$currentClusterId) {
                $currentClusterId = $clusterId;
            }
        }

        $takerId = Arr::get($spares, '0.taker_id');
        // Update total_quantity => When dry_run = 1
        $dryRun && $this->updateTotalQtyInTransaction($takerId, $typeTransaction, count($spares));

        $excludeBinIds = $this->adminService->getNotWorkingReturnSpares()->pluck('bin_id')->toArray();
        //        $excludeBinIds = $this->adminService->getNotWorkingSpareIds();
        $spareNoBin = $spareIssued = [];

        foreach ($spares as $value) {
            $spareId = Arr::get($value, 'spare_id');
            $quantity = Arr::get($value, 'quantity');
            $clusterId = Arr::get($value, 'cluster_id');
            $userId = Arr::get($value, 'taker_id');
            $binId = Arr::get($value, 'bin_id');
            $binQ = $this->selectBinsBySpareId([
                'bin_id' => $binId,
                'spare_id' => $spareId,
                'quantity' => $quantity,
                'cluster_id' => $clusterId,
                'exclude_ids' => $excludeBinIds,
                'ignore_empty' => true,
            ]);
            if (!$binQ->count()) {
                $binQ = $this->selectBinsBySpareId([
                    'spare_id' => $spareId,
                    'quantity' => $quantity,
                    'cluster_id' => $clusterId,
                    'exclude_ids' => $excludeBinIds,
                    'ignore_empty' => true,
                ]);
            }
            $binsUpdate = clone $binQ;
            $bins = $binQ->get();

            $dryRun || $binsUpdate->update(['quantity' => 0, 'quantity_oh' => 0]); // item quantity on bin is max = 1

            $key = $clusterId . '-' . $spareId;
            if (!isset($result[$key])) {
                $result[$key] = [
                    'cluster_id' => $clusterId,
                    'spare_id' => $spareId,
                    'remained' => 0,
                    'bins' => []
                ];
            }

            $torqueWrenchAreaId = Arr::get($value, 'torque_wrench_area_id');
            if ($torqueWrenchAreaId) {
                $mergeOptions[$spareId]['torque_wrench_area_id'] = $torqueWrenchAreaId;
            }
            $jobCardId = Arr::get($value, 'job_card_id');
            if ($jobCardId) {
                $mergeOptions[$spareId]['job_card_id'] = $jobCardId;
            }

            if ($bins->count()) {
                foreach ($bins as $bin) {
                    $issueCard = new IssueCard;
                    $trackingMo = new TrackingMo;
                    $value['issuer_id'] = Auth::id();
                    $value['quantity'] = 1; // quantity of spare per bin always 1
                    $value['bin_id'] = $bin->id;
                    $dryRun || $this->saveData($issueCard, $value);
                    $dryRun || $this->saveData($trackingMo, array_merge($value, ['issue_card_id' => $issueCard->id]));

                    $dryRun || $this->removeRemainBinsInTransaction($userId, $typeTransaction, $bin->id);

                    $result[$key]['bins'][] = array_merge(
                        ['torque_wrench_area_id' => $torqueWrenchAreaId],
                        $bin->toArray()
                    );
                    $spareIssued[] = $bin;
                }
            } else {
                array_push($spareNoBin, $spareId);

                $result[$key]['remained'] = $result[$key]['remained'] + 1;
            }

            $dryRun || $this->updateRemainQtyInTransaction($userId, $typeTransaction, $bins->count());
        }

        $suggestBinForSpare = (array)$this->adminService->suggestBinForSpare($spareNoBin);
        $transactionBinsRemain = $this->mergeSuggestBinForSpare(
            array_merge($suggestBinForSpare, $spareIssued),
            $mergeOptions
        );
        // Update bins_remain => When dry_run = 1
        $dryRun && $this->updateBinsRemainInTransaction($takerId, $typeTransaction, $transactionBinsRemain);

        $suggestedBinFromTransaction = $this->getSuggestedBins($takerId, $typeTransaction, $currentClusterId);
        return [
            'data' => array_values($result),
            'suggested_bin' => count($suggestedBinFromTransaction) > 0 ? head($suggestedBinFromTransaction) : null,
        ];
    }

    private function mergeSuggestBinForSpare($suggestBinForSpare, $options)
    {
        $result = [];
        foreach ($suggestBinForSpare as $item) {
            if (isset($options[$item->spare_id])) {
                $result[] = array_merge($item->toArray(), $options[$item->spare_id]);
            } else {
                $result[] = $item->toArray();
            }
        }

        return $result;
    }

    private function selectBinsBySpareId($params)
    {
        $binId = Arr::get($params, 'bin_id', 0);
        $spareId = Arr::get($params, 'spare_id');
        $quantity = Arr::get($params, 'quantity', 0);
        $clusterId = Arr::get($params, 'cluster_id');
        $excludeIds = Arr::get($params, 'exclude_ids');
        $ignoreEmpty = Arr::get($params, 'ignore_empty', false);
        $onlyEmpty = Arr::get($params, 'only_empty', false);

        return Bin::where('bins.spare_id', $spareId)
            ->leftJoin('spares', 'spares.id', 'bins.spare_id')
            ->leftJoin('shelfs', 'shelfs.id', 'bins.shelf_id')
            ->leftJoin('clusters', 'clusters.id', 'bins.cluster_id')
            ->leftJoin('bin_configures', 'bin_configures.bin_id', 'bins.id')
            ->when(!empty($binId), function ($query) use ($binId) {
                $query->where('bins.id', $binId);
            })
            ->when($ignoreEmpty, function ($query) {
                $query->where('quantity_oh', '>', 0);
            })
            ->when($onlyEmpty, function ($query) {
                $query->where('quantity_oh', 0);
            })
            ->when(!empty($excludeIds), function ($query) use ($excludeIds) {
                $query->whereNotIn('bins.id', $excludeIds);
            })
            ->when($clusterId, function ($query) use ($clusterId) {
                $query->where('clusters.id', $clusterId);
            })
            ->select(
                'bins.*',
                DB::raw('CONCAT(clusters.name," - ",shelfs.name," - ",bins.row, " - ",bins.bin) as location'),
                'spares.name as spare_name',
                'clusters.name as cluster_name',
                'shelfs.name as shelf_name',
                'bins.bin as bin_name',
                'bin_configures.serial_no as serial_no',
            )
            ->orderBy('bins.shelf_id', 'asc')
            ->orderBy('bins.row', 'asc')
            ->orderBy('bins.bin', 'asc')
            ->take($quantity);
    }

    private function selectEmptyBins($clusterId, $quantity)
    {
        return Bin::leftJoin('shelfs', 'shelfs.id', 'bins.shelf_id')
            ->leftJoin('clusters', 'clusters.id', 'bins.cluster_id')
            ->where('clusters.id', $clusterId)
            ->where('bins.is_drawer', '<>', 1)
            ->whereNull('bins.spare_id')
            ->select(
                'bins.*',
                DB::raw('CONCAT(clusters.name," - ",shelfs.name," - ",bins.row, " - ",bins.bin) as location'),
                'clusters.name as cluster_name',
                'bins.bin as bin_name',
            )
            ->orderBy('bins.shelf_id', 'asc')
            ->orderBy('bins.row', 'asc')
            ->orderBy('bins.bin', 'asc')
            ->take($quantity);
    }


    public function takingTransaction($params)
    {
        $clusterId = Arr::get($params, 'cluster_id');
        $totalQty = Arr::get($params, 'total_qty', 0);
        $transaction = $this->getCurrentTakingTransaction($params);
        if (!$transaction) {
            //            if ($totalQty <= 0) {
            //                return [];
            //            }

            $transaction = new TakingTransaction;
            $params['remain_qty'] = 0;
            $params['total_qty'] = 0;
            $transaction = $this->saveData($transaction, $params);
        }

        $transaction->remain_bins = json_decode($transaction->remain_bins, true);
        return [
            'transaction' => $transaction,
            //            'bins' => $this->suggestBinsFromTakingTransaction($transaction, $clusterId),
            //            'bins' => $this->suggestBinsByClusterId($clusterId, $transaction->remain_qty),
        ];
    }

    private function getCurrentTakingTransaction($params)
    {
        return TakingTransaction::where('user_id', Arr::get($params, 'user_id'))
            ->where('type', Arr::get($params, 'type'))
            ->where('status', Consts::TAKING_TRANSACTION_STATUS_OPENED)
            ->orderBy('updated_at', 'DESC')
            ->first();
    }

    private function updateRemainQtyInTransaction($userId, $type, $quantity)
    {
        $transaction = TakingTransaction::where('user_id', $userId)
            ->where('type', $type)
            ->where('status', Consts::TAKING_TRANSACTION_STATUS_OPENED)
            ->first();

        if ($transaction) {
            $remainQty = $transaction->remain_qty ?: $transaction->total_qty;
            $remainQty -= $quantity;
            $transaction->remain_qty = $remainQty > 0 ? $remainQty : 0;
            if ($transaction->remain_qty == 0) {
                $transaction->status = Consts::TAKING_TRANSACTION_STATUS_COMPLETED;
                $transaction->remain_bins = json_encode([]);
            }

            $transaction->save();
        }
    }

    private function updateBinsRemainInTransaction($userId, $type, array $remainBins = [])
    {
        $transaction = TakingTransaction::query()
            ->where('user_id', $userId)
            ->where('type', $type)
            ->where('status', Consts::TAKING_TRANSACTION_STATUS_OPENED)
            ->first();

        if ($transaction) {
            $transaction->remain_bins = json_encode($remainBins);
            $transaction->save();
        }
    }

    private function getTransaction($userId, $type)
    {
        return TakingTransaction::query()
            ->where('user_id', $userId)
            ->where('type', $type)
            ->where('status', Consts::TAKING_TRANSACTION_STATUS_OPENED)
            ->first();
    }

    private function updateTotalQtyInTransaction($userId, $type, $quantity)
    {
        $transaction = TakingTransaction::query()
            ->where('user_id', $userId)
            ->where('type', $type)
            ->where('status', Consts::TAKING_TRANSACTION_STATUS_OPENED)
            ->first();
        if ($transaction) {
            $transaction->total_qty = $quantity;
            $transaction->remain_qty = $quantity;
            $transaction->save();
        }

        return $transaction;
    }

    private function removeRemainBinsInTransaction($userId, $type, $binId)
    {
        $transaction = TakingTransaction::query()
            ->where('user_id', $userId)
            ->where('type', $type)
            ->where('status', Consts::TAKING_TRANSACTION_STATUS_OPENED)
            ->first();
        if ($transaction) {
            $remainBins = (array)json_decode($transaction->remain_bins, true);
            foreach ($remainBins as $index => $remainBin) {
                if ($remainBin['id'] == $binId) {
                    unset($remainBins[$index]);

                    // Unlock bin
                    $this->forceUnlockBinProcessing($binId);
                }
            }

            // Update remain bins
            $transaction->remain_bins = json_encode(array_values($remainBins));
            $transaction->save();
        }
    }

    private function suggestBinsFromTakingTransaction($takingTransaction, $clusterId)
    {
        return $takingTransaction->remain_bins;
        //        $suggestBins = [];
        //        $nextBin = null;
        //        if($clusterId) {
        //            $remainBins = json_decode($takingTransaction->remain_bins);
        //            foreach ($remainBins as $bin) {
        //                if($bin->cluster_id == $clusterId) {
        //                    $suggestBins[] = $bin;
        //                } elseif(!$nextBin) {
        //                    $nextBin = $bin;
        //                }
        //            }
        //        }
        //
        //        if($nextBin) {
        //            $suggestBins[] = $nextBin;
        //        }
        //        return $suggestBins;
    }

    private function suggestBinsByClusterId($clusterId, $quantity)
    {
        return Bin::leftJoin('shelfs', 'shelfs.id', 'bins.shelf_id')
            ->leftJoin('clusters', 'clusters.id', 'shelfs.cluster_id')
            ->where('clusters.id', $clusterId)
            ->select(
                'bins.*',
                DB::raw('CONCAT(clusters.name," - ",shelfs.name," - ",bins.row, " - ",bins.bin) as location'),
                'clusters.id as cluster_id',
                'clusters.name as cluster_name',
                'bins.bin as bin_name',
            )
            ->orderBy('bins.shelf_id', 'asc')
            ->orderBy('bins.row', 'asc')
            ->orderBy('bins.bin', 'asc')
            ->take($quantity)
            ->get();
    }

    public function replenishManualAutoBin($params)
    {
        $typeTransaction = Consts::TAKING_TRANSACTION_TYPE_REPLENISH;
        // Dry_run = 1 => Call to get date open bins => Do not update data of bin
        $dryRun = Arr::get($params, 'dry_run', 0);
        // If null => Replenishment did not create
        $replenishId = Arr::get($params, 'replenish_id', null);
        // List spares
        $spares = Arr::get($params, 'spares', []);
        //
        $userId = Arr::get($params, 'user_id');

        $result = $spareNoBin = $spareIssued = $mergeOptions = [];
        $currentClusterId = null;

        // Create replenishment
        $replenishment = null;
        if (!$replenishId) {
            $replenishment = Replenishment::create(
                [
                    'uuid' => Utils::currentMilliseconds(),
                    'type' => Consts::REPLENISHMENT_TYPE_MANUAL,
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ]
            );
        }

        // Update total_quantity => When dry_run = 1
        $dryRun && $this->updateTotalQtyInTransaction($userId, $typeTransaction, count($spares));

        foreach ($spares as $value) {
            $spareId = $value['spare_id'];
            $clusterId = Arr::get($value, 'cluster_id');
            $quantity = Arr::get($value, 'quantity'); // Always value = 1
            $binQ = $this->selectBinsBySpareId([
                'spare_id' => $spareId,
                'quantity' => $quantity, // Get number of the record
                'cluster_id' => $clusterId,
                'only_empty' => true,
            ]);
            if (!$currentClusterId) {
                $currentClusterId = $clusterId;
            }

            $binsUpdate = clone $binQ;
            $bins = $binQ->get();

            $dryRun || $binsUpdate->update(['quantity' => 1, 'quantity_oh' => 1]); // quantity of spare per bin always 1

            //            if ($bins->count() < $value['quantity']) {
            //                $remainQty = $value['quantity'] - $selectedTotal;
            //                $emptyBinQ = $this->selectEmptyBins($clusterId, $remainQty);
            //                $binsUpdate = clone $emptyBinQ;
            //                $emptyBins = $emptyBinQ->get();
            //                $defaultBinInfo = ['spare_id' => $spareId, 'min' => 1, 'max' => 1, 'critical' => 0];
            //                // quantity of spare per bin always 1
            //                $dryRun || $binsUpdate->update(array_merge($defaultBinInfo, [
            //                    'quantity'    => 1,
            //                    'quantity_oh' => 1,
            //                    'status'      => Consts::BIN_STATUS_ASSIGNED,
            //                ]));
            //                $bins = $bins->merge($emptyBins);
            //            }

            $configures = Arr::get($value, 'configures', []);
            if ($configures) {
                $mergeOptions[$spareId]['configures'] = $configures;
            }
            if ($replenishId) {
                $mergeOptions[$spareId]['replenish_id'] = $replenishId;
            }
            if ($replenishment) {
                $mergeOptions[$spareId]['replenish_id'] = $replenishment->id;
            }
            $key = $clusterId . '-' . $spareId;
            if (!isset($result[$key])) {
                $result[$key] = [
                    'cluster_id' => $clusterId,
                    'spare_id' => $spareId,
                    'remained' => 0,
                    'bins' => []
                ];
            }
            if ($bins->count() > 0) {
                foreach ($bins as $bin) {
                    $dryRun || $this->removeRemainBinsInTransaction($userId, $typeTransaction, $bin->id);

                    $bin->bin_id = $bin->id;
                    $spareIssued[] = $bin;

                    $binData = [
                        'bin_id' => $bin->id,
                        'spare_id' => $spareId,
                        'quantity' => 1,
                        'bin_name' => $bin->bin_name,
                        'drawer_name' => $bin->drawer_name,
                        'cluster_id' => $bin->cluster_id,
                        'cluster_name' => $bin->cluster_name,
                        'shelf_id' => $bin->shelf_id,
                        'location' => $bin->location,
                        'spare_name' => $bin->spare_name,
                        'shelf_name' => $bin->shelf_name,
                        'serial_no' => $bin->serial_no,
                    ];
                    $result[$key]['bins'][] = $binData;
                    $resultReplenished[] = $binData;
                }

                $dryRun || $this->updateRemainQtyInTransaction(
                    $userId,
                    $typeTransaction,
                    $bins->count()
                );
            } else {
                array_push($spareNoBin, $spareId);

                $result[$key]['remained'] = $result[$key]['remained'] + 1;
            }
        }

        $suggestBinForSpare = (array)$this->adminService->suggestBinForSpare($spareNoBin, [
            'ignore_empty' => false,
            'can_replenishment' => true,
        ]);
        $transactionBinsRemain = $this->mergeSuggestBinForSpare(
            array_merge($suggestBinForSpare, $spareIssued),
            $mergeOptions
        );
        // Update bins_remain => When dry_run = 1
        $dryRun && $this->updateBinsRemainInTransaction($userId, $typeTransaction, $transactionBinsRemain);

        // Create data replenishment_spares => Only first time
        if ($replenishment) {
            foreach ($transactionBinsRemain as $suggestBin) {
                $dataReplenishmentSpare = [
                    'replenishment_id' => $replenishment->id,
                    'bin_id' => Arr::get($suggestBin, 'bin_id'),
                    'spare_id' => Arr::get($suggestBin, 'spare_id'),
                    'quantity' => 1,
                ];
                $replenishmentSpare = new ReplenishmentSpare;
                $replenishmentSpare = $this->saveData($replenishmentSpare, $dataReplenishmentSpare);

                $configures = Arr::get($suggestBin, 'configures', []);
                if (empty($configures)) {
                    $configures = BinConfigure::where('bin_id', $replenishmentSpare->bin_id)->get()->toArray();
                }
                if (!empty($configures)) {
                    $this->saveReplenishmentSpareConfigures($configures, $replenishmentSpare);

                    $this->saveBinConfigures($configures, $replenishmentSpare);
                }
            }
        }

        $suggestedBinFromTransaction = $this->getSuggestedBins($userId, $typeTransaction, $currentClusterId);
        return [
            'data' => array_values($result),
            'suggested_bin' => count($suggestedBinFromTransaction) > 0 ? head($suggestedBinFromTransaction) : null,
            'replenish_id' => $replenishment ? $replenishment->id : $replenishId,
        ];
    }

    public function returnToStoreAutoBin($params)
    {
        $spares = Arr::get($params, 'spares', []);
        $userId = Arr::get($params, 'user_id');
        $dryRun = Arr::get($params, 'dry_run', 0);
        $data = [];
        $returnedSpares = [];
        $suggestedBins = [];
        $returnBins = collect([]);
        $typeTransaction = Consts::TAKING_TRANSACTION_TYPE_RETURN;
        $currentClusterId = null;
        $sparesNoMatchingBin = [];

        // Update total_quantity => When dry_run = 1
        $dryRun && $this->updateTotalQtyInTransaction($userId, $typeTransaction, count($spares));

        foreach ($spares as $item) {
            $bins = collect([]);
            $quantity = $item['quantity'];
            if ($quantity <= 0) {
                continue;
            }

            $issueCard = IssueCard::find(Arr::get($item, 'issue_card_id', 0));
            $spare = Spare::find($item['spare_id']);
            $oldBin = null;
            if ($issueCard && $issueCard->bin_id) {
                $oldBin = Bin::where('bins.id', $issueCard->bin_id)->where('bins.quantity_oh', 0)
                    //                    ->where('is_failed', Consts::BIN_IS_FAILED_NO)
                    ->leftJoin('bin_configures', 'bin_configures.bin_id', 'bins.id')
                    ->leftJoin('shelfs', 'shelfs.id', 'bins.shelf_id')
                    ->leftJoin('clusters', 'clusters.id', 'bins.cluster_id')
                    ->select(
                        'bins.*',
                        DB::raw('CONCAT(clusters.name," - ",shelfs.name," - ",bins.row, " - ",bins.bin) as location'),
                        'clusters.name as cluster_name',
                        'shelfs.name as shelf_name',
                        'bins.bin as bin_name',
                        'bin_configures.serial_no as serial_no'
                    )->first();
                // Spare can not return to any bins
                if ($oldBin && $oldBin->is_failed) {
                    $oldBin->spare_name = $spare->name;
                    $sparesNoMatchingBin[] = $oldBin;

                    continue;
                }

                if ($oldBin && $oldBin->cluster_id !== $item['cluster_id']) {
                    // show the way to old bin
                    $suggestedBin = $this->adminService->getSparesAssignedBin(
                        [
                            'binIds' => [$oldBin->id],
                            'limit' => 1,
                        ]
                    )->first();
                    if ($suggestedBin) {
                        $suggestedBin->issue_card_id = $item['issue_card_id'];
                        $suggestedBin->state = $item['state'];
                        $suggestedBins[] = $suggestedBin;
                    }

                    continue;
                }

                $quantity = $oldBin ? $quantity - 1 : $quantity;
                if ($oldBin) {
                    $dryRun || $oldBin->update(
                        ['quantity' => 1, 'quantity_oh' => 1]
                    ); // quantity of spare per bin always 1
                    $oldBin->spare_name = $spare ? $spare->name : '';
                    $oldBin->issue_card_id = $item['issue_card_id'];
                    $oldBin->state = $item['state'];
                    $bins->add($oldBin);
                    $returnBins->add($oldBin);

                    // Remove bin in remain bins
                    $dryRun || $this->removeRemainBinsInTransaction($userId, $typeTransaction, $oldBin->id);
                }
            }

            // Case return data from return_spares with type = handover
            //            if ($quantity > 0) {
            //                $binQ = $this->selectBinsBySpareId([
            //                    'spare_id'   => $item['spare_id'],
            //                    'quantity'   => $quantity,
            //                    'cluster_id' => $item['cluster_id'],
            //                    'only_empty' => true,
            //                    'exclude_ids' => $issueCard ? [$issueCard->bin_id] : null,
            //                ]);
            //                $binsUpdate = clone $binQ;
            //                $bins = $binQ->get();
            //                $dryRun || $binsUpdate->update(['quantity' => 1, 'quantity_oh' => 1]); // quantity of spare per bin always 1
            //                $oldBin && $bins->prepend($oldBin);
            //            }

            foreach ($bins as $bin) {
                $return = new ReturnSpare;
                $dryRun || $this->saveData($return, [
                    'type' => Consts::RETURN_TO_STORE,
                    'bin_id' => $bin->id,
                    'spare_id' => $item['spare_id'],
                    'state' => $item['state'],
                    'quantity' => 1,
                    //                    'handover_id' => Auth::id(),
                    'handover_id' => $userId ?? Auth::id(),
                ]);

                $returnedSpares[] = array_merge($item, ['bin_id' => $bin->id]);

                $data[] = (object)[
                    'spare_id' => $item['spare_id'],
                    'bin' => $bin,
                ];
            }

            if (!$currentClusterId) {
                $currentClusterId = Arr::get($item, 'cluster_id');
            }
        }

        // Update bins_remain => When dry_run = 1
        $transactionBinsRemain = array_merge($suggestedBins, $returnBins->toArray());
        $dryRun && $this->updateBinsRemainInTransaction($userId, $typeTransaction, $transactionBinsRemain);

        $dryRun || $this->updateRemainQtyInTransaction($userId, $typeTransaction, $returnBins->count());

        $dryRun || ReturningSpareJob::dispatch(Auth::id(), $returnedSpares);

        $suggestedBinFromTransaction = $this->getSuggestedBins($userId, $typeTransaction, $currentClusterId);

        $result = [
            'success' => true,
            'data' => $data,
            'suggested_bin' => count($suggestedBinFromTransaction) > 0 ? head($suggestedBinFromTransaction) : null,
            'message' => '',
        ];

        // Case all spares can not return because bin is failed
        if (!$data && !$suggestedBinFromTransaction && count($sparesNoMatchingBin)) {
            $errors = [];
            foreach ($sparesNoMatchingBin as $bin) {
                $errors[] = "Item $bin->spare_name at $bin->location";
            }
            $result = array_merge($result, [
                'success' => false,
                'message' => implode(', ', $errors) . ' that are failed, please contact Administrator to support!',
            ]);
        }

        return $result;
    }

    private function getSuggestedBins($userId, $type, $clusterId)
    {
        // Get data suggest
        $transaction = $this->getTransaction($userId, $type);
        $suggestBins = $transaction ? (array)json_decode($transaction->remain_bins ?? null, true) : [];

        $result = [];
        foreach ($suggestBins as $suggestBin) {
            $suggestBinClusterId = Arr::get($suggestBin, 'cluster_id');
            if ($suggestBinClusterId != $clusterId) {
                $result[] = $suggestBin;
            }
        }

        return $result;
    }

    public function forceUnlockBinProcessing($binId)
    {
        $bin = Bin::find($binId);
        // If bin does not exist
        if ($bin) {
            // Update bin is not processing
            $bin->fill(
                [
                    'is_processing' => 0,
                    'process_time' => null,
                    'process_by' => null,
                ]
            )->save();
        }
    }

    public function removeSpareByBinReplenishAuto($replenishId, $binId)
    {
        ReplenishmentSpare::query()
            ->where('replenishment_id', $replenishId)
            ->where('bin_id', $binId)
            ->delete();

        return true;
    }

    public function reportCompartmentDamaged($params = [])
    {
        $transactionId = Arr::get($params, 'transaction_id');
        $binIds = Arr::get($params, 'bin_ids');
        $isRfid = Arr::get($params, 'is_rfid', 0);

        // Update bin is failed
        if (!$isRfid) {
            $this->updateBinIsFailed($binIds);
        }

        // Remove remain bins
        $transaction = TakingTransaction::query()
            ->where('id', $transactionId)
            ->where('status', Consts::TAKING_TRANSACTION_STATUS_OPENED)
            ->first();
        if ($transaction) {
            $userId = $transaction->user_id;
            $type = $transaction->type;
            $remainBins = (array)json_decode($transaction->remain_bins, true);
            $binIdsRemainBins = array_column($remainBins, 'id');

            foreach ($binIds as $binId) {
                if (in_array($binId, $binIdsRemainBins)) {
                    $this->removeRemainBinsInTransaction($userId, $type, $binId);
                }
            }

            $transactionAfterUpdate = TakingTransaction::find($transactionId);
            $remainBinsAfterUpdate = (array)json_decode($transactionAfterUpdate->remain_bins, true);
            $this->updateRemainQtyInTransaction($userId, $type, count($remainBins) - count($remainBinsAfterUpdate));
        }
    }

    private function updateBinIsFailed($binIds)
    {
        Bin::query()
            ->whereIn('id', $binIds)
            ->update(
                [
                    'is_failed' => 1
                ]
            );
    }

    private function removeBinsEmpty($data)
    {
        foreach ($data as $index => $item) {
            if (!Arr::get($item, 'bins')) {
                unset($data[$index]);
            }
        }

        return $data;
    }

    /**
     * @param $params
     * @return array
     */
    public function createWeighingTransaction($params)
    {
        // Update all transaction type is weighing-system and open => completed
        TakingTransaction::query()
            ->where('type', Consts::TAKING_TRANSACTION_TYPE_WEIGHING_SYSTEM)
            ->where('status', Consts::TAKING_TRANSACTION_STATUS_OPENED)
            ->update(
                [
                    'status' => Consts::TAKING_TRANSACTION_STATUS_COMPLETED
                ]
            );

        $userId = Arr::get($params, 'user_id');
        // Create new transaction
        $transaction = new TakingTransaction();
        $transaction->fill(
            [
                'type' => Consts::TAKING_TRANSACTION_TYPE_WEIGHING_SYSTEM,
                'status' => Consts::TAKING_TRANSACTION_STATUS_OPENED,
                'remain_qty' => 0,
                'total_qty' => 0,
                'user_id' => $userId,
            ]
        )->save();

        $this->createWeighingHistory($userId);

        return [
            'transaction' => $transaction,
        ];
    }

    private function createWeighingHistory($userId)
    {
        $user = User::query()->where('id', $userId)->first();

        // Update all transaction history is not picking
        WeighingHistory::query()
            ->where('is_picking', Consts::TRUE)
            ->update(
                [
                    'is_picking' => Consts::FALSE
                ]
            );

        if ($user) {
            $weighingHistory = new WeighingHistory();
            $weighingHistory->fill(
                [
                    'user_id' => $userId,
                    'email' => $user->email,
                    'name' => $user->name,
                    'card_id' => $user->card_id,
                    'employee_id' => $user->employee_id,
                    'role' => $user->role,
                    'dept' => $user->dept,
                    'is_picking' => Consts::TRUE,
                    'transactions' => [],
                ]
            )->save();
        }
    }

    public function sendTnxReportNotification()
    {
        $emails = $this->settingService->getEmailReceivers(Consts::RECEIVER_EMAIL_TYPE_MAINTENANCE)->all();
        $range = $this->settingService->getRangeGetReportTnx();

        $issuedDate = ['start' => Arr::get($range, 'start'), 'end' => Arr::get($range, 'end')];
        $params = [
            'issued_date' => json_encode($issuedDate),
            'emails' => $emails,
        ];

        $this->sendSparesReportByTnx($params);

        return true;
    }

    public function reportSparesReturnedAndIssued(array $params)
    {
        $searchKey = strtolower(Arr::get($params, 'search_key'));
        $sort = Arr::get($params, 'sort', 'created_at');
        $sortType = Arr::get($params, 'sort_type', 1);

        switch ($searchKey) {
            case 'issue':
                $params['search_key'] = null;
                $queryBuilder = $this->getQuerySparesIssued($params);
                break;
            case 'return':
                $params['search_key'] = null;
                $queryBuilder = $this->getQuerySparesReturned($params);
                break;
            default:
                $queryBuilder = $this->getQuerySparesReturned($params)->union($this->getQuerySparesIssued($params));
                break;
        }

        return $queryBuilder
            ->when($sort, function ($query) use ($sort, $sortType) {
                $sortType = $sortType == -1 ? 'desc' : 'asc';
                $sort = in_array($sort, ['user_id', 'user_name', 'user_card_id', 'state', 'location', 'spare_name', 'operation', 'created_at', 'spare_id', 'spare_part_no', 'spare_material_no']) ? $sort : 'created_at';
                $query->orderBy($sort, $sortType);
            })
            ->paginate();
    }

    private function getQuerySparesReturned(array $params)
    {
        $searchKey = Arr::get($params, 'search_key');
        $status = Arr::get($params, 'status');
        return ReturnSpare::query()
            ->join('bins', 'bins.id', 'return_spares.bin_id')
            ->join('spares', 'spares.id', 'return_spares.spare_id')
            ->join('shelfs', 'shelfs.id', 'bins.shelf_id')
            ->leftJoin('users', 'users.id', 'return_spares.handover_id')
            ->leftJoin('clusters', 'clusters.id', 'bins.cluster_id')
            ->select(
                [
                    'return_spares.id as id',
                    'users.id as user_id',
                    'users.name as user_name',
                    'users.card_id as user_card_id',
                    'return_spares.state',
                    DB::raw("CONCAT(clusters.name, '-', shelfs.name, '-',bins.bin, '-',bins.row) as location"),
                    'spares.name as spare_name',
                    DB::raw('"Return" as operation'),
                    'return_spares.created_at',
                    'spares.id as spare_id',
                    'spares.part_no as spare_part_no',
                    'spares.material_no as spare_material_no',
                    DB::raw('"Returned" as status'),
                ]
            )
            ->when($searchKey, function ($query) use ($searchKey) {
                $searchKey = Utils::escapeLike($searchKey);
                $query->where(function ($subQuery) use ($searchKey) {
                    $subQuery->where('spares.name', 'LIKE', "%{$searchKey}%")
                        ->orWhere('spares.part_no', 'LIKE', "%{$searchKey}%")
                        ->orWhere('spares.material_no', 'LIKE', "%{$searchKey}%")
                        ->orWhere('users.id', 'LIKE', "%{$searchKey}%")
                        ->orWhere('users.name', 'LIKE', "%{$searchKey}%")
                        ->orWhere('users.card_id', 'LIKE', "%{$searchKey}%");
                });
            })
            ->when($status, function ($query) use ($status) {
                $status = strtolower(Utils::escapeLike($status));
                // Do not select any records
                if ($status == 'pending') {
                    $query->where('return_spares.id', '<', 0);
                }
            });
    }

    private function getQuerySparesIssued(array $params)
    {
        $searchKey = Arr::get($params, 'search_key');
        $status = Arr::get($params, 'status');

        return IssueCard::join('bins', 'bins.id', 'issue_cards.bin_id')
            ->join('shelfs', 'shelfs.id', 'bins.shelf_id')
            ->join('spares', 'spares.id', 'bins.spare_id')
            ->leftJoin('clusters', 'clusters.id', 'bins.cluster_id')
            ->leftJoin('users', 'users.id', 'issue_cards.issuer_id')
            ->when($searchKey, function ($query) use ($searchKey) {
                $searchKey = Utils::escapeLike($searchKey);
                $query->where(function ($subQuery) use ($searchKey) {
                    $subQuery->where('spares.name', 'LIKE', "%{$searchKey}%")
                        ->orWhere('spares.part_no', 'LIKE', "%{$searchKey}%")
                        ->orWhere('spares.material_no', 'LIKE', "%{$searchKey}%")
                        ->orWhere('users.id', 'LIKE', "%{$searchKey}%")
                        ->orWhere('users.name', 'LIKE', "%{$searchKey}%")
                        ->orWhere('users.card_id', 'LIKE', "%{$searchKey}%");
                });
            })
            ->when($status, function ($query) use ($status) {
                $status = strtolower(Utils::escapeLike($status));
                if ($status == 'pending') {
                    $query->whereColumn('issue_cards.quantity', '!=', 'issue_cards.returned_quantity')
                        ->orWhere(function ($subQuery) {
                            $subQuery->whereNull('issue_cards.quantity')
                                ->whereNotNull('issue_cards.returned_quantity');
                        })
                        ->orWhere(function ($subQuery) {
                            $subQuery->whereNull('issue_cards.returned_quantity')
                                ->whereNotNull('issue_cards.quantity');
                        });
                }
                if ($status == 'returned') {
                    $query->whereColumn('issue_cards.quantity', 'issue_cards.returned_quantity');
                }
            })
            ->select(
                [
                    'issue_cards.id as id',
                    'users.id as user_id',
                    'users.name as user_name',
                    'users.card_id as user_card_id',
                    DB::raw('"' . Consts::RETURN_SPARE_STATE_WORKING . '" as state'),
                    DB::raw("CONCAT(clusters.name, '-', shelfs.name, '-',bins.bin, '-',bins.row) as location"),
                    'spares.name as spare_name',
                    DB::raw('"Issue" as operation'),
                    'issue_cards.created_at',
                    'spares.id as spare_id',
                    'spares.part_no as spare_part_no',
                    'spares.material_no as spare_material_no',
                    DB::raw('IF(issue_cards.quantity = issue_cards.returned_quantity, "Returned", "Pending") AS status'),
                ]
            );
    }
    public function createTransaction($requests)
    {
        $taking_transactions = [];
        $requestss = $requests['data'];
        foreach ($requestss as $request) {
            if (isset($request['id']) && !empty($request['id'])) {
                $taking_transaction = TakingTransaction::find($request['id']);
                if (!empty($taking_transaction) && isset($taking_transaction->id)) {
                    $cabinet_id = isset($request['locations'][0]['cabinet']['id']) ? $request['locations'][0]['cabinet']['id'] : null;
                    $bin_id = isset($request['locations'][0]['bin']['id']) ? $request['locations'][0]['bin']['id'] : null;
                    $bin = Bin::find($bin_id);
                    $cluster = Cluster::find($bin['cluster_id']);
                    $shelf = Shelf::find($cabinet_id);
                    $taking_transaction->name = $request['name'];
                    $taking_transaction->status = $request['status'];
                    $taking_transaction->total_qty = 0;
                    $taking_transaction->remain_qty = 0;
                    $taking_transaction->request_qty = $request['request_qty'];
                    $taking_transaction->user_id = $request['user']['id'];
                    $taking_transaction->type = $request['type'];
                    $taking_transaction->cabinet_id = $request['locations'][0]['cabinet']['id'];
                    $taking_transaction->cabinet_name = isset($shelf['name']) ? $shelf['name'] : null;
                    $taking_transaction->cluster_name = isset($cluster['name']) ? $cluster['name'] : null;
                    $taking_transaction->bin_name = isset($bin['bin']) ? $bin['bin'] : null;
                    $taking_transaction->bin_id = $request['locations'][0]['bin']['id'];
                    $taking_transaction->save();
                    $spareIds = [];

                    foreach ($request['locations'][0]['spares'] as $item => $value) {
                        $spareId = $value['id'];
                        $listWO = isset($value['listWO'][0]) ? $value['listWO'][0] : null;
                        $jobCard = [];
                        $vehicleC = [];
                        $area = [];
                        if (isset($value['listWO']) && !empty($value['listWO'])) {
                            $jobCard = JobCard::find($value['listWO'][0]['wo_id']);
                            $vehicleC = Vehicle::find($value['listWO'][0]['vehicle_id']);
                            $area = TorqueWrenchArea::find($value['listWO'][0]['area_id']);
                        }
                        $spareIds[] = [
                            'spare_id' => $spareId,
                            'listWO' => json_encode($listWO),
                            'job_card_id' => !empty($value['listWO'][0]['wo_id']) ? $value['listWO'][0]['wo_id'] : null,
                            'job_name' => !empty($jobCard['wo']) ? $jobCard['wo'] : null,
                            'vehicle_id' =>  !empty($value['listWO'][0]['vehicle_id']) ? $value['listWO'][0]['vehicle_id'] : null,
                            'vehicle_num' => !empty($vehicleC['vehicle_num']) ? $vehicleC['vehicle_num'] : null,
                            'area_id' => !empty($value['listWO'][0]['area_id']) ? $value['listWO'][0]['area_id'] : null,
                            'area_name' => !empty($area['area']) ? $area['area'] : null,
                            'platform' => !empty($value['listWO'][0]['platform']) ? $value['listWO'][0]['platform'] : null,
                            'request_qty' => $value['quantity'],
                        ];
                        if ($request['type'] == 'issue') {
                            $bin_sparess = BinSpare::where('bin_id', $request['locations'][0]['bin']['id'])->where('spare_id', $value['id'])->first();
                            if ($bin_sparess->quantity < $value['quantity']) {
                                if (count($request['locations'][0]['spares']) > 1) {
                                    throw new Exception("Please quantity issue < quantity spare");
                                    return;
                                } else if (count($request['locations'][0]['spares']) == 1) {
                                    $taking_transaction->delete();
                                    throw new Exception("Please quantity issue < quantity spare");
                                }
                            } else {
                                $type = ($value['type'] == Consts::SPARE_TYPE_EUC) ? Consts::SPARE_TYPE_EUC : '';
                                switch ($type) {
                                    case Consts::SPARE_TYPE_EUC:
                                        $issueCard = IssueCard::create([
                                            'job_card_id' =>  !empty($value['listWO'][0]['wo_id']) ? $value['listWO'][0]['wo_id'] : null,
                                            'bin_id' => null,
                                            'spare_id' => $value['id'],
                                            'quantity' => $value['quantity'],
                                            'torque_wrench_area_id' => !empty($value['listWO'][0]['area_id']) ? $value['listWO'][0]['area_id'] : null,
                                            'issuer_id' => Auth::id(),
                                            'taker_id' =>  Auth::id(),
                                            'euc_box_id' => null,
                                        ]);
                                        $trackingMo = TrackingMo::create(
                                            [
                                                'job_card_id' => !empty($value['listWO'][0]['wo_id']) ? $value['listWO'][0]['wo_id'] : null,
                                                'issue_card_id' => $issueCard->id,
                                                'bin_id' => null,
                                                'spare_id' => $value['id'],
                                                'quantity' => $value['quantity'],
                                                'issuer_id' => Auth::id(),
                                                'torque_wrench_area_id' => !empty($value['listWO'][0]['area_id']) ? $value['listWO'][0]['area_id'] : null,
                                                'taker_id' => Auth::id(),
                                            ]
                                        );
                                        break;
                                    default:
                                        $this->adminService->checkProcessingBinSpare(['user_id' => Auth::id(), 'bin_id' => $request['locations'][0]['bin']['id'], 'spare_id' => $value['id'], 'value' => 1]);
                                        $issueCard = IssueCard::create([
                                            'job_card_id' => !empty($value['listWO'][0]['wo_id']) ? $value['listWO'][0]['wo_id'] : null,
                                            'bin_id' => $request['locations'][0]['bin']['id'],
                                            'spare_id' => $value['id'],
                                            'quantity' => $value['quantity'],
                                            'torque_wrench_area_id' => !empty($value['listWO'][0]['area_id']) ? $value['listWO'][0]['area_id'] : null,
                                            'issuer_id' => Auth::id(),
                                            'taker_id' => Auth::id(),
                                            'euc_box_id' => null
                                        ]);

                                        $trackingMo = TrackingMo::create(
                                            [
                                                'job_card_id' => !empty($value['listWO'][0]['wo_id']) ? $value['listWO'][0]['wo_id'] : null,
                                                'issue_card_id' => $issueCard->id,
                                                'bin_id' =>  $request['locations'][0]['bin']['id'],
                                                'spare_id' => $value['id'],
                                                'quantity' => $value['quantity'],
                                                'torque_wrench_area_id' =>  !empty($value['listWO'][0]['area_id']) ? $value['listWO'][0]['area_id'] : null,
                                                'issuer_id' => Auth::id(),
                                                'taker_id' => Auth::id(),
                                                'euc_box_id' => null,
                                            ]
                                        );
                                        $dd = $this->updateQuantityInBinSpare($issueCard->bin_id, $issueCard->spare_id, -$issueCard->quantity);
                                        break;
                                }
                            }
                        } else if ($request['type'] == 'return') {
                            $return = ReturnSpare::create([
                                'bin_id' => $request['locations'][0]['bin']['id'],
                                'spare_id' => $value['id'],
                                'quantity' => $value['quantity'],
                                'state' => $value['status'],
                                'type' => $value['type'],
                                'quantity_returned_store' => $value['quantity'],
                                'write_off' => 0,
                            ]);
                            $binSpareCollection = BinSpare::where('bin_id', $return->bin_id)->where('spare_id', $return->spare_id)->first();
                            if (!$binSpareCollection) {
                                throw new Exception("Bin with bin id = {$return->bin_id} is invalid.");
                            }
                            $binSpareCollection->is_processing = 0;
                            $binSpareCollection->process_time = null;
                            $binSpareCollection->process_by = null;
                            $binSpareCollection->save();
                            $this->updateQuantityInBinSpare($return->bin_id, $return->spare_id, $return->quantity);
                            $this->updateRemainQtyInTransaction(Auth::id(), Consts::TAKING_TRANSACTION_TYPE_RETURN, $return->quantity);
                            $returnings = $this->getItemsHandover($return->bin_id, $return->spare_id, Auth::id());
                            if (!empty($returnings)) {
                                foreach ($returnings as $returns) {
                                    $inputQuantity = $value['quantity'];
                                    if (!$inputQuantity) {
                                        continue;
                                    }
                                    $remainQuantityInCard = BigNumber::new($returns->quantity)
                                        ->sub($returns->quantity_returned_store)
                                        ->toString();

                                    $returnedQuantity = BigNumber::new($returns->quantity_returned_store ?: 0)
                                        ->add($inputQuantity)
                                        ->toString();
                                    if (~BigNumber::new($inputQuantity)->comp($remainQuantityInCard)) {
                                        $returnedQuantity = $returns->quantity;
                                    }
                                    $returns->quantity_returned_store = $returnedQuantity;
                                    $returns->save();
                                }
                            }
                            $cards = $this->getIssueCards($return->bin_id, $return->spare_id);
                            if (!empty($cards)) {
                                foreach ($cards as $card) {
                                    $inputQuantity = $value['quantity'];
                                    if (!$inputQuantity) {
                                        continue;
                                    }
                                    $remainQuantityInCard = BigNumber::new($card->quantity)
                                        ->sub($card->returned_quantity)
                                        ->toString();

                                    $state = Consts::RETURNED_TYPE_PARTIAL;
                                    $returnedQuantity = BigNumber::new($card->returned_quantity ?: 0)
                                        ->add($inputQuantity)
                                        ->toString();
                                    if (~BigNumber::new($inputQuantity)->comp($remainQuantityInCard)) {
                                        $state = Consts::RETURNED_TYPE_ALL;
                                        $returnedQuantity = $card->quantity;
                                    }
                                    $card->returned = $state;
                                    $card->returned_quantity = $returnedQuantity;
                                    $card->save();
                                }
                            }
                            TrackingMo::where('bin_id', $return->bin_id)->where('spare_id', $return->spare_id)
                                ->delete();
                        }
                    }
                    foreach ($spareIds as $spare) {
                        $TransactionSpare = TransactionSpare::create([
                            'taking_transaction_id' => $taking_transaction->id,
                            'spare_id' => $spare['spare_id'],
                            'listWO' => !empty($spare['listWO']) ? $spare['listWO'] : null,
                            'job_card_id' =>  !empty($spare['job_card_id']) ? $spare['job_card_id'] : null,
                            'vehicle_id' => !empty($spare['vehicle_id']) ? $spare['vehicle_id'] : null,
                            'job_name' => !empty($spare['job_name']) ? $spare['job_name'] : null,
                            'vehicle_num' => !empty($spare['vehicle_num']) ? $spare['vehicle_num'] : null,
                            'area_id' => !empty($spare['area_id']) ? $spare['area_id'] : null,
                            'area_name' => !empty($spare['area_name']) ? $spare['area_name'] : null,
                            'platform' => !empty($spare['platform']) ? $spare['platform'] : null,
                            'request_qty' => !empty($spare['request_qty']) ? $spare['request_qty'] : 0,
                        ]);
                    }
                } else {
                    $cabinet_id = isset($request['locations'][0]['cabinet']['id']) ? $request['locations'][0]['cabinet']['id'] : null;
                    $bin_id = isset($request['locations'][0]['bin']['id']) ? $request['locations'][0]['bin']['id'] : null;
                    $bin = Bin::find($bin_id);
                    $cluster = Cluster::find($bin['cluster_id']);
                    $shelf = Shelf::find($cabinet_id);
                    $taking_transaction = TakingTransaction::with('spares')->create([
                        'name' => $request['name'],
                        'id' => $request['id'],
                        'status' => $request['status'],
                        'request_qty' => $request['request_qty'],
                        'total_qty' => 0,
                        'remain_qty' => 0,
                        'user_id' => $request['user']['id'],
                        'type' => $request['type'],
                        'cabinet_id' => $request['locations'][0]['cabinet']['id'],
                        'cabinet_name' => isset($shelf['name']) ? $shelf['name'] : null,
                        'cluster_name' => isset($cluster['name']) ? $cluster['name'] : null,
                        'bin_name' => isset($bin['bin']) ? $bin['bin'] : null,
                        'bin_id' => $request['locations'][0]['bin']['id'],
                    ]);
                    $spareIds = [];
                    foreach ($request['locations'][0]['spares'] as $item => $value) {
                        $spareId = $value['id'];
                        $listWO = isset($value['listWO'][0]) ? $value['listWO'][0] : null;
                        $jobCard = [];
                        $vehicleC = [];
                        $area = [];
                        if (isset($value['listWO']) && !empty($value['listWO'])) {
                            $jobCard = JobCard::find($value['listWO'][0]['wo_id']);
                            $vehicleC = Vehicle::find($value['listWO'][0]['vehicle_id']);
                            $area = TorqueWrenchArea::find($value['listWO'][0]['area_id']);
                        }
                        $spareIds[] = [
                            'spare_id' => $spareId,
                            'listWO' => json_encode($listWO),
                            'job_card_id' => !empty($value['listWO'][0]['wo_id']) ? $value['listWO'][0]['wo_id'] : null,
                            'job_name' => !empty($jobCard['wo']) ? $jobCard['wo'] : null,
                            'vehicle_id' =>  !empty($value['listWO'][0]['vehicle_id']) ? $value['listWO'][0]['vehicle_id'] : null,
                            'vehicle_num' => !empty($vehicleC['vehicle_num']) ? $vehicleC['vehicle_num'] : null,
                            'area_id' => !empty($value['listWO'][0]['area_id']) ? $value['listWO'][0]['area_id'] : null,
                            'area_name' => !empty($area['area']) ? $area['area'] : null,
                            'platform' => !empty($value['listWO'][0]['platform']) ? $value['listWO'][0]['platform'] : null,
                            'request_qty' => $value['quantity'],
                        ];
                        if ($request['type'] == 'issue') {
                            $bin_sparess = BinSpare::where('bin_id', $request['locations'][0]['bin']['id'])->where('spare_id', $value['id'])->first();
                            if(!$bin_sparess){
                                throw new Exception("Please bin or spare does not exist");
                                return;
                            }
                            if ($bin_sparess->quantity < $value['quantity']) {
                                if (count($request['locations'][0]['spares']) > 1) {
                                    throw new Exception("Please quantity issue < quantity spare");
                                    return;
                                } else if (count($request['locations'][0]['spares']) == 1) {
                                    $taking_transaction->delete();
                                    throw new Exception("Please quantity issue < quantity spare");
                                }
                            } else {
                                $type = ($value['type'] == Consts::SPARE_TYPE_EUC) ? Consts::SPARE_TYPE_EUC : '';
                                switch ($type) {
                                    case Consts::SPARE_TYPE_EUC:
                                        $issueCard = IssueCard::create([
                                            'job_card_id' =>  !empty($value['listWO'][0]['wo_id']) ? $value['listWO'][0]['wo_id'] : null,
                                            'bin_id' => null,
                                            'spare_id' => $value['id'],
                                            'quantity' => $value['quantity'],
                                            'torque_wrench_area_id' => !empty($value['listWO'][0]['area_id']) ? $value['listWO'][0]['area_id'] : null,
                                            'issuer_id' => Auth::id(),
                                            'taker_id' =>  Auth::id(),
                                            'euc_box_id' => null,
                                        ]);
                                        $trackingMo = TrackingMo::create(
                                            [
                                                'job_card_id' => !empty($value['listWO'][0]['wo_id']) ? $value['listWO'][0]['wo_id'] : null,
                                                'issue_card_id' => $issueCard->id,
                                                'bin_id' => null,
                                                'spare_id' => $value['id'],
                                                'quantity' => $value['quantity'],
                                                'issuer_id' => Auth::id(),
                                                'torque_wrench_area_id' => !empty($value['listWO'][0]['area_id']) ? $value['listWO'][0]['area_id'] : null,
                                                'taker_id' => Auth::id(),
                                            ]
                                        );
                                        break;
                                    default:
                                        $this->adminService->checkProcessingBinSpare(['user_id' => Auth::id(), 'bin_id' => $request['locations'][0]['bin']['id'], 'spare_id' => $value['id'], 'value' => 1]);
                                        $issueCard = IssueCard::create([
                                            'job_card_id' => !empty($value['listWO'][0]['wo_id']) ? $value['listWO'][0]['wo_id'] : null,
                                            'bin_id' => $request['locations'][0]['bin']['id'],
                                            'spare_id' => $value['id'],
                                            'quantity' => $value['quantity'],
                                            'torque_wrench_area_id' => !empty($value['listWO'][0]['area_id']) ? $value['listWO'][0]['area_id'] : null,
                                            'issuer_id' => Auth::id(),
                                            'taker_id' => Auth::id(),
                                            'euc_box_id' => null
                                        ]);

                                        $trackingMo = TrackingMo::create(
                                            [
                                                'job_card_id' => !empty($value['listWO'][0]['wo_id']) ? $value['listWO'][0]['wo_id'] : null,
                                                'issue_card_id' => $issueCard->id,
                                                'bin_id' =>  $request['locations'][0]['bin']['id'],
                                                'spare_id' => $value['id'],
                                                'quantity' => $value['quantity'],
                                                'torque_wrench_area_id' =>  !empty($value['listWO'][0]['area_id']) ? $value['listWO'][0]['area_id'] : null,
                                                'issuer_id' => Auth::id(),
                                                'taker_id' => Auth::id(),
                                                'euc_box_id' => null,
                                            ]
                                        );
                                        $dd = $this->updateQuantityInBinSpare($issueCard->bin_id, $issueCard->spare_id, -$issueCard->quantity);
                                        break;
                                }
                            }
                        } else if ($request['type'] == 'return') {
                            $return = ReturnSpare::create([
                                'bin_id' => $request['locations'][0]['bin']['id'],
                                'spare_id' => $value['id'],
                                'quantity' => $value['quantity'],
                                'state' => $value['status'],
                                'type' => $value['type'],
                                'quantity_returned_store' => $value['quantity'],
                                'write_off' => 0,
                            ]);
                            $binSpareCollection = BinSpare::where('bin_id', $return->bin_id)->where('spare_id', $return->spare_id)->first();
                            if (!$binSpareCollection) {
                                throw new Exception("Bin with bin id = {$return->bin_id} is invalid.");
                            }
                            $binSpareCollection->is_processing = 0;
                            $binSpareCollection->process_time = null;
                            $binSpareCollection->process_by = null;
                            $binSpareCollection->save();
                            $this->updateQuantityInBinSpare($return->bin_id, $return->spare_id, $return->quantity);
                            $this->updateRemainQtyInTransaction(Auth::id(), Consts::TAKING_TRANSACTION_TYPE_RETURN, $return->quantity);
                            $returnings = $this->getItemsHandover($return->bin_id, $return->spare_id, Auth::id());
                            if (!empty($returnings)) {
                                foreach ($returnings as $returns) {
                                    $inputQuantity = $value['quantity'];
                                    if (!$inputQuantity) {
                                        continue;
                                    }
                                    $remainQuantityInCard = BigNumber::new($returns->quantity)
                                        ->sub($returns->quantity_returned_store)
                                        ->toString();

                                    $returnedQuantity = BigNumber::new($returns->quantity_returned_store ?: 0)
                                        ->add($inputQuantity)
                                        ->toString();
                                    if (~BigNumber::new($inputQuantity)->comp($remainQuantityInCard)) {
                                        $returnedQuantity = $returns->quantity;
                                    }
                                    $returns->quantity_returned_store = $returnedQuantity;
                                    $returns->save();
                                }
                            }
                            $cards = $this->getIssueCards($return->bin_id, $return->spare_id);
                            if (!empty($cards)) {
                                foreach ($cards as $card) {
                                    $inputQuantity = $value['quantity'];
                                    if (!$inputQuantity) {
                                        continue;
                                    }
                                    $remainQuantityInCard = BigNumber::new($card->quantity)
                                        ->sub($card->returned_quantity)
                                        ->toString();

                                    $state = Consts::RETURNED_TYPE_PARTIAL;
                                    $returnedQuantity = BigNumber::new($card->returned_quantity ?: 0)
                                        ->add($inputQuantity)
                                        ->toString();
                                    if (~BigNumber::new($inputQuantity)->comp($remainQuantityInCard)) {
                                        $state = Consts::RETURNED_TYPE_ALL;
                                        $returnedQuantity = $card->quantity;
                                    }
                                    $card->returned = $state;
                                    $card->returned_quantity = $returnedQuantity;
                                    $card->save();
                                }
                            }
                            TrackingMo::where('bin_id', $return->bin_id)->where('spare_id', $return->spare_id)
                                ->delete();
                        }
                    }

                    foreach ($spareIds as $spare) {
                        $TransactionSpare = TransactionSpare::create([
                            'taking_transaction_id' => $taking_transaction->id,
                            'spare_id' => $spare['spare_id'],
                            'listWO' => !empty($spare['listWO']) ? $spare['listWO'] : null,
                            'job_card_id' =>  !empty($spare['job_card_id']) ? $spare['job_card_id'] : null,
                            'vehicle_id' => !empty($spare['vehicle_id']) ? $spare['vehicle_id'] : null,
                            'job_name' => !empty($spare['job_name']) ? $spare['job_name'] : null,
                            'vehicle_num' => !empty($spare['vehicle_num']) ? $spare['vehicle_num'] : null,
                            'area_id' => !empty($spare['area_id']) ? $spare['area_id'] : null,
                            'area_name' => !empty($spare['area_name']) ? $spare['area_name'] : null,
                            'platform' => !empty($spare['platform']) ? $spare['platform'] : null,
                            'request_qty' => !empty($spare['request_qty']) ? $spare['request_qty'] : 0,
                        ]);
                    }
                }
            } else {
                $cabinet_id = isset($request['locations'][0]['cabinet']['id']) ? $request['locations'][0]['cabinet']['id'] : null;
                $bin_id = isset($request['locations'][0]['bin']['id']) ? $request['locations'][0]['bin']['id'] : null;
                $bin = Bin::find($bin_id);
                $cluster = Cluster::find($bin['cluster_id']);
                $shelf = Shelf::find($cabinet_id);
                $taking_transaction = TakingTransaction::with('spares')->create([
                    'name' => $request['name'],
                    'status' => $request['status'],
                    'request_qty' => $request['request_qty'],
                    'total_qty' => 0,
                    'remain_qty' => 0,
                    'user_id' => $request['user']['id'],
                    'type' => $request['type'],
                    'cabinet_id' => $request['locations'][0]['cabinet']['id'],
                    'cabinet_name' => isset($shelf['name']) ? $shelf['name'] : null,
                    'cluster_name' => isset($cluster['name']) ? $cluster['name'] : null,
                    'bin_name' => isset($bin['bin']) ? $bin['bin'] : null,
                    'bin_id' => $request['locations'][0]['bin']['id'],
                ]);
                $spareIds = [];

                foreach ($request['locations'][0]['spares'] as $item => $value) {
                    $spareId = $value['id'];
                    $listWO = isset($value['listWO'][0]) ? $value['listWO'][0] : null;
                    $jobCard = [];
                    $vehicleC = [];
                    $area = [];
                    if (isset($value['listWO']) && !empty($value['listWO'])) {
                        $jobCard = JobCard::find($value['listWO'][0]['wo_id']);
                        $vehicleC = Vehicle::find($value['listWO'][0]['vehicle_id']);
                        $area = TorqueWrenchArea::find($value['listWO'][0]['area_id']);
                    }
                    $spareIds[] = [
                        'spare_id' => $spareId,
                        'listWO' => json_encode($listWO),
                        'job_card_id' => !empty($value['listWO'][0]['wo_id']) ? $value['listWO'][0]['wo_id'] : null,
                        'job_name' => !empty($jobCard['wo']) ? $jobCard['wo'] : null,
                        'vehicle_id' =>  !empty($value['listWO'][0]['vehicle_id']) ? $value['listWO'][0]['vehicle_id'] : null,
                        'vehicle_num' => !empty($vehicleC['vehicle_num']) ? $vehicleC['vehicle_num'] : null,
                        'area_id' => !empty($value['listWO'][0]['area_id']) ? $value['listWO'][0]['area_id'] : null,
                        'area_name' => !empty($area['area']) ? $area['area'] : null,
                        'platform' => !empty($value['listWO'][0]['platform']) ? $value['listWO'][0]['platform'] : null,
                        'request_qty' => $value['quantity'],
                    ];
                    if ($request['type'] == 'issue') {
                        $bin_sparess = BinSpare::where('bin_id', $request['locations'][0]['bin']['id'])->where('spare_id', $value['id'])->first();
                        if ($bin_sparess->quantity < $value['quantity']) {
                            if (count($request['locations'][0]['spares']) > 1) {
                                throw new Exception("Please quantity issue < quantity spare");
                                return;
                            } else if (count($request['locations'][0]['spares']) == 1) {
                                $taking_transaction->delete();
                                throw new Exception("Please quantity issue < quantity spare");
                            }
                        } else {
                            $type = ($value['type'] == Consts::SPARE_TYPE_EUC) ? Consts::SPARE_TYPE_EUC : '';
                            switch ($type) {
                                case Consts::SPARE_TYPE_EUC:
                                    $issueCard = IssueCard::create([
                                        'job_card_id' =>  !empty($value['listWO'][0]['wo_id']) ? $value['listWO'][0]['wo_id'] : null,
                                        'bin_id' => null,
                                        'spare_id' => $value['id'],
                                        'quantity' => $value['quantity'],
                                        'torque_wrench_area_id' => !empty($value['listWO'][0]['area_id']) ? $value['listWO'][0]['area_id'] : null,
                                        'issuer_id' => Auth::id(),
                                        'taker_id' =>  Auth::id(),
                                        'euc_box_id' => null,
                                    ]);
                                    $trackingMo = TrackingMo::create(
                                        [
                                            'job_card_id' => !empty($value['listWO'][0]['wo_id']) ? $value['listWO'][0]['wo_id'] : null,
                                            'issue_card_id' => $issueCard->id,
                                            'bin_id' => null,
                                            'spare_id' => $value['id'],
                                            'quantity' => $value['quantity'],
                                            'issuer_id' => Auth::id(),
                                            'torque_wrench_area_id' => !empty($value['listWO'][0]['area_id']) ? $value['listWO'][0]['area_id'] : null,
                                            'taker_id' => Auth::id(),
                                        ]
                                    );
                                    break;
                                default:
                                    $this->adminService->checkProcessingBinSpare(['user_id' => Auth::id(), 'bin_id' => $request['locations'][0]['bin']['id'], 'spare_id' => $value['id'], 'value' => 1]);
                                    $issueCard = IssueCard::create([
                                        'job_card_id' => !empty($value['listWO'][0]['wo_id']) ? $value['listWO'][0]['wo_id'] : null,
                                        'bin_id' => $request['locations'][0]['bin']['id'],
                                        'spare_id' => $value['id'],
                                        'quantity' => $value['quantity'],
                                        'torque_wrench_area_id' => !empty($value['listWO'][0]['area_id']) ? $value['listWO'][0]['area_id'] : null,
                                        'issuer_id' => Auth::id(),
                                        'taker_id' => Auth::id(),
                                        'euc_box_id' => null
                                    ]);
    
                                    $trackingMo = TrackingMo::create(
                                        [
                                            'job_card_id' => !empty($value['listWO'][0]['wo_id']) ? $value['listWO'][0]['wo_id'] : null,
                                            'issue_card_id' => $issueCard->id,
                                            'bin_id' =>  $request['locations'][0]['bin']['id'],
                                            'spare_id' => $value['id'],
                                            'quantity' => $value['quantity'],
                                            'torque_wrench_area_id' =>  !empty($value['listWO'][0]['area_id']) ? $value['listWO'][0]['area_id'] : null,
                                            'issuer_id' => Auth::id(),
                                            'taker_id' => Auth::id(),
                                            'euc_box_id' => null,
                                        ]
                                    );
                                    $dd = $this->updateQuantityInBinSpare($issueCard->bin_id, $issueCard->spare_id, -$issueCard->quantity);
                                    break;
                            }
                        }
                    } else if ($request['type'] == 'return') {
                        $return = ReturnSpare::create([
                            'bin_id' => $request['locations'][0]['bin']['id'],
                            'spare_id' => $value['id'],
                            'quantity' => $value['quantity'],
                            'state' => $value['status'],
                            'type' => $value['type'],
                            'quantity_returned_store' => $value['quantity'],
                            'write_off' => 0,
                        ]);
                        $binSpareCollection = BinSpare::where('bin_id', $return->bin_id)->where('spare_id', $return->spare_id)->first();
                        if (!$binSpareCollection) {
                            throw new Exception("Bin with bin id = {$return->bin_id} is invalid.");
                        }
                        $binSpareCollection->is_processing = 0;
                        $binSpareCollection->process_time = null;
                        $binSpareCollection->process_by = null;
                        $binSpareCollection->save();
                        $this->updateQuantityInBinSpare($return->bin_id, $return->spare_id, $return->quantity);
                        $this->updateRemainQtyInTransaction(Auth::id(), Consts::TAKING_TRANSACTION_TYPE_RETURN, $return->quantity);
                        $returnings = $this->getItemsHandover($return->bin_id, $return->spare_id, Auth::id());
                        if (!empty($returnings)) {
                            foreach ($returnings as $returns) {
                                $inputQuantity = $value['quantity'];
                                if (!$inputQuantity) {
                                    continue;
                                }
                                $remainQuantityInCard = BigNumber::new($returns->quantity)
                                    ->sub($returns->quantity_returned_store)
                                    ->toString();
    
                                $returnedQuantity = BigNumber::new($returns->quantity_returned_store ?: 0)
                                    ->add($inputQuantity)
                                    ->toString();
                                if (~BigNumber::new($inputQuantity)->comp($remainQuantityInCard)) {
                                    $returnedQuantity = $returns->quantity;
                                }
                                $returns->quantity_returned_store = $returnedQuantity;
                                $returns->save();
                            }
                        }
                        $cards = $this->getIssueCards($return->bin_id, $return->spare_id);
                        if (!empty($cards)) {
                            foreach ($cards as $card) {
                                $inputQuantity = $value['quantity'];
                                if (!$inputQuantity) {
                                    continue;
                                }
                                $remainQuantityInCard = BigNumber::new($card->quantity)
                                    ->sub($card->returned_quantity)
                                    ->toString();
    
                                $state = Consts::RETURNED_TYPE_PARTIAL;
                                $returnedQuantity = BigNumber::new($card->returned_quantity ?: 0)
                                    ->add($inputQuantity)
                                    ->toString();
                                if (~BigNumber::new($inputQuantity)->comp($remainQuantityInCard)) {
                                    $state = Consts::RETURNED_TYPE_ALL;
                                    $returnedQuantity = $card->quantity;
                                }
                                $card->returned = $state;
                                $card->returned_quantity = $returnedQuantity;
                                $card->save();
                            }
                        }
                        TrackingMo::where('bin_id', $return->bin_id)->where('spare_id', $return->spare_id)
                            ->delete();
                    }
                }

                foreach ($spareIds as $spare) {
                    $TransactionSpare = TransactionSpare::create([
                        'taking_transaction_id' => $taking_transaction->id,
                        'spare_id' => $spare['spare_id'],
                        'listWO' => !empty($spare['listWO']) ? $spare['listWO'] : null,
                        'job_card_id' =>  !empty($spare['job_card_id']) ? $spare['job_card_id'] : null,
                        'vehicle_id' => !empty($spare['vehicle_id']) ? $spare['vehicle_id'] : null,
                        'job_name' => !empty($spare['job_name']) ? $spare['job_name'] : null,
                        'vehicle_num' => !empty($spare['vehicle_num']) ? $spare['vehicle_num'] : null,
                        'area_id' => !empty($spare['area_id']) ? $spare['area_id'] : null,
                        'area_name' => !empty($spare['area_name']) ? $spare['area_name'] : null,
                        'platform' => !empty($spare['platform']) ? $spare['platform'] : null,
                        'request_qty' => !empty($spare['request_qty']) ? $spare['request_qty'] : 0,
                    ]);
                }
            }
            $taking_transaction->makeHidden(['bin', 'cabinet', 'spares']);
            $taking_transaction->user;
            if (!empty($taking_transaction['spares'])) {
                $spareTypes = [
                    [
                        'accepted' => ['issue', 'return', 'replenish'],
                        'type'     => 'all',
                        'label'    => 'All',
                    ],
                    [
                        'accepted' => ['issue', 'replenish'],
                        'type'     => Consts::SPARE_TYPE_CONSUMABLE,
                        'label'    => 'Consumable',
                    ],
                    [
                        'accepted' => ['issue', 'return'],
                        'type'     => Consts::SPARE_TYPE_DURABLE,
                        'label'    => 'STEs',
                    ],
                    [
                        'accepted' => ['issue', 'return'],
                        'type'     => Consts::SPARE_TYPE_PERISHABLE,
                        'label'    => 'Perishable',
                    ],
                    [
                        'accepted' => ['issue', 'return'],
                        'type'     => Consts::SPARE_TYPE_AFES,
                        'label'    => 'AFES',
                    ],
                    [
                        'accepted' => ['issue', 'replenish'],
                        'type'     => Consts::SPARE_TYPE_EUC,
                        'label'    => 'EUC',
                    ],
                    [
                        'accepted' => ['issue', 'return'],
                        'type'     => Consts::SPARE_TYPE_TORQUE_WRENCH,
                        'label'    => 'Torque Wrench',
                    ],
                    [
                        'accepted' => ['issue', 'return'],
                        'type'     => Consts::SPARE_TYPE_OTHERS,
                        'label'    => 'Others',
                    ],
                ];
                foreach ($taking_transaction['spares'] as $key => $item) {
                    $type_item = $item['type'];
                    $type_transaction = $request['type'];
                    $found = false;
                    foreach ($spareTypes as $spareType) {
                        if (in_array($type_transaction, $spareType['accepted']) && $type_item === $spareType['type']) {
                            $item['label'] = $spareType['label'];
                            $item['bin_spare'] = BinSpare::where('bin_id', $taking_transaction['bin']['id'])->where('spare_id', $item['id'])->first();
                            $taking_transaction['spares'][$key] = $item;
                            $found = true;
                            break;
                        }
                    }
                    if ($found == false) {
                        $item['label'] = 'Unknown';
                        $item['bin_spare'] = null;
                        $taking_transaction['spares'][$key] = $item;
                    }
                }
                $taking_transaction['spares'] = $taking_transaction['spares'];
            }
            $taking_transactions[] = $taking_transaction;
        }
        return $taking_transactions;
    }
    // public function getReportByTnx($params = []){
    //     return $this->getIssueCardsBuilder($params);
    // }
    public function getReportByTnx($request = [])
    {
        $search_key = isset($request['search_key']) ? $request['search_key'] : '';
        $date = isset($request['issued_date']) ? $request['issued_date'] : [];
        $dateee = json_decode($date, true);
        $cluster_id = isset($request['cluster_id']) ? $request['cluster_id'] : 0;
        $shelf_id = isset($request['shelf_id']) ? $request['shelf_id'] : 0;
        $bin_id = isset($request['bin_id']) ? $request['bin_id'] : 0;
        $transactions = TakingTransaction::with('user')->select(['id', 'status', 'request_qty', 'user_id', 'type', 'cabinet_id', 'bin_id', 'bin_name', 'cluster_name', 'cabinet_name', 'updated_at', 'created_at'])->orderBy('created_at', 'desc');
        if (!empty($date)) {
            $transactions->whereBetween('created_at', [$dateee['start'], $dateee['end']]);
        }
        if (!empty($search_key)) {
            $transactions->where(function ($query) use ($search_key) {
                $query->where('id', $search_key)
                    ->orWhereHas('spares', function ($subquery) use ($search_key) {
                        $subquery->where('part_no', 'LIKE', '%' . $search_key . '%');
                    });
            });
        }

        if (!empty($cluster_id)) {
            $transactions->whereHas('cabinet', function ($query) use ($cluster_id) {
                $query->where('cluster_id', $cluster_id);
            });
        }

        if (!empty($shelf_id)) {
            $transactions->whereHas('cabinet', function ($query) use ($shelf_id) {
                $query->where('id', $shelf_id);
            });
        }

        if (!empty($bin_id)) {
            $transactions->whereHas('bin', function ($query) use ($bin_id) {
                $query->where('id', $bin_id);
            });
        }
        $paginatedTransactions = $transactions->get();
        $paginatedTransactionss = $paginatedTransactions->toArray();
        $newData = [];
        foreach ($paginatedTransactionss as $transaction) {
            $spares = $transaction['locations']['spares'];
            foreach ($spares as $spare) {
                $newTransaction = $transaction;
                $newTransaction['locations']['spares'] = $spare;
                $newData[] = $newTransaction;
            }
        }

        $perPage = $request['limit'];
        $page = $request['page'];
        $currentPage = $page;
        $perPage = $request['limit'];
        $paginatedData = array_slice($newData, ($currentPage - 1) * $perPage, $perPage);
        $paginatedTransactions = new LengthAwarePaginator($paginatedData, count($newData), $perPage, $currentPage);

        return $paginatedTransactions;
    }
    // private function getIssueCardsBuilder($params = [])
    // {
    //     $types = array_get($params, 'types', null);
    //     $wo = array_get($params, 'wo', null);
    //     $issuedDate = array_get($params, 'issued_date', null);
    //     $expiredDate = array_get($params, 'expired_date', null);
    //     $torqueWrench = array_get($params, 'torque_wrench', Consts::FALSE);
    //     $noPagination = array_get($params, 'no_pagination', Consts::FALSE);
    //     $limit = array_get($params, 'limit', Consts::DEFAULT_PER_PAGE);
    //     $onlyTypeEuc = array_get($params, 'only_type_euc', false);
    //     $trackingMo = Arr::get($params, 'tracking_mo', false);

    //     $fromTableName = 'issue_cards';
    //     if ($trackingMo) {
    //         $fromTableName = 'tracking_mo';
    //         $query = TrackingMo::join('job_cards', 'job_cards.id', "$fromTableName.job_card_id");
    //     } else {
    //         $query = IssueCard::join('job_cards', 'job_cards.id', "$fromTableName.job_card_id");
    //     }
    //     $rawData = $query
    //         ->join('vehicles', 'vehicles.id', 'job_cards.vehicle_id')
    //         ->join('users as issuer', 'issuer.id', "$fromTableName.issuer_id")
    //         ->join('users as taker', 'taker.id', "$fromTableName.taker_id")
    //         ->join('spares', 'spares.id', "$fromTableName.spare_id")
    //         ->leftJoin('torque_wrench_areas', 'torque_wrench_areas.id', "$fromTableName.torque_wrench_area_id")
    //         ->when($types, function ($query) use ($types) {
    //             $query->whereIn('spares.type', $types);
    //         })
    //         ->when($torqueWrench, function ($query) use ($fromTableName) {
    //             $query->whereNotNull("$fromTableName.torque_wrench_area_id");
    //         })
    //         ->when($wo, function ($query) use ($wo) {
    //             return $this->queryRange($query, $wo, 'job_cards.wo');
    //         })
    //         ->when($issuedDate, function ($query) use ($issuedDate, $fromTableName) {
    //             return $this->queryRange($query, $issuedDate, "$fromTableName.created_at");
    //         })
    //         ->when(!empty($params['search_key']), function ($query) use ($params) {
    //             $searchKey = Utils::escapeLike($params['search_key']);

    //             // $query->where(function ($subQuery) use ($searchKey) {
    //             //     $subQuery->where('spares.name', 'LIKE', "%{$searchKey}%")
    //             //         ->orWhere('spares.part_no', 'LIKE', "%{$searchKey}%")
    //             //         ->orWhere('spares.material_no', 'LIKE', "%{$searchKey}%");
    //             // });
    //         })
    //         ->when(!empty($params['spare_id']), function ($query) use ($params) {
    //             $query->where('spares.id', $params['spare_id']);
    //         })
    //         ->when(!empty($params['returned_type']), function ($query) use ($params, $fromTableName) {
    //             $returnTypes = (array)$params['returned_type'];
    //             $query->where(function ($subQuery) use ($returnTypes, $fromTableName) {
    //                 $subQuery->whereNull("$fromTableName.returned")
    //                     ->orWhereIn("$fromTableName.returned", $returnTypes);
    //             });
    //         }, function ($query) use ($params, $fromTableName) {
    //             $query->where(function ($subQuery) use ($fromTableName) {
    //                 $subQuery->whereNull("$fromTableName.returned")
    //                     ->orWhere("$fromTableName.returned", '!=', Consts::RETURNED_TYPE_LINK_MO);
    //             });
    //         })
    //         ->select(
    //             'job_cards.*',
    //             'vehicles.*',
    //             'spares.type as spare_type',
    //             'spares.name as spare_name',
    //             'spares.part_no',
    //             'spares.material_no',
    //             'spares.id AS spare_id',
    //             "$fromTableName.quantity",
    //             "$fromTableName.bin_id",
    //             "$fromTableName.created_at as issued_date",
    //             'issuer.name as issuer_name',
    //             'taker.name as taker_name',
    //             "$fromTableName.updated_at as issued_update_date",
    //             "$fromTableName.quantity as issued_quantity",
    //             "$fromTableName.returned_quantity",
    //             "$fromTableName.id as issued_id",
    //             'torque_wrench_areas.area as torque_area',
    //             'torque_wrench_areas.torque_value',
    //             'torque_wrench_areas.id as torque_id',
    //             DB::raw(
    //                 "
    //                 CASE
	//                 WHEN $fromTableName.quantity - $fromTableName.returned_quantity = 0 THEN 0
	//                 WHEN DATEDIFF(NOW(), DATE_ADD(DATE_FORMAT($fromTableName.created_at, '%Y-%m-%d 00:00:00'), INTERVAL 16 HOUR)) = 0 and $fromTableName.created_at > DATE_ADD(DATE_FORMAT($fromTableName.created_at, '%Y-%m-%d 00:00:00'),INTERVAL 16 HOUR) THEN 0
	//                 WHEN NOW() > DATE_ADD(DATE_FORMAT($fromTableName.created_at, '%Y-%m-%d 00:00:00'), INTERVAL 16 HOUR) THEN 1
	//                 ELSE 0
    //                 END as expired_return_time_sql"
    //             ),
    //         )
    //         ->orderBy('expired_return_time_sql', 'DESC')
    //         ->when($onlyTypeEuc, function ($query) {
    //             $query
    //                 ->join('euc_box_spares', function ($join, $fromTableName) {
    //                     $join->on("$fromTableName.euc_box_id", '=', 'euc_box_spares.euc_box_id');
    //                     $join->on("$fromTableName.spare_id", '=', 'euc_box_spares.spare_id');
    //                 })
    //                 ->join('euc_boxes', 'euc_boxes.id', 'euc_box_spares.euc_box_id')
    //                 ->addSelect('euc_box_spares.serial_no', 'euc_boxes.order AS euc_box_order');
    //         })
    //         ->when(
    //             !empty($params['sort']) && !empty($params['sort_type']),
    //             function ($query) use ($params) {
    //                 return $query->orderBy($params['sort'], $params['sort_type']);
    //             },
    //             function ($query) use ($fromTableName) {
    //                 return $query->orderBy("$fromTableName.updated_at", 'desc');
    //             }
    //         )
    //         ->when(
    //             empty($noPagination),
    //             function ($query) use ($limit) {
    //                 return $query->paginate($limit);
    //             },
    //             function ($query) {
    //                 return $query->get();
    //             }
    //         );

    //     $data = $noPagination ? $rawData : $rawData->getCollection();

    //     $binIds = $data->pluck('bin_id')->toArray();
    //     $configures = BinConfigure::whereIn('bin_id', $binIds)
    //         ->get()
    //         ->mapToGroups(function ($item) {
    //             return [$item['bin_id'] => $item];
    //         })
    //         ->toArray();
    //     $sparesExpiredReturns = $this->getSparesExpiredForReturns($params);

    //     if ($noPagination) {
    //         return $data->transform(function ($record) use ($configures, $sparesExpiredReturns) {
    //             return $this->transformIssueSpare($record, $configures, $sparesExpiredReturns);
    //         });
    //     }

    //     $rawData->getCollection()->transform(function ($record) use ($configures, $sparesExpiredReturns) {
    //         return $this->transformIssueSpare($record, $configures, $sparesExpiredReturns);
    //     });

    //     return $rawData;
    // }
    public function getReportByLoan($request = [])
    {
        $search_key = isset($request['search_key']) ? $request['search_key'] : '';
        if (!empty($date)) {
            $date = isset($request['issued_date']) ? $request['issued_date'] : [];
            $dateee = json_decode($date, true);
        }
        $transactions = TakingTransaction::with('user')->select(['id', 'status', 'request_qty', 'user_id', 'type', 'cabinet_id', 'bin_id', 'bin_name', 'cluster_name', 'cabinet_name', 'updated_at', 'created_at'])->orderBy('created_at', 'desc')->get();
        if (!empty($date)) {
            $transactions->whereBetween('created_at', [$dateee['start'], $dateee['end']]);
        }
        $transactions = $transactions->toArray();
        if (!empty($search_key)) {
            $transactions->where(function ($query) use ($search_key) {
                $query->where('id', $search_key)
                    ->orWhereHas('spares', function ($subquery) use ($search_key) {
                        $subquery->where('part_no', 'LIKE', '%' . $search_key . '%');
                    });
            });
        }
        foreach ($transactions as $key => $value) {
            if (!empty($value['locations']['spares'])) {
                foreach ($value['locations']['spares'] as $value2) {
                    if ($value2['type'] == Consts::SPARE_TYPE_CONSUMABLE) {
                        unset($transactions[$key]);
                    }
                }
            }
        }
        $newData = [];
        foreach ($transactions as $transaction) {
            $spares = $transaction['locations']['spares'];
            foreach ($spares as $spare) {
                $newTransaction = $transaction;
                $newTransaction['locations']['spares'] = $spare;
                $newData[] = $newTransaction;
            }
        }

        $perPage = $request['limit'];
        $page = $request['page'];
        $currentPage = $page;
        $perPage = $request['limit'];
        $paginatedData = array_slice($newData, ($currentPage - 1) * $perPage, $perPage);
        $paginatedTransactions = new LengthAwarePaginator($paginatedData, count($newData), $perPage, $currentPage);

        return $paginatedTransactions;
    }
    public function getReportForReturns($request = [])
    {
        $search_key = isset($request['search_key']) ? $request['search_key'] : '';
        $date = isset($request['returned_date']) ? $request['returned_date'] : [];
        $dateee = json_decode($date, true);
        $transactions = TakingTransaction::with('user')->where('type', 'return')->select(['id', 'status', 'request_qty', 'user_id', 'type', 'cabinet_id', 'bin_id', 'bin_name', 'cluster_name', 'cabinet_name', 'updated_at', 'created_at'])->orderBy('created_at', 'desc');
        if (!empty($date)) {
            $transactions->whereBetween('created_at', [$dateee['start'], $dateee['end']]);
        }
        if (!empty($search_key)) {
            $transactions->where(function ($query) use ($search_key) {
                $query->where('id', $search_key)
                    ->orWhereHas('spares', function ($subquery) use ($search_key) {
                        $subquery->where('part_no', 'LIKE', '%' . $search_key . '%');
                    });
            });
        }
        $paginatedTransactions = $transactions->get();
        $paginatedTransactionss = $paginatedTransactions->toArray();
        $newData = [];
        foreach ($paginatedTransactionss as $transaction) {
            $spares = $transaction['locations']['spares'];
            foreach ($spares as $spare) {
                $newTransaction = $transaction;
                $newTransaction['locations']['spares'] = $spare;
                $newData[] = $newTransaction;
            }
        }

        $perPage = $request['limit'];
        $page = $request['page'];
        $currentPage = $page;
        $perPage = $request['limit'];
        $paginatedData = array_slice($newData, ($currentPage - 1) * $perPage, $perPage);
        $paginatedTransactions = new LengthAwarePaginator($paginatedData, count($newData), $perPage, $currentPage);
        return $paginatedTransactions;
    }
    public function getSparesWriteOff($request = [])
    {
        $date = isset($request['dates']) ? $request['dates'] : [];
        $dateee = json_decode($date, true);
        $search_key = isset($request['search_key']) ? $request['search_key'] : '';
        $data = WriteOff::with('bin', 'spares', 'user')->orderBy('created_at', 'desc');
        if (!empty($date)) {
            $data->whereBetween('created_at', [$dateee['start'], $dateee['end']]);
        }
        if (!empty($search_key)) {
            $data->where(function ($query) use ($search_key) {
                $query->where('id', $search_key)
                    ->orWhereHas('spares', function ($subquery) use ($search_key) {
                        $subquery->where('part_no', 'LIKE', '%' . $search_key . '%');
                    });
            });
        }
        $perPage = $request['limit'];
        $page = $request['page'];
        $paginatedTransactions = $data->paginate($perPage, ['*'], 'page', $page);
        return $paginatedTransactions;
    }
    public function unwriteOffSpare($request = [])
    {
        $writeOff = WriteOff::find($request['id']);

        $writeOff->eliminator_id = Auth::id();
        $writeOff->save();
        $writeOff->delete();
        return $writeOff;
    }
    public function writeOffSpare($params = [])
    {
        $item = $params['return_spare_id'];
        // foreach ($params['return_spare_id'] as $item) {
        $returnedSpareCurrent = ReturnSpare::query()
            ->where('spare_id', $item)
            ->first();
        if ($returnedSpareCurrent->write_off == Consts::TRUE) {
            return;
        }
        $returnedSpares = ReturnSpare::query()
            ->where('bin_id', $returnedSpareCurrent->bin_id)
            ->where('spare_id', $returnedSpareCurrent->spare_id)
            // ->where('state', '!=', Consts::RETURN_SPARE_STATE_WORKING)
            ->where(function ($subQuery) {
                /** @var Builder $subQuery */
                $subQuery->whereNull('return_spares.write_off')
                    ->orWhere('return_spares.write_off', Consts::FALSE);
            })
            ->get();
        foreach ($returnedSpares as $returnedSpare) {
            $returnedSpare->write_off = Consts::TRUE;
            $returnedSpare->save();
            $bin = Bin::find($returnedSpare->bin_id);
            $cluster = Cluster::find($bin['cluster_id']);
            $shelf = Shelf::find($bin['shelf_id']);
            $writeOff = WriteOff::create([
                'return_spare_id' => $returnedSpare->id,
                'bin_id' => $returnedSpare->bin_id,
                'bin_name' => $bin['bin'],
                'cluster_name' => $cluster['name'],
                'cabinet_name' => $shelf['name'],
                'spare_id' => $returnedSpare->spare_id,
                'quantity' => $returnedSpare->quantity,
                'reason' => $params['reason'],
                'user_id' => Auth::id(),
            ]);

            /** @var Bin $bin */
            $bin = Bin::query()->where('id', $returnedSpare->bin_id)->first();
            $bin->quantity_oh = $bin->quantity;

            $bin->save();
        }
        // }
        return true;
    }
    public function getSparesTorqueWrench($params = [])
    {
        $params = array_merge([
            'torque_wrench' => Consts::TRUE,
            'types' => [
                Consts::SPARE_TYPE_TORQUE_WRENCH
            ],
            'returned_type' => [
                Consts::RETURNED_TYPE_PARTIAL,
                Consts::RETURNED_TYPE_ALL,
                Consts::RETURNED_TYPE_LINK_MO,
            ]
        ], $params);
        return $this->getIssueCardsBuilderBU($params);
    }
    private function getIssueCardsBuilderBU($params){
        $trackingMo = Arr::get($params, 'tracking_mo', false);
        $torqueWrench = array_get($params, 'torque_wrench', Consts::FALSE);
        $bin_id = $params['bin_id'];
        $spare_id = $params['spare_id'];
        // var_dump($spare_id);die();
        $issue_card_id = $params['issue_card'];
        $query = TrackingMo::with('torqueWrenchArea','issueCard','jobCard')
        ->where('bin_id', $bin_id)->where('spare_id', $spare_id)->where('issue_card_id',$issue_card_id)->get();
        return $query;
    }
    private function getIssueCardsBuilder($params = [])
    {
        $types = array_get($params, 'types', null);
        $wo = array_get($params, 'wo', null);
        $issuedDate = array_get($params, 'issued_date', null);
        $expiredDate = array_get($params, 'expired_date', null);
        $torqueWrench = array_get($params, 'torque_wrench', Consts::FALSE);
        $noPagination = array_get($params, 'no_pagination', Consts::FALSE);
        $limit = array_get($params, 'limit', Consts::DEFAULT_PER_PAGE);
        $onlyTypeEuc = array_get($params, 'only_type_euc', false);
        $trackingMo = Arr::get($params, 'tracking_mo', false);

        $fromTableName = 'issue_cards';
        if ($trackingMo) {
            $fromTableName = 'tracking_mo';
            $query = TrackingMo::join('job_cards', 'job_cards.id', "$fromTableName.job_card_id");
        } else {
            $query = IssueCard::join('job_cards', 'job_cards.id', "$fromTableName.job_card_id");
        }
        $rawData = $query
            ->join('vehicles', 'vehicles.id', 'job_cards.vehicle_id')
            ->join('users as issuer', 'issuer.id', "$fromTableName.issuer_id")
            ->join('users as taker', 'taker.id', "$fromTableName.taker_id")
            ->join('spares', 'spares.id', "$fromTableName.spare_id")
            ->leftJoin('torque_wrench_areas', 'torque_wrench_areas.id', "$fromTableName.torque_wrench_area_id")
            ->when($types, function ($query) use ($types) {
                $query->whereIn('spares.type', $types);
            })
            ->when($torqueWrench, function ($query) use ($fromTableName) {
                $query->whereNotNull("$fromTableName.torque_wrench_area_id");
            })
            ->when($wo, function ($query) use ($wo) {
                return $this->queryRange($query, $wo, 'job_cards.wo');
            })
            ->when($issuedDate, function ($query) use ($issuedDate, $fromTableName) {
                return $this->queryRange($query, $issuedDate, "$fromTableName.created_at");
            })
            ->when(!empty($params['search_key']), function ($query) use ($params) {
                $searchKey = Utils::escapeLike($params['search_key']);

                // $query->where(function ($subQuery) use ($searchKey) {
                //     $subQuery->where('spares.name', 'LIKE', "%{$searchKey}%")
                //         ->orWhere('spares.part_no', 'LIKE', "%{$searchKey}%")
                //         ->orWhere('spares.material_no', 'LIKE', "%{$searchKey}%");
                // });
            })
            ->when(!empty($params['spare_id']), function ($query) use ($params) {
                $query->where('spares.id', $params['spare_id']);
            })
            ->when(!empty($params['returned_type']), function ($query) use ($params, $fromTableName) {
                $returnTypes = (array)$params['returned_type'];
                $query->where(function ($subQuery) use ($returnTypes, $fromTableName) {
                    $subQuery->whereNull("$fromTableName.returned")
                        ->orWhereIn("$fromTableName.returned", $returnTypes);
                });
            }, function ($query) use ($params, $fromTableName) {
                $query->where(function ($subQuery) use ($fromTableName) {
                    $subQuery->whereNull("$fromTableName.returned")
                        ->orWhere("$fromTableName.returned", '!=', Consts::RETURNED_TYPE_LINK_MO);
                });
            })
            ->select(
                'job_cards.*',
                'vehicles.*',
                'spares.type as spare_type',
                'spares.name as spare_name',
                'spares.part_no',
                'spares.material_no',
                'spares.id AS spare_id',
                "$fromTableName.quantity",
                "$fromTableName.bin_id",
                "$fromTableName.created_at as issued_date",
                'issuer.name as issuer_name',
                'taker.name as taker_name',
                "$fromTableName.updated_at as issued_update_date",
                "$fromTableName.quantity as issued_quantity",
                "$fromTableName.returned_quantity",
                "$fromTableName.id as issued_id",
                'torque_wrench_areas.area as torque_area',
                'torque_wrench_areas.torque_value',
                'torque_wrench_areas.id as torque_id',
                DB::raw(
                    "
                    CASE
	                WHEN $fromTableName.quantity - $fromTableName.returned_quantity = 0 THEN 0
	                WHEN DATEDIFF(NOW(), DATE_ADD(DATE_FORMAT($fromTableName.created_at, '%Y-%m-%d 00:00:00'), INTERVAL 16 HOUR)) = 0 and $fromTableName.created_at > DATE_ADD(DATE_FORMAT($fromTableName.created_at, '%Y-%m-%d 00:00:00'),INTERVAL 16 HOUR) THEN 0
	                WHEN NOW() > DATE_ADD(DATE_FORMAT($fromTableName.created_at, '%Y-%m-%d 00:00:00'), INTERVAL 16 HOUR) THEN 1
	                ELSE 0
                    END as expired_return_time_sql"
                ),
            )
            ->orderBy('expired_return_time_sql', 'DESC')
            ->when($onlyTypeEuc, function ($query) {
                $query
                    ->join('euc_box_spares', function ($join, $fromTableName) {
                        $join->on("$fromTableName.euc_box_id", '=', 'euc_box_spares.euc_box_id');
                        $join->on("$fromTableName.spare_id", '=', 'euc_box_spares.spare_id');
                    })
                    ->join('euc_boxes', 'euc_boxes.id', 'euc_box_spares.euc_box_id')
                    ->addSelect('euc_box_spares.serial_no', 'euc_boxes.order AS euc_box_order');
            })
            ->when(
                !empty($params['sort']) && !empty($params['sort_type']),
                function ($query) use ($params) {
                    return $query->orderBy($params['sort'], $params['sort_type']);
                },
                function ($query) use ($fromTableName) {
                    return $query->orderBy("$fromTableName.updated_at", 'desc');
                }
            )
            ->when(
                empty($noPagination),
                function ($query) use ($limit) {
                    return $query->paginate($limit);
                },
                function ($query) {
                    return $query->get();
                }
            );

        $data = $noPagination ? $rawData : $rawData->getCollection();

        $binIds = $data->pluck('bin_id')->toArray();
        $configures = BinConfigure::whereIn('bin_id', $binIds)
            ->get()
            ->mapToGroups(function ($item) {
                return [$item['bin_id'] => $item];
            })
            ->toArray();
        $sparesExpiredReturns = $this->getSparesExpiredForReturns($params);

        if ($noPagination) {
            return $data->transform(function ($record) use ($configures, $sparesExpiredReturns) {
                return $this->transformIssueSpare($record, $configures, $sparesExpiredReturns);
            });
        }

        $rawData->getCollection()->transform(function ($record) use ($configures, $sparesExpiredReturns) {
            return $this->transformIssueSpare($record, $configures, $sparesExpiredReturns);
        });

        return $rawData;
    }
}
