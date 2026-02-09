import React, { useState, useEffect, useRef } from 'react';
import axios from 'axios';
import { formatCurrency } from '../../firefighters_helpers';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';

export default function Capture({ auth, communities: initialCommunities, firefighters: allFirefighters }) {
    const [communities, setCommunities] = useState(initialCommunities || []);
    const [firefighters, setFirefighters] = useState([]);
    const [formData, setFormData] = useState({
        date: new Date().toISOString().split('T')[0],
        year: new Date().getFullYear(),
        community_id: '',
        firefighter_id: '',
        subtotal: '',
        commission: '', // Calculated
        total: '',      // Calculated
        rounding_commission: '0',
        rounding_total: '0'
    });
    const [selectedFirefighter, setSelectedFirefighter] = useState(null);
    const [message, setMessage] = useState(null);
    const [lastCapture, setLastCapture] = useState(null);

    // Autocomplete states
    const [communitySearch, setCommunitySearch] = useState('');
    const [showCommunityDropdown, setShowCommunityDropdown] = useState(false);
    const [selectedCommunity, setSelectedCommunity] = useState(null);
    const [activeCommunityIndex, setActiveCommunityIndex] = useState(-1);
    const communityInputRef = useRef(null);
    const subtotalInputRef = useRef(null);
    const submitButtonRef = useRef(null);
    const dropdownRef = useRef(null);

    useEffect(() => {
        if (formData.community_id) {
            // Filter firefighters from the prop data
            const filtered = allFirefighters.filter(f => f.community_id == formData.community_id);
            setFirefighters(filtered);

            // Find the active one for auto-selection
            const activeFirefighter = filtered.find(f => f.active) || (filtered.length > 0 ? filtered[0] : null);

            if (activeFirefighter) {
                setTimeout(() => {
                    setFormData(prev => ({ ...prev, firefighter_id: String(activeFirefighter.id) }));
                }, 50);
            } else {
                setFormData(prev => ({ ...prev, firefighter_id: '' }));
            }
        } else {
            setFirefighters([]);
            setFormData(prev => ({ ...prev, firefighter_id: '' }));
        }
    }, [formData.community_id, allFirefighters]);

    useEffect(() => {
        if (formData.firefighter_id && firefighters.length > 0) {
            const found = firefighters.find(f => f.id == formData.firefighter_id);
            setSelectedFirefighter(found);
        } else {
            setSelectedFirefighter(null);
        }
    }, [formData.firefighter_id, firefighters]);

    useEffect(() => {
        // Calculation Logic
        const subtotal = parseFloat(formData.subtotal) || 0;
        const roundingComm = parseFloat(formData.rounding_commission) || 0;
        const roundingTot = parseFloat(formData.rounding_total) || 0;

        // Final commission is 15% of subtotal plus any rounding adjustment
        const rawCommission = subtotal * 0.15;
        const commission = rawCommission + roundingComm;

        // Total is Subtotal minus Commission plus Rounding Total adjustment
        const total = subtotal - commission + roundingTot;

        setFormData(prev => ({
            ...prev,
            commission: commission.toFixed(2),
            total: total.toFixed(2)
        }));
    }, [formData.subtotal, formData.rounding_commission, formData.rounding_total]);

    // Close dropdown when clicking outside
    useEffect(() => {
        const handleClickOutside = (event) => {
            if (dropdownRef.current && !dropdownRef.current.contains(event.target) &&
                communityInputRef.current && !communityInputRef.current.contains(event.target)) {
                setShowCommunityDropdown(false);
            }
        };
        document.addEventListener('mousedown', handleClickOutside);
        return () => document.removeEventListener('mousedown', handleClickOutside);
    }, []);

    const handleChange = (e) => {
        const { name, value } = e.target;

        // If it's a numeric field, limit to 2 decimals
        if (['subtotal', 'rounding_commission', 'rounding_total'].includes(name)) {
            if (value.includes('.')) {
                const parts = value.split('.');
                if (parts[1].length > 2) {
                    return; // Don't allow more than 2 decimals
                }
            }
        }

        setFormData(prev => ({ ...prev, [name]: value }));
    };

    const handleCommunitySearchChange = (e) => {
        const value = e.target.value;
        setCommunitySearch(value);
        setShowCommunityDropdown(true);
        setActiveCommunityIndex(-1); // Reset index on search

        // Clear selection if text is cleared
        if (!value) {
            setSelectedCommunity(null);
            setFormData(prev => ({ ...prev, community_id: '' }));
        }
    };

    const handleCommunityKeyDown = (e) => {
        if (!showCommunityDropdown || filteredCommunities.length === 0) return;

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            setActiveCommunityIndex(prev => (prev < filteredCommunities.length - 1 ? prev + 1 : prev));
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            setActiveCommunityIndex(prev => (prev > 0 ? prev - 1 : prev));
        } else if (e.key === 'Enter' || e.key === 'Tab') {
            if (activeCommunityIndex >= 0) {
                if (e.key === 'Enter') e.preventDefault();
                handleCommunitySelect(filteredCommunities[activeCommunityIndex]);
            }
        } else if (e.key === 'Escape') {
            setShowCommunityDropdown(false);
        }
    };

    const handleCommunitySelect = (community) => {
        setSelectedCommunity(community);
        setCommunitySearch(community.name);
        setFormData(prev => ({ ...prev, community_id: community.id }));
        setShowCommunityDropdown(false);
        setMessage(null); // Clear success/error message on new selection

        // Auto focus to subtotal field
        setTimeout(() => {
            if (subtotalInputRef.current) {
                subtotalInputRef.current.focus();
            }
        }, 0);
    };

    const filteredCommunities = communities.filter(c =>
        c.name.toLowerCase().includes(communitySearch.toLowerCase())
    );

    const handleSubmit = async (e) => {
        e.preventDefault();

        // Client side validation for max rounding
        if (selectedFirefighter && selectedFirefighter.max_rounding_amount > 0) {
            const totalRoundingValid = Math.abs(parseFloat(formData.rounding_total || 0));
            if (totalRoundingValid > parseFloat(selectedFirefighter.max_rounding_amount)) {
                setMessage({ type: 'error', text: `El redondeo excede el límite de ${selectedFirefighter.max_rounding_amount}` });
                return;
            }
        }

        try {
            const res = await axios.post('/captures', formData);
            setMessage({ type: 'success', text: 'Captura guardada exitosamente' });
            setLastCapture(res.data);

            // Clear entire form
            setFormData(prev => ({
                ...prev,
                community_id: '',
                firefighter_id: '',
                subtotal: '',
                rounding_commission: '0',
                rounding_total: '0'
            }));
            setCommunitySearch('');
            setSelectedCommunity(null);

            // Focus back to community input
            if (communityInputRef.current) {
                communityInputRef.current.focus();
            }

        } catch (error) {
            if (error.response && error.response.data.message) {
                setMessage({ type: 'error', text: error.response.data.message });
            } else {
                setMessage({ type: 'error', text: 'Error al guardar la captura' });
            }
        }
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Capturar Recaudación de Bomberos</h2>}
        >
            <Head title="Captura Bomberos" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900">
                            <div className="max-w-4xl mx-auto">
                                <h2 className="text-2xl font-bold mb-6 text-gray-800">Captura Mensual</h2>

                                {message && (
                                    <div className={`p-4 mb-4 rounded ${message.type === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'}`}>
                                        {message.text}
                                        {message.type === 'success' && lastCapture && (
                                            <div className="mt-2 pt-2 border-t border-green-200 text-sm">
                                                <p className="font-bold">Último registro agregado:</p>
                                                <ul className="list-disc list-inside mt-1">
                                                    <li>Comunidad: <span className="font-semibold">{lastCapture.community?.name}</span></li>
                                                    <li>Bombero: <span className="font-semibold">{lastCapture.firefighter?.name}</span></li>
                                                    <li>Total: <span className="font-semibold">{formatCurrency(lastCapture.total)}</span></li>
                                                    {lastCapture.requirement_number && (
                                                        <li>Requerimiento: <span className="font-bold text-blue-700">{lastCapture.requirement_number}</span></li>
                                                    )}
                                                </ul>
                                            </div>
                                        )}
                                    </div>
                                )}

                                <form onSubmit={handleSubmit} className="bg-white shadow rounded-lg p-6 space-y-6">
                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label className="block text-sm font-medium text-gray-700">Fecha</label>
                                            <input
                                                type="date"
                                                name="date"
                                                value={formData.date}
                                                onChange={handleChange}
                                                className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                                required
                                            />
                                        </div>

                                        <div>
                                            <label className="block text-sm font-medium text-gray-700">Año</label>
                                            <input
                                                type="number"
                                                name="year"
                                                value={formData.year}
                                                onChange={handleChange}
                                                min="2020"
                                                max="2030"
                                                className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                                required
                                            />
                                            <p className="mt-1 text-xs text-gray-500">Año al que pertenece el registro</p>
                                        </div>

                                        <div className="relative">
                                            <label className="block text-sm font-medium text-gray-700">Comunidad</label>
                                            <input
                                                ref={communityInputRef}
                                                type="text"
                                                value={communitySearch}
                                                onChange={handleCommunitySearchChange}
                                                onKeyDown={handleCommunityKeyDown}
                                                onFocus={() => setShowCommunityDropdown(true)}
                                                placeholder="Escribe para buscar..."
                                                className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2"
                                                required
                                            />
                                            {showCommunityDropdown && filteredCommunities.length > 0 && (
                                                <div
                                                    ref={dropdownRef}
                                                    className="absolute z-10 mt-1 w-full bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto"
                                                >
                                                    {filteredCommunities.map((community, index) => (
                                                        <div
                                                            key={community.id}
                                                            onClick={() => handleCommunitySelect(community)}
                                                            onMouseEnter={() => setActiveCommunityIndex(index)}
                                                            className={`px-4 py-2 cursor-pointer transition-colors ${activeCommunityIndex === index ? 'bg-blue-600 text-white' :
                                                                selectedCommunity?.id === community.id ? 'bg-blue-100' : 'hover:bg-blue-50'
                                                                }`}
                                                        >
                                                            {community.name}
                                                        </div>
                                                    ))}
                                                </div>
                                            )}
                                        </div>

                                        <div>
                                            <label className="block text-sm font-medium text-gray-700">Bombero</label>
                                            <select
                                                name="firefighter_id"
                                                value={formData.firefighter_id}
                                                onChange={handleChange}
                                                className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2 cursor-not-allowed bg-gray-50"
                                                required
                                                disabled
                                            >
                                                <option value="">
                                                    {formData.community_id
                                                        ? (firefighters.length > 0 ? (firefighters.find(f => f.id == formData.firefighter_id)?.name || 'Seleccionado automáticamente') : 'No hay bomberos')
                                                        : 'Seleccione una comunidad primero'}
                                                </option>
                                                {firefighters.map(f => <option key={f.id} value={f.id}>{f.name}</option>)}
                                            </select>
                                        </div>

                                        <div>
                                            <label className="block text-sm font-medium text-gray-700">Subtotal</label>
                                            <div className="mt-1 relative rounded-md shadow-sm">
                                                <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <span className="text-gray-500 sm:text-sm">$</span>
                                                </div>
                                                <input
                                                    ref={subtotalInputRef}
                                                    type="number"
                                                    step="0.01"
                                                    name="subtotal"
                                                    value={formData.subtotal}
                                                    onChange={handleChange}
                                                    onKeyDown={(e) => {
                                                        if (e.key === 'Enter') {
                                                            e.preventDefault();
                                                            submitButtonRef.current?.focus();
                                                        }
                                                    }}
                                                    className={`block w-full pl-7 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2 ${!selectedCommunity ? 'bg-gray-100 cursor-not-allowed' : ''
                                                        }`}
                                                    required
                                                    disabled={!selectedCommunity}
                                                    placeholder={!selectedCommunity ? "Seleccione comunidad primero" : "0.00"}
                                                />
                                            </div>
                                            {formData.subtotal && (
                                                <p className="mt-1 text-xs text-gray-500 font-medium">
                                                    Vista previa: <span className="text-blue-600 font-bold">{formatCurrency(formData.subtotal)}</span>
                                                </p>
                                            )}
                                        </div>
                                    </div>

                                    <div className="border-t border-gray-200 pt-4">
                                        <h3 className="text-lg font-medium text-gray-900 mb-4">Cálculos y Ajustes</h3>
                                        <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                                            <div>
                                                <label className="block text-sm font-medium text-gray-700">Comisión (Calc)</label>
                                                <input
                                                    type="text"
                                                    value={formatCurrency(formData.commission)}
                                                    disabled
                                                    className="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm border p-2 font-semibold text-blue-700"
                                                />
                                            </div>
                                            <div>
                                                <label className="block text-sm font-medium text-gray-700">Redondeo Comisión</label>
                                                <div className="mt-1 relative rounded-md shadow-sm">
                                                    <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                        <span className="text-gray-500 sm:text-sm">$</span>
                                                    </div>
                                                    <input
                                                        type="number"
                                                        step="0.01"
                                                        name="rounding_commission"
                                                        value={formData.rounding_commission}
                                                        onChange={handleChange}
                                                        className="block w-full pl-7 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2"
                                                    />
                                                </div>
                                                {formData.rounding_commission !== '0' && formData.rounding_commission !== '' && (
                                                    <p className="mt-1 text-xs text-gray-400">
                                                        {formatCurrency(formData.rounding_commission)}
                                                    </p>
                                                )}
                                            </div>
                                        </div>

                                        <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mt-4">
                                            <div>
                                                <label className="block text-sm font-medium text-gray-700">Total (Calc)</label>
                                                <input
                                                    type="text"
                                                    value={formatCurrency(formData.total)}
                                                    disabled
                                                    className="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm border p-2 font-bold text-green-700 text-lg"
                                                />
                                            </div>
                                            <div>
                                                <label className="block text-sm font-medium text-gray-700">Redondeo Total</label>
                                                <div className="mt-1 relative rounded-md shadow-sm">
                                                    <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                        <span className="text-gray-500 sm:text-sm">$</span>
                                                    </div>
                                                    <input
                                                        type="number"
                                                        step="0.01"
                                                        name="rounding_total"
                                                        value={formData.rounding_total}
                                                        onChange={handleChange}
                                                        className="block w-full pl-7 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2"
                                                    />
                                                </div>
                                                {formData.rounding_total !== '0' && formData.rounding_total !== '' && (
                                                    <p className="mt-1 text-xs text-gray-400">
                                                        {formatCurrency(formData.rounding_total)}
                                                    </p>
                                                )}
                                                {selectedFirefighter && selectedFirefighter.max_rounding_amount > 0 && Math.abs(parseFloat(formData.rounding_total || 0)) > parseFloat(selectedFirefighter.max_rounding_amount) && (
                                                    <p className="text-red-500 text-xs mt-1">Excede el límite autorizado: {formatCurrency(selectedFirefighter.max_rounding_amount)}</p>
                                                )}
                                            </div>
                                        </div>
                                    </div>

                                    <div className="flex justify-end">
                                        <button
                                            ref={submitButtonRef}
                                            type="submit"
                                            disabled={!selectedCommunity || !formData.subtotal}
                                            className={`px-4 py-2 rounded-md transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 ${(!selectedCommunity || !formData.subtotal)
                                                ? 'bg-gray-400 text-gray-200 cursor-not-allowed'
                                                : 'bg-blue-600 text-white hover:bg-blue-700'
                                                }`}
                                        >
                                            Agregar Captura
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
