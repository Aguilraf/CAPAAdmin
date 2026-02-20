import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import InputLabel from '@/Components/InputLabel';
import TextInput from '@/Components/TextInput';
import InputError from '@/Components/InputError';
import PrimaryButton from '@/Components/PrimaryButton';

export default function ProviderForm({ provider = null, auth }) {
    const isEditing = !!provider;
    const { data, setData, post, put, processing, errors } = useForm({
        name: provider?.name || '',
        rfc: provider?.rfc || '',
        bank_name: provider?.bank_name || '',
        account_number: provider?.account_number || '',
        clabe: provider?.clabe || '',
        active: provider ? !!provider.active : true,
    });

    const submit = (e) => {
        e.preventDefault();
        if (isEditing) {
            put(route('providers.update', provider.id));
        } else {
            post(route('providers.store'));
        }
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={
                <div className="flex justify-between items-center">
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">
                        {isEditing ? 'Editar Proveedor' : 'Nuevo Proveedor'}
                    </h2>
                    <Link
                        href={route('providers.index')}
                        className="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        ← Volver
                    </Link>
                </div>
            }
        >
            <Head title={isEditing ? 'Editar Proveedor' : 'Nuevo Proveedor'} />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            <form onSubmit={submit} className="space-y-6">
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    {/* Nombre */}
                                    <div className="md:col-span-2">
                                        <InputLabel htmlFor="name" value="Nombre / Razón Social *" />
                                        <TextInput
                                            id="name"
                                            className="mt-1 block w-full"
                                            value={data.name}
                                            onChange={(e) => setData('name', e.target.value)}
                                            required
                                            autoFocus
                                        />
                                        <InputError message={errors.name} className="mt-2" />
                                    </div>

                                    {/* RFC */}
                                    <div>
                                        <InputLabel htmlFor="rfc" value="RFC" />
                                        <TextInput
                                            id="rfc"
                                            className="mt-1 block w-full"
                                            value={data.rfc}
                                            onChange={(e) => setData('rfc', e.target.value)}
                                        />
                                        <InputError message={errors.rfc} className="mt-2" />
                                    </div>

                                    {/* Banco */}
                                    <div>
                                        <InputLabel htmlFor="bank_name" value="Banco" />
                                        <TextInput
                                            id="bank_name"
                                            className="mt-1 block w-full"
                                            value={data.bank_name}
                                            onChange={(e) => setData('bank_name', e.target.value)}
                                        />
                                        <InputError message={errors.bank_name} className="mt-2" />
                                    </div>

                                    {/* Cuenta */}
                                    <div>
                                        <InputLabel htmlFor="account_number" value="Número de Cuenta" />
                                        <TextInput
                                            id="account_number"
                                            className="mt-1 block w-full"
                                            value={data.account_number}
                                            onChange={(e) => setData('account_number', e.target.value)}
                                        />
                                        <InputError message={errors.account_number} className="mt-2" />
                                    </div>

                                    {/* CLABE */}
                                    <div>
                                        <InputLabel htmlFor="clabe" value="CLABE Interbancaria" />
                                        <TextInput
                                            id="clabe"
                                            className="mt-1 block w-full"
                                            value={data.clabe}
                                            onChange={(e) => setData('clabe', e.target.value)}
                                        />
                                        <InputError message={errors.clabe} className="mt-2" />
                                    </div>

                                    {/* Activo */}
                                    <div className="flex items-center">
                                        <label className="flex items-center">
                                            <input
                                                type="checkbox"
                                                checked={data.active}
                                                onChange={(e) => setData('active', e.target.checked)}
                                                className="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500"
                                            />
                                            <span className="ml-2 text-sm text-gray-600">Proveedor Activo</span>
                                        </label>
                                    </div>
                                </div>

                                <div className="flex items-center gap-4 pt-4">
                                    <PrimaryButton disabled={processing}>
                                        {isEditing ? 'Actualizar Proveedor' : 'Crear Proveedor'}
                                    </PrimaryButton>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
