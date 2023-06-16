<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;

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
}
