<?php

namespace App\Http\Services;

use App\Consts;
use App\Imports\SparesImport;
use App\Models\Bin;
use App\Models\BinConfigure;
use App\Models\EucBox;
use App\Models\EucBoxSpare;
use App\Models\IssueCard;
use App\Models\JobCard;
use App\Models\PolHistory;
use App\Models\PolManagement;
use App\Models\ReplenishEucBox;
use App\Models\ReplenishmentSpare;
use App\Models\ReturnSpare;
use App\Models\Shelf;
use App\Models\Spare;
use App\Models\TorqueWrenchArea;
use App\Models\UserAccessingSpare;
use App\Models\Vehicle;
use App\Models\VehicleType;
use App\Models\WriteOff;
use App\Traits\CustomQueryBuilder;
use App\User;
use App\Utils;
use App\Utils\BigNumber;
use App\Utils\SpareUtils;
use Auth;
use Cache;
use Carbon\Carbon;
use Excel;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AdminService extends BaseService
{
    use CustomQueryBuilder;

    public function getTorqueWrenchAreas($params)
    {
        $rawData = TorqueWrenchArea::when(!empty($params['q']), function ($query) use ($params) {
            return $query->where('area', 'LIKE', '%' . $params['q'] . '%');
        })
            ->when(
                !empty($params['no_pagination']),
                function ($query) {
                    return $query->get();
                },
                function ($query) use ($params) {
                    return $query->paginate(array_get($params, 'limit', Consts::DEFAULT_PER_PAGE));
                }
            );

        $transform = function ($record) {
            $record->torque_value = floatval($record->torque_value);
            return $record;
        };
        $noPagination = array_get($params, 'no_pagination', Consts::FALSE);
        if ($noPagination) {
            return $rawData->transform(function ($record) use ($transform) {
                return $transform($record);
            });
        }

        $rawData->getCollection()->transform(function ($record) use ($transform) {
            return $transform($record);
        });

        return $rawData;
    }

    public function createTorqueWrenchArea($params)
    {
        $torqueWrenchArea = new TorqueWrenchArea();
        $params['code'] = Str::slug($params['area']);
        return $this->saveData($torqueWrenchArea, $params);
    }

    public function updateTorqueWrenchArea($params)
    {
        $torqueWrenchArea = TorqueWrenchArea::find($params['id']);
        $torqueWrenchArea = $this->saveData($torqueWrenchArea, $params);

        return $torqueWrenchArea;
    }

    public function deleteTorqueWrenchArea($torqueWrenchAreaId)
    {
        $torqueWrenchArea = TorqueWrenchArea::find($torqueWrenchAreaId);
        // Check delete torqueWrenchArea
        $canDeleteTorqueWrenchArea = $this->canDeleteTorqueWrenchArea($torqueWrenchAreaId);
        if ($canDeleteTorqueWrenchArea) {
            $torqueWrenchArea->forceDelete();
        }

        return $torqueWrenchArea;
    }

    private function canDeleteTorqueWrenchArea($torqueWrenchAreaId)
    {
        // Check torqueWrenchAreaId is containing the issue_cards
        $countUseInIssueCard = IssueCard::where('torque_wrench_area_id', $torqueWrenchAreaId)->count();
        if ($countUseInIssueCard) {
            throw new Exception('Area Use is containing the issue card, cannot delete it');
        }

        return true;
    }

    public function getShelfs($params)
    {
        return Shelf::leftJoin('clusters', 'clusters.id', 'shelfs.cluster_id')
            ->when(!empty($params['shelfs_id']), function ($query) use ($params) {
                $query->where('shelfs.id', $params['shelfs_id']);
            })
            ->when(!empty($params['search_key']), function ($query) use ($params) {
                $searchKey = Utils::escapeLike($params['search_key']);
                $query->where(function ($subQuery) use ($searchKey) {
                    $subQuery->where('name', 'LIKE', "%{$searchKey}%")
                        ->orWhere('code', 'LIKE', "%{$searchKey}%");
                });
            })
            ->when(!empty($params['cluster_id']), function ($query) use ($params) {
                $query->where('cluster_id', $params['cluster_id']);
            })
            ->when(
                !empty($params['sort']) && !empty($params['sort_type']),
                function ($query) use ($params) {
                    return $query->orderBy($params['sort'], $params['sort_type']);
                },
                function ($query) {
                    return $query->orderBy('id', 'asc');
                }
            )
            ->select(
                'shelfs.*',
                'clusters.name as cluster_name',
                DB::raw(
                    '(CASE WHEN shelfs.type = "' . Consts::SHELF_TYPE_MAIN . '" THEN "Main Cabinet" 
                    ELSE "Sub Cabinet" END) AS shelf_type'
                )
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

    public function getShelfInfo($shelfId)
    {
        $shelf = Shelf::find($shelfId);
        return $shelf;
    }

    public function createShelf($params)
    {
        $shelf = new Shelf;
        $shelf = $this->saveData($shelf, $params);

        $this->saveBinsConfigure($shelf);
        $this->forceOnlyOneMainCabinet($shelf);

        $params = [
            'shelfs_id' => $shelf->id
        ];
        return $this->getShelfs($params)->first();
    }

    private function saveBinsConfigure($shelf)
    {
        for ($i = 0; $i < $shelf->num_rows; $i++) {
            for ($j = 0; $j < $shelf->num_bins; $j++) {
                $row = $i + 1;
                $bin = $j + 1;

                $binInfo = Bin::where('shelf_id', $shelf->id)
                    ->where('row', $row)
                    ->where('bin', $bin)
                    ->first();

                if (!$binInfo) {
                    Bin::create([
                        'cluster_id' => $shelf->cluster_id,
                        'shelf_id'  => $shelf->id,
                        'row'       => $row,
                        'bin'       => $bin,
                        'status'    => Consts::BIN_STATUS_UNASSIGNED
                    ]);
                }
            }
        }
    }

    public function updateShelf($params)
    {
        $shelf = Shelf::find($params['id']);
        $shelf = $this->saveData($shelf, $params);

        $this->saveBinsConfigure($shelf);
        $this->forceOnlyOneMainCabinet($shelf);

        $params = [
            'shelfs_id' => $shelf->id
        ];
        return $this->getShelfs($params)->first();
    }

    private function forceOnlyOneMainCabinet($shelf)
    {
        if (!$shelf->cluster_id || $shelf->type !== Consts::SHELF_TYPE_MAIN) {
            return;
        }

        Shelf::where('cluster_id', $shelf->cluster_id)
            ->where('id', '!=', $shelf->id)
            ->update(['type' => Consts::SHELF_TYPE_SUB]);
    }

    public function deleteShelf($shelfId)
    {
        $shelf = Shelf::find($shelfId);
        // Check delete shelf
        $canDeleteShelf = $this->canDeleteShelf($shelfId);
        if ($canDeleteShelf) {
            $shelf->delete();

            Bin::where('shelf_id', $shelf->id)->delete();
        }

        return $shelf;
    }

    private function canDeleteShelf($shelfId)
    {
        $binIds = Bin::select('id')->where('shelf_id', $shelfId)->pluck('id')->all();
        if (count($binIds)) {
            // Check bin is containing the issue_cards
            $countUseInIssueCard = IssueCard::whereIn('bin_id', $binIds)->count();
            if ($countUseInIssueCard) {
                throw new Exception('Bin is containing the issue card, cannot delete it');
            }

            // Check bin is containing the replenishment_spares
            $countUseInReplenishmentSpare = ReplenishmentSpare::whereIn('bin_id', $binIds)->count();
            if ($countUseInReplenishmentSpare) {
                throw new Exception('Bin is containing the replenishment spares, cannot delete it');
            }

            // Check bin is containing the return_spares
            $countUseInReturnSpare = ReturnSpare::whereIn('bin_id', $binIds)->count();
            if ($countUseInReturnSpare) {
                throw new Exception('Bin is containing the return spares, cannot delete it');
            }

            // Check bin is containing the write_offs
            $countUseInWriteOff = WriteOff::whereIn('bin_id', $binIds)->count();
            if ($countUseInWriteOff) {
                throw new Exception('Bin is containing the write off, cannot delete it');
            }
        }

        return true;
    }

    public function getSpares($params)
    {
        return Spare::with('userAccessingSpares')
            ->when(!empty($params['search_key']), function ($query) use ($params) {
                $searchKey = Utils::escapeLike($params['search_key']);
                $query->where(function ($subQuery) use ($searchKey) {
                    $subQuery->where('name', 'LIKE', "%{$searchKey}%")
                        ->orWhere('part_no', 'LIKE', "%{$searchKey}%")
                        ->orWhere('material_no', 'LIKE', "%{$searchKey}%");
                });
            })
            ->when(!empty($params['type']), function ($query) use ($params) {
                $query->where('type', $params['type']);
            })
            ->when(!empty($params['unassigned']), function ($query) use ($params) {
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

    public function getSpareByMpn($params)
    {
        $materialNo = array_get($params, 'material_no', null);
        return Spare::where('material_no', $materialNo)->first();
    }

    public function getSpareByPartNo($params)
    {
        $partNo = array_get($params, 'part_no', null);
        return Spare::where('part_no', $partNo)->first();
    }

    public function getSparesUnassigned($params)
    {
        $params['no_pagination'] = Consts::TRUE;
        $spareIds = Bin::whereNotNull('spare_id')->get()->pluck('spare_id')->toArray();

        return $this->getSpares($params)
            ->filter(function ($spare) use ($spareIds) {
                return !in_array($spare->id, $spareIds);
            })
            ->values();
    }

    public function getSparesAssignedBin($params = [])
    {
        // If params does not have excludeBinIds
        if (!Arr::get($params, 'excludeBinIds')) {
            //            $params['excludeBinIds'] = $this->getNotWorkingReturnSpares()->pluck('bin_id')->toArray();
            $params['excludeBinIds'] = $this->getNotWorkingSpareIds();
        }

        // Does not get empty bin
        $ignoreEmpty = Arr::get($params, 'ignore_empty', false);
        // Can replenishment (quantity = 0 and quantity_oh = 0)
        $canReplenishment = Arr::get($params, 'can_replenishment', false);

        return Bin::with('configures')
            ->join('spares', 'spares.id', 'bins.spare_id')
            ->join('shelfs', 'shelfs.id', 'bins.shelf_id')
            ->leftJoin('bin_configures', 'bin_configures.bin_id', 'bins.id')
            ->leftJoin('clusters', 'clusters.id', 'bins.cluster_id')
            ->where('bins.status', Consts::BIN_STATUS_ASSIGNED)
            ->where('bins.is_failed', 0)
            ->where('bins.is_processing', 0)
            ->when(!empty($params['excludeBinIds']), function ($query) use ($params) {
                $query->whereNotIn('bins.id', $params['excludeBinIds']);
            })
            ->when(!empty($params['binIds']), function ($query) use ($params) {
                $query->whereIn('bins.id', $params['binIds']);
            })
            ->when(!empty($params['spareIds']), function ($query) use ($params) {
                $query->whereIn('spares.id', $params['spareIds']);
            })
            ->when(!empty($params['type']), function ($query) use ($params) {
                $query->where('spares.type', $params['type']);
            })
            ->when(!empty($params['types']), function ($query) use ($params) {
                $query->whereIn('spares.type', $params['types']);
            })
            ->when(!empty($params['cluster_id']), function ($query) use ($params) {
                $query->where('bins.cluster_id', $params['cluster_id']);
            })
            ->when(!empty($params['search_key']), function ($query) use ($params) {
                $searchKey = Utils::escapeLike($params['search_key']);
                $query->where(function ($subQuery) use ($searchKey) {
                    $subQuery->where('spares.name', 'LIKE', "%{$searchKey}%")
                        ->orWhere('spares.part_no', 'LIKE', "%{$searchKey}%");
                });
            })
            ->when($ignoreEmpty, function ($query) {
                $query->where('quantity_oh', '>', 0);
            })
            ->when($canReplenishment, function ($query) {
                $query->where('quantity', 0)
                    ->where('quantity_oh', 0);
            })
            ->when(empty($params['include_is_virtual']), function ($query) use ($params) {
                $query->where('clusters.is_virtual', Consts::FALSE);
            })
            ->select('spares.*', 'bins.*', 'bins.id as bin_id', 'bins.bin as bin_name')
            ->addSelect('shelfs.name as shelf_name', 'clusters.name as cluster_name', 'spares.name as spare_name')
            ->addSelect(DB::raw('CONCAT(clusters.name," - ",shelfs.name," - ",bins.row, " - ",bins.bin) as location'))
            ->addSelect('bin_configures.serial_no as serial_no')
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

    public function getEucAssignedBox($params = [])
    {
        // Does not get empty bin
        $ignoreEmpty = Arr::get($params, 'ignore_empty', false);

        $eucboxes = EucBoxSpare::join('euc_boxes', 'euc_boxes.id', 'euc_box_spares.euc_box_id')
            ->join('spares', 'spares.id', 'euc_box_spares.spare_id')
            ->where('spares.type', Consts::SPARE_TYPE_EUC)
            ->when(isset($params['ignore_overdue_item']) && $params['ignore_overdue_item'] == true, function ($query) use ($params) {
                $query->where(function ($subQuery) {
                    // Do not get overdue items by calibration_due
                    $subQuery->whereNull('euc_box_spares.calibration_due')
                        ->orWhere('euc_box_spares.calibration_due', '>=', Carbon::now()->format('Y-m-d'));
                })
                    // Do not get overdue items by expiry_date
                    ->where(function ($subQuery) {
                        $subQuery->whereNull('euc_box_spares.expiry_date')
                            ->orWhere('euc_box_spares.expiry_date', '>=', Carbon::now()->format('Y-m-d'));
                    })
                    // Do not get overdue items by load_hydrostatic_test_due
                    ->where(function ($subQuery) {
                        $subQuery->whereNull('euc_box_spares.load_hydrostatic_test_due')
                            ->orWhere('euc_box_spares.load_hydrostatic_test_due', '>=', Carbon::now()->format('Y-m-d'));
                    });
            })
            ->when($ignoreEmpty, function ($query) {
                $query->where('euc_box_spares.quantity_oh', '>', 0);
            })
            ->select('spares.*', 'euc_box_spares.*', 'euc_boxes.order as euc_box_order')
            ->get();

        return $eucboxes;
    }

    public function getItemsForIssuing($params = [])
    {
        //        $params['excludeBinIds'] = $this->getNotWorkingReturnSpares()->pluck('bin_id')->toArray();
        $params['excludeBinIds'] = $this->getNotWorkingSpareIds();
        $sparesInBin = $this->getSparesAssignedBin($params);
        $eucboxes = $this->getEucAssignedBox($params);
        //        $eucboxes = EucBoxSpare::join('euc_boxes', 'euc_boxes.id', 'euc_box_spares.euc_box_id')
        //            ->join('spares', 'spares.id', 'euc_box_spares.spare_id')
        //            ->where('spares.type', Consts::SPARE_TYPE_EUC)
        //            ->when(isset($params['ignore_overdue_item']) && $params['ignore_overdue_item'] == true, function ($query) use ($params) {
        //                $query->where(function($subQuery) {
        //                    // Do not get overdue items by calibration_due
        //                    $subQuery->whereNull('euc_box_spares.calibration_due')
        //                        ->orWhere('euc_box_spares.calibration_due', '>=', Carbon::now()->format('Y-m-d'));
        //                    })
        //                    // Do not get overdue items by expiry_date
        //                    ->where(function($subQuery) {
        //                        $subQuery->whereNull('euc_box_spares.expiry_date')
        //                            ->orWhere('euc_box_spares.expiry_date', '>=', Carbon::now()->format('Y-m-d'));
        //                    })
        //                    // Do not get overdue items by load_hydrostatic_test_due
        //                    ->where(function($subQuery) {
        //                        $subQuery->whereNull('euc_box_spares.load_hydrostatic_test_due')
        //                            ->orWhere('euc_box_spares.load_hydrostatic_test_due', '>=', Carbon::now()->format('Y-m-d'));
        //                    });
        //            })
        //            ->select('spares.*', 'euc_box_spares.*', 'euc_boxes.order as euc_box_order')
        //            ->get();

        $sparesInBinNotOverdue = $sparesInBin->filter(function ($item, $key) {
            if ($item->configures->count()) {
                $now = Carbon::now();
                $configure = $item->configures->first();

                if ($configure->has_calibration_due && $configure->calibration_due) {
                    return Carbon::parse($configure->calibration_due)->greaterThanOrEqualTo($now);
                }

                if ($configure->has_expiry_date && $configure->expiry_date) {
                    return Carbon::parse($configure->expiry_date)->greaterThanOrEqualTo($now);
                }

                if ($configure->has_load_hydrostatic_test_due && $configure->load_hydrostatic_test_due) {
                    return Carbon::parse($configure->load_hydrostatic_test_due)->greaterThanOrEqualTo($now);
                }
            };

            return true;
        });

        $items = [
            'spare_bins' => $sparesInBinNotOverdue->values(),
            'spare_eucs' => $eucboxes
        ];

        // Param from tablet
        $isStrict = Arr::get($params, 'is_strict');
        $type = Arr::get($params, 'type');
        if ($isStrict) {
            unset($items['spare_eucs']);
        }
        //        if ($isStrict && $type == Consts::SPARE_TYPE_EUC) {
        //            unset($items['spare_bins']);
        //        }
        //        if ($isStrict && $type !== Consts::SPARE_TYPE_EUC) {
        //            unset($items['spare_eucs']);
        //        }
        return $items;
    }

    public function getSpareInfo($spareId)
    {
        $spare = Spare::find($spareId);
        return $spare;
    }

    public function createSpare($params)
    {
        $spare = new Spare;

        $imageUrl = array_get($params, 'url', null);
        $params['url'] = !empty($imageUrl) && file_exists($imageUrl)
            ? Utils::saveFileToStorage($imageUrl, 'spares', "spare_{$params['part_no']}")
            : $imageUrl;

        $spare = $this->saveData($spare, $params);

        $this->saveUserAccessing($spare, $params);

        return $spare;
    }

    public function updateSpare($params)
    {
        $spare = Spare::find($params['id']);

        $imageUrl = array_get($params, 'url', null);

        $params['url'] = !empty($imageUrl) && file_exists($imageUrl)
            ? Utils::saveFileToStorage($imageUrl, 'spares', "spare_{$params['part_no']}")
            : $imageUrl;

        $spare = $this->saveData($spare, $params);

        $this->saveUserAccessing($spare, $params);

        return $spare;
    }

    private function saveUserAccessing($spare, $params)
    {
        $userAccesses = array_get($params, 'user_access', []);
        if (!$userAccesses) {
            return false;
        }

        $data = [];
        foreach (json_decode($userAccesses, true) as $value) {
            $data[] = ['spare_id' => $spare->id, 'role' => $value];
        }

        $this->saveManyData(
            $data,
            UserAccessingSpare::class,
            ['key' => 'spare_id', 'value' => $spare->id],
            ['spare_id', 'role']
        );

        return true;
    }

    public function deleteSpare($spareId)
    {
        SpareUtils::canDelete($spareId);

        $spare = Spare::find($spareId);

        $spare->delete();

        return $spare;
    }

    public function importSpares($fileUpload)
    {
        Utils::saveFileToStorage($fileUpload, 'spares-importing');

        ini_set('memory_limit', '-1');

        Excel::queueImport(new SparesImport(), $fileUpload);
        return true;
    }

    public function getBins($params)
    {
        return Bin::with('configures', 'spares')
            ->join('shelfs', 'shelfs.id', 'bins.shelf_id')
            ->leftJoin('clusters', 'clusters.id', 'shelfs.cluster_id')
            ->leftJoin('bin_configures', 'bin_configures.bin_id', 'bins.id')
            ->when(!empty($params['bin_id']), function ($query) use ($params) {
                $query->where('id', $params['bin_id']);
            })
            ->when(!empty($params['cluster_id']), function ($query) use ($params) {
                $query->where('shelfs.cluster_id', $params['cluster_id']);
            })
            ->when(!empty($params['shelf_id']), function ($query) use ($params) {
                $query->where('shelf_id', $params['shelf_id']);
            })
            ->when(!empty($params['row']), function ($query) use ($params) {
                $query->where('row', $params['row']);
            })
            ->when(!empty($params['bin']), function ($query) use ($params) {
                $query->where('bin', $params['bin']);
            })
            ->when(!empty($params['status']), function ($query) use ($params) {
                // Where column is_failed = 1
                if ($params['status'] == 'is_failed') {
                    $query->where('is_failed', 1);
                }
                // Where column status
                else {
                    $query->where('status', $params['status']);
                }
            })
            ->when(!is_null(Arr::get($params, 'is_drawer')), function ($query) use ($params) {
                $query->where('is_drawer', $params['is_drawer']);
            })
            ->when(!empty($params['search_drawer']), function ($query) use ($params) {
                $searchKey = Utils::escapeLike($params['search_drawer']);
                $query->where(function ($subQuery) use ($searchKey) {
                    $subQuery->where('bins.drawer_name', 'LIKE', "%{$searchKey}%");
                });
            })
            ->when(!empty($params['search_key']), function ($query) use ($params) {
                $searchKey = Utils::escapeLike($params['search_key']);

                $query->where(function ($subQuery) use ($searchKey) {
                    $subQuery->where('spares.name', 'LIKE', "%{$searchKey}%")
                        ->orWhere('spares.part_no', 'LIKE', "%{$searchKey}%")
                        ->orWhere('spares.material_no', 'LIKE', "%{$searchKey}%")
                        ->orWhere('bins.drawer_name', 'LIKE', "%{$searchKey}%")
                        ->orWhere('bin_configures.serial_no', 'LIKE', "%{$searchKey}%");
                });
            })
            ->when(
                !empty($params['sort']) && !empty($params['sort_type']),
                function ($query) use ($params) {
                    return $query->orderBy($params['sort'], $params['sort_type']);
                },
                function ($query) {
                    return $query->orderBy('row', 'asc')
                        ->orderBy('bin', 'asc');
                }
            )
            ->select('clusters.*', 'bins.*', 'shelfs.name as shelf_name')
            ->addSelect('bins.bin as bin_name')
            ->addSelect('clusters.name as cluster_name', 'clusters.id as cluster_id', 'clusters.code', 'clusters.is_rfid')
            ->addSelect(DB::raw(
                '(CASE WHEN bins.quantity <= 0 THEN "Unavailable"
                    WHEN bins.status = "' . Consts::BIN_STATUS_UNASSIGNED . '" THEN "Unassigned"
                    ELSE "Available" END) AS status_lbl'
            ))->paginate(array_get($params, 'limit', Consts::DEFAULT_PER_PAGE));
    }
    public function getBinId($params)
    {
        $bin = Bin::findOrFail($params);
        $bin->spares;
        $bin->configures;
        return $bin;
    }
    public function getBinsSummary($params)
    {
        $paginator = $this->getBins($params);

        $spareIds = $paginator->getCollection()
            ->pluck('spare_id')
            ->unique()
            ->toArray();

        $loanHistories = IssueCard::whereIn('id', $spareIds)
            ->get()
            ->mapWithKeys(function ($record) {
                return [$record->spare_id => true];
            });

        $now = now()->startOfDay();
        $paginator->getCollection()->transform(function ($record) use ($now, $loanHistories) {
            $binConfigure = collect($record->configures)->first();

            $expiryDate = $binConfigure && $binConfigure->has_expiry_date
                ? Carbon::parse($binConfigure->expiry_date)
                : now();

            $diffDays = $now->diffInDays($expiryDate);

            $record->is_onloan = !empty($loanHistories[$record->spare_id]);
            $record->is_expired = $expiryDate->lt($now);
            $record->is_expiring_in_30days = $diffDays > 0 && $diffDays <= 30;

            return $record;
        });

        return $paginator;
    }

    public function patchBin($params)
    {
        $bin = Bin::findOrFail($params['id']);
        $bin->is_drawer = Arr::get($params, 'is_drawer', $bin->is_drawer);
        $bin->is_locked = Arr::get($params, 'is_locked', $bin->is_locked);

        return $this->saveData($bin, $params);
    }

    public function updateBin($paramss)
    {
        $bin = Bin::with('configures', 'spares')->find($paramss[0]['id']);
        $spareIds = [];
        $configureIds = [];
        foreach ($paramss as $index => $params) {
           
            $spareId = array_get($params, 'spare_id', null);
            $spareIds[] = $spareId;
            if ($bin->spare_id != $spareId) {
                ReturnSpare::query()
                    ->where('bin_id', $bin->id)
                    ->where('state', '!=', Consts::RETURN_SPARE_STATE_WORKING)
                    ->update([
                        'write_off' => Consts::TRUE
                    ]);
            }

            if (!Arr::get($params, 'critical')) {
                $bin->critical = 0;
            }

            if (!$bin->quantity_oh) {
                $bin->quantity_oh = $params['quantity'];
            } else {
                $quantityOh = BigNumber::new($bin->quantity_oh)
                    ->add($params['quantity'])
                    ->sub($bin->quantity)
                    ->toString();

                $bin->quantity_oh = $quantityOh < 0 ? $params['quantity'] : $quantityOh;
            }

            $bin->spare_id = json_encode($params['spare_id']);
            $bin->status = !!$spareId ? Consts::BIN_STATUS_ASSIGNED : Consts::BIN_STATUS_UNASSIGNED;

            $bin->save();
            
            if (isset($params['id'])) {
                $configure = BinConfigure::find($params['id']);
                if (!empty($configure)) {
                    $configure = new BinConfigure;
                    $configure->order = $index + 1;
                    $configure->bin_id = $bin->id;
                   
                }else{
                    $configure = new BinConfigure;
                    $configure->order = $index + 1;
                    $configure->bin_id = $bin->id;
                }
            } else {
                $configure = new BinConfigure;
                $configure->order = $index + 1;
                $configure->bin_id = $bin->id;
            }
            $configure->charge_time = $params['charge_time'];
            $configure->calibration_due = $params['calibration_due'];
            $configure->expiry_date = $params['expiry_date'];
            $configure->load_hydrostatic_test_due = $params['load_hydrostatic_test_due'];
            $configure->spare_id = $params['spare_id'];
            $configure->save();
            $configureIds[] = $configure->id;
            
        }
        BinConfigure::where('bin_id', $bin->id)
                ->whereNotIn('id', $configureIds)
                ->delete();
        $bin->spares()->sync($spareIds);
        return $bin->refresh();
    }
    // private function saveBinConfigures($bin, $params)
    // {
    //     var_dump($params);die();
    //     // $binConfigures = $bin->configures;
    //     // foreach ($binConfigures as $index => $configure) {
    //     //     if ($configure) {
    //     //         $value = [
    //     //             'order' => $index + 1,
    //     //             'bin_id' => $bin->id,
    //     //             'charge_time' => empty($configure['has_charge_time']) ? null : $configure['charge_time'],
    //     //             'calibration_due' => empty($configure['has_calibration_due']) ? null : $configure['calibration_due'],
    //     //             'expiry_date' => empty($configure['has_expiry_date']) ? null : $configure['expiry_date'],
    //     //             'load_hydrostatic_test_due' => empty($configure['has_load_hydrostatic_test_due']) ? null : $configure['load_hydrostatic_test_due'],
    //     //         ];
    //     //         $configure->fill($value); // Cập nhật $configure với các giá trị mới
    //     //         $configure->save(); 
    //     //     }
    //     // }
    // }

    private function saveBinConfigures($bin, $params)
    {
        $configureIds = [];
        if (isset($params['id'])) {
            $configure = BinConfigure::find($params['id']);
            if (!empty($configure)) {
                $configure = new BinConfigure;
                $value['order'] = $index + 1;
                $value['bin_id'] = $bin->id;
            }
        } else {
            $configure = new BinConfigure;
            $value['order'] = $index + 1;
            $value['bin_id'] = $bin->id;
        }
        $value['charge_time'] = empty($value['has_charge_time']) ? null : $value['charge_time'];
        $value['calibration_due'] = empty($value['has_calibration_due']) ? null : $value['calibration_due'];
        $value['expiry_date'] = empty($value['has_expiry_date']) ? null : $value['expiry_date'];
        $value['load_hydrostatic_test_due'] = empty($value['has_load_hydrostatic_test_due']) ? null : $value['load_hydrostatic_test_due'];

        $this->saveData($configure, $value);

        $configureIds[] = $configure->id;
        BinConfigure::where('bin_id', $bin->id)
            ->whereNotIn('id', $configureIds)
            ->delete();
        $configureIds = [];
        $configures = array_get($params, 'configures', []);
        foreach ($configures as $index => $value) {
            $configure = null;

            if (empty($value['id'])) {
                $configure = new BinConfigure;
                $value['order'] = $index + 1;
                $value['bin_id'] = $bin->id;
            } else {
                $configure = BinConfigure::find($value['id']);
                if (!$configure) {
                    $configure = new BinConfigure;
                    $value['order'] = $index + 1;
                    $value['bin_id'] = $bin->id;
                }
            }

            $value['charge_time'] = empty($value['has_charge_time']) ? null : $value['charge_time'];
            $value['calibration_due'] = empty($value['has_calibration_due']) ? null : $value['calibration_due'];
            $value['expiry_date'] = empty($value['has_expiry_date']) ? null : $value['expiry_date'];
            $value['load_hydrostatic_test_due'] = empty($value['has_load_hydrostatic_test_due']) ? null : $value['load_hydrostatic_test_due'];

            $this->saveData($configure, $value);

            $configureIds[] = $configure->id;
        }

        BinConfigure::where('bin_id', $bin->id)
            ->whereNotIn('id', $configureIds)
            ->delete();

        // if ($bin->is_drawer) {
        //     return;
        // }

        // $configures = array_get($params, 'configures', []);

        // // hardcode only have 1 row
        // if (!empty($configures)) {
        //     $first = collect($configures)->first();
        //     $configures = [$first];
        // }

        // $configureIds = [];

        // foreach ($configures as $index => $value) {
        //     $configure = null;

        //     if (empty($value['id'])) {
        //         $configure = new BinConfigure;
        //         $value['order'] = $index + 1;
        //         $value['bin_id'] = $bin->id;

        //     } else {
        //         $configure = BinConfigure::findOrFail($value['id']);
        //     }

        //     $value['charge_time'] = empty($value['has_charge_time']) ? null : $value['charge_time'];
        //     $value['calibration_due'] = empty($value['has_calibration_due']) ? null : $value['calibration_due'];
        //     $value['expiry_date'] = empty($value['has_expiry_date']) ? null : $value['expiry_date'];
        //     $value['load_hydrostatic_test_due'] = empty($value['has_load_hydrostatic_test_due']) ? null : $value['load_hydrostatic_test_due'];

        //     $configure = BinConfigure::create($value);

        //     $configureIds[] = $configure->id;
        // }

        // BinConfigure::where('bin_id', $bin->id)
        //     ->whereNotIn('id', $configureIds)
        //     ->delete();
    }

    public function unassignedBin($binId)
    {
        $binIsIssuing = $this->checkBinIsIssuing($binId);
        if ($binIsIssuing) {
            throw ValidationException::withMessages(
                [
                    'bin' => ['Can not remove this spare because it is currently being issuing!']
                ]
            );
        }

        $bin = Bin::find($binId);

        $bin->spare_id  = null;
        $bin->quantity  = null;
        $bin->quantity_oh = null;
        $bin->min       = null;
        $bin->max       = null;
        $bin->critical  = null;
        $bin->description  = null;
        $bin->status    = Consts::BIN_STATUS_UNASSIGNED;
        $bin->save();

        BinConfigure::where('bin_id', $bin->id)
            ->delete();

        return $bin;
    }

    private function checkBinIsIssuing($binId): bool
    {
        $binIsIssuing = IssueCard::query()
            ->where('bin_id', $binId)
            ->where(function ($query) {
                return $query->whereNull('returned')
                    ->orWhereNotIn('returned', [Consts::RETURNED_TYPE_LINK_MO, Consts::RETURNED_TYPE_ALL]);
            })
            ->count();

        return $binIsIssuing > 0;
    }

    private function getJobCardBuilder($params)
    {
        return JobCard::join('vehicles', 'vehicles.id', 'job_cards.vehicle_id')
            ->when(!empty($params['job_card_id']), function ($query) use ($params) {
                $query->where('job_cards.id', $params['job_card_id']);
            })
            ->when(!empty($params['card_num']), function ($query) use ($params) {
                $query->where('job_cards.card_num', $params['card_num']);
            })
            ->when(isset($params['is_active']), function ($query) use ($params) {
                $query->where('job_cards.is_active', $params['is_active']);
            })
            ->when(isset($params['search_key']), function ($query) use ($params) {
                $searchKey = Utils::escapeLike($params['search_key']);
                $query->where(function ($subQuery) use ($searchKey) {
                    $subQuery->where('job_cards.card_num', 'LIKE', "%{$searchKey}%");
                    //                        ->orWhere('job_cards.wo', 'LIKE', "%{$searchKey}%")
                    //                        ->orWhere('job_cards.platform', 'LIKE', "%{$searchKey}%");
                });
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
            ->select('job_cards.*', 'vehicles.vehicle_num as vehicle_num');
    }

    public function getJobCards($params)
    {
        return $this->getJobCardBuilder($params)
            ->paginate(array_get($params, 'limit', Consts::DEFAULT_PER_PAGE));
    }

    public function getJobCardInfo($jobCardId)
    {
        $params = [
            'job_card_id' => $jobCardId
        ];
        return $this->getJobCardBuilder($params)->get()->first();
    }

    public function getJobCardByCardNo($params)
    {
        return $this->getJobCardBuilder($params)->get()->first();
    }

    public function createJobCard($params)
    {
        // Check job card is exists
        $cardNum = Arr::get($params, 'card_num');
        if ($cardNum) {
            $jobCard = JobCard::where('card_num', $cardNum)->first();
            // Job card was inactivated
            if ($jobCard && $jobCard->is_active == 0) {
                throw new Exception('Job Card can not create, because this Job Card is closed');
            }
        }

        $jobCard = new JobCard;

        $params['wo'] = $cardNum;
        $jobCard = $this->saveData($jobCard, $params);

        $params = [
            'job_card_id' => $jobCard->id
        ];
        return $this->getJobCardBuilder($params)->get()->first();
    }

    public function updateJobCard($params)
    {
        $jobCard = JobCard::find($params['id']);

        $jobCard = $this->saveData($jobCard, $params);

        $params = [
            'job_card_id' => $jobCard->id
        ];
        return $this->getJobCardBuilder($params)->get()->first();
    }

    public function deleteJobCard($jobCardId)
    {
        $jobCard = JobCard::find($jobCardId);

        $jobCard->delete();

        return $jobCard;
    }

    public function closedJobCard($jobCardId)
    {
        $jobCard = JobCard::find($jobCardId);

        $jobCard->fill(['is_active' => false])->save();

        return $jobCard;
    }

    public function getVehicleTypes($params)
    {
        return VehicleType::when(
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

    public function createVehicleType($params)
    {
        $size = VehicleType::all()->count();
        /*if ($size === 3) {
            throw new Exception('Only have 3 vehicle types availability');
        }*/

        $vehicleType = new VehicleType;

        $vehicleType = $this->saveData($vehicleType, $params);

        return $vehicleType;
    }

    public function updateVehicleType($params)
    {
        $vehicleType = VehicleType::find($params['id']);

        $vehicleType = $this->saveData($vehicleType, $params);

        return $vehicleType;
    }

    public function deleteVehicleType($vehicleTypeId)
    {
        $vehicleType = VehicleType::find($vehicleTypeId);

        $vehicleType->delete();

        return $vehicleType;
    }

    private function getVehicleBuilder($params)
    {
        $createdDates = array_get($params, 'created_dates', null);
        $statusList = array_get($params, 'status', null);

        return Vehicle::join('vehicle_types', 'vehicle_types.id', 'vehicles.vehicle_type_id')
            // ->when(!empty($params['is_opened']), function ($query) {
            //     $query->where('vehicles.status', Consts::VEHICLE_STATUS_OPENED);
            // })
            ->when(
                empty($statusList),
                function ($query) {
                    $query->where('vehicles.status', Consts::VEHICLE_STATUS_OPENED);
                },
                function ($query) use ($statusList) {
                    $query->whereIn('vehicles.status', $statusList);
                }
            )
            ->when(!empty($params['vehicle_id']), function ($query) use ($params) {
                $query->where('vehicles.id', $params['vehicle_id']);
            })
            ->when($createdDates, function ($query) use ($createdDates) {
                return $this->queryRange($query, $createdDates, 'vehicles.created_at');
            })
            ->when(!empty($params['search_key']), function ($query) use ($params) {
                $searchKey = Utils::escapeLike($params['search_key']);
                $query->where(function ($subQuery) use ($searchKey) {
                    $subQuery->where('vehicles.vehicle_num', 'LIKE', "%{$searchKey}%")
                        ->orWhere('vehicles.variant', 'LIKE', "%{$searchKey}%")
                        ->orWhere('vehicles.unit', 'LIKE', "%{$searchKey}%")
                        ->orWhere('vehicle_types.name', 'LIKE', "%{$searchKey}%");
                });
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
            ->select('vehicles.*', 'vehicle_types.name as vehicle_type_name', 'vehicle_types.name as variant'); // vehicle tpe is variant
    }

    public function getVehicles($params)
    {
        return $this->getVehicleBuilder($params)
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

    public function getVehicleStatistic($params)
    {
        $cacheKey = 'vehicles-in-yearly';

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $now = now();
        $params = array_merge($params, [
            'no_pagination' => Consts::TRUE,
            'created_dates' => json_encode([
                'start' => $now->copy()->startOfYear()->format('Y-m-d'),
                'end'   => $now->copy()->endOfYear()->format('Y-m-d'),
            ])
        ]);

        $data = $this->getVehicles($params);

        Cache::put($cacheKey, $data, 60 * 1); // 1 minutes

        return $data;
    }

    public function getVehicleStatisticMonthly($params)
    {
        $cacheKey = 'vehicles-monthly';

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $now = now();
        $params = [
            'no_pagination' => Consts::TRUE,
            'created_dates' => json_encode([
                'start' => $now->copy()->startOfYear()->format('Y-m-d'),
                'end'   => $now->copy()->endOfYear()->format('Y-m-d'),
            ])
        ];

        $data = $this->getVehicles($params);

        Cache::put($cacheKey, $data, 60 * 1); // 1 minutes

        return $data;
    }

    public function createVehicle($params)
    {
        $exists = Vehicle::where('vehicle_num', $params['vehicle_num'])
            ->where('status', Consts::VEHICLE_STATUS_OPENED)
            ->exists();

        if ($exists) {
            throw new Exception("Cannot create a new job while another vehicle job hasn't completed yet");
        }

        $vehicle = new Vehicle;

        $params['status'] = $this->getVehicleStatus($params);
        $vehicle = $this->saveData($vehicle, $params);

        $vehicle = $this->addNewVehicleIfNeed($vehicle);

        $params = [
            'vehicle_id' => $vehicle->id
        ];
        return $this->getVehicleBuilder($params)->get()->first();
    }

    public function updateVehicle($params)
    {
        $vehicle = Vehicle::find($params['id']);

        $params['status'] = $this->getVehicleStatus($params);
        $vehicle = $this->saveData($vehicle, $params);

        $vehicle = $this->addNewVehicleIfNeed($vehicle);

        $params = [
            'vehicle_id' => $vehicle->id
        ];
        return $this->getVehicleBuilder($params)->get()->first();
    }

    public function revertVehicle($params)
    {
        $vehicle = Vehicle::where('id', $params['id'])
            ->where('status', Consts::VEHICLE_STATUS_COMPLETING)
            ->first();

        if (!$vehicle) {
            return;
        }

        Vehicle::where('vehicle_num', $vehicle->vehicle_num)
            ->where('id', '<>', $vehicle->id)
            ->where('vehicle_type_id', $vehicle->vehicle_type_id)
            ->where('status', Consts::VEHICLE_STATUS_OPENED)
            ->delete();

        $vehicle->status = Consts::VEHICLE_STATUS_OPENED;
        $vehicle->completion_date_24_months = array_get($params, 'completion_date_24_months', null);
        $vehicle->save();

        $params = [
            'vehicle_id' => $vehicle->id
        ];
        return $this->getVehicleBuilder($params)->get()->first();
    }

    private function addNewVehicleIfNeed($vehicle)
    {
        $statusList = [
            Consts::VEHICLE_STATUS_COMPLETING,
            Consts::VEHICLE_STATUS_COMPLETED
        ];
        if (!in_array($vehicle->status, $statusList)) {
            return $vehicle;
        }

        $calculateDate = function ($value, $number) {
            return $value ? Carbon::parse($value)->addMonths($number)->format('Y-m-d') : null;
        };

        $newVehicle = $vehicle->replicate();
        $newVehicle->status = Consts::VEHICLE_STATUS_OPENED;
        $newVehicle->last_point_servicing = $vehicle->completion_date_24_months;
        $newVehicle->schedule_6_months = $calculateDate($newVehicle->last_point_servicing, 6);
        $newVehicle->schedule_12_months = $calculateDate($newVehicle->last_point_servicing, 12);
        $newVehicle->schedule_18_months = $calculateDate($newVehicle->last_point_servicing, 18);
        $newVehicle->schedule_24_months = $calculateDate($newVehicle->last_point_servicing, 24);
        $newVehicle->completion_date_6_months = null;
        $newVehicle->completion_date_12_months = null;
        $newVehicle->completion_date_18_months = null;
        $newVehicle->completion_date_24_months = null;

        $newVehicle->save();

        $vehicles = Vehicle::where('vehicle_num', $vehicle->vehicle_num)
            ->where('id', '<>', $vehicle->id)
            ->where('vehicle_type_id', $vehicle->vehicle_type_id)
            ->where('status', Consts::VEHICLE_STATUS_COMPLETING)
            ->get();

        foreach ($vehicles as $key => $record) {
            $record->status = Consts::VEHICLE_STATUS_COMPLETED;
            $record->save();
        }

        return $newVehicle;
    }

    private function getVehicleStatus($data)
    {
        $isCompleted = !empty($data['completion_date_6_months'])
            && !empty($data['completion_date_12_months'])
            && !empty($data['completion_date_18_months'])
            && !empty($data['completion_date_24_months']);

        return $isCompleted ? Consts::VEHICLE_STATUS_COMPLETING : Consts::VEHICLE_STATUS_OPENED;
    }

    public function deleteVehicle($vehicleId)
    {
        $vehicle = Vehicle::find($vehicleId);

        $vehicle->delete();

        return $vehicle;
    }

    private function getEucListBuilder($params)
    {
        return EucBox::with('spares')
            ->join('vehicle_types', 'vehicle_types.id', 'euc_boxes.vehicle_type_id')
            ->when(!empty($params['euc_list_id']), function ($query) use ($params) {
                $query->where('euc_boxes.id', $params['euc_list_id']);
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
            ->select('euc_boxes.*', 'vehicle_types.name as vehicle_type_name');
    }

    public function getEucList($params)
    {
        return $this->getEucListBuilder($params)
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

    public function createEuc($params)
    {
        $euc = new EucBox;

        $euc = $this->saveData($euc, $params);

        $this->saveEucBoxSpares($euc, $params);

        $params = [
            'euc_list_id' => $euc->id
        ];
        return $this->getEucListBuilder($params)->get()->first();
    }

    public function createOnlyEuc($params)
    {
        $euc = new EucBox;

        return $this->saveData($euc, $params);
    }

    public function updateEuc($params)
    {
        $euc = EucBox::find($params['id']);

        $euc = $this->saveData($euc, $params);

        $this->saveEucBoxSpares($euc, $params);

        $params = [
            'euc_list_id' => $euc->id
        ];
        return $this->getEucListBuilder($params)->get()->first();
    }

    public function updateItemsEuc($eucBoxId, $params)
    {
        $euc = EucBox::find($eucBoxId);

        $this->saveEucBoxSpares($euc, $params);

        $params = [
            'euc_list_id' => $euc->id
        ];
        return $this->getEucListBuilder($params)->get()->first();
    }

    public function updateOnlyEuc($params)
    {
        $euc = EucBox::find($params['id']);

        return $this->saveData($euc, $params);
    }

    private function saveEucBoxSpares($euc, $params)
    {
        $paramSpares = array_get($params, 'spares', []);
        if (count($paramSpares)) {
            $serialNumbers = array_column($paramSpares, 'serial_no');
            $spareIds = array_column($paramSpares, 'spare_id');
            $serialNumberUnique = array_unique($serialNumbers);
            if (count($serialNumberUnique) != count($serialNumbers)) {
                throw new Exception('Duplicate Serial No');
            }

            $uecBoxSpares = EucBoxSpare::query()
                ->whereIn('serial_no', $serialNumbers)
                ->where('euc_box_id', '!=', $euc->id)
                ->whereNotIn('spare_id', $spareIds)
                ->get();
            $collectUecBoxSpares = collect($uecBoxSpares);

            collect($paramSpares)->map(function ($record) use ($euc, $collectUecBoxSpares) {
                $collectUecBoxSpares->filter(function ($uecBoxSpare) use ($record) {
                    if (
                        $uecBoxSpare->spare_id != $record['spare_id'] &&
                        $uecBoxSpare->serial_no == $record['serial_no']
                    ) {
                        throw new Exception('Serial No: ' . $record['serial_no'] . ' already exists');
                    }
                });
            });
        }

        $spares = collect(array_get($params, 'spares', []))
            ->map(function ($record) use ($euc) {
                $record['euc_box_id'] = $euc->id;

                $quantityReplenish = array_get($record, 'quantity_replenish', 0);
                $quantityOh = array_get($record, 'quantity_oh', 0);

                $record['quantity_oh'] = $quantityOh + $quantityReplenish;
                $record['quantity_replenish'] = 0;

                return $record;
            })
            ->toArray();

        $this->saveManyData(
            $spares,
            EucBoxSpare::class,
            ['key' => 'euc_box_id', 'value' => $euc->id],
            ['euc_box_id', 'spare_id']
        );

        $this->saveReplenishEucBox($euc, $spares);

        return true;
    }

    private function saveReplenishEucBox($euc, $spares)
    {
        foreach ($spares as $spare) {
            ReplenishEucBox::create(
                [
                    'euc_box_id' => $euc->id,
                    'spare_id' => Arr::get($spare, 'spare_id'),
                    'requester_id' => Arr::get($spare, 'requester_id'),
                    'receiver_id' => Auth::id(),
                    'is_confirmed' => 0,
                ]
            )->save();
            //            ReplenishEucBox::firstOrCreate(
            //                [
            //                    'euc_box_id' => $euc->id,
            //                    'spare_id' => Arr::get($spare, 'spare_id'),
            //                    'requester_id' => Arr::get($spare, 'requester_id'),
            //                    'receiver_id' => Auth::id(),
            //                ],
            //                [
            //                    'is_confirmed' => 0,
            //                ],
            //            );
        }
    }

    public function deleteEucList($eucListId)
    {
        $euc = EucBox::find($eucListId);
        $euc->delete();

        EucBoxSpare::where('euc_box_id', $euc->id)->delete();

        return $euc;
    }

    public function getPolManagements($params)
    {
        $noPagination = array_get($params, 'no_pagination', Consts::FALSE);

        $rawData = PolManagement::when(!empty($params['search_key']), function ($query) use ($params) {
            $searchKey = Utils::escapeLike($params['search_key']);
            $query->where(function ($subQuery) use ($searchKey) {
                $subQuery
                    ->where('material_number', 'LIKE', "%{$searchKey}%")
                    //                        ->orwhere('card_number', 'LIKE', "%{$searchKey}%")
                    ->orWhere('type', 'LIKE', "%{$searchKey}%");
            });
        })
            ->when(!empty($params['types']), function ($query) use ($params) {
                return $query->whereIn('type', $params['types']);
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
                $noPagination,
                function ($query) {
                    return $query->get();
                },
                function ($query) use ($params) {
                    return $query->paginate(array_get($params, 'limit', Consts::DEFAULT_PER_PAGE));
                }
            );

        $transfrom = function ($record) {
            $record->quantity_oh = BigNumber::new($record->received_quantity)
                ->sub($record->issued_quantity)
                ->toString();
            return $record;
        };

        if ($noPagination) {
            return $rawData->transform(function ($record) use ($transfrom) {
                return $transfrom($record);
            });
        }

        $rawData->getCollection()->transform(function ($record) use ($transfrom) {
            return $transfrom($record);
        });

        return $rawData;
    }

    public function getPolHistories($params)
    {
        $noPagination = array_get($params, 'no_pagination', Consts::FALSE);

        return PolHistory::join('pol_managements', 'pol_histories.pol_id', 'pol_managements.id')
            ->leftJoin('users as issuer', 'pol_histories.issuer_id', 'issuer.id')
            ->leftJoin('users as receiver', 'pol_histories.receiver_id', 'receiver.id')
            ->leftJoin('users as requester', 'pol_histories.requester_id', 'requester.id')
            ->leftJoin('users as receiver_requested', 'pol_histories.receiver_requested_id', 'receiver_requested.id')
            ->when(!empty($params['search_key']), function ($query) use ($params) {
                $searchKey = Utils::escapeLike($params['search_key']);
                $query->where(function ($subQuery) use ($searchKey) {
                    $subQuery->where('pol_managements.card_number', 'LIKE', "%{$searchKey}%")
                        ->orWhere('pol_managements.material_number', 'LIKE', "%{$searchKey}%")
                        ->orWhere('pol_managements.type', 'LIKE', "%{$searchKey}%");
                });
            })
            ->when(
                !empty($params['sort']) && !empty($params['sort_type']),
                function ($query) use ($params) {
                    return $query->orderBy($params['sort'], $params['sort_type']);
                },
                function ($query) {
                    return $query->orderBy('pol_histories.updated_at', 'desc');
                }
            )
            ->select(
                'pol_managements.*',
                'pol_managements.type as item_type',
                'pol_histories.*',
                'issuer.name as issuer_name',
                'receiver.name as receiver_name',
                'requester.name as requester_name',
                'receiver_requested.name as receiver_requested_name'
            )
            ->when(
                $noPagination,
                function ($query) {
                    return $query->get();
                },
                function ($query) use ($params) {
                    return $query->paginate(array_get($params, 'limit', Consts::DEFAULT_PER_PAGE));
                }
            );
    }

    public function getPolManagementInfo($id)
    {
        return PolManagement::find($id);
    }

    public function createPolManagement($params)
    {
        $pol = new PolManagement;

        $pol->card_number = array_get($params, 'card_number');
        $pol->material_number = array_get($params, 'material_number');
        $pol->type = array_get($params, 'type');
        // $pol->request_date = now();
        // $pol->request_quantity = array_get($params, 'request_quantity');
        // $pol->received_date = now();
        // $pol->received_quantity = array_get($params, 'received_quantity');
        $pol->expiry_date = array_get($params, 'expiry_date');
        $pol->purpose_use = array_get($params, 'purpose_use');
        $pol->description = array_get($params, 'description');
        $pol->status = Consts::POL_STATUS_NA;
        $pol->request_by = Auth::user()->name;
        $pol->auditor = Auth::user()->name;

        $pol->save();

        return $pol;
    }

    public function updatePolManagement($params)
    {
        $pol = PolManagement::find($params['id']);

        $oldPol = json_decode(json_encode($pol), true);

        $pol->card_number = array_get($params, 'card_number', $pol->card_number);
        $pol->material_number = array_get($params, 'material_number', $pol->material_number);
        $pol->type = array_get($params, 'type', $pol->type);
        $pol->expiry_date = array_get($params, 'expiry_date', $pol->expiry_date);
        $pol->purpose_use = array_get($params, 'purpose_use', $pol->purpose_use);
        $pol->description = array_get($params, 'description', $pol->description);
        $pol->auditor = array_get($params, 'auditor', $pol->auditor);

        // $requestQty = array_get($params, 'request_quantity', null);
        // if ($requestQty) {
        //     $pol->request_date = now();
        //     $pol->request_quantity = $requestQty;
        //     $pol->request_by = Auth::user()->name;
        // }

        // $receivedQty = array_get($params, 'received_quantity', null);
        // $receivedComparator = BigNumber::new($receivedQty)->comp($requestQty);
        // if ($receivedQty) {
        //     $pol->received_date = now();
        //     $pol->received_quantity = $receivedQty;
        //     // $pol->status = $receivedComparator < 0 ? Consts::POL_STATUS_RECEIVING : Consts::POL_STATUS_RECEIVED;
        //     $pol->status = Consts::POL_STATUS_RECEIVED;
        // }

        // $issuedQty = array_get($params, 'issued_quantity', null);
        // $smaller = BigNumber::new($issuedQty)->comp($receivedQty) < 0;
        // if ($receivedQty && $issuedQty) {
        //     $pol->issued_date = now();
        //     $pol->issued_quantity = $issuedQty;
        //     $pol->status = $smaller ? Consts::POL_STATUS_ISSUING : Consts::POL_STATUS_ISSUED;

        //     // fullfilled
        //     // if ($receivedComparator === 0) {
        //     //     $pol->status = $smaller ? Consts::POL_STATUS_ISSUING : Consts::POL_STATUS_ISSUED;
        //     // }
        // }

        $pol->save();

        return $pol;
    }

    public function deletePolManagements($polIds)
    {
        $pols = PolManagement::whereIn('id', $polIds)->get();

        foreach ($pols as $pol) {
            $oldPol = json_decode(json_encode($pol), true);

            $pol->delete();
        }

        return true;
    }

    public function getAdminCabinetData($params)
    {
        return Bin::join('bin_configures', 'bin_configures.bin_id', 'bins.id')
            ->join('spares', 'spares.id', 'bins.spare_id')
            ->join('shelfs', 'shelfs.id', 'bins.shelf_id')
            // ->where('bins.status', Consts::BIN_STATUS_ASSIGNED)
            ->when(!empty($params['cabinet']), function ($query) use ($params) {
                $query->where('shelfs.name', $params['cabinet']);
            })
            ->select(
                'spares.name as item_name',
                'spares.material_no as mpn',
                'spares.part_no as ssn',
                'bins.quantity_oh as qyt_oh',
                'bin_configures.serial_no as serial',
                'bin_configures.calibration_due as calibration_due',
                'bin_configures.charge_time as min_charge_time',
                'bin_configures.expiry_date as expire_date',
                DB::raw('(CASE WHEN bins.status = "' . Consts::BIN_STATUS_ASSIGNED . '" THEN 1 ELSE 0 END) AS available_state'),
                DB::raw('CONCAT(shelfs.name," - ",bins.row, " - ",bins.bin) as item_position'),
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

    public function issuePol($params)
    {
        $data = array_get($params, 'pols', []);

        foreach ($data as $value) {
            $issuedPol = PolHistory::create([
                'pol_id'        => $value['id'],
                'type'          => Consts::POL_HISTORY_TYPE_ISSUE,
                'quantity'      => $value['quantity'],
                'issuer_id'     => Auth::id(),
                'receiver_id'   => $value['receiver_id']
            ]);

            $pol = PolManagement::findOrFail($issuedPol->pol_id);
            $pol->issued_quantity = BigNumber::new($pol->issued_quantity ?? 0)
                ->add($issuedPol->quantity)
                ->toString();
            $pol->issued_date = now();
            $pol->save();
        }

        return true;
    }

    public function replenishPol($params)
    {
        $data = array_get($params, 'pols', []);

        foreach ($data as $value) {
            $replenishPol = PolHistory::create([
                'pol_id'                    => $value['id'],
                'type'                      => Consts::POL_HISTORY_TYPE_REPLENISH,
                'quantity'                  => $value['quantity'],
                'receiver_requested_id'     => Auth::id(),
                'requester_id'              => $value['requester_id']
            ]);

            $pol = PolManagement::findOrFail($replenishPol->pol_id);
            $pol->received_quantity = BigNumber::new($pol->received_quantity ?? 0)
                ->add($replenishPol->quantity)
                ->toString();
            $pol->received_date = now();
            $pol->save();
        }

        return true;
    }

    public function getNotWorkingReturnSpares()
    {
        return ReturnSpare::where('return_spares.state', '<>', Consts::RETURN_SPARE_STATE_WORKING)
            ->where(function ($query) {
                $query->whereNull('return_spares.write_off')
                    ->orWhere('return_spares.write_off', Consts::FALSE);
            });
    }

    public function getNotWorkingSpareIds()
    {
        $notWorkingFromReturnSpareIds = $this->getNotWorkingReturnSpares()->pluck('bin_id')->toArray();
        $notWorkingFormReturnIds = Bin::query()->where('is_failed', 1)->pluck('id')->toArray();

        return array_unique(array_merge($notWorkingFromReturnSpareIds, $notWorkingFormReturnIds));
    }

    public function suggestBinForSpare($spareIds = [], $options = [])
    {
        $ignoreEmpty = Arr::get($options, 'ignore_empty', true);
        $canReplenishment = Arr::get($options, 'can_replenishment', false);
        if (empty($spareIds)) {
            return null;
        }
        //        $excludeBinIds = $this->getNotWorkingReturnSpares()->pluck('bin_id')->toArray();
        $excludeBinIds = $this->getNotWorkingSpareIds();

        $suggestBins = [];
        // Iterator to get suggest bin fo every spare
        foreach ($spareIds as $spareId) {
            $bin = $this->getSparesAssignedBin(
                [
                    'excludeBinIds' => $excludeBinIds,
                    'spareIds' => [$spareId],
                    'limit' => 1,
                    'ignore_empty' => $ignoreEmpty,
                    'can_replenishment' => $canReplenishment,
                ]
            )->first();

            if ($bin) {
                $suggestBins[] = $bin;
                $excludeBinIds[] = $bin->id;
            }
        }

        return $suggestBins;
        //        return $this->getSparesAssignedBin([
        //            'excludeBinIds' => $excludeBinIds,
        //            'spareIds'      => $spareIds,
        //            'limit'         => 1,
        //            'ignore_empty'  => true
        //        ])->first();
    }



    public function getItemsForReplenish($params = [])
    {
        //        $params['binIds'] = $this->getNotWorkingReturnSpares()->pluck('bin_id')->toArray();
        $params['binIds'] = $this->getNotWorkingSpareIds();
        $params['type'] = Consts::SPARE_TYPE_CONSUMABLE;
        $sparesInBin = $this->getSparesAssignedBin($params);
        $eucboxes = $this->getEucAssignedBox($params);

        return [
            'spare_bins' => $sparesInBin,
            'spare_eucs' => $eucboxes
        ];
    }

    public function checkProcessingBin($params = [])
    {
        $binId = Arr::get($params, 'bin_id');
        $value = Arr::get($params, 'value');

        $bin = Bin::find($binId);
        // If bin does not exist
        if (!$bin) {
            throw new Exception('Bin does not exist');
        }

        // Case lock processing
        if ($value) {
            $this->lockBinProcessing($bin, $params);
        }
        // Case unlock processing
        else {
            $this->unlockBinProcessing($bin, $params);
        }
    }

    public function unlockProcessingBinByUserId($userId)
    {
        Bin::query()
            ->where('process_by', $userId)
            ->update(
                [
                    'is_processing' => 0,
                    'process_time' => null,
                    'process_by' => null,
                ]
            );
    }

    private function lockBinProcessing(Bin $bin, $params = [])
    {
        $userId = Arr::get($params, 'user_id');

        // If bin is processing
        if ($bin->is_processing && $bin->process_by != $userId) {
            $user = User::find($bin->process_by);
            throw new Exception('Bin is locked by ' . $user->name);
        }

        // Update bin is processing
        $bin->fill(
            [
                'is_processing' => 1,
                'process_time' => Carbon::now(),
                'process_by' => $userId,
            ]
        )->save();
    }

    private function unlockBinProcessing(Bin $bin, $params = [])
    {
        $userId = Arr::get($params, 'user_id');

        // If bin is processing
        if ($bin->is_processing && $bin->process_by != $userId) {
            $user = User::find($bin->process_by);
            throw new Exception('Bin is locked by ' . $user->name);
        }

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