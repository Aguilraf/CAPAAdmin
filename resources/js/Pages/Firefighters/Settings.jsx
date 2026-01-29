import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { Save, Upload, CheckCircle, AlertCircle } from 'lucide-react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';

export default function Settings(props) {
    const [settings, setSettings] = useState({
        report_title: '',
        report_subtitle: '',
        report_fondo_amount: '',
        report_signer1_name: '',
        report_signer1_position: '',
        report_signer2_name: '',
        report_signer2_position: '',
        // Layout Designer Settings
        layout_logo_state_w: '150',
        layout_logo_campaign_w: '130',
        layout_header_mt: '0',
        layout_footer_h: '80',
        layout_footer_bottom: '-20',
        layout_table_mt: '10',
        layout_info_mb: '10',
        layout_font_size: '8'
    });
    const [logos, setLogos] = useState({
        report_logo_state: null,
        report_logo_campaign: null,
        report_logo_footer: null
    });
    const [previews, setPreviews] = useState({
        report_logo_state: null,
        report_logo_campaign: null,
        report_logo_footer: null
    });
    const [status, setStatus] = useState(null);
    const [loading, setLoading] = useState(false);

    useEffect(() => {
        fetchSettings();
    }, []);

    const fetchSettings = async () => {
        try {
            const response = await axios.get('/api/firefighter-settings');
            const data = response.data;
            setSettings({
                report_title: data.report_title || '',
                report_subtitle: data.report_subtitle || '',
                report_fondo_amount: data.report_fondo_amount || '',
                report_signer1_name: data.report_signer1_name || '',
                report_signer1_position: data.report_signer1_position || '',
                report_signer2_name: data.report_signer2_name || '',
                report_signer2_position: data.report_signer2_position || '',
                layout_logo_state_w: data.layout_logo_state_w || '150',
                layout_logo_campaign_w: data.layout_logo_campaign_w || '130',
                layout_header_mt: data.layout_header_mt || '0',
                layout_footer_h: data.layout_footer_h || '80',
                layout_footer_bottom: data.layout_footer_bottom || '-20',
                layout_table_mt: data.layout_table_mt || '10',
                layout_info_mb: data.layout_info_mb || '10',
                layout_font_size: data.layout_font_size || '8'
            });

            if (data.report_logo_state) {
                setPreviews(prev => ({ ...prev, report_logo_state: `/storage/${data.report_logo_state}` }));
            }
            if (data.report_logo_campaign) {
                setPreviews(prev => ({ ...prev, report_logo_campaign: `/storage/${data.report_logo_campaign}` }));
            }
            if (data.report_logo_footer) {
                setPreviews(prev => ({ ...prev, report_logo_footer: `/storage/${data.report_logo_footer}` }));
            }
        } catch (error) {
            console.error('Error fetching settings:', error);
        }
    };

    const handleTextChange = (e) => {
        const { name, value } = e.target;
        setSettings(prev => ({ ...prev, [name]: value }));
    };

    const handleFileChange = (e) => {
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
            const response = await axios.post('/api/firefighter-settings', formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            });
            setStatus({ type: 'success', message: response.data.message || 'Configuración guardada exitosamente' });
            fetchSettings();
        } catch (error) {
            console.error('Error saving settings:', error);
            const errorMsg = error.response?.data?.message || 'Error al guardar la configuración';
            setStatus({ type: 'error', message: errorMsg });
        } finally {
            setLoading(false);
        }
    };

    return (
        <AuthenticatedLayout
            user={props.auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Configuración del Reporte</h2>}
        >
            <Head title="Configuración Reporte" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900">
                            <div className="max-w-4xl mx-auto">
                                <div className="flex justify-between items-center mb-6">
                                    <h2 className="text-2xl font-bold text-gray-800">Configuración del Reporte</h2>
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
                                        <h3 className="text-lg font-bold text-gray-800 mb-4 border-b pb-2">Firmas del Reporte</h3>
                                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <div className="space-y-4">
                                                <h4 className="font-bold text-gray-700 text-sm">Firma 1 (Izquierda)</h4>
                                                <div>
                                                    <label className="block text-xs font-semibold text-gray-600 mb-1">Nombre</label>
                                                    <input
                                                        type="text" name="report_signer1_name" value={settings.report_signer1_name} onChange={handleTextChange}
                                                        className="w-full rounded-lg border-gray-300 border p-2 text-sm"
                                                    />
                                                </div>
                                                <div>
                                                    <label className="block text-xs font-semibold text-gray-600 mb-1">Puesto / Cargo</label>
                                                    <input
                                                        type="text" name="report_signer1_position" value={settings.report_signer1_position} onChange={handleTextChange}
                                                        className="w-full rounded-lg border-gray-300 border p-2 text-sm"
                                                    />
                                                </div>
                                            </div>
                                            <div className="space-y-4">
                                                <h4 className="font-bold text-gray-700 text-sm">Firma 2 (Derecha)</h4>
                                                <div>
                                                    <label className="block text-xs font-semibold text-gray-600 mb-1">Nombre</label>
                                                    <input
                                                        type="text" name="report_signer2_name" value={settings.report_signer2_name} onChange={handleTextChange}
                                                        className="w-full rounded-lg border-gray-300 border p-2 text-sm"
                                                    />
                                                </div>
                                                <div>
                                                    <label className="block text-xs font-semibold text-gray-600 mb-1">Puesto / Cargo</label>
                                                    <input
                                                        type="text" name="report_signer2_position" value={settings.report_signer2_position} onChange={handleTextChange}
                                                        className="w-full rounded-lg border-gray-300 border p-2 text-sm"
                                                    />
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {/* Logo Settings */}
                                    <div className="bg-white shadow rounded-xl p-6 border border-gray-100">
                                        <h3 className="text-lg font-bold text-gray-800 mb-4 border-b pb-2">Logotipos Oficiales</h3>
                                        <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
                                            {/* State Logo */}
                                            <div className="space-y-3">
                                                <label className="block text-sm font-semibold text-gray-700">Logo del Estado (Izquierda)</label>
                                                <div className="border-2 border-dashed border-gray-200 rounded-xl p-4 flex flex-col items-center justify-center bg-gray-50 min-h-[160px]">
                                                    {previews.report_logo_state ? (
                                                        <img src={previews.report_logo_state} className="max-h-24 object-contain mb-3" alt="State Logo" />
                                                    ) : (
                                                        <div className="text-gray-400 text-xs text-center mb-3 italic">No hay imagen seleccionada</div>
                                                    )}
                                                    <label className="cursor-pointer bg-white px-4 py-2 border rounded-lg text-xs font-bold text-blue-600 hover:bg-blue-50 transition-colors flex items-center gap-2 shadow-sm">
                                                        <Upload className="w-4 h-4" /> Seleccionar Imagen
                                                        <input type="file" name="report_logo_state" onChange={handleFileChange} className="hidden" accept="image/*" />
                                                    </label>
                                                </div>
                                            </div>

                                            {/* Campaign Logo */}
                                            <div className="space-y-3">
                                                <label className="block text-sm font-semibold text-gray-700">Logo de Campaña (Derecha)</label>
                                                <div className="border-2 border-dashed border-gray-200 rounded-xl p-4 flex flex-col items-center justify-center bg-gray-50 min-h-[160px]">
                                                    {previews.report_logo_campaign ? (
                                                        <img src={previews.report_logo_campaign} className="max-h-24 object-contain mb-3" alt="Campaign Logo" />
                                                    ) : (
                                                        <div className="text-gray-400 text-xs text-center mb-3 italic">No hay imagen seleccionada</div>
                                                    )}
                                                    <label className="cursor-pointer bg-white px-4 py-2 border rounded-lg text-xs font-bold text-blue-600 hover:bg-blue-50 transition-colors flex items-center gap-2 shadow-sm">
                                                        <Upload className="w-4 h-4" /> Seleccionar Imagen
                                                        <input type="file" name="report_logo_campaign" onChange={handleFileChange} className="hidden" accept="image/*" />
                                                    </label>
                                                </div>
                                            </div>

                                            {/* Footer Image */}
                                            <div className="space-y-3 md:col-span-2">
                                                <label className="block text-sm font-semibold text-gray-700">Imagen de Pie de Página (Fondo Completo)</label>
                                                <div className="border-2 border-dashed border-gray-200 rounded-xl p-4 flex flex-col items-center justify-center bg-gray-50 min-h-[160px]">
                                                    {previews.report_logo_footer ? (
                                                        <img src={previews.report_logo_footer} className="max-h-32 w-full object-contain mb-3" alt="Footer Image" />
                                                    ) : (
                                                        <div className="text-gray-400 text-xs text-center mb-3 italic">No hay imagen de pie de página seleccionada</div>
                                                    )}
                                                    <label className="cursor-pointer bg-white px-4 py-2 border rounded-lg text-xs font-bold text-blue-600 hover:bg-blue-50 transition-colors flex items-center gap-2 shadow-sm">
                                                        <Upload className="w-4 h-4" /> Seleccionar Imagen de Pie
                                                        <input type="file" name="report_logo_footer" onChange={handleFileChange} className="hidden" accept="image/*" />
                                                    </label>
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
                                            {loading ? 'Guardando...' : 'Guardar Todos los Cambios'}
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
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
