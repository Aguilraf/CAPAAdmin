import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import RequirementForm from './Form';

export default function Create({ auth, nextNumber, year, employees, capitulos, partidas, vehicles, types, defaultSignatories, defaultLegend, travelAllowanceRates, defaultMonths, monthsList, defaultBomberos }) {
    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Nuevo Requerimiento</h2>}
        >
            <Head title="Nuevo Requerimiento" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900">
                            <div className="mb-6">
                                <Link href={route('requirements.index')} className="text-gray-500 hover:text-gray-700">← Regresar</Link>
                            </div>

                            <RequirementForm
                                mode="create"
                                nextNumber={nextNumber}
                                year={year}
                                employees={employees}
                                capitulos={capitulos}
                                partidas={partidas}
                                vehicles={vehicles} // Pass vehicles
                                types={types}
                                defaultSignatories={defaultSignatories}
                                defaultLegend={defaultLegend}
                                travelAllowanceRates={travelAllowanceRates}
                                defaultMonths={defaultMonths}
                                monthsList={monthsList}
                                defaultBomberos={defaultBomberos}
                            />
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
