import { useState, useEffect } from 'react';
import axios from 'axios';
import { X, Loader2 } from 'lucide-react';

const money = new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' });

const STATUS_OPTIONS = [
    { value: 'cancelada', label: 'Cancelada' },
    { value: 'anio_anterior', label: 'Año anterior' },
    { value: 'otro', label: 'Otro' },
];

export default function PendingInvoicesModal({ month, onClose, onResolved }) {
    const [loading, setLoading] = useState(true);
    const [invoices, setInvoices] = useState([]);
    const [drafts, setDrafts] = useState({}); // { [invoiceId]: { pending_status, pending_note } }
    const [savingId, setSavingId] = useState(null);
    const [error, setError] = useState(null);

    const load = () => {
        setLoading(true);
        axios.get(route('reconciliation.pending'), { params: { month } })
            .then(res => setInvoices(res.data.invoices))
            .catch(() => setError('No se pudieron cargar las facturas pendientes.'))
            .finally(() => setLoading(false));
    };

    useEffect(() => { load(); }, []);

    const setDraft = (id, patch) => {
        setDrafts(prev => ({ ...prev, [id]: { ...prev[id], ...patch } }));
    };

    const save = (invoice) => {
        const draft = drafts[invoice.id] || {};
        if (!draft.pending_status) return;
        if (['anio_anterior', 'otro'].includes(draft.pending_status) && !draft.pending_note?.trim()) {
            setError('Debes escribir información en el campo de texto.');
            return;
        }

        setError(null);
        setSavingId(invoice.id);
        axios.post(route('reconciliation.pending.update', invoice.id), {
            pending_status: draft.pending_status,
            pending_note: draft.pending_note || null,
        })
            .then(() => {
                setInvoices(prev => prev.filter(i => i.id !== invoice.id));
                onResolved?.();
            })
            .catch(() => setError('Ocurrió un error al guardar el estatus.'))
            .finally(() => setSavingId(null));
    };

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
            <div className="bg-white rounded-xl shadow-xl w-full max-w-3xl max-h-[85vh] flex flex-col">
                <div className="flex items-center justify-between border-b border-slate-200 p-4">
                    <h3 className="font-bold text-slate-800">Facturas Pendientes</h3>
                    <button onClick={onClose} className="text-slate-400 hover:text-slate-700">
                        <X className="w-5 h-5" />
                    </button>
                </div>

                <div className="flex-1 overflow-y-auto p-4 space-y-3">
                    {error && (
                        <div className="bg-red-50 border border-red-200 text-red-700 text-xs rounded-lg p-2">{error}</div>
                    )}

                    {loading && (
                        <div className="flex items-center justify-center py-10 text-slate-400">
                            <Loader2 className="w-5 h-5 animate-spin mr-2" /> Cargando...
                        </div>
                    )}

                    {!loading && invoices.length === 0 && (
                        <p className="text-center text-slate-400 text-sm py-10">No hay facturas pendientes.</p>
                    )}

                    {!loading && invoices.map(inv => {
                        const draft = drafts[inv.id] || {};
                        const needsNote = ['anio_anterior', 'otro'].includes(draft.pending_status);
                        return (
                            <div key={inv.id} className="border border-slate-200 rounded-lg p-3">
                                <div className="flex justify-between items-center text-sm mb-2">
                                    <div>
                                        <span className="font-semibold text-slate-800">{inv.numero_factura || 'Sin número'}</span>
                                        <span className="text-xs text-slate-400 ml-2">Fecha: {inv.fecha}</span>
                                    </div>
                                    <span className="font-bold text-slate-700">{money.format(inv.total)}</span>
                                </div>

                                <div className="flex flex-wrap items-center gap-2">
                                    <select
                                        value={draft.pending_status || ''}
                                        onChange={e => setDraft(inv.id, { pending_status: e.target.value })}
                                        className="border-slate-300 rounded-md text-sm p-2 bg-slate-50"
                                    >
                                        <option value="">-- Selecciona estatus --</option>
                                        {STATUS_OPTIONS.map(o => <option key={o.value} value={o.value}>{o.label}</option>)}
                                    </select>

                                    {needsNote && (
                                        <input
                                            type="text"
                                            value={draft.pending_note || ''}
                                            onChange={e => setDraft(inv.id, { pending_note: e.target.value })}
                                            placeholder="Escribe la información aquí..."
                                            className="flex-1 min-w-[200px] border-slate-300 rounded-md text-sm p-2"
                                        />
                                    )}

                                    <button
                                        onClick={() => save(inv)}
                                        disabled={!draft.pending_status || savingId === inv.id}
                                        className="ml-auto bg-blue-600 hover:bg-blue-700 disabled:bg-slate-300 text-white text-xs font-semibold px-3 py-2 rounded-md"
                                    >
                                        {savingId === inv.id ? 'Guardando...' : 'Marcar'}
                                    </button>
                                </div>
                            </div>
                        );
                    })}
                </div>
            </div>
        </div>
    );
}
