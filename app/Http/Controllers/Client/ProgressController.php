<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\ProgressEntry;
use App\Models\ProgressPhoto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProgressController extends Controller
{
    public function index(Request $request): View
    {
        $measure = array_key_exists($request->query('medida'), ProgressEntry::MEASURES) ? $request->query('medida') : 'weight_kg';
        $user = $request->user()->load('progressEntries');
        $entries = $user->progressEntries;

        return view('client.progress', [
            'measure' => $measure,
            'entries' => $entries,
            'available' => collect(ProgressEntry::MEASURES)->filter(fn ($m, $field) => $entries->whereNotNull($field)->isNotEmpty()),
            'latest' => $entries->last(),
            'first' => $entries->first(),
            'history' => $user->progressEntries()->reorder('recorded_on', 'desc')->orderByDesc('id')->paginate(10, ['*'], 'registros')->withQueryString()->fragment('historial'),
            'photos' => $user->progressPhotos()->orderByDesc('id')->paginate(6, ['*'], 'fotos')->withQueryString()->fragment('fotos'),
            'comparisonPhotos' => $user->progressPhotos()->where('pose', 'front')->reorder('taken_on')->orderBy('id')->get(),
            'today' => $entries->first(fn ($e) => $e->recorded_on->isToday()),
        ]);
    }

    /** Crea o actualiza el registro del día: una fecha = un registro. */
    public function store(Request $request): RedirectResponse
    {
        $measures = array_keys(ProgressEntry::MEASURES);
        $data = $request->validate([
            'recorded_on' => ['nullable', 'date', 'before_or_equal:today', 'after:-5 years'],
            'weight_kg' => ['nullable', 'numeric', 'between:20,400', 'required_without_all:'.implode(',', array_diff($measures, ['weight_kg']))],
            'body_fat_pct' => ['nullable', 'numeric', 'between:2,70'],
            'waist_cm' => ['nullable', 'numeric', 'between:30,250'],
            'hips_cm' => ['nullable', 'numeric', 'between:30,250'],
            'chest_cm' => ['nullable', 'numeric', 'between:30,250'],
            'arms_cm' => ['nullable', 'numeric', 'between:10,100'],
            'legs_cm' => ['nullable', 'numeric', 'between:20,150'],
            'notes' => ['nullable', 'string', 'max:500'],
        ], ['weight_kg.required_without_all' => 'Anota al menos tu peso o una medida.'], [
            'weight_kg' => 'peso', 'body_fat_pct' => 'grasa corporal', 'waist_cm' => 'cintura', 'hips_cm' => 'cadera',
            'chest_cm' => 'pecho', 'arms_cm' => 'brazos', 'legs_cm' => 'piernas', 'recorded_on' => 'fecha',
        ]);

        $date = $data['recorded_on'] ?? today()->toDateString();
        // El registro rápido solo trae el peso: no borra las medidas que ya hubiera ese día.
        $values = collect($data)->except('recorded_on')->reject(fn ($v) => $v === null || $v === '')->all();

        $entry = $request->user()->progressEntries()->updateOrCreate(['recorded_on' => $date], $values);
        $previous = $request->user()->progressEntries()->where('recorded_on', '<', $date)->whereNotNull('weight_kg')->reorder('recorded_on', 'desc')->first();

        $message = '¡Registrado!';
        if ($entry->weight_kg && $previous) {
            $diff = round($entry->weight_kg - $previous->weight_kg, 1);
            $message .= match (true) {
                $diff < 0 => ' Bajaste '.abs($diff).' kg desde la última vez. 💪',
                $diff > 0 => ' Subiste '.$diff.' kg desde la última vez.',
                default => ' Mismo peso que la última vez.',
            };
        }

        return back()->with('success', $message);
    }

    public function destroy(Request $request, ProgressEntry $entry): RedirectResponse
    {
        abort_unless($entry->user_id === $request->user()->id, 404);
        $entry->delete();

        return back()->with('success', 'Registro eliminado.');
    }

    public function storePhoto(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'photo' => ['required', 'image', 'max:10240'],
            'pose' => ['required', Rule::in(array_keys(ProgressPhoto::POSES))],
        ], ['photo.required' => 'Elige o toma una foto.', 'photo.image' => 'El archivo debe ser una imagen.', 'photo.max' => 'La foto no puede pesar más de 10 MB.']);

        $request->user()->progressPhotos()->create([
            'path' => $data['photo']->store('progress/'.$request->user()->id, 'local'),
            'pose' => $data['pose'],
            'taken_on' => today(),
        ]);

        return back()->with('success', 'Foto guardada. Solo tú y tu entrenador pueden verla.');
    }

    public function destroyPhoto(Request $request, ProgressPhoto $photo): RedirectResponse
    {
        abort_unless($photo->user_id === $request->user()->id, 404);
        Storage::disk('local')->delete($photo->path);
        $photo->delete();

        return back()->with('success', 'Foto eliminada.');
    }
}
