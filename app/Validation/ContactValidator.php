<?php

declare(strict_types=1);

namespace App\Validation;

/**
 * Server-side validation and normalisation for the contact / work-request form.
 * Output is plain text; escaping happens at render time, never here.
 */
final class ContactValidator
{
    /** @var array<string, string> value => label */
    public const SERVICES = [
        'web' => 'Websites & web apps',
        'platform' => 'Platforms & backend',
        'automation' => 'Automation & integrations',
        'design' => 'Design & branding',
        'ai' => 'AI-assisted tooling',
        'other' => 'Something else',
    ];

    /**
     * @param list<string> $services
     * @return array{data: array{name: string, email: string, company: string, services: list<string>, message: string}, errors: array<string, string>}
     */
    public function validate(string $name, string $email, string $company, array $services, string $message): array
    {
        $name = Text::line($name);
        $email = strtolower(Text::line($email));
        $company = Text::line($company);
        $message = Text::multiline($message);
        $services = array_values(array_unique(array_filter($services, static fn(string $s): bool => isset(self::SERVICES[$s]))));

        $errors = [];

        $nameLength = mb_strlen($name);
        if ($nameLength < 2 || $nameLength > 80) {
            $errors['name'] = 'Please enter your name (2–80 characters).';
        }

        if ($email === '' || mb_strlen($email) > 254 || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $errors['email'] = 'Please enter a valid email address.';
        }

        if (mb_strlen($company) > 120) {
            $errors['company'] = 'Company name is too long (max 120 characters).';
        }

        $messageLength = mb_strlen($message);
        if ($messageLength < 10 || $messageLength > 4000) {
            $errors['message'] = 'Please tell us a little more (10–4000 characters).';
        }

        return ['data' => compact('name', 'email', 'company', 'services', 'message'), 'errors' => $errors];
    }
}
