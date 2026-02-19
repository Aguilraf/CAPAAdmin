import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import InputLabel from '@/Components/InputLabel';
import TextInput from '@/Components/TextInput';
import InputError from '@/Components/InputError';
import PrimaryButton from '@/Components/PrimaryButton';

export default function Edit({ auth, rate, partidas }) {
    const { data, setData, put, processing, errors } = useForm({
        cargo: rate.cargo || '',
        nivel: rate.nivel || '',
        zona_1_amount: rate.zona_1_amount || '',
        zona_2_amount: rate.zona_2_amount || '',
        rate_type: rate.rate_type || 'viaticos',
        partida_id: rate.partida_id || '',
        year: rate.year || new Date().getFullYear(),
        active: rate.active ? true : false,
    });

    const submit = (e) => {
        e.preventDefault();
        put(route('travel-allowance-rates.update', rate.id));
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Editar Tarifa</h2>}
        >
            <Head title="Editar Tarifa" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900">
                            <div className="mb-6">
                                <Link href={route('travel-allowance-rates.index')} className="text-gray-500 hover:text-gray-700">← Regresar</Link>
                            </div>

                            <form onSubmit={submit} className="max-w-2xl">
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    {/* Año */}
                                    <div>
                                        <InputLabel for="year" value="Año Fiscal" />
                                        <TextInput
                                            id="year"
                                            type="number"
                                            className="mt-1 block w-full"
                                            value={data.year}
                                            onChange={(e) => setData('year', e.target.value)}
                                            required
                                        />
                                        <InputError message={errors.year} className="mt-2" />
                                    </div>

                                    {/* Tipo */}
                                    <div>
                                        <InputLabel for="rate_type" value="Tipo de Tarifa" />
                                        <select
                                            id="rate_type"
                                            className="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full"
                                            value={data.rate_type}
                                            onChange={(e) => setData('rate_type', e.target.value)}
                                            required
                                        >
                                            <option value="viaticos">Viáticos</option>
                                            <option value="pasajes">Pasajes</option>
                                            <option value="hospedaje">Hospedaje</option>
                                        </select>
                                        <InputError message={errors.rate_type} className="mt-2" />
                                    </div>

                                    {/* Cargo */}
                                    <div>
                                        <InputLabel for="cargo" value="Cargo / Función" />
                                        <TextInput
                                            id="cargo"
                                            className="mt-1 block w-full"
                                            value={data.cargo}
                                            onChange={(e) => setData('cargo', e.target.value)}
                                            required
                                        />
                                        <InputError message={errors.cargo} className="mt-2" />
                                    </div>

                                    {/* Nivel */}
                                    <div>
                                        <InputLabel for="nivel" value="Nivel del Puesto" />
                                        <TextInput
                                            id="nivel"
                                            className="mt-1 block w-full"
                                            value={data.nivel}
                                            onChange={(e) => setData('nivel', e.target.value)}
                                            required
                                        />
                                        <InputError message={errors.nivel} className="mt-2" />
                                    </div>

                                    {/* Zona I */}
                                    <div>
                                        <InputLabel for="zona_1_amount" value="Monto Zona I" />
                                        <TextInput
                                            id="zona_1_amount"
                                            type="number"
                                            step="0.01"
                                            className="mt-1 block w-full"
                                            value={data.zona_1_amount}
                                            onChange={(e) => setData('zona_1_amount', e.target.value)}
                                            required
                                        />
                                        <InputError message={errors.zona_1_amount} className="mt-2" />
                                    </div>

                                    {/* Zona II */}
                                    <div>
                                        <InputLabel for="zona_2_amount" value="Monto Zona II" />
                                        <TextInput
                                            id="zona_2_amount"
                                            type="number"
                                            step="0.01"
                                            className="mt-1 block w-full"
                                            value={data.zona_2_amount}
                                            onChange={(e) => setData('zona_2_amount', e.target.value)}
                                            required
                                        />
                                        <InputError message={errors.zona_2_amount} className="mt-2" />
                                    </div>

                                    {/* Clave Presupuestal */}
                                    <div className="col-span-2">
                                        <InputLabel for="budget_code" value="Clave Presupuestal (Anexo 2)" />
                                        <TextInput
                                            id="budget_code"
                                            className="mt-1 block w-full"
                                            value={data.budget_code}
                                            onChange={(e) => setData('budget_code', e.target.value)}
                                            placeholder="Ej: 2621125263262131211E027C012611000010600137501..."
                                        />
                                        <InputError message={errors.budget_code} className="mt-2" />
                                    </div>

                                    {/* Partida */}
                                    <div className="col-span-2">
                                        <InputLabel for="partida_id" value="Partida Presupuestal Asociada" />
                                        <select
                                            id="partida_id"
                                            className="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full"
                                            value={data.partida_id}
                                            onChange={(e) => setData('partida_id', e.target.value)}
                                            required
                                        >
                                            <option value="">-- Seleccione Partida --</option>
                                            {partidas.map(p => (
                                                <option key={p.id} value={p.id}>{p.codigo} - {p.nombre}</option>
                                            ))}
                                        </select>
                                        <InputError message={errors.partida_id} className="mt-2" />
                                    </div>
                                </div>

                                <div className="flex items-center justify-end mt-4">
                                    <PrimaryButton className="ml-4" disabled={processing}>
                                        Actualizar Tarifa
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
