<?php

namespace App\Http\Requests\Events;

use App\Models\Event;
use App\Models\Venue;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_id' => ['required', 'integer', 'exists:clients,id'],
            'type' => ['required', Rule::in(Event::TYPES)],
            'date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'venue_id' => ['required', 'integer', 'exists:venues,id'],
            'guest_count' => ['required', 'integer', 'min:1'],
            'budget' => ['required', 'numeric', 'min:0'],
            'charged_user_id' => ['required', 'integer', 'exists:users,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'client_id.required' => 'Le client est obligatoire.',
            'client_id.exists' => 'Le client sélectionné n\'existe pas.',
            'type.required' => 'Le type d\'événement est obligatoire.',
            'type.in' => 'Le type d\'événement sélectionné n\'est pas valide.',
            'date.required' => 'La date est obligatoire.',
            'date.after_or_equal' => 'La date ne peut pas être dans le passé.',
            'start_time.required' => 'L\'heure de début est obligatoire.',
            'end_time.required' => 'L\'heure de fin est obligatoire.',
            'end_time.after' => 'L\'heure de fin doit être après l\'heure de début.',
            'venue_id.required' => 'Le lieu est obligatoire.',
            'venue_id.exists' => 'Le lieu sélectionné n\'existe pas.',
            'guest_count.required' => 'Le nombre d\'invités est obligatoire.',
            'guest_count.min' => 'Le nombre d\'invités doit être au moins de 1.',
            'budget.required' => 'Le budget est obligatoire.',
            'budget.min' => 'Le budget ne peut pas être négatif.',
            'charged_user_id.required' => 'Le chargé d\'événements est obligatoire.',
            'charged_user_id.exists' => 'Le chargé d\'événements sélectionné n\'existe pas.',
        ];
    }

    /**
     * Add conditional validation for venue capacity and double booking.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $data = $validator->getData();

            // RG4: Guest count must not exceed venue capacity
            if (!empty($data['venue_id']) && !empty($data['guest_count'])) {
                $venue = Venue::find($data['venue_id']);
                if ($venue && $data['guest_count'] > $venue->capacity) {
                    $validator->errors()->add('guest_count', "Le nombre d'invités ({$data['guest_count']}) dépasse la capacité du lieu ({$venue->capacity}).");
                }
            }

            // RG3: No double booking
            if (!empty($data['venue_id']) && !empty($data['date']) && !empty($data['start_time']) && !empty($data['end_time'])) {
                $conflict = Event::venueConflictExists(
                    $data['venue_id'],
                    $data['date'],
                    $data['start_time'],
                    $data['end_time']
                );

                if ($conflict) {
                    $validator->errors()->add('venue_id', 'Ce lieu est déjà réservé pour cette plage horaire.');
                }
            }
        });
    }
}
