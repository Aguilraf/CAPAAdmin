<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\FirefighterSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class FirefighterSettingController extends Controller
{
    public function index(Request $request)
    {
        $settings = FirefighterSetting::pluck('value', 'key');

        if ($request->wantsJson()) {
            return response()->json($settings);
        }

        return Inertia::render('Firefighters/Settings', [
            'initialSettings' => $settings,
        ]);
    }

    public function update(Request $request)
    {
        try {
            $data = $request->all();

            foreach ($data as $key => $value) {
                if ($request->hasFile($key)) {
                    $file = $request->file($key);
                    if (!$file->isValid())
                        continue;

                    // Delete old logo if exists
                    $oldSetting = FirefighterSetting::where('key', $key)->first();
                    if ($oldSetting && $oldSetting->value) {
                        try {
                            Storage::disk('public')->delete($oldSetting->value);
                        } catch (\Exception $e) {
                            \Log::warning("No se pudo eliminar el logo anterior: " . $e->getMessage());
                        }
                    }

                    $path = $file->store('logos', 'public');
                    FirefighterSetting::updateOrCreate(['key' => $key], ['value' => $path]);
                } else {
                    // Solo actualizar si el valor no es nulo
                    if ($value !== null) {
                        FirefighterSetting::updateOrCreate(['key' => $key], ['value' => $value]);
                    }
                }
            }

            return redirect()->back()->with('success', 'Configuración actualizada exitosamente');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al actualizar configuración: ' . $e->getMessage());
        }
    }
}
