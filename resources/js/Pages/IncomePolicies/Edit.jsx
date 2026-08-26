import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import InputError from '@/Components/InputError';
import PrimaryButton from '@/Components/PrimaryButton';
import { Head, useForm } from '@inertiajs/react';

export default function Edit({ policy, accounts = [], policyTypes = [] }) {
    const existing = new Map(policy.details.map((detail) => [detail.income_account_id, detail.amount]));
    const { data, setData, put, transform, processing, errors } = useForm({
        policy_number: policy.policy_number,
        policy_type: policy.policy_type,
        concept: policy.concept || '',
        start_date: String(policy.start_date).slice(0, 10),
        end_date: String(policy.end_date).slice(0, 10),
        observations: policy.observations || '',
        details: accounts.map((account) => ({ income_account_id: account.id, amount: existing.get(account.id) || '' })),
    });

    const total = data.details.reduce((sum, detail) => sum + Number(detail.amount || 0), 0);
    const setAmount = (id, amount) => setData('details', data.details.map((detail) => detail.income_account_id === id ? { ...detail, amount } : detail));
    const submit = (event) => {
        event.preventDefault();
        transform((form) => ({ ...form, details: form.details.filter((detail) => Number(detail.amount || 0) > 0) }));
        put(route('income-policies.update', policy.id));
    };

    return (
        <AuthenticatedLayout header={<h2 className="text-xl font-semibold text-gray-800">Editar Póliza de Ingreso</h2>}>
            <Head title="Editar póliza" />
            <div className="py-8"><div className="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8"><form onSubmit={submit} className="space-y-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <div className="grid gap-5 md:grid-cols-2">
                    <div><label className="block text-sm font-medium text-slate-700">Número de póliza *</label><input value={data.policy_number} onChange={(e) => setData('policy_number', e.target.value)} className="mt-1 w-full rounded-md border-gray-300" required /><InputError message={errors.policy_number} /></div>
                    <div><label className="block text-sm font-medium text-slate-700">Tipo de póliza *</label><select value={data.policy_type} onChange={(e) => setData('policy_type', e.target.value)} className="mt-1 w-full rounded-md border-gray-300" required>{policyTypes.map((type) => <option key={type.id} value={type.name}>{type.name}</option>)}</select><InputError message={errors.policy_type} /></div>
                    <div><label className="block text-sm font-medium text-slate-700">Fecha inicial *</label><input type="date" value={data.start_date} onChange={(e) => setData('start_date', e.target.value)} className="mt-1 w-full rounded-md border-gray-300" required /></div>
                    <div><label className="block text-sm font-medium text-slate-700">Fecha final *</label><input type="date" value={data.end_date} onChange={(e) => setData('end_date', e.target.value)} className="mt-1 w-full rounded-md border-gray-300" required /></div>
                </div>
                <div><label className="block text-sm font-medium text-slate-700">Concepto *</label><textarea value={data.concept} onChange={(e) => setData('concept', e.target.value)} className="mt-1 w-full rounded-md border-gray-300" required /></div>
                <div className="rounded-lg border border-slate-200 bg-slate-50 p-4"><div className="flex justify-between"><h3 className="font-semibold text-slate-800">Cuentas de la póliza</h3><strong className="text-emerald-700">Total: {total.toLocaleString('es-MX', { style: 'currency', currency: 'MXN' })}</strong></div><div className="mt-4 overflow-x-auto"><table className="min-w-full"><thead><tr className="text-left text-xs uppercase text-slate-500"><th className="px-3 py-2">Cuenta contable</th><th className="px-3 py-2">Concepto</th><th className="px-3 py-2">Importe</th></tr></thead><tbody>{accounts.map((account) => <tr key={account.id}><td className="px-3 py-2 text-sm">{account.accounting_account}</td><td className="px-3 py-2 text-sm">{account.concept}</td><td className="px-3 py-2"><input type="number" min="0" step="0.01" value={data.details.find((detail) => detail.income_account_id === account.id)?.amount || ''} onChange={(e) => setAmount(account.id, e.target.value)} className="w-full rounded-md border-gray-300" /></td></tr>)}</tbody></table></div><InputError className="mt-2" message={errors.details} /></div>
                <div><label className="block text-sm font-medium text-slate-700">Observaciones</label><textarea value={data.observations} onChange={(e) => setData('observations', e.target.value)} className="mt-1 w-full rounded-md border-gray-300" /></div>
                <div className="flex justify-end"><PrimaryButton disabled={processing || total <= 0}>{processing ? 'Guardando...' : 'Guardar cambios'}</PrimaryButton></div>
            </form></div></div>
        </AuthenticatedLayout>
    );
}
