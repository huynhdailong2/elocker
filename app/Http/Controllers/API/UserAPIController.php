<?php

namespace App\Http\Controllers\API;

use App\Exports\UsersExport;
use App\Http\Controllers\AppBaseController;
use App\Http\Requests\ImportUserRequest;
use App\Http\Services\UserService;
use DB;
use Excel;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserAPIController extends AppBaseController
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function getAllUsers(Request $request)
    {
        try {
            $data = $this->userService->getAllUsers($request->all());
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function getUserInfo(Request $request)
    {
        try {
            $data = $this->userService->getUserInfo(Auth::id());
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function getUsersStatistic(Request $request)
    {
        try {
            $data = $this->userService->getUsersStatistic();
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function getUserInfoByCardId(Request $request)
    {
        $request->validate(
            [
                'card_id' => 'required'
            ]
        );
        try {
            $data = $this->userService->getUserInfoByCardId($request->card_id);
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function createNewAccount(Request $request)
    {
        $request->validate(
            [
                'login_name' => 'required|unique:users',
                'card_id' => 'unique:users',
                'employee_id' => 'required',
                'role' => 'required|numeric|min:1'
            ]
        );

        DB::beginTransaction();
        try {
            $data = $this->userService->createNewAccount($request->all());
            DB::commit();
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            DB::rollBack();
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function updateAccount(Request $request)
    {
        $request->validate(
            [
                'id' => 'required|exists:users,id',
                'login_name' => "required|unique_login_id:{$request->id}",
                'card_id' => "unique_card_id:{$request->id}",
                'employee_id' => 'required',
                'password' => $request->id ? 'nullable' : 'required',
                'role' => 'required',
                'email' => 'nullable|email',
            ]
        );

        DB::beginTransaction();
        try {
            $userId = $request->user_id;
            $input = $request->all();
            $data = $this->userService->updateAccount($userId, $input);
            DB::commit();
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            DB::rollBack();
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function updateUserCards(Request $request)
    {
        $request->validate([
            'data' => 'required',
        ]);

        DB::beginTransaction();
        try {
            $input = $request->all();
            $data = $this->userService->updateUserCards($input);
            DB::commit();
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            DB::rollBack();
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    /* For app only */
    public function updateAccountApp(Request $request)
    {
        $request->validate(
            [
                'id' => 'required|exists:users,id',
                'card_id' => "unique_card_id:{$request->id}",
                'role' => 'nullable|numeric|min:1'
            ], [
                'role.min' => 'Role is wrong!'
            ]
        );

        DB::beginTransaction();
        try {
            $userId = $request->id;
            $input = $request->all();
            unset($input['login_name'], $input['email']);
            $data = $this->userService->updateAccount($userId, $input);
            DB::commit();
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            DB::rollBack();
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function deleteUser(Request $request)
    {
        DB::beginTransaction();
        try {
            $userId = $request->user_id;
            $data = $this->userService->deleteUser($userId);
            DB::commit();
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            DB::rollBack();
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function getAvailableRoles(Request $request)
    {
        try {
            $data = [];
            $roles = $this->userService->getAvailableRoles();
            foreach ($roles as $roleId => $name) {
                $data[] = (object)[
                    'id' => $roleId,
                    'label' => $name,
                ];
            }
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function exportUsers(Request $request)
    {
        try {
            $currentTime = now()->format('Y-m-d');
            $filename = "users-{$currentTime}.xlsx";
            return Excel::download(new UsersExport(), $filename);
        } catch (Exception $ex) {
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function importUsers(ImportUserRequest $request)
    {
        try {
            $this->userService->importUsers($request->file);
            return $this->sendResponse('Ok');
        } catch (Exception $ex) {
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }
}
