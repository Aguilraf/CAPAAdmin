import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router, Link } from '@inertiajs/react'; // Use router instead of useForm for simple actions
import PrimaryButton from '@/Components/PrimaryButton';
import DangerButton from '@/Components/DangerButton';
// import Pagination from '@/Components/Pagination'; // Removed unused
import TextInput from '@/Components/TextInput';
import InputLabel from '@/Components/InputLabel';
import Modal from '@/Components/Modal';
import SecondaryButton from '@/Components/SecondaryButton';
import { useState } from 'react';

export default function Requests({ auth, requests, filters, flash, errors }) {
    const [search, setSearch] = useState(filters.search || '');

    const handleSearch = (e) => {
        e.preventDefault();
        router.get(route('vacations.admin.requests'), { search }, { preserveState: true });
    };

    const [confirmingApproval, setConfirmingApproval] = useState(null);
    const [numeroOficio, setNumeroOficio] = useState('');
    const [errorOficio, setErrorOficio] = useState('');

    const openApproveModal = (id) => {
        setConfirmingApproval(id);
        setNumeroOficio('');
        setErrorOficio('');
    };

    const confirmApprove = () => {
        if (!numeroOficio.trim()) {
            setErrorOficio('El número de oficio es obligatorio');
            return;
        }

        router.post(route('vacations.admin.requests.approve', confirmingApproval),
            { numero_oficio: numeroOficio },
            {
                onSuccess: () => closeModal(),
                preserveScroll: true
            }
        );
    };

    const closeModal = () => {
        setConfirmingApproval(null);
        setNumeroOficio('');
        setErrorOficio('');
    };

    const handleReject = (id) => {
        if (confirm('¿Estás seguro de RECHAZAR esta solicitud? Esta acción liberará los días pendientes.')) {
            router.post(route('vacations.admin.requests.reject', id));
        }
    };

    const formatDate = (dateStr) => {
        if (!dateStr) return '';
        const cleanDate = dateStr.includes('T') ? dateStr.split('T')[0] : dateStr;
        const [year, month, day] = cleanDate.split('-');
        return `${day}/${month}/${year}`;
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Solicitudes Pendientes</h2>}
        >
            <Head title="Solicitudes Pendientes" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    {flash?.success && (
                        <div className="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                            {flash.success}
                        </div>
                    )}
                    {flash?.error && (
                        <div className="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                            {flash.error}
                        </div>
                    )}

                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                        {/* Search */}
                        <form onSubmit={handleSearch} className="mb-6 flex gap-4">
                            <TextInput
                                className="w-full md:w-1/3"
                                placeholder="Buscar empleado..."
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                            />
                            <PrimaryButton>Buscar</PrimaryButton>
                        </form>

                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Empleado</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Periodo Afectado</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fechas</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Días</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody className="bg-white divide-y divide-gray-200">
                                    {requests.data.length > 0 ? requests.data.map((solicitud) => (
                                        <tr key={solicitud.id}>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <div className="text-sm font-medium text-gray-900">{solicitud.empleado.nombre}</div>
                                                <div className="text-sm text-gray-500">{solicitud.empleado.numero_empleado}</div>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {solicitud.tipo_solicitud}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {(() => {
                                                    const detalle = solicitud.detalles && solicitud.detalles.length > 0 ? solicitud.detalles[0] : null;

                                                    // Check for Vacation Period
                                                    if (detalle?.origen?.periodo) {
                                                        const periodoInfo = detalle.origen.periodo;
                                                        const suffix = periodoInfo.numero_periodo === 1 ? 'er' : 'do';
                                                        return `${periodoInfo.numero_periodo} ${suffix} PERIODO ${periodoInfo.anio}`;
                                                    }

                                                    // Check for Bonus
                                                    if (detalle?.origen?.cuatrimestre) {
                                                        const { cuatrimestre, anio } = detalle.origen;
                                                        const ordinal = cuatrimestre === 1 ? '1er' : (cuatrimestre === 2 ? '2do' : '3er');
                                                        return `BONO ${ordinal} CUATRIMESTRE ${anio}`;
                                                    }

                                                    return '-';
                                                })()}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                Del {formatDate(solicitud.fecha_inicio)} al {formatDate(solicitud.fecha_fin)}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                                {solicitud.dias_solicitados}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                                <button
                                                    onClick={() => openApproveModal(solicitud.id)}
                                                    className="text-green-600 hover:text-green-900 font-bold"
                                                >
                                                    Aprobar
                                                </button>
                                                <button
                                                    onClick={() => handleReject(solicitud.id)}
                                                    className="text-red-600 hover:text-red-900 font-bold"
                                                >
                                                    Rechazar
                                                </button>
                                            </td>
                                        </tr>
                                    )) : (
                                        <tr>
                                            <td colSpan="6" className="px-6 py-4 text-center text-sm text-gray-500">
                                                No hay solicitudes pendientes.
                                            </td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>

                        {/* Paginación simple si es necesaria */}
                        <div className="mt-4 flex flex-wrap gap-1">
                            {requests.links && requests.links.map((link, k) => (
                                link.url ? (
                                    <Link
                                        key={k}
                                        href={link.url}
                                        className={`px-4 py-2 border rounded ${link.active ? 'bg-indigo-500 text-white' : 'bg-white text-gray-700'}`}
                                    >
                                        <span dangerouslySetInnerHTML={{ __html: link.label }} />
                                    </Link>
                                ) : (
                                    <span
                                        key={k}
                                        className="px-4 py-2 border rounded bg-white text-gray-400 cursor-not-allowed"
                                        dangerouslySetInnerHTML={{ __html: link.label }}
                                    />
                                )
                            ))}
                        </div>
                    </div>
                </div>
            </div>

            {/* Modal de Aprobación */}
            <Modal show={confirmingApproval !== null} onClose={closeModal}>
                <div className="p-6">
                    <h2 className="text-lg font-medium text-gray-900">
                        Aprobar Solicitud
                    </h2>

                    <p className="mt-1 text-sm text-gray-600">
                        Por favor ingresa el número de oficio (solo números, ej. 101 o 101 BIS).
                    </p>

                    <div className="mt-6">
                        <InputLabel htmlFor="numero_oficio" value="Número de Oficio" className="sr-only" />

                        <TextInput
                            id="numero_oficio"
                            name="numero_oficio"
                            value={numeroOficio}
                            onChange={(e) => setNumeroOficio(e.target.value.toUpperCase())}
                            className="mt-1 block w-3/4"
                            placeholder="Ej. 2026001 o 2026001 BIS"
                            isFocused
                        />

                        {errorOficio && <p className="text-red-500 text-xs mt-1">{errorOficio}</p>}
                        {/* Display server-side error for duplicate or invalid office number */}
                        {errors && errors.numero_oficio && (
                            <p className="text-red-500 text-sm mt-2 font-bold p-2 bg-red-50 rounded border border-red-200">
                                {errors.numero_oficio}
                            </p>
                        )}
                    </div>

                    <div className="mt-6 flex justify-end">
                        <SecondaryButton onClick={closeModal}>
                            Cancelar
                        </SecondaryButton>

                        <PrimaryButton className="ml-3" onClick={confirmApprove}>
                            Confirmar Aprobación
                        </PrimaryButton>
                    </div>
                </div>
            </Modal>
        </AuthenticatedLayout>
    );
}

// Helper para Link se me olvido importarlo
