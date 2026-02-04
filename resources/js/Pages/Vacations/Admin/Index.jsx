import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, usePage, router, useForm } from '@inertiajs/react';
import { useState } from 'react';
import Modal from '@/Components/Modal';
import SecondaryButton from '@/Components/SecondaryButton';
import PrimaryButton from '@/Components/PrimaryButton';
import InputLabel from '@/Components/InputLabel';
import TextInput from '@/Components/TextInput';
import InputError from '@/Components/InputError';

export default function Index({ auth, empleados, filters }) {
    const { flash } = usePage().props;
    const [search, setSearch] = useState(filters.search || '');

    // Modal state
    const [showModal, setShowModal] = useState(false);
    const [selectedEmployee, setSelectedEmployee] = useState(null);

    // Form for generation
    const { data, setData, post, processing, errors, reset, clearErrors } = useForm({
        empleado_id: '',
        anio: new Date().getFullYear(),
        numero_periodo: '1',
    });

    const handleSearch = (e) => {
        e.preventDefault();
        router.get(route('vacations.admin.index'), { search }, { preserveState: true });
    };

    const openModal = (empleado) => {
        setSelectedEmployee(empleado);
        setData({
            empleado_id: empleado.id,
            anio: new Date().getFullYear(),
            numero_periodo: '1'
        });
        clearErrors();
        setShowModal(true);
    };

    const closeModal = () => {
        setShowModal(false);
        reset();
    };

    const submitGeneration = (e) => {
        e.preventDefault();
        post(route('vacations.admin.generate'), {
            onSuccess: () => closeModal(),
        });
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Administración de Vacaciones</h2>}
        >
            <Head title="Admin Vacaciones" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                    {/* Alertas */}
                    {flash.success && (
                        <div className="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                            {flash.success}
                        </div>
                    )}
                    {flash.error && (
                        <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                            {flash.error}
                        </div>
                    )}

                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div className="flex justify-between items-center mb-4">
                            <h3 className="text-lg font-bold">Resumen de Empleados</h3>
                            <form onSubmit={handleSearch} className="flex gap-2">
                                <TextInput
                                    type="text"
                                    placeholder="Buscar empleado..."
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                    className="block w-full"
                                />
                                <SecondaryButton type="submit">Buscar</SecondaryButton>
                            </form>
                        </div>

                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Empleado</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Antigüedad</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sindicalizado</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Último Periodo</th>
                                        <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody className="bg-white divide-y divide-gray-200">
                                    {empleados.data.map((empleado) => (
                                        <tr key={empleado.id}>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <div className="text-sm font-medium text-gray-900">{empleado.nombre}</div>
                                                <div className="text-sm text-gray-500">{empleado.numero_empleado}</div>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {empleado.antiguedad}
                                                <div className="text-xs text-gray-400">Alta: {empleado.fecha_alta}</div>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {empleado.es_sindicalizado ? 'Sí' : 'No'}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {empleado.ultimo_periodo}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <button
                                                    onClick={() => openModal(empleado)}
                                                    className="text-indigo-600 hover:text-indigo-900 bg-indigo-50 px-3 py-1 rounded-md"
                                                >
                                                    Generar Periodo
                                                </button>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>

                        {/* Pagination links could go here */}
                    </div>
                </div>
            </div>

            {/* Modal Generar Periodo */}
            <Modal show={showModal} onClose={closeModal}>
                <form onSubmit={submitGeneration} className="p-6">
                    <h2 className="text-lg font-medium text-gray-900 mb-4">
                        Generar Periodo Vacacional
                    </h2>

                    {selectedEmployee && (
                        <p className="text-sm text-gray-600 mb-4">
                            Empleado: <b>{selectedEmployee.nombre}</b><br />
                            Antigüedad: {selectedEmployee.antiguedad}
                        </p>
                    )}

                    <div className="mt-4">
                        <InputLabel htmlFor="anio" value="Año" />
                        <TextInput
                            id="anio"
                            type="number"
                            className="mt-1 block w-full"
                            value={data.anio}
                            onChange={(e) => setData('anio', e.target.value)}
                        />
                        <InputError message={errors.anio} className="mt-2" />
                    </div>

                    <div className="mt-4">
                        <InputLabel htmlFor="periodo" value="Periodo" />
                        <select
                            id="periodo"
                            className="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            value={data.numero_periodo}
                            onChange={(e) => setData('numero_periodo', e.target.value)}
                        >
                            <option value="1">1 (Enero - Junio)</option>
                            <option value="2">2 (Julio - Diciembre)</option>
                        </select>
                        <InputError message={errors.numero_periodo} className="mt-2" />
                    </div>

                    <div className="mt-6 flex justify-end">
                        <SecondaryButton onClick={closeModal}>Cancelar</SecondaryButton>
                        <PrimaryButton className="ml-3" disabled={processing}>
                            Generar
                        </PrimaryButton>
                    </div>
                </form>
            </Modal>
        </AuthenticatedLayout>
    );
}
