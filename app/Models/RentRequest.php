<?php

namespace App\Models;

use App\Models\Rent;
use App\Models\RentMessage;
use App\Models\Space;
use App\Models\Status;
use App\Models\User;
use App\Traits\Filterable;
use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class RentRequest extends Model
{
    use HasFactory, Filterable, Searchable;

    protected $fillable = [
        'renter_id', 
        'space_id',
        'start_date',
        'end_date', 
        'visit_date', 
        'total_price', 
        'price_breakdown', 
        'status_id'
    ];

    protected $searchable = [
        'space.name',
        'space.location.city',
        'space.location.address',
        'renter.name' 
    ];

    protected $casts = [
        'price_breakdown' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
        'visit_date' => 'date',
    ];

    public function renter(){
        return $this->belongsTo(User::class, 'renter_id');
    }

    public function space(){
        return $this->belongsTo(Space::class);
    }

    public function status(){
        return $this->belongsTo(Status::class);
    }

    public function rent(){
        return $this->hasOne(Rent::class, 'request_id');
    }

    public function messages(){
        return $this->hasMany(RentMessage::class, 'request_id')->latest();
    }

    public function reschedules()
    {
        return $this->hasMany(RentReschedule::class, 'rent_request_id');
    }

    public function getDurationAttribute(): ?int
    {
        if (!$this->pricing || !$this->pricing->price) {
            return null;
        }
        return (int) round($this->total_price / $this->pricing->price);
    }

    public function getDurationUnitAttribute(): string
    {
        $type = $this->pricing->pricingType->code ?? null;
        return match ($type) {
            'daily' => 'day',
            'weekly' => 'week',
            'monthly' => 'month',
            default => 'unit',
        };
    }

    public function scopeWithStatus($query, $statusName)
    {
        if (!$statusName || $statusName === 'all') {
            return $query;
        }

        $userId = Auth::id();
        $pendingId = Status::where('code', 'rnt_req_pending')->value('id');
        $awaitingPaymentId = Status::where('code', 'rnt_awaiting_payment')->value('id');
        $ongoingId = Status::where('code', 'rnt_ongoing')->value('id');
        
        $msgFinishReqId = Status::where('code', 'msg_finish_request')->value('id');
        $msgFinishAcceptedId = Status::where('code', 'msg_finish_accepted')->value('id');
        $msgFinishRejectedId = Status::where('code', 'msg_finish_rejected')->value('id');

        $latestSenderSubquery = "
            COALESCE(
                (
                    SELECT sender_id FROM (
                        SELECT sender_id, created_at, 1 as action_type FROM rent_messages WHERE request_id = rent_requests.id
                        UNION ALL
                        SELECT sender_id, created_at, 2 as action_type FROM rent_reschedules WHERE rent_request_id = rent_requests.id
                    ) AS combined_actions 
                    ORDER BY created_at DESC, action_type DESC 
                    LIMIT 1
                ),
                rent_requests.renter_id
            )
        ";

        if ($statusName === 'action_required') {
            return $query->where(function ($q) use ($userId, $pendingId, $awaitingPaymentId, $ongoingId, $msgFinishReqId, $msgFinishAcceptedId, $msgFinishRejectedId, $latestSenderSubquery) {
                
                $q->orWhere(function ($sub) use ($userId, $awaitingPaymentId) {
                    $sub->where('status_id', $awaitingPaymentId)
                        ->where('renter_id', $userId);
                });

                $q->orWhere(function ($sub) use ($userId, $pendingId, $latestSenderSubquery) {
                    $sub->where('status_id', $pendingId)
                        ->whereRaw("($latestSenderSubquery) != ?", [$userId]);
                });

                $q->orWhere(function ($sub) use ($userId, $ongoingId, $msgFinishReqId, $msgFinishAcceptedId, $msgFinishRejectedId) {
                    $sub->where('status_id', $ongoingId)
                        ->whereHas('messages', function ($msgQ) use ($userId, $msgFinishReqId, $msgFinishAcceptedId, $msgFinishRejectedId) {
                            $msgQ->where('id', function ($latestFinishMsgQ) use ($msgFinishReqId, $msgFinishAcceptedId, $msgFinishRejectedId) {
                                $latestFinishMsgQ->selectRaw('max(id)')
                                                 ->from('rent_messages')
                                                 ->whereColumn('request_id', 'rent_requests.id')
                                                 ->whereIn('type_id', [$msgFinishReqId, $msgFinishAcceptedId, $msgFinishRejectedId]);
                            })
                            ->where('type_id', $msgFinishReqId)
                            ->where('sender_id', '!=', $userId);
                        });
                });
            });
        }

        if ($statusName === 'rnt_req_pending') {
            return $query->where(function ($q) use ($userId, $pendingId, $latestSenderSubquery) {
                $q->where('status_id', $pendingId)
                  ->whereRaw("($latestSenderSubquery) = ?", [$userId]);
            });
        }

        return $query->whereHas('status', function ($q) use ($statusName) {
            $q->where('code', $statusName);
        });
    }
}