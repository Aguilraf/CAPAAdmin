import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';
import PrimaryButton from '@/Components/PrimaryButton';
import InputError from '@/Components/InputError';
import { useState } from 'react';

export default function Index() {
    const { data, setData, post, processing, errors, reset } = useForm({
        file: null,
    });

    const [fileName, setFileName] = useState(null);

    const handleFileChange = (e) => {
        const file = e.target.files[0];
        setData('file', file);
        setFileName(file ? file.name : null);
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
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Importación Masiva de Datos
                </h2>
            }
        >
            <Head title="Importar Datos" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900">
                            <h3 className="text-lg font-medium text-gray-900 mb-4">
                                Cargar Clasificador por Objeto del Gasto (Excel)
                            </h3>
                            <p className="mb-6 text-sm text-gray-600">
                                Sube el archivo Excel con los Capítulos y Partidas. El sistema detectará automáticamente la estructura y actualizará la base de datos.
                            </p>

                            <div className="mb-6">
                                <a
                                    href={route('import.template')}
                                    className="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150"
                                >
                                    <svg className="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                                    Descargar Plantilla (Layout)
                                </a>
                            </div>

                            <form onSubmit={submit} className="max-w-xl">
                                <div className="flex items-center justify-center w-full">
                                    <label htmlFor="dropzone-file" className="flex flex-col items-center justify-center w-full h-64 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100">
                                        <div className="flex flex-col items-center justify-center pt-5 pb-6">
                                            <svg className="w-8 h-8 mb-4 text-gray-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 16">
                                                <path stroke="currentColor" strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2" />
                                            </svg>
                                            <p className="mb-2 text-sm text-gray-500"><span className="font-semibold">Click para subir</span> o arrastra el archivo aquí</p>
                                            <p className="text-xs text-gray-500">XLSX, XLS o CSV</p>
                                        </div>
                                        <input id="dropzone-file" type="file" className="hidden" onChange={handleFileChange} accept=".xlsx,.xls,.csv" />
                                    </label>
                                </div>
                                {fileName && (
                                    <div className="mt-2 text-sm text-green-600 font-semibold">
                                        Archivo seleccionado: {fileName}
                                    </div>
                                )}

                                <InputError message={errors.file} className="mt-2" />

                                <div className="mt-6">
                                    <PrimaryButton disabled={processing}>
                                        {processing ? 'Importando...' : 'Iniciar Importación'}
                                    </PrimaryButton>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
