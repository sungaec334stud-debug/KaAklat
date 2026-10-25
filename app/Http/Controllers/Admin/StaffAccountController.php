<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\StoreStaffAccountRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class StaffAccountController
{
    public function index(): View
    {
        return view('admin.staff.index', [
            'staff' => User::whereIn('role', ['librarian', 'admin'])->orderBy('name')->get(),
        ]);
    }

    public function store(StoreStaffAccountRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'password' => $data['password'],
            'student_id' => null,
            'is_active' => false,
            'email_verified_at' => null,
        ]);

        event(new Registered($user));

        return redirect()->route('admin.staff.index')->with('status', 'staff-created');
    }
}
