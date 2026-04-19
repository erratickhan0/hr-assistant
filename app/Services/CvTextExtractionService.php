<?php

namespace App\Services;

use Illuminate\Support\Str;
use RuntimeException;
use Smalot\PdfParser\Parser;
use ZipArchive;

class CvTextExtractionService
{
    public function extract(string $absolutePath, string $mime): string
    {
        if (! is_readable($absolutePath)) {
            throw new RuntimeException('CV file is not readable.');
        }

        $mime = Str::lower($mime);

        if (str_contains($mime, 'pdf')) {
            return $this->extractPdf($absolutePath);
        }

        if (str_contains($mime, 'wordprocessingml') || str_contains($mime, 'officedocument')) {
            return $this->extractDocx($absolutePath);
        }

        if ($mime === 'application/msword') {
            throw new RuntimeException('Legacy .doc files are not supported yet; please upload PDF or .docx.');
        }

        throw new RuntimeException('Unsupported CV file type for text extraction.');
    }

    private function extractPdf(string $absolutePath): string
    {
        $parser = new Parser;
        $pdf = $parser->parseFile($absolutePath);
        $text = $pdf->getText();

        return trim($text) !== '' ? trim($text) : '';
    }

    private function extractDocx(string $absolutePath): string
    {
        $zip = new ZipArchive;
        if ($zip->open($absolutePath) !== true) {
            throw new RuntimeException('Could not open DOCX archive.');
        }

        $xml = $zip->getFromName('word/document.xml');
        $zip->close();

        if ($xml === false) {
            throw new RuntimeException('DOCX missing word/document.xml.');
        }

        $xml = str_replace(['</w:p>', '</w:tr>'], "\n", $xml);
        $text = strip_tags($xml);

        return trim(preg_replace("/[ \t]+/u", ' ', $text) ?? '') !== ''
            ? trim((string) preg_replace("/[ \t]+/u", ' ', $text))
            : '';
    }
}
