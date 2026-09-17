<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function edit(Request $request): Response
    {
        $user = $request->user()->load('employee');

        return Inertia::render('Employee/Profile', [
            'user' => $user,
            'employee' => $user->employee,
        ]);
    }

    public function updatePersonal(Request $request)
    {
        $user = $request->user();
        $employee = $user->employee;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        $user->update(['name' => $validated['name']]);

        if ($employee) {
            $employee->update(['phone' => $validated['phone']]);
        }

        return back()->with('success', 'Informasi pribadi berhasil diperbarui.');
    }

    public function updateBank(Request $request)
    {
        $employee = $request->user()->employee;
        if (! $employee) {
            return back()->with('error', 'Profil pegawai tidak ditemukan.');
        }

        $validated = $request->validate([
            'bank_name' => 'required|string|max:100',
            'account_number' => 'required|string|max:50',
        ]);

        $employee->update([
            'bank_name' => $validated['bank_name'],
            'account_number' => $validated['account_number'],
        ]);

        return back()->with('success', 'Informasi rekening bank berhasil diperbarui.');
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Kata sandi berhasil diperbarui.');
    }

    public function updateAvatar(Request $request)
    {
        $employee = $request->user()->employee;
        if (! $employee) {
            return back()->with('error', 'Profil pegawai tidak ditemukan.');
        }

        $request->validate([
            'avatar' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $file = $request->file('avatar');
        $fileName = 'avatar_'.$employee->id.'_'.time().'.'.$file->getClientOriginalExtension();
        $path = $file->storeAs('avatars', $fileName, 'public');

        $employee->update([
            'avatar' => '/storage/'.$path,
        ]);

        return back()->with('success', 'Foto profil berhasil diperbarui.');
    }
}
