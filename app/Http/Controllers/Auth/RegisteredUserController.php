<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterUserRequest;
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

    public function store(RegisterUserRequest $request): RedirectResponse 
    {
        $ktpTypeId = DocumentType::where('code', 'ktp')->value('id');
        $selfieTypeId = DocumentType::where('code', 'selfie_ktp')->value('id');
        $pendingId = Status::where('code', 'usr_verify_pending')->value('id');

        if (!$ktpTypeId || !$selfieTypeId || !$pendingId) {
            return back()->withInput()->with('error', 'System configuration error: Missing document types or statuses.');
        }

        DB::beginTransaction();
        try {
            $user = User::create([
                'name' => $request->name,
                'username'=> $request->username,
                'email'=> $request->email,
                'phone'=> $request->phone_code . $request->phone,
                'password' => Hash::make($request->password),
                'ver_status' => $pendingId,
            ]);

            $user->assignRole('renter');

            // Store files
            $dir = "staging/verifications/{$user->id}";
            $ktpPath = $request->file('ktp')->store($dir, 'public');
            $selfiePath = $request->file('selfie_ktp')->store($dir, 'public');

            // Create Log & Link Documents
            $log = VerificationLog::create([
                'user_id'=> $user->id,
                'status_id' => $pendingId,
            ]);

            $log->documents()->createMany([
                ['document_type_id' => $ktpTypeId, 'file_path' => $ktpPath, 'description' => 'KTP Submission'],
                ['document_type_id' => $selfieTypeId, 'file_path' => $selfiePath, 'description' => 'Selfie Verification'],
            ]);

          DB::commit();

        Auth::login($user);

        return redirect()->route('dashboard');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Registration failed: ' . $e->getMessage());
        }
    }
}