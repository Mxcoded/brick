<?php

namespace Modules\Website\Support;

use Illuminate\Support\Str;
use Modules\Website\Models\NewsletterSubscriber;

class NewsletterPersonalizer
{
    /**
     * The placeholders admins can use in a campaign.
     *
     * @var array<string, string>
     */
    public const TAGS = [
        'name' => 'Full name',
        'first_name' => 'First name',
        'email' => 'Email address',
    ];

    /**
     * Replace {{ name }}, {{ first_name }} and {{ email }} with the subscriber's data.
     *
     * Values are HTML-escaped by default because campaign content is rendered
     * unescaped; pass $escape = false for plain-text targets like the subject.
     */
    public static function apply(?string $text, NewsletterSubscriber $subscriber, bool $escape = true): string
    {
        $text = (string) $text;

        if ($text === '' || ! str_contains($text, '{{')) {
            return $text;
        }

        $fullName = trim((string) $subscriber->name);
        $firstName = $fullName === '' ? '' : Str::before($fullName, ' ');

        $values = [
            'name' => $fullName !== '' ? $fullName : 'Valued Subscriber',
            'first_name' => $firstName !== '' ? $firstName : 'there',
            'email' => (string) $subscriber->email,
        ];

        if ($escape) {
            $values = array_map(fn (string $value) => e($value), $values);
        }

        return preg_replace_callback(
            '/\{\{\s*(name|first_name|email)\s*\}\}/i',
            fn (array $matches) => $values[strtolower($matches[1])],
            $text
        );
    }

    /**
     * Whether the content already greets the subscriber by name, in which case
     * the automatic "Dear ..." line would duplicate it.
     */
    public static function containsNameTag(?string $text): bool
    {
        return preg_match('/\{\{\s*(name|first_name)\s*\}\}/i', (string) $text) === 1;
    }
}
