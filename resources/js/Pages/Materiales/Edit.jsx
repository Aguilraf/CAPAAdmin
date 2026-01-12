import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import Checkbox from '@/Components/Checkbox';
import { useEffect } from 'react';

export default function Edit({ material, unidades }) {
    const { data, setData, put, processing, errors, reset } = useForm({
        articulo: material.articulo,
        cantidad: material.cantidad,
        unidad_medida_id: material.unidad_medida_id || '',
        es_default: Boolean(material.es_default),
    });

    useEffect(() => {
        setData({
            articulo: material.articulo,
            cantidad: material.cantidad,
            unidad_medida_id: material.unidad_medida_id || '',
            es_default: Boolean(material.es_default),
        });
    }, [material]);

    const submit = (e) => {
        e.preventDefault();
        put(route('materiales.update', material.id));
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Editar Material
                </h2>
            }
        >
            <Head title="Editar Material" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900">
                            <form onSubmit={submit}>
                                <div>
                                    <InputLabel htmlFor="articulo" value="Artículo" />
                                    <TextInput
                                        id="articulo"
                                        type="text"
                                        name="articulo"
                                        value={data.articulo}
                                        className="mt-1 block w-full"
                                        isFocused={true}
                                        onChange={(e) => setData('articulo', e.target.value)}
                                    />
                                    <InputError message={errors.articulo} className="mt-2" />
                                </div>

                                <div className="mt-4">
                                    <InputLabel htmlFor="unidad_medida_id" value="Unidad de Medida" />
                                    <select
                                        id="unidad_medida_id"
                                        name="unidad_medida_id"
                                        value={data.unidad_medida_id}
                                        className="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        onChange={(e) => setData('unidad_medida_id', e.target.value)}
                                    >
                                        <option value="">Seleccione una unidad</option>
                                        {unidades.map((unidad) => (
                                            <option key={unidad.id} value={unidad.id}>
                                                {unidad.nombre}
                                            </option>
                                        ))}
                                    </select>
                                    <InputError message={errors.unidad_medida_id} className="mt-2" />
                                </div>

                                <div className="mt-4">
                                    <InputLabel htmlFor="cantidad" value="Cantidad (Default)" />
                                    <TextInput
                                        id="cantidad"
                                        type="number"
                                        name="cantidad"
                                        value={data.cantidad}
                                        className="mt-1 block w-full"
                                        min="0"
                                        onChange={(e) => setData('cantidad', e.target.value)}
                                    />
                                    <InputError message={errors.cantidad} className="mt-2" />
                                </div>

                                <div className="block mt-4">
                                    <label className="flex items-center">
                                        <Checkbox
                                            name="es_default"
                                            checked={data.es_default}
                                            onChange={(e) => setData('es_default', e.target.checked)}
                                        />
                                        <span className="ms-2 text-sm text-gray-600">Material Predeterminado (Es default)</span>
                                    </label>
                                    <InputError message={errors.es_default} className="mt-2" />
                                </div>

                                <div className="flex items-center justify-end mt-4">
                                    <Link
                                        href={route('materiales.index')}
                                        className="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150 mr-2"
                                    >
                                        Cancelar
                                    </Link>
                                    <PrimaryButton className="ms-4" disabled={processing}>
                                        Actualizar Material
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
