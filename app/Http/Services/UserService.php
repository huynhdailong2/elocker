<?php

namespace App\Http\Services;

use App\Consts;
use App\Imports\UsersImport;
use App\User;
use App\Utils;
use Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Storage;
use Excel;

class UserService extends BaseService
{
    public function getAllUsers($input)
    {
        $availableRoles = $this->getAvailableRoles();
        $allowRoleIds = array_keys($availableRoles);

        return User::leftJoin('users as updater', 'updater.id', 'users.updated_by')
            ->select(
                'users.id', 'users.name', 'users.login_name', 'users.card_id', 'users.employee_id', 'users.role',
                'users.dept', 'users.avatar', 'users.updated_at', 'users.email',
                DB::raw('(CASE WHEN users.role = '.Consts::USER_ROLE_SUPER_ADMIN.' THEN "Super Admin"
                    WHEN users.role = '.Consts::USER_ROLE_ADMINISTRATOR.' THEN "Admin"
                    WHEN users.role = '.Consts::USER_ROLE_ADMIN_SUPPORT.' THEN "Admin Support"
                    WHEN users.role = '.Consts::USER_ROLE_STOREMAN.' THEN "Store man"
                    WHEN users.role = '.Consts::USER_ROLE_INSPECTOR.' THEN "Inspector"
                    ELSE "" END) AS role_name'),
                'updater.id as updated_id', 'updater.login_name as updated_by'
            )
            ->when(!empty($allowRoleIds), function ($query) use ($allowRoleIds) {
                return $query->whereIn('users.role', $allowRoleIds);
            })
            ->when( !empty($input['sort']) && !empty($input['sort_type']),
                function ($query) use ($input) {
                    return $query->orderBy($input['sort'], $input['sort_type']);
                },
                function ($query) {
                    return $query->orderBy('users.updated_at', 'desc');
                }
            )
            ->when(!empty($input['time']), function ($query) use ($input) {
                $lastTime = Carbon::createFromFormat('Y-m-d H:i:s', $input['time']);
                $query->where('users.updated_at', '>=', $lastTime->toDateTimeString());
            })
            ->when(Arr::get($input, 'search_key'), function($query) use ($input) {
                $keyword = Arr::get($input, 'search_key');
                $query->where('users.card_id', 'LIKE', "%$keyword%");
            })
            ->when(
                !empty($input['no_pagination']),
                function ($query) {
                    return $query->get();
                },
                function ($query) use ($input) {
                    return $query->paginate(array_get($input, 'limit', Consts::DEFAULT_PER_PAGE));
                }
            );
    }

    private function standardFilePath($filePath)
    {
        $keyword = 'storage';
        $info = parse_url($filePath);
        if (strpos($info['path'], $keyword) !== false) {
            $rootPath = rtrim(Storage::disk('public')->getAdapter()->getPathPrefix(), DIRECTORY_SEPARATOR);
            $newPath = trim(str_replace($keyword, '', $info['path']), DIRECTORY_SEPARATOR);

            return "$rootPath/$newPath";
        }

        return $filePath;
    }

    private function saveAvatarToBase64($avatar)
    {
        if (empty($avatar)) {
            return null;
        }
        $imgPath = Utils::saveFileToStorage($avatar, 'avatars');
        $imgPath = self::standardFilePath($imgPath);
        return Utils::convertBase64FromImage($imgPath);
    }

    public function createNewAccount($input)
    {
        $user = User::where('login_name', $input['login_name'])->first();
        if (!$user) {
            $user = new User;
        }

        $user->role = Arr::get($input, 'role', $user->role);
        if (!self::isGteRole($user->role)) {
            throw new \Exception('You have no permission to create this role.');
        }

        $user->login_name = $input['login_name'];
        $user->card_id = Arr::get($input, 'card_id', '');
        $user->employee_id = $input['employee_id'];
        $user->login_name = $input['login_name'];
        $user->name = str_replace('_', '', ucwords($user->login_name, '_'));
        $user->password = Arr::get($input, 'password', '');
        $user->dept = Arr::get($input, 'dept');
        $user->avatar = $this->saveAvatarToBase64(array_get($input, 'avatar'));

        $user->save();

        return $user;
    }

    public function getUserInfo($userId)
    {
        return User::findOrFail($userId);
    }

    public function getUsersStatistic()
    {
        $users = User::all();
        return [
            'total'                 => $users->count(),
            'total_inspector'       => $this->countUsersByRole($users, Consts::USER_ROLE_INSPECTOR),
            'total_admin'           => $this->countUsersByRole($users, Consts::USER_ROLE_ADMINISTRATOR),
            'total_admin_support'   => $this->countUsersByRole($users, Consts::USER_ROLE_ADMIN_SUPPORT),
            // 'total_super_admin'     => $this->countUsersByRole($users, Consts::USER_ROLE_SUPER_ADMIN),
            'total_storeman'        => $this->countUsersByRole($users, Consts::USER_ROLE_STOREMAN)
        ];
    }

    private function countUsersByRole($users, $role)
    {
        return $users->filter(function ($user) use ($role) {
            return $user->role === $role;
        })->count();
    }

    public function getUserInfoByCardId($cardId)
    {
        $users = User::where('card_id', $cardId)->first();
        if(!empty($users)) {
          $users->role_name = $this->getRoleName($users->role);
        }
        return $users;
    }

