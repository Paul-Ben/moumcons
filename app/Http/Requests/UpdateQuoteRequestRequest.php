<?php

namespace App\Http\Requests;

use App\Enums\QuoteStatus;
use App\Models\QuoteRequest;
use App\Policies\QuoteRequestPolicy;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * PRD §13 — admin handling of a quote request.
 *
 * Authorised per field, like enquiries: progression, ownership and notes need
 * 'update-quotes'; the price and message and the move to Quote Sent need
 * 'create-quotes'; settling (or reopening) a quote needs 'close-quotes'.
 * A field the user may not change is refused, never silently dropped.
 */
class UpdateQuoteRequestRequest extends FormRequest
{
    private const PREPARE_FIELDS = ['quoted_amount', 'quote_message', 'quote_valid_until'];

    private const UPDATE_FIELDS = ['assigned_to', 'internal_notes', 'status'];

    public function authorize(): bool
    {
        $user = $this->user();
        $quote = $this->route('quoteRequest');

        if ($user === null || ! $quote instanceof QuoteRequest) {
            return false;
        }

        $mayUpdate = $user->can('update', $quote);
        $mayPrepare = $user->can('prepare', $quote);

        if (! $mayUpdate && ! $mayPrepare) {
            return false;
        }

        if (! $mayUpdate && $this->hasAny(self::UPDATE_FIELDS)) {
            return false;
        }

        if (! $mayPrepare && $this->hasAny(self::PREPARE_FIELDS)) {
            return false;
        }

        if ($this->filled('status')) {
            $status = (string) $this->input('status');

            if (QuoteRequestPolicy::statusTransitionRequiresClosePermission($quote, $status)
                && $user->cannot('close', $quote)) {
                return false;
            }

            if (QuoteRequestPolicy::statusTransitionRequiresPreparePermission($quote, $status) && ! $mayPrepare) {
                return false;
            }
        }

        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['sometimes', 'required', Rule::enum(QuoteStatus::class)],
            'assigned_to' => ['sometimes', 'nullable', 'integer', Rule::exists('users', 'id')->where('is_active', true)],
            'internal_notes' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'quoted_amount' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:999999999999'],
            'quote_message' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'quote_valid_until' => ['sometimes', 'nullable', 'date', 'after_or_equal:today'],
            'notify_customer' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * A quote cannot be sent empty: moving to Quote Sent needs an amount or a
     * written quote, taken from this submission or already on the record.
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $quote = $this->route('quoteRequest');

                if ($this->input('status') !== QuoteStatus::QuoteSent->value || $quote->status === QuoteStatus::QuoteSent) {
                    return;
                }

                $amount = $this->has('quoted_amount') ? $this->input('quoted_amount') : $quote->quoted_amount;
                $message = $this->has('quote_message') ? $this->input('quote_message') : $quote->quote_message;

                if (blank($amount) && blank($message)) {
                    $validator->errors()->add('status', 'Add a quoted amount or a quote message before sending the quote.');
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'assigned_to.exists' => 'That user cannot be assigned — choose an active member of staff.',
            'internal_notes.max' => 'Internal notes are limited to 5000 characters.',
            'status.enum' => 'That is not a valid quote status.',
            'quote_valid_until.after_or_equal' => 'The quote cannot expire in the past.',
        ];
    }

    /** @return array<string, mixed> columns to persist */
    public function triagePayload(): array
    {
        return collect($this->validated())->except('notify_customer')->all();
    }

    public function shouldNotifyCustomer(): bool
    {
        return $this->boolean('notify_customer');
    }
}
