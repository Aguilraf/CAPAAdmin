
import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';
import Form from './Form';

export default function Edit({ auth, vehicle, organismos }) {
    const { data, setData, post, processing, errors } = useForm({
        _method: 'put',
        organismo_id: vehicle.organismo_id || '',
        inventory_number: vehicle.inventory_number || '',
        unit_type: vehicle.unit_type || '',
        brand: vehicle.brand || '',
        vehicle_type: vehicle.vehicle_type || '',
        color: vehicle.color || '',
        model_year: vehicle.model_year || '',
        serial_number: vehicle.serial_number || '',
        engine_number: vehicle.engine_number || '',
        invoice_number: vehicle.invoice_number || '',
        supplier: vehicle.supplier || '',
        policy_number: vehicle.policy_number || '',
        area: vehicle.area || '',
        location: vehicle.location || '',
        sub_location: vehicle.sub_location || '',
        custodian: vehicle.custodian || '',
        plate_number: vehicle.plate_number || '',
        photo: null,
        active: vehicle.active ?? true,
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('vehicles.update', vehicle.id));
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Editar Vehículo" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            <h2 className="text-2xl font-bold text-gray-800 mb-6">
                                Editar Vehículo
                            </h2>

                            {/* Show existing photo thumbnail if available */}
                            {vehicle.photo_path && (
                                <div className="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200 inline-block">
                                    <p className="text-sm font-medium text-gray-700 mb-2">Foto Actual:</p>
                                    <img
                                        src={`/storage/${vehicle.photo_path}`}
                                        alt="Vehículo actual"
                                        className="h-32 w-auto object-cover rounded-md border border-gray-300"
                                    />
                                    <p className="text-xs text-gray-500 mt-2">Subir una nueva foto reemplazará la actual.</p>
                                </div>
                            )}

                            <Form
                                data={data}
                                setData={setData}
                                errors={errors}
                                processing={processing}
                                submitLabel="Actualizar Vehículo"
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
