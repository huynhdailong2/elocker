<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\CreateLinkMORequest;
use App\Http\Requests\DeleteLinkMORequest;
use App\Http\Requests\UpdateLinkMORequest;
use App\Http\Services\AdminService;
use App\Http\Services\IssueCardService;
use App\Http\Services\JobCardService;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController as BaseController;
use App\Http\Services\SpareService;
use Illuminate\Validation\Rule;
use App\Http\Requests\IssueCardFormRequest;
use App\Consts;
use DB;
use Auth;
use Exception;

class IssueAPIController extends BaseController
{
    private $jobCardService;
    private $issueCardService;
    private $adminService;

    public function __construct(
        SpareService $spareService,
        JobCardService $jobCardService,
        IssueCardService $issueCardService,
        AdminService $adminService
    ) {
        $this->spareService = $spareService;
        $this->jobCardService = $jobCardService;
        $this->issueCardService = $issueCardService;
        $this->adminService = $adminService;
    }

    public function issueCard(Request $request)
    {
        // DB::beginTransaction();
        try {
            $params = $request->all();
            $data = $this->spareService->issueCard($params);
            // DB::commit();
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            DB::rollback();
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function issueSpares(IssueCardFormRequest $request)
    {
        DB::beginTransaction();
        try {
            $params = $request->all();
            [
                'data' => $data,
                'suggested_bin' => $suggestedBin
            ] = $this->spareService->issueSpares($params);
            DB::commit();
            return $this->sendResponse($data, null, ['suggested_bin' => $suggestedBin]);
        } catch (Exception $ex) {
            DB::rollback();
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function getIssueCardHistories(Request $request)
    {
        try {
            $params = $request->all();
            $data = $this->spareService->getIssueCardHistories($params);
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function searchToolRoomJobCard(Request $request)
    {
        $request->validate([
            'job_card_number' => 'required',
        ]);

        try {
            $params = $request->all();
            $data = $this->jobCardService->searchJobCard($params);
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function addRoomJobCard(Request $request)
    {
        $request->validate([
            'job_card_number' => 'required',
            'wo'              => 'required',
            'veh'             => 'required',
            'veh_type'        => 'required',
            'platform'        => 'required',
        ]);

        try {
            $params = $request->all();
            $data = $this->jobCardService->addRoomJobCard($params);
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function getIssueItemData(Request $request) {
        // $request->validate([
        //     'user_id' => 'required',
        // ]);

        try {
            $params = $request->all();
            $data = $this->issueCardService->getIssueItemData($params);
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function createLinkMO(CreateLinkMORequest $request)
    {
        try {
            $params = $request->all();
            $data = $this->spareService->createLinkMO($params);
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function updateLinkMO(UpdateLinkMORequest $request)
    {
        try {
            $params = $request->all();
            $data = $this->spareService->updateLinkMO($params);
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function deleteLinkMO(DeleteLinkMORequest $request)
    {
        try {
            $params = $request->all();
            $data = $this->spareService->deleteLinkMO($params);
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }
}
