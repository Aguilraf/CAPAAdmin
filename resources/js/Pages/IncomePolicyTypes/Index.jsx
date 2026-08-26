import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import InputError from '@/Components/InputError';
import PrimaryButton from '@/Components/PrimaryButton';
import { Head, router, useForm, usePage } from '@inertiajs/react';
import { useState } from 'react';

export default function Index({ types }) {
    const { flash } = usePage().props;
    const [editingId, setEditingId] = useState(null);
    const { data, setData, post, patch, processing, errors, reset } = useForm({ name: '' });

    const submit = (event) => {
        event.preventDefault();
        if (editingId) {
            patch(route('income-policy-types.update', editingId), { onSuccess: () => { setEditingId(null); reset(); } });
            return;
        }
        post(route('income-policy-types.store'), { onSuccess: () => reset() });
    };

    const edit = (type) => {
        setEditingId(type.id);
        setData('name', type.name);
    };

    const cancel = () => {
        setEditingId(null);
        reset();
    };

    const remove = (id) => {
        if (confirm('¿Deseas eliminar este tipo de póliza?')) {
            router.delete(route('income-policy-types.destroy', id), { preserveScroll: true });
        }
    };

    return (
        <AuthenticatedLayout header={<h2 className="text-xl font-semibold text-gray-800">Catálogo de tipos de póliza</h2>}>
            <Head title="Tipos de póliza" />
            <div className="py-8">
                <div className="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                    <div className="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                        <div className="border-b border-slate-200 bg-slate-50 px-6 py-5">
                            <h3 className="text-lg font-semibold text-slate-800">{editingId ? 'Editar tipo de póliza' : 'Agregar tipo de póliza'}</h3>
                            <p className="mt-1 text-sm text-slate-500">Estas opciones aparecerán en el selector de la póliza.</p>
                        </div>
                        {flash?.success && <div className="mx-6 mt-5 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{flash.success}</div>}
                        <form onSubmit={submit} className="flex flex-col gap-4 p-6 sm:flex-row sm:items-end">
                            <div className="flex-1">
                                <label htmlFor="name" className="block text-sm font-medium text-slate-700">Nombre del tipo *</label>
                                <input id="name" value={data.name} onChange={(event) => setData('name', event.target.value)} className="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required />
                                <InputError className="mt-2" message={errors.name} />
                            </div>
                            <PrimaryButton disabled={processing}>{editingId ? 'Guardar cambios' : 'Agregar tipo'}</PrimaryButton>
                            {editingId && <button type="button" onClick={cancel} className="rounded-md border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700">Cancelar</button>}
                        </form>
                    </div>

                    <div className="mt-6 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                        <div className="border-b border-slate-200 px-6 py-4"><h3 className="font-semibold text-slate-800">Tipos registrados</h3></div>
                        <div className="divide-y divide-slate-200">
                            {types.length === 0 ? <div className="px-6 py-8 text-center text-sm text-slate-500">No hay tipos registrados.</div> : types.map((type) => <div key={type.id} className="flex items-center justify-between gap-4 px-6 py-4"><span className="font-medium text-slate-800">{type.name}</span><div className="flex gap-2"><button type="button" onClick={() => edit(type)} className="rounded-md border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700">Editar</button><button type="button" onClick={() => remove(type.id)} className="rounded-md border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700">Eliminar</button></div></div>)}
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
