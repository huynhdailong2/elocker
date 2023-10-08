<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
use Illuminate\Support\Facades\Auth;
use App\Http\Services\AdminService;
use App\Consts;
use DB;
use Exception;
use Illuminate\Validation\Rule;
use App\Http\Requests\IssuePolFormRequest;
use App\Http\Requests\ReplenishPolFormRequest;

class PolManagementAPIController extends AppBaseController
{
    public function __construct(AdminService $adminService)
    {
        $this->adminService = $adminService;
    }

    public function getPolManagements(Request $request)
    {
        $params = $request->all();
        $data = $this->adminService->getPolManagements($params);
        return $this->sendResponse($data);
    }

    public function getPolHistories(Request $request)
    {
        $params = $request->all();
        $data = $this->adminService->getPolHistories($params);
        return $this->sendResponse($data);
    }

    public function getPolManagementInfo(Request $request)
    {
        $request->validate([
            'id' => 'required',
        ]);

        $polId = $request->id;
        $data = $this->adminService->getPolManagementInfo($polId);
        return $this->sendResponse($data);
    }

    public function createPolManagement(Request $request)
    {
        $request->validate([
            'card_number' => 'required',
            'material_number' => 'required|unique_pol_material_no',
            // 'received_quantity' => 'required',
            'type' => [
                'required',
                Rule::in(Consts::POL_TYPE_OIL, Consts::POL_TYPE_GREASE, Consts::POL_TYPE_COOLANT, Consts::POL_TYPE_APPLICATION, Consts::POL_TYPE_OTHERS)
            ],
            // 'request_quantity' => 'required|numeric',
        ]);

        DB::beginTransaction();
        try {
            $params = $request->all();
            $data = $this->adminService->createPolManagement($params);
            DB::commit();
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            DB::rollback();
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function updatePolManagement(Request $request)
    {
        $polId = $request->id;
        $request->validate([
            'id' => 'required|exists:pol_managements,id',
            'card_number' => 'required',
//            'material_number' => "required|unique_pol_material_no:{$polId}",
            'material_number' => [
                'required',
                Rule::unique('pol_managements')
                    ->ignore($polId)
                    ->whereNull('deleted_at'),
            ],
            'type' => [
                'required',
                Rule::in(Consts::POL_TYPE_OIL, Consts::POL_TYPE_GREASE, Consts::POL_TYPE_COOLANT, Consts::POL_TYPE_APPLICATION, Consts::POL_TYPE_OTHERS)
            ],
            // 'request_quantity' => 'required|numeric',
            // 'received_quantity' => 'numeric|min:1',
            // 'issued_quantity' => 'nullable|numeric|min:1'
        ]);

        DB::beginTransaction();
        try {
            $params = $request->all();
            $data = $this->adminService->updatePolManagement($params);
            DB::commit();
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            DB::rollback();
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function deletePolManagements(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
        ]);

        DB::beginTransaction();
        try {
            $ids = $request->ids;
            $data = $this->adminService->deletePolManagements($ids);
            DB::commit();
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            DB::rollback();
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function issuePol(IssuePolFormRequest $request)
    {
        DB::beginTransaction();
        try {
            $params = $request->all();
            $data = $this->adminService->issuePol($params);
            DB::commit();
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            DB::rollback();
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function replenishPol(ReplenishPolFormRequest $request)
    {
        DB::beginTransaction();
        try {
            $params = $request->all();
            $data = $this->adminService->replenishPol($params);
            DB::commit();
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            DB::rollback();
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }
}
