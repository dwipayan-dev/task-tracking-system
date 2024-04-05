<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\PermissionRole;
use App\Models\Role;
use App\Models\RoleUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RolePermissionController extends Controller
{
    public function index()
    {
        return response()->json([
            'status' => "success",
            'result' =>  Role::with('getPermissions')->get()
        ], 200);
    }
    public function create()
    {
        $permissions = Permission::get();
        $transformedPermissions = [];

        foreach ($permissions as $permission) {
            $resource = explode('-', $permission['name'])[0];
            $common_name = ucfirst($resource);
            if (!isset($transformedPermissions[$common_name])) {
                $transformedPermissions[$common_name] = [
                    'name' => $common_name,
                    'value' => [
                        'create' => null,
                        'read' => null,
                        'update' => null,
                        'delete' => null,
                    ],
                ];
            }
            $operation = explode('-', $permission['name'])[1];
            $transformedPermissions[$common_name]['value'][$operation] = $permission['id'];
            $finalArray = array_values($transformedPermissions);
        }
        return response()->json([
            'status' => "success",
            'result' =>  $finalArray
        ], 200);
    }
    public function store(Request $request)
    {
        $role_unique = Role::where('name', $request->role_name)->first();
        if ($role_unique != null) {
            return response()->json([
                'status' => "error",
                'message' =>  'Duplicate role name'
            ], 404);
        }
        $role_details = Role::create([
            'name' => $request->role_name,
            'display_name' => ucfirst($request->role_name),
            'description' => $request->role_name,
        ]);
        if ($role_details == null)
            return response()->json([
                'status' => "error",
                'message' =>  'Unable to create role'
            ], 404);
        $role_details->permissions()->sync($request->permissions);

        // foreach ($request->permissions as $permission) {
        //     $permission_details = Permission::where('id', $permission)->first();
        //     if ($permission_details != null)
        //         $role_details->attachPermission($permission_details);
        // }
        return response()->json([
            'status' => "success",
            'message' =>  'Role created successfully.'
        ], 200);
    }
    public function delete(Request $request)
    {
        $role = Role::whereId($request->role_id)->first();
        if ($role == null)
            return response()->json([
                'status' => "error",
                'message' =>  'Role not found.'
            ], 404);

        if ($role->display_name == 'Admin')
            return response()->json([
                'status' => "error",
                'message' =>  'System reserved role can\'t be deleted'
            ], 404);
        $is_users_assigned = RoleUser::where('role_id', $request->role_id)->count();
        if ($is_users_assigned)
            return response()->json([
                'status' => "error",
                'message' =>  'Users were assigned in this role so it can\'t be deleted'
            ], 404);
        Role::whereId($request->role_id)->delete();
        PermissionRole::where('role_id', $request->role_id)->delete();
        return response()->json([
            'status' => "success",
            'message' =>  'Role deleted successfully'
        ], 200);
    }
    public function edit($id)
    {
        $role_details = Role::with('getPermissions')->whereId($id)->first();
        if ($role_details == null)
            return response()->json([
                'status' => "error",
                'message' =>  'Role not found'
            ], 404);
        if ($role_details->display_name == 'Admin')
            return response()->json([
                'status' => "error",
                'message' =>  'System reserved role can\'t be editable'
            ], 404);

        return response()->json([
            'status' => "success",
            'result' =>  $role_details
        ], 200);
    }
    public function update(Request $request)
    {
        $role = Role::where('id', $request->id)->first();
        if ($role == null)
            return response()->json([
                'status' => "error",
                'message' =>  'Role not found'
            ], 404);
        if ($role->display_name == 'Admin')
            return response()->json([
                'status' => "error",
                'message' =>  'System reserved role can\'t be editable'
            ], 404);
        if (count((array)$request->permissions)) {
            $role->permissions()->sync($request->permissions);
            return response()->json([
                'status' => "success",
                'message' =>  "Permissions updated successfully"
            ], 200);
        } else {
            return response()->json([
                'status' => "error",
                'message' =>  'At least one permission is required for a role'
            ], 404);
        }
    }
}
