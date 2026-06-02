<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterUserRequest; // Import your new request
use App\Models\DocumentType;
use App\Models\Status;
use App\Models\User;
use App\Models\VerificationLog;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    // Inject the new RegisterUserRequest here!
    public function store(RegisterUserRequest $request): RedirectResponse
    {
        $ktpTypeId = DocumentType::where('code', 'ktp')->value('id');
        $selfieTypeId = DocumentType::where('code', 'selfie_ktp')->value('id');

        if (!$ktpTypeId || !$selfieTypeId) {
            return back()->withInput()->with('error', 'Configuration Error: Required document types are missing from the database.');
        }

        // The phone is already ltrim-ed thanks to prepareForValidation() in the FormRequest!
        $fullPhoneNumber = $request->phone_code . $request->phone;

        DB::beginTransaction();
        try {
            // The email is already lowercased thanks to prepareForValidation()
            $user = User::create([
                'name' => $request->name,
                'username' => $request->username,
                'email' => $request->email, 
                'phone' => $fullPhoneNumber,
                'password' => Hash::make($request->password),
                'ver_status' => Status::where('code', 'usr_verify_pending')->value('id'), 
            ]);

            $user->assignRole('renter');

            $ktpPath = $request->file('ktp')->store("staging/verifications/{$user->id}", 'public');
            $selfiePath = $request->file('selfie_ktp')->store("staging/verifications/{$user->id}", 'public');

            $log = VerificationLog::create([
                'user_id' => $user->id,
                'status_id' => Status::where('code', 'usr_verify_pending')->value('id'),
            ]);

            // Note: Updated 'desc' to 'description' based on your VerificationDocument model
            $log->documents()->createMany([
                ['document_type_id' => $ktpTypeId, 'file_path' => $ktpPath, 'description' => 'Initial KTP Submission on Registration'],
                ['document_type_id' => $selfieTypeId, 'file_path' => $selfiePath, 'description' => 'Initial Selfie Verification on Registration'],
            ]);

            DB::commit();

            event(new Registered($user));
            Auth::login($user);

            return redirect()->route('verification.notice'); // Assuming you have this route defined

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Registration failed: ' . $e->getMessage());
        }
    }
}