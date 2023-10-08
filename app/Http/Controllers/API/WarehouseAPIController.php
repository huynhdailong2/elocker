<?php

namespace App\Http\Controllers\API;

use App\Http\Services\WarehouseService;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController as BaseController;
use DB;
use Auth;
use Exception;

class WarehouseAPIController extends BaseController
{
    protected $warehouseService;

    public function __construct(WarehouseService $warehouseService)
    {
        $this->warehouseService = $warehouseService;
    }

    public function getClusters(Request $request)
    {
        try {
            $params = $request->all();
            $data = $this->warehouseService->getClusters($params);
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function getClusterInfo(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:clusters,id',
        ]);

        try {
            $clusterId = $request->id;
            $data = $this->warehouseService->getClusterInfo($clusterId);
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function createCluster(Request $request)
    {
        $request->validate([
            'name' => 'required',
        ]);

        DB::beginTransaction();
        try {
            $params = $request->all();

            $data = $this->warehouseService->createCluster($params);
            DB::commit();
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            DB::rollBack();
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function updateCluster(Request $request)
    {
        $request->validate([
            'id'   => 'required|exists:clusters,id',
            'name' => 'required',
        ]);

        DB::beginTransaction();
        try {
            $params = $request->all();

            $data = $this->warehouseService->updateCluster($params);
            DB::commit();
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            DB::rollBack();
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function updateVirtualCluster(Request $request)
    {
        $request->validate([
            'id'   => 'required|exists:clusters,id',
            'is_virtual' => 'required|in:0,1',
        ]);

        DB::beginTransaction();
        try {
            $params = $request->all();

            $data = $this->warehouseService->updateVirtualCluster($request->get('id'), $request->get('is_virtual'));
            DB::commit();
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            DB::rollBack();
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function deleteCluster(Request $request)
    {
        $request->validate([
            'id' => 'required',
        ]);

        DB::beginTransaction();
        try {
            $clusterId = $request->id;
            $data = $this->warehouseService->deleteCluster($clusterId);
            DB::commit();
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            DB::rollback();
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

}
