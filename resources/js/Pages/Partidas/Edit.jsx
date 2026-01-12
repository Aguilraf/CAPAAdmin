import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import PartidaForm from './Form';

export default function Edit({ partida, capitulos }) {
    const { data, setData, put, processing, errors } = useForm({
        capitulo_id: partida.capitulo_id,
        codigo: partida.codigo,
        nombre: partida.nombre,
        descripcion: partida.descripcion || '',
        activo: Boolean(partida.activo),
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        put(route('partidas.update', partida.id));
    };

    return (
        <AuthenticatedLayout
            header={
                <div className="flex justify-between items-center">
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">
                        Editar Partida
                    </h2>
                    <Link
                        href={route('partidas.index')}
                        className="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        ← Volver
                    </Link>
                </div>
            }
        >
            <Head title="Editar Partida" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            <PartidaForm
                                data={data}
                                setData={setData}
                                errors={errors}
                                processing={processing}
                                submit={handleSubmit}
                                isEditing={true}
                                capitulos={capitulos}
                            />
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
