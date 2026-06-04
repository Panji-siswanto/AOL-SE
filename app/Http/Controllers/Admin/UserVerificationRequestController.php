<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Status;
use App\Models\UserDocument;
use App\Models\VerificationLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserVerificationRequestController extends Controller {

    public function index(Request $request) {
        $query = VerificationLog::with(['user', 'documents.documentType'])
            ->where('status_id', Status::USR_VERIFY_PENDING)
            ->search($request->search);

        $request->sort_date === 'oldest' ? $query->oldest('created_at') : $query->latest('created_at');
        $pendingLogs = $query->get();

        return $request->wantsJson() 
            ? response()->json(['status' => 'success', 'data' => $pendingLogs])
            : view('admin.user-verification.index', compact('pendingLogs'));
    }

    public function show(Request $request, VerificationLog $verificationLog) {
        $verificationLog->loadMissing(['user', 'documents.documentType']);

        return $request->wantsJson() 
            ? response()->json(['status' => 'success', 'data' => $verificationLog])
            : view('admin.user-verification', compact('verificationLog'));
    }

    public function approve(Request $request, VerificationLog $verificationLog) {
        $verificationLog->loadMissing(['user', 'documents']);

        if ($verificationLog->status_id !== Status::USR_VERIFY_PENDING) {
            return $this->handleSessionError($request, 'This verification session has already been processed.');
        }

        DB::transaction(function () use ($verificationLog) {
            $verificationLog->update([
                'status_id'=> Status::USR_VERIFIED,
                'admin_id'=> Auth::id(),
                'note'=> 'Original staging documents verified and approved.',
            ]);

            foreach ($verificationLog->documents as $stagedDoc) {
                UserDocument::firstOrCreate([
                    'user_id'=> $verificationLog->user_id,
                    'document_type_id' => $stagedDoc->document_type_id, 
                    'file_path'=> $stagedDoc->file_path,
                    'description'=> $stagedDoc->desc ?? 'Promoted Verification Asset',
                ]);
            }

            $user = $verificationLog->user;
            $user->update(['ver_status' => Status::USR_VERIFIED, 'verified_at' => now()]);
            $user->givePermissionTo(['submit space registration', 'create rent request', 'send proposal', 'send response']);
        });

        return $request->wantsJson()
            ? response()->json(['status' => 'success', 'data' => $verificationLog->fresh(['user', 'documents.documentType'])])
            : redirect()->back()->with('success', "User '{$verificationLog->user->name}' verified successfully.");
    }

    public function reject(Request $request, VerificationLog $verificationLog) {
        $request->validate(['reason' => 'required|string|max:500']);
        $verificationLog->loadMissing('user');

        DB::transaction(function () use ($request, $verificationLog) {
            $verificationLog->update([
                'status_id' => Status::USR_REJECTED,
                'admin_id' => Auth::id(),
                'note' => $request->reason,
            ]);

            $verificationLog->user->update(['ver_status' => Status::USR_REJECTED, 'verified_at' => null]);
        });

        return $request->wantsJson()
            ? response()->json(['status' => 'success', 'data' => $verificationLog->fresh(['user', 'documents.documentType'])])
            : redirect()->back()->with('success', 'User verification rejected.');
    }

    public function history(Request $request) {
        $query = VerificationLog::with(['user', 'status', 'admin', 'documents.documentType'])
            ->where('status_id', '!=', Status::USR_VERIFY_PENDING)
            ->search($request->search)
            ->withStatus($request->status);

        $request->sort_date === 'oldest' ? $query->oldest('updated_at') : $query->latest('updated_at');

        $historicalLogs = $query->paginate(15)->withQueryString();
        return view('admin.user-verification.history', compact('historicalLogs'));
    }

    private function handleSessionError($request, $message) {
        return $request->wantsJson()
            ? response()->json(['status' => 'error', 'message' => $message], 400)
            : redirect()->back()->withErrors(['Session Error' => $message]);
    }
}