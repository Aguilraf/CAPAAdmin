import { useState } from 'react';
import { router } from '@inertiajs/react';
import { Eye, X } from 'lucide-react';

const money = new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' });

const invoiceTotal = (income) => (income.invoices || []).reduce((sum, invoice) => sum + Number(invoice.total || 0), 0);
const reconciliationTotal = (income) => Number(income.total_amount || 0) - Number(income.draef_amount || 0);

export default function DailyIncomeDetails({ dailyIncomes, policy }) {
    const [selectedIncome, setSelectedIncome] = useState(null);

    const unlinkPolicy = (income) => {
        if (!window.confirm(`¿Quitar la relación de la póliza con la cobranza del ${income.income_date}? La cobranza y sus movimientos se conservarán.`)) {
            return;
        }

        router.delete(route('reconciliation.unlink-policy', income.id), {
            data: { policy_id: policy.id },
            preserveScroll: true,
            onSuccess: () => setSelectedIncome(null),
        });
    };

    return (
        <>
            <div className="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                <div className="px-6 py-4 border-b border-slate-200">
                    <h3 className="text-sm font-bold text-slate-800">Resumen de cobranzas</h3>
                    <p className="text-xs text-slate-500 mt-1">Los dias con facturas guardadas ya no aparecen para nueva conciliacion.</p>
                    {policy && <div className="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-3">
                        <div><div className="text-xs font-bold uppercase text-slate-400">Total póliza</div><div className="text-base font-extrabold text-slate-800">{money.format(policy.amount)}</div></div>
                        <div><div className="text-xs font-bold uppercase text-slate-400">Total cobranza</div><div className="text-base font-extrabold text-orange-600">{money.format(dailyIncomes.reduce((sum, income) => sum + Number(income.total_amount || 0), 0))}</div></div>
                        <div><div className="text-xs font-bold uppercase text-slate-400">Total facturas</div><div className="text-base font-extrabold text-green-700">{money.format(dailyIncomes.reduce((sum, income) => sum + (income.invoices || []).reduce((invoiceSum, invoice) => invoiceSum + Number(invoice.total || 0), 0), 0))}</div></div>
                    </div>}
                </div>
                <div className="divide-y divide-slate-100">
                    {dailyIncomes.length === 0 && <p className="px-6 py-5 text-sm text-slate-500">Selecciona una poliza para consultar sus cobranzas.</p>}
                    {dailyIncomes.map((income) => {
                        const invoicesTotal = invoiceTotal(income);
                        return (
                            <div key={income.id} className="px-6 py-4 flex flex-wrap items-center gap-5 justify-between">
                                <div className="font-semibold text-slate-700 text-sm">{income.income_date}</div>
                                <div className="text-xs text-slate-500">Total a conciliar <strong className="text-orange-600 text-sm">{money.format(reconciliationTotal(income))}</strong></div>
                                <div className="text-xs text-slate-500">Total facturas <strong className="text-green-700 text-sm">{money.format(invoicesTotal)}</strong></div>
                                <button type="button" onClick={() => setSelectedIncome(income)} className="inline-flex items-center gap-2 rounded-md border border-blue-200 bg-blue-50 px-3 py-2 text-xs font-bold text-blue-700 hover:bg-blue-100">
                                    <Eye className="w-4 h-4" /> Ver detalle
                                </button>
                                {policy && <button type="button" onClick={() => unlinkPolicy(income)} className="inline-flex items-center gap-2 rounded-md border border-red-200 bg-red-50 px-3 py-2 text-xs font-bold text-red-700 hover:bg-red-100">
                                    Quitar relación
                                </button>}
                            </div>
                        );
                    })}
                </div>
            </div>

            {selectedIncome && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 p-4" role="dialog" aria-modal="true">
                    <div className="w-full max-w-5xl max-h-[90vh] overflow-y-auto rounded-xl bg-white shadow-xl">
                        <div className="sticky top-0 z-10 flex items-center justify-between border-b border-slate-200 bg-white px-6 py-4">
                            <div>
                                <h3 className="font-bold text-slate-800">Detalle de cobranza: {selectedIncome.income_date}</h3>
                                <div className="flex flex-wrap gap-x-5 gap-y-1 text-xs text-slate-500">
                                    <span>Total a conciliar <strong className="text-orange-600">{money.format(reconciliationTotal(selectedIncome))}</strong></span>
                                    <span>Facturas <strong className="text-green-700">{money.format(invoiceTotal(selectedIncome))}</strong></span>
                                    <span>Diferencia <strong className={reconciliationTotal(selectedIncome) - invoiceTotal(selectedIncome) >= 0 ? 'text-blue-700' : 'text-red-600'}>{money.format(reconciliationTotal(selectedIncome) - invoiceTotal(selectedIncome))}</strong></span>
                                </div>
                            </div>
                            <button type="button" onClick={() => setSelectedIncome(null)} aria-label="Cerrar detalle" className="rounded-md p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-800"><X className="w-5 h-5" /></button>
                        </div>
                        <div className="grid gap-6 p-6 lg:grid-cols-2">
                            <section>
                                <h4 className="mb-3 text-sm font-bold text-slate-800">Facturas ({selectedIncome.invoices?.length || 0})</h4>
                                <div className="max-h-80 overflow-y-auto rounded-lg border border-slate-200 divide-y divide-slate-100">
                                    {[...(selectedIncome.invoices || [])].sort((left, right) => {
                                        const leftNumber = left.numero_factura || '';
                                        const rightNumber = right.numero_factura || '';
                                        if (!leftNumber && rightNumber) return 1;
                                        if (leftNumber && !rightNumber) return -1;
                                        return leftNumber.localeCompare(rightNumber, 'es', { numeric: true, sensitivity: 'base' });
                                    }).map((invoice) => <div key={invoice.id} className="flex justify-between gap-3 px-3 py-3 text-sm"><div><div className="font-semibold text-slate-700">{invoice.numero_factura || invoice.uuid}</div><div className="text-xs text-slate-400">{invoice.fecha}</div></div><strong className="text-green-700">{money.format(invoice.total)}</strong></div>)}
                                    {!selectedIncome.invoices?.length && <p className="p-4 text-xs text-slate-500">Sin facturas asociadas.</p>}
                                </div>
                            </section>
                            <section>
                                <h4 className="mb-3 text-sm font-bold text-slate-800">Movimientos bancarios ({selectedIncome.details?.length || 0})</h4>
                                <div className="max-h-80 overflow-y-auto rounded-lg border border-slate-200 divide-y divide-slate-100">
                                    {(selectedIncome.details || []).map((detail) => <div key={detail.id} className="px-3 py-3 text-sm"><div className="flex justify-between gap-3"><strong className="text-slate-700">{detail.movement?.movement_number || detail.movement?.reference || 'Movimiento'}</strong><strong className="text-blue-700">{money.format(detail.movement?.credit_amount)}</strong></div><div className="text-xs text-slate-500">{detail.movement?.operation_date} · {detail.movement?.bank?.name || 'Banco'}</div><div className="mt-1 text-xs text-slate-400">{detail.movement?.description}</div></div>)}
                                    {!selectedIncome.details?.length && <p className="p-4 text-xs text-slate-500">Sin movimientos asociados.</p>}
                                </div>
                            </section>
                        </div>
                    </div>
                </div>
            )}
        </>
    );
}
