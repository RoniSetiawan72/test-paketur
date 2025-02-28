<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Manager;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ManagerController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where('username', 'LIKE', "%$search%");
        }

        if ($request->has('sort_by')) {
            $sortBy = $request->sort_by;
            $name = $request->has('username') ? $request->username : 'asc';
            $query->orderBy($sortBy, $name);
        }

        $query->where('role', 'manager');

        $perPage = $request->has('per_page') ? $request->per_page : 2;
        $manager = $query->whereNull('deleted_at')->paginate($perPage);

        return response()->json([
            'status'    => true,
            'data'      => $manager
        ], 200);
    }

    public function detail($id)
    {
        $manager = User::join('company', 'user.company_id', 'company.id')
        ->where('user.id', $id)
        ->first();

        return response()->json([
            'status'    => true,
            'data'      => $manager
        ]);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'username'  => 'required|string|max:255',
            'email'     => 'required|string|email|max:255|unique:user',
            'password'  => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $manager = User::findOrFail($id);
        $manager->update([
            'username' => $request->get('username'),
            'email' => $request->get('email'),
            'password' => Hash::make($request->get('password')),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Manager updated successfully',
            'data' => $manager
        ], 200);
    }

    public function destroy($id)
    {
        $userCheck = Auth::user();

        if($userCheck->role == 'Admin'){
            $user = User::findOrFail($id);
            $manager = Manager::where('company_id', $user->company_id)->delete();
            $user->delete();

            return response()->json([
                'status'    => true,
                'message'   => 'Manager successfully deleted'
            ]);
        } else {
            return response()->json([
                'status'    => false,
                'message'   => 'You doesnt have permission to this action.'
            ]);
        }
    }
}
