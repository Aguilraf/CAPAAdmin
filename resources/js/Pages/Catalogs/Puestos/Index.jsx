import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, usePage, router } from '@inertiajs/react';
import Pagination from '@/Components/Pagination';
import TextInput from '@/Components/TextInput';
import { Search, Plus, Pencil, Trash2 } from 'lucide-react';

export default function Index({ auth, puestos, filters }) {
    const { url } = usePage();
    const [searchTerm, setSearchTerm] = React.useState(filters.search || '');

    const handleSearch = (term) => {
        setSearchTerm(term);
        router.get(
            route('puestos.index'),
            { search: term },
            { preserveState: true, replace: true }
        );
    };

    const handleDelete = (id) => {
        if (confirm('¿Estás seguro de que deseas eliminar este puesto?')) {
            router.delete(route('puestos.destroy', id));
        }
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Catálogo de Puestos</h2>}
        >
            <Head title="Catálogo de Puestos" />

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
                                            placeholder="Buscar puesto..."
                                            value={searchTerm}
                                            onChange={(e) => handleSearch(e.target.value)}
                                            className="pl-10"
                                        />
                                        <Search className="absolute left-3 top-3 h-5 w-5 text-gray-400" />
                                    </div>
                                </div>
                                <Link
                                    href={route('puestos.create')}
                                    className="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500"
                                >
                                    <Plus className="h-4 w-4 mr-2" />
                                    Nuevo Puesto
                                </Link>
                            </div>

                            {/* Table */}
                            <div className="overflow-x-auto">
                                <table className="min-w-full divide-y divide-gray-200">
                                    <thead className="bg-gray-50">
                                        <tr>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nivel</th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descripción</th>
                                            <th className="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                            <th className="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody className="bg-white divide-y divide-gray-200">
                                        {puestos.data.map((puesto) => (
                                            <tr key={puesto.id}>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{puesto.nombre}</td>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{puesto.nivel}</td>
                                                <td className="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">{puesto.descripcion || '-'}</td>
                                                <td className="px-6 py-4 whitespace-nowrap text-center text-sm">
                                                    <span className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${puesto.activo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}`}>
                                                        {puesto.activo ? 'Activo' : 'Inactivo'}
                                                    </span>
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                                    <Link href={route('puestos.edit', puesto.id)} className="text-indigo-600 hover:text-indigo-900 mr-3">
                                                        <Pencil className="h-5 w-5 inline" />
                                                    </Link>
                                                    <button onClick={() => handleDelete(puesto.id)} className="text-red-600 hover:text-red-900">
                                                        <Trash2 className="h-5 w-5 inline" />
                                                    </button>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                            <div className="mt-4">
                                <Pagination links={puestos.links} />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
