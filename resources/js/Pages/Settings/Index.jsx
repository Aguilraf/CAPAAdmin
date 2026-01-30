import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm, usePage } from '@inertiajs/react';
import PrimaryButton from '@/Components/PrimaryButton';
import InputLabel from '@/Components/InputLabel';

export default function Index({ initialSettings }) {
    const { data, setData, post, processing, errors } = useForm({
        logo_qroo: null,
        logo_unidos: null,
        logo_capa: null,
        footer_organismo: initialSettings.footer_organismo || '',
        footer_direccion: initialSettings.footer_direccion || '',
        footer_telefono: initialSettings.footer_telefono || '',
        footer_email: initialSettings.footer_email || '',
        footer_imagen: null,
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('settings.update'), {
            preserveScroll: true,
            onSuccess: () => {
                // Optional: clear file inputs or show success message manually if not using flash
            }
        });
    };

    const getLogoUrl = (path) => {
        return path ? `/media/${path}` : null;
    };

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

                    <div className="bg-white p-6 shadow-sm sm:rounded-lg">
                        <h3 className="text-lg font-medium text-gray-900 mb-6 border-b pb-2">Logotipos Oficiales</h3>

                        <form onSubmit={submit} className="space-y-8">

                            {/* Logo QROO */}
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-8 items-start">
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
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-8 items-start">
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

                            <div className="flex justify-end pt-4">
                                <PrimaryButton disabled={processing}>
                                    Guardar Cambios
                                </PrimaryButton>
                            </div>

                        </form>

                        <div className="mt-10 border-t pt-6">
                            <h3 className="text-lg font-medium text-gray-900 mb-6 border-b pb-2">Configuración del Pie de Página (Reportes)</h3>
                            <form onSubmit={submit} className="space-y-6">
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
                                        Guardar Configuración
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
