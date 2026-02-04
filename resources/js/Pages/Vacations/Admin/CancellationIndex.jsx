import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import TextInput from '@/Components/TextInput';
import PrimaryButton from '@/Components/PrimaryButton';

export default function CancellationIndex({ auth, employees, filters }) {
    const { data, setData, get, processing } = useForm({
        search: filters.search || '',
    });

    const submit = (e) => {
        e.preventDefault();
        get(route('vacations.admin.cancellation'), { preserveState: true });
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Admin Vacaciones: Cancelar Periodos</h2>}
        >
            <Head title="Cancelar Periodos" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                        {/* Search Filter */}
                        <form onSubmit={submit} className="flex gap-4 mb-6">
                            <TextInput
                                id="search"
                                type="text"
                                className="mt-1 block w-full"
                                placeholder="Buscar por nombre o número de empleado..."
                                value={data.search}
                                onChange={(e) => setData('search', e.target.value)}
                            />
                            <PrimaryButton className="mt-1" disabled={processing}>
                                Buscar
                            </PrimaryButton>
                        </form>

                        <div className="overflow-x-auto">
                            <table className="min-w-full bg-white border border-gray-300">
                                <thead>
                                    <tr className="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                                        <th className="py-3 px-6 text-left">No. Empleado</th>
                                        <th className="py-3 px-6 text-left">Nombre</th>
                                        <th className="py-3 px-6 text-center">Periodos</th>
                                        <th className="py-3 px-6 text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody className="text-gray-600 text-sm font-light">
                                    {employees && employees.data && employees.data.map((empleado) => (
                                        <tr key={empleado.id} className="border-b border-gray-200 hover:bg-gray-50">
                                            <td className="py-3 px-6 text-left font-bold">{empleado.numero_empleado}</td>
                                            <td className="py-3 px-6 text-left">{empleado.nombre}</td>
                                            <td className="py-3 px-6 text-center">{empleado.periodos_count}</td>
                                            <td className="py-3 px-6 text-center">
                                                <Link
                                                    href={route('vacations.admin.periods', empleado.id)}
                                                    className="bg-red-500 text-white py-1 px-3 rounded hover:bg-red-600 transition duration-300"
                                                >
                                                    Gestionar Periodos
                                                </Link>
                                            </td>
                                        </tr>
                                    ))}

                                    {(!employees || !employees.data || employees.data.length === 0) && (
                                        <tr>
                                            <td colSpan="4" className="py-3 px-6 text-center">No se encontraron empleados.</td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>

                        {/* Pagination */}
                        <div className="mt-4 flex flex-wrap">
                            {employees && employees.links && employees.links.map((link, key) => (
                                link.url ? (
                                    <Link
                                        key={key}
                                        href={link.url}
                                        className={`px-3 py-1 border rounded mr-1 mb-1 ${link.active ? 'bg-blue-500 text-white' : 'bg-white text-gray-700'}`}
                                        dangerouslySetInnerHTML={{ __html: link.label }}
                                    />
                                ) : (
                                    <span
                                        key={key}
                                        className="px-3 py-1 border rounded mr-1 mb-1 bg-gray-100 text-gray-400 cursor-not-allowed"
                                        dangerouslySetInnerHTML={{ __html: link.label }}
                                    />
                                )
                            ))}
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
