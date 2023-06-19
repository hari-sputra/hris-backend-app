<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\RoleRequest;
use App\Models\Role;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleController extends Controller
{
    public function all(Request $request)
    {
        $id = $request->input('id');
        $name = $request->input('name');
        $limit = $request->input('limit', 10);


        $query = Role::query();

        if ($id) {
            $role = $query->find($id);

            if ($role) {
                return ResponseFormatter::success($role, 'Role with id found');
            }
            return ResponseFormatter::error('Role not found', 404);
        }

        $role = $query->where('company_id', $request->company_id);
        if ($name) {
            $role = Role::where('name', 'like', '%' . $name . '%');
        }

        return ResponseFormatter::success($role->paginate($limit), 'Roles Found');
    }

    public function create(RoleRequest $request)
    {
        try {

            $role = Role::create([
                'name' => $request->name,
                'company_id' => $request->company_id
            ]);

            if (!$role) {
                throw new Exception('Role not created');
            }

            return ResponseFormatter::success($role, 'Role Created Successfuly');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }

    public function update(RoleRequest $request, $id)
    {
        try {
            $role = Role::find($id);

            if (!$role) {
                throw new Exception('Role not found');
            }

            // update role with update
            $role->update([
                'name' => $request->name,
                'company_id' => $request->company_id
            ]);

            // return
            return ResponseFormatter::success($role, 'Role Updated Successfuly');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        try {
            $role = Role::find($id);
            if (!$role) {
                throw new Exception('Role not found');
            }

            // check if role is owned by user
            $currentUser = User::find(Auth::id());
            if (!$currentUser->companies()->where('company_user.company_id', $role->company_id)->exists()) {
                throw new Exception('Role not owned by user');
            }

            // delete role
            $role->delete();
            return ResponseFormatter::success($role, 'Role Deleted Successfuly');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }
}
