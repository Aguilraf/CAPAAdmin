import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import InputError from '@/Components/InputError';
import PrimaryButton from '@/Components/PrimaryButton';
import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { useState } from 'react';
import DniMovementsPicker from './DniMovementsPicker';

const DNI_TYPE = 'DNI (pagos no identificados)';

export default function Create({ accounts = [], policyTypes = [], banks = [] }) {
    const { flash } = usePage().props;
    const { data, setData, post, transform, processing, errors } = useForm({
        policy_number: '',
        policy_type: '',
        concept: '',
        start_date: '',
        end_date: '',
        observations: '',
        details: accounts.map((item) => ({ income_account_id: item.id, amount: '' })),
        movement_ids: [],
    });

    const isDni = data.policy_type === DNI_TYPE;
    const [dniTotals, setDniTotals] = useState({ total: 0, iva: 0 });
    const totalAmount = isDni ? dniTotals.total : data.details.reduce((sum, detail) => sum + Number(detail.amount || 0), 0);

    const handleStartDateChange = (value) => {
        const shouldSyncEndDate = !data.end_date || data.end_date === data.start_date;
        setData((current) => ({
            ...current,
            start_date: value,
            end_date: shouldSyncEndDate ? value : current.end_date,
        }));
    };

    const updateDetailAmount = (accountId, amount) => {
        setData('details', data.details.map((detail) => detail.income_account_id === accountId ? { ...detail, amount } : detail));
    };

    const submit = (event) => {
        event.preventDefault();
        transform((formData) => ({
            ...formData,
            details: isDni ? [] : formData.details.filter((detail) => Number(detail.amount || 0) > 0),
        }));
        post(route('income-policies.store'));
    };

    return (
        <AuthenticatedLayout header={<h2 className="text-xl font-semibold text-gray-800">Agregar Póliza de Ingreso</h2>}>
            <Head title="Agregar Póliza de Ingreso" />

            <div className="py-8">
                <div className="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                    <div className="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                        <div className="flex flex-col gap-4 border-b border-slate-200 bg-slate-50 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h3 className="text-lg font-semibold text-slate-800">Nueva póliza</h3>
                                <p className="mt-1 text-sm text-slate-500">Captura la información de la póliza de ingreso.</p>
                            </div>
                            <Link href={route('income-policies.create')} className="inline-flex items-center justify-center rounded-md bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">
                                Nuevo
                            </Link>
                        </div>

                        {flash?.success && (
                            <div className="mx-6 mt-5 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                                {flash.success}
                            </div>
                        )}

                        <form onSubmit={submit} className="space-y-6 p-6">
                            <div className="grid grid-cols-1 gap-5 md:grid-cols-2">
                                <div>
                                    <label htmlFor="policy_number" className="block text-sm font-medium text-slate-700">Número de póliza *</label>
                                    <input id="policy_number" value={data.policy_number} onChange={(event) => setData('policy_number', event.target.value)} className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required />
                                    <InputError className="mt-2" message={errors.policy_number} />
                                </div>

                                <div>
                                    <label htmlFor="policy_type" className="block text-sm font-medium text-slate-700">Tipo de póliza *</label>
                                    <select id="policy_type" value={data.policy_type} onChange={(event) => setData('policy_type', event.target.value)} className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                        <option value="">Selecciona un tipo</option>
                                        {policyTypes.map((type) => <option key={type.id} value={type.name}>{type.name}</option>)}
                                    </select>
                                    <InputError className="mt-2" message={errors.policy_type} />
                                </div>

                                <div>
                                    <label htmlFor="start_date" className="block text-sm font-medium text-slate-700">Fecha inicial *</label>
                                    <input id="start_date" type="date" value={data.start_date} onChange={(event) => handleStartDateChange(event.target.value)} className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required />
                                    <InputError className="mt-2" message={errors.start_date} />
                                </div>

                                <div>
                                    <label htmlFor="end_date" className="block text-sm font-medium text-slate-700">Fecha final *</label>
                                    <input id="end_date" type="date" value={data.end_date} onChange={(event) => setData('end_date', event.target.value)} className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required />
                                    <InputError className="mt-2" message={errors.end_date} />
                                </div>
                            </div>

                            <div>
                                <label htmlFor="concept" className="block text-sm font-medium text-slate-700">Concepto *</label>
                                <textarea id="concept" rows="3" value={data.concept} onChange={(event) => setData('concept', event.target.value)} className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required />
                                <InputError className="mt-2" message={errors.concept} />
                            </div>

                            {isDni ? (
                                <>
                                    <DniMovementsPicker
                                        date={data.start_date}
                                        banks={banks}
                                        selectedIds={data.movement_ids}
                                        onChange={(ids) => setData('movement_ids', ids)}
                                        onTotalsChange={setDniTotals}
                                    />
                                    <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                        <div className="rounded-md border border-emerald-200 bg-emerald-50 p-3">
                                            <p className="text-xs font-semibold uppercase text-emerald-700">Total</p>
                                            <p className="text-lg font-bold text-emerald-800">{dniTotals.total.toLocaleString('es-MX', { style: 'currency', currency: 'MXN' })}</p>
                                        </div>
                                        <div className="rounded-md border border-emerald-200 bg-emerald-50 p-3">
                                            <p className="text-xs font-semibold uppercase text-emerald-700">IVA (16% incluido)</p>
                                            <p className="text-lg font-bold text-emerald-800">{dniTotals.iva.toLocaleString('es-MX', { style: 'currency', currency: 'MXN' })}</p>
                                        </div>
                                    </div>
                                    <InputError className="mt-2" message={errors.movement_ids} />
                                </>
                            ) : (
                                <div className="rounded-lg border border-slate-200 bg-slate-50 p-4">
                                    <div className="flex items-center justify-between gap-4">
                                        <div>
                                            <h3 className="font-semibold text-slate-800">Cuentas que soportan la póliza</h3>
                                            <p className="mt-1 text-sm text-slate-500">Captura el importe correspondiente a cada concepto visible.</p>
                                        </div>
                                        <div className="text-right font-semibold text-emerald-700">Total: {totalAmount.toLocaleString('es-MX', { style: 'currency', currency: 'MXN' })}</div>
                                    </div>
                                    <div className="mt-4 overflow-x-auto">
                                        <table className="min-w-full divide-y divide-slate-200">
                                            <thead><tr className="text-left text-xs uppercase text-slate-500"><th className="px-3 py-2">Cuenta contable</th><th className="px-3 py-2">Concepto</th><th className="px-3 py-2">Importe</th></tr></thead>
                                            <tbody className="divide-y divide-slate-200 bg-white">
                                                {accounts.length === 0 ? <tr><td colSpan="3" className="px-3 py-6 text-center text-sm text-slate-500">No hay cuentas marcadas para mostrar.</td></tr> : accounts.map((item) => <tr key={item.id}><td className="px-3 py-2 text-sm text-slate-700">{item.accounting_account}</td><td className="px-3 py-2 text-sm text-slate-700">{item.concept}</td><td className="px-3 py-2"><input type="number" min="0" step="0.01" value={data.details.find((detail) => detail.income_account_id === item.id)?.amount || ''} onChange={(event) => updateDetailAmount(item.id, event.target.value)} className="w-full rounded-md border-gray-300 text-sm shadow-sm" /></td></tr>)}
                                            </tbody>
                                        </table>
                                    </div>
                                    <InputError className="mt-2" message={errors.details} />
                                </div>
                            )}

                            <div>
                                <label htmlFor="observations" className="block text-sm font-medium text-slate-700">Observaciones</label>
                                <textarea id="observations" rows="3" value={data.observations} onChange={(event) => setData('observations', event.target.value)} className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                                <InputError className="mt-2" message={errors.observations} />
                            </div>

                            <div className="flex justify-end border-t border-slate-200 pt-5">
                                <PrimaryButton disabled={processing || (isDni ? data.movement_ids.length === 0 : totalAmount <= 0)}>{processing ? 'Guardando...' : 'Guardar póliza'}</PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
