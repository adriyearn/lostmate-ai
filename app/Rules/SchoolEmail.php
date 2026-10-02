<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;

/**
 * Only allows emails from the school's own domain(s), configured with
 * SCHOOL_EMAIL_DOMAINS in .env. If no domains are configured, every
 * email passes.
 */
class SchoolEmail implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $domains = config('lostmate.school_email_domains');

        if (empty($domains)) {
            return;
        }

        $emailDomain = Str::lower(Str::after((string) $value, '@'));

        if (! in_array($emailDomain, array_map([Str::class, 'lower'], $domains), true)) {
            $fail('Please register with your school email address (@'.implode(' or @', $domains).').');
        }
    }
}
