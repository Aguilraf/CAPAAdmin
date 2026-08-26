import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import DangerButton from '@/Components/DangerButton';
import Modal from '@/Components/Modal';
import Pagination from '@/Components/Pagination';
import SecondaryButton from '@/Components/SecondaryButton';
import TextInput from '@/Components/TextInput';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { useState } from 'react';

const templates = { hsbc: 'HSBC', azteca: 'Banco Azteca', custom: 'Por configurar' };

export default function Index({ banks, filters = {} }) {
    const [search, setSearch] = useState(filters.search || '');
    const [selectedBank, setSelectedBank] = useState(null);
    const { delete: destroy, processing } = useForm();

    const changeSearch = (event) => {
        const value = event.target.value;
        setSearch(value);
        router.get(route('banks.index'), { search: value }, { preserveState: true, replace: true });
    };

    const deleteBank = () => destroy(route('banks.destroy', selectedBank.id), { preserveScroll: true, onSuccess: () => setSelectedBank(null) });

    return (
        <AuthenticatedLayout header={<div className="flex items-center justify-between"><h2 className="text-xl font-semibold text-gray-800">Catálogo de Bancos</h2><Link href={route('banks.create')} className="rounded-md bg-blue-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-blue-700">Nuevo banco</Link></div>}>
            <Head title="Bancos" />
            <div className="py-12"><div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div className="mb-6"><TextInput value={search} onChange={changeSearch} className="w-full md:w-96" placeholder="Buscar por banco, cuenta o nombre..." /></div>
                <div className="overflow-x-auto bg-white shadow-sm sm:rounded-lg"><table className="min-w-full divide-y divide-gray-200"><thead className="bg-gray-50"><tr><th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Banco / cuenta</th><th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Formato</th><th className="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500">Movimientos</th><th className="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500">Estado</th><th className="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Acciones</th></tr></thead><tbody className="divide-y divide-gray-200 bg-white">
                    {banks.data.map((bank) => <tr key={bank.id}><td className="px-6 py-4"><div className="font-medium text-gray-900">{bank.name}</div><div className="text-sm text-gray-500">{bank.account_number}{bank.account_name ? ` · ${bank.account_name}` : ''}</div></td><td className="px-6 py-4 text-sm text-gray-600">{templates[bank.import_template]}</td><td className="px-6 py-4 text-center text-sm text-gray-600">{bank.movements_count}</td><td className="px-6 py-4 text-center"><span className={`inline-flex rounded-full px-2 text-xs font-semibold leading-5 ${bank.active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}`}>{bank.active ? 'Activo' : 'Inactivo'}</span></td><td className="px-6 py-4 text-right text-sm"><Link href={route('incomes.index', { bank_id: bank.id })} className="mr-3 text-emerald-600 hover:text-emerald-900 font-semibold">Ingresar</Link><Link href={route('banks.edit', bank.id)} className="mr-3 text-indigo-600 hover:text-indigo-900">Editar</Link><button onClick={() => setSelectedBank(bank)} className="text-red-600 hover:text-red-900">Eliminar</button></td></tr>)}
                    {!banks.data.length && <tr><td colSpan="5" className="px-6 py-10 text-center text-gray-500">Aún no hay bancos registrados.</td></tr>}
                </tbody></table></div><div className="mt-4"><Pagination links={banks.links} /></div>
            </div></div>
            <Modal show={Boolean(selectedBank)} onClose={() => setSelectedBank(null)}><div className="p-6"><h3 className="text-lg font-medium text-gray-900">¿Eliminar este banco?</h3><p className="mt-2 text-sm text-gray-600">Solo se podrá eliminar si todavía no tiene movimientos importados.</p><div className="mt-6 flex justify-end gap-3"><SecondaryButton onClick={() => setSelectedBank(null)}>Cancelar</SecondaryButton><DangerButton onClick={deleteBank} disabled={processing}>Eliminar</DangerButton></div></div></Modal>
        </AuthenticatedLayout>
    );
}
