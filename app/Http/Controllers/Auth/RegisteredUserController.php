<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\DocumentType;
use App\Models\Status;
use App\Models\User;
use App\Models\VerificationLog;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        // 1. Validate data (Removed the 'lowercase' rule from email)
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username', 'alpha_num'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'phone_code' => ['nullable', 'string', 'max:5'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'ktp' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'selfie_ktp' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ]);

        $ktpTypeId = DocumentType::where('code', 'ktp')->value('id');
        $selfieTypeId = DocumentType::where('code', 'selfie_ktp')->value('id');

        if (!$ktpTypeId || !$selfieTypeId) {
            return back()->withInput()->with('error', 'Configuration Error: Required document types are missing from the database.');
        }

        // Clean up the phone number (remove leading zero if they typed it)
        $cleanPhone = ltrim($request->phone, '0');
        $fullPhoneNumber = $request->phone_code . $cleanPhone;

        DB::beginTransaction();
        try {
            // 2. Create User (Force email to lowercase here natively)
            $user = User::create([
                'name' => $request->name,
                'username' => $request->username,
                'email' => strtolower($request->email), 
                'phone' => $fullPhoneNumber,
                'password' => Hash::make($request->password),
                'ver_status' => Status::USR_VERIFY_PENDING, 
            ]);

            $user->assignRole('renter');

            $ktpPath = $request->file('ktp')->store("staging/verifications/{$user->id}", 'public');
            $selfiePath = $request->file('selfie_ktp')->store("staging/verifications/{$user->id}", 'public');

            $log = VerificationLog::create([
                'user_id' => $user->id,
                'status_id' => Status::USR_VERIFY_PENDING,
            ]);

            $log->documents()->createMany([
                ['document_type_id' => $ktpTypeId, 'file_path' => $ktpPath, 'desc' => 'Initial KTP Submission on Registration'],
                ['document_type_id' => $selfieTypeId, 'file_path' => $selfiePath, 'desc' => 'Initial Selfie Verification on Registration'],
            ]);

            DB::commit();

            event(new Registered($user));
            Auth::login($user);

            return redirect()->route('verification.notice');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Registration failed: ' . $e->getMessage());
        }
    }
}