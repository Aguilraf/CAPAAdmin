import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import { Transition } from '@headlessui/react';
import { useForm, usePage } from '@inertiajs/react';

export default function UpdateProfileInformation({
    className = '',
}) {
    const user = usePage().props.auth.user;
    const { empleados } = usePage().props;

    const { data, setData, patch, errors, processing, recentlySuccessful } =
        useForm({
            empleado_id: user.empleado_id || '',
        });

    const handleEmpleadoChange = (e) => {
        const empleadoId = e.target.value;
        setData('empleado_id', empleadoId);
    };

    const submit = (e) => {
        e.preventDefault();
        patch(route('profile.update'));
    };

    // Get selected employee info
    const selectedEmpleado = empleados?.find(emp => emp.id == data.empleado_id);

    return (
        <section className={className}>
            <header>
                <h2 className="text-lg font-medium text-gray-900">
                    Información del Perfil
                </h2>

                <p className="mt-1 text-sm text-gray-600">
                    Vincula tu cuenta con un empleado del catálogo. El nombre de usuario se actualizará automáticamente.
                </p>
            </header>

            <form onSubmit={submit} className="mt-6 space-y-6">
                <div>
                    <InputLabel htmlFor="empleado_id" value="Empleado Asociado" />

                    <select
                        id="empleado_id"
                        className="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                        value={data.empleado_id}
                        onChange={handleEmpleadoChange}
                    >
                        <option value="">Sin empleado asociado</option>
                        {empleados && empleados.map((empleado) => (
                            <option key={empleado.id} value={empleado.id}>
                                {empleado.nombre} - {empleado.puesto}
                            </option>
                        ))}
                    </select>

                    <InputError className="mt-2" message={errors.empleado_id} />
                </div>

                {selectedEmpleado && (
                    <div className="p-4 bg-blue-50 border border-blue-200 rounded-md">
                        <p className="text-sm text-blue-800">
                            <span className="font-semibold">Nombre del usuario:</span> {selectedEmpleado.nombre}
                        </p>
                        <p className="text-sm text-blue-800 mt-1">
                            <span className="font-semibold">Puesto:</span> {selectedEmpleado.puesto}
                        </p>
                        <p className="text-xs text-blue-600 mt-2">
                            Al guardar, tu nombre de usuario se actualizará con estos datos.
                        </p>
                    </div>
                )}

                <div className="flex items-center gap-4">
                    <PrimaryButton disabled={processing}>Guardar</PrimaryButton>

                    <Transition
                        show={recentlySuccessful}
                        enter="transition ease-in-out"
                        enterFrom="opacity-0"
                        leave="transition ease-in-out"
                        leaveTo="opacity-0"
                    >
                        <p className="text-sm text-gray-600">
                            Guardado.
                        </p>
                    </Transition>
                </div>
            </form>
        </section>
    );
}
