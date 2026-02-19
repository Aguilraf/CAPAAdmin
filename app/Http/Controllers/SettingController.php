<?php



// Just ensuring the model is available if I missed the file (but I made it).
// Actually this file is the Controller, mistakenly put namespace App\Models in thought.
// Correct namespace is App\Http\Controllers.

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class SettingController extends Controller
{
    public function index()
    {
        // Fetch only relevant settings to send to the view
        $keys = [
            'logo_qroo',
            'logo_unidos',
            'logo_capa_header',
            'logo_capa',
            'footer_organismo',
            'footer_direccion',
            'footer_telefono',
            'footer_email',
            'footer_imagen',
            'footer_margin_bottom'
        ];
        $settings = Setting::whereIn('key', $keys)->pluck('value', 'key');

        // Fetch Firefighter settings
        $firefighterSettings = \App\Models\FirefighterSetting::pluck('value', 'key');

        // Automate Signatures Detection for UI Info
        $subgerente = \App\Models\Empleado::where('activo', true)->where('puesto', 'like', '%Subgerente%')->first();
        $gerente = \App\Models\Empleado::where('activo', true)->where('es_gerente', true)->first();

        $detectedSigners = [
            'signer1' => [
                'name' => $subgerente ? $subgerente->nombre . ' ' . $subgerente->primer_apellido . ' ' . $subgerente->segundo_apellido : 'NO DETECTADO (Revise Cat. Empleados)',
                'position' => $subgerente ? $subgerente->puesto : 'Debe tener puesto "Subgerente..."'
            ],
            'signer2' => [
                'name' => $gerente ? $gerente->nombre . ' ' . $gerente->primer_apellido . ' ' . $gerente->segundo_apellido : 'NO ASIGNADO (Revise Cat. Empleados)',
                'position' => $gerente ? $gerente->puesto : 'Debe marcarse como "Es Gerente"'
            ]
        ];

        return Inertia::render('Settings/Index', [
            'initialSettings' => $settings,
            'firefighterSettings' => $firefighterSettings,
            'detectedSigners' => $detectedSigners,
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'logo_qroo' => 'nullable|image|max:2048', // 2MB Max
            'logo_unidos' => 'nullable|image|max:2048',
            'logo_capa_header' => 'nullable|image|max:2048',
            'logo_capa' => 'nullable|image|max:2048',
            'footer_organismo' => 'nullable|string|max:255',
            'footer_direccion' => 'nullable|string|max:255',
            'footer_telefono' => 'nullable|string|max:50',
            'footer_email' => 'nullable|string|email|max:255',
            'footer_imagen' => 'nullable|image|max:2048',
            'footer_margin_bottom' => 'nullable|integer|min:0',
        ]);

        // Handle File Uploads
        $fileKeys = ['logo_qroo', 'logo_unidos', 'logo_capa_header', 'logo_capa', 'footer_imagen'];

        foreach ($fileKeys as $key) {
            if ($request->hasFile($key)) {
                // Delete old file if exists
                $oldSetting = Setting::where('key', $key)->first();
                if ($oldSetting && $oldSetting->value) {
                    // Assuming value is relative path like 'logos/xyz.png'
                    if (Storage::disk('public')->exists($oldSetting->value)) {
                        Storage::disk('public')->delete($oldSetting->value);
                    }
                }

                // Store new file
                $path = $request->file($key)->store('logos', 'public');

                // Update DB
                Setting::updateOrCreate(
                    ['key' => $key],
                    ['value' => $path, 'group' => 'branding']
                );
            }
        }

        // Handle Text Inputs
        $textKeys = ['footer_organismo', 'footer_direccion', 'footer_telefono', 'footer_email', 'footer_margin_bottom'];
        foreach ($textKeys as $key) {
            if ($request->has($key)) {
                Setting::updateOrCreate(
                    ['key' => $key],
                    ['value' => $request->input($key), 'group' => 'branding']
                );
            }
        }

        return redirect()->back()->with('success', 'Configuración actualizada correctamente.');
    }
}
