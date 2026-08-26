import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';

export default function Index({ auth, requirements, items, selectedRequirement, filters }) {

    const handleRequirementChange = (e) => {
        router.get(route('reportes.revolvente.index'), {
            requirement_id: e.target.value
        }, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const handleExport = () => {
        if (!selectedRequirement) return;
        window.location.href = route('reportes.revolvente.export', { requirement_id: selectedRequirement.id });
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Reporte de Fondo Revolvente</h2>}
        >
            <Head title="Reporte Revolvente" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg border-t-4 border-indigo-600">
                        <div className="p-6 text-gray-900">

                            <div className="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
                                <div className="w-full md:w-1/2">
                                    <label className="block text-sm font-bold text-gray-700 mb-1 uppercase tracking-wider">
                                        Seleccionar Requerimiento
                                    </label>
                                    <select
                                        value={filters.requirement_id || ''}
                                        onChange={handleRequirementChange}
                                        className="w-full bg-gray-50 border border-gray-300 rounded-lg px-4 py-2.5 text-sm font-medium focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all shadow-sm"
                                    >
                                        <option value="">-- Seleccione un requerimiento --</option>
                                        {requirements.map(req => (
                                            <option key={req.id} value={req.id}>
                                                #{req.revolving_fund_number || 'S/N'} - {req.year} ({req.description?.substring(0, 50)}...)
                                            </option>
                                        ))}
                                    </select>
                                </div>

                                {selectedRequirement && (
                                    <div className="flex gap-2 w-full md:w-auto">
                                        <button
                                            onClick={handleExport}
                                            className="flex-1 md:flex-none inline-flex items-center justify-center px-4 py-2.5 bg-green-600 border border-transparent rounded-lg font-bold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-all shadow-md"
                                        >
                                            <svg className="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                            Exportar Excel
                                        </button>

                                        <div className="flex-1 md:flex-none flex gap-2">
                                            <a
                                                href={route('requirements.revolvente-cedula', selectedRequirement.id)}
                                                target="_blank"
                                                className="inline-flex items-center justify-center px-4 py-2.5 bg-orange-600 border border-transparent rounded-lg font-bold text-xs text-white uppercase tracking-widest hover:bg-orange-700 transition-all shadow-md"
                                                title="Descargar Cédula"
                                            >
                                                Cédula PDF
                                            </a>
                                            <a
                                                href={route('requirements.revolvente-anexo4', selectedRequirement.id)}
                                                target="_blank"
                                                className="inline-flex items-center justify-center px-4 py-2.5 bg-teal-600 border border-transparent rounded-lg font-bold text-xs text-white uppercase tracking-widest hover:bg-teal-700 transition-all shadow-md"
                                                title="Descargar Anexo 4"
                                            >
                                                Anexo 4 PDF
                                            </a>
                                        </div>
                                    </div>
                                )}
                            </div>

                            {selectedRequirement ? (
                                <div className="space-y-6">
                                    {/* Requirement Info Summary */}
                                    <div className="grid grid-cols-1 md:grid-cols-4 gap-4 p-4 bg-indigo-50 rounded-xl border border-indigo-100">
                                        <div className="space-y-1">
                                            <p className="text-[10px] font-black text-indigo-400 uppercase tracking-widest">Fondo Revolvente No.</p>
                                            <p className="text-lg font-black text-indigo-900">{selectedRequirement.revolving_fund_number}</p>
                                        </div>
                                        <div className="space-y-1 md:col-span-2">
                                            <p className="text-[10px] font-black text-indigo-400 uppercase tracking-widest">Descripción</p>
                                            <p className="text-sm font-medium text-indigo-800 line-clamp-2">{selectedRequirement.description}</p>
                                        </div>
                                        <div className="space-y-1 text-right">
                                            <p className="text-[10px] font-black text-indigo-400 uppercase tracking-widest">Total Requerimiento</p>
                                            <p className="text-xl font-black text-indigo-900">${Number(selectedRequirement.total).toLocaleString('es-MX', { minimumFractionDigits: 2 })}</p>
                                        </div>
                                    </div>

                                    {/* Items Table */}
                                    <div className="overflow-x-auto rounded-xl border border-gray-200">
                                        <table className="w-full text-left border-collapse">
                                            <thead>
                                                <tr className="bg-gray-50 border-b border-gray-200">
                                                    <th className="px-4 py-3 text-[10px] font-black text-gray-400 uppercase tracking-widest">Folio / Fecha</th>
                                                    <th className="px-4 py-3 text-[10px] font-black text-gray-400 uppercase tracking-widest">UUID (Folio Fiscal)</th>
                                                    <th className="px-4 py-3 text-[10px] font-black text-gray-400 uppercase tracking-widest">Proveedor</th>
                                                    <th className="px-4 py-3 text-[10px] font-black text-gray-400 uppercase tracking-widest whitespace-nowrap">Partida</th>
                                                    <th className="px-4 py-3 text-[10px] font-black text-gray-400 uppercase tracking-widest">Concepto</th>
                                                    <th className="px-4 py-3 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">Subtotal</th>
                                                    <th className="px-4 py-3 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">IVA</th>
                                                    <th className="px-4 py-3 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest whitespace-nowrap">Total</th>
                                                </tr>
                                            </thead>
                                            <tbody className="divide-y divide-gray-100 uppercase">
                                                {items.map((item, idx) => (
                                                    <tr key={item.id} className="hover:bg-gray-50/50 transition-colors">
                                                        <td className="px-4 py-3">
                                                            <div className="font-bold text-gray-900 text-xs">{item.invoice_folio}</div>
                                                            <div className="text-[10px] text-gray-500">{item.invoice_date ? new Date(item.invoice_date).toLocaleDateString('es-MX') : '-'}</div>
                                                        </td>
                                                        <td className="px-4 py-3">
                                                            <div className="text-[10px] font-mono text-gray-500 break-all max-w-[150px]">
                                                                {item.uuid || '-'}
                                                            </div>
                                                        </td>
                                                        <td className="px-4 py-3">
                                                            <div className="font-bold text-gray-900 text-[11px] leading-tight">{item.provider_name}</div>
                                                            <div className="text-[10px] font-mono text-gray-400">{item.provider_rfc}</div>
                                                        </td>
                                                        <td className="px-4 py-3">
                                                            <div className="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-black bg-indigo-50 text-indigo-700 border border-indigo-100">
                                                                {item.partida?.codigo}
                                                            </div>
                                                        </td>
                                                        <td className="px-4 py-3 text-[10px] font-medium text-gray-600 max-w-xs truncate">
                                                            {item.description}
                                                        </td>
                                                        <td className="px-4 py-3 text-right font-medium text-gray-600 text-xs">
                                                            {Number(item.invoice_subtotal).toLocaleString('es-MX', { minimumFractionDigits: 2 })}
                                                        </td>
                                                        <td className="px-4 py-3 text-right font-medium text-gray-400 text-xs">
                                                            {Number(item.invoice_iva).toLocaleString('es-MX', { minimumFractionDigits: 2 })}
                                                        </td>
                                                        <td className="px-4 py-3 text-right font-black text-gray-900 text-xs">
                                                            {Number(item.invoice_total).toLocaleString('es-MX', { minimumFractionDigits: 2 })}
                                                        </td>
                                                    </tr>
                                                ))}
                                            </tbody>
                                            <tfoot className="bg-gray-50 border-t-2 border-gray-200">
                                                <tr>
                                                    <td colSpan="6" className="px-4 py-3 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">Suma Total Comprobaciones</td>
                                                    <td className="px-4 py-3 text-right font-black text-indigo-700 text-sm">
                                                        ${Number(selectedRequirement.total).toLocaleString('es-MX', { minimumFractionDigits: 2 })}
                                                    </td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            ) : (
                                <div className="py-20 text-center bg-gray-50 rounded-xl border border-dashed border-gray-200">
                                    <div className="inline-flex items-center justify-center w-16 h-16 rounded-full bg-white shadow-sm mb-4">
                                        <svg className="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    </div>
                                    <p className="text-gray-400 font-medium">Seleccione un requerimiento de fondo revolvente para ver los detalles.</p>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
