import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import InputError from '@/Components/InputError';
import PrimaryButton from '@/Components/PrimaryButton';
import { Head, useForm, usePage } from '@inertiajs/react';

export default function Create({ accounts = [] }) {
    const { flash } = usePage().props;
    const { data, setData, post, processing, errors } = useForm({
        policy_number: '',
        policy_type: '',
        account: '',
        concept: '',
        amount: '',
        start_date: '',
        end_date: '',
        observations: '',
    });

    const submit = (event) => {
        event.preventDefault();
        post(route('income-policies.store'));
    };

    return (
        <AuthenticatedLayout header={<h2 className="text-xl font-semibold text-gray-800">Agregar Póliza de Ingreso</h2>}>
            <Head title="Agregar Póliza de Ingreso" />

            <div className="py-8">
                <div className="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                    <div className="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                        <div className="border-b border-slate-200 bg-slate-50 px-6 py-5">
                            <h3 className="text-lg font-semibold text-slate-800">Nueva póliza</h3>
                            <p className="mt-1 text-sm text-slate-500">Captura la información de la póliza de ingreso.</p>
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
                                        <option value="Ingreso">Ingreso</option>
                                        <option value="Ingreso extraordinario">Ingreso extraordinario</option>
                                        <option value="Traspaso">Traspaso</option>
                                        <option value="Otro">Otro</option>
                                    </select>
                                    <InputError className="mt-2" message={errors.policy_type} />
                                </div>

                                <div>
                                    <label htmlFor="account" className="block text-sm font-medium text-slate-700">Cuenta *</label>
                                    <select id="account" value={data.account} onChange={(event) => setData('account', event.target.value)} className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                        <option value="">Selecciona una cuenta</option>
                                        {accounts.map((item) => <option key={item.id} value={item.budget_account}>{item.accounting_account} · {item.concept}</option>)}
                                    </select>
                                    <InputError className="mt-2" message={errors.account} />
                                </div>

                                <div>
                                    <label htmlFor="amount" className="block text-sm font-medium text-slate-700">Importe *</label>
                                    <input id="amount" type="number" min="0.01" step="0.01" value={data.amount} onChange={(event) => setData('amount', event.target.value)} className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required />
                                    <InputError className="mt-2" message={errors.amount} />
                                </div>

                                <div>
                                    <label htmlFor="start_date" className="block text-sm font-medium text-slate-700">Fecha inicial *</label>
                                    <input id="start_date" type="date" value={data.start_date} onChange={(event) => setData('start_date', event.target.value)} className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required />
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

                            <div>
                                <label htmlFor="observations" className="block text-sm font-medium text-slate-700">Observaciones</label>
                                <textarea id="observations" rows="3" value={data.observations} onChange={(event) => setData('observations', event.target.value)} className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                                <InputError className="mt-2" message={errors.observations} />
                            </div>

                            <div className="flex justify-end border-t border-slate-200 pt-5">
                                <PrimaryButton disabled={processing}>{processing ? 'Guardando...' : 'Guardar póliza'}</PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
