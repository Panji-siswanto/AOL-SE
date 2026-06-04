<?php

namespace App\Http\Controllers;

use App\Models\DocumentType;
use App\Models\Status;
use App\Models\VerificationLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VerificationController extends Controller {

    public function index() {
        $user = Auth::user();

        if ($user->ver_status == Status::USR_VERIFIED) {
            return redirect()->route('dashboard')->with('info', 'Your account is already verified.');
        }

        $latestLog = VerificationLog::where('user_id', $user->id)->latest()->first();

        return view('renter.verification.index', compact('user', 'latestLog'));
    }

    public function store(Request $request) {
        $request->validate([
            'ktp' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'selfie_ktp' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $user = Auth::user();
        $ktpTypeId    = DocumentType::where('code', 'ktp')->value('id');
        $selfieTypeId = DocumentType::where('code', 'selfie_ktp')->value('id');

        if (!$ktpTypeId || !$selfieTypeId) {
            return back()->with('error', 'Configuration Error: Missing document types.');
        }

        DB::beginTransaction();
        try {
            $path = "staging/verifications/{$user->id}";
            $ktpPath = $request->file('ktp')->store($path, 'public');
            $selfiePath = $request->file('selfie_ktp')->store($path, 'public');

            $log = VerificationLog::create([
                'user_id'   => $user->id,
                'status_id' => Status::USR_VERIFY_PENDING,
            ]);

            $log->documents()->createMany([
                ['document_type_id' => $ktpTypeId, 'file_path' => $ktpPath, 'description' => 'KTP Submission'],
                ['document_type_id' => $selfieTypeId, 'file_path' => $selfiePath, 'description' => 'Selfie Verification'],
            ]);

            $user->update(['ver_status' => Status::USR_VERIFY_PENDING]);

            DB::commit();
            return redirect()->route('verification.index')
                ->with('success', 'Documents submitted successfully! Please wait for review.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Upload failed: ' . $e->getMessage());
        }
    }
}