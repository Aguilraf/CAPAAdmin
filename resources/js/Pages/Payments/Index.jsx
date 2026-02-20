import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import Modal from '@/Components/Modal';
import { useState } from 'react';

export default function Index({ auth, payments }) {
    const [showingSelectionModal, setShowingSelectionModal] = useState(false);

    const openSelectionModal = () => setShowingSelectionModal(true);
    const closeSelectionModal = () => setShowingSelectionModal(false);

    const handleCreatePayment = (withRequirement) => {
        closeSelectionModal();
        if (withRequirement) {
            router.get(route('payments.create'));
        } else {
            // For now, sin requerimiento is not implemented as per user request
            alert('La lógica para pagos sin requerimiento se implementará más adelante.');
        }
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Pagos (Módulo de Cobro)</h2>}
        >
            <Head title="Pagos" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900">
                            <div className="flex justify-between mb-6">
                                <h3 className="text-lg font-medium">Historial de Pagos</h3>
                                <PrimaryButton onClick={openSelectionModal}>
                                    Nuevo Pago
                                </PrimaryButton>
                            </div>

                            <table className="min-w-full divide-y divide-gray-200">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Referencia</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Beneficiario</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requerimiento</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monto</th>
                                        <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody className="bg-white divide-y divide-gray-200">
                                    {payments.data.map((payment) => (
                                        <tr key={payment.id}>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                {new Date(payment.payment_date).toLocaleDateString('es-MX')}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <span className="font-mono text-xs bg-gray-100 px-2 py-1 rounded">
                                                    {payment.payment_type === 'cheque' ? 'CH-' : 'TR-'}
                                                    {payment.reference}
                                                </span>
                                            </td>
                                            <td className="px-6 py-4">
                                                <div className="text-sm font-medium text-gray-900">{payment.beneficiary}</div>
                                                <div className="text-xs text-gray-500 truncate max-w-xs">{payment.concept}</div>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                {payment.requirement ? (
                                                    <Link 
                                                        href={route('requirements.index', { search: payment.requirement.requirement_number })}
                                                        className="text-indigo-600 hover:text-indigo-900"
                                                    >
                                                        {String(payment.requirement.requirement_number).padStart(3, '0')}/{payment.requirement.year}
                                                    </Link>
                                                ) : (
                                                    <span className="text-gray-400 italic">Sin Req.</span>
                                                )}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap font-bold">
                                                ${Number(payment.amount).toLocaleString('es-MX', { minimumFractionDigits: 2 })}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <a 
                                                    href={route('payments.pdf', payment.id)} 
                                                    target="_blank"
                                                    className="text-blue-600 hover:text-blue-900 mr-4"
                                                >
                                                    Imprimir Recibo
                                                </a>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>

                            {payments.data.length === 0 && (
                                <div className="text-center py-12 text-gray-500">
                                    No se han registrado pagos aún.
                                </div>
                            )}

                            {/* Pagination would go here if needed, keeping it simple for now */}
                        </div>
                    </div>
                </div>
            </div>

            <Modal show={showingSelectionModal} onClose={closeSelectionModal}>
                <div className="p-6">
                    <h2 className="text-lg font-medium text-gray-900 mb-4">
                        Tipo de Pago
                    </h2>
                    <p className="text-sm text-gray-600 mb-6">
                        ¿El pago que desea realizar está asociado a un requerimiento previo?
                    </p>
                    <div className="flex flex-col gap-4">
                        <button
                            onClick={() => handleCreatePayment(true)}
                            className="w-full inline-flex items-center justify-center px-4 py-4 bg-indigo-600 border border-transparent rounded-md font-semibold text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                        >
                            <span className="text-lg">Con Requerimiento</span>
                        </button>
                        <button
                            onClick={() => handleCreatePayment(false)}
                            className="w-full inline-flex items-center justify-center px-4 py-4 bg-white border border-gray-300 rounded-md font-semibold text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150"
                        >
                            <span className="text-lg">Sin Requerimiento</span>
                        </button>
                    </div>
                    <div className="mt-6 flex justify-end">
                        <SecondaryButton onClick={closeSelectionModal}>Cancelar</SecondaryButton>
                    </div>
                </div>
            </Modal>
        </AuthenticatedLayout>
    );
}
