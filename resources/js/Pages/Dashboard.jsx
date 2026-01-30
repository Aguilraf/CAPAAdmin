import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, usePage } from '@inertiajs/react';

export default function Dashboard() {
    const user = usePage().props.auth.user;

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Dashboard
                </h2>
            }
        >
            <Head title="Dashboard" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                        {/* Tarjeta de Solicitar Material */}
                        {user.permissions && user.permissions.includes('generar reportes') && (
                            <Link
                                href={route('reportes.material-request.create')}
                                className="block group"
                            >
                                <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg border-2 border-transparent hover:border-indigo-500 transition-all duration-200 h-full">
                                    <div className="p-6">
                                        <div className="flex items-center justify-between mb-4">
                                            <div className="p-3 rounded-full bg-green-100 text-green-600 group-hover:bg-green-600 group-hover:text-white transition-colors duration-200">
                                                <svg xmlns="http://www.w3.org/2000/svg" className="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                            </div>
                                        </div>
                                        <h3 className="text-lg font-bold text-gray-900 group-hover:text-indigo-600 mb-2">
                                            Solicitar Material
                                        </h3>
                                        <p className="text-gray-500 text-sm">
                                            Crea una nueva solicitud de material para tu departamento.
                                        </p>
                                    </div>
                                </div>
                            </Link>
                        )}

                        {/* Tarjeta de Capturar Bomberos */}
                        {user.permissions && user.permissions.includes('capturar bomberos') && (
                            <Link
                                href="/firefighters/capture"
                                className="block group"
                            >
                                <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg border-2 border-transparent hover:border-red-500 transition-all duration-200 h-full">
                                    <div className="p-6">
                                        <div className="flex items-center justify-between mb-4">
                                            <div className="p-3 rounded-full bg-red-100 text-red-600 group-hover:bg-red-600 group-hover:text-white transition-colors duration-200">
                                                <svg xmlns="http://www.w3.org/2000/svg" className="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z" />
                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9.879 16.121A3 3 0 1012.015 11L11 14H9c0 .768.293 1.536.879 2.121z" />
                                                </svg>
                                            </div>
                                        </div>
                                        <h3 className="text-lg font-bold text-gray-900 group-hover:text-red-600 mb-2">
                                            Capturar Bomberos
                                        </h3>
                                        <p className="text-gray-500 text-sm">
                                            Registra las capturas mensuales de recaudación de bomberos.
                                        </p>
                                    </div>
                                </div>
                            </Link>
                        )}

                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
