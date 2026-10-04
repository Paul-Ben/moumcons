<?php

namespace App\Http\Requests;

use App\Enums\PricingType;
use App\Enums\ServiceStatus;
use App\Http\Requests\Concerns\ContentRules;
use App\Models\Service;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * PRD §11 — create/edit a catalogue service.
 *
 * (Named ServiceRequestForm because App\Models\ServiceRequest is the customer
 * "request a service" record.) Status and featured need 'publish-services'.
 * A price is required only for pricing types that show one, and is cleared
 * otherwise so an unapproved figure never sits behind "Contact Us" (§11).
 */
class ServiceRequestForm extends FormRequest
{
    use ContentRules;

    public function authorize(): bool
    {
        $service = $this->route('service');
        $user = $this->user();

        $allowed = $service instanceof Service
            ? $user->can('update', $service)
            : $user->can('create', Service::class);

        if (! $allowed) {
            return false;
        }

        return ! $this->hasAny(['status', 'featured']) || $user->can('publish', Service::class);
    }

    public function rules(): array
    {
        $service = $this->route('service');
        $priced = [PricingType::Fixed->value, PricingType::StartingFrom->value];

        return [
            'business_division_id' => ['required', 'integer', 'exists:business_divisions,id'],
            'service_category_id' => ['nullable', 'integer', 'exists:service_categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => $this->slugRules('services', $service?->id),
            'short_description' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:50000'],
            'service_type' => ['nullable', 'string', 'max:255'],
            'pricing_type' => ['required', Rule::enum(PricingType::class)],
            'starting_price' => ['nullable', Rule::requiredIf(in_array($this->input('pricing_type'), $priced, true)), 'numeric', 'min:0', 'max:9999999999'],
            'status' => ['sometimes', 'required', Rule::enum(ServiceStatus::class)],
            'featured' => ['sometimes', 'boolean'],
            'image' => $this->imageRules(),
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            ...$this->seoRules(),
        ];
    }

    public function messages(): array
    {
        return $this->contentMessages() + [
            'business_division_id.required' => 'Choose the division that delivers this service.',
            'starting_price.required' => 'Enter the price for this pricing type.',
        ];
    }

    /** @return array<string, mixed> */
    public function serviceData(): array
    {
        $data = $this->validated();
        $data['sort_order'] ??= 0;

        if (! PricingType::from($data['pricing_type'])->priceIsPublic()) {
            $data['starting_price'] = null;
        }

        return $data;
    }
}
