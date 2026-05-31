<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Status;
use App\Models\UserDocument;
use App\Models\VerificationLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserVerificationRequestController extends Controller
{
    /**
     * INDEX: Fetch all pending verification logs eager-loaded with user profiles
     * and their newly normalized staging document collections.
     */
  /**
     * INDEX: Fetch all pending verification logs eager-loaded with user profiles
     * and their newly normalized staging document collections.
     */
    public function index(Request $request)
    {
        // Build the base query for pending logs [cite: 585, 586]
        $query = VerificationLog::with(['user', 'documents.documentType'])
            ->where('status_id', \App\Models\Status::USR_VERIFY_PENDING)
            ->search($request->search); // Automatically searches name/email via trait

        // Apply dynamic date sorting (using created_at to see who has been waiting longest)
        if ($request->sort_date === 'oldest') {
            $query->oldest('created_at');
        } else {
            $query->latest('created_at'); // Default to newest first
        }

        $pendingLogs = $query->get();

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'data' => $pendingLogs
            ]);
        }

        return view('admin.user-verification.index', compact('pendingLogs'));
    }
    /**
     * SHOW: Eager-load nested user and normalized document relations onto the bound staging instance.
     */
    public function show(Request $request, VerificationLog $verificationLog)
    {
        $verificationLog->loadMissing(['user', 'documents.documentType']);

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'data' => $verificationLog
            ]);
        }

        // return view('admin.user-verification.show', compact('verificationLog'));
    }

    /**
     * APPROVE: Promote normalized staging child records to production tables using Model Binding.
     */
    public function approve(Request $request, VerificationLog $verificationLog)
    {
        // Safely eager-load parent profiles and staged documents to prevent N+1 updates
        $verificationLog->loadMissing(['user', 'documents']);

        // Prevent redundant transactions on finalized workflow logs
        if ($verificationLog->status_id != Status::USR_VERIFY_PENDING) {
            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'This verification session has already been processed.'
                ], 400);
            }
            return redirect()->back()->withErrors(['Session Error' => 'This verification session has already been processed.']);
        }

        DB::transaction(function () use ($verificationLog) {
            $adminId = Auth::id();

            // 1. Mark Quarantine Log as Approved
            $verificationLog->update([
                'status_id' => Status::USR_VERIFIED,
                'admin_id' => $adminId,
                'note' => 'Original staging documents verified and approved by Administrator.',
            ]);

            // 2. Promote files into the permanent user documents schema
            foreach ($verificationLog->documents as $stagedDoc) {
                UserDocument::firstOrCreate([
                    'user_id' => $verificationLog->user_id,
                    'document_type_id' => $stagedDoc->document_type_id, 
                    'file_path' => $stagedDoc->file_path,
                    'description' => $stagedDoc->desc ?? 'Promoted Verification Asset',
                ]);
            }

            // Finalize core user identity verification profile state
            $user = $verificationLog->user;
            $user->update([
                'ver_status' => Status::USR_VERIFIED,
                'verified_at' => now(), 
            ]);

            // assign permission
            $user->givePermissionTo([
                'submit space registration',
                'create rent request',
                'send proposal',
                'send response'
            ]);
        });

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => "Account {$verificationLog->user->name} verified successfully. Staged assets promoted to core platform documents.",
                'data' => $verificationLog->fresh(['user', 'documents.documentType'])
            ], 200);
        }

        return redirect()->back()->with('success', "User account '{$verificationLog->user->name}' has been successfully verified.");
    }

    /**
     * REJECT: Deny bound staging workflow structures, record specific administrative reasoning, and reset profiles.
     */
    public function reject(Request $request, VerificationLog $verificationLog)
    {
        $request->validate([
            'reason' => ['required', 'string', 'max:500']
        ]);

        $verificationLog->loadMissing('user');

        DB::transaction(function () use ($request, $verificationLog) {
            $adminId = Auth::id();

            // 1. Mark Log as Rejected and document custom administrator feedback strings
            $verificationLog->update([
                'status_id' => Status::USR_REJECTED,
                'admin_id' => $adminId,
                'note' => $request->reason, // Matches the generic 'note' descriptor column perfectly
            ]);

            // 2. Revert targeted user identity profile verification state
            $verificationLog->user->update([
                'ver_status' => Status::USR_REJECTED,
                'verified_at' => null,
            ]);
        });

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Identity verification rejected. Administrator feedback saved.',
                'data' => $verificationLog->fresh(['user', 'documents.documentType'])
            ], 200);
        }

        return redirect()->back()->with('success', 'User verification submission has been rejected.');
    }
    public function history(Request $request)
        {
            // Build the base query
            $query = VerificationLog::with(['user', 'status', 'admin', 'documents.documentType'])
                ->where('status_id', '!=', \App\Models\Status::USR_VERIFY_PENDING)
                ->search($request->search)       // Uses our custom Searchable trait
                ->withStatus($request->status);  // Uses our Filterable trait

            // Apply dynamic date sorting
            if ($request->sort_date === 'oldest') {
                $query->oldest('updated_at');
            } else {
                $query->latest('updated_at'); // Default to newest first
            }

            // Paginate and retain all query parameters in the URL
            $historicalLogs = $query->paginate(15)->withQueryString();

            return view('admin.user-verification.history', compact('historicalLogs'));
        }
}