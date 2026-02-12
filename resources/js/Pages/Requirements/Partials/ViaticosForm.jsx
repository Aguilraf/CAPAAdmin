import React, { useEffect } from 'react';
import TextInput from '@/Components/TextInput';
import InputLabel from '@/Components/InputLabel';
import Checkbox from '@/Components/Checkbox';

export default function ViaticosForm({ data, setData, employees, partidas, vehicles, travelAllowanceRates = [] }) {

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

        // Determine zona based on destination state
        // Zona I: Quintana Roo (local)
        // Zona II: Other states (national)
        const isZona1 = data.destination_state?.toLowerCase().includes('quintana roo');
        const zonaField = isZona1 ? 'zona_1_amount' : 'zona_2_amount';

        // Find rates for this employee's cargo and nivel
        const viaticosRate = travelAllowanceRates.find(
            r => r.cargo === cargo && r.nivel === nivel && r.rate_type === 'viaticos'
        );
        const pasajesRate = travelAllowanceRates.find(
            r => r.cargo === cargo && r.nivel === nivel && r.rate_type === 'pasajes'
        );
        const hospedajeRate = travelAllowanceRates.find(
            r => r.cargo === cargo && r.nivel === nivel && r.rate_type === 'hospedaje'
        );

        // Auto-fill amounts if rates found and checkboxes are checked
        const updates = {};

        if (data.has_viaticos && viaticosRate && !data.viaticos_amount) {
            updates.viaticos_amount = parseFloat(viaticosRate[zonaField]);
            updates.viaticos_partida_id = viaticosRate.partida_id;
        }

        if (data.has_pasaje && pasajesRate && !data.pasaje_amount) {
            updates.pasaje_amount = parseFloat(pasajesRate[zonaField]);
            updates.pasaje_partida_id = pasajesRate.partida_id;
        }

        if (data.has_hospedaje && hospedajeRate && !data.hospedaje_amount) {
            updates.hospedaje_amount = parseFloat(hospedajeRate[zonaField]);
            updates.hospedaje_partida_id = hospedajeRate.partida_id;
        }

        if (Object.keys(updates).length > 0) {
            setData(prev => ({ ...prev, ...updates }));
        }
    }, [selectedEmployee, data.destination_state, data.has_viaticos, data.has_pasaje, data.has_hospedaje, travelAllowanceRates]);

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
        if (data.has_pasaje) addExpenseItem(data.pasaje_partida_id, 'Pasaje', data.pasaje_amount || 0);
        if (data.has_hospedaje) addExpenseItem(data.hospedaje_partida_id, 'Hospedaje', data.hospedaje_amount || 0);

        // Only update if there are changes to avoid loops, but since we recreate array, strict equality check might fail.
        // We rely on parent Form's handle logic or just setData here.
        // NOTE: This will overwrite manual items if the user adds others. 
        // For Viaticos type, we assume these ARE the items.
        // To allow extras, we would need to merge. For now, let's overwrite to keep it simple as per requirement structure.
        if (newItems.length > 0) {
            setData(prev => ({ ...prev, items: newItems }));
        }

    }, [data.has_viaticos, data.viaticos_partida_id, data.viaticos_amount, data.has_pasaje, data.pasaje_partida_id, data.pasaje_amount, data.has_hospedaje, data.hospedaje_partida_id, data.hospedaje_amount]);


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
                            <select
                                value={data.viaticos_partida_id || ''}
                                onChange={e => setData('viaticos_partida_id', e.target.value)}
                                className="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full text-xs"
                                disabled={!data.has_viaticos}
                            >
                                <option value="">Seleccione Partida para Viáticos...</option>
                                {partidas.filter(p => p.nombre.toLowerCase().includes('viaticos') || p.nombre.toLowerCase().includes('servicio')).map(p => (
                                    <option key={p.id} value={p.id}>{p.codigo} - {p.nombre}</option>
                                ))}
                            </select>
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

                    {/* Pasaje */}
                    <div className="flex items-center space-x-4">
                        <div className="flex items-center h-5">
                            <Checkbox
                                id="has_pasaje"
                                checked={data.has_pasaje || false}
                                onChange={(e) => setData('has_pasaje', e.target.checked)}
                            />
                        </div>
                        <div className="text-sm font-medium text-gray-700 w-24">Pasaje</div>
                        <div className="flex-1">
                            <select
                                value={data.pasaje_partida_id || ''}
                                onChange={e => setData('pasaje_partida_id', e.target.value)}
                                className="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full text-xs"
                                disabled={!data.has_pasaje}
                            >
                                <option value="">Seleccione Partida para Pasaje...</option>
                                {partidas.filter(p => p.nombre.toLowerCase().includes('pasaje') || p.nombre.toLowerCase().includes('transporte')).map(p => (
                                    <option key={p.id} value={p.id}>{p.codigo} - {p.nombre}</option>
                                ))}
                            </select>
                        </div>
                        <div className="w-32">
                            <TextInput
                                type="number"
                                placeholder="Importe"
                                step="0.01"
                                value={data.pasaje_amount || ''}
                                onChange={e => setData('pasaje_amount', e.target.value)}
                                className="mt-1 block w-full text-xs"
                                disabled={!data.has_pasaje}
                            />
                        </div>
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
                            <select
                                value={data.hospedaje_partida_id || ''}
                                onChange={e => setData('hospedaje_partida_id', e.target.value)}
                                className="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full text-xs"
                                disabled={!data.has_hospedaje}
                            >
                                <option value="">Seleccione Partida para Hospedaje...</option>
                                {partidas.filter(p => p.nombre.toLowerCase().includes('hospedaje')).map(p => (
                                    <option key={p.id} value={p.id}>{p.codigo} - {p.nombre}</option>
                                ))}
                            </select>
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
