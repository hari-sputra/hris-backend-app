<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\User;
use App\Models\Employee;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\EmployeeRequest;
use Illuminate\Support\Facades\Storage;

class EmployeeController extends Controller
{
    public function all(Request $request)
    {
        $id = $request->input('id');
        $name = $request->input('name');
        $role_id = $request->input('role_id');
        $email = $request->input('email');
        $gender = $request->input('gender');
        $age = $request->input('age');
        $phone = $request->input('phone');
        $team_id = $request->input('team_id');
        $limit = $request->input(
            'limit',
            10
        );

        $query = Employee::query();

        if ($id) {
            $employee = $query->find($id);

            if ($employee) {
                return ResponseFormatter::success($employee, 'Employee with id found');
            }
            return ResponseFormatter::error('Employee not found', 404);
        }

        $currentUser = User::find(Auth::id());
        $companyIds = $currentUser->companies->pluck('id');

        $query->whereIn('role_id', function ($query) use ($companyIds) {
            $query->select('id')
                ->from('roles')
                ->whereIn('company_id', $companyIds);
        });

        if ($email) {
            $query->where('email', $email);
        }
        if ($gender) {
            $query->where('gender', $gender);
        }
        if ($age) {
            $query->where('age', $age);
        }
        if ($phone) {
            $query->where(
                'phone',
                'like',
                '%' . $phone . '%'
            );
        }
        if ($role_id) {
            $query->with('role')->where('role_id', $role_id);
        }
        if ($team_id) {
            $query->with('team')->where('team_id', $team_id);
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


    public function create(EmployeeRequest $request)
    {
        try {

            if ($request->file('photo')) {
                // add new photo
                $path = $request->file('photo')->store('public/employee/photos');
            }

            $employee = Employee::create([
                'name' => $request->name,
                'email' => $request->email,
                'gender' => $request->gender,
                'age' => $request->age,
                'phone' => $request->phone,
                'photo' => $path,
                'team_id' => $request->team_id,
                'role_id' => $request->role_id
            ]);

            if (!$employee) {
                throw new Exception('Employee not created');
            }

            return ResponseFormatter::success($employee, 'Employee Created Successfuly');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }

    public function update(EmployeeRequest $request, $id)
    {
        try {
            $employee = Employee::find($id);

            if (!$employee) {
                throw new Exception('Employee not found');
            }

            // upload photo
            if ($request->file('photo')) {
                // delete photo
                Storage::delete($employee->photo);

                // add new photo
                $path = $request->file('photo')->store('public/employees/photos');
            }

            // update employee with update
            $employee->update([
                'name' => $request->name,
                'email' => $request->email,
                'gender' => $request->gender,
                'age' => $request->age,
                'phone' => $request->phone,
                'photo' => isset($path) ? $path : $employee->photo,
                'team_id' => $request->team_id,
                'role_id' => $request->role_id
            ]);

            // return
            return ResponseFormatter::success($employee, 'Employee Updated Successfuly');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        try {
            $employee = Employee::find($id);
            if (!$employee) {
                throw new Exception('Employee not found');
            }

            // check if employee is owned by user
            $currentUser = User::find(Auth::id());
            if (!$currentUser->companies()->where('company_user.company_id', $employee->role->company_id)->exists()) {
                throw new Exception('Employee not owned by user');
            }

            // delete employee
            $employee->delete();
            return ResponseFormatter::success($employee, 'Employee Deleted Successfuly');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }
}
