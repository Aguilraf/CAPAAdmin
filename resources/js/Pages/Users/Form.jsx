import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';
import InputLabel from '@/Components/InputLabel';
import TextInput from '@/Components/TextInput';
import InputError from '@/Components/InputError';
import PrimaryButton from '@/Components/PrimaryButton';

export default function UserForm({ auth, user, roles, currentRole }) {
    const isEditing = !!user;

    const { data, setData, post, put, processing, errors, reset } = useForm({
        name: user ? user.name : '',
        username: user ? (user.username || '') : '',
        email: user ? user.email : '',
        password: '',
        password_confirmation: '',
        role: currentRole || (roles.length > 0 ? roles[0].name : ''),
    });

    const submit = (e) => {
        e.preventDefault();

        if (isEditing) {
            put(route('users.update', user.id));
        } else {
            post(route('users.store'));
        }
    };

    return (
        <AuthenticatedLayout
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">{isEditing ? 'Editar Usuario' : 'Crear Usuario'}</h2>}
        >
            <Head title={isEditing ? 'Editar Usuario' : 'Crear Usuario'} />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900">
                            <form onSubmit={submit} className="max-w-xl">
                                <div className="mb-4">
                                    <InputLabel htmlFor="name" value="Nombre" />
                                    <TextInput
                                        id="name"
                                        type="text"
                                        className="mt-1 block w-full"
                                        value={data.name}
                                        onChange={(e) => setData('name', e.target.value)}
                                        required
                                        autoFocus
                                        autoComplete="name"
                                    />
                                    <InputError className="mt-2" message={errors.name} />
                                </div>

                                <div className="mb-4">
                                    <InputLabel htmlFor="username" value="Nombre de usuario" />
                                    <TextInput
                                        id="username"
                                        type="text"
                                        className="mt-1 block w-full"
                                        value={data.username}
                                        onChange={(e) => setData('username', e.target.value)}
                                        required
                                        autoComplete="username"
                                    />
                                    <InputError className="mt-2" message={errors.username} />
                                </div>

                                <div className="mb-4">
                                    <InputLabel htmlFor="email" value="Email" />
                                    <TextInput
                                        id="email"
                                        type="email"
                                        className="mt-1 block w-full"
                                        value={data.email}
                                        onChange={(e) => setData('email', e.target.value)}
                                        required
                                        autoComplete="username"
                                    />
                                    <InputError className="mt-2" message={errors.email} />
                                </div>

                                <div className="mb-4">
                                    <InputLabel htmlFor="role" value="Rol" />
                                    <select
                                        id="role"
                                        className="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        value={data.role}
                                        onChange={(e) => setData('role', e.target.value)}
                                        required
                                    >
                                        <option value="">Seleccione un rol</option>
                                        {roles.map((role) => (
                                            <option key={role.id} value={role.name}>{role.name}</option>
                                        ))}
                                    </select>
                                    <InputError className="mt-2" message={errors.role} />
                                </div>

                                <div className="mb-4">
                                    <InputLabel htmlFor="password" value={isEditing ? "Nueva Contraseña (Dejar en blanco para mantener)" : "Contraseña"} />
                                    <TextInput
                                        id="password"
                                        type="password"
                                        className="mt-1 block w-full"
                                        value={data.password}
                                        onChange={(e) => setData('password', e.target.value)}
                                        required={!isEditing}
                                        autoComplete="new-password"
                                    />
                                    <InputError className="mt-2" message={errors.password} />
                                </div>

                                <div className="mb-4">
                                    <InputLabel htmlFor="password_confirmation" value="Confirmar Contraseña" />
                                    <TextInput
                                        id="password_confirmation"
                                        type="password"
                                        className="mt-1 block w-full"
                                        value={data.password_confirmation}
                                        onChange={(e) => setData('password_confirmation', e.target.value)}
                                        required={!isEditing}
                                        autoComplete="new-password"
                                    />
                                    <InputError className="mt-2" message={errors.password_confirmation} />
                                </div>

                                <div className="flex items-center justify-end mt-4">
                                    <PrimaryButton className="ml-4" disabled={processing}>
                                        {isEditing ? 'Actualizar' : 'Crear'}
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
