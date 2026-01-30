<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MediaController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function show($path)
    {
        // Check if file exists in 'public' disk
        if (!Storage::disk('public')->exists($path)) {
            abort(404);
        }

        // Serve the file directly
        return Storage::disk('public')->response($path);
    }
}
