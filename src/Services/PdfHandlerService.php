<?php

namespace App\Services;

use Dompdf\Dompdf;

class PdfHandlerService
{
    public function createPdf(string $data): ?string
    {
        $file = new Dompdf();
        $file->loadHtml($data);
        $file->setPaper('letter', 'portrait');
        $file->render();

        return $file->output();
    }
}
