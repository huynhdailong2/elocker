<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController as BaseController;
use App\Http\Services\SpareService;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use App\Http\Requests\ReturnToStoreRequest;
use App\Http\Requests\ReturnHandoverRequest;
use App\Consts;
use DB;
use Auth;
use Exception;

class ReturnAPIController extends BaseController
{
    public function __construct(SpareService $spareService)
    {
        $this->spareService = $spareService;
    }

    public function getSparesReturn(Request $request)
    {
        try {
            $params = $request->all();
            $data = $this->spareService->getSparesReturn($params);
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function returnToStore(Request $request)
    {
        DB::beginTransaction();
        try {
            $params = $request->all();
            $data = $this->spareService->returnToStore($params);
            DB::commit();
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            DB::rollback();
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function returnToStoreAutoBin(Request $request)
    {
        $request->validate([
            'spares.*.spare_id' => 'required|exists:spares,id',
            'spares.*.quantity' => 'required|numeric',
            'spares.*.state'    => ['required', Rule::in(
                Consts::RETURN_SPARE_STATE_INCOMPLETE,
                Consts::RETURN_SPARE_STATE_WORKING,
                Consts::RETURN_SPARE_STATE_DAMAGE,
                Consts::RETURN_SPARE_STATE_EXPIRED,
                Consts::RETURN_SPARE_STATE_FINISHED,
                Consts::RETURN_SPARE_STATE_INCOMPLETE,
            )]
        ]);

        DB::beginTransaction();
        try {
            $params = $request->all();
            [
                'data' => $data,
                'suggested_bin' => $suggestedBin,
                'success' => $success,
                'message' => $message,
            ] = $this->spareService->returnToStoreAutoBin($params);
            DB::commit();
            if(!$success) {
                return $this->sendMessageFailure($message);
            }

            return $this->sendResponse($data, null, ['suggested_bin' => $suggestedBin]);
        } catch (Exception $ex) {
            DB::rollback();
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function handOverSpares(ReturnHandoverRequest $request)
    {
        DB::beginTransaction();
        try {
            $params = $request->all();
            $data = $this->spareService->handOverSpares($params);
            DB::commit();
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            DB::rollback();
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function searchReturnProductItem(Request $request) {
        try {
            $params = $request->all();
            $data = $this->spareService->searchSpare($params);
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function getReturnItemInfo(Request $request) {
        $request->validate([
            'item_pn' => 'required',
        ]);

        try {
            $params = $request->all();
            $data = $this->spareService->getSpareInfo($params);
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }
}
