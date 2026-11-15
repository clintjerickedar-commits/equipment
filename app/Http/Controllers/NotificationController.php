<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function markRead(Request $request, NotificationService $notifications): JsonResponse
    {
        $validated = $request->validate([
            'keys' => ['required', 'array', 'max:7'],
            'keys.*' => ['required', 'string', 'max:255'],
        ]);

        $notifications->markRead($request->user(), $validated['keys']);

        return response()->json(['marked' => count($validated['keys'])]);
    }
}
