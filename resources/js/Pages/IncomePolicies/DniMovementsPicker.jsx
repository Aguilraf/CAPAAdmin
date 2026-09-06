import { useEffect, useState, Fragment } from 'react';
import axios from 'axios';

const money = new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' });

const formatDate = (value) => {
    if (!value) return '';
    const [year, month, day] = String(value).slice(0, 10).split('-');
    if (year && month && day && year.length === 4) return `${day}/${month}/${year}`;
    return value;
};

export default function DniMovementsPicker({ date, banks = [], incomePolicyId, selectedIds, onChange, onTotalsChange }) {
    const [bankId, setBankId] = useState('');
    const [movements, setMovements] = useState([]);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);

    useEffect(() => {
        if (!date) {
            setMovements([]);
            return;
        }

        setLoading(true);
        setError(null);
        axios.get(route('income-policies.dni-movements'), {
            params: { date, bank_id: bankId || null, income_policy_id: incomePolicyId || null },
        })
            .then((res) => setMovements(res.data))
            .catch(() => setError('No se pudieron cargar los movimientos.'))
            .finally(() => setLoading(false));
    }, [date, bankId, incomePolicyId]);

    useEffect(() => {
        const selectedTotal = movements
            .filter((m) => selectedIds.includes(m.id))
            .reduce((sum, m) => sum + Number(m.credit_amount || 0), 0);
        const iva = Number((selectedTotal - selectedTotal / 1.16).toFixed(2));
        onTotalsChange?.({ total: selectedTotal, iva });
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [movements, selectedIds]);

    const toggle = (id) => {
        onChange(selectedIds.includes(id) ? selectedIds.filter((m) => m !== id) : [...selectedIds, id]);
    };

    const selectedTotal = movements.filter((m) => selectedIds.includes(m.id)).reduce((sum, m) => sum + Number(m.credit_amount || 0), 0);

    let previousBank = null;

    return (
        <div className="rounded-lg border border-slate-200 bg-slate-50 p-4">
            <div className="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h3 className="font-semibold text-slate-800">Movimientos no identificados</h3>
                    <p className="mt-1 text-sm text-slate-500">Se muestran los movimientos del mes de la fecha inicial que aún no han sido usados.</p>
                </div>
                <div className="flex items-center gap-3">
                    <select value={bankId} onChange={(e) => setBankId(e.target.value)} className="rounded-md border-gray-300 text-sm shadow-sm">
                        <option value="">Todos los bancos</option>
                        {banks.map((bank) => <option key={bank.id} value={bank.id}>{bank.name}</option>)}
                    </select>
                    <div className="text-right font-semibold text-emerald-700">Seleccionado: {money.format(selectedTotal)}</div>
                </div>
            </div>

            {!date ? (
                <p className="mt-3 text-sm text-slate-500">Captura la fecha inicial para cargar los movimientos disponibles del mes.</p>
            ) : loading ? (
                <p className="mt-3 text-sm text-slate-500">Cargando movimientos...</p>
            ) : error ? (
                <p className="mt-3 text-sm text-red-600">{error}</p>
            ) : (
                <div className="mt-4 max-h-72 overflow-y-auto overflow-x-auto rounded-md border border-slate-200 bg-white">
                    <table className="min-w-full divide-y divide-slate-200 text-sm">
                        <thead className="bg-slate-100 text-left text-xs uppercase text-slate-500">
                            <tr>
                                <th className="px-3 py-2">Sel.</th>
                                <th className="px-3 py-2">Fecha</th>
                                <th className="px-3 py-2">Banco</th>
                                <th className="px-3 py-2">Movimiento</th>
                                <th className="px-3 py-2">Concepto</th>
                                <th className="px-3 py-2 text-right">Importe</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100">
                            {movements.length === 0 && (
                                <tr><td colSpan="6" className="px-3 py-6 text-center text-slate-400">No hay movimientos disponibles en ese mes.</td></tr>
                            )}
                            {movements.map((m) => {
                                const showBankHeader = m.bank?.name !== previousBank;
                                previousBank = m.bank?.name;
                                return (
                                    <Fragment key={m.id}>
                                        {showBankHeader && (
                                            <tr className="bg-slate-200">
                                                <td colSpan="6" className="px-3 py-1.5 text-xs font-bold uppercase tracking-wide text-slate-700">{m.bank?.name || 'Banco'}</td>
                                            </tr>
                                        )}
                                        <tr onClick={() => toggle(m.id)} className={`cursor-pointer ${selectedIds.includes(m.id) ? 'bg-blue-50' : 'hover:bg-slate-50'}`}>
                                            <td className="px-3 py-2"><input type="checkbox" checked={selectedIds.includes(m.id)} onChange={() => toggle(m.id)} onClick={(e) => e.stopPropagation()} /></td>
                                            <td className="px-3 py-2 whitespace-nowrap">{formatDate(m.operation_date)}</td>
                                            <td className="px-3 py-2 whitespace-nowrap">{m.bank?.name}</td>
                                            <td className="px-3 py-2 whitespace-nowrap">{m.movement_number || m.reference || '-'}</td>
                                            <td className="px-3 py-2">{m.description}</td>
                                            <td className="px-3 py-2 text-right whitespace-nowrap">{money.format(m.credit_amount)}</td>
                                        </tr>
                                    </Fragment>
                                );
                            })}
                        </tbody>
                    </table>
                </div>
            )}
        </div>
    );
}
