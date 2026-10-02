<?php

declare(strict_types=1);

namespace Website\Contact;

use Core\Validation\Text;

/**
 * Server-side validation and normalisation for the Contact page form (email, subject, message).
 * Output is plain text; escaping happens at render time, never here.
 */
final class ContactValidator
{
    public const SUBJECT_MAX = 120;
    public const MESSAGE_MIN = 10;
    public const MESSAGE_MAX = 4000;

    /**
     * @return array{data: array{email: string, subject: string, message: string}, errors: array<string, string>}
     */
    public function validate(string $email, string $subject, string $message): array
    {
        $email = strtolower(Text::line($email));
        $subject = Text::line($subject);
        $message = Text::multiline($message);

        $errors = [];

        if ($email === '' || mb_strlen($email) > 254 || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $errors['email'] = 'Please enter a valid email address.';
        }

        $subjectLength = mb_strlen($subject);
        if ($subjectLength < 2 || $subjectLength > self::SUBJECT_MAX) {
            $errors['subject'] = 'Please enter a subject (2–' . self::SUBJECT_MAX . ' characters).';
        }

        $messageLength = mb_strlen($message);
        if ($messageLength < self::MESSAGE_MIN || $messageLength > self::MESSAGE_MAX) {
            $errors['message'] = 'Please tell us a little more (' . self::MESSAGE_MIN . '–' . self::MESSAGE_MAX . ' characters).';
        }

        return ['data' => compact('email', 'subject', 'message'), 'errors' => $errors];
    }
}
