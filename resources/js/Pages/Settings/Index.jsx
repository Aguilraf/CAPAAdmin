import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm, usePage } from '@inertiajs/react';
import PrimaryButton from '@/Components/PrimaryButton';
import InputLabel from '@/Components/InputLabel';
import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { Save, Upload, CheckCircle, AlertCircle } from 'lucide-react';

export default function Index({ initialSettings, firefighterSettings, detectedSigners }) {
    const user = usePage().props.auth.user;
    const isAdministrator = user.roles && user.roles.some(r => r.name === 'Administrador');
    const canConfigureFirefighters = isAdministrator || (user.permissions && user.permissions.includes('configurar bomberos'));

    const [activeTab, setActiveTab] = useState(isAdministrator ? 'system' : 'firefighters');

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Configuración del Sistema
                </h2>
            }
        >
            <Head title="Configuración" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">

                    {/* Tabs Navigation */}
                    <div className="border-b border-gray-200 mb-6 flex space-x-8">
                        {isAdministrator && (
                            <button
                                onClick={() => setActiveTab('system')}
                                className={`py-4 px-1 border-b-2 font-medium text-sm ${activeTab === 'system'
                                    ? 'border-indigo-500 text-indigo-600'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                                    }`}
                            >
                                Sistema General
                            </button>
                        )}

                        {canConfigureFirefighters && (
                            <button
                                onClick={() => setActiveTab('firefighters')}
                                className={`py-4 px-1 border-b-2 font-medium text-sm ${activeTab === 'firefighters'
                                    ? 'border-indigo-500 text-indigo-600'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                                    }`}
                            >
                                Reportes Bomberos
                            </button>
                        )}
                    </div>


                    <div className="bg-white p-6 shadow-sm sm:rounded-lg">

                        {activeTab === 'system' && isAdministrator && (
                            <SystemSettingsForm initialSettings={initialSettings} />
                        )}

                        {activeTab === 'firefighters' && canConfigureFirefighters && (
                            <FirefighterSettingsForm
                                initialSettings={firefighterSettings}
                                systemSettings={initialSettings}
                                detectedSigners={detectedSigners}
                            />
                        )}

                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

