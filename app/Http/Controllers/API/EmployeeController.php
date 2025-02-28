<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Manager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = Employee::query();

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where('name', 'LIKE', "%$search%");
        }

        if ($request->has('sort_by')) {
            $sortBy = $request->sort_by;
            $name = $request->has('name') ? $request->name : 'asc';
            $query->orderBy($sortBy, $name);
        }

        $perPage = $request->has('per_page') ? $request->per_page : 2;
        $emp = $query->whereNull('deleted_at')->paginate($perPage);

        return response()->json([
            'status'    => true,
            'data'      => $emp
        ], 200);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        if ($user->role == 'Admin' || $user->role == 'Manager') {
            $validator = Validator::make($request->all(), [
                'name'  => 'required|string|max:255|unique:employee',
                'address' => 'required|string',
                'phone' => 'required|string|max:20|unique:employee'
            ]);

            if ($validator->fails()) {
                return response()->json($validator
                ->errors()->toJson(), 400);
            }

            $manager = Manager::where('company_id', $user->company_id)->first();

            $emp = Employee::create([
                'company_id'    => $user->company_id,
                'manager_id'    => $manager->id,
                'name'          => $request->name,
                'phone'         => $request->phone,
                'address'       => $request->address
            ]);

            return response()->json([
                'status'    => true,
                'message'   => 'Employee Successfully Created',
                'data'      => $emp
            ], 201);
        }
    }

    public function detail($id)
    {
        $emp = Employee::join('company', 'employee.company_id', 'company.id')
        ->join('user', 'company.id', 'user.company_id')
        ->select('employee.*', 'company.company_name', 'user.username as manager_name')
        ->where('employee.id', $id)
        ->first();

        return response()->json([
            'status'    => true,
            'data'      => $emp
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name'  => 'required|string|max:255|unique:employee',
            'address' => 'required|string',
            'phone' => 'required|string|max:20|unique:employee'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $emp = Employee::findOrFail($id);
        $emp->update([
            'name' => $request->get('name'),
            'address' => $request->get('address'),
            'phone' => $request->get('phone'),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Employee updated successfully',
            'data' => $emp
        ], 200);
    }

    public function destroy($id)
    {
        $emp = Employee::findOrFail($id);
        $emp->delete();

        return response()->json([
            'status'    => true,
            'message'   => 'Employee successfully deleted'
        ]);
    }
}
