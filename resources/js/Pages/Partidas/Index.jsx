import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm, router } from '@inertiajs/react';
import TextInput from '@/Components/TextInput';
import { useState } from 'react';
import Modal from '@/Components/Modal';
import SecondaryButton from '@/Components/SecondaryButton';
import DangerButton from '@/Components/DangerButton';

export default function Index({ partidas, filters }) {
    const [searchTerm, setSearchTerm] = useState(filters?.search || '');
    const { delete: destroy, processing } = useForm();
    const [confirmingDeletion, setConfirmingDeletion] = useState(false);
    const [selectedId, setSelectedId] = useState(null);

    // Safety check for critical data
    if (!partidas || !partidas.data) {
        return (
            <AuthenticatedLayout>
                <div className="p-12 text-center text-gray-500">
                    Cargando datos o no hay información disponible...
                </div>
            </AuthenticatedLayout>
        );
    }

    const handleSearch = (e) => {
        const value = e.target.value;
        setSearchTerm(value);
        router.get(
            route('partidas.index'),
            { search: value },
            { preserveState: true, replace: true }
        );
    };

    const confirmDeletion = (id) => {
        setSelectedId(id);
        setConfirmingDeletion(true);
    };

    const deleteItem = () => {
        destroy(route('partidas.destroy', selectedId), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
        });
    };

    const closeModal = () => {
        setConfirmingDeletion(false);
        setSelectedId(null);
    };

    return (
        <AuthenticatedLayout
            header={
                <div className="flex justify-between items-center">
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">
                        Partidas
                    </h2>
                    <Link
                        href={route('partidas.create')}
                        className="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        Nueva Partida
                    </Link>
                </div>
            }
        >
            <Head title="Partidas" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="mb-6">
                        <TextInput
                            type="text"
                            placeholder="Buscar por código, nombre o capítulo..."
                            value={searchTerm}
                            onChange={handleSearch}
                            className="w-full md:w-1/3"
                        />
                    </div>

                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900">
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Capítulo</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Código</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                        <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody className="bg-white divide-y divide-gray-200">
                                    {partidas.data.length === 0 ? (
                                        <tr>
                                            <td colSpan="5" className="px-6 py-4 text-center text-gray-500">
                                                No hay partidas registradas.
                                            </td>
                                        </tr>
                                    ) : (
                                        partidas.data.map((partida) => (
                                            <tr key={partida.id}>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {partida.capitulo?.nombre ?? 'N/A'} ({partida.capitulo?.codigo ?? 'N/A'})
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                    {partida.codigo}
                                                </td>
                                                <td className="px-6 py-4 whitespace-normal text-sm text-gray-500">
                                                    {partida.nombre}
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    <span className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${partida.activo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}`}>
                                                        {partida.activo ? 'Activa' : 'Inactiva'}
                                                    </span>
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                    <Link href={route('partidas.edit', partida.id)} className="text-indigo-600 hover:text-indigo-900 mr-4">Editar</Link>
                                                    <button onClick={() => confirmDeletion(partida.id)} className="text-red-600 hover:text-red-900">Eliminar</button>
                                                </td>
                                            </tr>
                                        ))
                                    )}
                                </tbody>
                            </table>

                            {/* Pagination - Check if links exist */}
                            {partidas.links && (
                                <div className="mt-4 flex flex-col md:flex-row justify-between items-center">
                                    <span className="text-sm text-gray-700">
                                        Mostrando {partidas.from} a {partidas.to} de {partidas.total} resultados
                                    </span>
                                    <div className="mt-2 md:mt-0 flex flex-wrap gap-1">
                                        {partidas.links.map((link, index) => (
                                            <Link
                                                key={index}
                                                href={link.url || '#'}
                                                className={`px-3 py-1 rounded border text-sm ${link.active
                                                        ? 'bg-blue-600 text-white border-blue-600'
                                                        : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'
                                                    } ${!link.url ? 'opacity-50 cursor-not-allowed hidden' : ''}`}
                                                dangerouslySetInnerHTML={{ __html: link.label }}
                                                preserveState
                                                preserveScroll
                                            />
                                        ))}
                                    </div>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>

            <Modal show={confirmingDeletion} onClose={closeModal}>
                <div className="p-6">
                    <h2 className="text-lg font-medium text-gray-900">
                        ¿Estás seguro de eliminar esta partida?
                    </h2>
                    <p className="mt-1 text-sm text-gray-600">
                        Esta acción no se puede deshacer.
                    </p>
                    <div className="mt-6 flex justify-end">
                        <SecondaryButton onClick={closeModal}>Cancelar</SecondaryButton>
                        <DangerButton className="ml-3" disabled={processing} onClick={deleteItem}>
                            Eliminar
                        </DangerButton>
                    </div>
                </div>
            </Modal>
        </AuthenticatedLayout>
    );
}