    public function updateUserCards($input)
    {
        try
        {
            if( !empty($input['data']) ) {
                foreach($input['data'] as $val) {
                    $userModel = User::find($val['id']);
                    if(!empty($userModel)) {
                        $userModel = $this->saveData($userModel, ['card_id' => $val['card_id']]);
                    }
                }
            }
        }
        catch(\Exception $e)
        {
            throw new \Exception($e->getMessage());
        }
        return true;
    }

    public function updateAccount($userId, $input)
    {
        $user = User::find($userId);
        if (!$user) {
            $user = new User;
        }

        $user->role = Arr::get($input, 'role', $user->role);
        if (!self::isGteRole($user->role)) {
            throw ValidationException::withMessages(
                [
                    'role' => ['You have no permission to edit this user.']
                ]
            );
//            throw new ValidationException('You have no permission to edit this user.');
        }

        $user->login_name = Arr::get($input, 'login_name', $user->login_name);
        $user->card_id = Arr::get($input, 'card_id', $user->card_id);
        $user->employee_id = Arr::get($input, 'employee_id', $user->employee_id);
        $user->name = str_replace('_', '', ucwords($user->login_name, '_'));
        $user->role = Arr::get($input, 'role', $user->role);
        $user->dept = Arr::get($input, 'dept', $user->dept);
        $user->email = Arr::get($input, 'email');
        if (Arr::get($input, 'avatar')) {
            $user->avatar = $this->saveAvatarToBase64(Arr::get($input, 'avatar'));
        }
        $user->updated_by = Auth::id();

        // Do not update password when empty
        if(Arr::get($input, 'password')) {
            $user->password = $input['password'];
        }

        $user->save();

        return $user;
    }

    public function deleteUser($userId)
    {
        if (!$user = User::find($userId)) {
            throw new \Exception('User not exist.');
        }

        if ($user->isSuperAdmin()) {
            throw new \Exception('Cannot delete Super Admin account.');
        }

        if (!self::isGteRole($user->role)) {
            throw new \Exception('You have no permission.');
        }

        $user->delete();

        // Update card_id
        $prefixDeleted = '_deleted_' . \Illuminate\Support\Carbon::now()->toDateTimeString();
        $user->card_id = $user->card_id . $prefixDeleted;
//        $user->name = $user->name . $prefixDeleted;
        $user->login_name = $user->login_name . $prefixDeleted;
        $user->save();

        return $user;
    }

    public function getRoleName($roleId = 0) {
      $rs = '';
      if(!empty($roleId)) {
        $roles = $this->getListRoles();
        $rs = $roles[$roleId];
      }
      return $rs;
    }

    public function getListRoles() {
      return [
          Consts::USER_ROLE_SUPER_ADMIN   => 'Super Admin',
          Consts::USER_ROLE_ADMINISTRATOR => 'Administrator',
          Consts::USER_ROLE_ADMIN_SUPPORT => 'Admin Support',
          Consts::USER_ROLE_STOREMAN      => 'Storeman',
          Consts::USER_ROLE_INSPECTOR     => 'Inspector',
      ];
    }

    public function getAvailableRoles()
    {
        $user = Auth::user();
        $availableRoles = $this->getListRoles();
        if ($user->role == Consts::USER_ROLE_ADMINISTRATOR) {
            unset($availableRoles[Consts::USER_ROLE_SUPER_ADMIN]);
        } elseif ($user->role == Consts::USER_ROLE_ADMIN_SUPPORT) {
            unset($availableRoles[Consts::USER_ROLE_SUPER_ADMIN], $availableRoles[Consts::USER_ROLE_ADMINISTRATOR]);
        } elseif ($user->role != Consts::USER_ROLE_SUPER_ADMIN) {
            unset(
                $availableRoles[Consts::USER_ROLE_SUPER_ADMIN],
                $availableRoles[Consts::USER_ROLE_ADMINISTRATOR],
                $availableRoles[Consts::USER_ROLE_ADMIN_SUPPORT],
            );
        }
        return $availableRoles;
    }

    /**
     * Check if current role is greater or equal targetRole
     *
     * @param int $targetRole
     * @return bool
     */
    public static function isGteRole($targetRole)
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        $isGte = true;
        switch ($targetRole) {
            case Consts::USER_ROLE_SUPER_ADMIN:
                $isGte = $user->role === Consts::USER_ROLE_SUPER_ADMIN;
                break;
            case Consts::USER_ROLE_ADMINISTRATOR:
                $isGte = in_array($user->role, [Consts::USER_ROLE_SUPER_ADMIN, Consts::USER_ROLE_ADMINISTRATOR]);
                break;
            case Consts::USER_ROLE_ADMIN_SUPPORT:
                $isGte = in_array($user->role, [
                    Consts::USER_ROLE_SUPER_ADMIN,
                    Consts::USER_ROLE_ADMINISTRATOR,
                    Consts::USER_ROLE_ADMIN_SUPPORT
                ]);
                break;
            default:
        }

        return $isGte;
    }

    public function importUsers($fileUpload)
    {
        Utils::saveFileToStorage($fileUpload, 'users-importing');

        ini_set('memory_limit', '-1');

        Excel::queueImport(new UsersImport(), $fileUpload);
        return true;
    }
}
