<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    protected $fillable = [
        'key',
        'mailable',
        'label',
        'subject',
        'body',
    ];

    public static function override(string $key): ?self
    {
        return static::where('key', $key)->first();
    }

    public function scopeForMailable($query, string $mailable)
    {
        return $query->where('mailable', $mailable);
    }

    /**
     * The Mailable class that resolves each template key's placeholder set.
     */
    protected static function mailableClassFor(string $key): ?string
    {
        return match (true) {
            $key === 'new_account' => \App\Mail\NewAccountMail::class,
            $key === 'new_vendor' => \App\Mail\NewVendorMail::class,
            $key === 'otp' => \App\Mail\OTPMail::class,
            str_starts_with($key, 'vendor_update_') => \App\Mail\VendorUpdateMail::class,
            str_starts_with($key, 'document_request_') => \App\Mail\DocumentRequestMail::class,
            default => null,
        };
    }

    /**
     * Read the actual '{{token}}' => ... keys out of the Mailable's build()
     * strtr() call, so the placeholder list is always in sync with what the
     * mailable really substitutes — no hardcoded/duplicated list to maintain.
     */
    public static function placeholdersFor(string $key): array
    {
        $mailableClass = static::mailableClassFor($key);
        if (!$mailableClass) {
            return [];
        }

        try {
            $source = file_get_contents((new \ReflectionClass($mailableClass))->getFileName());
        } catch (\Throwable $e) {
            return [];
        }

        preg_match_all('/\'(\{\{[a-z_]+\}\})\'\s*=>/', $source, $matches);

        return $matches[1] ?? [];
    }
}
