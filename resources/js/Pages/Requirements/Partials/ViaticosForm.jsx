import React, { useEffect, useState } from 'react';
import TextInput from '@/Components/TextInput';
import InputLabel from '@/Components/InputLabel';
import Checkbox from '@/Components/Checkbox';
import SecondaryButton from '@/Components/SecondaryButton';

export default function ViaticosForm({ data, setData, employees, partidas, vehicles, travelAllowanceRates = [] }) {

    // Initialize Pasajes List State (Default 37201 if empty)
    const [pasajesList, setPasajesList] = useState(() => {
        // Try to reconstruct from data items if editing, else default
        // For simplicity in Create mode, start with one default.
        // In Edit mode, we might need to parse existing items, but data.items is complex.
        // Let's rely on data.pasaje_items if we decide to store it there, 
        // or just default to 37201 for now as per user request.
        // If data.pasaje_partida_id exists, use it.
        const defaultPasaje = partidas.find(p => p.codigo === '37201');
        return [{
            id: Date.now(),
            partida_id: data.pasaje_partida_id || (defaultPasaje ? defaultPasaje.id : ''),
            amount: data.pasaje_amount || 0
        }];
    });

    // Auto-fill Employee Data
    const handleCommissionerChange = (e) => {
        const empId = e.target.value;
        setData(prev => ({ ...prev, commissioner_id: empId }));
    };

    const selectedEmployee = employees.find(e => e.id == data.commissioner_id);

    // Auto-fill amounts from rates catalog
    useEffect(() => {
        if (!selectedEmployee || !data.destination_state || travelAllowanceRates.length === 0) {
            return;
        }

        const cargo = selectedEmployee.cargo;
        const nivel = selectedEmployee.nivel;

        // Determine zona based on selection or destination state
        // Zona I: Quintana Roo (local)
        // Zona II: Other states (national)
        const currentZona = data.zona || (data.destination_state?.toLowerCase().includes('quintana roo') ? 'I' : 'II');
        const zonaField = currentZona === 'I' ? 'zona_1_amount' : 'zona_2_amount';

        // Normalize helper
        const normalize = (str) => str ? str.toString().trim().toUpperCase() : '';

        // Find rates for this employee's cargo and nivel
        // Cascading lookup:
        // 1. Try exact match (Cargo + Nivel)
        // 2. Try match by Nivel only (if Cargo spelling differs)

        console.log('ViaticosForm: Auto-filling for', { cargo, nivel, zone: currentZona });

        const findRate = (type) => {
            // 1. Exact Match
            let rate = travelAllowanceRates.find(
                r => normalize(r.cargo) === normalize(cargo) && normalize(r.nivel) === normalize(nivel) && r.rate_type === type
            );

            // 2. Nivel Match (Fallback)
            if (!rate) {
                rate = travelAllowanceRates.find(
                    r => normalize(r.nivel) === normalize(nivel) && r.rate_type === type
                );
                if (rate) console.log(`Found ${type} rate by Nivel match only:`, rate);
            } else {
                console.log(`Found ${type} rate by Exact match:`, rate);
            }
            return rate;
        };

        const viaticosRate = findRate('viaticos');
        const pasajesRate = findRate('pasajes');
        const hospedajeRate = findRate('hospedaje');

        // Auto-fill amounts if rates found and checkboxes are checked
        const updates = {};

        // Hardcode Partida IDs
        const viaticosPartida = partidas.find(p => p.codigo === '37501');
        const pasajePartida = partidas.find(p => p.codigo === '37201');
        const hospedajePartida = partidas.find(p => p.codigo === '37502');

        if (data.has_viaticos) {
            if (viaticosPartida) updates.viaticos_partida_id = viaticosPartida.id;
            if (viaticosRate) {
                let amount = parseFloat(viaticosRate[zonaField]);
                if (data.half_day_payment) {
                    amount = amount / 2;
                }
                // Multiply by duration (default to 1 if missing)
                const duration = parseInt(data.days_duration) || 1;
                updates.viaticos_amount = (amount * duration).toFixed(2);
            }
        }

        // Pasaje Logic - Update first item in list if needed
        // NOTE: User wants specific behavior for pasajes dropdown (37201, 37301). 
        // Auto-fill might conflict with manual multiple rows. 
        // We will only auto-fill the FIRST row if it matches default 37201 and amount is 0.
        if (data.has_pasaje) {
            setPasajesList(prev => {
                const newList = [...prev];
                if (newList.length > 0 && newList[0].amount == 0 && pasajesRate) {
                    newList[0].amount = parseFloat(pasajesRate[zonaField]).toFixed(2);
                }
                // Also ensure ID is set if empty
                if (newList.length > 0 && !newList[0].partida_id && pasajePartida) {
                    newList[0].partida_id = pasajePartida.id;
                }
                return newList;
            });
        }

        if (data.has_hospedaje) {
            if (hospedajePartida) updates.hospedaje_partida_id = hospedajePartida.id;
            if (hospedajeRate && !data.hospedaje_amount) {
                updates.hospedaje_amount = parseFloat(hospedajeRate[zonaField]).toFixed(2);
            }
        }

        if (Object.keys(updates).length > 0) {
            setData(prev => ({ ...prev, ...updates }));
        }
    }, [selectedEmployee, data.destination_state, data.has_viaticos, data.has_pasaje, data.has_hospedaje, travelAllowanceRates, partidas, data.half_day_payment, data.days_duration, data.zona]);

    // Calculate Duration
    useEffect(() => {
        if (data.departure_date && data.return_date) {
            const start = new Date(data.departure_date);
            const end = new Date(data.return_date);
            const diffTime = Math.abs(end - start);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            setData(prev => ({ ...prev, days_duration: diffDays }));
        }
    }, [data.departure_date, data.return_date]);

    // Handle Pasajes List Changes
    const updatePasajeItem = (index, field, value) => {
        const newList = [...pasajesList];
        newList[index][field] = value;
        setPasajesList(newList);
    };

    const addPasajeItem = () => {
        // Default to 37301 for second item as requested, or 37201 fallback
        const defaultNext = partidas.find(p => p.codigo === '37301') || partidas.find(p => p.codigo === '37201');
        setPasajesList([...pasajesList, { id: Date.now(), partida_id: defaultNext ? defaultNext.id : '', amount: 0 }]);
    };

    const removePasajeItem = (index) => {
        const newList = [...pasajesList];
        newList.splice(index, 1);
        setPasajesList(newList);
    };

    // Sync Expenses with Main Items
    useEffect(() => {
        const newItems = [];

        const addExpenseItem = (partidaId, description, amount) => {
            if (partidaId && amount > 0) {
                const partida = partidas.find(p => p.id == partidaId);
                if (partida) {
                    newItems.push({
                        partida_id: partida.id,
                        capitulo_id: partida.capitulo_id,
                        description: description,
                        amount: parseFloat(amount)
                    });
                }
            }
        };

        if (data.has_viaticos) addExpenseItem(data.viaticos_partida_id, 'Viáticos', data.viaticos_amount || 0);

        // Sync Pasajes List
        let totalPasaje = 0;
        let mainPasajeId = null;

        if (data.has_pasaje) {
            pasajesList.forEach((p, idx) => {
                const amount = parseFloat(p.amount) || 0;
                totalPasaje += amount;
                if (idx === 0) mainPasajeId = p.partida_id; // Keep first as main for TravelAllowance table
                addExpenseItem(p.partida_id, `Pasaje (${idx + 1})`, amount);
            });
        }

        if (data.has_hospedaje) addExpenseItem(data.hospedaje_partida_id, 'Hospedaje', data.hospedaje_amount || 0);

        // Update Data Props for TravelAllowance (Aggregates)
        // We need to avoid infinite loops, so check if values changed
        const updates = {};
        if (data.pasaje_amount !== totalPasaje) updates.pasaje_amount = totalPasaje;
        if (data.pasaje_partida_id !== mainPasajeId) updates.pasaje_partida_id = mainPasajeId;

        if (Object.keys(updates).length > 0) {
            setData(prev => ({ ...prev, ...updates }));
        }

        // Sync to parent items
        // NOTE: This comparison is simple (length check), meant to reduce re-renders. 
        // Real app might need deep compare or just accept re-renders.
        if (JSON.stringify(newItems) !== JSON.stringify(data.items)) {
            setData(prev => ({ ...prev, items: newItems }));
        }

    }, [data.has_viaticos, data.viaticos_partida_id, data.viaticos_amount,
    data.has_pasaje, pasajesList,
    data.has_hospedaje, data.hospedaje_partida_id, data.hospedaje_amount]);


    return (
        <div className="bg-blue-50 p-6 rounded-lg space-y-6 border border-blue-200">
            <h3 className="text-lg font-bold text-blue-800">Detalles de Comisión (Viáticos)</h3>

            {/* Header Data */}
            <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <InputLabel value="Número de Oficio" />
                    <TextInput
                        value={data.oficio_number || ''}
                        onChange={e => setData('oficio_number', e.target.value)}
                        className="mt-1 block w-full"
                    />
                </div>
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

            {/* Employee Selection & Info */}
            <div className="border-t border-blue-200 pt-4">
                <h4 className="font-semibold text-blue-700 mb-2">Datos del Comisionado</h4>
                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <InputLabel value="Seleccionar Empleado" />
                        <select
                            value={data.commissioner_id || ''}
                            onChange={handleCommissionerChange}
                            className="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full"
                        >
                            <option value="">Seleccione...</option>
                            {employees.map(e => (
                                <option key={e.id} value={e.id}>{e.clave} - {e.nombre}</option>
                            ))}
                        </select>
                    </div>
                </div>

                {selectedEmployee && (
                    <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4 bg-white p-3 rounded shadow-sm text-sm">
                        <div><span className="font-bold">Nombre:</span> {selectedEmployee.primer_nombre || 'N/A'}</div>
                        <div><span className="font-bold">Primer Apellido:</span> {selectedEmployee.primer_apellido || 'N/A'}</div>
                        <div><span className="font-bold">Segundo Apellido:</span> {selectedEmployee.segundo_apellido || 'N/A'}</div>
                        <div><span className="font-bold">Puesto:</span> {selectedEmployee.puesto}</div>
                        <div><span className="font-bold">Cargo:</span> {selectedEmployee.cargo || 'N/A'}</div>
                        <div><span className="font-bold">Nivel:</span> {selectedEmployee.nivel}</div>
                        <div><span className="font-bold">RFC:</span> {selectedEmployee.rfc}</div>
                        <div><span className="font-bold">Banco:</span> {selectedEmployee.banco || 'N/A'}</div>
                        <div><span className="font-bold">CLABE:</span> {selectedEmployee.clabe || 'N/A'}</div>
                        <div><span className="font-bold">Área Adscripción:</span> {selectedEmployee.departamento}</div> {/* Using departamento as area */}
                        <div><span className="font-bold">Tipo Plaza:</span> {selectedEmployee.categoria || 'N/A'}</div>
                        <div><span className="font-bold">Sindicalizado:</span> {selectedEmployee.es_sindicalizado ? 'SÍ' : 'NO'}</div>
                        <div className="col-span-2"><span className="font-bold">Jefe Inmediato:</span> {selectedEmployee.jefe_inmediato || 'N/A'}</div>
                    </div>
                )}
            </div>

            {/* Travel Details */}
            <div className="border-t border-blue-200 pt-4">
                <h4 className="font-semibold text-blue-700 mb-2">Itinerario</h4>
                <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    {/* Origin (Fixed mostly but editable) */}
                    <div>
                        <InputLabel value="Origen (País, Edo, Ciudad)" />
                        <div className="flex space-x-1">
                            <TextInput value={data.origin_country || 'México'} onChange={e => setData('origin_country', e.target.value)} className="w-1/3 text-xs" placeholder="País" />
                            <TextInput value={data.origin_state || 'Quintana Roo'} onChange={e => setData('origin_state', e.target.value)} className="w-1/3 text-xs" placeholder="Estado" />
                            <TextInput value={data.origin_city || 'José María Morelos'} onChange={e => setData('origin_city', e.target.value)} className="w-1/3 text-xs" placeholder="Ciudad" />
                        </div>
                    </div>
                    {/* Destination */}
                    <div className="col-span-2">
                        <InputLabel value="Destino (País, Edo, Ciudad)" />
                        <div className="flex space-x-1">
                            <TextInput value={data.destination_country || 'México'} onChange={e => setData('destination_country', e.target.value)} className="w-1/3 text-xs" placeholder="País" />
                            <TextInput value={data.destination_state || ''} onChange={e => setData('destination_state', e.target.value)} className="w-1/3 text-xs" placeholder="Estado" />
                            <TextInput value={data.destination_city || ''} onChange={e => setData('destination_city', e.target.value)} className="w-1/3 text-xs" placeholder="Ciudad" />
                        </div>
                    </div>
                </div>

                <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <InputLabel value="Salida (Fecha/Hora)" />
                        <TextInput type="datetime-local" value={data.departure_date || ''} onChange={e => setData('departure_date', e.target.value)} className="block w-full text-xs" />
                    </div>
                    <div>
                        <InputLabel value="Regreso (Fecha/Hora)" />
                        <TextInput type="datetime-local" value={data.return_date || ''} onChange={e => setData('return_date', e.target.value)} className="block w-full text-xs" />
                    </div>
                    <div>
                        <InputLabel value="Días" />
                        <TextInput value={data.days_duration || ''} readOnly className="block w-full bg-gray-100" />
                    </div>
                    <div className="flex items-center space-x-2 mt-6">
                        <Checkbox
                            id="half_day_payment"
                            checked={data.half_day_payment || false}
                            onChange={(e) => setData('half_day_payment', e.target.checked)}
                        />
                        <label htmlFor="half_day_payment" className="text-sm font-medium text-gray-700">
                            Medio día
                        </label>
                    </div>
                </div>

                <div className="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <InputLabel value="Zona" />
                        <select
                            value={data.zona || (data.destination_state?.toLowerCase().includes('quintana roo') ? 'I' : 'II')}
                            onChange={e => setData('zona', e.target.value)}
                            className="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full"
                        >
                            <option value="I">Zona I (Local / Quintana Roo)</option>
                            <option value="II">Zona II (Nacional / Otros Estados)</option>
                        </select>
                    </div>
                </div>

                <div className="mt-4">
                    <InputLabel value="Justificación de la Comisión" />
                    <textarea
                        value={data.justification || ''}
                        onChange={e => setData('justification', e.target.value)}
                        className="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full"
                        rows="2"
                    ></textarea>
                </div>
            </div>

            {/* Expenses & Budget Items */}
            <div className="border-t border-blue-200 pt-4">
                <h4 className="font-semibold text-blue-700 mb-2">Desglose de Gastos y Partidas</h4>
                <div className="space-y-3">
                    {/* Viaticos */}
                    <div className="flex items-center space-x-4">
                        <div className="flex items-center h-5">
                            <Checkbox
                                id="has_viaticos"
                                checked={data.has_viaticos || false}
                                onChange={(e) => setData('has_viaticos', e.target.checked)}
                            />
                        </div>
                        <div className="text-sm font-medium text-gray-700 w-24">Viáticos</div>
                        <div className="flex-1">
                            <div className="text-sm text-gray-600 bg-gray-50 border border-gray-300 rounded-md p-2">
                                37501 - Viáticos en el país
                            </div>
                        </div>
                        <div className="w-32">
                            <TextInput
                                type="number"
                                placeholder="Importe"
                                step="0.01"
                                value={data.viaticos_amount || ''}
                                onChange={e => setData('viaticos_amount', e.target.value)}
                                className="mt-1 block w-full text-xs"
                                disabled={!data.has_viaticos}
                            />
                        </div>
                    </div>

                    {/* Pasajes List */}
                    <div className="space-y-2 border-l-4 border-indigo-200 pl-4 py-2 my-2">
                        <div className="flex items-center space-x-4 mb-2">
                            <div className="flex items-center h-5">
                                <Checkbox
                                    id="has_pasaje"
                                    checked={data.has_pasaje || false}
                                    onChange={(e) => setData('has_pasaje', e.target.checked)}
                                />
                            </div>
                            <div className="text-sm font-medium text-gray-700">Pasaje</div>
                        </div>

                        {data.has_pasaje && pasajesList.map((pasaje, index) => (
                            <div key={pasaje.id} className="flex items-center space-x-4">
                                <div className="w-8 text-xs text-gray-500 text-right">{index + 1}.</div>
                                <div className="flex-1">
                                    <select
                                        value={pasaje.partida_id || ''}
                                        onChange={e => updatePasajeItem(index, 'partida_id', e.target.value)}
                                        className="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full text-xs"
                                    >
                                        <option value="">Seleccione Partida...</option>
                                        {partidas.filter(p => p.codigo === '37201' || p.codigo === '37301').map(p => (
                                            <option key={p.id} value={p.id}>{p.codigo} - {p.nombre}</option>
                                        ))}
                                    </select>
                                </div>
                                <div className="w-32">
                                    <TextInput
                                        type="number"
                                        placeholder="Importe"
                                        step="0.01"
                                        value={pasaje.amount || ''}
                                        onChange={e => updatePasajeItem(index, 'amount', e.target.value)}
                                        className="mt-1 block w-full text-xs"
                                    />
                                </div>
                                <div>
                                    {index > 0 && <button type="button" onClick={() => removePasajeItem(index)} className="text-red-500 font-bold">X</button>}
                                </div>
                            </div>
                        ))}

                        {data.has_pasaje && (
                            <div className="flex justify-start pl-12 mt-2">
                                <SecondaryButton onClick={addPasajeItem} type="button" size="sm">
                                    + Agregar Pasaje (37301)
                                </SecondaryButton>
                            </div>
                        )}
                    </div>

                    {/* Hospedaje */}
                    <div className="flex items-center space-x-4">
                        <div className="flex items-center h-5">
                            <Checkbox
                                id="has_hospedaje"
                                checked={data.has_hospedaje || false}
                                onChange={(e) => setData('has_hospedaje', e.target.checked)}
                            />
                        </div>
                        <div className="text-sm font-medium text-gray-700 w-24">Hospedaje</div>
                        <div className="flex-1">
                            <div className="text-sm text-gray-600 bg-gray-50 border border-gray-300 rounded-md p-2">
                                37502 - Hospedaje
                            </div>
                        </div>
                        <div className="w-32">
                            <TextInput
                                type="number"
                                placeholder="Importe"
                                step="0.01"
                                value={data.hospedaje_amount || ''}
                                onChange={e => setData('hospedaje_amount', e.target.value)}
                                className="mt-1 block w-full text-xs"
                                disabled={!data.has_hospedaje}
                            />
                        </div>
                    </div>
                </div>
            </div>

            {/* Transport */}
            <div className="border-t border-blue-200 pt-4">
                <h4 className="font-semibold text-blue-700 mb-2">Transporte</h4>
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <InputLabel value="Tipo de Transporte" />
                        <select
                            value={data.transport_type || 'Oficial'}
                            onChange={e => setData('transport_type', e.target.value)}
                            className="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full"
                        >
                            <option value="Oficial">Oficial</option>
                            <option value="Particular">Particular</option>
                            <option value="Publico">Público</option>
                        </select>
                    </div>

                    {data.transport_type === 'Oficial' && (
                        <div>
                            <InputLabel value="Seleccionar Vehículo Oficial" />
                            <select
                                value={data.vehicle_id || ''}
                                onChange={e => setData('vehicle_id', e.target.value)}
                                className="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full"
                            >
                                <option value="">Seleccione...</option>
                                {vehicles.map(v => (
                                    <option key={v.id} value={v.id}>{v.brand} {v.model} - {v.plate}</option>
                                ))}
                            </select>
                        </div>
                    )}
                </div>
            </div>

            {/* Invoice Data (Fiscal) */}
            <div className="border-t border-blue-200 pt-4">
                <h4 className="font-semibold text-blue-700 mb-2">Datos de Facturación (Opcional/Preliminar)</h4>
                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <InputLabel value="Folio Fiscal / Factura" />
                        <TextInput value={data.invoice_folio || ''} onChange={e => setData('invoice_folio', e.target.value)} className="mt-1 block w-full" />
                    </div>
                    <div>
                        <InputLabel value="Fecha Factura" />
                        <TextInput type="date" value={data.invoice_date || ''} onChange={e => setData('invoice_date', e.target.value)} className="mt-1 block w-full" />
                    </div>
                    <div>
                        <InputLabel value="RFC Proveedor" />
                        <TextInput value={data.provider_rfc || ''} onChange={e => setData('provider_rfc', e.target.value)} className="mt-1 block w-full" />
                    </div>
                </div>
            </div>

        </div>
    );
}



