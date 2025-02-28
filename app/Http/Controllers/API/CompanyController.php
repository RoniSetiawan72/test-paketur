<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Manager;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class CompanyController extends Controller
{
    public function index(Request $request)
    {
        $query = Company::query();

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where('company_name', 'LIKE', "%$search%");
        }

        if ($request->has('sort_by')) {
            $sortBy = $request->sort_by;
            $name = $request->has('company_name') ? $request->company_name : 'asc';
            $query->orderBy($sortBy, $name);
        }

        $perPage = $request->has('per_page') ? $request->per_page : 2;
        $company = $query->whereNull('deleted_at')->paginate($perPage);

        return response()->json([
            'status'    => true,
            'data'      => $company
        ], 200);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        if ($user->role == 'Admin') {
            $validator = Validator::make($request->all(), [
                'company_name'  => 'required|string|max:255|unique:company',
                'company_email' => 'required|string|email|max:255|unique:company',
                'company_phone' => 'required|string|max:20'
            ]);


            if($validator->fails()) {
                return response()->json($validator->errors()->toJson(), 400);
            }

            $company = Company::create([
                'company_name'  => $request->company_name,
                'company_email' => $request->company_email,
                'company_phone' => $request->company_phone,
            ]);

            $manager = Manager::create([
                'company_id'    => $company->id
            ]);

            $userManager = User::create([
                'username'      => 'Manager ' . $company->company_name,
                'role'          => 'Manager',
                'company_id'    => $company->id,
                'password'      => Hash::make('123456'),
                'email'         => 'manager' . $company->company_name . '@gmail.com',
            ]);

            return response()->json([
                'status'    => true,
                'message'   => 'Company created successfully',
                'data'      => $company
            ], 201);
        } else {
            return response()->json([
                'status'    => false,
                'message'   => 'This role doesnt have permission to this action.'
            ], 403);
        }
    }
}
