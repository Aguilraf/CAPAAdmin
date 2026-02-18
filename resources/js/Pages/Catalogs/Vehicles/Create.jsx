
import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';
import Form from './Form';

export default function Create({ auth, organismos }) {
    const { data, setData, post, processing, errors } = useForm({
        organismo_id: '',
        inventory_number: '',
        unit_type: '',
        brand: '',
        vehicle_type: '',
        color: '',
        model_year: '',
        serial_number: '',
        engine_number: '',
        invoice_number: '',
        supplier: '',
        policy_number: '',
        area: '',
        location: '',
        sub_location: '',
        custodian: '',
        plate_number: '',
        photo: null,
        active: true,
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('vehicles.store'));
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Nuevo Vehículo" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            <h2 className="text-2xl font-bold text-gray-800 mb-6">
                                Nuevo Vehículo
                            </h2>

                            <Form
                                data={data}
                                setData={setData}
                                errors={errors}
                                processing={processing}
                                submitLabel="Crear Vehículo"
                                onSubmit={handleSubmit}
                                organismos={organismos}
                            />
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
