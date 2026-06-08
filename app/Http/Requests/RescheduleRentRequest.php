<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RescheduleRentRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Safely grab the model from the route
        $rentRequest = $this->route('rentRequest');
        if (!$rentRequest) return false;

        return $rentRequest->space->owner_id === $this->user()->id || $rentRequest->renter_id === $this->user()->id;
    }

    public function rules(): array
    {
        $rentRequest = $this->route('rentRequest');
        
        // Check if the user actually changed the dates
        $oldStart = \Carbon\Carbon::parse($rentRequest->start_date)->format('Y-m-d');
        $oldEnd = \Carbon\Carbon::parse($rentRequest->end_date)->format('Y-m-d');
        $oldVisit = $rentRequest->visit_date ? \Carbon\Carbon::parse($rentRequest->visit_date)->format('Y-m-d') : null;

        $datesChanged = $this->new_start_date !== $oldStart || 
                        $this->new_end_date !== $oldEnd || 
                        $this->new_visit_date !== $oldVisit;

        return [
            'new_visit_date' => ['nullable', 'date', 'before:new_start_date'], // Must be before start
            'new_start_date' => ['required', 'date', 'after_or_equal:today'],
            'new_end_date'   => ['required', 'date', 'after:new_start_date'],
            // Message is optional ONLY if they changed the dates
            'response_note'  => [$datesChanged ? 'nullable' : 'required', 'string', 'max:1000'], 
        ];
    }

    public function messages()
    {
        return [
            'response_note.required' => 'If you do not change any dates, you must provide a message.',
            'new_visit_date.before'  => 'The visit date must be before the contract start date.',
        ];
    }
}