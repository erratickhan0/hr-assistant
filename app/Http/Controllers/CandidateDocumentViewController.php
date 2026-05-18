<?php

namespace App\Http\Controllers;

use App\Models\CandidateDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CandidateDocumentViewController extends Controller
{
    public function __invoke(Request $request, CandidateDocument $document): StreamedResponse
    {
        $this->authorize('view', $document);

        $filename = str_replace(['"', '\\'], '', $document->original_name);

        return Storage::disk($document->disk)->response(
            $document->path,
            $document->original_name,
            [
                'Content-Type' => $document->mime,
                'Content-Disposition' => 'inline; filename="'.$filename.'"',
            ],
            'inline',
        );
    }
}
