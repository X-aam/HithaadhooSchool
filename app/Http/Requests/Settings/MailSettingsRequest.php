<?php

namespace App\Http\Requests\Settings;

use App\Models\MailSetting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MailSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-users') ?? false;
    }

    public function rules(): array
    {
        $smtp = $this->input('mailer') === 'smtp';

        return [
            'mailer' => ['required', Rule::in(MailSetting::MAILERS)],
            'host' => [$smtp ? 'required' : 'nullable', 'string', 'max:255'],
            'port' => [$smtp ? 'required' : 'nullable', 'integer', 'between:1,65535'],
            'encryption' => ['nullable', Rule::in(MailSetting::ENCRYPTIONS)],
            'username' => ['nullable', 'string', 'max:255'],
            // Left blank on save to keep the stored password unchanged.
            'password' => ['nullable', 'string', 'max:255'],
            'from_address' => ['required', 'email', 'max:255'],
            'from_name' => ['required', 'string', 'max:255'],
            'verify_peer' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'host.required' => 'An SMTP server address is required.',
            'port.required' => 'An SMTP port is required (usually 587 for TLS or 465 for SSL).',
        ];
    }
}
