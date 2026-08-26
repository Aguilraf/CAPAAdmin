import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import InputError from '@/Components/InputError';
import PrimaryButton from '@/Components/PrimaryButton';
import { Head, router, useForm, usePage } from '@inertiajs/react';
import { useState } from 'react';

export default function Index({ accounts }) {
    const { flash } = usePage().props;
    const [search, setSearch] = useState('');
    const { data, setData, post, processing, errors, reset } = useForm({ budget_account: '', accounting_account: '', concept: '' });
    const uploadForm = useForm({ file: null });

    const submit = (event) => {
        event.preventDefault();
        post(route('income-accounts.store'), { onSuccess: () => reset() });
    };

    const upload = (event) => {
        event.preventDefault();
        uploadForm.post(route('income-accounts.import'), { forceFormData: true, onSuccess: () => uploadForm.reset('file') });
    };

    const remove = (id) => {
        if (confirm('¿Deseas eliminar esta cuenta del catálogo?')) {
            router.delete(route('income-accounts.destroy', id));
        }
    };

    const toggleVisibility = (id) => {
        router.patch(route('income-accounts.visibility', id), {}, { preserveScroll: true });
    };

    const filteredAccounts = accounts.filter((item) => {
        const text = `${item.budget_account} ${item.accounting_account} ${item.concept}`.toLowerCase();
        return text.includes(search.toLowerCase().trim());
    });

    return (
        <AuthenticatedLayout header={<h2 className="text-xl font-semibold text-gray-800">Catálogo de cuentas de ingreso</h2>}>
            <Head title="Catálogo de cuentas" />
            <div className="py-8">
                <div className="mx-auto max-w-6xl space-y-6 px-4 sm:px-6 lg:px-8">
                    <div className="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                        <div className="border-b border-slate-200 bg-slate-50 px-6 py-5">
                            <h3 className="text-lg font-semibold text-slate-800">Agregar cuenta</h3>
                            <p className="mt-1 text-sm text-slate-500">Estas cuentas estarán disponibles al capturar una póliza.</p>
                        </div>
                        {flash?.success && <div className="mx-6 mt-5 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{flash.success}</div>}
                        <form onSubmit={submit} className="grid gap-5 p-6 md:grid-cols-[1fr_1fr_2fr_auto] md:items-end">
                            <div>
                                <label htmlFor="budget_account" className="block text-sm font-medium text-slate-700">Cuenta presupuestal *</label>
                                <input id="budget_account" value={data.budget_account} onChange={(event) => setData('budget_account', event.target.value)} className="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required />
                                <InputError className="mt-2" message={errors.budget_account} />
                            </div>
                            <div>
                                <label htmlFor="accounting_account" className="block text-sm font-medium text-slate-700">Cuenta contable *</label>
                                <input id="accounting_account" value={data.accounting_account} onChange={(event) => setData('accounting_account', event.target.value)} className="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required />
                                <InputError className="mt-2" message={errors.accounting_account} />
                            </div>
                            <div>
                                <label htmlFor="concept" className="block text-sm font-medium text-slate-700">Concepto *</label>
                                <input id="concept" value={data.concept} onChange={(event) => setData('concept', event.target.value)} className="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required />
                                <InputError className="mt-2" message={errors.concept} />
                            </div>
                            <PrimaryButton disabled={processing}>{processing ? 'Guardando...' : 'Agregar cuenta'}</PrimaryButton>
                        </form>
                        <form onSubmit={upload} className="flex flex-col gap-3 border-t border-slate-200 bg-slate-50 p-6 md:flex-row md:items-end">
                            <div className="flex-1"><label htmlFor="file" className="block text-sm font-medium text-slate-700">Cargar layout Excel o CSV</label><input id="file" type="file" accept=".xlsx,.xls,.csv" onChange={(event) => uploadForm.setData('file', event.target.files[0])} className="mt-1 block w-full text-sm text-slate-600" required /></div>
                            <a href={route('income-accounts.template')} className="text-sm font-semibold text-indigo-700 underline">Descargar plantilla</a>
                            <PrimaryButton disabled={uploadForm.processing || !uploadForm.data.file}>{uploadForm.processing ? 'Cargando...' : 'Cargar catálogo'}</PrimaryButton>
                            <InputError message={uploadForm.errors.file} />
                        </form>
                    </div>

                    <div className="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                        <div className="flex flex-col gap-4 border-b border-slate-200 px-6 py-4 md:flex-row md:items-center md:justify-between">
                            <h3 className="font-semibold text-slate-800">Cuentas registradas</h3>
                            <input type="search" value={search} onChange={(event) => setSearch(event.target.value)} placeholder="Buscar cuenta o concepto..." className="w-full rounded-md border-gray-300 text-sm shadow-sm md:w-80" />
                        </div>
                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead className="bg-gray-50"><tr><th className="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">Cuenta presupuestal</th><th className="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">Cuenta contable</th><th className="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">Concepto</th><th className="px-6 py-3 text-center text-xs font-semibold uppercase text-gray-500">Mostrar</th><th className="px-6 py-3 text-right text-xs font-semibold uppercase text-gray-500">Acciones</th></tr></thead>
                                <tbody className="divide-y divide-gray-200 bg-white">
                                    {filteredAccounts.length === 0 ? <tr><td colSpan="5" className="px-6 py-8 text-center text-sm text-gray-500">{accounts.length === 0 ? 'Aún no hay cuentas registradas.' : 'No se encontraron cuentas.'}</td></tr> : filteredAccounts.map((item) => <tr key={item.id}><td className="px-6 py-3 text-sm font-medium text-slate-800">{item.budget_account}</td><td className="px-6 py-3 text-sm text-slate-600">{item.accounting_account}</td><td className="px-6 py-3 text-sm text-slate-600">{item.concept}</td><td className="px-6 py-3 text-center"><input type="checkbox" checked={item.visible} onChange={() => toggleVisibility(item.id)} aria-label={`Mostrar ${item.concept}`} /></td><td className="px-6 py-3 text-right"><button type="button" onClick={() => remove(item.id)} className="rounded-md border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-100">Eliminar</button></td></tr>)}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
