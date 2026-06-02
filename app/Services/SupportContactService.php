<?php

namespace App\Services;

class SupportContactService
{
    private const WHATSAPP_MESSAGE = 'Hola, necesito restablecer mi contraseña de AviCore. Mi documento es: ';

    private const EMAIL_SUBJECT = 'Recuperar contraseña AviCore';

    private const EMAIL_BODY = 'Hola, necesito restablecer mi contraseña. Mi documento es: ';

    /**
     * @return array{
     *     whatsapp_display: string,
     *     email: string,
     *     whatsapp_url: string|null,
     *     mailto_url: string|null,
     *     has_whatsapp: bool,
     *     has_email: bool,
     * }
     */
    public function contacts(): array
    {
        $support = config('avicore.support', []);

        $whatsappUrl = $this->whatsappUrl((string) ($support['whatsapp'] ?? ''));
        $email = trim((string) ($support['email'] ?? ''));
        $mailtoUrl = $this->mailtoUrl($email);

        return [
            'whatsapp_display' => trim((string) ($support['whatsapp_display'] ?? '')),
            'email' => $email,
            'whatsapp_url' => $whatsappUrl,
            'mailto_url' => $mailtoUrl,
            'has_whatsapp' => $whatsappUrl !== null,
            'has_email' => $mailtoUrl !== null,
        ];
    }

    public function whatsappUrl(string $whatsapp): ?string
    {
        $digits = preg_replace('/\D+/', '', $whatsapp);

        if ($digits === '') {
            return null;
        }

        return 'https://wa.me/'.$digits.'?text='.rawurlencode(self::WHATSAPP_MESSAGE);
    }

    public function mailtoUrl(string $email): ?string
    {
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return null;
        }

        return 'mailto:'.$email
            .'?subject='.rawurlencode(self::EMAIL_SUBJECT)
            .'&body='.rawurlencode(self::EMAIL_BODY);
    }

    public function hasValidContactChannel(): bool
    {
        $contacts = $this->contacts();

        return $contacts['has_whatsapp'] || $contacts['has_email'];
    }
}
