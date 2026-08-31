import { useState } from 'react';
import { Head, router, usePage } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import PrimaryButton from '@/Components/PrimaryButton';

export default function Index({ invoices, prefixes, selectedPrefix }) {
    const { flash } = usePage().props;
    const [file, setFile] = useState(null);

    const upload = (e) => {
        e.preventDefault();
        const formData = new FormData();
        formData.append('file', file);

        router.post(route('invoices.upload'), formData, {
            forceFormData: true,
            onSuccess: () => setFile(null)
        });
    };

    return (
        <AuthenticatedLayout header={<h2 className="text-xl font-semibold text-gray-800">Facturas</h2>}>
            <Head title="Facturas" />

            <div className="py-8">
                <div className="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                    
                    {/* Formulario de Subida */}
                    <div className="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div className="flex justify-between items-center mb-4">
                            <h3 className="font-semibold text-gray-700">Importar Facturas/Complementos</h3>
                            <a 
                                href={route('invoices.template')} 
                                className="inline-flex items-center px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs uppercase tracking-widest rounded-md border border-slate-300 transition duration-150 ease-in-out"
                            >
                                Descargar Plantilla CSV
                            </a>
                        </div>
                        <form onSubmit={upload} className="flex items-center gap-4">
                            <input 
                                type="file" 
                                onChange={e => setFile(e.target.files[0])} 
                                className="text-sm border border-gray-300 rounded-md p-2 bg-slate-50" 
                                required 
                                accept=".csv"
                            />
                            <PrimaryButton type="submit">IMPORTAR</PrimaryButton>
                        </form>
                    </div>

                    {/* Filtros por Prefijo */}
                    <div className="flex gap-2">
                        <a href={route('invoices.index')} className={`px-4 py-2 rounded-full text-sm ${!selectedPrefix ? 'bg-blue-600 text-white' : 'bg-gray-200'}`}>Todos</a>
                        {prefixes.map(p => (
                            <a key={p} href={route('invoices.index', { prefix: p })} className={`px-4 py-2 rounded-full text-sm ${selectedPrefix === p ? 'bg-blue-600 text-white' : 'bg-gray-200'}`}>{p}</a>
                        ))}
                    </div>

                    {/* Tabla */}
                    <div className="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                        <table className="w-full text-left text-sm">
                            <thead className="bg-slate-50 text-slate-500 uppercase text-[10px] tracking-wider">
                                <tr>
                                    <th className="px-6 py-4">Factura</th>
                                    <th className="px-6 py-4">Fecha</th>
                                    <th className="px-6 py-4">Tipo</th>
                                    <th className="px-6 py-4">Status</th>
                                    <th className="px-6 py-4 text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                {invoices.length === 0 ? (
                                    <tr>
                                        <td colSpan="5" className="px-6 py-8 text-center text-gray-400">
                                            No hay facturas cargadas. Descarga la plantilla superior, ingresa tus datos y súbela en formato CSV.
                                        </td>
                                    </tr>
                                ) : (
                                    invoices.map(invoice => (
                                        <tr key={invoice.id} className="border-t border-slate-100 hover:bg-slate-50">
                                            <td className="px-6 py-4 font-medium">{invoice.numero_factura}</td>
                                            <td className="px-6 py-4">{invoice.fecha}</td>
                                            <td className="px-6 py-4">{invoice.tipo}</td>
                                            <td className="px-6 py-4"><span className={`px-2 py-1 rounded text-[10px] ${invoice.status === 'Pagado' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'}`}>{invoice.status}</span></td>
                                            <td className="px-6 py-4 text-right">{invoice.total}</td>
                                        </tr>
                                    ))
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

