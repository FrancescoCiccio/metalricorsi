<?php

namespace App\Support;

use Closure;

/**
 * Allowed file types for every upload in the portal.
 *
 * The *Rules() helpers check the extension (what the web server uses to decide
 * how to serve a file); pair them with ->acceptedFileTypes(...MIMES), which checks
 * the real MIME type detected from the content. A PHP webshell renamed to .png
 * or disguised as a GIF is rejected either way.
 */
class SafeUploads
{
    public const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp'];

    public const IMAGE_MIMES = ['image/jpeg', 'image/png', 'image/webp'];

    public const DOCUMENT_EXTENSIONS = ['pdf', 'zip', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'];

    public const DOCUMENT_MIMES = [
        'application/pdf',
        'application/zip',
        'application/x-zip-compressed',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    ];

    /** Extensions that must never be stored, regardless of field configuration. */
    public const BLOCKED_EXTENSION_PATTERN = '/\.(php\d?|phtml|phar|pht|phps|cgi|pl|py|sh|bash|exe|jsp|asp|aspx|htaccess|htpasswd|user\.ini|ini|svg|svgz|html?|xhtml|js|mjs)$/i';

    public static function imageRules(int $maxKb): array
    {
        return [
            'file',
            'extensions:' . implode(',', self::IMAGE_EXTENSIONS),
            "max:{$maxKb}",
        ];
    }

    public static function documentRules(int $maxKb): array
    {
        return [
            'file',
            'extensions:' . implode(',', self::DOCUMENT_EXTENSIONS),
            "max:{$maxKb}",
        ];
    }

    /** Extension whitelist for Livewire's temporary upload endpoint (union of all fields). */
    public static function temporaryUploadRules(int $maxKb): array
    {
        return [
            'file',
            'extensions:' . implode(',', array_merge(self::IMAGE_EXTENSIONS, self::DOCUMENT_EXTENSIONS)),
            'safe_filename', // registered in AppServiceProvider (a closure would break config:cache)
            "max:{$maxKb}",
        ];
    }

    /** True when the uploaded file name is executable/scriptable (also double extensions like x.php.png). */
    public static function isDangerous(mixed $value): bool
    {
        if (! is_object($value) || ! method_exists($value, 'getClientOriginalName')) {
            return false;
        }

        $name = (string) $value->getClientOriginalName();

        return (bool) (preg_match(self::BLOCKED_EXTENSION_PATTERN, $name) || preg_match('/\.(php\d?|phtml|phar)\./i', $name));
    }

    /** Safety net rule for Filament fields. */
    public static function blockDangerousNames(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            if (self::isDangerous($value)) {
                $fail('Tipo di file non consentito.');
            }
        };
    }
}
