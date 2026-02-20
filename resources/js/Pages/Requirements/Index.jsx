import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import { router } from '@inertiajs/react';

export default function Index({ auth, requirements, filters, types }) {

    // Function to handle search - RELOADS PAGE WITH PARAMS (No internal API)
    const handleSearch = (e) => {
        if (e.key === 'Enter') {
            router.get(route('requirements.index'), { ...filters, search: e.target.value }, { preserveState: true });
        }
    };

    const handleDelete = (id) => {
        if (confirm('¿Estás seguro de querer eliminar este requerimiento?')) {
            router.delete(route('requirements.destroy', id));
        }
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Requerimientos</h2>}
        >
            <Head title="Requerimientos" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900">

                            <div className="flex justify-between mb-4">
                                <div className="flex gap-4">
                                    <TextInput
                                        placeholder="Buscar..."
                                        defaultValue={filters.search}
                                        onKeyDown={handleSearch}
                                        className="w-64"
                                    />
                                </div>
                                <Link href={route('requirements.create')}>
                                    <PrimaryButton>Nuevo Requerimiento</PrimaryButton>
                                </Link>
                            </div>

                            <table className="min-w-full divide-y divide-gray-200">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Folio</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descripción</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                        <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody className="bg-white divide-y divide-gray-200">
                                    {requirements.data.map((req) => (
                                        <tr key={req.id}>
                                            <td className="px-6 py-4 whitespace-nowrap">{String(req.requirement_number).padStart(3, '0')}/{req.year}</td>
                                            <td className="px-6 py-4 whitespace-nowrap">{types[req.type] || req.type}</td>
                                            <td className="px-6 py-4">{req.description || '-'}</td>
                                            <td className="px-6 py-4 whitespace-nowrap">${Number(req.total).toLocaleString('es-MX', { minimumFractionDigits: 2 })}</td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                {req.status === 'pending' ? (
                                                    <Link
                                                        href={route('payments.create', { requirement_id: req.id })}
                                                        className="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800 hover:bg-yellow-200 transition-colors cursor-pointer"
                                                        title="Pagar este requerimiento"
                                                    >
                                                        {req.status}
                                                    </Link>
                                                ) : (
                                                    <span className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${req.status === 'paid' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}`}>
                                                        {req.status === 'paid' ? 'Pagado' : req.status}
                                                    </span>
                                                )}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <a href={route('requirements.pdf', req.id)} className="text-blue-600 hover:text-blue-900 mr-4" title="Requerimiento PDF" target="_blank">PDF</a>
                                                {req.type === 'viaticos' && (
                                                    <div className="flex flex-col gap-1 mt-1">

                                                        {req.travel_allowance && req.travel_allowance.commissioners && req.travel_allowance.commissioners.length > 0 && (
                                                            <div className="flex flex-col gap-2 mt-2 items-end">
                                                                {req.travel_allowance.commissioners.map(comm => (
                                                                    <div key={comm.id} className="flex items-center gap-2 text-[10px]">
                                                                        <span className="font-bold text-gray-500 uppercase">{comm.primer_apellido}:</span>
                                                                        <a
                                                                            href={route('requirements.anexo-2', [req.id, comm.id])}
                                                                            className="text-purple-600 hover:text-purple-800 font-semibold"
                                                                            target="_blank"
                                                                            title="Descargar Anexo 2"
                                                                        >
                                                                            ANEXO 2
                                                                        </a>
                                                                        <span className="text-gray-300">|</span>
                                                                        <a
                                                                            href={route('requirements.comprobacion-viaticos', [req.id, comm.id])}
                                                                            className="text-orange-600 hover:text-orange-800 font-semibold"
                                                                            target="_blank"
                                                                            title="Descargar Comprobación"
                                                                        >
                                                                            COMPROBACIÓN
                                                                        </a>
                                                                    </div>
                                                                ))}
                                                            </div>
                                                        )}
                                                    </div>
                                                )}
                                                {req.type === 'cfe' && (
                                                    <a href={route('requirements.cfe-relation', req.id)} className="text-green-600 hover:text-green-900 mr-4" target="_blank">Relación</a>
                                                )}
                                                <Link href={route('requirements.edit', req.id)} className="text-indigo-600 hover:text-indigo-900 mr-4">Editar</Link>
                                                <button onClick={() => handleDelete(req.id)} className="text-red-600 hover:text-red-900">Eliminar</button>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                            {requirements.data.length === 0 && (
                                <div className="text-center py-4 text-gray-500">No hay requerimientos encontrados.</div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
