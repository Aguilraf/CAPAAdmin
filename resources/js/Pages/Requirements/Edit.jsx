import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import RequirementForm from './Form';

export default function Edit({ auth, requirement, employees, capitulos, partidas, vehicles, types, defaultLegend, travelAllowanceRates }) {
    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Editar Requerimiento</h2>}
        >
            <Head title="Editar Requerimiento" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900">
                            <div className="mb-6">
                                <Link href={route('requirements.index')} className="text-gray-500 hover:text-gray-700">← Regresar</Link>
                            </div>

                            <RequirementForm
                                mode="edit"
                                initialData={requirement}
                                employees={employees}
                                capitulos={capitulos}
                                partidas={partidas}
                                vehicles={vehicles}
                                types={types}
                                defaultLegend={defaultLegend}
                                travelAllowanceRates={travelAllowanceRates}
                            />
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
