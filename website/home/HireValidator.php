<?php

declare(strict_types=1);

namespace Website\Home;

use Core\Validation\Text;

/** Validation for the main page "You want us to do" work-request form (name, e-mail, services). */
final class HireValidator
{
    /** @var array<string, string> value (legacy checkbox value) => label (legacy checkbox text) */
    public const SERVICES = [
        'app design' => 'App Design',
        'graphic design' => 'Graphic Design',
        'motion design' => 'Motion Design',
        'ux design' => 'UX Design',
        'webdesign' => 'Webdesign',
        'marketing' => 'Marketing',
    ];

    /**
     * @param list<string> $services
     * @return array{data: array{name: string, email: string, services: list<string>}, errors: array<string, string>}
     */
    public function validate(string $name, string $email, array $services): array
    {
        $name = Text::line($name);
        $email = strtolower(Text::line($email));
        $services = array_values(array_unique(array_filter($services, static fn(string $s): bool => isset(self::SERVICES[$s]))));

        $errors = [];
        $length = mb_strlen($name);
        if ($length < 2 || $length > 80) {
            $errors['name'] = 'Please enter your name (2–80 characters).';
        }
        if ($email === '' || mb_strlen($email) > 254 || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $errors['email'] = 'Please enter a valid email address.';
        }

        return ['data' => compact('name', 'email', 'services'), 'errors' => $errors];
    }
}
