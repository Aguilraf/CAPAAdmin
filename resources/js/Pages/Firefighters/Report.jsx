import React, { useEffect } from 'react';
import { router } from '@inertiajs/react';
import { formatCurrency } from '../../firefighters_helpers';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';

export default function Report({ auth, requirements, captures, filters, settings }) {
    // Derived Data
    const availableYears = [...new Set(requirements.map(r => r.year?.toString()).filter(Boolean))];
    const selectedYear = filters.year || '';
    const selectedRequirement = filters.requirement_number || '';

    const filteredRequirementsInYear = requirements.filter(r => r.year?.toString() === selectedYear);

    // Auto-select defaults if no filters present
    useEffect(() => {
        if (!selectedYear && availableYears.length > 0) {
            const defaultYear = availableYears[0];
            const reqsInYear = requirements.filter(r => r.year?.toString() === defaultYear);
            const defaultReq = reqsInYear.length > 0 ? reqsInYear[0].requirement_number : '';

            if (defaultYear && defaultReq) {
                router.get(route('firefighters.report'), {
                    year: defaultYear,
                    requirement_number: defaultReq
                }, { replace: true });
            }
        }
    }, []); // Run once on mount

    const handleYearChange = (e) => {
        const newYear = e.target.value;
        const reqs = requirements.filter(r => r.year?.toString() === newYear);
        const newReq = reqs.length > 0 ? reqs[0].requirement_number : '';

        router.get(route('firefighters.report'), {
            year: newYear,
            requirement_number: newReq
        }, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const handleRequirementChange = (e) => {
        router.get(route('firefighters.report'), {
            year: selectedYear,
            requirement_number: e.target.value
        }, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const downloadPDF = () => {
        if (!selectedYear || !selectedRequirement) return;
        // PDF download still uses a direct link/window.open because it's a file download
        window.location.href = `/report/firefighters/pdf?year=${selectedYear}&requirement_number=${selectedRequirement}&requirement_type=bomberos`;
    };

    // Calculations
    const totalSubtotal = captures.reduce((acc, c) => acc + parseFloat(c.subtotal || 0), 0);
    const totalCommission = captures.reduce((acc, c) => acc + parseFloat(c.commission || 0), 0);
    const totalTotal = captures.reduce((acc, c) => acc + parseFloat(c.total || 0), 0);

    const defaultTitle = "COMISION DE AGUA POTABLE Y ALCANTARILLADO";
    const defaultSubtitle = "ORGANISMO OPERADOR : JOSE MARIA MORELOS";
    const defaultLocationDate = `JOSE MARIA MORELOS, QUINTANA ROO; A ${new Date().toLocaleDateString('es-MX', { day: 'numeric', month: 'long', year: 'numeric' }).toUpperCase()}`;

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Reporte de Bomberos</h2>}
        >
            <Head title="Reporte" />

            <div className="max-w-7xl mx-auto p-0 md:p-4 min-h-screen bg-gray-50 print:bg-white print:p-0" style={{ fontFamily: '"Montserrat", sans-serif' }}>
                <div className="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-6 gap-4 print:hidden">
                    <div>
                        <h2 className="text-2xl font-bold text-gray-800">Reporte de Bomberos</h2>
                        <p className="text-sm text-gray-500">Seleccione el año y requerimiento</p>
                    </div>

                    <div className="flex flex-col md:flex-row items-center gap-3 w-full lg:w-auto">
                        {/* Year Selector */}
                        <div className="w-full md:w-32">
                            <select
                                value={selectedYear}
                                onChange={handleYearChange}
                                className="w-full bg-white border border-gray-300 rounded-lg px-4 py-2 text-sm font-bold text-gray-700 shadow-sm focus:ring-2 focus:ring-red-500"
                            >
                                {availableYears.map(year => (
                                    <option key={year} value={year}>Año: {year}</option>
                                ))}
                            </select>
                        </div>

                        {/* Requirement Selector */}
                        <div className="w-full md:w-64">
                            <select
                                value={selectedRequirement}
                                onChange={handleRequirementChange}
                                className="w-full bg-white border border-gray-300 rounded-lg px-4 py-2 text-sm font-bold text-gray-700 shadow-sm focus:ring-2 focus:ring-red-500"
                            >
                                {filteredRequirementsInYear.length > 0 ? filteredRequirementsInYear.map(req => (
                                    <option key={`${req.year}-${req.requirement_number}`} value={req.requirement_number}>
                                        Req: {req.requirement_number}
                                    </option>
                                )) : (
                                    <option value="">No hay requerimientos</option>
                                )}
                            </select>
                        </div>

                        <button
                            onClick={downloadPDF}
                            disabled={!selectedRequirement || captures.length === 0}
                            className={`w-full md:w-auto px-6 py-2 bg-red-600 text-white font-black uppercase tracking-wider text-sm rounded-lg hover:bg-red-700 transition-all shadow-lg active:scale-95 flex items-center justify-center gap-2 ${(!selectedRequirement || captures.length === 0) ? 'opacity-50 cursor-not-allowed' : ''}`}
                        >
                            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            Descargar PDF
                        </button>
                    </div>
                </div>

                {/* Official Report Container */}
                <div className="bg-white p-8 md:p-12 shadow-2xl print:shadow-none print:p-8 border-t-8 border-red-700">

                    {/* Header Section */}
                    <div className="relative mb-8">
                        <div className="absolute top-0 right-0 text-[10px] font-bold text-gray-800 uppercase">
                            HOJA 1/1
                        </div>

                        <div className="text-center space-y-1">
                            <h1 className="text-xl font-extrabold text-gray-900 tracking-tight uppercase">{settings?.report_title || defaultTitle}</h1>
                            <h2 className="text-lg font-bold text-gray-800 uppercase">{settings?.report_subtitle || defaultSubtitle}</h2>
                        </div>

                        <div className="flex justify-between items-end mt-4">
                            <div className="w-48 h-20 flex items-center justify-start">
                                {settings?.report_logo_state ? (
                                    <img src={`/media/${settings.report_logo_state}`} className="max-h-full object-contain" alt="State Logo" />
                                ) : (
                                    <div className="w-40 h-16 bg-gray-50 border border-dashed border-gray-300 flex items-center justify-center text-[10px] text-gray-400 font-bold uppercase p-2 text-center rounded">
                                        LOGO QUINTANA ROO
                                    </div>
                                )}
                            </div>
                            <div className="text-right flex flex-col items-end">
                                <div className="w-40 h-16 flex items-center justify-end mb-2">
                                    {settings?.report_logo_campaign ? (
                                        <img src={`/media/${settings.report_logo_campaign}`} className="max-h-full object-contain" alt="Campaign Logo" />
                                    ) : (
                                        <div className="w-32 h-12 bg-gray-50 border border-dashed border-gray-300 flex items-center justify-center text-[10px] text-gray-400 font-bold uppercase p-2 text-center rounded">
                                            LOGO CAMPAÑA
                                        </div>
                                    )}
                                </div>
                                <div className="bg-gray-100 px-3 py-1 rounded text-sm font-bold text-gray-700 border border-gray-200">
                                    FONDO BOMBEROS ({selectedYear || '----'}) : {selectedRequirement || 'S/N'}
                                </div>
                            </div>
                        </div>

                        <div className="mt-8 text-center border-b-2 border-gray-900 pb-2">
                            <p className="text-sm font-black uppercase tracking-widest text-gray-900">
                                {settings?.report_location_date || defaultLocationDate}
                            </p>
                        </div>
                    </div>

                    {/* Report Table */}
                    <div className="overflow-x-auto">
                        <table className="w-full border-collapse">
                            <thead>
                                <tr className="bg-white">
                                    <th className="border-2 border-black px-3 py-2 text-sm font-black uppercase text-left w-1/4">COMUNIDADES</th>
                                    <th className="border-2 border-black px-3 py-2 text-sm font-black uppercase text-left w-1/4">N O M B R E S</th>
                                    <th className="border-2 border-black px-3 py-2 text-sm font-black uppercase text-right w-1/6">SUBTOTAL</th>
                                    <th className="border-2 border-black px-3 py-2 text-sm font-black uppercase text-right w-1/6">15%</th>
                                    <th className="border-2 border-black px-3 py-2 text-sm font-black uppercase text-right w-1/6">T O T A L</th>
                                </tr>
                            </thead>
                            <tbody>
                                {captures.length > 0 ? captures.map((c) => (
                                    <tr key={c.id} className="hover:bg-gray-50">
                                        <td className="border border-black px-3 py-1.5 text-xs font-bold text-gray-900 uppercase">{c.community?.name}</td>
                                        <td className="border border-black px-3 py-1.5 text-xs font-medium text-gray-800 uppercase">{c.firefighter?.name}</td>
                                        <td className="border border-black px-3 py-1.5 text-xs font-bold text-right text-black">{formatCurrency(c.subtotal).replace('$', '').trim()}</td>
                                        <td className="border border-black px-3 py-1.5 text-xs font-medium text-right text-gray-700">{formatCurrency(c.commission).replace('$', '').trim()}</td>
                                        <td className="border border-black px-3 py-1.5 text-xs font-black text-right text-black">{formatCurrency(c.total).replace('$', '').trim()}</td>
                                    </tr>
                                )) : (
                                    <tr>
                                        <td colSpan="5" className="border border-black px-3 py-12 text-center text-gray-400 italic">No hay datos registrados para este reporte</td>
                                    </tr>
                                )}
                            </tbody>
                            <tfoot>
                                <tr className="bg-gray-50 print:bg-gray-50 border-t-2 border-black">
                                    <td colSpan="2" className="border-2 border-black px-3 py-2 text-sm font-black text-right uppercase">TOTAL</td>
                                    <td className="border-2 border-black px-3 py-2 text-sm font-black text-right bg-blue-50/50">
                                        <div className="flex justify-between">
                                            <span>$</span>
                                            <span>{formatCurrency(totalSubtotal).replace('$', '').trim()}</span>
                                        </div>
                                    </td>
                                    <td className="border-2 border-black px-3 py-2 text-sm font-black text-right">
                                        <div className="flex justify-between">
                                            <span>$</span>
                                            <span>{formatCurrency(totalCommission).replace('$', '').trim()}</span>
                                        </div>
                                    </td>
                                    <td className="border-2 border-black px-3 py-2 text-sm font-black text-right bg-red-50/50">
                                        <div className="flex justify-between">
                                            <span>$</span>
                                            <span>{formatCurrency(totalTotal).replace('$', '').trim()}</span>
                                        </div>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    {/* Footer Info */}
                    <div className="mt-12 grid grid-cols-2 gap-8 print:mt-16 invisible print:visible">
                        <div className="text-center pt-8 border-t border-gray-400">
                            <p className="text-xs font-bold uppercase">Nombre y Firma</p>
                            <p className="text-[10px] text-gray-500">Responsable de Captura</p>
                        </div>
                        <div className="text-center pt-8 border-t border-gray-400">
                            <p className="text-xs font-bold uppercase">Sello de Recibido</p>
                            <p className="text-[10px] text-gray-500">Organismo Operador CAPA</p>
                        </div>
                    </div>

                    <div className="mt-8 text-center text-[10px] text-gray-400 uppercase tracking-widest hidden print:block">
                        SISTEMA DE GESTION DE BOMBEROS - CAPA JMM
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
