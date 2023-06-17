<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateCompanyRequest;
use App\Models\Company;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyController extends Controller
{
    public function all(Request $request)
    {
        $id = $request->input('id');
        $name = $request->input('name');
        $limit = $request->input('limit', 10);

        if ($id) {
            $company = Company::with(['users'])->find($id);

            if ($company) {
                return ResponseFormatter::success($company, 'Company with id found');
            }
            return ResponseFormatter::error('Company not found', 404);
        }

        $company = Company::with(['users']);
        if ($name) {
            $company = Company::where('name', 'like', '%' . $name . '%');
        }

        return ResponseFormatter::success($company->paginate($limit), 'Companies Found');
    }

    public function create(CreateCompanyRequest $request)
    {
        try {
            if ($request->file('logo')) {
                $path = $request->file('logo')->store('public/logos');
            }
            $company = Company::create([
                'name' => $request->name,
                'logo' => $path,
            ]);

            if (!$company) {
                throw new Exception('Company not created');
            }

            // pivot table
            $user = User::find(Auth::id());
            $user->companies()->attach($company->id);

            $company->load('users');

            return ResponseFormatter::success($company, 'Company Created Successfuly');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }
}
