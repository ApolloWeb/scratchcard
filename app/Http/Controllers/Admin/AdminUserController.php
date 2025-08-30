<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index()
    {
        return AdminUser::paginate(20);
    }

    public function show(AdminUser $adminUser)
    {
        return $adminUser;
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admin_users,email',
            'password' => 'required|string|min:8',
            'role' => 'required|in:super_admin,admin,viewer',
        ]);

        $admin = AdminUser::create($data);

        return response($admin, 201);
    }

    public function update(Request $request, AdminUser $adminUser)
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:admin_users,email,' . $adminUser->id,
            'password' => 'sometimes|string|min:8',
            'role' => 'sometimes|in:super_admin,admin,viewer',
            'is_active' => 'sometimes|boolean',
        ]);

        $adminUser->update($data);

        return $adminUser;
    }

    public function destroy(AdminUser $adminUser)
    {
        $adminUser->delete();

        return response(null, 204);
    }
}
