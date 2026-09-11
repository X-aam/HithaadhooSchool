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
        $oauth = in_array($this->input('auth_type'), ['microsoft', 'google'], true);

        return [
            'mailer' => ['required', Rule::in(MailSetting::MAILERS)],
            // Absent means classic SMTP; only the settings form sends it.
            'auth_type' => ['nullable', Rule::in(['password', 'microsoft', 'google'])],
            'host' => [$smtp ? 'required' : 'nullable', 'string', 'max:255'],
            'port' => [$smtp ? 'required' : 'nullable', 'integer', 'between:1,65535'],
            'encryption' => ['nullable', Rule::in(MailSetting::ENCRYPTIONS)],
            'username' => ['nullable', 'string', 'max:255'],
            // Left blank on save to keep the stored password unchanged.
            'password' => ['nullable', 'string', 'max:255'],

            /*
             * OAuth fields. The client secret follows the same rule as the
             * password: blank means "keep what is stored".
             */
            'oauth_client_id' => [$oauth ? 'required' : 'nullable', 'string', 'max:255'],
            'oauth_client_secret' => ['nullable', 'string', 'max:512'],
            'oauth_email' => [$oauth ? 'required' : 'nullable', 'email', 'max:255'],
            'oauth_tenant' => ['nullable', 'string', 'max:255'],
            'from_address' => ['required', 'email', 'max:255'],
            'from_name' => ['required', 'string', 'max:255'],
            'verify_peer' => ['boolean'],
        ];
    }

    /**
     * @param  string|null  $key
     * @param  mixed  $default
     * @return array<string, mixed>
     */
    public function validated($key = null, $default = null): array
    {
        $data = parent::validated();

        // Absent means classic SMTP username/password.
        $data['auth_type'] ??= 'password';

        return $data;
    }

    public function messages(): array
    {
        return [
            'host.required' => 'An SMTP server address is required.',
            'oauth_client_id.required' => 'The application (client) ID from the provider is required.',
            'oauth_email.required' => 'The mailbox address to send from is required.',
            'port.required' => 'An SMTP port is required (usually 587 for TLS or 465 for SSL).',
        ];
    }
}
