import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';
import { useState } from 'react';
import {
    UploadCloud,
    FileDown,
    CheckCircle,
    Database,
    RefreshCw
} from 'lucide-react';

export default function Index({ availableSheets }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        file: null,
        sheets: Object.keys(availableSheets),
    });

    const [fileName, setFileName] = useState(null);
    const [dragActive, setDragActive] = useState(false);

    const handleFileChange = (e) => {
        const file = e.target.files[0];
        if (file) {
            setData('file', file);
            setFileName(file.name);
        }
    };

    const handleDrag = (e) => {
        e.preventDefault();
        e.stopPropagation();
        if (e.type === "dragenter" || e.type === "dragover") {
            setDragActive(true);
        } else if (e.type === "dragleave") {
            setDragActive(false);
        }
    };

    const handleDrop = (e) => {
        e.preventDefault();
        e.stopPropagation();
        setDragActive(false);
        if (e.dataTransfer.files && e.dataTransfer.files[0]) {
            const file = e.dataTransfer.files[0];
            setData('file', file);
            setFileName(file.name);
        }
    };

    const toggleSheet = (key) => {
        const newSheets = data.sheets.includes(key)
            ? data.sheets.filter(s => s !== key)
            : [...data.sheets, key];
        setData('sheets', newSheets);
    };

    const submit = (e) => {
        e.preventDefault();
        post(route('import.store'), {
            forceFormData: true,
            onSuccess: () => {
                reset('file');
                setFileName(null);
            },
        });
    };

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center space-x-3">
                    <RefreshCw className="h-6 w-6 text-blue-600" />
                    <h2 className="text-2xl font-bold text-gray-800">
                        Centro de Gestión de Datos
                    </h2>
                </div>
            }
        >
            <Head title="Importar/Exportar Datos" />

            <div className="py-6">
                <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">

                    {/* Left Column: Export & Templates */}
                    <div className="lg:col-span-1 space-y-6">
                        <div className="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 overflow-hidden relative">
                            <div className="absolute top-0 right-0 -mt-4 -mr-4 h-24 w-24 bg-blue-50 rounded-full opacity-50 blur-2xl"></div>

                            <h3 className="text-lg font-bold text-gray-900 mb-2 flex items-center">
                                <FileDown className="h-5 w-5 mr-2 text-blue-600" />
                                Respaldo y Plantillas
                            </h3>
                            <p className="text-sm text-gray-500 mb-6">
                                Descarga todos los catálogos actuales en un solo archivo para respaldo o usa la plantilla para carga masiva.
                            </p>

                            <div className="space-y-3">
                                <a
                                    href={route('import.export')}
                                    className="w-full flex items-center justify-center px-4 py-3 bg-blue-600 text-white rounded-xl font-semibold shadow-lg shadow-blue-200 hover:bg-blue-700 transition-all active:scale-95 group"
                                >
                                    <UploadCloud className="h-5 w-5 mr-2 group-hover:animate-bounce" />
                                    Exportar Todo (.xlsx)
                                </a>

                                <a
                                    href={route('import.template')}
                                    className="w-full flex items-center justify-center px-4 py-3 bg-white border-2 border-blue-50 text-blue-700 rounded-xl font-semibold hover:bg-blue-50 transition-all active:scale-95"
                                >
                                    <Database className="h-5 w-5 mr-2" />
                                    Descargar Plantilla
                                </a>
                            </div>

                            <div className="mt-8 pt-6 border-t border-gray-100">
                                <h4 className="text-sm font-bold text-gray-700 mb-3 uppercase tracking-wider">Instrucciones:</h4>
                                <ul className="text-xs text-gray-500 space-y-2 list-disc pl-4">
                                    <li>Usa el archivo exportado como base para editar.</li>
                                    <li>No cambies los nombres de las pestañas (hojas).</li>
                                    <li>Las celdas vacías en campos obligatorios serán ignoradas.</li>
                                    <li>El sistema detecta duplicados por "CLAVE" o "CODIGO".</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    {/* Right Column: Import */}
                    <div className="lg:col-span-2">
                        <div className="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                            <form onSubmit={submit}>
                                <h3 className="text-xl font-bold text-gray-900 mb-6">Importación de Catálogos</h3>

                                {/* Dropzone */}
                                <div
                                    className={`relative border-2 border-dashed rounded-2xl transition-all p-10 flex flex-col items-center justify-center cursor-pointer
                                        ${dragActive ? 'border-blue-500 bg-blue-50 scale-[1.01]' : 'border-gray-200 bg-gray-50 hover:border-blue-300 hover:bg-blue-50/30'}
                                        ${fileName ? 'border-green-300 bg-green-50/30' : ''}`}
                                    onDragEnter={handleDrag}
                                    onDragLeave={handleDrag}
                                    onDragOver={handleDrag}
                                    onDrop={handleDrop}
                                    onClick={() => document.getElementById('file-upload').click()}
                                >
                                    <input
                                        id="file-upload"
                                        type="file"
                                        className="hidden"
                                        onChange={handleFileChange}
                                        accept=".xlsx,.xls,.csv"
                                    />

                                    {fileName ? (
                                        <div className="text-center">
                                            <div className="mx-auto h-16 w-16 bg-green-100 rounded-full flex items-center justify-center mb-4">
                                                <CheckCircle className="h-10 w-10 text-green-600" />
                                            </div>
                                            <p className="text-lg font-bold text-green-800">{fileName}</p>
                                            <button
                                                type="button"
                                                onClick={(e) => { e.stopPropagation(); setFileName(null); setData('file', null); }}
                                                className="mt-2 text-sm text-red-500 underline hover:text-red-700"
                                            >
                                                Quitar archivo
                                            </button>
                                        </div>
                                    ) : (
                                        <div className="text-center">
                                            <div className="mx-auto h-16 w-16 bg-blue-100 rounded-full flex items-center justify-center mb-4 animate-pulse">
                                                <UploadCloud className="h-10 w-10 text-blue-600" />
                                            </div>
                                            <p className="text-lg font-semibold text-gray-700 mb-1">
                                                Arrastra y suelta tu archivo Excel
                                            </p>
                                            <p className="text-sm text-gray-500">
                                                Soporta formatos (.xlsx, .xls, .csv)
                                            </p>
                                        </div>
                                    )}
                                </div>

                                {errors.file && (
                                    <p className="mt-3 text-sm text-red-600 flex items-center bg-red-50 p-2 rounded-lg">
                                        <svg className="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fillRule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clipRule="evenodd" /></svg>
                                        {errors.file}
                                    </p>
                                )}

                                {/* Catalog Selector */}
                                <div className="mt-10">
                                    <div className="flex items-center justify-between mb-4">
                                        <h4 className="text-sm font-bold text-gray-700 uppercase tracking-widest">
                                            Seleccionar Catálogos a Importar
                                        </h4>
                                        <button
                                            type="button"
                                            onClick={() => setData('sheets', data.sheets.length === Object.keys(availableSheets).length ? [] : Object.keys(availableSheets))}
                                            className="text-xs font-bold text-blue-600 underline"
                                        >
                                            {data.sheets.length === Object.keys(availableSheets).length ? 'Deseleccionar todos' : 'Seleccionar todos'}
                                        </button>
                                    </div>

                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
                                        {Object.entries(availableSheets).map(([key, label]) => (
                                            <label
                                                key={key}
                                                className={`flex items-center p-3 rounded-xl border cursor-pointer transition-all hover:shadow-sm
                                                    ${data.sheets.includes(key)
                                                        ? 'bg-blue-50 border-blue-200'
                                                        : 'bg-white border-gray-100 opacity-60 hover:opacity-100'}`}
                                            >
                                                <input
                                                    type="checkbox"
                                                    checked={data.sheets.includes(key)}
                                                    onChange={() => toggleSheet(key)}
                                                    className="h-4 w-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500"
                                                />
                                                <span className={`ml-3 text-sm font-medium ${data.sheets.includes(key) ? 'text-blue-900' : 'text-gray-600'}`}>
                                                    {label}
                                                </span>
                                            </label>
                                        ))}
                                    </div>
                                </div>

                                <div className="mt-10 flex justify-end">
                                    <button
                                        type="submit"
                                        disabled={processing || !data.file || data.sheets.length === 0}
                                        className="px-8 py-3 bg-gray-900 text-white rounded-xl font-bold shadow-xl hover:bg-black transition-all disabled:opacity-30 disabled:cursor-not-allowed transform active:scale-95"
                                    >
                                        {processing ? (
                                            <span className="flex items-center">
                                                <svg className="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle><path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                                Procesando...
                                            </span>
                                        ) : 'Procesar Importación'}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                </div>
            </div>

            <style dangerouslySetInnerHTML={{
                __html: `
                @keyframes fade-out {
                    0% { opacity: 1; transform: translateY(0); }
                    80% { opacity: 1; transform: translateY(0); }
                    100% { opacity: 0; transform: translateY(-20px); }
                }
                .animate-fade-out {
                    animation: fade-out 5s forwards;
                }
            ` }} />
        </AuthenticatedLayout>
    );
}
