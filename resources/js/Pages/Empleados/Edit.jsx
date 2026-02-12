import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import EmpleadoForm from './Form';

export default function Edit({ empleado, posiblesJefes, previousEmployeeId, nextEmployeeId }) {
    const handleSubmit = (data) => {
        // Use router for PUT with proper data format
        window.location.href = route('empleados.index');
    };

    return (
        <AuthenticatedLayout
            header={
                <div className="flex justify-between items-center">
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">
                        Editar Empleado: {empleado.nombre}
                    </h2>
                    <div className="flex gap-2">
                        {previousEmployeeId && (
                            <Link
                                href={route('empleados.edit', previousEmployeeId)}
                                className="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
                            >
                                ← Anterior
                            </Link>
                        )}
                        {nextEmployeeId && (
                            <Link
                                href={route('empleados.edit', nextEmployeeId)}
                                className="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
                            >
                                Siguiente →
                            </Link>
                        )}
                        <Link
                            href={route('empleados.index')}
                            className="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
                        >
                            ← Volver
                        </Link>
                    </div>
                </div>
            }
        >
            <Head title={`Editar Empleado: ${empleado.nombre}`} />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            <EmpleadoForm
                                empleado={empleado}
                                submitUrl={route('empleados.update', empleado.id)}
                                submitMethod="put"
                                posiblesJefes={posiblesJefes}
                            />
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
