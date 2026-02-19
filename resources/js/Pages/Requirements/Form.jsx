import { useForm } from '@inertiajs/react';
import { useEffect, useState, useRef } from 'react';
import TextInput from '@/Components/TextInput';
import InputLabel from '@/Components/InputLabel';
import InputError from '@/Components/InputError';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import ViaticosForm from './Partials/ViaticosForm';

export default function RequirementForm({
    initialData = {},
    mode = 'create',
    employees,
    capitulos = [],
    partidas,
    types,
    nextNumber,
    year,
    vehicles = [], // Receive vehicles
    defaultSignatories = {}, // Receive defaults
    defaultLegend = '', // Receive legend
    travelAllowanceRates = [] // Receive rates
}) {
    // Helper to find chapter from partida
    const getCapituloFromPartida = (partidaId) => {
        if (!partidaId) return '';
        const partida = partidas.find(p => p.id == partidaId);
        return partida ? partida.capitulo_id : '';
    };

    // Initialize items
    const initializeItems = (items) => {
        if (!items || items.length === 0) return [{ capitulo_id: '', partida_id: '', description: '', amount: 0 }];
        return items.map(item => ({
            ...item,
            capitulo_id: item.capitulo_id || getCapituloFromPartida(item.partida_id)
        }));
    };

    // Find defaults
    const defaultManager = employees.find(e => e.puesto && e.puesto.toLowerCase().includes('gerente'));
    const defaultElaborator = employees.find(e => e.puesto && e.puesto.toUpperCase() === 'SUBGERENTE ADMINISTRATIVO');


    // Helper to format date for datetime-local input
    const formatDateForInput = (dateString) => {
        if (!dateString) return '';
        if (typeof dateString === 'string' && dateString.match(/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/)) {
            return dateString.replace(' ', 'T').substring(0, 16);
        }
        if (typeof dateString === 'string' && dateString.includes('T')) {
            return dateString.substring(0, 16);
        }
        return dateString;
    };

    const { data, setData, post, put, processing, errors } = useForm({
        year: initialData.year || year,
        requirement_number: initialData.requirement_number || nextNumber,
        type: initialData.type || 'bomberos',
        assignment_date: initialData.assignment_date || new Date().toISOString().split('T')[0],
        coordinator_id: initialData.coordinator_id || defaultSignatories.coordinator_id || '',
        director_id: initialData.director_id || defaultSignatories.director_id || '',
        manager_id: initialData.manager_id || (defaultManager ? defaultManager.id : ''),
        elaborator_id: initialData.elaborator_id || (defaultElaborator ? defaultElaborator.id : ''),
        month_charged: initialData.month_charged || '',
        month_billed: initialData.month_billed || '',
        start_date: initialData.start_date || '',
        end_date: initialData.end_date || '',
        due_date: initialData.due_date || '',
        description: initialData.description || '',
        items: initializeItems(initialData.items),
        cfe_receipts: initialData.cfe_receipts || [], // New State for CFE Receipts

        // Setup Viaticos Default
        commission_summary_legend: initialData.travel_allowance?.commission_summary_legend || defaultLegend,
        exercise_year: initialData.travel_allowance?.exercise_year || new Date().getFullYear(),
        quarter: initialData.travel_allowance?.quarter || '',

        // Origin / Destination
        origin_country: initialData.travel_allowance?.origin_country || 'México',
        origin_state: initialData.travel_allowance?.origin_state || 'Quintana Roo',
        origin_city: initialData.travel_allowance?.origin_city || 'José María Morelos',
        destination_country: initialData.travel_allowance?.destination_country || 'México',
        destination_state: initialData.travel_allowance?.destination_state || '',
        destination_city: initialData.travel_allowance?.destination_city || '',

        // Dates & Duration
        departure_date: formatDateForInput(initialData.travel_allowance?.departure_date),
        return_date: formatDateForInput(initialData.travel_allowance?.return_date),
        days_duration: initialData.travel_allowance?.days_duration || 1,
        half_day_payment: (initialData.travel_allowance?.half_day_payment == 1 || initialData.travel_allowance?.half_day_payment === true) ? true : false,
        justification: initialData.travel_allowance?.justification || '',

        // Transport
        transport_type: initialData.travel_allowance?.transport_type || 'Oficial',
        vehicle_id: initialData.travel_allowance?.vehicle_id || '',

        // Booleans & Amounts (if needed for editing logic, though mostly calculated)
        has_viaticos: initialData.travel_allowance?.has_viaticos ? Boolean(initialData.travel_allowance.has_viaticos) : false,
        viaticos_partida_id: initialData.travel_allowance?.viaticos_partida_id || '',
        has_pasaje: initialData.travel_allowance?.has_pasaje ? Boolean(initialData.travel_allowance.has_pasaje) : false,
        pasaje_partida_id: initialData.travel_allowance?.pasaje_partida_id || '',
        has_hospedaje: initialData.travel_allowance?.has_hospedaje ? Boolean(initialData.travel_allowance.has_hospedaje) : false,
        hospedaje_partida_id: initialData.travel_allowance?.hospedaje_partida_id || '',

        commissioners_details: initialData.travel_allowance && initialData.travel_allowance.commissioners
            ? initialData.travel_allowance.commissioners.map(c => ({
                id: c.id,
                oficio_number: c.pivot?.oficio_number || ''
            }))
            : [],

        ...initialData
    });

    const [totals, setTotals] = useState({ subtotal: 0, iva: 0, total: 0 });
    const fileInputRef = useRef(null);

    // Calculate Totals
    useEffect(() => {
        let finalSub = 0;
        let finalIva = 0;
        let finalTotal = 0;

        if (data.type === 'cfe' && data.cfe_receipts && data.cfe_receipts.length > 0) {
            // CFE Logic: Sum receipts directly
            const sumSub = data.cfe_receipts.reduce((acc, r) => acc + Number(r.subtotal || 0), 0);
            const sumIva = data.cfe_receipts.reduce((acc, r) => acc + Number(r.iva || 0), 0);
            const sumTotal = data.cfe_receipts.reduce((acc, r) => acc + Number(r.total || 0), 0);

            finalSub = sumSub;
            finalIva = sumIva;
            finalTotal = sumTotal;
        } else if (data.type === 'viaticos') {
            // Viaticos Logic: Sum items directly (amount includes relevant taxes or is exempt)
            const sub = data.items.reduce((acc, item) => acc + Number(item.amount || 0), 0);
            finalSub = sub;
            finalIva = 0; // Or sum specific IVA if tracked, but for now 0 as per controller
            finalTotal = finalSub;
        } else {
            // Standard Logic: Sum items + 16% IVA
            const sub = data.items.reduce((acc, item) => acc + Number(item.amount || 0), 0);
            finalSub = sub;
            finalIva = sub * 0.16;
            finalTotal = finalSub + finalIva;
        }

        setTotals({
            subtotal: parseFloat(finalSub.toFixed(2)),
            iva: parseFloat(finalIva.toFixed(2)),
            total: parseFloat(finalTotal.toFixed(2))
        });
    }, [data.items, data.cfe_receipts, data.type]);

    const submit = (e) => {
        e.preventDefault();
        if (mode === 'create') {
            post(route('requirements.store'));
        } else {
            put(route('requirements.update', initialData.id));
        }
    };

    const addItem = () => {
        setData('items', [...data.items, { capitulo_id: '', partida_id: '', description: '', amount: 0 }]);
    };

    const removeItem = (index) => {
        const newItems = [...data.items];
        newItems.splice(index, 1);
        setData('items', newItems);
    };

    const updateItem = (index, field, value) => {
        const newItems = [...data.items];
        newItems[index][field] = value;
        if (field === 'capitulo_id') {
            newItems[index]['partida_id'] = '';
        }
        setData('items', newItems);
    };

    const handleXmlUpload = async (e) => {
        const files = Array.from(e.target.files);
        if (files.length === 0) return;

        const newReceipts = [];
        const parser = new DOMParser();

        for (const file of files) {
            try {
                const text = await file.text();
                const xmlDoc = parser.parseFromString(text, "text/xml");

                // --- 1. UUID & General Info ---
                const timbre = xmlDoc.getElementsByTagName('tfd:TimbreFiscalDigital')[0]
                    || xmlDoc.getElementsByTagName('TimbreFiscalDigital')[0];
                const uuid = timbre ? timbre.getAttribute('UUID') : '';

                const comprobante = xmlDoc.getElementsByTagName('cfdi:Comprobante')[0]
                    || xmlDoc.getElementsByTagName('Comprobante')[0];

                const subtotal = comprobante ? parseFloat(comprobante.getAttribute('SubTotal')) : 0;
                const total = comprobante ? parseFloat(comprobante.getAttribute('Total')) : 0;

                // --- 2. Addenda Parsing (Address, Town, RPU) ---
                let rpu = '';
                let town = '';
                let address = '';

                // Look for RPU in cfe:ComisionFederalElectricidad
                const cfeNode = xmlDoc.getElementsByTagName('cfe:ComisionFederalElectricidad')[0]
                    || xmlDoc.getElementsByTagName('ComisionFederalElectricidad')[0];
                if (cfeNode && cfeNode.hasAttribute('RPU')) {
                    rpu = cfeNode.getAttribute('RPU');
                }

                // Look for Address/Town in fa:Datos (Namespace often used in Addenda)
                const faDatos = xmlDoc.getElementsByTagName('fa:Datos')[0]
                    || xmlDoc.getElementsByTagName('Datos')[0];

                if (faDatos) {
                    if (faDatos.hasAttribute('calle')) address = faDatos.getAttribute('calle');
                    if (faDatos.hasAttribute('municipio')) town = faDatos.getAttribute('municipio');
                }

                // Fallback: Search in Concept Description if still missing RPU/Addr
                const conceptos = Array.from(xmlDoc.getElementsByTagName('cfdi:Concepto')
                    || xmlDoc.getElementsByTagName('Concepto'));

                let conceptDescription = '';
                conceptos.forEach(c => {
                    const desc = c.getAttribute('Descripcion') || '';
                    if (!conceptDescription && !desc.toLowerCase().includes('redondeo')) {
                        conceptDescription = desc;
                        if (!rpu) {
                            const rpuMatch = conceptDescription.match(/RPU[:\s]+(\d+)/i);
                            if (rpuMatch) rpu = rpuMatch[1];
                        }
                    }
                });

                // Final Description Logic: Prefer "Town, Address" -> fallback to Concept Desc
                let finalLocDescription = [town, address].filter(Boolean).join(', ');
                if (!finalLocDescription) finalLocDescription = conceptDescription;

                // --- 3. Rounding Logic ---
                let redondeo = 0;
                if (cfeNode && cfeNode.hasAttribute('AJUSTE_POR_REDONDEO')) {
                    redondeo = parseFloat(cfeNode.getAttribute('AJUSTE_POR_REDONDEO'));
                } else {
                    const allElements = xmlDoc.getElementsByTagName('*');
                    for (let i = 0; i < allElements.length; i++) {
                        if (allElements[i].hasAttribute('AJUSTE_POR_REDONDEO')) {
                            redondeo = parseFloat(allElements[i].getAttribute('AJUSTE_POR_REDONDEO') || 0);
                            break;
                        }
                    }
                }
                if (redondeo === 0) {
                    conceptos.forEach(c => {
                        const desc = c.getAttribute('Descripcion') || '';
                        if (desc.toLowerCase().includes('redondeo')) {
                            redondeo += parseFloat(c.getAttribute('Importe') || 0);
                        }
                    });
                }

                // --- 4. Dates ---
                let dueDate = '';
                const feclimite = xmlDoc.getElementsByTagName('FECLIMITE')[0];
                if (feclimite) {
                    const rawDate = feclimite.textContent.trim();
                    const parseSpanishDate = (str) => {
                        const months = { 'ENE': '01', 'FEB': '02', 'MAR': '03', 'ABR': '04', 'MAY': '05', 'JUN': '06', 'JUL': '07', 'AGO': '08', 'SEP': '09', 'OCT': '10', 'NOV': '11', 'DIC': '12' };
                        const parts = str.split(' ');
                        if (parts.length === 3) {
                            const day = parts[0].padStart(2, '0');
                            const month = months[parts[1].toUpperCase()] || '01';
                            const year = '20' + parts[2];
                            return `${year}-${month}-${day}`;
                        }
                        return '';
                    };
                    dueDate = parseSpanishDate(rawDate);
                }
                if (!dueDate) {
                    const reciboNodes = xmlDoc.getElementsByTagName('*');
                    for (let i = 0; i < reciboNodes.length; i++) {
                        if ((reciboNodes[i].localName === 'Recibo' || reciboNodes[i].nodeName.endsWith(':Recibo')) && reciboNodes[i].hasAttribute('FechaVencimiento')) {
                            dueDate = reciboNodes[i].getAttribute('FechaVencimiento');
                            break;
                        }
                    }
                }

                // --- 5. Calculation ---
                let realIva = 0;
                const impuestos = xmlDoc.getElementsByTagName('cfdi:Impuestos')[0] || xmlDoc.getElementsByTagName('Impuestos')[0];
                if (impuestos) {
                    if (impuestos.hasAttribute('TotalImpuestosTrasladados')) {
                        realIva = parseFloat(impuestos.getAttribute('TotalImpuestosTrasladados'));
                    } else {
                        const traslados = impuestos.getElementsByTagName('cfdi:Traslados')[0] || impuestos.getElementsByTagName('Traslados')[0];
                        if (traslados) {
                            Array.from(traslados.getElementsByTagName('*')).forEach(t => {
                                if (t.getAttribute('Impuesto') === '002') {
                                    realIva += parseFloat(t.getAttribute('Importe') || 0);
                                }
                            });
                        }
                    }
                }
                if (realIva === 0) realIva = total - subtotal;

                const adjustedTotal = total - redondeo;
                const adjustedSubtotal = adjustedTotal - realIva;

                newReceipts.push({
                    uuid: uuid,
                    rpu: rpu,
                    description: finalLocDescription,
                    subtotal: parseFloat(adjustedSubtotal.toFixed(2)),
                    iva: parseFloat(realIva.toFixed(2)),
                    rounding: parseFloat(redondeo.toFixed(2)),
                    total: parseFloat(adjustedTotal.toFixed(2)),
                    _dueDate: dueDate
                });

            } catch (error) {
                console.error("Error parsing XML", file.name, error);
            }
        }

        // --- Aggregation & Form Update ---
        if (newReceipts.length > 0) {
            const allReceipts = [...data.cfe_receipts, ...newReceipts];

            // 1. Calculate Description (Due Dates)
            const dues = allReceipts.map(r => r._dueDate).filter(Boolean);
            let maxDue = null;
            if (dues.length) {
                dues.sort();
                maxDue = dues[dues.length - 1];
            }
            const uniqueDues = [...new Set(dues)].sort();

            // Group by month and year
            const groupedByMonth = {};
            const years = new Set();

            uniqueDues.forEach(d => {
                const dateObj = new Date(d + 'T12:00:00');
                const month = dateObj.toLocaleDateString('es-ES', { month: 'long' }).toUpperCase();
                const year = dateObj.getFullYear();
                const monthYear = `${month} DE ${year}`;
                const day = dateObj.getDate();

                years.add(year);

                if (!groupedByMonth[monthYear]) groupedByMonth[monthYear] = [];
                groupedByMonth[monthYear].push(day);
            });

            // Check if all dates are in the same year
            const sameYear = years.size === 1;
            const commonYear = sameYear ? Array.from(years)[0] : null;

            let dueNarrativeParts = [];
            for (const [monthYear, days] of Object.entries(groupedByMonth)) {
                const sortedDays = days.sort((a, b) => a - b);
                const dayStr = sortedDays.length === 1 ? sortedDays[0] : sortedDays.join(', ').replace(/, ([^,]*)$/, ' Y $1');

                if (sameYear) {
                    // Remove year from month name since we'll add it once at the end
                    const monthOnly = monthYear.replace(` DE ${commonYear}`, '');
                    dueNarrativeParts.push(`${dayStr} DE ${monthOnly}`);
                } else {
                    dueNarrativeParts.push(`${dayStr} DE ${monthYear}`);
                }
            }

            // If same year, append year once at the end
            let finalNarrative = dueNarrativeParts.join('; ');
            if (sameYear && commonYear) {
                finalNarrative += ` DE ${commonYear}`;
            }

            const finalDesc = dueNarrativeParts.length > 0 ? `VENCIMIENTO ${finalNarrative}` : data.description;

            // 2. Create Single Summary Item (Partida 31101)
            const sumSubtotal = allReceipts.reduce((acc, r) => acc + r.subtotal, 0);

            // Find Partida 31101
            const partida31101 = partidas.find(p => p.codigo.startsWith('31101'));
            const partidaId = partida31101 ? partida31101.id : '';
            const capituloId = partida31101 ? partida31101.capitulo_id : '';

            const summaryItem = {
                capitulo_id: capituloId,
                partida_id: partidaId,
                description: 'Pago de Energía Eléctrica',
                amount: parseFloat(sumSubtotal.toFixed(2))
            };

            const updatedData = {
                ...data,
                description: finalDesc,
                cfe_receipts: allReceipts,
                items: [summaryItem] // Replace items with single summary
            };
            if (maxDue) updatedData.due_date = maxDue;

            setData(updatedData);
        }

        if (fileInputRef.current) fileInputRef.current.value = '';
    };

    const removeReceipt = (index) => {
        const newReceipts = [...data.cfe_receipts];
        newReceipts.splice(index, 1);

        // Setup updated data (re-run aggregation logic ideally, but simple here)
        const sumSubtotal = newReceipts.reduce((acc, r) => acc + r.subtotal, 0);

        // Find existing item to update amount
        const newItems = [...data.items];
        if (newItems.length > 0) {
            newItems[0].amount = parseFloat(sumSubtotal.toFixed(2));
        }

        setData((prev) => ({
            ...prev,
            cfe_receipts: newReceipts,
            items: newItems
        }));
    };

    const updateReceipt = (index, value) => {
        const newReceipts = [...data.cfe_receipts];
        const subtotal = parseFloat(value) || 0;
        const iva = newReceipts[index].iva;
        const total = parseFloat((subtotal + iva).toFixed(2));

        newReceipts[index].subtotal = subtotal;
        newReceipts[index].total = total;

        // Recalculate Summary Item based on new Subtotals
        const sumSubtotal = newReceipts.reduce((acc, r) => acc + r.subtotal, 0);
        const newItems = [...data.items];
        if (newItems.length > 0) {
            newItems[0].amount = parseFloat(sumSubtotal.toFixed(2));
        }

        setData(prev => ({
            ...prev,
            cfe_receipts: newReceipts,
            items: newItems
        }));
    };

    return (
        <form onSubmit={submit} className="space-y-6">
            <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <InputLabel value="Año" />
                    <TextInput value={data.year} className="mt-1 block w-full bg-gray-100" readOnly />
                </div>
                <div>
                    <InputLabel value="Número" />
                    <TextInput value={data.requirement_number} onChange={e => setData('requirement_number', e.target.value)} className="mt-1 block w-full" type="number" />
                    <InputError message={errors.requirement_number} className="mt-2" />
                </div>
                <div>
                    <InputLabel value="Tipo" />
                    <select
                        value={data.type}
                        onChange={e => setData('type', e.target.value)}
                        className="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full"
                    >
                        {Object.entries(types).map(([key, label]) => (
                            <option key={key} value={key}>{label}</option>
                        ))}
                    </select>
                </div>
                <div>
                    <InputLabel value="Fecha Asignación" />
                    <TextInput type="date" value={data.assignment_date} onChange={e => setData('assignment_date', e.target.value)} className="mt-1 block w-full" />
                </div>
            </div>

            {/* CFE Specific Section */}
            {data.type === 'cfe' && (
                <div className="bg-yellow-50 p-4 rounded-md space-y-4">
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <InputLabel value="Fecha Inicial" />
                            <TextInput type="date" value={data.start_date} onChange={e => setData('start_date', e.target.value)} className="mt-1 block w-full" />
                        </div>
                        <div>
                            <InputLabel value="Fecha Final" />
                            <TextInput type="date" value={data.end_date} onChange={e => setData('end_date', e.target.value)} className="mt-1 block w-full" />
                        </div>
                        <div>
                            <InputLabel value="Concepto General (Vencimiento)" />
                            {/* MOVED DESCRIPTION HERE replacing Due Date input */}
                            <textarea
                                value={data.description}
                                onChange={e => setData('description', e.target.value)}
                                className="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full"
                                rows="2"
                                placeholder="VENCIMIENTO..."
                            ></textarea>
                        </div>
                    </div>

                    {/* XML Upload Button */}
                    <div className="flex items-center space-x-4 border-t border-yellow-200 pt-4">
                        <input
                            type="file"
                            multiple
                            accept=".xml"
                            onChange={handleXmlUpload}
                            className="hidden"
                            ref={fileInputRef}
                        />
                        <SecondaryButton type="button" onClick={() => fileInputRef.current.click()}>
                            📥 Cargar Recibos XML (CFE)
                        </SecondaryButton>
                        <span className="text-sm text-gray-600">
                            Se agruparán automáticamente en la partida 31101 y se listarán abajo.
                        </span>
                    </div>
                </div>
            )}

            {/* Viaticos Specific Section */}
            {data.type === 'viaticos' && (
                <ViaticosForm
                    data={data}
                    setData={setData}
                    employees={employees}
                    partidas={partidas}
                    vehicles={vehicles}
                    travelAllowanceRates={travelAllowanceRates}
                />
            )}

            {/* General Description Field - HIDE for CFE and Viaticos (since they have their own) */}
            {data.type !== 'cfe' && data.type !== 'viaticos' && (
                <div>
                    <InputLabel value="Concepto General (Descripción)" />
                    <textarea
                        value={data.description}
                        onChange={e => setData('description', e.target.value)}
                        className="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full"
                        rows="3"
                    ></textarea>
                </div>
            )}

            {/* Conditional Rendering: If CFE, we mainly show Summary Item + Receipts Table */}

            {/* Standard Items List - HIDE for CFE and Viaticos (Viaticos has its own table) */}
            {data.type !== 'cfe' && data.type !== 'viaticos' && (
                <div className="border p-4 rounded-md">
                    <div className="flex justify-between items-center mb-4">
                        <h3 className="text-lg font-medium">Partidas / Conceptos</h3>
                        <SecondaryButton onClick={addItem} type="button">Agregar Partida</SecondaryButton>
                    </div>

                    {data.items.map((item, index) => (
                        <div key={index} className="grid grid-cols-1 md:grid-cols-12 gap-2 mb-4 items-start border-b pb-4">
                            <div className="col-span-1 md:col-span-3">
                                <InputLabel value="Capítulo" className="text-xs" />
                                <select
                                    value={item.capitulo_id}
                                    onChange={e => updateItem(index, 'capitulo_id', e.target.value)}
                                    className="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full text-sm"
                                >
                                    <option value="">Seleccione...</option>
                                    {capitulos.map(c => <option key={c.id} value={c.id}>{c.codigo} - {c.nombre}</option>)}
                                </select>
                            </div>
                            <div className="col-span-1 md:col-span-3">
                                <InputLabel value="Partida" className="text-xs" />
                                <select
                                    value={item.partida_id}
                                    onChange={e => updateItem(index, 'partida_id', e.target.value)}
                                    className="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full text-sm"
                                    disabled={!item.capitulo_id}
                                >
                                    <option value="">Seleccione...</option>
                                    {partidas.filter(p => !item.capitulo_id || p.capitulo_id == item.capitulo_id).map(p => (
                                        <option key={p.id} value={p.id}>{p.codigo} - {p.nombre}</option>
                                    ))}
                                </select>
                            </div>
                            <div className="col-span-1 md:col-span-3 space-y-1">
                                <InputLabel value="Descripción" className="text-xs" />
                                <TextInput
                                    value={item.description}
                                    onChange={e => updateItem(index, 'description', e.target.value)}
                                    className="w-full text-sm"
                                />
                            </div>
                            <div className="col-span-1 md:col-span-2">
                                <InputLabel value="Importe" className="text-xs" />
                                <TextInput
                                    type="number" step="0.01" value={item.amount}
                                    onChange={e => updateItem(index, 'amount', e.target.value)}
                                    className="w-full text-sm"
                                />
                            </div>
                            <div className="col-span-1 md:col-span-1 flex items-center justify-center pt-6">
                                <button type="button" onClick={() => removeItem(index)} className="text-red-500 hover:text-red-700 font-bold">X</button>
                            </div>
                        </div>
                    ))}
                </div>
            )}

            {/* CFE Receipts Table */}
            {data.type === 'cfe' && data.cfe_receipts && data.cfe_receipts.length > 0 && (
                <div className="border p-4 rounded-md mt-6 bg-gray-50">
                    <h3 className="text-lg font-medium mb-4">Relación de Recibos CFE</h3>
                    <div className="overflow-x-auto">
                        <table className="min-w-full text-xs text-left">
                            <thead className="bg-gray-200 uppercase font-bold text-gray-700">
                                <tr>
                                    <th className="px-2 py-2">RPU</th>
                                    <th className="px-2 py-2">Dirección / Pob.</th>
                                    <th className="px-2 py-2">UUID</th>
                                    <th className="px-2 py-2 text-right">Subtotal</th>
                                    <th className="px-2 py-2 text-right">IVA</th>
                                    <th className="px-2 py-2 text-right">Total</th>
                                    <th className="px-2 py-2"></th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-200">
                                {data.cfe_receipts.map((r, idx) => {
                                    const isClosed = r.total % 1 === 0;
                                    return (
                                        <tr key={idx} className="hover:bg-gray-100">
                                            <td className="px-2 py-2 font-mono">{r.rpu}</td>
                                            <td className="px-2 py-2">{r.description}</td>
                                            <td className="px-2 py-2 font-mono text-[10px] break-all">{r.uuid}</td>
                                            <td className="px-2 py-2 text-right">
                                                <input
                                                    type="number" step="0.01"
                                                    value={r.subtotal}
                                                    onChange={(e) => updateReceipt(idx, e.target.value)}
                                                    className={`w-20 text-right text-xs p-1 border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500 ${isClosed ? 'bg-gray-100 ring-0 border-transparent text-gray-500' : 'bg-white'}`}
                                                    readOnly={isClosed}
                                                />
                                            </td>
                                            <td className="px-2 py-2 text-right">
                                                <input
                                                    type="number" step="0.01"
                                                    value={r.iva}
                                                    className="w-20 text-right text-xs p-1 border-transparent bg-transparent text-gray-700"
                                                    readOnly
                                                />
                                            </td>
                                            <td className="px-2 py-2 text-right">
                                                <input
                                                    type="number" step="0.01"
                                                    value={r.total}
                                                    className="w-20 text-right text-xs p-1 border-transparent bg-transparent font-bold text-gray-900"
                                                    readOnly
                                                />
                                            </td>
                                            <td className="px-2 py-2 text-center">
                                                <button type="button" onClick={() => removeReceipt(idx)} className="text-red-500 font-bold">X</button>
                                            </td>
                                        </tr>
                                    );
                                })}
                            </tbody>
                            <tfoot className="bg-gray-200 font-bold">
                                <tr>
                                    <td colSpan="3" className="px-2 py-2 text-right">TOTALES:</td>
                                    <td className="px-2 py-2 text-right">${totals.subtotal.toLocaleString('es-MX', { minimumFractionDigits: 2 })}</td>
                                    <td className="px-2 py-2 text-right">${totals.iva.toLocaleString('es-MX', { minimumFractionDigits: 2 })}</td>
                                    <td className="px-2 py-2 text-right">${totals.total.toLocaleString('es-MX', { minimumFractionDigits: 2 })}</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            )}

            <div className="flex justify-end mt-4">
                <div className="w-64 space-y-2">
                    <div className="flex justify-between font-bold text-lg border-t pt-2">
                        <span>Gran Total:</span>
                        <span>${totals.total.toLocaleString('es-MX', { minimumFractionDigits: 2 })}</span>
                    </div>
                </div>
            </div>

            {/* Signatures */}
            <div className="grid grid-cols-1 md:grid-cols-4 gap-4 mt-6">
                <div>
                    <InputLabel value="Coordinador" />
                    <select
                        value={data.coordinator_id}
                        onChange={e => setData('coordinator_id', e.target.value)}
                        className="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full"
                    >
                        <option value="">Seleccione...</option>
                        {employees.map(e => <option key={e.id} value={e.id}>{e.nombre} - {e.puesto}</option>)}
                    </select>
                </div>
                {data.type !== 'cfe' && data.type !== 'viaticos' && (
                    <div>
                        <InputLabel value="Director Rec. Mat." />
                        <select
                            value={data.director_id}
                            onChange={e => setData('director_id', e.target.value)}
                            className="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full"
                        >
                            <option value="">Seleccione...</option>
                            {employees.map(e => <option key={e.id} value={e.id}>{e.nombre} - {e.puesto}</option>)}
                        </select>
                    </div>
                )}
                <div>
                    <InputLabel value="Gerente" />
                    <select
                        value={data.manager_id}
                        onChange={e => setData('manager_id', e.target.value)}
                        className="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full"
                    >
                        <option value="">Seleccione...</option>
                        {employees.map(e => <option key={e.id} value={e.id}>{e.nombre} - {e.puesto}</option>)}
                    </select>
                </div>
                <div>
                    <InputLabel value="Elaboró" />
                    <select
                        value={data.elaborator_id}
                        onChange={e => setData('elaborator_id', e.target.value)}
                        className="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full"
                    >
                        <option value="">Seleccione...</option>
                        {employees.map(e => <option key={e.id} value={e.id}>{e.nombre} - {e.puesto}</option>)}
                    </select>
                </div>
            </div>

            <div className="flex items-center justify-end mt-4">
                <PrimaryButton className="ms-4" disabled={processing}>
                    {mode === 'create' ? 'Crear Requerimiento' : 'Actualizar Requerimiento'}
                </PrimaryButton>
            </div>
        </form>
    );
}
