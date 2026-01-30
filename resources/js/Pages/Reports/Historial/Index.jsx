import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { useState, useEffect } from 'react';

export default function Index({ reportes, filters }) {
    const { user } = usePage().props.auth;
    const isAdmin = user.roles && user.roles.some(r => r.name === 'Administrador');

    const [search, setSearch] = useState(filters.search || '');
    const [fechaDesde, setFechaDesde] = useState(filters.fecha_desde || '');
    const [fechaHasta, setFechaHasta] = useState(filters.fecha_hasta || '');

    // Expanded row state
    const [expandedReportId, setExpandedReportId] = useState(null);

    // Live search with debounce
    useEffect(() => {
        const delayDebounce = setTimeout(() => {
            router.get(route('reportes.historial'), { search, fecha_desde: fechaDesde, fecha_hasta: fechaHasta }, {
                preserveState: true,
                replace: true,
            });
        }, 500);

        return () => clearTimeout(delayDebounce);
    }, [search, fechaDesde, fechaHasta]);

    const handleReprint = (reportId) => {
        // Since we switched the route to GET, we can simply open it in a new tab
        window.open(route('reportes.historial.print', reportId), '_blank');
    };

    const toggleDetails = (reportId) => {
        if (expandedReportId === reportId) {
            setExpandedReportId(null);
        } else {
            setExpandedReportId(reportId);
        }
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Historial de Reportes de Material
                </h2>
            }
        >
            <Head title="Historial de Reportes" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            {/* Filtros */}
                            <div className="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                                {isAdmin && (
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700 mb-2">
                                            Buscar
                                        </label>
                                        <input
                                            type="text"
                                            value={search}
                                            onChange={(e) => setSearch(e.target.value)}
                                            placeholder="Solicitante, destinatario, departamento..."
                                            className="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        />
                                    </div>
                                )}
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-2">
                                        Desde
                                    </label>
                                    <input
                                        type="date"
                                        value={fechaDesde}
                                        onChange={(e) => setFechaDesde(e.target.value)}
                                        className="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    />
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-2">
                                        Hasta
                                    </label>
                                    <input
                                        type="date"
                                        value={fechaHasta}
                                        onChange={(e) => setFechaHasta(e.target.value)}
                                        className="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    />
                                </div>
                            </div>

                            {/* Tabla */}
                            <div className="overflow-x-auto">
                                <table className="min-w-full divide-y divide-gray-200">
                                    <thead className="bg-gray-50">
                                        <tr>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Fecha
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Solicitante
                                            </th>
                                            {isAdmin && (
                                                <>
                                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Departamento
                                                    </th>
                                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Destinatario
                                                    </th>
                                                </>
                                            )}
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Materiales
                                            </th>
                                            {isAdmin && (
                                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Generado por
                                                </th>
                                            )}
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Acciones
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody className="bg-white divide-y divide-gray-200">
                                        {reportes.data.map((reporte) => (
                                            <>
                                                <tr key={reporte.id} className="hover:bg-gray-50">
                                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        {new Date(reporte.fecha_reporte).toLocaleDateString('es-MX', { timeZone: 'UTC' })}
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                        {reporte.solicitante_nombre}
                                                    </td>
                                                    {isAdmin && (
                                                        <>
                                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                                {reporte.solicitante_departamento || 'N/A'}
                                                            </td>
                                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                                {reporte.destinatario_nombre}
                                                            </td>
                                                        </>
                                                    )}
                                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        {Array.isArray(reporte.materiales) ? reporte.materiales.length : 0} items
                                                    </td>
                                                    {isAdmin && (
                                                        <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                            {reporte.user?.name || 'N/A'}
                                                        </td>
                                                    )}
                                                    <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                        <button
                                                            onClick={() => toggleDetails(reporte.id)}
                                                            className="text-blue-600 hover:text-blue-900 mr-3 focus:outline-none"
                                                            title={expandedReportId === reporte.id ? "Ocultar detalles" : "Ver detalles"}
                                                        >
                                                            {expandedReportId === reporte.id ? '🔼' : '👁️'}
                                                        </button>
                                                        <button
                                                            onClick={() => handleReprint(reporte.id)}
                                                            className="text-gray-600 hover:text-gray-900"
                                                            title="Re-imprimir PDF"
                                                        >
                                                            🖨️
                                                        </button>
                                                    </td>
                                                </tr>
                                                {/* Fila Expandible de Detalles */}
                                                {expandedReportId === reporte.id && (
                                                    <tr className="bg-blue-50">
                                                        <td colSpan={isAdmin ? "7" : "4"} className="px-6 py-4">
                                                            <div className="rounded-md border border-blue-200 bg-white p-4 shadow-inner">
                                                                <h4 className="mb-3 text-sm font-bold text-gray-800 uppercase tracking-wide border-b pb-2">
                                                                    Detalles de Materiales
                                                                </h4>
                                                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4 text-xs text-gray-600">
                                                                    <div>
                                                                        <span className="font-semibold">Cargo Solicitante:</span> {reporte.solicitante_cargo}
                                                                    </div>
                                                                    <div>
                                                                        <span className="font-semibold">Cargo Destinatario:</span> {reporte.destinatario_cargo}
                                                                    </div>
                                                                </div>
                                                                <table className="min-w-full divide-y divide-gray-200 border text-sm">
                                                                    <thead className="bg-gray-100">
                                                                        <tr>
                                                                            <th className="px-4 py-2 text-left font-medium text-gray-600">Artículo</th>
                                                                            <th className="px-4 py-2 text-left font-medium text-gray-600">Cantidad</th>
                                                                            <th className="px-4 py-2 text-left font-medium text-gray-600">Unidad</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody className="divide-y divide-gray-200 bg-white">
                                                                        {Array.isArray(reporte.materiales) && reporte.materiales.map((item, idx) => (
                                                                            <tr key={idx}>
                                                                                <td className="px-4 py-2 text-gray-800">{item.articulo}</td>
                                                                                <td className="px-4 py-2 text-gray-800">{item.cantidad}</td>
                                                                                <td className="px-4 py-2 text-gray-500">{item.unidad}</td>
                                                                            </tr>
                                                                        ))}
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                )}
                                            </>
                                        ))}
                                    </tbody>
                                </table>
                            </div>

                            {/* Paginación */}
                            {reportes.links && (
                                <div className="mt-6 flex justify-center gap-2">
                                    {reportes.links.map((link, index) => (
                                        <Link
                                            key={index}
                                            href={link.url || '#'}
                                            className={`px-3 py-2 rounded-md text-sm ${link.active
                                                ? 'bg-blue-600 text-white'
                                                : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
                                                } ${!link.url ? 'opacity-50 cursor-not-allowed' : ''}`}
                                            dangerouslySetInnerHTML={{ __html: link.label }}
                                        />
                                    ))}
                                </div>
                            )}

                            {reportes.data.length === 0 && (
                                <div className="text-center py-8 text-gray-500">
                                    No se encontraron reportes
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

