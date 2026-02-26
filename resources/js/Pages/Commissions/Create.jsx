import React, { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';

export default function Create({ auth, empleados, vehicles }) {
    const [showVehicle, setShowVehicle] = useState(false);

    const { data, setData, post, processing, errors } = useForm({
        empleado_id: '',
        oficio_number: '',
        start_date: '',
        end_date: '',
        reason: 'para operar los equipos de bombeo de las comunidades de Saban y Huay-Max, (GUARDIA DE SABADO, DOMINGO Y DIAS FESTIVOS).',
        vehicle_id: '',
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('commissions.store'));
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Crear Comisión</h2>}
        >
            <Head title="Crear Comisión" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900 border-b border-gray-200">
                            <form onSubmit={submit} className="space-y-6">

                                <div>
                                    <label htmlFor="empleado_id" className="block text-sm font-medium text-gray-700">Empleado</label>
                                    <select
                                        id="empleado_id"
                                        name="empleado_id"
                                        value={data.empleado_id}
                                        onChange={(e) => setData('empleado_id', e.target.value)}
                                        className="mt-1 block w-full pl-3 pr-10 py-2 border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                                    >
                                        <option value="">Seleccione un empleado...</option>
                                        {empleados.map((emp) => (
                                            <option key={emp.id} value={emp.id}>
                                                {emp.nombre} {emp.primer_apellido} {emp.segundo_apellido}
                                            </option>
                                        ))}
                                    </select>
                                    {errors.empleado_id && <div className="text-red-600 text-sm mt-1">{errors.empleado_id}</div>}
                                </div>

                                <div>
                                    <label htmlFor="oficio_number" className="block text-sm font-medium text-gray-700">No. Oficio (Opcional)</label>
                                    <input
                                        type="text"
                                        id="oficio_number"
                                        name="oficio_number"
                                        value={data.oficio_number}
                                        onChange={(e) => setData('oficio_number', e.target.value)}
                                        className="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                    />
                                    {errors.oficio_number && <div className="text-red-600 text-sm mt-1">{errors.oficio_number}</div>}
                                </div>

                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label htmlFor="start_date" className="block text-sm font-medium text-gray-700">Fecha de Inicio</label>
                                        <input
                                            type="date"
                                            id="start_date"
                                            name="start_date"
                                            value={data.start_date}
                                            onChange={(e) => setData('start_date', e.target.value)}
                                            className="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                        />
                                        {errors.start_date && <div className="text-red-600 text-sm mt-1">{errors.start_date}</div>}
                                    </div>
                                    <div>
                                        <label htmlFor="end_date" className="block text-sm font-medium text-gray-700">Fecha de Fin</label>
                                        <input
                                            type="date"
                                            id="end_date"
                                            name="end_date"
                                            value={data.end_date}
                                            onChange={(e) => setData('end_date', e.target.value)}
                                            className="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                        />
                                        {errors.end_date && <div className="text-red-600 text-sm mt-1">{errors.end_date}</div>}
                                    </div>
                                </div>

                                <div>
                                    <label htmlFor="reason" className="block text-sm font-medium text-gray-700">Motivo (Texto para el PDF)</label>
                                    <p className="text-xs text-gray-500 mb-2">Este texto aparecerá en el oficio antes del punto final. Ej: "para operar los equipos..."</p>
                                    <textarea
                                        id="reason"
                                        name="reason"
                                        rows="4"
                                        value={data.reason}
                                        onChange={(e) => setData('reason', e.target.value)}
                                        className="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                    />
                                    {errors.reason && <div className="text-red-600 text-sm mt-1">{errors.reason}</div>}
                                </div>

                                <div className="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                    <div className="flex items-center mb-4">
                                        <input
                                            type="checkbox"
                                            id="use_vehicle"
                                            checked={showVehicle}
                                            onChange={(e) => {
                                                setShowVehicle(e.target.checked);
                                                if (!e.target.checked) setData('vehicle_id', '');
                                            }}
                                            className="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                        />
                                        <label htmlFor="use_vehicle" className="ml-2 block text-sm font-medium text-gray-700">
                                            ¿Requerirá vehículo para esta comisión?
                                        </label>
                                    </div>

                                    {showVehicle && (
                                        <div>
                                            <label htmlFor="vehicle_id" className="block text-sm font-medium text-gray-700">Seleccionar Vehículo</label>
                                            <select
                                                id="vehicle_id"
                                                name="vehicle_id"
                                                value={data.vehicle_id}
                                                onChange={(e) => setData('vehicle_id', e.target.value)}
                                                className="mt-1 block w-full pl-3 pr-10 py-2 border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                                            >
                                                <option value="">Seleccione un vehículo...</option>
                                                {vehicles.map((v) => (
                                                    <option key={v.id} value={v.id}>
                                                        {v.inventory_number} - {v.brand} {v.vehicle_type} ({v.plate_number})
                                                    </option>
                                                ))}
                                            </select>
                                            {errors.vehicle_id && <div className="text-red-600 text-sm mt-1">{errors.vehicle_id}</div>}
                                        </div>
                                    )}
                                </div>

                                <div className="flex items-center justify-end space-x-3">
                                    <Link href={route('commissions.index')} className="text-gray-600 hover:text-gray-900 transition-colors">
                                        Cancelar
                                    </Link>
                                    <button
                                        type="submit"
                                        disabled={processing}
                                        className="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150"
                                    >
                                        Guardar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
