import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';

export default function Index() {
    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Reportes
                </h2>
            }
        >
            <Head title="Reportes" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900">
                            <h3 className="text-lg font-medium mb-4">Disponibles</h3>

                            <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                                {/* Card for Material Request */}
                                <Link
                                    href={route('reportes.material-request.create')}
                                    className="block p-6 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors shadow-sm"
                                >
                                    <h4 className="font-bold text-indigo-600 mb-2">Solicitud de Material</h4>
                                    <p className="text-sm text-gray-600">
                                        Generar formato oficial de solicitud de material de oficina para Recursos Financieros.
                                    </p>
                                    <div className="mt-4 flex items-center text-sm font-medium text-indigo-500">
                                        Crear Solicitud &rarr;
                                    </div>
                                </Link>

                                {/* Card for History */}
                                <Link
                                    href={route('reportes.historial')}
                                    className="block p-6 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors shadow-sm"
                                >
                                    <h4 className="font-bold text-green-600 mb-2">Historial de Reportes</h4>
                                    <p className="text-sm text-gray-600">
                                        Consultar la bitácora de reportes generados, buscar por fecha o solicitante.
                                    </p>
                                    <div className="mt-4 flex items-center text-sm font-medium text-green-500">
                                        Ver Historial &rarr;
                                    </div>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