function SystemSettingsForm({ initialSettings }) {
    const { data, setData, post, processing, errors } = useForm({
        logo_qroo: null,
        logo_unidos: null,
        logo_capa: null,
        footer_organismo: initialSettings.footer_organismo || '',
        footer_direccion: initialSettings.footer_direccion || '',
        footer_telefono: initialSettings.footer_telefono || '',
        footer_email: initialSettings.footer_email || '',
        footer_imagen: null,
        footer_margin_bottom: initialSettings.footer_margin_bottom || 60,
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('settings.update'), {
            preserveScroll: true,
        });
    };

    const getLogoUrl = (path) => {
        return path ? `/media/${path}` : null;
    };

    return (
        <form onSubmit={submit} className="space-y-8 animate-fade-in">
            <div>
                <h3 className="text-lg font-medium text-gray-900 mb-6 border-b pb-2">Logotipos Oficiales del Sistema</h3>

                {/* Logo QROO */}
                <div className="grid grid-cols-1 md:grid-cols-2 gap-8 items-start mb-8">
                    <div>
                        <InputLabel value="Logo Gobierno Quintana Roo (Cabecera Izquierda)" className="mb-2" />
                        <input
                            type="file"
                            onChange={e => setData('logo_qroo', e.target.files[0])}
                            className="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                            accept="image/*"
                        />
                        {errors.logo_qroo && <div className="text-red-600 mt-1 text-xs">{errors.logo_qroo}</div>}
                    </div>
                    <div className="border p-4 rounded flex justify-center bg-gray-50 h-32 items-center">
                        {initialSettings.logo_qroo ? (
                            <img src={getLogoUrl(initialSettings.logo_qroo)} className="max-h-full object-contain" alt="Logo QROO" />
                        ) : (
                            <span className="text-gray-400 text-sm">Sin imagen</span>
                        )}
                    </div>
                </div>

                {/* Logo Unidos */}
                <div className="grid grid-cols-1 md:grid-cols-2 gap-8 items-start mb-8">
                    <div>
                        <InputLabel value="Logo Unidos para Transformar (Cabecera Derecha)" className="mb-2" />
                        <input
                            type="file"
                            onChange={e => setData('logo_unidos', e.target.files[0])}
                            className="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                            accept="image/*"
                        />
                        {errors.logo_unidos && <div className="text-red-600 mt-1 text-xs">{errors.logo_unidos}</div>}
                    </div>
                    <div className="border p-4 rounded flex justify-center bg-gray-50 h-32 items-center">
                        {initialSettings.logo_unidos ? (
                            <img src={getLogoUrl(initialSettings.logo_unidos)} className="max-h-full object-contain" alt="Logo Unidos" />
                        ) : (
                            <span className="text-gray-400 text-sm">Sin imagen</span>
                        )}
                    </div>
                </div>

                {/* Logo CAPA */}
                <div className="grid grid-cols-1 md:grid-cols-2 gap-8 items-start">
                    <div>
                        <InputLabel value="Logo CAPA (Pie de Página)" className="mb-2" />
                        <input
                            type="file"
                            onChange={e => setData('logo_capa', e.target.files[0])}
                            className="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                            accept="image/*"
                        />
                        {errors.logo_capa && <div className="text-red-600 mt-1 text-xs">{errors.logo_capa}</div>}
                    </div>
                    <div className="border p-4 rounded flex justify-center bg-gray-50 h-32 items-center">
                        {initialSettings.logo_capa ? (
                            <img src={getLogoUrl(initialSettings.logo_capa)} className="max-h-full object-contain" alt="Logo CAPA" />
                        ) : (
                            <span className="text-gray-400 text-sm">Sin imagen</span>
                        )}
                    </div>
                </div>
            </div>

            <div className="mt-10 border-t pt-6">
                <h3 className="text-lg font-medium text-gray-900 mb-6 border-b pb-2">Configuración del Pie de Página (Reportes Generales)</h3>

                {/* Campos de Texto del Pie de Página */}
                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <InputLabel value="Organismo Operador" className="mb-2" />
                        <input
                            type="text"
                            value={data.footer_organismo || ''}
                            onChange={e => setData('footer_organismo', e.target.value)}
                            className="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            placeholder="EJ: JOSE MARIA MORELOS"
                        />
                        {errors.footer_organismo && <div className="text-red-600 mt-1 text-xs">{errors.footer_organismo}</div>}
                    </div>
                    <div>
                        <InputLabel value="Dirección" className="mb-2" />
                        <input
                            type="text"
                            value={data.footer_direccion || ''}
                            onChange={e => setData('footer_direccion', e.target.value)}
                            className="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            placeholder="Calle Noh Bec entre Cecilio Chi..."
                        />
                        {errors.footer_direccion && <div className="text-red-600 mt-1 text-xs">{errors.footer_direccion}</div>}
                    </div>
                    <div>
                        <InputLabel value="Teléfono" className="mb-2" />
                        <input
                            type="text"
                            value={data.footer_telefono || ''}
                            onChange={e => setData('footer_telefono', e.target.value)}
                            className="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            placeholder="(997) 97 80179"
                        />
                        {errors.footer_telefono && <div className="text-red-600 mt-1 text-xs">{errors.footer_telefono}</div>}
                    </div>
                    <div>
                        <InputLabel value="Correo Electrónico" className="mb-2" />
                        <input
                            type="email"
                            value={data.footer_email || ''}
                            onChange={e => setData('footer_email', e.target.value)}
                            className="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            placeholder="capamorelos@capa.gob.mx"
                        />
                        {errors.footer_email && <div className="text-red-600 mt-1 text-xs">{errors.footer_email}</div>}
                    </div>
                    <div>
                        <InputLabel value="Margen Inferior (px) - Altura de Pie de Página" className="mb-2" />
                        <input
                            type="number"
                            value={data.footer_margin_bottom || ''}
                            onChange={e => setData('footer_margin_bottom', e.target.value)}
                            className="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            placeholder="Ej: 60"
                            min="0"
                        />
                        <p className="text-xs text-gray-500 mt-1">Aumente este valor para subir la imagen del pie de página.</p>
                        {errors.footer_margin_bottom && <div className="text-red-600 mt-1 text-xs">{errors.footer_margin_bottom}</div>}
                    </div>
                </div>

                {/* Imagen del Pie de Página */}
                <div className="grid grid-cols-1 md:grid-cols-2 gap-8 items-start mt-4">
                    <div>
                        <InputLabel value="Imagen Decorativa (Pie de Página)" className="mb-2" />
                        <input
                            type="file"
                            onChange={e => setData('footer_imagen', e.target.files[0])}
                            className="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                            accept="image/*"
                        />
                        <p className="mt-1 text-xs text-gray-500">Se recomienda una imagen con fondo transparente o diseño de ola.</p>
                        {errors.footer_imagen && <div className="text-red-600 mt-1 text-xs">{errors.footer_imagen}</div>}
                    </div>
                    <div className="border p-4 rounded flex justify-center bg-gray-50 h-32 items-center relative overflow-hidden">
                        {initialSettings.footer_imagen ? (
                            <img src={getLogoUrl(initialSettings.footer_imagen)} className="max-h-full object-contain" alt="Footer Img" />
                        ) : (
                            <span className="text-gray-400 text-sm">Sin imagen</span>
                        )}
                    </div>
                </div>

                <div className="flex justify-end pt-4">
                    <PrimaryButton disabled={processing}>
                        Guardar Cambios del Sistema
                    </PrimaryButton>
                </div>
            </div>
        </form>
    );
}

function FirefighterSettingsForm({ initialSettings, systemSettings, detectedSigners }) {
    const [settings, setSettings] = useState({
        report_title: initialSettings.report_title || '',
        report_subtitle: initialSettings.report_subtitle || '',
        report_fondo_amount: initialSettings.report_fondo_amount || '',
        report_signer1_name: initialSettings.report_signer1_name || '',
        report_signer1_position: initialSettings.report_signer1_position || '',
        report_signer2_name: initialSettings.report_signer2_name || '',
        report_signer2_position: initialSettings.report_signer2_position || '',
        layout_logo_state_w: initialSettings.layout_logo_state_w || '150',
        layout_logo_campaign_w: initialSettings.layout_logo_campaign_w || '130',
        layout_header_mt: initialSettings.layout_header_mt || '0',
        layout_footer_h: initialSettings.layout_footer_h || '80',
        layout_footer_bottom: initialSettings.layout_footer_bottom || '-20',
        layout_table_mt: initialSettings.layout_table_mt || '10',
        layout_info_mb: initialSettings.layout_info_mb || '10',
        layout_font_size: initialSettings.layout_font_size || '8'
    });

    const [logos, setLogos] = useState({
        report_logo_state: null,
        report_logo_campaign: null,
        report_logo_footer: null
    });

    // Initial previews from SYSTEM SETTINGS
    const [previews, setPreviews] = useState({
        report_logo_state: systemSettings.logo_qroo ? `/media/${systemSettings.logo_qroo}` : null,
        report_logo_campaign: systemSettings.logo_unidos ? `/media/${systemSettings.logo_unidos}` : null,
        report_logo_footer: systemSettings.footer_imagen ? `/media/${systemSettings.footer_imagen}` : null
    });

    const [status, setStatus] = useState(null);
    const [loading, setLoading] = useState(false);

    const handleTextChange = (e) => {
        const { name, value } = e.target;
        setSettings(prev => ({ ...prev, [name]: value }));
    };

    const handleFileChange = (e) => {
        // Only if we were allowing other files? Currently we are removing all logo uploads from this form.
        // If there are no other file inputs, this function might be unused.
        // Keeping it just in case we add something later or for safety, but effectively no-op for logos now.
        const { name, files } = e.target;
        if (files && files[0]) {
            const file = files[0];
            setLogos(prev => ({ ...prev, [name]: file }));
            setPreviews(prev => ({ ...prev, [name]: URL.createObjectURL(file) }));
        }
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setLoading(true);
        setStatus(null);

        const formData = new FormData();
        Object.keys(settings).forEach(key => formData.append(key, settings[key]));
        if (logos.report_logo_state) formData.append('report_logo_state', logos.report_logo_state);
        if (logos.report_logo_campaign) formData.append('report_logo_campaign', logos.report_logo_campaign);
        if (logos.report_logo_footer) formData.append('report_logo_footer', logos.report_logo_footer);

        try {
            const response = await axios.post('/firefighter-settings-update', formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            });
            setStatus({ type: 'success', message: response.data.message || 'Configuración guardada exitosamente' });
        } catch (error) {
            console.error('Error saving settings:', error);
            const errorMsg = error.response?.data?.message || 'Error al guardar la configuración';
            setStatus({ type: 'error', message: errorMsg });
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="animate-fade-in">
            <div className="flex justify-between items-center mb-6">
                <h2 className="text-lg font-medium text-gray-900">Configuración de Reportes de Bomberos</h2>
            </div>

            {status && (
                <div className={`p-4 mb-6 rounded-lg flex items-center gap-3 ${status.type === 'success' ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200'
                    }`}>
                    {status.type === 'success' ? <CheckCircle className="w-5 h-5" /> : <AlertCircle className="w-5 h-5" />}
                    <span className="font-medium">{status.message}</span>
                </div>
            )}

            <form onSubmit={handleSubmit} className="space-y-8">
                {/* Text Settings */}
                <div className="bg-white shadow rounded-xl p-6 border border-gray-100">
                    <h3 className="text-lg font-bold text-gray-800 mb-4 border-b pb-2">Textos del Encabezado</h3>
                    <div className="space-y-4">
                        <div>
                            <label className="block text-sm font-semibold text-gray-700 mb-1">Título Principal (Fila 1)</label>
                            <input
                                type="text" name="report_title" value={settings.report_title} onChange={handleTextChange}
                                className="w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 border p-2.5 text-sm"
                                placeholder="Ej: COMISION DE AGUA POTABLE Y ALCANTARILLADO"
                            />
                        </div>
                        <div>
                            <label className="block text-sm font-semibold text-gray-700 mb-1">Subtítulo (Fila 2)</label>
                            <input
                                type="text" name="report_subtitle" value={settings.report_subtitle} onChange={handleTextChange}
                                className="w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 border p-2.5 text-sm"
                                placeholder="Ej: ORGANISMO OPERADOR : JOSE MARIA MORELOS"
                            />
                        </div>
                        <div>
                            <label className="block text-sm font-semibold text-gray-700 mb-1">Cantidad de Fondo de Bomberos</label>
                            <input
                                type="number" name="report_fondo_amount" value={settings.report_fondo_amount} onChange={handleTextChange}
                                className="w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 border p-2.5 text-sm"
                                placeholder="Ej: 10000"
                            />
                        </div>
                    </div>
                </div>

                {/* Signatures Settings */}
                <div className="bg-white shadow rounded-xl p-6 border border-gray-100">
                    <h3 className="text-lg font-bold text-gray-800 mb-4 border-b pb-2">Firmas del Reporte (Automático)</h3>
                    <div className="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded mb-6 text-sm flex items-center gap-2">
                        <AlertCircle className="w-5 h-5" />
                        <span>Las firmas se detectan automáticamente según el catálogo de empleados (Activos).</span>
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div className="space-y-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                            <h4 className="font-bold text-gray-700 text-sm border-b pb-2">Firma 1 (Izquierda) - Subgerente</h4>
                            <div>
                                <label className="block text-xs font-semibold text-gray-500 mb-1">Nombre Detectado</label>
                                <div className="font-medium text-gray-900 text-sm">{detectedSigners?.signer1?.name}</div>
                            </div>
                            <div>
                                <label className="block text-xs font-semibold text-gray-500 mb-1">Puesto Detectado</label>
                                <div className="font-medium text-gray-900 text-sm">{detectedSigners?.signer1?.position}</div>
                            </div>
                        </div>
                        <div className="space-y-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                            <h4 className="font-bold text-gray-700 text-sm border-b pb-2">Firma 2 (Derecha) - Gerente</h4>
                            <div>
                                <label className="block text-xs font-semibold text-gray-500 mb-1">Nombre Detectado</label>
                                <div className="font-medium text-gray-900 text-sm">{detectedSigners?.signer2?.name}</div>
                            </div>
                            <div>
                                <label className="block text-xs font-semibold text-gray-500 mb-1">Puesto Detectado</label>
                                <div className="font-medium text-gray-900 text-sm">{detectedSigners?.signer2?.position}</div>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Logo Settings */}
                <div className="bg-white shadow rounded-xl p-6 border border-gray-100">
                    <h3 className="text-lg font-bold text-gray-800 mb-4 border-b pb-2">Logotipos del Reporte</h3>
                    <div className="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded mb-6 text-sm flex items-center gap-2">
                        <AlertCircle className="w-5 h-5" />
                        <span>Estos logotipos se toman de la <strong>Configuración del Sistema</strong>. Para cambiarlos, contacte a un Administrador.</span>
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
                        {/* State Logo */}
                        <div className="space-y-3">
                            <label className="block text-sm font-semibold text-gray-700">Logo del Estado (Izquierda)</label>
                            <div className="border border-gray-200 rounded-xl p-4 flex flex-col items-center justify-center bg-gray-50 min-h-[160px]">
                                {previews.report_logo_state ? (
                                    <img src={previews.report_logo_state} className="max-h-24 object-contain mb-3" alt="State Logo" />
                                ) : (
                                    <div className="text-gray-400 text-xs text-center mb-3 italic">No hay imagen configurada en el sistema</div>
                                )}
                                <div className="text-xs text-gray-500 italic">Heredado del Sistema</div>
                            </div>
                        </div>

                        {/* Campaign Logo */}
                        <div className="space-y-3">
                            <label className="block text-sm font-semibold text-gray-700">Logo de Campaña (Derecha)</label>
                            <div className="border border-gray-200 rounded-xl p-4 flex flex-col items-center justify-center bg-gray-50 min-h-[160px]">
                                {previews.report_logo_campaign ? (
                                    <img src={previews.report_logo_campaign} className="max-h-24 object-contain mb-3" alt="Campaign Logo" />
                                ) : (
                                    <div className="text-gray-400 text-xs text-center mb-3 italic">No hay imagen configurada en el sistema</div>
                                )}
                                <div className="text-xs text-gray-500 italic">Heredado del Sistema</div>
                            </div>
                        </div>

                        {/* Footer Image */}
                        <div className="space-y-3 md:col-span-2">
                            <label className="block text-sm font-semibold text-gray-700">Imagen de Pie de Página (Fondo Completo)</label>
                            <div className="border border-gray-200 rounded-xl p-4 flex flex-col items-center justify-center bg-gray-50 min-h-[160px]">
                                {previews.report_logo_footer ? (
                                    <img src={previews.report_logo_footer} className="max-h-32 w-full object-contain mb-3" alt="Footer Image" />
                                ) : (
                                    <div className="text-gray-400 text-xs text-center mb-3 italic">No hay imagen configurada en el sistema</div>
                                )}
                                <div className="text-xs text-gray-500 italic">Heredado del Sistema</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div className="flex justify-end">
                    <button
                        type="submit"
                        disabled={loading}
                        className={`flex items-center gap-2 px-8 py-3 bg-blue-700 text-white font-bold rounded-xl shadow-lg hover:bg-blue-800 transition-all ${loading ? 'opacity-50 cursor-wait' : ''}`}
                    >
                        <Save className="w-5 h-5" />
                        {loading ? 'Guardando...' : 'Guardar Configuración de Bomberos'}
                    </button>
                </div>

                {/* VISUAL DESIGNER */}
                <div className="bg-white shadow rounded-xl p-6 border border-gray-100">
                    <h3 className="text-lg font-bold text-gray-800 mb-4 border-b pb-2">Modificar Reporte (Diseño Visual)</h3>
                    <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        {/* Controls */}
                        <div className="space-y-6">
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-xs font-bold text-gray-600 mb-2 uppercase">Logo Estado Ancho: {settings.layout_logo_state_w}px</label>
                                    <input type="range" min="50" max="300" name="layout_logo_state_w" value={settings.layout_logo_state_w} onChange={handleTextChange} className="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-blue-600" />
                                </div>
                                <div>
                                    <label className="block text-xs font-bold text-gray-600 mb-2 uppercase">Logo Campaña Ancho: {settings.layout_logo_campaign_w}px</label>
                                    <input type="range" min="50" max="300" name="layout_logo_campaign_w" value={settings.layout_logo_campaign_w} onChange={handleTextChange} className="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-blue-600" />
                                </div>
                                <div>
                                    <label className="block text-xs font-bold text-gray-600 mb-2 uppercase">Margen Sup. Hoja: {settings.layout_header_mt}px</label>
                                    <input type="range" min="-20" max="100" name="layout_header_mt" value={settings.layout_header_mt} onChange={handleTextChange} className="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-blue-600" />
                                </div>
                                <div>
                                    <label className="block text-xs font-bold text-gray-600 mb-2 uppercase">Margen Sup. Tabla: {settings.layout_table_mt}px</label>
                                    <input type="range" min="0" max="200" name="layout_table_mt" value={settings.layout_table_mt} onChange={handleTextChange} className="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-blue-600" />
                                </div>
                                <div>
                                    <label className="block text-xs font-bold text-gray-600 mb-2 uppercase">Margen Inf. Info (JMM): {settings.layout_info_mb}px</label>
                                    <input type="range" min="-10" max="50" name="layout_info_mb" value={settings.layout_info_mb} onChange={handleTextChange} className="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-blue-600" />
                                </div>
                                <div>
                                    <label className="block text-xs font-bold text-gray-600 mb-2 uppercase">Tamaño Letra Tabla: {settings.layout_font_size}pt</label>
                                    <input type="range" min="6" max="14" name="layout_font_size" value={settings.layout_font_size} onChange={handleTextChange} className="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-blue-600" />
                                </div>
                                <div className="md:col-span-2 p-4 bg-gray-50 rounded-lg border border-gray-100 mt-2">
                                    <h4 className="font-bold text-sm text-blue-800 mb-4 flex items-center gap-2">
                                        Ajustes de Pie de Página (Fondo)
                                    </h4>
                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label className="block text-xs font-bold text-gray-600 mb-2 uppercase">Altura Imagen: {settings.layout_footer_h}px</label>
                                            <input type="range" min="20" max="300" name="layout_footer_h" value={settings.layout_footer_h} onChange={handleTextChange} className="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-blue-600" />
                                        </div>
                                        <div>
                                            <label className="block text-xs font-bold text-gray-600 mb-2 uppercase">Posición Vertical: {settings.layout_footer_bottom}px</label>
                                            <p className="text-[9px] text-gray-400 mb-1">Más negativo = más abajo</p>
                                            <input type="range" min="-100" max="200" name="layout_footer_bottom" value={settings.layout_footer_bottom} onChange={handleTextChange} className="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-blue-600" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {/* Visual Preview */}
                        <div className="bg-gray-100 p-6 rounded-xl border border-gray-200 overflow-hidden">
                            <h4 className="text-xs font-black text-gray-400 uppercase mb-4 tracking-widest text-center">Vista Previa del Diseño</h4>
                            <div className="bg-white aspect-[1/1.4] w-full shadow-lg origin-top mx-auto overflow-hidden relative" style={{ borderRadius: '2px', border: '1px solid #ddd' }}>
                                {/* Header Preview */}
                                <div style={{ marginTop: `${settings.layout_header_mt / 4}px`, padding: '10px' }}>
                                    <div className="text-center">
                                        <div className="h-2 w-3/4 bg-gray-800 mx-auto mb-1"></div>
                                        <div className="h-1.5 w-1/2 bg-gray-600 mx-auto"></div>
                                    </div>
                                    <div className="flex justify-between mt-4">
                                        <div style={{ width: `${settings.layout_logo_state_w / 4}px` }} className="h-8 bg-blue-100 flex items-center justify-center text-[6px] font-bold text-blue-400 border border-blue-200 border-dashed">LOGO 1</div>
                                        <div style={{ width: `${settings.layout_logo_campaign_w / 4}px` }} className="h-6 bg-red-100 flex items-center justify-center text-[6px] font-bold text-red-400 border border-red-200 border-dashed self-end">LOGO 2</div>
                                    </div>
                                    <div className="text-center mt-2 border-t pt-1" style={{ marginBottom: `${settings.layout_info_mb / 4}px` }}>
                                        <div className="h-1 w-1/2 bg-gray-400 mx-auto"></div>
                                    </div>
                                </div>
                                {/* Content Area Simulation */}
                                <div style={{ marginTop: `${settings.layout_table_mt / 4}px`, padding: '0 16px' }}>
                                    <table className="w-full opacity-30">
                                        <thead><tr className="border-[0.5px] border-black"><th className="h-2 bg-gray-200"></th><th className="h-2 bg-gray-200"></th><th className="h-2 bg-gray-200"></th></tr></thead>
                                        <tbody>{[1, 2, 3, 4, 5].map(i => <tr key={i}><td className="h-3 border-[0.5px] border-gray-300"></td><td className="h-3 border-[0.5px] border-gray-300"></td><td className="h-3 border-[0.5px] border-gray-300"></td></tr>)}</tbody>
                                    </table>
                                </div>
                                {/* Footer Image Preview */}
                                <div
                                    className="absolute left-0 right-0 bg-blue-400 opacity-20 border-t border-blue-500 flex items-center justify-center font-black text-blue-700 text-[10px]"
                                    style={{
                                        height: `${settings.layout_footer_h / 4}px`,
                                        bottom: `${settings.layout_footer_bottom / 4}px`
                                    }}
                                >
                                    PIE DE PÁGINA (IMG)
                                </div>
                            </div>
                            <p className="text-[10px] text-gray-500 mt-4 italic text-center">La vista previa es aproximada. Los cambios reales se verán en el PDF.</p>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    );
}
