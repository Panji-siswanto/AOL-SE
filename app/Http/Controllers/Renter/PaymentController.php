<?php

namespace App\Http\Controllers\Renter;

use App\Http\Controllers\Controller;
use App\Models\RentRequest;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function show(RentRequest $rentRequest)
    {
        abort_if($rentRequest->renter_id !== auth()->id(), 403);

        return view('rents.payment', compact('rentRequest'));
    }

    public function process(Request $request, RentRequest $rentRequest)
    {
        abort_if($rentRequest->renter_id !== auth()->id(), 403);

        $request->validate([
            'payment_method' => 'required|in:gopay,ovo,bca,mandiri',
        ]);

        $rentRequest->update(['status' => 'paid']);

        return response()->json(['success' => true]);
    }
}