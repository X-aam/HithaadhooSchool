<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FileUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-content') ?? false;
    }

    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,csv,txt,zip,jpeg,jpg,png,webp,gif',
                'max:20480',
            ],
            // Directory to upload into, relative to the public disk root. The
            // controller normalises it and rejects anything that escapes.
            'path' => ['nullable', 'string', 'max:255'],
        ];
    }
}
