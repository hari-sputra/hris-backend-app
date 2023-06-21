<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Responsibility;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\ResponsibilityRequest;

class ResponsibilityController extends Controller
{
    // public function all(Request $request)
    // {
    //     $id = $request->input('id');
    //     $name = $request->input('name');
    //     $limit = $request->input('limit', 10);


    //     $query = Responsibility::query();

    //     if ($id) {
    //         $responsibility = $query->find($id);

    //         if ($responsibility) {
    //             return ResponseFormatter::success($responsibility, 'Responsibility with id found');
    //         }
    //         return ResponseFormatter::error('Responsibility not found', 404);
    //     }

    //     $responsibility = $query->where('role_id', $request->role_id);
    //     if ($name) {
    //         $responsibility = Responsibility::where('name', 'like', '%' . $name . '%');
    //     }

    //     return ResponseFormatter::success($responsibility->paginate($limit), 'Responsibilitys Found');
    // }

    public function all(Request $request)
    {
        $id = $request->input('id');
        $name = $request->input('name');
        $role_id = $request->input('role_id');
        $limit = $request->input(
            'limit',
            10
        );

        $query = Responsibility::query();

        if ($id) {
            $responsibility = $query->find($id);

            if ($responsibility) {
                return ResponseFormatter::success($responsibility, 'Responsibility with id found');
            }
            return ResponseFormatter::error('Responsibility not found', 404);
        }

        $currentUser = User::find(Auth::id());
        $companyIds = $currentUser->companies->pluck('id');

        $query->whereIn('role_id', function ($query) use ($companyIds) {
            $query->select('id')
                ->from('roles')
                ->whereIn('company_id', $companyIds);
        });

        if ($role_id) {
            $query->where('role_id', $role_id);
        }

        if ($name) {
            $query->where(
                'name',
                'like',
                '%' . $name . '%'
            );
        }

        return ResponseFormatter::success($query->paginate($limit), 'Responsibilities Found');
    }


    public function create(ResponsibilityRequest $request)
    {
        try {

            $responsibility = Responsibility::create([
                'name' => $request->name,
                'role_id' => $request->role_id
            ]);

            if (!$responsibility) {
                throw new Exception('Responsibility not created');
            }

            return ResponseFormatter::success($responsibility, 'Responsibility Created Successfuly');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        try {
            $responsibility = Responsibility::find($id);
            if (!$responsibility) {
                throw new Exception('Responsibility not found');
            }

            // check if responsibility is owned by user
            $currentUser = User::find(Auth::id());
            if (!$currentUser->companies()->where('company_user.company_id', $responsibility->role->company_id)->exists()) {
                throw new Exception('Responsibility not owned by user');
            }

            // delete responsibility
            $responsibility->delete();
            return ResponseFormatter::success($responsibility, 'Responsibility Deleted Successfuly');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }
}
