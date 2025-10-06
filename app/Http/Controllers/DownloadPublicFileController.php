<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DownloadPublicFileController extends Controller
{
    public function __invoke(Request $request, string $path): StreamedResponse
    {
        // TODO: Optionally authorize that the current user owns/has access to the expense/file
        // e.g. check DB relation from $path -> expense -> user_id === auth()->id()

        abort_unless(Storage::disk('public')->exists($path), 404);

        $filename = basename($path);
        return Storage::disk('public')->download($path, $filename);
    }
}
