<?php

namespace App\Http\Controllers;

use App\Models\DocumentType;
use App\Models\Status;
use App\Models\VerificationLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VerificationController extends Controller
{
    /**
     * Display the identity verification submission status page.
     */
    public function index()
    {
        $user = Auth::user();
        
        // Redirect back to dashboard if the user has already passed ID vetting
        if ($user->ver_status == Status::USR_VERIFIED) {
            return redirect()->route('dashboard')->with('info', 'Your account is already verified.');
        }

        // Fetch the latest staging log to review previous outcomes or display rejection feedback
        $latestLog = VerificationLog::where('user_id', $user->id)->latest()->first();

        return view('renter.verification.index', compact('user', 'latestLog'));
    }

    /**
     * Store newly uploaded identity verification documents into normalized staging tables.
     */
    public function store(Request $request)
    {
        // @dd($request->all());
        // Enforce secure image constraints before processing file streams
        $request->validate([
            'ktp' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'selfie_ktp' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $user = Auth::user();

        $ktpTypeId = DocumentType::where('code', 'ktp')->value('id');
        $selfieTypeId = DocumentType::where('code', 'selfie_ktp')->value('id');

        if (!$ktpTypeId || !$selfieTypeId) {
            return back()->with('error', 'Configuration Error: Required document classification types are missing from the database.');
        }

        DB::beginTransaction();
        try {
            // 1. Store source files securely into quarantine staging paths
            $ktpPath = $request->file('ktp')->store("staging/verifications/{$user->id}", 'public');
            $selfiePath = $request->file('selfie_ktp')->store("staging/verifications/{$user->id}", 'public');

            $log = VerificationLog::create([
                'user_id' => $user->id,
                'status_id' => Status::USR_VERIFY_PENDING,
                // Note: Optional string reasoning column left null upon initial application
            ]);

            // 3. Populate child verification documents using the normalized HasMany architecture
            $log->documents()->createMany([
                [
                    'document_type_id' => $ktpTypeId,
                    'file_path' => $ktpPath,
                    'description' => 'Identity Card (KTP) Submission'
                ],
                [
                    'document_type_id' => $selfieTypeId,
                    'file_path' => $selfiePath,
                    'description' => 'Selfie with KTP Verification Submission'
                ],
            ]);

            $user->update(['ver_status' => Status::USR_VERIFY_PENDING]);

            DB::commit();
            
            return redirect()->route('verification.index')
                ->with('success', 'Verification documents submitted successfully! Please wait for administrator review.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to upload verification documents: ' . $e->getMessage());
        }
    }
}