import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';
import { useState, useEffect } from 'react';
import PrimaryButton from '@/Components/PrimaryButton';
import InputLabel from '@/Components/InputLabel';
import TextInput from '@/Components/TextInput';
import InputError from '@/Components/InputError';
import Modal from '@/Components/Modal';
import SecondaryButton from '@/Components/SecondaryButton';

export default function Index({ auth, periodos, solicitudes, bonos = [], isSindicalizado, flash, canAccessVacations, tenureMessage }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        tipo_solicitud: 'VACACION',
        fecha_inicio: '',
        fecha_fin: '',
        motivo: '',
    });

    const [showConfirm, setShowConfirm] = useState(false);
    const [showBonusModal, setShowBonusModal] = useState(false);
    const [bonusPeriodo, setBonusPeriodo] = useState(null);
    const [selectedDiasPagados, setSelectedDiasPagados] = useState(null);
    const [bonusSubmitting, setBonusSubmitting] = useState(false);

    // Function to check if a date is a weekend
    const isWeekend = (dateString) => {
        if (!dateString) return false;
        const date = new Date(dateString + 'T00:00:00');
        const day = date.getDay();
        return day === 0 || day === 6; // 0 = Sunday, 6 = Saturday
    };

    // Handle fecha_inicio change with weekend validation
    const handleFechaInicioChange = (e) => {
        const value = e.target.value;
        if (isWeekend(value)) {
            alert('No puedes seleccionar sábado o domingo como fecha de inicio.');
            return;
        }
        setData('fecha_inicio', value);
    };

    // Handle fecha_fin change with weekend validation
    const handleFechaFinChange = (e) => {
        const value = e.target.value;
        if (isWeekend(value)) {
            alert('No puedes seleccionar sábado o domingo como fecha de fin.');
            return;
        }
        setData('fecha_fin', value);
    };

    const submit = (e) => {
        e.preventDefault();
        setShowConfirm(true);
    };

    const confirmSubmit = () => {
        post(route('vacations.store'), {
            onSuccess: () => {
                setShowConfirm(false);
                reset();
            },
            onError: () => setShowConfirm(false),
        });
    };

    const calculateDays = () => {
        if (!data.fecha_inicio || !data.fecha_fin) return 0;
        const start = new Date(data.fecha_inicio);
        const end = new Date(data.fecha_fin);

        if (end < start) return 0; // Fecha inválida

        // Diferencia en ms
        const diffTime = end - start;
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
        return diffDays;
    };

    // Check for pending bonus on mount
    useEffect(() => {
        fetch(route('evaluation.bonus.check'))
            .then(res => res.json())
            .then(data => {
                if (data.pending) {
                    setBonusPeriodo(data.periodo);
                    setShowBonusModal(true);
                }
            })
            .catch(err => console.error('Error checking bonus status:', err));
    }, []);

    const submitBonus = () => {
        if (selectedDiasPagados === null) {
            alert('Por favor seleccione una opción');
            return;
        }

        setBonusSubmitting(true);

        fetch(route('evaluation.bonus.store'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({
                dias_pagados: selectedDiasPagados,
                anio: bonusPeriodo.anio,
                cuatrimestre: bonusPeriodo.cuatrimestre,
            }),
        })
            .then(res => res.json())
            .then(data => {
                setBonusSubmitting(false);
                if (data.success) {
                    alert(data.message);
                    setShowBonusModal(false);
                    window.location.reload(); // Refresh to show updated balance
                } else {
                    alert('Error al registrar el bono');
                }
            })
            .catch(err => {
                setBonusSubmitting(false);
                console.error('Error submitting bonus:', err);
                alert('Error de conexión');
            });
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Mis Vacaciones</h2>}
        >
            <Head title="Mis Vacaciones" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                    {/* Alertas */}
                    {flash?.success && (
                        <div key="success-alert" className="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                            {flash.success}
                        </div>
                    )}
                    {flash?.error && (
                        <div key="error-alert" className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                            {flash.error}
                        </div>
                    )}

                    {/* Alerta de Antigüedad */}
                    {!canAccessVacations && (
                        <div className="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4" role="alert">
                            <p className="font-bold">Acceso Restringido</p>
                            <p>{tenureMessage}</p>
                        </div>
                    )}

                    {/* Saldos Section */}
                    <div className={`grid grid-cols-1 md:grid-cols-2 gap-4 ${!canAccessVacations ? 'opacity-50 pointer-events-none' : ''}`}>
                        {periodos.map((periodo) => (
                            <div key={periodo.id} className="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                                <h3 className="text-lg font-bold mb-4">
                                    Periodo {periodo.numero_periodo} - {periodo.anio}
                                </h3>
                                <div className="space-y-4">
                                    {periodo.saldos_desglosados.map((saldo) => (
                                        <div key={saldo.id} className="border-b pb-2">
                                            <div className="flex justify-between items-center">
                                                <span className="font-medium text-gray-700">{saldo.tipo}</span>
                                                <span className="text-sm text-gray-500">
                                                    Disponible: <span className="font-bold text-gray-900">{saldo.disponibles}</span> / {saldo.total}
                                                </span>
                                            </div>
                                            <div className="w-full bg-gray-200 rounded-full h-2.5 mt-2">
                                                <div
                                                    className="bg-blue-600 h-2.5 rounded-full"
                                                    style={{ width: `${Math.max(0, (saldo.disponibles / saldo.total) * 100)}%` }}
                                                ></div>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        ))}

                        {/* Bonos Cuatrimestrales */}
                        {bonos && bonos.length > 0 && bonos.map((bono) => (
                            <div key={bono.id} className="bg-gradient-to-br from-purple-50 to-indigo-50 overflow-hidden shadow-sm sm:rounded-lg p-6 border-2 border-purple-200">
                                <h3 className="text-lg font-bold mb-4 text-purple-900">
                                    Bono Cuatrimestre {bono.cuatrimestre} - {bono.anio}
                                </h3>
                                <div className="space-y-3">
                                    <div className="bg-white rounded-lg p-4 border border-purple-200">
                                        <div className="flex justify-between items-center mb-2">
                                            <span className="font-medium text-gray-700">
                                                Días de Descanso
                                            </span>
                                            <span className="text-sm text-gray-500">
                                                Disponible: <span className="font-bold text-purple-700">{bono.disponibles}</span> / {bono.total}
                                            </span>
                                        </div>
                                        <div className="text-xs text-gray-500 mb-2">
                                            Expira: {bono.expira}
                                        </div>
                                        <div className="w-full bg-gray-200 rounded-full h-2.5">
                                            <div
                                                className="bg-purple-600 h-2.5 rounded-full"
                                                style={{ width: `${Math.max(0, (bono.disponibles / bono.total) * 100)}%` }}
                                            ></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        ))}

                        {periodos.length === 0 && (
                            <div className="bg-white p-6 shadow-sm rounded-lg col-span-2 text-center text-gray-500">
                                No tienes periodos vacacionales activos asignados.
                            </div>
                        )}
                    </div>

                    {/* Formluario de Solicitud */}
                    <div className={`bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 ${!canAccessVacations ? 'opacity-50 pointer-events-none' : ''}`}>
                        <h3 className="text-lg font-bold mb-4">Nueva Solicitud</h3>
                        <form onSubmit={submit} className="grid grid-cols-1 gap-6 md:grid-cols-2">

                            {/* Tipo de Solicitud */}
                            <div className="col-span-2 md:col-span-1">
                                <InputLabel htmlFor="tipo" value="Tipo de Permiso" />
                                <select
                                    id="tipo"
                                    className="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full mt-1"
                                    value={data.tipo_solicitud}
                                    onChange={(e) => setData('tipo_solicitud', e.target.value)}
                                    disabled={!canAccessVacations}
                                >
                                    <option value="VACACION">Vacaciones</option>
                                    {bonos && bonos.length > 0 && bonos.some(b => b.disponibles > 0) && (
                                        <option value="BONO_CUATRIMESTRAL">Bono Cuatrimestral</option>
                                    )}
                                    {isSindicalizado && (
                                        <option value="ONOMASTICO">Permiso por Onomástico</option>
                                    )}
                                    <option value="DEFUNCION">Permiso por Defunción</option>
                                    <option value="NACIMIENTO">Permiso por Nacimiento</option>
                                </select>
                                <InputError message={errors.tipo_solicitud} className="mt-2" />
                            </div>

                            {/* Fechas */}
                            <div>
                                <InputLabel htmlFor="inicio" value="Fecha Inicio" />
                                <TextInput
                                    id="inicio"
                                    type="date"
                                    className="mt-1 block w-full"
                                    value={data.fecha_inicio}
                                    onChange={handleFechaInicioChange}
                                    disabled={!canAccessVacations}
                                />
                                <InputError message={errors.fecha_inicio} className="mt-2" />
                            </div>

                            <div>
                                <InputLabel htmlFor="fin" value="Fecha Fin" />
                                <TextInput
                                    id="fin"
                                    type="date"
                                    className="mt-1 block w-full"
                                    value={data.fecha_fin}
                                    min={data.fecha_inicio} // Restricción visual
                                    onChange={handleFechaFinChange}
                                    disabled={!canAccessVacations}
                                />
                                <InputError message={errors.fecha_fin} className="mt-2" />
                            </div>

                            {/* Motivo */}
                            <div className="col-span-2">
                                <InputLabel htmlFor="motivo" value="Motivo (Opcional)" />
                                <TextInput
                                    id="motivo"
                                    className="mt-1 block w-full"
                                    value={data.motivo}
                                    onChange={(e) => setData('motivo', e.target.value)}
                                    placeholder="Detalles adicionales..."
                                    disabled={!canAccessVacations}
                                />
                            </div>

                            <div className="col-span-2 flex items-center justify-end">
                                <span className="mr-4 text-gray-600">
                                    Días calculados: <span className="font-bold">{calculateDays()}</span>
                                </span>
                                <PrimaryButton disabled={processing || !canAccessVacations}>
                                    Solicitar
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>

                    {/* Historial */}
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 className="text-lg font-bold mb-4">Historial de Solicitudes</h3>
                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha Solicitud</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Periodo</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Días</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody className="bg-white divide-y divide-gray-200">
                                    {solicitudes.length > 0 ? solicitudes.map((solicitud) => (
                                        <tr key={solicitud.id}>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {new Date(solicitud.created_at).toLocaleDateString()}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {solicitud.tipo_solicitud}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {(() => {
                                                    // Formatear fechas a DD/MM/YYYY
                                                    const formatDate = (dateStr) => {
                                                        if (!dateStr) return '';
                                                        const cleanDate = dateStr.includes('T') ? dateStr.split('T')[0] : dateStr;
                                                        const [year, month, day] = cleanDate.split('-');
                                                        return `${day}/${month}/${year}`;
                                                    };

                                                    const fInicio = formatDate(solicitud.fecha_inicio);
                                                    const fFin = formatDate(solicitud.fecha_fin);

                                                    // Check if it's a bonus with bono_info
                                                    if (solicitud.bono_info) {
                                                        const cuatrimestreText = solicitud.bono_info.cuatrimestre === 1 ? '1er' : (solicitud.bono_info.cuatrimestre === 2 ? '2do' : '3er');
                                                        return `${cuatrimestreText} Cuatrimestre ${solicitud.bono_info.anio} del ${fInicio} al ${fFin}`;
                                                    }

                                                    // Check if it has periodo_info
                                                    if (solicitud.periodo_info) {
                                                        return `Periodo ${solicitud.periodo_info.numero_periodo} - ${solicitud.periodo_info.anio} del ${fInicio} al ${fFin}`;
                                                    }

                                                    return `Del ${fInicio} al ${fFin}`;
                                                })()}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {solicitud.dias_solicitados}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <span className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                    ${solicitud.estado === 'APROBADA' ? 'bg-green-100 text-green-800' :
                                                        solicitud.estado === 'RECHAZADA' ? 'bg-red-100 text-red-800' :
                                                            'bg-yellow-100 text-yellow-800'}`}>
                                                    {solicitud.estado}
                                                </span>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <a
                                                    href={route('vacations.pdf.request', solicitud.id)}
                                                    target="_blank"
                                                    className="text-indigo-600 hover:text-indigo-900 flex items-center"
                                                >
                                                    <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                    </svg>
                                                    Descargar
                                                </a>
                                            </td>
                                        </tr>
                                    )) : (
                                        <tr>
                                            <td colSpan="5" className="px-6 py-4 text-center text-sm text-gray-500">
                                                No hay solicitudes registradas.
                                            </td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>

            {/* Confirmation Modal */}
            <Modal show={showConfirm} onClose={() => setShowConfirm(false)}>
                <div className="p-6">
                    <h2 className="text-lg font-medium text-gray-900">Confirmar Solicitud</h2>
                    <p className="mt-1 text-sm text-gray-600">
                        Estás a punto de solicitar <b>{calculateDays()}</b> días.
                        {data.tipo_solicitud === 'VACACION' && " Estos se descontarán de tus saldos disponibles (Ordinario > Antigüedad > Sindicato)."}
                    </p>
                    <div className="mt-6 flex justify-end">
                        <SecondaryButton onClick={() => setShowConfirm(false)}>Cancelar</SecondaryButton>
                        <PrimaryButton className="ml-3" onClick={confirmSubmit} disabled={processing}>Confirmar</PrimaryButton>
                    </div>
                </div>
            </Modal>

            {/* Bonus Evaluation Modal */}
            <Modal show={showBonusModal} onClose={() => { }} maxWidth="2xl">
                <div className="p-6">
                    <h2 className="text-lg font-bold text-gray-900 mb-4">Declaración de Incentivo Cuatrimestral</h2>
                    {bonusPeriodo && (
                        <p className="text-sm text-gray-600 mb-4">
                            Periodo: <strong>{bonusPeriodo.periodo_nombre}</strong>
                        </p>
                    )}
                    <p className="text-sm text-gray-700 mb-6">
                        ¿Cuántos días de incentivo se te pagaron en este periodo?
                    </p>

                    <div className="grid grid-cols-2 gap-4 mb-6">
                        {[
                            { dias: 0, label: '0 Días', descanso: '0 días libres' },
                            { dias: 5, label: '5 Días', descanso: '1 día libre' },
                            { dias: 10, label: '10 Días', descanso: '2 días libres' },
                            { dias: 15, label: '15 Días', descanso: '3 días libres' },
                        ].map(option => (
                            <button
                                key={option.dias}
                                onClick={() => setSelectedDiasPagados(option.dias)}
                                className={`p-4 border-2 rounded-lg text-center transition ${selectedDiasPagados === option.dias
                                    ? 'border-indigo-600 bg-indigo-50'
                                    : 'border-gray-300 hover:border-indigo-400'
                                    }`}
                            >
                                <div className="font-bold text-lg">{option.label}</div>
                                <div className="text-sm text-gray-600">({option.descanso})</div>
                            </button>
                        ))}

                        {/* Bonos Cuatrimestrales Section */}
                        {bonos.length > 0 && (
                            <div className="bg-gradient-to-br from-purple-50 to-indigo-50 overflow-hidden shadow-sm sm:rounded-lg p-6 border-2 border-purple-200">
                                <h3 className="text-lg font-bold mb-4 text-purple-900">
                                    Bonos Cuatrimestrales
                                </h3>
                                <div className="space-y-3">
                                    {bonos.map((bono) => (
                                        <div key={bono.id} className="bg-white rounded-lg p-4 border border-purple-200">
                                            <div className="flex justify-between items-center mb-2">
                                                <span className="font-medium text-gray-700">
                                                    Cuatrimestre {bono.cuatrimestre} - {bono.anio}
                                                </span>
                                                <span className="text-sm text-gray-500">
                                                    Disponible: <span className="font-bold text-purple-700">{bono.disponibles}</span> / {bono.total}
                                                </span>
                                            </div>
                                            <div className="text-xs text-gray-500">
                                                Expira: {bono.expira}
                                            </div>
                                            <div className="w-full bg-gray-200 rounded-full h-2.5 mt-2">
                                                <div
                                                    className="bg-purple-600 h-2.5 rounded-full"
                                                    style={{ width: `${Math.max(0, (bono.disponibles / bono.total) * 100)}%` }}
                                                ></div>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        )}
                    </div>

                    <div className="flex justify-end gap-2">
                        <SecondaryButton onClick={() => setShowBonusModal(false)}>
                            Más tarde
                        </SecondaryButton>
                        <PrimaryButton onClick={submitBonus} disabled={bonusSubmitting || selectedDiasPagados === null}>
                            {bonusSubmitting ? 'Guardando...' : 'Confirmar'}
                        </PrimaryButton>
                    </div>
                </div>
            </Modal>

        </AuthenticatedLayout>
    );
}

