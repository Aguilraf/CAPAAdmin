import React, { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';
import { Search, Download, X } from 'lucide-react';
import TextInput from '@/Components/TextInput';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';

export default function Index({ auth, captures, filters, availableYears, availableRequirements, availableCommunities, availableFirefighters, totals }) {
    const [filterValues, setFilterValues] = useState({
        year: filters.year || '',
        requirement_number: filters.requirement_number || '',
        community_id: filters.community_id || '',
        firefighter_id: filters.firefighter_id || '',
        date_from: filters.date_from || '',
        date_to: filters.date_to || '',
        amount_min: filters.amount_min || '',
        amount_max: filters.amount_max || '',
    });

    const handleFilterChange = (e) => {
        const newValues = {
            ...filterValues,
            [e.target.name]: e.target.value,
        };

        // If year changes, clear requirement_number and reload
        if (e.target.name === 'year') {
            newValues.requirement_number = '';
            router.get(route('firefighters.query'), newValues, {
                preserveState: true,
                preserveScroll: true,
            });
        }

        // If community changes, clear firefighter_id and reload
        if (e.target.name === 'community_id') {
            newValues.firefighter_id = '';
            router.get(route('firefighters.query'), newValues, {
                preserveState: true,
                preserveScroll: true,
            });
        }

        setFilterValues(newValues);
    };

    const handleSearch = () => {
        router.get(route('firefighters.query'), filterValues, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const handleClearFilters = () => {
        setFilterValues({
            year: '',
            requirement_number: '',
            community_id: '',
            firefighter_id: '',
            date_from: '',
            date_to: '',
            amount_min: '',
            amount_max: '',
        });
        router.get(route('firefighters.query'));
    };

    const handleSort = (field) => {
        const direction = filters.sort === field && filters.direction === 'asc' ? 'desc' : 'asc';
        router.get(route('firefighters.query'), {
            ...filterValues,
            sort: field,
            direction: direction,
        }, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const handleExport = (format) => {
        const params = new URLSearchParams(filterValues).toString();
        window.location.href = `/firefighters/export?format=${format}&${params}`;
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Consulta de Historial por Comunidad - Bomberos</h2>}
        >
            <Head title="Consulta Bomberos" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900">
                            {/* Page Header */}
                            <div className="mb-6">
                                <h3 className="text-2xl font-bold text-gray-800">Consulta de Historial por Comunidad</h3>
                                <p className="text-sm text-gray-600 mt-1">Busque y filtre capturas de bomberos por comunidad</p>
                            </div>

                            {/* Filters Panel */}
                            <div className="bg-gray-50 p-6 rounded-lg mb-6">
                                <h4 className="text-lg font-semibold mb-4 text-gray-700">Filtros de Búsqueda</h4>

                                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                    {/* Year */}
                                    <div>
                                        <InputLabel value="Año" />
                                        <select
                                            name="year"
                                            value={filterValues.year}
                                            onChange={handleFilterChange}
                                            className="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        >
                                            <option value="">Todos</option>
                                            {availableYears.map(year => (
                                                <option key={year} value={year}>{year}</option>
                                            ))}
                                        </select>
                                    </div>

                                    {/* Requirement Number */}
                                    <div>
                                        <InputLabel value="Req. #" />
                                        <select
                                            name="requirement_number"
                                            value={filterValues.requirement_number}
                                            onChange={handleFilterChange}
                                            className="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        >
                                            <option value="">Todos</option>
                                            {availableRequirements.map(reqNum => (
                                                <option key={reqNum} value={reqNum}>{reqNum}</option>
                                            ))}
                                        </select>
                                    </div>

                                    {/* Community */}
                                    <div>
                                        <InputLabel value="Comunidad" />
                                        <select
                                            name="community_id"
                                            value={filterValues.community_id}
                                            onChange={handleFilterChange}
                                            className="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        >
                                            <option value="">Todas</option>
                                            {availableCommunities.map(community => (
                                                <option key={community.id} value={community.id}>{community.name}</option>
                                            ))}
                                        </select>
                                    </div>

                                    {/* Firefighter */}
                                    <div>
                                        <InputLabel value="Bombero" />
                                        <select
                                            name="firefighter_id"
                                            value={filterValues.firefighter_id}
                                            onChange={handleFilterChange}
                                            className="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        >
                                            <option value="">Todos</option>
                                            {availableFirefighters.map(firefighter => (
                                                <option key={firefighter.id} value={firefighter.id}>{firefighter.name}</option>
                                            ))}
                                        </select>
                                    </div>

                                    {/* Date From */}
                                    <div>
                                        <InputLabel value="Fecha Desde" />
                                        <TextInput
                                            type="date"
                                            name="date_from"
                                            value={filterValues.date_from}
                                            onChange={handleFilterChange}
                                            className="mt-1 block w-full"
                                        />
                                    </div>

                                    {/* Date To */}
                                    <div>
                                        <InputLabel value="Fecha Hasta" />
                                        <TextInput
                                            type="date"
                                            name="date_to"
                                            value={filterValues.date_to}
                                            onChange={handleFilterChange}
                                            className="mt-1 block w-full"
                                        />
                                    </div>

                                    {/* Amount Min */}
                                    <div>
                                        <InputLabel value="Monto Mínimo" />
                                        <TextInput
                                            type="number"
                                            step="0.01"
                                            name="amount_min"
                                            value={filterValues.amount_min}
                                            onChange={handleFilterChange}
                                            className="mt-1 block w-full"
                                            placeholder="0.00"
                                        />
                                    </div>

                                    {/* Amount Max */}
                                    <div>
                                        <InputLabel value="Monto Máximo" />
                                        <TextInput
                                            type="number"
                                            step="0.01"
                                            name="amount_max"
                                            value={filterValues.amount_max}
                                            onChange={handleFilterChange}
                                            className="mt-1 block w-full"
                                            placeholder="0.00"
                                        />
                                    </div>
                                </div>

                                {/* Action Buttons */}
                                <div className="flex gap-3 mt-6">
                                    <PrimaryButton onClick={handleSearch}>
                                        <Search className="w-4 h-4 mr-2" />
                                        Buscar
                                    </PrimaryButton>
                                    <SecondaryButton onClick={handleClearFilters}>
                                        <X className="w-4 h-4 mr-2" />
                                        Limpiar Filtros
                                    </SecondaryButton>
                                </div>
                            </div>

                            {/* Export Buttons */}
                            <div className="flex gap-3 mb-4">
                                <button
                                    onClick={() => handleExport('excel')}
                                    className="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150"
                                >
                                    <Download className="w-4 h-4 mr-2" />
                                    Exportar Excel
                                </button>
                                <button
                                    onClick={() => handleExport('pdf')}
                                    className="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-900 focus:outline-none focus:border-red-900 focus:ring ring-red-300 disabled:opacity-25 transition ease-in-out duration-150"
                                >
                                    <Download className="w-4 h-4 mr-2" />
                                    Exportar PDF
                                </button>
                            </div>

                            {/* Summary */}
                            <div className="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                                <div className="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                                    <div>
                                        <p className="text-sm text-gray-600">Registros</p>
                                        <p className="text-2xl font-bold text-blue-600">{totals.count}</p>
                                    </div>
                                    <div>
                                        <p className="text-sm text-gray-600">Total Recaudado</p>
                                        <p className="text-2xl font-bold text-green-600">${parseFloat(totals.subtotal).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</p>
                                    </div>
                                    <div>
                                        <p className="text-sm text-gray-600">Comisión</p>
                                        <p className="text-2xl font-bold text-orange-600">${parseFloat(totals.commission).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</p>
                                    </div>
                                    <div>
                                        <p className="text-sm text-gray-600">Neto</p>
                                        <p className="text-2xl font-bold text-red-600">${parseFloat(totals.total).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</p>
                                    </div>
                                </div>
                            </div>

                            {/* Results Table */}
                            <div className="overflow-x-auto">
                                <table className="min-w-full divide-y divide-gray-200">
                                    <thead className="bg-gray-50">
                                        <tr>
                                            <th onClick={() => handleSort('year')} className="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                                                Año
                                            </th>
                                            <th onClick={() => handleSort('requirement_number')} className="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                                                Req. #
                                            </th>
                                            <th onClick={() => handleSort('date')} className="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                                                Fecha
                                            </th>
                                            <th className="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Comunidad
                                            </th>
                                            <th className="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Bombero
                                            </th>
                                            <th onClick={() => handleSort('subtotal')} className="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                                                Total Recaudado
                                            </th>
                                            <th onClick={() => handleSort('commission')} className="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                                                Comisión
                                            </th>
                                            <th onClick={() => handleSort('total')} className="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                                                Neto
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody className="bg-white divide-y divide-gray-200">
                                        {captures.data.length > 0 ? (
                                            captures.data.map((capture) => (
                                                <tr key={capture.id} className="hover:bg-gray-50">
                                                    <td className="px-3 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                        {capture.year}
                                                    </td>
                                                    <td className="px-3 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        {capture.requirement_number}
                                                    </td>
                                                    <td className="px-3 py-4 whitespace-nowrap text-sm">
                                                        {capture.date ? new Date(capture.date).toLocaleDateString('es-MX') : '-'}
                                                    </td>
                                                    <td className="px-3 py-4 text-sm">
                                                        {capture.community?.name}
                                                    </td>
                                                    <td className="px-3 py-4 text-sm">
                                                        {capture.firefighter?.name}
                                                    </td>
                                                    <td className="px-3 py-4 whitespace-nowrap text-sm text-right">
                                                        ${parseFloat(capture.subtotal).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                                                    </td>
                                                    <td className="px-3 py-4 whitespace-nowrap text-sm text-right">
                                                        ${parseFloat(capture.commission).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                                                    </td>
                                                    <td className="px-3 py-4 whitespace-nowrap text-sm text-right font-semibold">
                                                        ${(parseFloat(capture.subtotal) - parseFloat(capture.commission)).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                                                    </td>
                                                </tr>
                                            ))
                                        ) : (
                                            <tr>
                                                <td colSpan="8" className="px-3 py-8 text-center text-gray-500">
                                                    No se encontraron registros con los filtros aplicados.
                                                </td>
                                            </tr>
                                        )}
                                    </tbody>
                                </table>
                            </div>

                            {/* Pagination */}
                            {captures.links && captures.links.length > 3 && (
                                <div className="mt-6 flex justify-center">
                                    <nav className="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                                        {captures.links.map((link, index) => (
                                            <button
                                                key={index}
                                                onClick={() => link.url && router.get(link.url)}
                                                disabled={!link.url}
                                                className={`relative inline-flex items-center px-4 py-2 border text-sm font-medium ${link.active
                                                    ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600'
                                                    : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'
                                                    } ${!link.url ? 'cursor-not-allowed opacity-50' : 'cursor-pointer'}`}
                                                dangerouslySetInnerHTML={{ __html: link.label }}
                                            />
                                        ))}
                                    </nav>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
