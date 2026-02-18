
import React, { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, usePage, router } from '@inertiajs/react';
import Pagination from '@/Components/Pagination';
import TextInput from '@/Components/TextInput';
import { Search, Plus, Pencil, Trash2, Car, ImageIcon } from 'lucide-react';

export default function Index({ auth, vehicles, filters }) {
    const { url } = usePage();
    const [searchTerm, setSearchTerm] = useState(filters?.search || '');

    const handleSearch = (term) => {
        setSearchTerm(term);
        router.get(
            route('vehicles.index'),
            { search: term },
            { preserveState: true, replace: true }
        );
    };

    const handleDelete = (id) => {
        if (confirm('¿Estás seguro de que deseas eliminar este vehículo?')) {
            router.delete(route('vehicles.destroy', id), {
                preserveScroll: true,
                onSuccess: () => {
                    // Toast handled by flash message
                }
            });
        }
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Parque Vehicular</h2>}
        >
            <Head title="Parque Vehicular" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900">

                            {/* Actions Bar */}
                            <div className="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                                <div className="flex w-full md:w-auto">
                                    <div className="relative w-full md:w-64">
                                        <TextInput
                                            type="text"
                                            placeholder="Buscar vehículo..."
                                            value={searchTerm}
                                            onChange={(e) => handleSearch(e.target.value)}
                                            className="pl-10 w-full"
                                        />
                                        <Search className="absolute left-3 top-3 h-5 w-5 text-gray-400" />
                                    </div>
                                </div>
                                <Link
                                    href={route('vehicles.create')}
                                    className="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                >
                                    <Plus className="h-4 w-4 mr-2" />
                                    Nuevo Vehículo
                                </Link>
                            </div>

                            {/* Table */}
                            <div className="overflow-x-auto rounded-lg border border-gray-200">
                                <table className="min-w-full divide-y divide-gray-200">
                                    <thead className="bg-gray-50">
                                        <tr>
                                            <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Foto</th>
                                            <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Inventario</th>
                                            <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vehículo</th>
                                            <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">Ubicación / Resguardante</th>
                                            <th scope="col" className="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                            <th scope="col" className="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody className="bg-white divide-y divide-gray-200">
                                        {vehicles.data.length > 0 ? (
                                            vehicles.data.map((vehicle) => (
                                                <tr key={vehicle.id} className="hover:bg-gray-50 transition-colors">
                                                    <td className="px-6 py-4 whitespace-nowrap">
                                                        {vehicle.photo_path ? (
                                                            <div className="h-12 w-16 overflow-hidden rounded-md border border-gray-200">
                                                                <img
                                                                    src={`/storage/${vehicle.photo_path}`}
                                                                    alt={vehicle.brand}
                                                                    className="h-full w-full object-cover"
                                                                />
                                                            </div>
                                                        ) : (
                                                            <div className="h-12 w-16 bg-gray-100 rounded-md flex items-center justify-center border border-gray-200 text-gray-400">
                                                                <ImageIcon className="h-6 w-6" />
                                                            </div>
                                                        )}
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                        {vehicle.inventory_number}
                                                        {vehicle.invoice_number && (
                                                            <div className="text-xs text-gray-500 mt-0.5">Fact: {vehicle.invoice_number}</div>
                                                        )}
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap">
                                                        <div className="text-sm font-semibold text-gray-800">{vehicle.brand} {vehicle.model_year}</div>
                                                        <div className="text-xs text-gray-500">{vehicle.unit_type} - {vehicle.vehicle_type}</div>
                                                        <div className="text-xs text-gray-500 mt-0.5">Placa: {vehicle.plate_number || 'S/N'}</div>
                                                    </td>
                                                    <td className="px-6 py-4 hidden md:table-cell">
                                                        <div className="text-sm text-gray-900">{vehicle.area || 'Sin área asignada'}</div>
                                                        <div className="text-xs text-gray-500">{vehicle.location} {vehicle.sub_location ? `- ${vehicle.sub_location}` : ''}</div>
                                                        {vehicle.custodian && (
                                                            <div className="text-xs text-indigo-600 font-medium mt-1 truncate max-w-xs">{vehicle.custodian}</div>
                                                        )}
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap text-center">
                                                        <span className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${vehicle.active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}`}>
                                                            {vehicle.active ? 'Activo' : 'Inactivo'}
                                                        </span>
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                                        <div className="flex justify-center space-x-3">
                                                            <Link href={route('vehicles.edit', vehicle.id)} className="text-indigo-600 hover:text-indigo-900 transition-colors" title="Editar">
                                                                <Pencil className="h-5 w-5" />
                                                            </Link>
                                                            <button
                                                                onClick={() => handleDelete(vehicle.id)}
                                                                className="text-red-600 hover:text-red-900 transition-colors"
                                                                title="Eliminar"
                                                            >
                                                                <Trash2 className="h-5 w-5" />
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            ))
                                        ) : (
                                            <tr>
                                                <td colSpan="6" className="px-6 py-10 text-center text-gray-500">
                                                    <div className="flex flex-col items-center justify-center">
                                                        <Car className="h-10 w-10 text-gray-300 mb-2" />
                                                        <p>No se encontraron vehículos registrados.</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        )}
                                    </tbody>
                                </table>
                            </div>

                            {/* Pagination */}
                            <div className="mt-4">
                                <Pagination links={vehicles.links} />
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
