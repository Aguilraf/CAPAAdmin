import { useState, useEffect } from 'react';
import { Head, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { CheckCircle, AlertTriangle, Info, Calendar } from 'lucide-react';

const money = new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' });

export default function Index({ policies, selectedPolicy, dailyIncomes, selectedDailyIncome, invoicesList, stats, filters }) {
    const [month, setMonth] = useState(filters.month || '');
    const [policyId, setPolicyId] = useState(filters.policy_id || '');
    const [dailyIncomeId, setDailyIncomeId] = useState(filters.daily_income_id || '');
    const [withoutIncome, setWithoutIncome] = useState(filters.without_income || false);
    const [selectedInvoices, setSelectedInvoices] = useState([]);

    useEffect(() => {
        const initial = invoicesList
            .filter(inv => filters.without_income
                ? inv.is_reconciled_without_income
                : inv.daily_income_id === Number(dailyIncomeId) || inv.income_policy_id === Number(policyId))
            .map(inv => inv.id);
        setSelectedInvoices(initial);
    }, [invoicesList, dailyIncomeId, policyId, filters.without_income]);

    const handleMonth = (e) => { setMonth(e.target.value); router.get(route('reconciliation.index'), { month: e.target.value }); };
    const handlePolicy = (e) => { setPolicyId(e.target.value); router.get(route('reconciliation.index'), { month, policy_id: e.target.value }); };
    const handleDay = (e) => {
        const val = e.target.value;
        if (val === 'without_income') {
            setWithoutIncome(true); setDailyIncomeId('');
            router.get(route('reconciliation.index'), { month, policy_id: policyId, without_income: true });
        } else {
            setWithoutIncome(false); setDailyIncomeId(val);
            router.get(route('reconciliation.index'), { month, policy_id: policyId, daily_income_id: val });
        }
    };

    const toggle = (id) => { setSelectedInvoices(prev => prev.includes(id) ? prev.filter(i => i !== id) : [...prev, id]); };
    const submit = (e) => {
        e.preventDefault();
        router.post(route('reconciliation.store'), {
            daily_income_id: withoutIncome ? null : dailyIncomeId,
            without_income: withoutIncome,
            policy_id: withoutIncome ? null : policyId,
            save_as_policy: true,
            invoices: selectedInvoices
        });
    };

    const policyTotal = Number(selectedPolicy?.amount || 0);
    const sumDaily = dailyIncomes.reduce((sum, d) => sum + Number(d.total_amount || 0), 0);
    const dayGoal = Number(selectedDailyIncome?.total_amount || 0);
    const invoicesSum = invoicesList.filter(inv => selectedInvoices.includes(inv.id)).reduce((sum, inv) => sum + Number(inv.total || 0), 0);
    const difference = dayGoal - invoicesSum;
    const isMatched = Math.abs(difference) < 0.01;

    const normal = invoicesList.filter(inv => inv.tipo === 'I');
    const complements = invoicesList.filter(inv => inv.tipo === 'P');

    return (
        <AuthenticatedLayout header={<h2 className="text-xl font-bold text-gray-800">Conciliación Facturas Vs Ingresos</h2>}>
            <Head title="Conciliación" />
            <div className="py-6 px-4 max-w-7xl mx-auto space-y-6">
                
                {/* Indicadores Mensuales */}
                <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div className="bg-white border rounded-xl p-4 flex flex-col justify-center shadow-sm">
                        <label className="text-xs font-semibold text-slate-500 uppercase">Mes</label>
                        <input type="month" value={month} onChange={handleMonth} className="mt-1 border border-slate-300 rounded-md p-2 text-sm bg-slate-50 w-full" />
                    </div>
                    <div className="bg-white border border-slate-200 rounded-xl p-4 flex items-center gap-4 shadow-sm">
                        <div className="p-3 bg-blue-50 text-blue-600 rounded-lg"><Calendar className="w-6 h-6" /></div>
                        <div><div className="text-xl font-bold">{stats.total}</div><div className="text-xs text-slate-500 uppercase font-medium">Facturas del Mes</div></div>
                    </div>
                    <div className="bg-white border border-slate-200 rounded-xl p-4 flex items-center gap-4 shadow-sm">
                        <div className="p-3 bg-green-50 text-green-600 rounded-lg"><CheckCircle className="w-6 h-6" /></div>
                        <div><div className="text-xl font-bold">{stats.reconciled}</div><div className="text-xs text-slate-500 uppercase font-medium">Relacionadas</div></div>
                    </div>
                    <div className="bg-white border border-slate-200 rounded-xl p-4 flex items-center gap-4 shadow-sm">
                        <div className="p-3 bg-yellow-50 text-yellow-600 rounded-lg"><AlertTriangle className="w-6 h-6" /></div>
                        <div><div className="text-xl font-bold">{stats.pending}</div><div className="text-xs text-slate-500 uppercase font-medium">Pendientes</div></div>
                    </div>
                </div>

                {/* Filtros de Selección de Póliza e Ingreso */}
                <div className="bg-white border border-slate-200 rounded-xl p-6 shadow-sm grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label className="block text-sm font-semibold text-slate-700 mb-1">1. Seleccionar Póliza de Ingreso</label>
                        <select value={policyId} onChange={handlePolicy} className="block w-full border-slate-300 rounded-lg p-2.5 text-sm bg-slate-50">
                            <option value="">-- Selecciona una póliza --</option>
                            {policies.map(p => <option key={p.id} value={p.id}>{p.policy_number} · {p.concept} ({money.format(p.amount)})</option>)}
                        </select>
                    </div>
                    {policyId && (
                        <div>
                            <label className="block text-sm font-semibold text-slate-700 mb-1">2. Seleccionar Día de Cobranza</label>
                            <select value={withoutIncome ? 'without_income' : dailyIncomeId} onChange={handleDay} className="block w-full border-slate-300 rounded-lg p-2.5 text-sm bg-slate-50">
                                <option value="">-- Selecciona un día --</option>
                                <option value="without_income">Sin día a comprobar (Depurar facturas libres)</option>
                                {dailyIncomes.map(d => <option key={d.id} value={d.id}>Día: {d.income_date} · Total: {money.format(d.total_amount)}</option>)}
                            </select>
                        </div>
                    )}
                </div>

                {selectedPolicy&&(<div className="bg-white border border-slate-200 rounded-xl p-6 flex flex-wrap gap-6 justify-between items-center bg-gradient-to-r from-slate-50 to-white shadow-sm"><div className="flex flex-wrap gap-6 text-sm"><div><div className="text-xs font-bold text-slate-400 uppercase">Monto Póliza</div><div className="font-extrabold text-slate-800 text-base">{money.format(policyTotal)}</div></div><div><div className="text-xs font-bold text-slate-400 uppercase">Suma Cobranzas</div><div className="font-extrabold text-blue-700 text-base">{money.format(sumDaily)}</div></div>{selectedDailyIncome&&(<><div><div className="text-xs font-bold text-slate-400 uppercase">Cobranza del Día</div><div className="font-extrabold text-orange-600 text-base">{money.format(dayGoal)}</div></div><div><div className="text-xs font-bold text-slate-400 uppercase">Suma Facturas Elegidas</div><div className="font-extrabold text-green-700 text-base">{money.format(invoicesSum)}</div></div></>)}</div>{selectedDailyIncome&&(<div className="flex items-center gap-4"><div className="text-right"><span className="text-xs text-slate-400 uppercase block font-semibold">Diferencia</span><span className={`text-lg font-bold ${isMatched?'text-green-600':'text-red-500'}`}>{money.format(difference)}</span></div>{isMatched?<span className="bg-green-100 text-green-800 text-xs font-bold px-3 py-1.5 rounded-full shadow-sm font-semibold">✓ Cuadrado</span>:<span className="bg-red-100 text-red-800 text-xs font-bold px-3 py-1.5 rounded-full shadow-sm font-semibold">⚠ Descuadrado</span>}</div>)}</div>)}

                {(selectedDailyIncome||withoutIncome)&&(<form onSubmit={submit} className="space-y-6">{selectedDailyIncome&&<div className="bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 rounded-lg text-xs flex items-center gap-2 shadow-sm"><Info className="w-4 h-4 shrink-0"/><span>Solo facturas con <strong>Fecha de Emisión igual o posterior ({selectedDailyIncome.income_date})</strong>.</span></div>}<div className="grid grid-cols-1 lg:grid-cols-2 gap-6"><div className="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm"><div className="bg-slate-50 border-b border-slate-200 p-3 font-bold text-slate-800 flex justify-between text-sm"><span>Facturas de Ingreso (I)</span><span className="text-xs bg-slate-200 px-2 py-0.5 rounded-full text-slate-700 font-semibold">{normal.length}</span></div><div className="max-h-[300px] overflow-y-auto divide-y divide-slate-100">{normal.map(inv=>(<label key={inv.id} className="flex items-center justify-between p-3 cursor-pointer hover:bg-slate-50 text-sm"><div className="flex items-center gap-3"><input type="checkbox" checked={selectedInvoices.includes(inv.id)} onChange={()=>toggle(inv.id)} className="rounded border-slate-300 text-blue-600 h-4 w-4"/><div className="font-semibold text-slate-800">{inv.numero_factura}</div><div className="text-xs text-slate-400">Fecha: {inv.fecha}</div></div><span className="font-bold text-slate-700">{money.format(inv.total)}</span></label>))}{normal.length===0&&<p className="p-6 text-center text-slate-400 text-xs font-semibold">No hay facturas de ingreso.</p>}</div></div><div className="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm"><div className="bg-slate-50 border-b border-slate-200 p-3 font-bold text-slate-800 flex justify-between text-sm"><span>Complementos de Pago (P)</span><span className="text-xs bg-slate-200 px-2 py-0.5 rounded-full text-slate-700 font-semibold">{complements.length}</span></div><div className="max-h-[300px] overflow-y-auto divide-y divide-slate-100">{complements.map(inv=>(<label key={inv.id} className="flex items-center justify-between p-3 cursor-pointer hover:bg-slate-50 text-sm"><div className="flex items-center gap-3"><input type="checkbox" checked={selectedInvoices.includes(inv.id)} onChange={()=>toggle(inv.id)} className="rounded border-slate-300 text-blue-600 h-4 w-4"/><div className="font-semibold text-slate-800">{inv.numero_factura} (Pago)</div><div className="text-xs text-slate-400">Fecha: {inv.fecha}</div></div><span className="font-bold text-indigo-700">{money.format(inv.total)}</span></label>))}{complements.length===0&&<p className="p-6 text-center text-slate-400 text-xs font-semibold">No hay complementos de pago.</p>}</div></div></div><div className="flex justify-end pt-2"><button type="submit" className="px-8 py-3 bg-gray-900 hover:bg-black text-white rounded-xl font-bold shadow-md transition-all active:scale-95 disabled:opacity-30 disabled:cursor-not-allowed" disabled={!withoutIncome&&selectedInvoices.length===0}>{withoutIncome?'Marcar como Sin Día a Comprobar':'Guardar Conciliación del Día'}</button></div></form>)}
            </div>
        </AuthenticatedLayout>
    );
}
