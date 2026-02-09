import React, { useState, useRef } from 'react';
import axios from 'axios';
import * as XLSX from 'xlsx';
// const XLSX = {};
import { Upload, FileUp, AlertCircle, CheckCircle } from 'lucide-react';
import { formatCurrency } from '../../firefighters_helpers';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';

export default function Import(props) {
    const [file, setFile] = useState(null);
    const [previewData, setPreviewData] = useState([]);
    const [headers, setHeaders] = useState([]);
    const [loading, setLoading] = useState(false);
    const [progress, setProgress] = useState(0); // 0 to 100
    const [status, setStatus] = useState(null); // { type: 'success' | 'error', message: '' }
    const fileInputRef = useRef(null);

    const handleFileChange = (e) => {
        const selectedFile = e.target.files[0];
        setFile(selectedFile);
        setStatus(null);
        setProgress(0);

        if (selectedFile) {
            readExcel(selectedFile);
        }
    };

    const readExcel = (file) => {
        const reader = new FileReader();
        reader.onload = (e) => {
            const data = e.target.result;
            const workbook = XLSX.read(data, { type: 'binary' });
            const sheetName = workbook.SheetNames[0]; // Read first sheet
            const sheet = workbook.Sheets[sheetName];
            const parsedData = XLSX.utils.sheet_to_json(sheet, { header: 1 }); // Header: 1 returns array of arrays

            if (parsedData.length > 0) {
                setHeaders(parsedData[0]);
                setPreviewData(parsedData.slice(1, 6)); // Preview first 5 rows
            }
        };
        reader.readAsBinaryString(file);
    };

    const handleUpload = async () => {
        if (!file) return;

        setLoading(true);
        setStatus(null);
        setProgress(10); // Start progress

        const formData = new FormData();
        formData.append('file', file);

        try {
            setProgress(50);
            const response = await axios.post('/captures/import', formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                },
                onUploadProgress: (progressEvent) => {
                    const percentCompleted = Math.round((progressEvent.loaded * 100) / progressEvent.total);
                    // Only update up to 80% here, wait for server response for 100%
                    if (percentCompleted < 80) setProgress(percentCompleted);
                }
            });

            setProgress(100);
            setStatus({
                type: 'success',
                message: response.data.message || 'Importación completada exitosamente.'
            });

            if (response.data.skipped_rows > 0) {
                setStatus(prev => ({
                    ...prev,
                    message: `${prev.message} Se omitieron ${response.data.skipped_rows} filas (ver logs o duplicados).`
                }));
            }

        } catch (error) {
            setProgress(0);
            console.error(error);
            const errorMsg = error.response?.data?.message || error.message || 'Error desconocido al importar.';
            setStatus({
                type: 'error',
                message: `Error al importar: ${errorMsg}`
            });
        } finally {
            setLoading(false);
        }
    };

    return (
        <AuthenticatedLayout
            user={props.auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Importar Capturas Masivas</h2>}
        >
            <Head title="Importar" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900">
                            <div className="max-w-3xl mx-auto">
                                <div className="mb-8 text-center">
                                    <h2 className="text-3xl font-bold text-gray-800 mb-2">Importar Capturas</h2>
                                    <p className="text-gray-500">Carga un archivo Excel (.xlsx, .xls) para registrar capturas masivamente.</p>
                                </div>

                                <div className="bg-blue-50 border border-blue-200 rounded-xl p-6 mb-8">
                                    <h3 className="font-bold text-blue-800 mb-2 flex items-center gap-2">
                                        <AlertCircle className="w-5 h-5" /> Instrucciones del Formato
                                    </h3>
                                    <ul className="list-disc list-inside text-sm text-blue-700 space-y-1">
                                        <li>El archivo debe tener las siguientes columnas (el orden importa si no hay cabeceras, pero idealmente usa cabeceras):</li>
                                        <li className="font-mono bg-blue-100 inline-block px-2 py-0.5 rounded ml-4 mt-1">DATE, YEAR, COMMUNITY, FIREFIGHTER, SUBTOTAL</li>
                                        <li><b>DATE:</b> Formato YYYY-MM-DD</li>
                                        <li><b>YEAR:</b> Año numérico (ej. 2024)</li>
                                        <li><b>COMMUNITY:</b> Nombre exacto de la comunidad (debe existir en el catálogo).</li>
                                        <li><b>FIREFIGHTER:</b> Nombre del bombero (si no existe, se intentará buscar o crear si la lógica lo permite).</li>
                                        <li><b>SUBTOTAL:</b> Monto numérico.</li>
                                    </ul>
                                    <div className="mt-4 text-center">
                                        <a href="/captures/import/template" className="text-blue-600 underline font-semibold text-sm hover:text-blue-800">Descargar Plantilla de Ejemplo</a>
                                    </div>
                                </div>

                                <div className="border-2 border-dashed border-gray-300 rounded-2xl p-10 flex flex-col items-center justify-center bg-gray-50 hover:bg-white transition-all cursor-pointer"
                                    onClick={() => fileInputRef.current?.click()}
                                >
                                    <input
                                        ref={fileInputRef}
                                        type="file"
                                        accept=".xlsx, .xls, .csv"
                                        className="hidden"
                                        onChange={handleFileChange}
                                    />
                                    <div className="p-4 bg-blue-100 rounded-full mb-4">
                                        <FileUp className="w-10 h-10 text-blue-600" />
                                    </div>
                                    {file ? (
                                        <div className="text-center">
                                            <p className="text-lg font-bold text-gray-800">{file.name}</p>
                                            <p className="text-sm text-gray-500">{(file.size / 1024).toFixed(2)} KB</p>
                                        </div>
                                    ) : (
                                        <div className="text-center">
                                            <p className="text-lg font-medium text-gray-600">Haz clic para seleccionar archivo</p>
                                            <p className="text-sm text-gray-400 mt-1">Soporta archivos Excel</p>
                                        </div>
                                    )}
                                </div>

                                {file && previewData.length > 0 && (
                                    <div className="mt-8 animate-in slide-in-from-bottom duration-300">
                                        <h3 className="font-bold text-gray-700 mb-2 text-sm uppercase tracking-wide">Vista Previa (Primeras 5 filas)</h3>
                                        <div className="overflow-x-auto border rounded-lg shadow-sm">
                                            <table className="min-w-full divide-y divide-gray-200 text-xs">
                                                <thead className="bg-gray-100">
                                                    <tr>
                                                        {headers.map((h, i) => (
                                                            <th key={i} className="px-3 py-2 text-left font-bold text-gray-600 uppercase">{h}</th>
                                                        ))}
                                                    </tr>
                                                </thead>
                                                <tbody className="bg-white divide-y divide-gray-200">
                                                    {previewData.map((row, i) => (
                                                        <tr key={i}>
                                                            {row.map((cell, j) => (
                                                                <td key={j} className="px-3 py-2 text-gray-700">{cell}</td>
                                                            ))}
                                                        </tr>
                                                    ))}
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                )}

                                {loading && (
                                    <div className="mt-6">
                                        <div className="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                                            <div className="bg-blue-600 h-2.5 rounded-full transition-all duration-300" style={{ width: `${progress}%` }}></div>
                                        </div>
                                        <p className="text-center text-xs text-gray-500 mt-2">Procesando... {progress}%</p>
                                    </div>
                                )}

                                {status && (
                                    <div className={`mt-6 p-4 rounded-lg flex items-start gap-3 ${status.type === 'success' ? 'bg-green-50 border border-green-200 text-green-800' : 'bg-red-50 border border-red-200 text-red-800'
                                        }`}>
                                        {status.type === 'success' ? <CheckCircle className="w-5 h-5 flex-shrink-0" /> : <AlertCircle className="w-5 h-5 flex-shrink-0" />}
                                        <div>
                                            <p className="font-bold">{status.type === 'success' ? 'Éxito' : 'Error'}</p>
                                            <p className="text-sm">{status.message}</p>
                                        </div>
                                    </div>
                                )}

                                <div className="mt-8 flex justify-end">
                                    <button
                                        onClick={handleUpload}
                                        disabled={!file || loading}
                                        className={`px-6 py-3 bg-blue-700 text-white font-bold rounded-lg shadow hover:bg-blue-800 transition-all flex items-center gap-2 ${(!file || loading) ? 'opacity-50 cursor-not-allowed' : ''
                                            }`}
                                    >
                                        <Upload className="w-5 h-5" />
                                        {loading ? 'Subiendo...' : 'Importar Datos'}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
