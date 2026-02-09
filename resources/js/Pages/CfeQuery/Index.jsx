import React, { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';
import { MagnifyingGlassIcon, ArrowDownTrayIcon, XMarkIcon } from '@heroicons/react/24/outline';
import TextInput from '@/Components/TextInput';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';

export default function Index({ auth, receipts, filters, availableYears, totals }) {
    const [filterValues, setFilterValues] = useState({
        year: filters.year || '',
        requirement_number: filters.requirement_number || '',
        rpu: filters.rpu || '',
        search: filters.search || '',
        date_from: filters.date_from || '',
        date_to: filters.date_to || '',
        amount_min: filters.amount_min || '',
        amount_max: filters.amount_max || '',
    });

    const handleFilterChange = (e) => {
        setFilterValues({
            ...filterValues,
            [e.target.name]: e.target.value,
        });
    };

    const handleSearch = () => {
        router.get(route('cfe.query'), filterValues, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const handleClearFilters = () => {
        setFilterValues({
            year: '',
            requirement_number: '',
            rpu: '',
            search: '',
            date_from: '',
            date_to: '',
            amount_min: '',
            amount_max: '',
        });
        router.get(route('cfe.query'));
    };

    const handleSort = (field) => {
        const direction = filters.sort === field && filters.direction === 'asc' ? 'desc' : 'asc';
        router.get(route('cfe.query'), {
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
        window.location.href = route('cfe.export') + '?format=' + format + '&' + params;
    };

    const parsePoblado = (description) => {
        const parts = description?.split(',') || [];
        return parts[0]?.trim() || '';
    };

    const parseDireccion = (description) => {
        const parts = description?.split(',') || [];
        return parts[1]?.trim() || '';
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Consulta CFE" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            <h2 className="text-2xl font-semibold mb-6">Consulta de Recibos CFE</h2>

                            {/* Filter Panel */}
                            <div className="bg-gray-50 p-4 rounded-lg mb-6">
                                <h3 className="text-lg font-medium mb-4">Filtros de Búsqueda</h3>
                                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                    {/* Year Filter */}
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
                                        <TextInput
                                            type="number"
                                            name="requirement_number"
                                            value={filterValues.requirement_number}
                                            onChange={handleFilterChange}
                                            className="mt-1 block w-full"
                                            placeholder="Número"
                                        />
                                    </div>

                                    {/* RPU */}
                                    <div>
                                        <InputLabel value="RPU" />
                                        <TextInput
                                            type="text"
                                            name="rpu"
                                            value={filterValues.rpu}
                                            onChange={handleFilterChange}
                                            className="mt-1 block w-full"
                                            placeholder="Buscar RPU"
                                        />
                                    </div>

                                    {/* Search (Poblado/Dirección) */}
                                    <div>
                                        <InputLabel value="Poblado/Dirección" />
                                        <TextInput
                                            type="text"
                                            name="search"
                                            value={filterValues.search}
                                            onChange={handleFilterChange}
                                            className="mt-1 block w-full"
                                            placeholder="Buscar ubicación"
                                        />
                                    </div>

                                    {/* Date From */}
                                    <div>
                                        <InputLabel value="Periodo Desde" />
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
                                        <InputLabel value="Periodo Hasta" />
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
                                <div className="mt-4 flex gap-2">
                                    <PrimaryButton onClick={handleSearch}>
                                        <MagnifyingGlassIcon className="h-5 w-5 mr-2" />
                                        Buscar
                                    </PrimaryButton>
                                    <SecondaryButton onClick={handleClearFilters}>
                                        <XMarkIcon className="h-5 w-5 mr-2" />
                                        Limpiar Filtros
                                    </SecondaryButton>
                                </div>
                            </div>

                            {/* Export Buttons */}
                            <div className="mb-4 flex gap-2 justify-end">
                                <SecondaryButton onClick={() => handleExport('excel')}>
                                    <ArrowDownTrayIcon className="h-5 w-5 mr-2" />
                                    Exportar Excel
                                </SecondaryButton>
                                <SecondaryButton onClick={() => handleExport('pdf')}>
                                    <ArrowDownTrayIcon className="h-5 w-5 mr-2" />
                                    Exportar PDF
                                </SecondaryButton>
                            </div>

                            {/* Results Summary */}
                            <div className="mb-4 p-4 bg-blue-50 rounded-lg">
                                <div className="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                                    <div>
                                        <div className="text-sm text-gray-600">Registros</div>
                                        <div className="text-xl font-bold">{totals.count}</div>
                                    </div>
                                    <div>
                                        <div className="text-sm text-gray-600">Subtotal</div>
                                        <div className="text-xl font-bold">${totals.subtotal.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</div>
                                    </div>
                                    <div>
                                        <div className="text-sm text-gray-600">IVA</div>
                                        <div className="text-xl font-bold">${totals.iva.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</div>
                                    </div>
                                    <div>
                                        <div className="text-sm text-gray-600">Total</div>
                                        <div className="text-xl font-bold text-green-600">${totals.total.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</div>
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
                                            <th onClick={() => handleSort('rpu')} className="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                                                RPU
                                            </th>
                                            <th className="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Poblado
                                            </th>
                                            <th className="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Dirección
                                            </th>
                                            <th onClick={() => handleSort('period_start')} className="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                                                Periodo
                                            </th>
                                            <th onClick={() => handleSort('subtotal')} className="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                                                Subtotal
                                            </th>
                                            <th onClick={() => handleSort('iva')} className="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                                                IVA
                                            </th>
                                            <th onClick={() => handleSort('total')} className="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                                                Total
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody className="bg-white divide-y divide-gray-200">
                                        {receipts.data.length > 0 ? (
                                            receipts.data.map((receipt) => (
                                                <tr key={receipt.id} className="hover:bg-gray-50">
                                                    <td className="px-3 py-4 whitespace-nowrap text-sm">
                                                        {receipt.requirement?.year}
                                                    </td>
                                                    <td className="px-3 py-4 whitespace-nowrap text-sm">
                                                        {receipt.requirement?.number}
                                                    </td>
                                                    <td className="px-3 py-4 whitespace-nowrap text-sm font-medium">
                                                        {receipt.rpu}
                                                    </td>
                                                    <td className="px-3 py-4 text-sm">
                                                        {parsePoblado(receipt.description)}
                                                    </td>
                                                    <td className="px-3 py-4 text-sm">
                                                        {parseDireccion(receipt.description)}
                                                    </td>
                                                    <td className="px-3 py-4 whitespace-nowrap text-sm">
                                                        {receipt.period_start && receipt.period_end ? (
                                                            <>
                                                                {new Date(receipt.period_start).toLocaleDateString('es-MX')} - {new Date(receipt.period_end).toLocaleDateString('es-MX')}
                                                            </>
                                                        ) : '-'}
                                                    </td>
                                                    <td className="px-3 py-4 whitespace-nowrap text-sm text-right">
                                                        ${parseFloat(receipt.subtotal).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                                                    </td>
                                                    <td className="px-3 py-4 whitespace-nowrap text-sm text-right">
                                                        ${parseFloat(receipt.iva).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                                                    </td>
                                                    <td className="px-3 py-4 whitespace-nowrap text-sm text-right font-medium">
                                                        ${parseFloat(receipt.total).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                                                    </td>
                                                </tr>
                                            ))
                                        ) : (
                                            <tr>
                                                <td colSpan="9" className="px-3 py-8 text-center text-gray-500">
                                                    No se encontraron registros con los filtros aplicados.
                                                </td>
                                            </tr>
                                        )}
                                    </tbody>
                                </table>
                            </div>

                            {/* Pagination */}
                            {receipts.data.length > 0 && (
                                <div className="mt-4 flex items-center justify-between">
                                    <div className="text-sm text-gray-700">
                                        Mostrando {receipts.from} a {receipts.to} de {receipts.total} registros
                                    </div>
                                    <div className="flex gap-2">
                                        {receipts.links.map((link, index) => (
                                            <button
                                                key={index}
                                                onClick={() => link.url && router.get(link.url)}
                                                disabled={!link.url}
                                                className={`px-3 py-1 rounded ${link.active
                                                        ? 'bg-indigo-600 text-white'
                                                        : link.url
                                                            ? 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-300'
                                                            : 'bg-gray-100 text-gray-400 cursor-not-allowed'
                                                    }`}
                                                dangerouslySetInnerHTML={{ __html: link.label }}
                                            />
                                        ))}
                                    </div>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
