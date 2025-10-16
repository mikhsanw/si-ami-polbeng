<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class FileAllowed implements Rule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    protected $allowedExt;

    protected $allowedMimes;

    public function __construct(?array $allowedExt = null, ?array $allowedMimes = null)
    {
        $this->allowedExt = $allowedExt ?? ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'jpg', 'jpeg', 'png'];
        $this->allowedMimes = $allowedMimes ?? [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'text/plain',
            'image/jpeg',
            'image/png',
        ];
    }

    public function passes($attribute, $value)
    {
        if (! $value) {
            return false;
        }

        $clientExt = strtolower($value->getClientOriginalExtension() ?: '');
        $origName = $value->getClientOriginalName();
        $mime = $value->getMimeType() ?: $value->getClientMimeType();

        if ($clientExt !== '' && in_array($clientExt, $this->allowedExt)) {
            return true;
        }

        $extFromName = strtolower(pathinfo($origName, PATHINFO_EXTENSION) ?: '');
        if ($extFromName !== '' && in_array($extFromName, $this->allowedExt)) {
            return true;
        }

        if ($mime && in_array($mime, $this->allowedMimes)) {
            return true;
        }

        return false;
    }

    public function message()
    {
        return 'File tidak diizinkan (ekstensi atau mime tidak dikenal).';
    }
}
