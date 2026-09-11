<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AnnouncementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'category' => ['required', Rule::in(['academic', 'events', 'emergency', 'general'])],
            'pinned' => ['boolean'],
            'is_published' => ['boolean'],
            'published_at' => ['required', 'date'],
            'title.en' => ['required', 'string', 'max:255'],
            'title.dv' => ['nullable', 'string', 'max:255'],
            'body.en' => ['required', 'string'],
            'body.dv' => ['nullable', 'string'],
            // Files already uploaded through the admin upload endpoint; only
            // their descriptions are stored on the announcement.
            'attachments' => ['array', 'max:20'],
            'attachments.*.name' => ['required', 'string', 'max:255'],
            'attachments.*.url' => ['required', 'string', 'max:2048'],
            'attachments.*.size' => ['nullable', 'string', 'max:32'],
            'attachments.*.extension' => ['nullable', 'string', 'max:16'],
        ];
    }

    public function validated($key = null, $default = null): array
    {
        $data = parent::validated();

        $data['pinned'] = $this->boolean('pinned');
        $data['is_published'] = $this->boolean('is_published');
        $data['title'] = ['en' => $data['title']['en'], 'dv' => $data['title']['dv'] ?? ''];
        $data['body'] = ['en' => $data['body']['en'], 'dv' => $data['body']['dv'] ?? ''];
        $data['attachments'] = array_values($data['attachments'] ?? []);

        return $data;
    }
}
