<?php

namespace App\Http\Controllers;

use App\Models\ProgressPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** Las fotos de progreso son privadas: solo las ve su dueño o un entrenador. */
class ProgressPhotoController extends Controller
{
    public function __invoke(Request $request, ProgressPhoto $photo): StreamedResponse
    {
        $user = $request->user();
        abort_unless($user->isAdmin() || $photo->user_id === $user->id, 404);
        abort_unless(Storage::disk('local')->exists($photo->path), 404);

        return Storage::disk('local')->response($photo->path, headers: ['Cache-Control' => 'private, max-age=86400']);
    }
}
