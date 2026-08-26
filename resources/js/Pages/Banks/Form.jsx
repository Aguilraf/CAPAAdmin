import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';

export default function Form({ bank = null }) {
    const editing = Boolean(bank);
    const { data, setData, post, put, processing, errors } = useForm({
        name: bank?.name || '',
        account_number: bank?.account_number || '',
        account_name: bank?.account_name || '',
        currency: bank?.currency || 'MXN',
        import_template: bank?.import_template || 'custom',
        active: bank ? Boolean(bank.active) : true,
    });

    const submit = (event) => {
        event.preventDefault();
        if (editing) {
            put(route('banks.update', bank.id));
            return;
        }
        post(route('banks.store'));
    };

    return (
        <AuthenticatedLayout header={<div className="flex items-center justify-between"><h2 className="text-xl font-semibold text-gray-800">{editing ? 'Editar banco' : 'Nuevo banco'}</h2><Link href={route('banks.index')} className="rounded-md bg-gray-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-gray-700">Volver</Link></div>}>
            <Head title={editing ? 'Editar banco' : 'Nuevo banco'} />
            <div className="py-12"><div className="mx-auto max-w-3xl sm:px-6 lg:px-8"><div className="overflow-hidden bg-white shadow-sm sm:rounded-lg"><form onSubmit={submit} className="space-y-6 p-6">
                <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div><InputLabel htmlFor="name" value="Banco *" /><TextInput id="name" className="mt-1 block w-full" value={data.name} onChange={(event) => setData('name', event.target.value)} required autoFocus /><InputError className="mt-2" message={errors.name} /></div>
                    <div><InputLabel htmlFor="account_number" value="Número de cuenta *" /><TextInput id="account_number" className="mt-1 block w-full" value={data.account_number} onChange={(event) => setData('account_number', event.target.value)} required /><InputError className="mt-2" message={errors.account_number} /></div>
                    <div><InputLabel htmlFor="account_name" value="Nombre de la cuenta" /><TextInput id="account_name" className="mt-1 block w-full" value={data.account_name} onChange={(event) => setData('account_name', event.target.value)} placeholder="Ej. Cuenta de cheques CAPA" /><InputError className="mt-2" message={errors.account_name} /></div>
                    <div><InputLabel htmlFor="currency" value="Moneda *" /><select id="currency" value={data.currency} onChange={(event) => setData('currency', event.target.value)} className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"><option value="MXN">MXN — Peso mexicano</option><option value="USD">USD — Dólar estadounidense</option></select><InputError className="mt-2" message={errors.currency} /></div>
                    <div className="md:col-span-2"><InputLabel htmlFor="import_template" value="Formato de reporte bancario *" /><select id="import_template" value={data.import_template} onChange={(event) => setData('import_template', event.target.value)} className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"><option value="hsbc">HSBC</option><option value="azteca">Banco Azteca</option><option value="custom">Otro formato / por configurar</option></select><p className="mt-2 text-sm text-gray-500">Esta selección permitirá interpretar correctamente las columnas al importar movimientos.</p><InputError className="mt-2" message={errors.import_template} /></div>
                    <label className="flex items-center md:col-span-2"><input type="checkbox" checked={data.active} onChange={(event) => setData('active', event.target.checked)} className="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" /><span className="ml-2 text-sm text-gray-600">Banco activo</span></label>
                </div>
                <PrimaryButton disabled={processing}>{editing ? 'Actualizar banco' : 'Registrar banco'}</PrimaryButton>
            </form></div></div></div>
        </AuthenticatedLayout>
    );
}
