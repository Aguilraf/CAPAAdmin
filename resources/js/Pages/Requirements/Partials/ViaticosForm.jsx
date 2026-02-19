import React, { useEffect, useState } from 'react';
import TextInput from '@/Components/TextInput';
import InputLabel from '@/Components/InputLabel';
import Checkbox from '@/Components/Checkbox';
import SecondaryButton from '@/Components/SecondaryButton';

export default function ViaticosForm({ data, setData, employees, partidas, vehicles, travelAllowanceRates = [] }) {

    // Local state for the "Add Expense" form
    const [expenseForm, setExpenseForm] = useState({
        employee_id: '',
        type: 'Viaticos', // Viaticos, Pasaje, Hospedaje
        amount: '',
        description: '',
        uuid: '',
        invoice_folio: '',
        invoice_date: '',
        provider_rfc: '',
        provider_name: '',
        invoice_subtotal: '',
        invoice_iva: '',
        invoice_retention_isr: '',
        invoice_retention_iva: '',
        invoice_total: '',
        xml_file: null // Just for UI ref
    });

    // Helper to get employee object by ID
    const getEmployee = (id) => employees.find(e => e.id == id);

    // Helper: Get Authorized Daily Rate for Employee
    const getAuthorizedLimit = (employeeId) => {
        const emp = getEmployee(employeeId);
        if (!emp) return 0;

        // Find rate based on Nivel and Year
        const rate = travelAllowanceRates.find(r =>
            r.year == (data.year || new Date().getFullYear()) &&
            r.nivel === emp.nivel &&
            r.rate_type === 'Viaticos' // Ensure we pick Viaticos rate
        );

        // Default to Zona 1 for now (or implement zone logic if needed)
        // If "half_day_payment" is checked, maybe reduce? User didn't specify, but usually it's full day unless specified.
        // Actually, previous task mentioned "Half Day". Let's check matching logic.
        // For now, simple daily rate.
        return rate ? parseFloat(rate.zona_1_amount) : 0;
    };

    // Initialize Commissioner List State (for UI)
    useEffect(() => {
        if (!data.commissioners_details) {
            setData(prev => ({ ...prev, commissioners_details: [] }));
        }
        // Ensure items is initialized
        if (!data.items) {
            setData(prev => ({ ...prev, items: [] }));
        }
    }, []);

    // Add Commissioner
    const handleAddCommissioner = (e) => {
        const empId = e.target.value;
        if (!empId) return;

        // Prevent duplicates
        if (data.commissioners_details && data.commissioners_details.some(c => c.id == empId)) {
            alert('El empleado ya ha sido seleccionado.');
            return;
        }

        const newDetails = [...(data.commissioners_details || []), {
            id: parseInt(empId),
            oficio_number: '',
            report_date: '',
            report_link: ''
        }];
        setData(prev => ({ ...prev, commissioners_details: newDetails }));
    };

    // Remove Commissioner
    const handleRemoveCommissioner = (index) => {
        const idToRemove = data.commissioners_details[index].id;
        const newDetails = [...data.commissioners_details];
        newDetails.splice(index, 1);

        // Also remove items associated with this commissioner?
        // Maybe ask confirmation? For now, we keep them or filter them?
        // Better to remove them to avoid consistency issues.
        const newItems = (data.items || []).filter(item => item.employee_id != idToRemove);

        setData(prev => ({ ...prev, commissioners_details: newDetails, items: newItems }));
    };

    // Handle XML Upload for Expense Form
    const handleExpenseXmlUpload = (e) => {
        const file = e.target.files[0];
        if (!file) return;

        const formData = new FormData();
        formData.append('file', file);

        axios.post(route('requirements.parse-xml'), formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        }).then(response => {
            const raw = response.data.data;
            setExpenseForm(prev => ({
                ...prev,
                uuid: raw.uuid,
                invoice_folio: raw.invoice_folio,
                invoice_date: raw.invoice_date,
                provider_rfc: raw.provider_rfc,
                provider_name: raw.provider_name,
                amount: raw.total, // Auto-fill amount from XML total
                invoice_subtotal: raw.subtotal,
                invoice_iva: raw.iva,
                invoice_retention_isr: raw.retention_isr,
                invoice_retention_iva: raw.retention_iva,
                invoice_total: raw.total,
                description: prev.description || raw.description.substring(0, 255)
            }));
            alert('Datos fiscales extraídos. Verifique el monto y concepto.');
        }).catch(error => {
            console.error('Error parsing XML', error);
            alert('Error al leer el XML.');
        });
    };

    // Add Expense Item
    const handleAddExpense = () => {
        if (!expenseForm.employee_id) {
            alert('Seleccione un comisionado.');
            return;
        }
        if (!expenseForm.amount || parseFloat(expenseForm.amount) <= 0) {
            alert('Ingrese un monto válido.');
            return;
        }

        // Determine Partida ID based on Type
        let partidaId = null;
        let partidaCodigo = '';
        if (expenseForm.type === 'Viaticos') {
            const p = partidas.find(pt => pt.codigo === '37501');
            if (p) { partidaId = p.id; partidaCodigo = '37501'; }
        } else if (expenseForm.type === 'Hospedaje') {
            const p = partidas.find(pt => pt.codigo === '37502');
            if (p) { partidaId = p.id; partidaCodigo = '37502'; }
        } else if (expenseForm.type === 'Pasaje') {
            const p = partidas.find(pt => pt.codigo === '37201'); // Default terrestrial
            if (p) { partidaId = p.id; partidaCodigo = '37201'; }
        }

        if (!partidaId) {
            alert('No se encontró la partida presupuestal para este concepto.');
            return;
        }

        const newItem = {
            id: Date.now(), // Temp ID
            employee_id: expenseForm.employee_id,
            partida_id: partidaId,
            partida_codigo: partidaCodigo, // For display/grouping
            amount: parseFloat(expenseForm.amount),
            description: expenseForm.description || `${expenseForm.type} - ${getEmployee(expenseForm.employee_id)?.nombre || ''}`,
            uuid: expenseForm.uuid,
            invoice_folio: expenseForm.invoice_folio,
            invoice_date: expenseForm.invoice_date,
            provider_rfc: expenseForm.provider_rfc,
            provider_name: expenseForm.provider_name,
            invoice_subtotal: expenseForm.invoice_subtotal,
            invoice_iva: expenseForm.invoice_iva,
            invoice_retention_isr: expenseForm.invoice_retention_isr,
            invoice_retention_iva: expenseForm.invoice_retention_iva,
            invoice_total: expenseForm.invoice_total,
            type: expenseForm.type // Friendly type
        };

        const newItems = [...(data.items || []), newItem];
        setData(prev => ({ ...prev, items: newItems }));

        // Reset form (keep employee maybe?)
        setExpenseForm({
            employee_id: '',
            type: 'Viaticos',
            amount: '',
            description: '',
            uuid: '',
            invoice_folio: '',
            invoice_date: '',
            provider_rfc: '',
            provider_name: '',
            invoice_subtotal: '',
            invoice_iva: '',
            invoice_retention_isr: '',
            invoice_retention_iva: '',
            invoice_total: '',
            xml_file: null
        });
    };

    const handleRemoveItem = (index) => {
        const newItems = [...(data.items || [])];
        newItems.splice(index, 1);
        setData(prev => ({ ...prev, items: newItems }));
    };

    // Calculate Totals when Items change
    useEffect(() => {
        if (!data.items) return;

        let totalViaticos = 0;
        let totalPasaje = 0;
        let totalHospedaje = 0;

        // IDs
        const viaticosId = partidas.find(p => p.codigo === '37501')?.id;
        const hospedajeId = partidas.find(p => p.codigo === '37502')?.id;
        const pasajeId1 = partidas.find(p => p.codigo === '37201')?.id;
        const pasajeId2 = partidas.find(p => p.codigo === '37301')?.id; // Aereo

        data.items.forEach(item => {
            const amt = parseFloat(item.amount) || 0;
            if (item.partida_id == hospedajeId) totalHospedaje += amt; // Hospedaje not capped per plan (usually per invoice)
            else if (item.partida_id == pasajeId1 || item.partida_id == pasajeId2) totalPasaje += amt;
        });

        // Calculate Viaticos Capped per Commissioner
        // Calculate Viaticos Capped per Commissioner
        let calculatedViaticos = 0;
        (data.commissioners_details || []).forEach(comm => {
            const empId = comm.id;
            const limitPerDay = getAuthorizedLimit(empId);
            const duration = parseInt(data.days_duration) || 1;
            const maxAuthorized = limitPerDay * duration;

            // Debugging
            // console.log('Calc Debug:', { empId, viaticosId, items: data.items });

            // Sum actual expenses for this employee
            const actualSpent = data.items
                .filter(item => {
                    // Loose equality for IDs to handle string/number mismatch
                    return item.employee_id == empId && item.partida_id == viaticosId;
                })
                .reduce((sum, item) => sum + (parseFloat(item.amount) || 0), 0);

            // Cap it
            calculatedViaticos += Math.min(actualSpent, maxAuthorized);
        });

        // Add uncategorized viaticos (no employee_id)? Or assume correct?
        // Better: Add non-assigned items directly (fallback)
        const unassignedViaticos = data.items
            .filter(item => !item.employee_id && item.partida_id == viaticosId)
            .reduce((sum, item) => sum + (parseFloat(item.amount) || 0), 0);

        totalViaticos = calculatedViaticos + unassignedViaticos;

        // Update headers if changed
        const updates = {};
        if (data.viaticos_amount !== totalViaticos) updates.viaticos_amount = totalViaticos;
        if (data.hospedaje_amount !== totalHospedaje) updates.hospedaje_amount = totalHospedaje;
        if (data.pasaje_amount !== totalPasaje) updates.pasaje_amount = totalPasaje;

        // Update Booleans
        if (totalViaticos > 0 && !data.has_viaticos) updates.has_viaticos = true;
        if (totalHospedaje > 0 && !data.has_hospedaje) updates.has_hospedaje = true;
        if (totalPasaje > 0 && !data.has_pasaje) updates.has_pasaje = true;

        // Set Partida IDs if missing
        if (totalViaticos > 0 && !data.viaticos_partida_id) updates.viaticos_partida_id = viaticosId;
        if (totalHospedaje > 0 && !data.hospedaje_partida_id) updates.hospedaje_partida_id = hospedajeId;
        if (totalPasaje > 0 && !data.pasaje_partida_id) updates.pasaje_partida_id = pasajeId1;

        if (Object.keys(updates).length > 0) {
            setData(prev => ({ ...prev, ...updates }));
        }

    }, [data.items, partidas]);

    // Duration Calc (Keep separate)
    useEffect(() => {
        if (data.departure_date && data.return_date) {
            const start = new Date(data.departure_date);
            const end = new Date(data.return_date);
            const diffTime = Math.abs(end - start);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            setData(prev => ({ ...prev, days_duration: diffDays || 1 }));
        }
    }, [data.departure_date, data.return_date]);

    // Adjust Limit Handler
    const handleAdjustLimit = (employeeId, maxAuthorized) => {
        const viaticosId = partidas.find(p => p.codigo === '37501')?.id;
        const employeeItems = data.items
            .map((item, index) => ({ ...item, originalIndex: index }))
            .filter(item => item.employee_id == employeeId && item.partida_id === viaticosId);

        let currentTotal = employeeItems.reduce((sum, item) => sum + (parseFloat(item.amount) || 0), 0);
        let excess = currentTotal - maxAuthorized;

        if (excess <= 0) return;

        const newItems = [...data.items];
        // Reduce from the last item backwards
        for (let i = employeeItems.length - 1; i >= 0; i--) {
            if (excess <= 0) break;

            const item = employeeItems[i];
            const originalIndex = item.originalIndex;
            const currentAmount = parseFloat(item.amount) || 0;

            if (currentAmount > excess) { // Changed >= to > to avoid 0.00 if exact match (though functionally same)
                newItems[originalIndex].amount = (currentAmount - excess).toFixed(2);
                excess = 0;
            } else {
                newItems[originalIndex].amount = 0;
                excess -= currentAmount;
            }
        }
        setData('items', newItems);
    };


    return (
        <div className="bg-blue-50 p-6 rounded-lg space-y-6 border border-blue-200">
            <h3 className="text-lg font-bold text-blue-800">Detalles de Comisión (Viáticos Multi-Empleado)</h3>

            {/* Header Data */}
            <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                {/* Oficio Number Removed from Header - Per Commissioner Logic */}
                <div>
                    <InputLabel value="Ejercicio" />
                    <TextInput
                        type="number"
                        value={data.exercise_year || new Date().getFullYear()}
                        onChange={e => setData('exercise_year', e.target.value)}
                        className="mt-1 block w-full"
                    />
                </div>
                <div>
                    <InputLabel value="Trimestre" />
                    <select
                        value={data.quarter || ''}
                        onChange={e => setData('quarter', e.target.value)}
                        className="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full"
                    >
                        <option value="">Seleccione...</option>
                        <option value="I">I</option>
                        <option value="II">II</option>
                        <option value="III">III</option>
                        <option value="IV">IV</option>
                    </select>
                </div>
                <div>
                    <InputLabel value="Leyenda Resumen" />
                    <TextInput
                        value={data.commission_summary_legend || ''}
                        onChange={e => setData('commission_summary_legend', e.target.value)}
                        className="mt-1 block w-full text-xs"
                    />
                </div>
            </div>

            {/* Fechas y Lugares */}
            <div className="grid grid-cols-1 md:grid-cols-3 gap-4 border-t border-blue-200 pt-4">
                <div>
                    <InputLabel value="Lugar de Origen" />
                    <div className="grid grid-cols-3 gap-1">
                        <TextInput placeholder="País" value={data.origin_country || 'México'} onChange={e => setData('origin_country', e.target.value)} className="w-full text-xs" />
                        <TextInput placeholder="Estado" value={data.origin_state || 'Quintana Roo'} onChange={e => setData('origin_state', e.target.value)} className="w-full text-xs" />
                        <TextInput placeholder="Ciudad" value={data.origin_city || 'José María Morelos'} onChange={e => setData('origin_city', e.target.value)} className="w-full text-xs" />
                    </div>
                </div>
                <div>
                    <InputLabel value="Lugar de Destino" />
                    <div className="grid grid-cols-3 gap-1">
                        <TextInput placeholder="País" value={data.destination_country || 'México'} onChange={e => setData('destination_country', e.target.value)} className="w-full text-xs" />
                        <TextInput placeholder="Estado" value={data.destination_state || ''} onChange={e => setData('destination_state', e.target.value)} className="w-full text-xs" />
                        <TextInput placeholder="Ciudad" value={data.destination_city || ''} onChange={e => setData('destination_city', e.target.value)} className="w-full text-xs" />
                    </div>
                </div>
                <div>
                    <InputLabel value="Fechas (Salida - Regreso)" />
                    <div className="grid grid-cols-2 gap-2">
                        <TextInput type="datetime-local" value={data.departure_date || ''} onChange={e => setData('departure_date', e.target.value)} className="w-full text-xs" />
                        <TextInput type="datetime-local" value={data.return_date || ''} onChange={e => setData('return_date', e.target.value)} className="w-full text-xs" />
                    </div>
                    <div className="flex justify-between items-center mt-1">
                        <div className="text-xs text-gray-500">Duración: {data.days_duration} días</div>
                        <label className="flex items-center space-x-2 text-xs">
                            <Checkbox
                                checked={data.half_day_payment || false}
                                onChange={(e) => setData('half_day_payment', e.target.checked)}
                            />
                            <span className="text-gray-700">Pago Medio Día</span>
                        </label>
                    </div>
                </div>
            </div>

            {/* Transporte */}
            <div className="border-t border-blue-200 pt-4">
                <h4 className="font-semibold text-blue-700 mb-2">Transporte</h4>
                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <InputLabel value="Tipo de Transporte" />
                        <select
                            value={data.transport_type || 'Oficial'}
                            onChange={e => setData('transport_type', e.target.value)}
                            className="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full text-sm"
                        >
                            <option value="Oficial">Vehículo Oficial</option>
                            <option value="Particular">Vehículo Particular</option>
                            <option value="Publico">Transporte Público</option>
                        </select>
                    </div>

                    {data.transport_type === 'Oficial' && (
                        <div className="md:col-span-2">
                            <InputLabel value="Vehículo Asignado" />
                            <select
                                value={data.vehicle_id || ''}
                                onChange={e => setData('vehicle_id', e.target.value)}
                                className="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full text-sm"
                            >
                                <option value="">Seleccione Vehículo...</option>
                                {vehicles
                                    .filter(v => {
                                        // Show vehicles matching any selected commissioner's organism
                                        if (!data.commissioners_details || data.commissioners_details.length === 0) return true;
                                        // Find organisms of selected commissioners
                                        const selectedOrganisms = data.commissioners_details
                                            .map(c => getEmployee(c.id)?.organismo_id)
                                            .filter(Boolean); // Remove nulls

                                        // If no commissioners have organism (unlikely), show all?
                                        if (selectedOrganisms.length === 0) return true;

                                        return selectedOrganisms.includes(v.organismo_id);
                                    })
                                    .map(v => (
                                        <option key={v.id} value={v.id}>
                                            {v.brand} {v.model_year} - {v.plate_number}
                                        </option>
                                    ))}
                            </select>
                            {/* Debug info if no vehicles found */}
                            {vehicles.length === 0 && <span className="text-xs text-red-500">No hay vehículos registrados.</span>}
                        </div>
                    )}
                </div>
            </div>

            {/* Justificacion */}
            <div className="border-t border-blue-200 pt-4">
                <InputLabel value="Justificación de la Comisión" />
                <textarea
                    value={data.justification || ''}
                    onChange={e => setData('justification', e.target.value)}
                    className="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full h-20 text-sm"
                />
            </div>


            {/* Employee Selection */}
            <div className="border-t border-blue-200 pt-4">
                <h4 className="font-semibold text-blue-700 mb-2">1. Seleccionar Comisionados</h4>
                <div className="flex gap-4 items-end mb-4">
                    <div className="w-1/2">
                        <InputLabel value="Agregar Empleado a la Comisión" />
                        <select
                            onChange={handleAddCommissioner}
                            className="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full"
                            value=""
                        >
                            <option value="">Seleccione para agregar...</option>
                            {employees.map(e => (
                                <option key={e.id} value={e.id}>{e.clave} - {e.nombre}</option>
                            ))}
                        </select>
                    </div>
                </div>

                {/* Selected Commissioners Table */}
                {data.commissioners_details && data.commissioners_details.length > 0 && (
                    <div className="overflow-x-auto bg-white rounded shadow-sm border border-blue-100">
                        <table className="min-w-full text-xs text-left">
                            <thead className="bg-blue-100 text-blue-800 uppercase font-bold">
                                <tr>
                                    <th className="px-3 py-2">Empleado</th>
                                    <th className="px-3 py-2">N° Oficio</th>
                                    <th className="px-3 py-2">Fecha Informe</th>
                                    <th className="px-3 py-2">Link Informe</th>
                                    <th className="px-3 py-2 text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-200">
                                {data.commissioners_details.map((comm, idx) => {
                                    const emp = getEmployee(comm.id);
                                    return (
                                        <tr key={comm.id}>
                                            <td className="px-3 py-2 font-medium">
                                                {emp ? `${emp.nombre} ${emp.primer_apellido}` : 'Desconocido'}
                                            </td>
                                            <td className="px-3 py-2">
                                                <TextInput
                                                    value={comm.oficio_number || ''}
                                                    onChange={e => {
                                                        const newDetails = [...data.commissioners_details];
                                                        newDetails[idx].oficio_number = e.target.value;
                                                        setData('commissioners_details', newDetails);
                                                    }}
                                                    className="w-full text-xs p-1"
                                                    placeholder="Oficio..."
                                                />
                                            </td>
                                            <td className="px-3 py-2">
                                                <TextInput
                                                    type="date"
                                                    value={comm.report_date || ''}
                                                    onChange={e => {
                                                        const newDetails = [...data.commissioners_details];
                                                        newDetails[idx].report_date = e.target.value;
                                                        setData('commissioners_details', newDetails);
                                                    }}
                                                    className="w-full text-xs p-1"
                                                />
                                            </td>
                                            <td className="px-3 py-2">
                                                <TextInput
                                                    value={comm.report_link || ''}
                                                    onChange={e => {
                                                        const newDetails = [...data.commissioners_details];
                                                        newDetails[idx].report_link = e.target.value;
                                                        setData('commissioners_details', newDetails);
                                                    }}
                                                    className="w-full text-xs p-1"
                                                    placeholder="https://..."
                                                />
                                            </td>
                                            <td className="px-3 py-2 text-center">
                                                <button
                                                    type="button"
                                                    onClick={() => handleRemoveCommissioner(idx)}
                                                    className="text-red-500 hover:text-red-700 font-bold"
                                                >
                                                    Eliminar
                                                </button>
                                            </td>
                                        </tr>
                                    );
                                })}
                            </tbody>
                        </table>
                    </div>
                )}
            </div>

            {/* Expense Entry Section */}
            <div className="border-t border-blue-200 pt-4 bg-white p-4 rounded shadow-sm">
                <h4 className="font-semibold text-green-700 mb-2">2. Registrar Gastos Individuales</h4>
                <div className="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">

                    {/* Commissioner Dropdown */}
                    <div className="md:col-span-1">
                        <InputLabel value="Comisionado" />
                        <select
                            value={expenseForm.employee_id}
                            onChange={e => setExpenseForm(prev => ({ ...prev, employee_id: e.target.value }))}
                            className="border-gray-300 rounded-md shadow-sm w-full text-sm"
                        >
                            <option value="">Seleccione...</option>
                            {data.commissioners_details && data.commissioners_details.map(comm => {
                                const emp = getEmployee(comm.id);
                                return <option key={comm.id} value={comm.id}>{emp?.nombre} {emp?.primer_apellido}</option>
                            })}
                        </select>
                    </div>

                    {/* Type Dropdown */}
                    <div className="md:col-span-1">
                        <InputLabel value="Concepto" />
                        <select
                            value={expenseForm.type}
                            onChange={e => setExpenseForm(prev => ({ ...prev, type: e.target.value }))}
                            className="border-gray-300 rounded-md shadow-sm w-full text-sm"
                        >
                            <option value="Viaticos">Viáticos</option>
                            <option value="Hospedaje">Hospedaje</option>
                            <option value="Pasaje">Pasaje</option>
                        </select>
                    </div>

                    {/* XML Upload (Small) */}
                    <div className="md:col-span-1">
                        <InputLabel value="Factura (Opcional)" />
                        <label className="cursor-pointer inline-block w-full text-center px-2 py-2 bg-gray-200 text-xs rounded hover:bg-gray-300">
                            📂 Cargar XML
                            <input type="file" accept=".xml" className="hidden" onChange={handleExpenseXmlUpload} />
                        </label>
                        {expenseForm.uuid && <div className="text-[10px] text-green-600 mt-1 truncate">UUID: {expenseForm.uuid}</div>}
                    </div>

                    {/* Amount */}
                    <div className="md:col-span-1">
                        <InputLabel value="Monto" />
                        <TextInput
                            type="number"
                            step="0.01"
                            value={expenseForm.amount}
                            onChange={e => setExpenseForm(prev => ({ ...prev, amount: e.target.value }))}
                            className="w-full"
                        />
                    </div>

                    {/* Add Button */}
                    <div className="md:col-span-1">
                        <SecondaryButton onClick={handleAddExpense} className="w-full justify-center bg-green-100 hover:bg-green-200 text-green-800 border-green-300">
                            + Agregar Gasto
                        </SecondaryButton>
                    </div>
                </div>
            </div>

            {/* Expenses Table */}
            <div className="border-t border-blue-200 pt-4">
                <h4 className="font-semibold text-gray-700 mb-2">Desglose de Gastos (Partidas)</h4>
                {data.items && data.items.length > 0 ? (
                    <div className="overflow-x-auto">
                        <table className="min-w-full text-xs text-left bg-white rounded shadow-sm">
                            <thead className="bg-gray-100 uppercase font-bold text-gray-600">
                                <tr>
                                    <th className="px-3 py-2">Comisionado</th>
                                    <th className="px-3 py-2">Concepto</th>
                                    <th className="px-3 py-2">Factura</th>
                                    <th className="px-3 py-2 text-right">Subtotal</th>
                                    <th className="px-3 py-2 text-right">IVA</th>
                                    <th className="px-3 py-2 text-right">Total</th>
                                    <th className="px-3 py-2 text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-200">
                                {data.items.map((item, idx) => {
                                    const emp = getEmployee(item.employee_id);
                                    return (
                                        <tr key={idx}>
                                            <td className="px-3 py-2 font-medium text-gray-800">{emp ? `${emp.nombre} ${emp.primer_apellido}` : 'General'}</td>
                                            <td className="px-3 py-2">
                                                <span className={`px-2 py-0.5 rounded-full text-[10px] ${item.partida_codigo === '37501' ? 'bg-blue-100 text-blue-800' : item.partida_codigo === '37502' ? 'bg-purple-100 text-purple-800' : 'bg-orange-100 text-orange-800'}`}>
                                                    {item.description || item.partida_codigo}
                                                </span>
                                            </td>
                                            <td className="px-3 py-2 text-gray-500 text-[10px]">
                                                {item.uuid && <div><span className="font-bold">UUID:</span> {item.uuid.substring(0, 8)}...</div>}
                                                {item.invoice_folio && <div><span className="font-bold">Folio:</span> {item.invoice_folio}</div>}
                                                {item.provider_name && <div><span className="font-bold">Prov:</span> {item.provider_name.substring(0, 10)}...</div>}
                                                {item.invoice_date && <div><span className="font-bold">Fecha:</span> {item.invoice_date}</div>}
                                            </td>
                                            <td className="px-3 py-2 text-right text-gray-600">${item.invoice_subtotal ? parseFloat(item.invoice_subtotal).toFixed(2) : '-'}</td>
                                            <td className="px-3 py-2 text-right text-gray-600">${item.invoice_iva ? parseFloat(item.invoice_iva).toFixed(2) : '-'}</td>
                                            <td className="px-3 py-2 text-right font-bold">${parseFloat(item.amount).toFixed(2)}</td>
                                            <td className="px-3 py-2 text-center">
                                                <button type="button" onClick={() => handleRemoveItem(idx)} className="text-red-500 hover:text-red-700 font-bold">×</button>
                                            </td>
                                        </tr>
                                    );
                                })}
                                {/* Totals Row */}
                                <tr className="bg-gray-50 font-bold">
                                    <td colSpan="5" className="px-3 py-2 text-right uppercase">Total Viáticos (37501):</td>
                                    <td className="px-3 py-2 text-right">${(data.viaticos_amount || 0)}</td>
                                    <td></td>
                                </tr>
                                <tr className="bg-gray-50 font-bold">
                                    <td colSpan="3" className="px-3 py-2 text-right uppercase">Total Hospedaje (37502):</td>
                                    <td className="px-3 py-2 text-right">${(data.hospedaje_amount || 0)}</td>
                                    <td></td>
                                </tr>
                                <tr className="bg-gray-50 font-bold">
                                    <td colSpan="3" className="px-3 py-2 text-right uppercase">Total Pasajes (37201):</td>
                                    <td className="px-3 py-2 text-right">${(data.pasaje_amount || 0)}</td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                ) : (
                    <p className="text-sm text-gray-500 italic p-4 bg-gray-50 rounded border border-dashed border-gray-300 text-center">
                        No se han registrado gastos. Utilice el formulario de arriba para agregar.
                    </p>
                )}
            </div>

            {/* Summary Table: Authorized vs Spent */}
            {
                data.commissioner_ids && data.commissioner_ids.length > 0 && (
                    <div className="border-t border-blue-200 pt-4">
                        <h4 className="font-semibold text-blue-700 mb-2">Resumen de Viáticos por Comisionado</h4>
                        <div className="overflow-x-auto">
                            <table className="min-w-full text-xs text-left bg-blue-50 rounded">
                                <thead className="bg-blue-100 font-bold text-blue-800">
                                    <tr>
                                        <th className="px-3 py-2">Comisionado</th>
                                        <th className="px-3 py-2">Nivel</th>
                                        <th className="px-3 py-2 text-right">Cuota Diaria</th>
                                        <th className="px-3 py-2 text-right">Días</th>
                                        <th className="px-3 py-2 text-right">Máximo Autorizado</th>
                                        <th className="px-3 py-2 text-right">Gastado Real (37501)</th>
                                        <th className="px-3 py-2 text-right">A Comprobar/Pagar</th>
                                        <th className="px-3 py-2 text-center">Estado</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-blue-200">
                                    {data.commissioner_ids.map(id => {
                                        const emp = getEmployee(id);
                                        if (!emp) return null;

                                        const limitPerDay = getAuthorizedLimit(id);
                                        const duration = parseInt(data.days_duration) || 1;
                                        const maxAuthorized = limitPerDay * duration;

                                        const viaticosId = partidas.find(p => p.codigo === '37501')?.id;
                                        const actualSpent = data.items
                                            .filter(item => item.employee_id == id && item.partida_id === viaticosId)
                                            .reduce((sum, item) => sum + (parseFloat(item.amount) || 0), 0);

                                        const payable = Math.min(actualSpent, maxAuthorized);
                                        const isOverLimit = actualSpent > maxAuthorized;

                                        return (
                                            <tr key={id}>
                                                <td className="px-3 py-2">{emp.nombre} {emp.primer_apellido}</td>
                                                <td className="px-3 py-2">{emp.nivel}</td>
                                                <td className="px-3 py-2 text-right">${limitPerDay.toFixed(2)}</td>
                                                <td className="px-3 py-2 text-right">{duration}</td>
                                                <td className="px-3 py-2 text-right font-medium">${maxAuthorized.toFixed(2)}</td>
                                                <td className="px-3 py-2 text-right text-gray-700">${actualSpent.toFixed(2)}</td>
                                                <td className="px-3 py-2 text-right font-bold text-green-700">${payable.toFixed(2)}</td>
                                                <td className="px-3 py-2 text-center">
                                                    {isOverLimit ?
                                                        <button
                                                            type="button"
                                                            onClick={() => handleAdjustLimit(id, maxAuthorized)}
                                                            className="text-red-600 font-bold text-[10px] hover:text-red-800 underline"
                                                            title={`Ajustar a límite (Reducir $${(actualSpent - maxAuthorized).toFixed(2)})`}
                                                        >
                                                            EXCEDE LÍMITE (AJUSTAR)
                                                        </button> :
                                                        <span className="text-green-600 font-bold text-[10px]">OK</span>
                                                    }
                                                </td>
                                            </tr>
                                        );
                                    })}
                                </tbody>
                            </table>
                        </div>
                    </div>
                )
            }

        </div >
    );
}



