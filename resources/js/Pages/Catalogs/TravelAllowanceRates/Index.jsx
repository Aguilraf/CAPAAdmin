import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, usePage, router } from '@inertiajs/react'; // Import router
import Pagination from '@/Components/Pagination';
import TextInput from '@/Components/TextInput';
import { Search, Plus, Pencil, Trash2 } from 'lucide-react'; // Using Lucide React

export default function Index({ auth, rates, filters }) {
    const { url } = usePage();

    // Debounce search
    const [searchTerm, setSearchTerm] = React.useState(filters.search || '');
    const [yearFilter, setYearFilter] = React.useState(filters.year || '');
    const [typeFilter, setTypeFilter] = React.useState(filters.rate_type || ''); // Changed to match controller filter 'rate_type'

    const handleSearch = (term) => {
        setSearchTerm(term);
        router.get(
            route('travel-allowance-rates.index'),
            { search: term, year: yearFilter, rate_type: typeFilter },
            { preserveState: true, replace: true }
        );
    };

    const handleYearChange = (e) => {
        const year = e.target.value;
        setYearFilter(year);
        router.get(
            route('travel-allowance-rates.index'),
            { search: searchTerm, year: year, rate_type: typeFilter },
            { preserveState: true, replace: true }
        );
    };

    const handleTypeChange = (e) => {
        const type = e.target.value;
        setTypeFilter(type);
        router.get(
            route('travel-allowance-rates.index'),
            { search: searchTerm, year: yearFilter, rate_type: type },
            { preserveState: true, replace: true }
        );
    };

    const handleDelete = (id) => {
        if (confirm('¿Estás seguro de que deseas eliminar esta tarifa?')) {
            router.delete(route('travel-allowance-rates.destroy', id));
        }
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Catálogo de Viáticos, Hospedaje y Pasaje</h2>}
        >
            <Head title="Catálogo de Viáticos" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900">

                            {/* Actions Bar */}
                            <div className="flex justify-between items-center mb-6">
                                <div className="flex space-x-4">
                                    <div className="relative">
                                        <TextInput
                                            type="text"
                                            placeholder="Buscar por cargo/nivel..."
                                            value={searchTerm}
                                            onChange={(e) => handleSearch(e.target.value)}
                                            className="pl-10"
                                        />
                                        <Search className="absolute left-3 top-3 h-5 w-5 text-gray-400" />
                                    </div>
                                    <select
                                        value={yearFilter}
                                        onChange={handleYearChange}
                                        className="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    >
                                        <option value="">Todos los Años</option>
                                        {[2024, 2025, 2026, 2027].map(y => <option key={y} value={y}>{y}</option>)}
                                    </select>
                                    <select
                                        value={typeFilter}
                                        onChange={handleTypeChange}
                                        className="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    >
                                        <option value="">Todos los Tipos</option>
                                        <option value="viaticos">Viáticos</option>
                                        <option value="pasajes">Pasajes</option>
                                        <option value="hospedaje">Hospedaje</option>
                                    </select>
                                </div>
                                <Link
                                    href={route('travel-allowance-rates.create')}
                                    className="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500"
                                >
                                    <Plus className="h-4 w-4 mr-2" />
                                    Nueva Tarifa
                                </Link>
                            </div>

                            {/* Table */}
                            <div className="overflow-x-auto">
                                <table className="min-w-full divide-y divide-gray-200">
                                    <thead className="bg-gray-50">
                                        <tr>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Año</th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cargo / Nivel</th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Partida</th>
                                            <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Zona I</th>
                                            <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Zona II</th>
                                            <th className="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody className="bg-white divide-y divide-gray-200">
                                        {rates.data.map((rate) => (
                                            <tr key={rate.id}>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{rate.year}</td>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900 capitalize">{rate.rate_type}</td>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    <div className="font-medium">{rate.cargo}</div>
                                                    <div className="text-gray-500 text-xs">{rate.nivel}</div>
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {rate.partida ? `${rate.partida.codigo} - ${rate.partida.nombre}` : 'N/A'}
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">${rate.zona_1_amount}</td>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">${rate.zona_2_amount}</td>
                                                <td className="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                                    <Link href={route('travel-allowance-rates.edit', rate.id)} className="text-indigo-600 hover:text-indigo-900 mr-3">
                                                        <Pencil className="h-5 w-5 inline" />
                                                    </Link>
                                                    <button onClick={() => handleDelete(rate.id)} className="text-red-600 hover:text-red-900">
                                                        <Trash2 className="h-5 w-5 inline" />
                                                    </button>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                            <div className="mt-4">
                                <Pagination links={rates.links} />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
