<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController as BaseController;
use App\Http\Requests\ReplenishManualEucForm;
use App\Http\Services\AdminService;
use App\Http\Services\SpareService;
use Auth;
use DB;
use Exception;
use Illuminate\Http\Request;

class ReplenishAPIController extends BaseController
{
    public function __construct(SpareService $spareService)
    {
        $this->spareService = $spareService;
    }

    public function replenishManual(Request $request)
    {
        // $request->validate([
        //     'spares'                        => 'required|array',
        //     'spares.*.bin_id'               => 'required|exists:bins,id',
        //     'spares.*.spare_id'             => 'required|exists:spares,id',
        //     'spares.*.quantity'             => 'required',
        //     // 'spares.*.configures'           => 'required|array'
        // ]);

        DB::beginTransaction();
        try {
            $params = $request->all();
            $data = $this->spareService->replenishManual($params);
            DB::commit();
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            DB::rollback();
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function getReplenishAutoList(Request $request)
    {
        try {
            $params = $request->all();
            $data = $this->spareService->getReplenishAutoList($params);
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function getReplenishAutoByUuid(Request $request)
    {
        try {
            $params = $request->all();
            $data = $this->spareService->getReplenishAutoByUuid($params);
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function replenishAuto(Request $request)
    {
        $request->validate([
            'spares'                => 'required|array',
            'spares.*.bin_id'       => 'required|exists:bins,id',
            'spares.*.spare_id'     => 'required|exists:spares,id',
            'spares.*.quantity'     => 'required'
        ]);

        DB::beginTransaction();
        try {
            $params = $request->all();
            $data = $this->spareService->replenishAuto($params);
            DB::commit();
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            DB::rollback();
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function confirmReplenishAuto(Request $request)
    {
        $request->validate([
            'replenish_id' => 'required|exists:replenishments,id'
        ]);

        DB::beginTransaction();
        try {
            $params = $request->all();
            $data = $this->spareService->confirmReplenishAuto($params);
            DB::commit();
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            DB::rollback();
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function confirmReplenishAutoTablet(Request $request)
    {
        $request->validate(
            [
                'replenish_id' => 'required|exists:replenishments,id',
                'cluster_id' => 'required|exists:clusters,id',
                'taker_id' => 'required|exists:users,id',
            ]
        );

        DB::beginTransaction();
        try {
            $params = $request->all();
            [
                'data' => $data,
                'suggested_bin' => $suggestedBin
            ] = $this->spareService->confirmReplenishAutoTablet($params);
            DB::commit();
            return $this->sendResponse($data, null, ['suggested_bin' => $suggestedBin]);
        } catch (Exception $ex) {
            DB::rollback();
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function deleteReplenishAuto(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:replenishments,id'
        ]);

        DB::beginTransaction();
        try {
            $replenishId = $request->id;
            $data = $this->spareService->deleteReplenishAuto($replenishId);
            DB::commit();
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            DB::rollback();
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function replenishManualForEuc(ReplenishManualEucForm $request)
    {
        DB::beginTransaction();
        try {
            $params = $request->all();
            $data = $this->spareService->replenishManualForEuc($params);
            DB::commit();
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            DB::rollback();
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function getEucItemHistories(Request $request)
    {
        try {
            $params = $request->all();
            $data = $this->spareService->getEucItemHistories($params);
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function replenishManualAutoBin(Request $request)
    {
        $request->validate([
            'spares'            => 'required|array',
            'spares.*.spare_id' => 'required|exists:spares,id',
            'spares.*.quantity' => 'required',
        ]);

        DB::beginTransaction();
        try {
            $params = $request->all();
            [
                'data' => $data,
                'suggested_bin' => $suggestedBin,
                'replenish_id' => $replenishId,
            ] = $this->spareService->replenishManualAutoBin($params);
            DB::commit();
            return $this->sendResponse($data, null, ['suggested_bin' => $suggestedBin, 'replenish_id' => $replenishId]);
        } catch (Exception $ex) {
            DB::rollback();
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function getReplenishSpares(Request $request)
    {
        try {
            $adminService = new AdminService();
            $params = $request->all();
            $data = $adminService->getItemsForReplenish($params);

            return $this->sendResponse($data);
        } catch (Exception $ex) {
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function removeSpareByBinReplenishAuto(Request $request)
    {
        $request->validate(
            [
                'replenishment_id' => 'required|exists:replenishments,id',
                'bin_id' => 'required|numeric'
            ]
        );

        DB::beginTransaction();
        try {
            $replenishId = $request->get('replenishment_id');
            $binId = $request->get('bin_id');

            $data = $this->spareService->removeSpareByBinReplenishAuto($replenishId, $binId);
            DB::commit();
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            DB::rollback();
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }
}
