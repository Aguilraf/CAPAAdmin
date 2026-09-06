import { Fragment, useState, useEffect } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm, usePage } from '@inertiajs/react';
import PrimaryButton from '@/Components/PrimaryButton';
import axios from 'axios';

export default function Create({ nextDate }) {
    const { errors } = usePage().props;
    const { data, setData, post, processing } = useForm({
        date: nextDate || '',
        movements: [],
        dni_movements: [],
        has_draef: false,
        draef_subtotal: '',
        draef_iva: ''
    });

    const [movements, setMovements] = useState([]);
    const [loading, setLoading] = useState(false);
    const [dniMovements, setDniMovements] = useState([]);
    const [dniLoading, setDniLoading] = useState(false);
    const [showDniPanel, setShowDniPanel] = useState(false);

    const fetchDniMovements = async () => {
        setDniLoading(true);
        try {
            const response = await axios.get(route('daily-incomes.dni-movements'));
            setDniMovements(response.data);
        } finally {
            setDniLoading(false);
        }
    };

    const toggleDniPanel = () => {
        const next = !showDniPanel;
        setShowDniPanel(next);
        if (next && dniMovements.length === 0) {
            fetchDniMovements();
        }
    };

    const toggleDniMovement = (id) => {
        const current = data.dni_movements;
        if (current.includes(id)) {
            setData('dni_movements', current.filter(m => m !== id));
        } else {
            setData('dni_movements', [...current, id]);
        }
    };

    const fetchMovements = async (date) => {
        setLoading(true);
        try {
            const response = await axios.get(route('daily-incomes.movements'), { params: { date } });
            setMovements(response.data);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        if (!data.date) {
            setMovements([]);
            return;
        }

        fetchMovements(data.date);
    }, [data.date]);

    const toggleMovement = (id) => {
        const current = data.movements;
        if (current.includes(id)) {
            setData('movements', current.filter(m => m !== id));
        } else {
            setData('movements', [...current, id]);
        }
    };

    const handleDateChange = (event) => {
        const nextDate = event.target.value;

        if (!nextDate || nextDate === data.date) {
            setData('date', nextDate);
            return;
        }

        if (data.movements.length > 0) {
            const action = window.prompt(
                'Ya tienes movimientos seleccionados.\n\nEscribe una opción:\n1 = Guardar y cambiar de fecha\n2 = Desechar cambios y cambiar de fecha\n3 = Cancelar',
                '3'
            );

            if (action === '1') {
                post(route('daily-incomes.store'));
                return;
            }

            if (action === '2') {
                setData('movements', []);
            } else {
                return;
            }
        }

        setData('date', nextDate);
    };

    const selectedMovements = movements.filter(m => data.movements.includes(m.id));
    const movementsAmount = selectedMovements.reduce((sum, m) => sum + parseFloat(m.credit_amount), 0);
    const selectedDniMovements = dniMovements.filter(m => data.dni_movements.includes(m.id));
    const dniMovementsAmount = selectedDniMovements.reduce((sum, m) => sum + parseFloat(m.credit_amount), 0);
    const draefSubtotal = data.has_draef ? parseFloat(data.draef_subtotal || 0) : 0;
    const draefIva = data.has_draef ? parseFloat(data.draef_iva || 0) : 0;
    const draefAmount = draefSubtotal + draefIva;
    const totalAmount = movementsAmount;
    const totalGeneral = movementsAmount + dniMovementsAmount + draefAmount;

    const handleDraefSubtotalChange = (value) => {
        const iva = Number(parseFloat(value || 0) * 0.16).toFixed(2);
        setData((current) => ({ ...current, draef_subtotal: value, draef_iva: iva }));
    };

    const formatMoney = (value) => Number(parseFloat(value || 0)).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });

    const formatDate = (dateString) => {
        if (!dateString) return '';
        const [year, month, day] = String(dateString).slice(0, 10).split('-');
        if (year && month && day && year.length === 4) {
            return `${day}/${month}/${year}`;
        }
        const date = new Date(dateString);
        if (Number.isNaN(date.getTime())) return dateString;
        return date.toLocaleDateString('es-MX');
    };

    const submit = (e) => {
        e.preventDefault();
        post(route('daily-incomes.store'));
    };

    return (
        <AuthenticatedLayout header={<h2 className="text-xl font-semibold">Registrar Ingreso Diario</h2>}>
            <Head title="Registrar Ingreso" />
            <div className="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8">
                <form onSubmit={submit} className="bg-white p-6 shadow sm:rounded-lg">
                    <div className="mb-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label className="block text-sm font-medium text-gray-700">Día de Ingreso</label>
                            <input type="date" value={data.date} onChange={handleDateChange} className="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
                            {errors.date && <p className="mt-2 text-sm text-red-600">{errors.date}</p>}
                        </div>
                        <div className="flex items-end">
                            <div className="flex flex-wrap items-center gap-4">
                                <div className="text-lg font-bold">
                                {data.date ? `Total día: $${formatMoney(totalAmount)} (${data.movements.length} movimientos)` : 'Total día: $0.00 (0 movimientos)'}
                                </div>
                                <div className="text-lg font-bold text-amber-700">
                                {data.date ? `Total general: $${formatMoney(totalGeneral)} (${data.movements.length + data.dni_movements.length} movimientos)` : 'Total general: $0.00 (0 movimientos)'}
                                </div>
                                <PrimaryButton type="submit" disabled={processing || !data.date || (data.movements.length === 0 && data.dni_movements.length === 0)}>
                                    Guardar Ingreso
                                </PrimaryButton>
                                <button
                                    type="button"
                                    onClick={toggleDniPanel}
                                    className="inline-flex items-center rounded-md border border-amber-400 bg-amber-50 px-3 py-2 text-sm font-semibold text-amber-700 hover:bg-amber-100"
                                >
                                    Pagos No Identificados{data.dni_movements.length > 0 ? ` (${data.dni_movements.length})` : ''}
                                </button>
                            </div>
                        </div>
                    </div>

                    {showDniPanel && (
                        <div className="mb-6 rounded-lg border border-amber-200 bg-amber-50 p-4">
                            <div className="flex items-center justify-between">
                                <div>
                                    <h3 className="font-semibold text-amber-800">Pagos No Identificados (DNI)</h3>
                                    <p className="text-xs text-amber-700">Movimientos ya asociados a una póliza DNI. Al capturarlos aquí se marcan como usados y se agregan a esta cobranza de forma informativa (no afectan la conciliación contra facturas).</p>
                                </div>
                                <div className="text-right text-sm font-semibold text-amber-800">Seleccionado: ${formatMoney(dniMovementsAmount)}</div>
                            </div>

                            {dniLoading ? (
                                <p className="mt-3 text-sm text-amber-700">Cargando movimientos...</p>
                            ) : (
                                <div className="mt-3 max-h-64 overflow-y-auto rounded-md border border-amber-200 bg-white">
                                    <table className="min-w-full divide-y divide-amber-100 text-sm">
                                        <thead className="bg-amber-100 text-left text-xs uppercase text-amber-700">
                                            <tr>
                                                <th className="px-3 py-2">Sel.</th>
                                                <th className="px-3 py-2">Fecha</th>
                                                <th className="px-3 py-2">Banco</th>
                                                <th className="px-3 py-2">Movimiento</th>
                                                <th className="px-3 py-2">Concepto</th>
                                                <th className="px-3 py-2 text-right">Importe</th>
                                            </tr>
                                        </thead>
                                        <tbody className="divide-y divide-amber-50">
                                            {dniMovements.length === 0 && (
                                                <tr><td colSpan="6" className="px-3 py-6 text-center text-slate-400">No hay pagos no identificados disponibles.</td></tr>
                                            )}
                                            {dniMovements.map((m) => (
                                                <tr key={m.id} onClick={() => toggleDniMovement(m.id)} className={`cursor-pointer ${data.dni_movements.includes(m.id) ? 'bg-amber-100' : 'hover:bg-amber-50'}`}>
                                                    <td className="px-3 py-2"><input type="checkbox" checked={data.dni_movements.includes(m.id)} onChange={() => toggleDniMovement(m.id)} onClick={(e) => e.stopPropagation()} /></td>
                                                    <td className="px-3 py-2 whitespace-nowrap">{formatDate(m.operation_date)}</td>
                                                    <td className="px-3 py-2 whitespace-nowrap">{m.bank?.name}</td>
                                                    <td className="px-3 py-2 whitespace-nowrap">{m.movement_number || m.reference || '-'}</td>
                                                    <td className="px-3 py-2">{m.description}</td>
                                                    <td className="px-3 py-2 text-right whitespace-nowrap">${formatMoney(m.credit_amount)}</td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            )}
                            {errors.dni_movements && <p className="mt-2 text-sm text-red-600">{errors.dni_movements}</p>}
                        </div>
                    )}

                    <div className="mb-6 rounded-lg border border-slate-200 bg-slate-50 p-4">
                        <label className="flex items-center gap-2 text-sm font-medium text-slate-700">
                            <input
                                type="checkbox"
                                checked={data.has_draef}
                                onChange={(event) => setData('has_draef', event.target.checked)}
                            />
                            Tiene DRAEF
                        </label>
                        {data.has_draef && (
                            <div className="mt-3 grid gap-4 sm:grid-cols-3">
                                <div>
                                    <label className="block text-sm font-medium text-slate-700">Facturación (subtotal)</label>
                                    <input
                                        type="number"
                                        min="0.01"
                                        step="0.01"
                                        value={data.draef_subtotal}
                                        onChange={(event) => handleDraefSubtotalChange(event.target.value)}
                                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                    />
                                    {errors.draef_subtotal && <p className="mt-2 text-sm text-red-600">{errors.draef_subtotal}</p>}
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-slate-700">IVA</label>
                                    <input
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        value={data.draef_iva}
                                        onChange={(event) => setData('draef_iva', event.target.value)}
                                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                    />
                                    {errors.draef_iva && <p className="mt-2 text-sm text-red-600">{errors.draef_iva}</p>}
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-slate-700">Total DRAEF</label>
                                    <input type="text" disabled value={`$${formatMoney(draefAmount)}`} className="mt-1 block w-full rounded-md border-gray-200 bg-slate-100 shadow-sm" />
                                </div>
                            </div>
                        )}
                    </div>

                    {!data.date ? (
                        <div className="flex min-h-[180px] items-center justify-center rounded-lg border border-dashed border-slate-300 bg-slate-50 text-sm text-slate-500">
                            Selecciona una fecha para cargar los movimientos disponibles.
                        </div>
                    ) : (
                    <table className="min-w-full divide-y divide-gray-200">
                        <thead className="bg-gray-50">
                            <tr>
                                <th className="px-4 py-2">Select</th>
                                <th className="px-4 py-2">Fecha</th>
                                <th className="px-4 py-2">Banco</th>
                                <th className="px-4 py-2"># Movimiento</th>
                                <th className="px-4 py-2">Movimiento / Concepto</th>
                                <th className="px-4 py-2">Importe</th>
                            </tr>
                        </thead>
                        <tbody>
                            {movements.map((m, index) => {
                                const isBbva = m.bank?.name?.toUpperCase().includes('BBVA');
                                const hasReference = m.reference && m.reference.toString().trim() !== '';
                                const movementNumber = isBbva && hasReference
                                    ? m.reference
                                    : m.movement_number || m.reference || '-';
                                const conceptLabel = !isBbva && hasReference && m.description?.toUpperCase().includes('DEPOSITO EN EFECTIVO')
                                        ? `${m.description} / ${m.reference}`
                                        : m.description;

                                const isSelected = data.movements.includes(m.id);

                                const previousBank = movements[index - 1]?.bank?.name;
                                const showBankHeader = m.bank?.name !== previousBank;

                                return (
                                    <Fragment key={m.id}>
                                        {showBankHeader && (
                                            <tr className="bg-slate-200">
                                                <td colSpan="6" className="px-4 py-2 text-sm font-bold uppercase tracking-wide text-slate-700">
                                                    {m.bank?.name || 'Banco'}
                                                </td>
                                            </tr>
                                        )}
                                        <tr
                                            onClick={() => toggleMovement(m.id)}
                                            className={`cursor-pointer ${isSelected ? 'bg-blue-100' : 'hover:bg-gray-50'}`}
                                        >
                                            <td className="px-4 py-2"><input type="checkbox" checked={isSelected} onChange={() => toggleMovement(m.id)} onClick={(event) => event.stopPropagation()} /></td>
                                            <td className="px-4 py-2">{formatDate(m.operation_date)}</td>
                                            <td className="px-4 py-2">{m.bank.name}</td>
                                            <td className="px-4 py-2">{movementNumber}</td>
                                            <td className="px-4 py-2">{conceptLabel}</td>
                                            <td className="px-4 py-2">${formatMoney(m.credit_amount)}</td>
                                        </tr>
                                    </Fragment>
                                );
                            })}
                        </tbody>
                    </table>
                    )}

                    {errors.movements && <p className="mt-3 text-sm text-red-600">{errors.movements}</p>}
                    <PrimaryButton type="submit" className="mt-4" disabled={processing || !data.date || (data.movements.length === 0 && data.dni_movements.length === 0)}>Guardar Ingreso</PrimaryButton>
                </form>
            </div>
        </AuthenticatedLayout>
    );
}
