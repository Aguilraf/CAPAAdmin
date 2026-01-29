import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';
import InputLabel from '@/Components/InputLabel';
import TextInput from '@/Components/TextInput';
import InputError from '@/Components/InputError';
import PrimaryButton from '@/Components/PrimaryButton';
import Checkbox from '@/Components/Checkbox';

export default function RoleForm({ role, permissions, rolePermissions = [] }) {
    const isEditing = !!role;

    const { data, setData, post, put, processing, errors } = useForm({
        name: role ? role.name : '',
        permissions: rolePermissions, // Array of permission names or IDs? Controller sends names.
    });

    const handlePermissionChange = (permissionName, checked) => {
        if (checked) {
            setData('permissions', [...data.permissions, permissionName]);
        } else {
            setData('permissions', data.permissions.filter(p => p !== permissionName));
        }
    };

    const submit = (e) => {
        e.preventDefault();
        if (isEditing) {
            put(route('roles.update', role.id));
        } else {
            post(route('roles.store'));
        }
    };

    return (
        <AuthenticatedLayout
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">{isEditing ? 'Editar Rol' : 'Crear Rol'}</h2>}
        >
            <Head title={isEditing ? 'Editar Rol' : 'Crear Rol'} />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900">
                            <form onSubmit={submit}>
                                <div className="mb-6">
                                    <InputLabel htmlFor="name" value="Nombre del Rol" />
                                    <TextInput
                                        id="name"
                                        type="text"
                                        className="mt-1 block w-full"
                                        value={data.name}
                                        onChange={(e) => setData('name', e.target.value)}
                                        required
                                        autoFocus
                                    />
                                    <InputError className="mt-2" message={errors.name} />
                                </div>

                                <div className="mb-6">
                                    <h3 className="text-lg font-medium text-gray-900 mb-4">Permisos</h3>

                                    {/* Agrupar permisos por categoría */}
                                    {(() => {
                                        const categories = {
                                            'Usuarios': permissions.filter(p => p.name.includes('usuarios')),
                                            'Roles': permissions.filter(p => p.name.includes('roles')),
                                            'Reportes': permissions.filter(p => p.name.includes('reportes') && !p.name.includes('bomberos')),
                                            'Bomberos': permissions.filter(p =>
                                                p.name.includes('bomberos') ||
                                                p.name.includes('comunidades') ||
                                                p.name === 'capturar bomberos' ||
                                                p.name === 'recibir bomberos' ||
                                                p.name === 'reportes bomberos' ||
                                                p.name === 'configurar bomberos' ||
                                                p.name === 'importar bomberos'
                                            ),
                                        };

                                        return Object.entries(categories).map(([category, perms]) => {
                                            if (perms.length === 0) return null;

                                            return (
                                                <div key={category} className="mb-6 p-4 border border-gray-200 rounded-lg bg-gray-50">
                                                    <h4 className="font-semibold text-gray-800 mb-3 text-sm uppercase tracking-wide">{category}</h4>
                                                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                                        {perms.map((permission) => (
                                                            <label key={permission.id} className="flex items-center space-x-2 p-2 bg-white border rounded hover:bg-gray-50 cursor-pointer">
                                                                <Checkbox
                                                                    checked={data.permissions.includes(permission.name)}
                                                                    onChange={(e) => handlePermissionChange(permission.name, e.target.checked)}
                                                                />
                                                                <span className="text-gray-700 text-sm">{permission.name}</span>
                                                            </label>
                                                        ))}
                                                    </div>
                                                </div>
                                            );
                                        });
                                    })()}

                                    <InputError className="mt-2" message={errors.permissions} />
                                </div>

                                <div className="flex items-center justify-end mt-4">
                                    <PrimaryButton className="ml-4" disabled={processing}>
                                        {isEditing ? 'Actualizar Rol' : 'Crear Rol'}
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
