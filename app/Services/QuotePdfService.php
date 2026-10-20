<?php

namespace App\Services;

use App\Models\Quote;
use App\Models\SiteSetting;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\HttpFoundation\Response;

class QuotePdfService
{
    /**
     * Render the quote PDF binary string using Dompdf.
     */
    public function generatePdfContent(Quote $quote): string
    {
        $quote->loadMissing(['items.product', 'customer', 'assignedSeller']);

        $html = view('pdf.quote', [
            'quote' => $quote,
            'companyName' => SiteSetting::getValue('company_commercial_name', 'Import Hnos San Luis'),
            'companyLegalName' => SiteSetting::getValue('company_legal_name', 'IMPORT HNOS SAN LUIS S.A.C.'),
            'companyRuc' => SiteSetting::getValue('company_ruc', '20608628143'),
            'companyAddress' => SiteSetting::getValue('company_address', 'Av. Guillermo Dansey 429 - Lima Centro, Lima, Perú'),
            'companyEmail' => SiteSetting::getValue('company_contact_email', 'ventas@hnssanluis.com'),
            'companyPhone' => SiteSetting::getValue('company_contact_phone', '977 208 514'),
        ])->render();

        $options = new Options;
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', false);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return (string) $dompdf->output();
    }

    /**
     * Stream or download the PDF as an HTTP response.
     */
    public function streamPdf(Quote $quote, bool $download = false): Response
    {
        $pdfContent = $this->generatePdfContent($quote);
        $filename = sprintf('Cotizacion-%s.pdf', $quote->reference_code);
        $disposition = $download ? 'attachment' : 'inline';

        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => sprintf('%s; filename="%s"', $disposition, $filename),
        ]);
    }
}
