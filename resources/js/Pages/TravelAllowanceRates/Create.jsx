import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';
import Form from './Form';

export default function Create({ auth, partidas, niveles }) {
    const { data, setData, post, processing, errors } = useForm({
        partida_id: '',
        cargo: '',
        nivel: [],
        zona_1_amount: '',
        zona_2_amount: '',
        rate_type: '',
        year: new Date().getFullYear(),
        active: true,
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('travel-allowance-rates.store'));
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Nueva Tarifa de Viáticos" />

            <div className="py-12">
                <div className="max-w-4xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            <h2 className="text-2xl font-bold text-gray-800 mb-6">
                                Nueva Tarifa de Viáticos
                            </h2>

                            <Form
                                data={data}
                                setData={setData}
                                errors={errors}
                                processing={processing}
                                submitLabel="Crear Tarifa"
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
