<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DownloadPublicFileController extends Controller
{
    public function expenseDownload(Request $request, string $path): StreamedResponse
    {
        abort_unless(Storage::disk('public')->exists($path), 404);

        $filename = basename($path);
        return Storage::disk('public')->download($path, $filename);
    }
}
