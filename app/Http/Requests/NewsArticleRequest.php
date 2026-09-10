<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class NewsArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        // Auto-generate a slug from the English title when left blank.
        $slug = $this->input('slug');

        if (blank($slug)) {
            $slug = Str::slug((string) $this->input('title.en'));
        }

        $this->merge(['slug' => Str::slug((string) $slug)]);
    }

    public function rules(): array
    {
        $id = $this->route('news')?->id;

        return [
            'slug' => ['required', 'string', 'max:255', Rule::unique('news_articles', 'slug')->ignore($id)],
            'category' => ['required', Rule::in(['schoolNews', 'achievements', 'events'])],
            'image' => ['nullable', 'string', 'max:2048'],
            'is_published' => ['boolean'],
            'published_at' => ['required', 'date'],
            'title.en' => ['required', 'string', 'max:255'],
            'title.dv' => ['nullable', 'string', 'max:255'],
            'excerpt.en' => ['required', 'string'],
            'excerpt.dv' => ['nullable', 'string'],
            'body.en' => ['required', 'string'],
            'body.dv' => ['nullable', 'string'],
        ];
    }

    public function validated($key = null, $default = null): array
    {
        $data = parent::validated();

        $data['is_published'] = $this->boolean('is_published');

        foreach (['title', 'excerpt', 'body'] as $field) {
            $data[$field] = [
                'en' => $data[$field]['en'],
                'dv' => $data[$field]['dv'] ?? '',
            ];
        }

        return $data;
    }
}
