import { Head, router, usePage } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import PrimaryButton from '@/Components/PrimaryButton';
import { useMemo, useState } from 'react';

const money = new Intl.NumberFormat('es-MX', {
    style: 'currency',
    currency: 'MXN',
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
});

const formatDate = (value) => {
    if (!value) return '';
    const [year, month, day] = String(value).slice(0, 10).split('-');
    if (year && month && day && year.length === 4) {
        return `${day}/${month}/${year}`;
    }
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return value;
    return `${String(date.getDate()).padStart(2, '0')}/${String(date.getMonth() + 1).padStart(2, '0')}/${date.getFullYear()}`;
};

const formatMoney = (value) => money.format(Number(value || 0));

export default function Index({ dailyIncomes }) {
    const { flash } = usePage().props;
    const [search, setSearch] = useState('');
    const [selectedDate, setSelectedDate] = useState('');
    const [expandedIncomeIds, setExpandedIncomeIds] = useState([]);

    const totalGeneral = useMemo(
        () => dailyIncomes.reduce((sum, income) => sum + Number(income.total_amount || 0), 0),
        [dailyIncomes]
    );

    const filteredIncomes = useMemo(() => {
        return dailyIncomes.filter((income) => {
            const matchesDate = !selectedDate || income.income_date === selectedDate;
            const text = [
                income.income_date,
                income.total_amount,
                ...income.details.map((detail) => `${detail.movement?.description || ''} ${detail.movement?.reference || ''} ${detail.movement?.movement_number || ''}`),
            ]
                .join(' ')
                .toLowerCase();

            const matchesSearch = !search.trim() || text.includes(search.toLowerCase());
            return matchesDate && matchesSearch;
        });
    }, [dailyIncomes, search, selectedDate]);

    const deleteIncome = (incomeId) => {
        if (!confirm('¿Deseas eliminar esta cobranza del día? Esta acción quitará la relación y dejará libre cada movimiento asociado.')) {
            return;
        }

        router.delete(route('daily-incomes.destroy', incomeId), {
            preserveScroll: true,
        });
    };

    const removeMovement = (incomeId, detailId) => {
        if (!confirm('¿Deseas quitar este movimiento de la cobranza del día? Se liberará y quedará disponible para otra fecha.')) {
            return;
        }

        router.delete(route('daily-incomes.details.destroy', { dailyIncome: incomeId, dailyIncomeDetail: detailId }), {
            preserveScroll: true,
        });
    };

    const toggleIncome = (incomeId) => {
        setExpandedIncomeIds((current) =>
            current.includes(incomeId)
                ? current.filter((id) => id !== incomeId)
                : [...current, incomeId]
        );
    };

    return (
        <AuthenticatedLayout header={<h2 className="text-xl font-semibold text-gray-800">Cobranza del día</h2>}>
            <Head title="Cobranza del día" />

            <div className="py-8">
                <div className="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                    <div className="rounded-xl border border-slate-200 bg-white shadow-sm">
                        <div className="flex flex-col gap-4 border-b border-slate-200 bg-slate-50 px-6 py-4 md:flex-row md:items-center md:justify-between">
                            <div>
                                <h3 className="text-lg font-semibold text-slate-800">Ingresos del día</h3>
                                <p className="text-sm text-slate-500">Consulta, elimina movimientos o borra la cobranza completa si ya no aplicará.</p>
                            </div>

                            <div className="flex items-center gap-3">
                                <a
                                    href={route('daily-incomes.create')}
                                    className="inline-flex items-center justify-center rounded-lg bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700"
                                >
                                    Nueva cobranza
                                </a>
                            </div>
                        </div>

                        {flash?.success && (
                            <div className="mx-6 mt-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                                {flash.success}
                            </div>
                        )}

                        <div className="px-6 py-5">
                            <div className="mb-5 grid gap-4 md:grid-cols-[1fr_1fr_auto] md:items-end">
                                <div>
                                    <label className="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Filtrar por fecha</label>
                                    <input
                                        type="date"
                                        value={selectedDate}
                                        onChange={(event) => setSelectedDate(event.target.value)}
                                        className="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-slate-500 focus:outline-none"
                                    />
                                </div>

                                <div>
                                    <label className="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Buscar</label>
                                    <input
                                        type="text"
                                        value={search}
                                        onChange={(event) => setSearch(event.target.value)}
                                        placeholder="Concepto, referencia..."
                                        className="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-slate-500 focus:outline-none"
                                    />
                                </div>

                                <div className="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-right">
                                    <div className="text-[11px] font-semibold uppercase tracking-wide text-emerald-700">Total general</div>
                                    <div className="text-lg font-bold text-emerald-800">{formatMoney(totalGeneral)}</div>
                                </div>
                            </div>

                            <div className="overflow-x-auto">
                                <table className="min-w-full border-separate border-spacing-0 text-left">
                                    <thead>
                                        <tr className="bg-slate-100 text-sm font-semibold text-slate-700">
                                            <th className="border-b border-slate-200 px-4 py-3">Fecha</th>
                                            <th className="border-b border-slate-200 px-4 py-3">Monto</th>
                                            <th className="border-b border-slate-200 px-4 py-3">Movimientos</th>
                                            <th className="border-b border-slate-200 px-4 py-3 text-right">Acciones</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        {filteredIncomes.length === 0 ? (
                                            <tr>
                                                <td colSpan="4" className="px-4 py-10 text-center text-sm text-slate-500">
                                                    No hay cobros del día registrados.
                                                </td>
                                            </tr>
                                        ) : (
                                            filteredIncomes.map((income) => {
                                                const isExpanded = expandedIncomeIds.includes(income.id);

                                                return (
                                                    <>
                                                        <tr key={income.id} className="align-top text-sm text-slate-700">
                                                            <td className="border-b border-slate-200 px-4 py-4 font-medium">
                                                                {formatDate(income.income_date)}
                                                            </td>
                                                            <td className="border-b border-slate-200 px-4 py-4 font-semibold text-emerald-700">
                                                                {formatMoney(income.total_amount)}
                                                            </td>
                                                            <td className="border-b border-slate-200 px-4 py-4">
                                                                <div className="flex items-center gap-2 text-slate-600">
                                                                    <span className="rounded-full bg-slate-200 px-2 py-1 text-xs font-semibold text-slate-700">
                                                                        {income.details.length}
                                                                    </span>
                                                                    <span>{income.details.length === 1 ? 'movimiento' : 'movimientos'}</span>
                                                                    {Number(income.draef_amount || 0) > 0 && (
                                                                        <span className="text-xs text-amber-700">+ DRAEF {formatMoney(income.draef_amount)}</span>
                                                                    )}
                                                                </div>
                                                            </td>
                                                            <td className="border-b border-slate-200 px-4 py-4">
                                                                <div className="flex items-center justify-end gap-2">
                                                                    <button
                                                                        type="button"
                                                                        onClick={() => toggleIncome(income.id)}
                                                                        className="rounded-md border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-100"
                                                                    >
                                                                        {isExpanded ? 'Ocultar detalle' : 'Ver detalle'}
                                                                    </button>
                                                                    <button
                                                                        type="button"
                                                                        onClick={() => deleteIncome(income.id)}
                                                                        className="rounded-md border border-red-200 bg-red-50 px-3 py-2 text-xs font-semibold text-red-700 hover:bg-red-100"
                                                                    >
                                                                        Eliminar
                                                                    </button>
                                                                </div>
                                                            </td>
                                                        </tr>

                                                        {isExpanded && (
                                                            <tr key={`${income.id}-details`} className="bg-slate-50">
                                                                <td colSpan="4" className="px-4 py-4">
                                                                    <div className="space-y-2 rounded-lg border border-slate-200 bg-white p-3">
                                                                        {income.details.length === 0 && Number(income.draef_amount || 0) === 0 ? (
                                                                            <div className="text-sm text-slate-500">Sin movimientos asociados.</div>
                                                                        ) : (
                                                                            <>
                                                                                {income.draef_amount > 0 && (
                                                                                    <div className="flex flex-col gap-3 rounded-md border border-amber-200 bg-amber-50 p-3 md:flex-row md:items-center md:justify-between">
                                                                                        <div className="space-y-1 text-xs text-amber-800">
                                                                                            <div className="font-semibold">DRAEF</div>
                                                                                            <div>Importe respaldado por DRAEF</div>
                                                                                        </div>
                                                                                        <div className="text-right">
                                                                                            <div className="text-[10px] font-semibold uppercase tracking-wide text-amber-700">Importe DRAEF</div>
                                                                                            <div className="font-semibold text-amber-800">{formatMoney(income.draef_amount)}</div>
                                                                                        </div>
                                                                                    </div>
                                                                                )}
                                                                                {income.details.map((detail) => (
                                                                                    <div key={detail.id} className="flex flex-col gap-3 rounded-md border border-slate-200 bg-slate-50 p-3 md:flex-row md:items-center md:justify-between">
                                                                                        <div className="space-y-1 text-xs text-slate-600">
                                                                                            <div className="font-semibold text-slate-800">
                                                                                                {detail.movement?.bank?.name || 'Banco'}
                                                                                            </div>
                                                                                            <div>{detail.movement?.description || 'Sin concepto'}</div>
                                                                                            <div>
                                                                                                {detail.movement?.bank?.name?.toUpperCase().includes('BBVA') && detail.movement?.reference
                                                                                                    ? `Contrato: ${detail.movement.reference}`
                                                                                                    : detail.movement?.reference || detail.movement?.movement_number || 'Sin referencia'}
                                                                                            </div>
                                                                                        </div>

                                                                                        <div className="flex items-center gap-3">
                                                                                            <div className="text-right">
                                                                                                <div className="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Importe</div>
                                                                                                <div className="font-semibold text-emerald-700">{formatMoney(detail.movement?.credit_amount || 0)}</div>
                                                                                            </div>
                                                                                            <button
                                                                                                type="button"
                                                                                                onClick={() => removeMovement(income.id, detail.id)}
                                                                                                className="rounded-md border border-red-200 bg-red-50 px-2 py-1 text-[11px] font-medium text-red-700 hover:bg-red-100"
                                                                                            >
                                                                                                Quitar
                                                                                            </button>
                                                                                        </div>
                                                                                    </div>
                                                                                ))}
                                                                            </>
                                                                        )}
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        )}
                                                    </>
                                                );
                                            })
                                        )}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
