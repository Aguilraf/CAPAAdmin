import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import PrimaryButton from '@/Components/PrimaryButton';
import { Head, router, usePage } from '@inertiajs/react';
import { Fragment, useState } from 'react';

const money = new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' });
const date = (value) => value ? String(value).slice(0, 10).split('-').reverse().join('/') : '';

export default function Index({ policies, filters = {} }) {
    const { flash } = usePage().props;
    const [search, setSearch] = useState(filters.search || '');
    const [expandedId, setExpandedId] = useState(null);
    const filtered = policies.filter((policy) => `${policy.policy_number} ${policy.policy_type} ${policy.concept}`.toLowerCase().includes(search.toLowerCase().trim()));

    const remove = (id) => {
        if (confirm('¿Deseas eliminar esta póliza?')) router.delete(route('income-policies.destroy', id), { preserveScroll: true });
    };

    return (
        <AuthenticatedLayout header={<h2 className="text-xl font-semibold text-gray-800">Pólizas de ingreso</h2>}>
            <Head title="Pólizas de ingreso" />
            <div className="py-8"><div className="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                <div className="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                    <div className="flex flex-col gap-4 border-b border-slate-200 bg-slate-50 px-6 py-5 md:flex-row md:items-center md:justify-between"><div><h3 className="text-lg font-semibold text-slate-800">Pólizas capturadas</h3><p className="mt-1 text-sm text-slate-500">Consulta, modifica o elimina las pólizas registradas.</p></div><PrimaryButton type="button" onClick={() => router.visit(route('income-policies.create'))}>Nueva póliza</PrimaryButton></div>
                    {flash?.success && <div className="mx-6 mt-5 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{flash.success}</div>}
                    <div className="p-6"><input type="search" value={search} onChange={(event) => setSearch(event.target.value)} placeholder="Buscar por número, tipo o concepto..." className="mb-5 w-full rounded-md border-gray-300 text-sm shadow-sm md:w-96" />
                        <div className="overflow-x-auto"><table className="min-w-full divide-y divide-slate-200"><thead className="bg-slate-50"><tr><th className="px-4 py-3 text-left text-xs uppercase text-slate-500">Póliza</th><th className="px-4 py-3 text-left text-xs uppercase text-slate-500">Tipo</th><th className="px-4 py-3 text-left text-xs uppercase text-slate-500">Periodo</th><th className="px-4 py-3 text-left text-xs uppercase text-slate-500">Concepto</th><th className="px-4 py-3 text-right text-xs uppercase text-slate-500">Importe</th><th className="px-4 py-3 text-right text-xs uppercase text-slate-500">Acciones</th></tr></thead><tbody className="divide-y divide-slate-200">
                            {filtered.length === 0 ? <tr><td colSpan="6" className="px-4 py-10 text-center text-sm text-slate-500">No hay pólizas registradas.</td></tr> : filtered.map((policy) => {
                                const isExpanded = expandedId === policy.id;

                                return (
                                    <Fragment key={policy.id}>
                                        <tr>
                                            <td className="px-4 py-4 text-sm font-semibold text-slate-800">{policy.policy_number}</td>
                                            <td className="px-4 py-4 text-sm text-slate-600">{policy.policy_type}</td>
                                            <td className="px-4 py-4 text-sm text-slate-600">{date(policy.start_date)} - {date(policy.end_date)}</td>
                                            <td className="max-w-xs px-4 py-4 text-sm text-slate-600">{policy.concept}</td>
                                            <td className="px-4 py-4 text-right text-sm font-semibold text-emerald-700">{money.format(policy.amount)}</td>
                                            <td className="px-4 py-4 text-right"><div className="flex justify-end gap-2"><button type="button" onClick={() => setExpandedId(isExpanded ? null : policy.id)} className="rounded-md border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700">{isExpanded ? 'Ocultar' : 'Detalles'}</button><button type="button" onClick={() => router.visit(route('income-policies.edit', policy.id))} className="rounded-md border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700">Editar</button><button type="button" onClick={() => remove(policy.id)} className="rounded-md border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700">Eliminar</button></div></td>
                                        </tr>
                                        {isExpanded && <tr className="bg-slate-50"><td colSpan="6" className="px-6 py-4"><div className="mb-3 text-sm font-semibold text-slate-700">Cuentas que soportan la póliza</div><div className="overflow-x-auto"><table className="min-w-full text-sm"><thead><tr className="text-left text-xs uppercase text-slate-500"><th className="px-3 py-2">Cuenta presupuestal</th><th className="px-3 py-2">Cuenta contable</th><th className="px-3 py-2">Concepto</th><th className="px-3 py-2 text-right">Importe</th></tr></thead><tbody className="divide-y divide-slate-200 bg-white">{policy.details.map((detail) => <tr key={detail.id}><td className="px-3 py-2">{detail.account?.budget_account || '-'}</td><td className="px-3 py-2">{detail.account?.accounting_account || '-'}</td><td className="px-3 py-2">{detail.account?.concept || '-'}</td><td className="px-3 py-2 text-right font-semibold text-emerald-700">{money.format(detail.amount)}</td></tr>)}</tbody></table></div>{policy.observations && <div className="mt-3 text-sm text-slate-600"><strong>Observaciones:</strong> {policy.observations}</div>}</td></tr>}
                                    </Fragment>
                                );
                            })}
                        </tbody></table></div>
                    </div>
                </div>
            </div></div>
        </AuthenticatedLayout>
    );
}
