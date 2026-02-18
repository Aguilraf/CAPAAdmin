import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';
import Form from './Form';

export default function Edit({ auth, rate, partidas, niveles }) {
    const { data, setData, put, processing, errors } = useForm({
        partida_id: rate.partida_id || '',
        cargo: rate.cargo || '',
        nivel: rate.nivel || '',
        zona_1_amount: rate.zona_1_amount || '',
        zona_2_amount: rate.zona_2_amount || '',
        rate_type: rate.rate_type || '',
        year: rate.year || new Date().getFullYear(),
        active: rate.active ?? true,
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        put(route('travel-allowance-rates.update', rate.id));
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Editar Tarifa de Viáticos" />

            <div className="py-12">
                <div className="max-w-4xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            <h2 className="text-2xl font-bold text-gray-800 mb-6">
                                Editar Tarifa de Viáticos
                            </h2>

                            <Form
                                data={data}
                                setData={setData}
                                errors={errors}
                                processing={processing}
                                submitLabel="Actualizar Tarifa"
                                onSubmit={handleSubmit}
                                partidas={partidas}
                                niveles={niveles}
                            />
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
