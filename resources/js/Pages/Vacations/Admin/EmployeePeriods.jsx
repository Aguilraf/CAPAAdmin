import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router, usePage } from '@inertiajs/react';
import DangerButton from '@/Components/DangerButton';
import SecondaryButton from '@/Components/SecondaryButton';
import { useState } from 'react';
import Modal from '@/Components/Modal';

export default function EmployeePeriods({ auth, empleado, periodos }) {
    const { flash } = usePage().props;
    const [confirmingDeletion, setConfirmingDeletion] = useState(false);
    const [selectedPeriod, setSelectedPeriod] = useState(null);

    const checkDelete = (periodo) => {
        setSelectedPeriod(periodo);
        setConfirmingDeletion(true);
    };

    const confirmDelete = () => {
        router.delete(route('vacations.admin.periods.destroy', selectedPeriod.id), {
            onSuccess: () => setConfirmingDeletion(false),
        });
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Gestión de Periodos: {empleado.nombre}</h2>}
        >
            <Head title={`Periodos - ${empleado.nombre}`} />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
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
                        <h3 className="text-lg font-bold mb-4">Periodos Vacacionales Registrados</h3>

                        <div className="grid gap-4">
                            {periodos.length === 0 ? (
                                <p className="text-gray-500">No hay periodos registrados.</p>
                            ) : (
                                periodos.map((periodo) => (
                                    <div key={periodo.id} className="border rounded-lg p-4 flex flex-col md:flex-row justify-between items-center bg-gray-50">
                                        <div>
                                            <h4 className="font-bold text-lg text-gray-800">
                                                {periodo.anio} - Periodo {periodo.numero_periodo}
                                            </h4>
                                            <div className="text-sm text-gray-600 mt-1">
                                                <span className="mr-4">Días Totales: <b>{periodo.total_dias}</b></span>
                                                <span className={`${periodo.can_delete ? 'text-green-600' : 'text-red-600'} font-bold`}>
                                                    Usados: {periodo.dias_usados}
                                                </span>
                                            </div>
                                            <div className="mt-2 text-xs text-gray-500">
                                                {periodo.saldos.map(s => (
                                                    <span key={s.id} className="mr-2 px-2 py-1 bg-white border rounded">
                                                        {s.tipo}: {s.dias_disponibles}/{s.total_dias}
                                                    </span>
                                                ))}
                                            </div>
                                        </div>

                                        <div className="mt-4 md:mt-0">
                                            {periodo.can_delete ? (
                                                <DangerButton onClick={() => checkDelete(periodo)}>
                                                    Cancelar Periodo
                                                </DangerButton>
                                            ) : (
                                                <span className="text-xs text-red-500 font-bold border border-red-200 bg-red-50 px-2 py-1 rounded">
                                                    No cancelable (días usados)
                                                </span>
                                            )}
                                        </div>
                                    </div>
                                ))
                            )}
                        </div>
                    </div>
                </div>
            </div>

            <Modal show={confirmingDeletion} onClose={() => setConfirmingDeletion(false)}>
                <div className="p-6">
                    <h2 className="text-lg font-medium text-gray-900">
                        ¿Estás seguro de que quieres cancelar este periodo?
                    </h2>
                    <p className="mt-1 text-sm text-gray-600">
                        Esta acción eliminará el periodo y todos sus saldos asociados. Esta acción no se puede deshacer.
                    </p>
                    <div className="mt-6 flex justify-end">
                        <SecondaryButton onClick={() => setConfirmingDeletion(false)}>
                            Cancelar
                        </SecondaryButton>
                        <DangerButton className="ms-3" onClick={confirmDelete}>
                            Eliminar Periodo
                        </DangerButton>
                    </div>
                </div>
            </Modal>
        </AuthenticatedLayout>
    );
}
