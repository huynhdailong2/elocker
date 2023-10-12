<?php

namespace App\Http\Controllers\API;

use App\Consts;
use App\Exports\SparesConfigureExport;
use App\Exports\VehiclesExport;
use App\Http\Controllers\AppBaseController as BaseController;
use App\Http\Requests\BinFormRequest;
use App\Http\Requests\CheckProcessingBinRequest;
use App\Http\Requests\SpareFormRequest;
use App\Http\Requests\UnlockProcessingBinRequest;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use App\Http\Requests\VehicleSchedulingRequest;
use App\Http\Services\AdminService;
use App\Utils;
use Auth;
use DB;
use Excel;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Storage;

class AdminAPIController extends BaseController
{
    public function __construct(AdminService $adminService)
    {
        $this->adminService = $adminService;
    }

    public function getTorqueWrenchAreas(Request $request)
    {
        try {
            $params = $request->all();
            $data = $this->adminService->getTorqueWrenchAreas($params);
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function createTorqueWrenchArea(Request  $request)
    {
        $request->validate(
            [
                'area' => 'required',
                'torque_value' => 'required|numeric',
            ]
        );

        DB::beginTransaction();
        try {
            $params = $request->all();

            $data = $this->adminService->createTorqueWrenchArea($params);
            DB::commit();
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            DB::rollBack();
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function updateTorqueWrenchArea(Request $request)
    {
        $request->validate(
            [
                'area' => 'required',
                'torque_value' => 'required|numeric',
            ]
        );

        DB::beginTransaction();
        try {
            $params = $request->all();

            $data = $this->adminService->updateTorqueWrenchArea($params);
            DB::commit();
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            DB::rollBack();
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function deleteTorqueWrenchArea(Request $request)
    {
        $request->validate(
            [
                'id' => 'required',
            ]
        );

        DB::beginTransaction();
        try {
            $torqueWrenchAreaId = $request->id;
            $data = $this->adminService->deleteTorqueWrenchArea($torqueWrenchAreaId);
            DB::commit();
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            DB::rollback();
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function getShelfs(Request $request)
    {
        try {
            $params = $request->all();
            $data = $this->adminService->getShelfs($params);
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function getShelfInfo(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:shelfs,id',
        ]);

        try {
            $shelfId = $request->id;
            $data = $this->adminService->getShelfInfo($shelfId);
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function createShelf(Request $request)
    {
        $request->validate([
            'name'              => 'required',
            'num_rows'          => 'required|numeric|gte:1',
            'num_bins'          => 'required|numeric|gte:1',
        ]);

        DB::beginTransaction();
        try {
            $params = $request->all();

            $data = $this->adminService->createShelf($params);
            DB::commit();
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            DB::rollBack();
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function updateShelf(Request $request)
    {
        $request->validate([
            'id'                => 'required|exists:shelfs,id',
            'name'              => 'required',
            'num_rows'          => 'required|numeric|gte:1',
            'num_bins'          => 'required|numeric|gte:1',
        ]);

        DB::beginTransaction();
        try {
            $params = $request->all();

            $data = $this->adminService->updateShelf($params);
            DB::commit();
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            DB::rollBack();
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function deleteShelf(Request $request)
    {
        $request->validate([
            'id' => 'required',
        ]);

        DB::beginTransaction();
        try {
            $shelfId = $request->id;
            $data = $this->adminService->deleteShelf($shelfId);
            DB::commit();
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            DB::rollback();
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function getSpareTypes(Request $request)
    {
        try {
            $params = $request->all();

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
            $data = [];
            collect($spareTypes)->filter(function ($spareType) use ($params) {
                $type = Arr::get($params, 'type');
                if (empty($type) || $type === 'all') {
                    return true;
                }
                return in_array($type, $spareType['accepted']);
            })->map(function ($item) use (&$data) {
                $data[] = (object)[
                    'type'  => $item['type'],
                    'label' => $item['label'],
                ];
            });

            return $this->sendResponse($data);
        } catch (Exception $ex) {
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function getSpares(Request $request)
    {
        try {
            $params = $request->all();
            $data = $this->adminService->getSpares($params);
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function getSpareByMpn(Request $request)
    {
        try {
            $params = $request->all();
            $data = $this->adminService->getSpareByMpn($params);
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function getSpareByPartNo(Request $request)
    {
        try {
            $params = $request->all();
            $data = $this->adminService->getSpareByPartNo($params);
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function getSparesUnassigned(Request $request)
    {
        try {
            $params = $request->all();
            $data = $this->adminService->getSparesUnassigned($params);
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function getSparesAssignedBin(Request $request)
    {
        try {
            $params = $request->all();
            $type = Arr::get($params, 'type');
//            if($type == Consts::SPARE_TYPE_EUC) {
//                $data = $this->adminService->getEucAssignedBox($params);
//            } else {
                $data = $this->adminService->getSparesAssignedBin($params);
//            }

            return $this->sendResponse($data);
        } catch (Exception $ex) {
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function getItemsForIssuing(Request $request)
    {
        try {
            $params = $request->all();
            // Only get bins have quantity_oh > 0
            $params['ignore_empty'] = true;

            if (!empty($params['type']) && $params['type'] === Consts::SPARE_TYPE_DURABLE_2) {
                $params['type'] = Consts::SPARE_TYPE_DURABLE;
            }

            $params['ignore_overdue_item'] = true;
            $data = $this->adminService->getItemsForIssuing($params);
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function createSpare(SpareFormRequest $request)
    {
        DB::beginTransaction();
        try {
            $params = $request->all();
            $params['auditor'] = Auth::user()->login_name;

            $params = Utils::standardValues($params);

            $data = $this->adminService->createSpare($params);
            DB::commit();
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            DB::rollBack();
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function updateSpare(SpareFormRequest $request)
    {
        DB::beginTransaction();
        try {
            $params = $request->all();
            $params['auditor'] = Auth::user()->login_name;

            $params = Utils::standardValues($params);

            $data = $this->adminService->updateSpare($params);
            DB::commit();
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            DB::rollBack();
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function deleteSpare(Request $request)
    {
        $request->validate([
            'id' => 'required',
        ]);

        DB::beginTransaction();
        try {
            $spareId = $request->id;
            $data = $this->adminService->deleteSpare($spareId);
            DB::commit();
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            DB::rollback();
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function importSpares(Request $request)
    {
        $request->validate([
            'file' => 'required'
        ]);

        try {
            $this->adminService->importSpares($request->file);
            return $this->sendResponse('Ok');
        } catch (Exception $ex) {
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function exportSpares(Request $request)
    {
        try {
            $currentTime = now()->format('Y-m-d');
            $filename = "spares-{$currentTime}.xlsx";
            return Excel::download(new SparesConfigureExport(), $filename);
        } catch (Exception $ex) {
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function getBins(Request $request)
    {
        $params = $request->all();
            $data = $this->adminService->getBins($params);
            return $data;
    }

    public function getBinsSummary(Request $request)
    {
        try {
            $params = $request->all();
            $data = $this->adminService->getBinsSummary($params);
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function updateBin(Request $request)
    {
        DB::beginTransaction();
        try {
            $params = $request->all();
            $data = $this->adminService->updateBin($params);
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            DB::rollBack();
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function patchBin(Request $request)
    {
        $request->validate([
            'id'        => 'required|exists:bins,id',
        ]);
        DB::beginTransaction();
        try {
            $params = $request->all();
            $data = $this->adminService->patchBin($params);
            DB::commit();
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            DB::rollBack();
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function unassignedBin(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:bins,id'
        ]);

        DB::beginTransaction();
        try {
            $binId = $request->id;
            $data = $this->adminService->unassignedBin($binId);
            DB::commit();
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            DB::rollBack();
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function getVehicles(Request $request)
    {
        try {
            $params = $request->all();
            $data = $this->adminService->getVehicles($params);
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function exportExcelVehicles(Request  $request)
    {
        $params = $request->all();
        $params['no_pagination'] = true;

        $currentTIme = now()->format('Y-m-d');
        $filename = "reports/vehicle-scheduling-{$currentTIme}.xlsx";
        Excel::store(new VehiclesExport($params), $filename, 'public');
        $filePath = Storage::disk('public')->path($filename);

        $headers = [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'inline; filename="' . $filename . '"'
        ];
        return response()->file($filePath, $headers);
    }

    public function getVehicleStatistic(Request $request)
    {
        try {
            $params = $request->all();
            $data = $this->adminService->getVehicleStatistic($params);
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function getVehicleStatisticMonthly(Request $request)
    {
        try {
            $params = $request->all();
            $data = $this->adminService->getVehicleStatisticMonthly($params);
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function createVehicle(Request $request)
    {
        $request->validate([
            'vehicle_num'        => 'required',
            'vehicle_type_id'    => 'required|exists:vehicle_types,id',
        ]);

        DB::beginTransaction();
        try {
            $params = $request->all();

            $data = $this->adminService->createVehicle($params);
            DB::commit();
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            DB::rollBack();
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function updateVehicle(Request $request)
    {
        $request->validate([
            'id'                => 'required|exists:vehicles,id',
            'vehicle_num'       => 'required',
            'vehicle_type_id'   => 'required|exists:vehicle_types,id'
        ]);

        DB::beginTransaction();
        try {
            $params = $request->all();

            $data = $this->adminService->updateVehicle($params);
            DB::commit();
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            DB::rollBack();
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function revertVehicle(Request $request)
    {
        $request->validate([
            'id'                => 'required|exists:vehicles,id',
            'vehicle_num'       => 'required',
            'vehicle_type_id'   => 'required|exists:vehicle_types,id'
        ]);

        DB::beginTransaction();
        try {
            $params = $request->all();

            $data = $this->adminService->revertVehicle($params);
            DB::commit();
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            DB::rollBack();
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function deleteVehicle(Request $request)
    {
        $request->validate([
            'id' => 'required',
        ]);

        DB::beginTransaction();
        try {
            $id = $request->id;
            $data = $this->adminService->deleteVehicle($id);
            DB::commit();
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            DB::rollback();
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function getVehicleTypes(Request $request)
    {
        try {
            $params = $request->all();
            $data = $this->adminService->getVehicleTypes($params);
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function createVehicleType(Request $request)
    {
        $request->validate([
            'name'        => 'required'
        ]);

        DB::beginTransaction();
        try {
            $params = $request->all();

            $data = $this->adminService->createVehicleType($params);
            DB::commit();
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            DB::rollBack();
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function updateVehicleType(Request $request)
    {
        $request->validate([
            'id'         => 'required|exists:vehicle_types,id',
            'name'       => 'required',
        ]);

        DB::beginTransaction();
        try {
            $params = $request->all();

            $data = $this->adminService->updateVehicleType($params);
            DB::commit();
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            DB::rollBack();
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function deleteVehicleType(Request $request)
    {
        $request->validate([
            'id' => 'required',
        ]);

        DB::beginTransaction();
        try {
            $id = $request->id;
            $data = $this->adminService->deleteVehicleType($id);
            DB::commit();
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            DB::rollback();
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function getJobCards(Request $request)
    {
        try {
            $params = $request->all();
            $params['is_active'] = true;
            $data = $this->adminService->getJobCards($params);
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function getClosedJobCards(Request $request)
    {
        try {
            $params = $request->all();
            $params['is_active'] = false;
            $data = $this->adminService->getJobCards($params);
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function getJobCardInfo(Request $request)
    {
        try {
            $params = $request->all();
            $data = $this->adminService->getJobCardInfo($params);
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function getJobCardByCardNo(Request $request)
    {
        try {
            $params = $request->all();
            $params['is_active'] = true;
            $data = $this->adminService->getJobCardByCardNo($params);
            if(!$data) {
                return $this->sendMessageFailure('Your service card is unavailable!');
            }

            return $this->sendResponse($data);
        } catch (Exception $ex) {
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function createJobCard(Request $request)
    {
        $request->validate([
            'card_num'      => 'required',
//            'wo'            => 'required',
            'wo'            => 'nullable',
            'vehicle_id'    => 'required|exists:vehicles,id',
            'platform'      => 'required',
        ]);

        DB::beginTransaction();
        try {
            $params = $request->all();

            $data = $this->adminService->createJobCard($params);
            DB::commit();
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            DB::rollBack();
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function updateJobCard(Request $request)
    {
        $request->validate([
            'id'            => 'required|exists:job_cards,id',
            'card_num'      => 'required',
            'wo'            => 'required',
            'vehicle_id'    => 'required|exists:vehicles,id',
            'platform'      => 'required',
        ]);

        DB::beginTransaction();
        try {
            $params = $request->all();

            $data = $this->adminService->updateJobCard($params);
            DB::commit();
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            DB::rollBack();
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function deleteJobCard(Request $request)
    {
        $request->validate([
            'id' => 'required',
        ]);

        DB::beginTransaction();
        try {
            $id = $request->id;
            $data = $this->adminService->deleteJobCard($id);
            DB::commit();
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            DB::rollback();
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function closedJobCard(Request $request)
    {
        $request->validate([
           'id' => 'required',
       ]);

        DB::beginTransaction();
        try {
            $id = $request->id;
            $data = $this->adminService->closedJobCard($id);
            DB::commit();
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            DB::rollback();
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function getEucList(Request $request)
    {
        try {
            $params = $request->all();
            $data = $this->adminService->getEucList($params);
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function createEuc(Request $request)
    {
        $request->validate([
            'order'             => 'required|numeric|gt:0',
            'vehicle_type_id'   => 'required|exists:vehicle_types,id',
            'platform'          => 'required|numeric|gt:0'
        ]);

        DB::beginTransaction();
        try {
            $params = $request->all();

            $data = $this->adminService->createEuc($params);
            DB::commit();
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            DB::rollBack();
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function createOnlyEuc(Request $request)
    {
        $request->validate(
            [
                'order' => 'required|numeric|gt:0',
                'vehicle_type_id' => 'required|exists:vehicle_types,id',
                'platform' => 'required|numeric|gt:0'
            ]
        );

        DB::beginTransaction();
        try {
            $params = $request->all();

            $data = $this->adminService->createOnlyEuc($params);
            DB::commit();
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            DB::rollBack();
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function updateEuc(Request $request)
    {
        $request->validate([
            'id'                => 'required|exists:euc_boxes,id',
            'order'             => 'required|numeric|gt:0',
            'vehicle_type_id'   => 'required|exists:vehicle_types,id',
            'platform'          => 'required|numeric|gt:0'
        ]);

        DB::beginTransaction();
        try {
            $params = $request->all();

            $data = $this->adminService->updateEuc($params);
            DB::commit();
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            DB::rollBack();
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function updateItemsEuc($eucBoxId, Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate(
                [
                    'spares.*.serial_no' => 'required',
                ]
            );

            $params = $request->all();

            $data = $this->adminService->updateItemsEuc($eucBoxId, $params);
            DB::commit();
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            DB::rollBack();
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function updateOnlyEuc(Request $request)
    {
        $request->validate(
            [
                'id' => 'required|exists:euc_boxes,id',
                'order' => 'required|numeric|gt:0',
                'vehicle_type_id' => 'required|exists:vehicle_types,id',
                'platform' => 'required|numeric|gt:0'
            ]
        );

        DB::beginTransaction();
        try {
            $params = $request->all();

            $data = $this->adminService->updateOnlyEuc($params);
            DB::commit();
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            DB::rollBack();
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function deleteEuc(Request $request)
    {
        $request->validate([
            'id' => 'required',
        ]);

        DB::beginTransaction();
        try {
            $id = $request->id;
            $data = $this->adminService->deleteEucList($id);
            DB::commit();
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            DB::rollback();
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function getAdminCabinetData(Request $request)
    {
        $request->validate([
            'cabinet' => 'required',
        ]);

        try {
            $params = $request->all();
            $data = $this->adminService->getAdminCabinetData($params);
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function checkProcessingBin(CheckProcessingBinRequest $request)
    {
        try {
            $params = $request->validated();
            $data = $this->adminService->checkProcessingBin($params);
            return $this->sendResponse($data, 'Successful Bin');
        } catch (Exception $ex) {
            logger()->error($ex);
            return $this->sendError($ex, 200);
        }
    }

    public function unlockProcessingBin(UnlockProcessingBinRequest $request)
    {
        try {
            $params = $request->validated();
            $data = $this->adminService->unlockProcessingBinByUserId(Arr::get($params, 'user_id'));
            return $this->sendResponse($data, 'Successful unlock');
        } catch (Exception $ex) {
            logger()->error($ex);
            return $this->sendError($ex, 200);
        }
    }
}
