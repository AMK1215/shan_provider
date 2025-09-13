<?php

namespace App\Http\Requests\Slot;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SlotWebhookRequest extends FormRequest
{
    /**
     * The cached member instances
     */
    protected array $members = [];

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'batch_requests' => 'required|array',
            'batch_requests.*.member_account' => 'required|string|max:50',
            'batch_requests.*.product_code' => 'required|integer',
            'operator_code' => 'required|string|size:4',
            'currency' => 'required|string',
            'sign' => 'required|string',
            'request_time' => 'required|integer',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        // Convert operator_code to uppercase if it exists
        if ($this->has('operator_code')) {
            $this->merge([
                'operator_code' => Str::upper($this->operator_code),
            ]);
        }
    }

    /**
     * Get the batch requests array
     */
    public function getBatchRequests(): array
    {
        return $this->input('batch_requests', []);
    }

    /**
     * Get the operator code
     */
    public function getOperatorCode(): string
    {
        return $this->input('operator_code');
    }

    /**
     * Get the currency code
     */
    public function getCurrency(): string
    {
        return $this->input('currency');
    }

    /**
     * Get the request signature
     */
    public function getSign(): string
    {
        return $this->input('sign');
    }

    /**
     * Get the request timestamp
     */
    public function getRequestTime(): int
    {
        return $this->input('request_time');
    }

    /**
     * Get the API method name from the URL
     */
    public function getMethodName(): string
    {
        return strtolower(Str::afterLast($this->path(), '/'));
    }

    /**
     * Get the member/user associated with a member account
     */
    public function getMember(string $memberAccount): ?User
    {
        // Return cached member if already retrieved
        if (isset($this->members[$memberAccount])) {
            return $this->members[$memberAccount];
        }

        // Find and cache the member
        $member = User::where('user_name', $memberAccount)->first();
        $this->members[$memberAccount] = $member;

        return $member;
    }

    /**
     * Get all members for the batch requests
     *
     * @return array<string, User|null> Array of members keyed by member_account
     */
    public function getAllMembers(): array
    {
        $members = [];
        foreach ($this->getBatchRequests() as $request) {
            $memberAccount = $request['member_account'];
            if (! isset($this->members[$memberAccount])) {
                $this->members[$memberAccount] = User::where('user_name', $memberAccount)->first();
            }
            $members[$memberAccount] = $this->members[$memberAccount];
        }

        return $members;
    }
}
