<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Quote;
use App\Models\SiteSetting;

class WhatsAppService
{
    /**
     * Default WhatsApp advisors and numbers.
     *
     * @return array<int, array{name: string, phone: string}>
     */
    public static function defaultAdvisors(): array
    {
        return [
            ['name' => 'Angie', 'phone' => '977 208 514'],
            ['name' => 'Gracia', 'phone' => '933 399 010'],
            ['name' => 'Sabrina', 'phone' => '922 353 855'],
            ['name' => '', 'phone' => '977 983 045'],
        ];
    }

    /**
     * Normalize a Peruvian phone number to international digits (e.g. 51977208514).
     */
    public function normalizePhoneDigits(string $rawNumber): string
    {
        $digits = preg_replace('/\D/', '', $rawNumber) ?: '';
        if ($digits === '') {
            return '51977208514';
        }

        if (strlen($digits) === 9 && str_starts_with($digits, '9')) {
            return '51'.$digits;
        }

        return $digits;
    }

    /**
     * Return all configured WhatsApp advisors with their formatted numbers, names, and wa.me URLs.
     *
     * @return array<int, array{name: string, phone: string, digits: string, url: string}>
     */
    public function getAdvisors(?string $customMessage = null): array
    {
        $stored = SiteSetting::getValue('whatsapp_advisors', self::defaultAdvisors());
        if (is_string($stored)) {
            $decoded = json_decode($stored, true);
            $stored = is_array($decoded) ? $decoded : self::defaultAdvisors();
        }

        if (! is_array($stored) || empty($stored)) {
            $stored = self::defaultAdvisors();
        }

        $advisors = [];
        foreach ($stored as $row) {
            $phone = trim((string) ($row['phone'] ?? ''));
            if ($phone === '') {
                continue;
            }
            $name = trim((string) ($row['name'] ?? ''));
            $digits = $this->normalizePhoneDigits($phone);

            $advisors[] = [
                'name' => $name,
                'phone' => $phone,
                'digits' => $digits,
                'url' => $this->advisorUrl($phone, $name, $customMessage),
            ];
        }

        if (empty($advisors)) {
            foreach (self::defaultAdvisors() as $row) {
                $advisors[] = [
                    'name' => $row['name'],
                    'phone' => $row['phone'],
                    'digits' => $this->normalizePhoneDigits($row['phone']),
                    'url' => $this->advisorUrl($row['phone'], $row['name'], $customMessage),
                ];
            }
        }

        return $advisors;
    }

    /**
     * Build a pre-filled WhatsApp URL for a specific advisor phone number.
     */
    public function advisorUrl(string $rawPhone, ?string $advisorName = null, ?string $customMessage = null): string
    {
        $digits = $this->normalizePhoneDigits($rawPhone);

        if ($customMessage !== null && trim($customMessage) !== '') {
            $message = trim($customMessage);
        } else {
            $defaultMessage = (string) SiteSetting::getValue(
                'whatsapp_default_message',
                'Hola IMPORT HNOS SAN LUIS S.A.C., deseo recibir asesoría técnica y cotización sobre su catálogo de productos.'
            );
            $message = trim($defaultMessage);
            if ($advisorName !== null && trim($advisorName) !== '') {
                $message = sprintf('Hola %s (IMPORT HNOS SAN LUIS S.A.C.), deseo cotizar productos de su catálogo.', trim($advisorName));
            }
        }

        return sprintf('https://wa.me/%s?text=%s', $digits, rawurlencode($message));
    }

    /**
     * Return the sanitized primary WhatsApp Business phone number (digits only, with country code).
     */
    public function getBusinessNumber(): string
    {
        $rawNumber = (string) SiteSetting::getValue(
            'whatsapp_business_number',
            env('WHATSAPP_BUSINESS_NUMBER', '51977208514')
        );

        $digits = $this->normalizePhoneDigits($rawNumber);
        if ($digits === '51900000000') {
            $advisors = $this->getAdvisors();
            if (! empty($advisors[0]['digits'])) {
                return $advisors[0]['digits'];
            }

            return '51977208514';
        }

        return $digits;
    }

    /**
     * Build a pre-filled WhatsApp URL for inquiring about a specific product.
     * Never exposes internal or private information.
     */
    public function productInquiryUrl(Product $product, ?string $productUrl = null): string
    {
        $number = $this->getBusinessNumber();
        $url = $productUrl ?: url('/catalogo/'.$product->slug);

        $message = sprintf(
            "Hola IMPORT HNOS SAN LUIS S.A.C., deseo consultar disponibilidad y cotización del siguiente producto:\n• Producto: %s\n• SKU: %s\n• Unidad: %s\n• Enlace: %s",
            $product->name,
            $product->sku,
            $product->sale_unit,
            $url
        );

        return sprintf('https://wa.me/%s?text=%s', $number, rawurlencode($message));
    }

    /**
     * Build a pre-filled WhatsApp URL referencing a submitted quote (COT-YYYY-NNNNN).
     */
    public function quoteSummaryUrl(Quote $quote): string
    {
        $quote->loadMissing('items');
        $number = $this->getBusinessNumber();
        $entityLabel = $quote->company_name
            ? sprintf('%s (%s)', $quote->company_name, $quote->contact_name)
            : $quote->contact_name;

        $message = sprintf(
            'Hola IMPORT HNOS SAN LUIS S.A.C., he registrado la solicitud de cotización N° %s con %d ítem(s) a nombre de %s. Quedo atento(a) a su atención comercial.',
            $quote->reference_code,
            $quote->items->count(),
            $entityLabel
        );

        return sprintf('https://wa.me/%s?text=%s', $number, rawurlencode($message));
    }

    /**
     * Build a general commercial contact WhatsApp URL.
     */
    public function generalContactUrl(?string $customMessage = null): string
    {
        $number = $this->getBusinessNumber();
        $defaultMessage = (string) SiteSetting::getValue(
            'whatsapp_default_message',
            'Hola IMPORT HNOS SAN LUIS S.A.C., deseo recibir asesoría técnica y comercial sobre su catálogo de productos.'
        );

        $message = trim((string) ($customMessage ?: $defaultMessage));

        return sprintf('https://wa.me/%s?text=%s', $number, rawurlencode($message));
    }
}
