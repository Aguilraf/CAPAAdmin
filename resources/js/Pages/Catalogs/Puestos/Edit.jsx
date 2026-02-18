import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm, Link } from '@inertiajs/react';
import InputLabel from '@/Components/InputLabel';
import TextInput from '@/Components/TextInput';
import InputError from '@/Components/InputError';
import Checkbox from '@/Components/Checkbox';
import PrimaryButton from '@/Components/PrimaryButton';

export default function Edit({ auth, puesto }) {
    const { data, setData, put, processing, errors } = useForm({
        nombre: puesto.nombre || '',
        nivel: puesto.nivel || '',
        descripcion: puesto.descripcion || '',
        activo: puesto.activo !== undefined ? puesto.activo : true,
    });

    const submit = (e) => {
        e.preventDefault();
        put(route('puestos.update', puesto.id));
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Editar Puesto</h2>}
        >
            <Head title="Editar Puesto" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900">
                            <form onSubmit={submit}>
                                <div className="grid grid-cols-1 gap-6">
                                    <div>
                                        <InputLabel htmlFor="nombre" value="Nombre del Puesto" />
                                        <TextInput
                                            id="nombre"
                                            name="nombre"
                                            value={data.nombre}
                                            className="mt-1 block w-full"
                                            onChange={(e) => setData('nombre', e.target.value)}
                                            required
                                        />
                                        <InputError message={errors.nombre} className="mt-2" />
                                    </div>

                                    <div>
                                        <InputLabel htmlFor="nivel" value="Nivel Tabular" />
                                        <TextInput
                                            id="nivel"
                                            name="nivel"
                                            value={data.nivel}
                                            className="mt-1 block w-full"
                                            onChange={(e) => setData('nivel', e.target.value)}
                                            required
                                        />
                                        <InputError message={errors.nivel} className="mt-2" />
                                    </div>

                                    <div>
                                        <InputLabel htmlFor="descripcion" value="Descripción (Opcional)" />
                                        <textarea
                                            id="descripcion"
                                            name="descripcion"
                                            value={data.descripcion}
                                            className="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                            rows="3"
                                            onChange={(e) => setData('descripcion', e.target.value)}
                                        />
                                        <InputError message={errors.descripcion} className="mt-2" />
                                    </div>

                                    <div className="block mt-4">
                                        <label className="flex items-center">
                                            <Checkbox
                                                name="activo"
                                                checked={data.activo}
                                                onChange={(e) => setData('activo', e.target.checked)}
                                            />
                                            <span className="ms-2 text-sm text-gray-600">Activo</span>
                                        </label>
                                    </div>
                                </div>

                                <div className="flex items-center justify-end mt-6">
                                    <Link
                                        href={route('puestos.index')}
                                        className="text-gray-600 hover:text-gray-900 mr-4"
                                    >
                                        CANCELAR
                                    </Link>
                                    <PrimaryButton className="ms-4" disabled={processing}>
                                        ACTUALIZAR PUESTO
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
