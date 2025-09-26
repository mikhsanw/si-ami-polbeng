<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SafeFile implements ValidationRule
{
    protected $dangerousPatterns = [
        // === PHP code execution ===
        '/<\?php/i',
        '/\b(eval|assert|create_function|preg_replace)\s*\(/i',
        '/preg_replace\s*\(.*\/e[imsx]*\)/i', // preg_replace with /e modifier (obfuscation)
        '/file_get_contents\s*\(/i',           // panggilan file_get_contents di payload
        '/include\s*(\(|_once)/i',
        '/require\s*(\(|_once)/i',

        // === File system / command execution ===
        '/\b(system|exec|shell_exec|passthru|popen|proc_open)\s*\(/i',

        // === Encoding / Obfuscation ===
        '/base64_decode\s*\(/i',
        '/base64_encode\s*\(/i',
        '/gzinflate\s*\(/i',
        '/gzdecode\s*\(/i',
        '/str_rot13\s*\(/i',
        // heuristic: very long base64-like string (indikasi obfuscation)
        '/[A-Za-z0-9+\/]{100,}={0,2}/',

        // === Dangerous PHP Globals ===
        '/\$_(GET|POST|REQUEST|COOKIE|FILES|SERVER)\s*\[/i',

        // === JavaScript / HTML Injection ===
        '/<script\b[^>]*>/i',
        '/onerror\s*=/i',
        '/onload\s*=/i',
        '/document\.cookie/i',
        '/<iframe\b[^>]*>/i',
        '/<embed\b[^>]*>/i',
        '/<object\b[^>]*>/i',

        // === Suspicious debug / dump / exit ===
        '/\b(phpinfo|var_dump|print_r|die|exit)\s*\(/i',
    ];

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value->isValid()) {
            // Cek isi file dari temporary path
            $content = file_get_contents($value->getRealPath(), false, null, 0, 5000);

            foreach ($this->dangerousPatterns as $pattern) {
                if (preg_match($pattern, $content)) {
                    $fail('File mengandung konten berbahaya, silakan periksa kembali.');
                    // $fail("File $attribute mengandung konten berbahaya.");
                }
            }
        }
    }
}
