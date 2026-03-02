import { useState, useRef } from 'react';
import { router } from '@inertiajs/react';
import TextInput from '@/Components/TextInput';
import InputLabel from '@/Components/InputLabel';
import InputError from '@/Components/InputError';
import SecondaryButton from '@/Components/SecondaryButton';

export default function RevolventeForm({ data, setData, partidas, capitulos, errors }) {
    const fileInputRef = useRef(null);
    const [uploadResults, setUploadResults] = useState([]);
    const [refreshing, setRefreshing] = useState(false);

    const refreshCatalogos = () => {
        setRefreshing(true);
        router.reload({
            only: ['partidas', 'capitulos'],
            onFinish: () => setRefreshing(false),
        });
    };

    // Manual adjustment of totals (max ±$0.02 per column)
    // Helper: sum a field from items using integer arithmetic (avoid float drift)
    const calcItemSum = (field) => Math.round(
        (data.items || []).reduce((acc, item) => acc + Math.round(Number(item[field] || 0) * 100), 0)
    ) / 100;

    // On edit mode, seed states only for fields whose saved value differs ≤$0.02 from items sum
    // (larger differences = legacy/unset column → use item-calculated value instead)
    const [totalsInput, setTotalsInput] = useState(() => {
        const adj = data.totals_adjust;
        if (!adj) return {};
        const fieldMap = [
            { inputKey: 'invoice_subtotal', itemField: 'invoice_subtotal' },
            { inputKey: 'invoice_iva', itemField: 'invoice_iva' },
            { inputKey: 'amount', itemField: 'amount' },
        ];
        const result = {};
        fieldMap.forEach(({ inputKey, itemField }) => {
            const saved = adj[inputKey];
            if (saved == null) return;
            const diff = Math.abs(Math.round((Number(saved) - calcItemSum(itemField)) * 100) / 100);
            if (diff <= 0.02) result[inputKey] = Number(saved).toFixed(2);
        });
        return result;
    });

    const [totalsEdit, setTotalsEdit] = useState(() => {
        const adj = data.totals_adjust;
        if (!adj) return {};
        const fieldMap = [
            { editKey: 'invoice_subtotal', itemField: 'invoice_subtotal' },
            { editKey: 'invoice_discount', itemField: 'invoice_discount' },
            { editKey: 'invoice_iva', itemField: 'invoice_iva' },
            { editKey: 'invoice_ieps', itemField: 'invoice_ieps' },
            { editKey: 'invoice_retention_isr', itemField: 'invoice_retention_isr' },
            { editKey: 'invoice_retention_iva', itemField: 'invoice_retention_iva' },
            { editKey: 'amount', itemField: 'amount' },
        ];
        const result = {};
        fieldMap.forEach(({ editKey, itemField }) => {
            const saved = adj[editKey];
            if (saved == null) return;
            const diff = Math.abs(Math.round((Number(saved) - calcItemSum(itemField)) * 100) / 100);
            if (diff <= 0.02) result[editKey] = Number(saved);
        });
        return result;
    });
    const [totalsError, setTotalsError] = useState({});

    const handleSumEdit = (field, inputVal, calculatedVal, allSums) => {
        const parsed = parseFloat(inputVal);
        if (isNaN(parsed)) {
            // reset to last valid
            setTotalsInput(prev => ({ ...prev, [field]: (totalsEdit[field] ?? calculatedVal).toFixed(2) }));
            return;
        }
        const diff = Math.abs(Math.round((parsed - calculatedVal) * 100) / 100);
        if (diff <= 0.02) {
            const newEdit = { ...totalsEdit, [field]: parsed };
            setTotalsEdit(newEdit);
            setTotalsInput(prev => ({ ...prev, [field]: parsed.toFixed(2) }));
            setTotalsError(prev => ({ ...prev, [field]: false }));

            // Auto-recalculate total from adjusted components
            const adj = (f) => newEdit[f] ?? allSums[f];
            const newTotal = Math.round((
                adj('invoice_subtotal') - adj('invoice_discount') +
                adj('invoice_iva') + adj('invoice_ieps') -
                adj('invoice_retention_isr') - adj('invoice_retention_iva')
            ) * 100) / 100;
            setTotalsEdit(prev => ({ ...prev, amount: newTotal }));
            setTotalsInput(prev => ({ ...prev, amount: newTotal.toFixed(2) }));

            // Persist in form data for submission
            setData('totals_adjust', { ...newEdit, amount: newTotal });
        } else {
            setTotalsError(prev => ({ ...prev, [field]: true }));
            // Revert input to last valid value
            setTotalsInput(prev => ({ ...prev, [field]: (totalsEdit[field] ?? calculatedVal).toFixed(2) }));
        }
    };

    const getAdjusted = (field, calculatedVal) =>
        totalsEdit[field] !== undefined ? totalsEdit[field] : calculatedVal;

    const handleXmlUpload = async (e) => {
        const files = Array.from(e.target.files);
        if (files.length === 0) return;

        const newItems = [...data.items.filter(item => item.partida_id || item.description || item.amount > 0)];
        const parsingResults = [];

        for (const file of files) {
            try {
                const text = await file.text();
                const parser = new DOMParser();
                const xmlDoc = parser.parseFromString(text, "text/xml");

                // Check for parsing errors
                const parseError = xmlDoc.getElementsByTagName("parsererror");
                if (parseError.length > 0) {
                    throw new Error("Error de formato XML");
                }

                // --- Namespaces ---
                const ns = {
                    cfdi: "http://www.sat.gob.mx/cfd/4",
                    tfd: "http://www.sat.gob.mx/TimbreFiscalDigital"
                };

                // --- Validation ---
                const receptor = xmlDoc.getElementsByTagName("cfdi:Receptor")[0] || xmlDoc.getElementsByTagName("Receptor")[0];
                const rfcReceptor = receptor ? receptor.getAttribute("Rfc") : "";
                const regimenReceptor = receptor ? receptor.getAttribute("RegimenFiscalReceptor") : "";

                const isValid = rfcReceptor === "CAP811007MT7" && regimenReceptor === "603";

                // --- General Info ---
                const comprobante = xmlDoc.getElementsByTagName("cfdi:Comprobante")[0] || xmlDoc.getElementsByTagName("Comprobante")[0];
                const emisor = xmlDoc.getElementsByTagName("cfdi:Emisor")[0] || xmlDoc.getElementsByTagName("Emisor")[0];
                const providerName = emisor ? emisor.getAttribute("Nombre") : "";
                const providerRfc = emisor ? emisor.getAttribute("Rfc") : "";
                const invoiceDate = comprobante ? comprobante.getAttribute("Fecha") : "";
                const totalInvoice = comprobante ? parseFloat(comprobante.getAttribute("Total") || 0) : 0;

                const timbre = xmlDoc.getElementsByTagName("tfd:TimbreFiscalDigital")[0] || xmlDoc.getElementsByTagName("TimbreFiscalDigital")[0];
                const uuid = timbre ? timbre.getAttribute("UUID") : "";

                // Folio: use Serie-Folio from comprobante, fallback to first 2 UUID groups
                const serie = comprobante ? (comprobante.getAttribute("Serie") || "") : "";
                const folio = comprobante ? (comprobante.getAttribute("Folio") || "") : "";
                let folioShort = "";
                if (serie && folio) {
                    folioShort = `${serie}-${folio}`;
                } else if (folio) {
                    folioShort = folio;
                } else if (uuid) {
                    folioShort = uuid.split('-').slice(0, 2).join('-');
                }

                // --- Concepts ---
                const conceptNodes = Array.from(xmlDoc.getElementsByTagName("cfdi:Concepto") || xmlDoc.getElementsByTagName("Concepto"));

                conceptNodes.forEach((node) => {
                    const conceptDesc = node.getAttribute("Descripcion");
                    const subtotal = parseFloat(node.getAttribute("Importe") || 0);
                    const discount = parseFloat(node.getAttribute("Descuento") || 0);

                    // Taxes for this concept
                    let iva = 0;
                    let ieps = 0;
                    let retentionIsr = 0;
                    let retentionIva = 0;

                    const traslados = node.getElementsByTagName("cfdi:Traslado") || node.getElementsByTagName("Traslado");
                    Array.from(traslados).forEach(t => {
                        const impuesto = t.getAttribute("Impuesto");
                        const importe = parseFloat(t.getAttribute("Importe") || 0);
                        if (impuesto === "002") iva += importe;
                        if (impuesto === "003") ieps += importe;
                    });

                    const retenciones = node.getElementsByTagName("cfdi:Retencion") || node.getElementsByTagName("Retencion");
                    Array.from(retenciones).forEach(r => {
                        const impuesto = r.getAttribute("Impuesto");
                        const importe = parseFloat(r.getAttribute("Importe") || 0);
                        if (impuesto === "001") retentionIsr += importe;
                        if (impuesto === "002") retentionIva += importe;
                    });

                    const totalConcept = subtotal - discount + iva + ieps - retentionIsr - retentionIva;

                    newItems.push({
                        capitulo_id: '',
                        partida_id: '',
                        description: conceptDesc,
                        amount: totalConcept,
                        uuid: uuid,
                        invoice_folio: folioShort,
                        invoice_date: invoiceDate ? invoiceDate.split('T')[0] : '',
                        provider_rfc: providerRfc,
                        provider_name: providerName,
                        invoice_subtotal: subtotal,
                        invoice_iva: iva,
                        invoice_discount: discount,
                        invoice_ieps: ieps,
                        invoice_retention_isr: retentionIsr,
                        invoice_retention_iva: retentionIva,
                        invoice_total: totalConcept,
                        _isValid: isValid,
                        _fileName: file.name
                    });
                });

                parsingResults.push({
                    fileName: file.name,
                    isValid: isValid,
                    rfc: rfcReceptor,
                    regimen: regimenReceptor
                });

            } catch (error) {
                console.error("Error parsing XML:", file.name, error);
                parsingResults.push({
                    fileName: file.name,
                    isValid: false,
                    error: error.message
                });
            }
        }

        // Remove empty first item if it exists
        if (newItems.length > 1 && !newItems[0].partida_id && !newItems[0].description && newItems[0].amount === 0) {
            newItems.shift();
        }

        setData('items', newItems);
        setUploadResults(parsingResults);
        if (fileInputRef.current) fileInputRef.current.value = '';
    };

    const updateItem = (index, field, value) => {
        const newItems = [...data.items];
        newItems[index][field] = value;
        if (field === 'capitulo_id') {
            newItems[index]['partida_id'] = '';
        }
        setData('items', newItems);
    };

    const removeItem = (index) => {
        const newItems = [...data.items];
        newItems.splice(index, 1);
        setData('items', newItems);
    };

    return (
        <div className="space-y-6">
            <div className="grid grid-cols-1 md:grid-cols-4 gap-4 bg-blue-50 p-4 rounded-md border border-blue-200">
                <div>
                    <InputLabel value="Fecha Inicial (Periodo)" />
                    <TextInput
                        type="date"
                        value={data.start_date || ''}
                        onChange={e => setData('start_date', e.target.value)}
                        className="mt-1 block w-full"
                    />
                </div>
                <div>
                    <InputLabel value="Fecha Final (Periodo)" />
                    <TextInput
                        type="date"
                        value={data.end_date || ''}
                        onChange={e => setData('end_date', e.target.value)}
                        className="mt-1 block w-full"
                    />
                </div>
                <div>
                    <InputLabel value="Núm. Revolvente" />
                    <TextInput
                        value={data.revolving_fund_number || ''}
                        onChange={e => setData('revolving_fund_number', e.target.value)}
                        className="mt-1 block w-full"
                    />
                    <InputError message={errors.revolving_fund_number} className="mt-2" />
                </div>
                <div>
                    <InputLabel value="Tipo de Fondo" />
                    <select
                        value={data.revolving_fund_type || ''}
                        onChange={e => setData('revolving_fund_type', e.target.value)}
                        className="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full"
                    >
                        <option value="">Seleccione...</option>
                        <option value="Reposición">Reposición</option>
                        <option value="Cancelación">Cancelación</option>
                    </select>
                    <InputError message={errors.revolving_fund_type} className="mt-2" />
                </div>
                <div className="md:col-span-4 flex justify-end items-end">
                    <div className="w-full md:w-1/4">
                        <input
                            type="file"
                            multiple
                            accept=".xml"
                            onChange={handleXmlUpload}
                            className="hidden"
                            ref={fileInputRef}
                        />
                        <SecondaryButton type="button" onClick={() => fileInputRef.current.click()} className="w-full justify-center text-xs py-2">
                            📥 Cargar Facturas XML
                        </SecondaryButton>
                    </div>
                </div>
            </div>

            {uploadResults.length > 0 && (
                <div className="p-3 bg-gray-50 rounded-md border text-sm">
                    <h4 className="font-bold mb-2">Resultados de Validación:</h4>
                    <ul className="space-y-1">
                        {uploadResults.map((res, i) => (
                            <li key={i} className={`flex items-center space-x-2 ${res.isValid ? 'text-green-700' : 'text-red-700'}`}>
                                <span>{res.isValid ? '✅' : '❌'}</span>
                                <span className="font-mono">{res.fileName}</span>
                                {!res.isValid && (
                                    <span className="italic">
                                        ({res.error || `RFC: ${res.rfc}, Regimen: ${res.regimen}`})
                                    </span>
                                )}
                            </li>
                        ))}
                    </ul>
                </div>
            )}

            <div className="border p-4 rounded-md">
                <div className="flex justify-between items-center mb-4">
                    <h3 className="text-lg font-medium">Conceptos de Facturas</h3>
                    <button
                        type="button"
                        onClick={refreshCatalogos}
                        disabled={refreshing}
                        className="flex items-center gap-1 text-xs text-blue-600 hover:text-blue-800 disabled:opacity-50 border border-blue-300 rounded px-2 py-1 hover:bg-blue-50 transition"
                        title="Actualizar lista de capítulos y partidas"
                    >
                        <span className={refreshing ? 'animate-spin inline-block' : ''}>🔄</span>
                        {refreshing ? 'Actualizando...' : 'Actualizar catálogos'}
                    </button>
                </div>

                <div className="overflow-x-auto">
                    <table className="min-w-full divide-y divide-gray-200">
                        <thead className="bg-gray-50">
                            <tr>
                                <th className="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/4">Capítulo / Partida</th>
                                <th className="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/4">Concepto / XML Info</th>
                                <th className="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                                <th className="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider text-orange-600">Descuento</th>
                                <th className="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">IVA</th>
                                <th className="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider text-purple-600">IEPS</th>
                                <th className="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider text-red-600">Retención ISR</th>
                                <th className="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider text-red-600">Retención IVA</th>
                                <th className="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                <th className="px-3 py-2"></th>
                            </tr>
                        </thead>
                        <tbody className="bg-white divide-y divide-gray-200">
                            {(() => {
                                // Group items by invoice_folio (or uuid as fallback)
                                const groups = [];
                                const groupMap = {};
                                data.items.forEach((item, index) => {
                                    const key = item.invoice_folio || item.uuid || `_manual_${index}`;
                                    if (!groupMap[key]) {
                                        groupMap[key] = { key, items: [] };
                                        groups.push(groupMap[key]);
                                    }
                                    groupMap[key].items.push({ item, index });
                                });

                                const fmt = (v) => Number(v || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });

                                // Apply capítulo/partida to all items in a group
                                const applyToGroup = (groupItems, capituloId, partidaId) => {
                                    const newItems = [...data.items];
                                    groupItems.forEach(({ index }) => {
                                        if (capituloId !== undefined) {
                                            newItems[index] = { ...newItems[index], capitulo_id: capituloId, partida_id: '' };
                                        }
                                        if (partidaId !== undefined) {
                                            newItems[index] = { ...newItems[index], partida_id: partidaId };
                                        }
                                    });
                                    setData('items', newItems);
                                };

                                return groups.map((group) => {
                                    const firstItem = group.items[0].item;
                                    const isMulti = group.items.length > 1;

                                    // Shared capítulo/partida for the group header (use first item as reference)
                                    const groupCapituloId = firstItem.capitulo_id || '';
                                    const groupPartidaId = firstItem.partida_id || '';

                                    return (
                                        <>
                                            {/* Group header row — only shown when multiple concepts share same invoice */}
                                            {isMulti && (
                                                <tr key={`header-${group.key}`} className="bg-blue-50 border-t-2 border-blue-300">
                                                    <td className="px-3 py-2 space-y-1" colSpan={1}>
                                                        <div className="text-[10px] font-bold text-blue-700 mb-1 uppercase tracking-wide">
                                                            📄 Aplicar a toda la factura:
                                                        </div>
                                                        <select
                                                            value={groupCapituloId}
                                                            onChange={e => applyToGroup(group.items, e.target.value, undefined)}
                                                            className="block w-full text-xs border-blue-300 rounded-md bg-white"
                                                        >
                                                            <option value="">Capítulo...</option>
                                                            {capitulos.map(c => <option key={c.id} value={c.id}>{c.codigo} - {c.nombre}</option>)}
                                                        </select>
                                                        <select
                                                            value={groupPartidaId}
                                                            onChange={e => applyToGroup(group.items, undefined, e.target.value)}
                                                            className="block w-full text-xs border-blue-300 rounded-md bg-white"
                                                            disabled={!groupCapituloId}
                                                        >
                                                            <option value="">Partida...</option>
                                                            {partidas.filter(p => p.capitulo_id == groupCapituloId).map(p => (
                                                                <option key={p.id} value={p.id}>{p.codigo} - {p.nombre}</option>
                                                            ))}
                                                        </select>
                                                    </td>
                                                    <td className="px-3 py-2" colSpan={8}>
                                                        <div className="text-xs font-bold text-blue-800">{firstItem.provider_name}</div>
                                                        <div className="text-[10px] text-blue-600 flex gap-x-3 mt-0.5">
                                                            <span>Factura: <b>{firstItem.invoice_folio}</b></span>
                                                            <span>Fecha: {firstItem.invoice_date}</span>
                                                            <span className="text-blue-400">RFC: {firstItem.provider_rfc}</span>
                                                            <span className="font-semibold">{group.items.length} conceptos</span>
                                                        </div>
                                                    </td>
                                                    <td />
                                                </tr>
                                            )}

                                            {/* Individual concept rows */}
                                            {group.items.map(({ item, index }) => (
                                                <tr
                                                    key={index}
                                                    className={`${isMulti ? 'border-l-4 border-blue-200' : ''} ${item._isValid === false ? 'bg-red-50' : item._isValid === true ? 'bg-green-50' : ''}`}
                                                >
                                                    <td className="px-3 py-2 space-y-1">
                                                        <select
                                                            value={item.capitulo_id || ''}
                                                            onChange={e => updateItem(index, 'capitulo_id', e.target.value)}
                                                            className="block w-full text-xs border-gray-300 rounded-md"
                                                        >
                                                            <option value="">Capítulo...</option>
                                                            {capitulos.map(c => <option key={c.id} value={c.id}>{c.codigo} - {c.nombre}</option>)}
                                                        </select>
                                                        <select
                                                            value={item.partida_id || ''}
                                                            onChange={e => updateItem(index, 'partida_id', e.target.value)}
                                                            className="block w-full text-xs border-gray-300 rounded-md"
                                                            disabled={!item.capitulo_id}
                                                        >
                                                            <option value="">Partida...</option>
                                                            {partidas.filter(p => p.capitulo_id == item.capitulo_id).map(p => (
                                                                <option key={p.id} value={p.id}>{p.codigo} - {p.nombre}</option>
                                                            ))}
                                                        </select>
                                                    </td>
                                                    <td className="px-3 py-2">
                                                        <textarea
                                                            value={item.description || ''}
                                                            onChange={e => updateItem(index, 'description', e.target.value)}
                                                            className="block w-full text-xs border-gray-300 rounded-md"
                                                            rows="2"
                                                        />
                                                        {/* Show provider info only for single-concept invoices */}
                                                        {!isMulti && item.provider_name && (
                                                            <div className="mt-1 text-[10px] text-gray-500 flex flex-wrap gap-x-2">
                                                                <span className="font-bold">{item.provider_name}</span>
                                                                <span>Folio: {item.invoice_folio}</span>
                                                                <span>Fecha: {item.invoice_date}</span>
                                                            </div>
                                                        )}
                                                    </td>
                                                    <td className="px-3 py-2 text-right text-xs">$ {fmt(item.invoice_subtotal)}</td>
                                                    <td className="px-3 py-2 text-right text-xs text-orange-600">
                                                        {Number(item.invoice_discount || 0) > 0 ? `- $ ${fmt(item.invoice_discount)}` : '-'}
                                                    </td>
                                                    <td className="px-3 py-2 text-right text-xs">$ {fmt(item.invoice_iva)}</td>
                                                    <td className="px-3 py-2 text-right text-xs text-purple-600">
                                                        {Number(item.invoice_ieps || 0) > 0 ? `$ ${fmt(item.invoice_ieps)}` : '-'}
                                                    </td>
                                                    <td className="px-3 py-2 text-right text-xs text-red-600">
                                                        {Number(item.invoice_retention_isr || 0) > 0 ? `- $ ${fmt(item.invoice_retention_isr)}` : '-'}
                                                    </td>
                                                    <td className="px-3 py-2 text-right text-xs text-red-600">
                                                        {Number(item.invoice_retention_iva || 0) > 0 ? `- $ ${fmt(item.invoice_retention_iva)}` : '-'}
                                                    </td>
                                                    <td className="px-3 py-2 text-right text-xs font-bold">$ {fmt(item.amount)}</td>
                                                    <td className="px-3 py-2 text-center text-red-600">
                                                        <button type="button" onClick={() => removeItem(index)} className="hover:text-red-800">✕</button>
                                                    </td>
                                                </tr>
                                            ))}

                                            {/* Subtotal row per invoice group */}
                                            {isMulti && (() => {
                                                // Suma con aritmética entera para evitar errores de punto flotante
                                                const gsum = (field) => Math.round(
                                                    group.items.reduce((acc, { item }) => acc + Math.round(Number(item[field] || 0) * 100), 0)
                                                ) / 100;
                                                return (
                                                    <tr key={`subtotal-${group.key}`} className="bg-blue-100 border-t border-blue-300 border-b-2 border-b-blue-400">
                                                        <td colSpan={2} className="px-3 py-1.5 text-right text-[11px] font-bold text-blue-800 uppercase tracking-wide">
                                                            Subtotal factura
                                                        </td>
                                                        <td className="px-3 py-1.5 text-right text-[11px] font-bold text-blue-800">$ {fmt(gsum('invoice_subtotal'))}</td>
                                                        <td className="px-3 py-1.5 text-right text-[11px] font-bold text-orange-600">
                                                            {gsum('invoice_discount') > 0 ? `- $ ${fmt(gsum('invoice_discount'))}` : '-'}
                                                        </td>
                                                        <td className="px-3 py-1.5 text-right text-[11px] font-bold text-blue-800">$ {fmt(gsum('invoice_iva'))}</td>
                                                        <td className="px-3 py-1.5 text-right text-[11px] font-bold text-purple-700">
                                                            {gsum('invoice_ieps') > 0 ? `$ ${fmt(gsum('invoice_ieps'))}` : '-'}
                                                        </td>
                                                        <td className="px-3 py-1.5 text-right text-[11px] font-bold text-red-700">
                                                            {gsum('invoice_retention_isr') > 0 ? `- $ ${fmt(gsum('invoice_retention_isr'))}` : '-'}
                                                        </td>
                                                        <td className="px-3 py-1.5 text-right text-[11px] font-bold text-red-700">
                                                            {gsum('invoice_retention_iva') > 0 ? `- $ ${fmt(gsum('invoice_retention_iva'))}` : '-'}
                                                        </td>
                                                        <td className="px-3 py-1.5 text-right text-[11px] font-bold text-indigo-800">$ {fmt(gsum('amount'))}</td>
                                                        <td />
                                                    </tr>
                                                );
                                            })()}
                                        </>
                                    );
                                });
                            })()}
                        </tbody>
                        {data.items.length > 0 && (() => {
                            const fmt = (v) => Number(v || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                            // Suma con aritmética entera para evitar errores de punto flotante
                            const sum = (field) => Math.round(
                                data.items.reduce((acc, item) => acc + Math.round(Number(item[field] || 0) * 100), 0)
                            ) / 100;

                            // All sums as object (passed to handler for auto-total recalc)
                            const allSums = {
                                invoice_subtotal: sum('invoice_subtotal'),
                                invoice_discount: sum('invoice_discount'),
                                invoice_iva: sum('invoice_iva'),
                                invoice_ieps: sum('invoice_ieps'),
                                invoice_retention_isr: sum('invoice_retention_isr'),
                                invoice_retention_iva: sum('invoice_retention_iva'),
                                amount: sum('amount'),
                            };

                            // Auto-computed grand total using adjusted values
                            const adjTotal = Math.round((
                                getAdjusted('invoice_subtotal', allSums.invoice_subtotal)
                                - getAdjusted('invoice_discount', allSums.invoice_discount)
                                + getAdjusted('invoice_iva', allSums.invoice_iva)
                                + getAdjusted('invoice_ieps', allSums.invoice_ieps)
                                - getAdjusted('invoice_retention_isr', allSums.invoice_retention_isr)
                                - getAdjusted('invoice_retention_iva', allSums.invoice_retention_iva)
                            ) * 100) / 100;

                            const editCell = (field, extraClass = '') => {
                                const calc = allSums[field];
                                const inputVal = totalsInput[field] ?? calc.toFixed(2);
                                const isDiff = Math.abs(Math.round((getAdjusted(field, calc) - calc) * 100)) > 0;
                                const isErr = totalsError[field];
                                return (
                                    <td className={`px-1 py-1 text-right ${extraClass}`}>
                                        <div className="flex items-center justify-end gap-0.5">
                                            <span className="text-xs text-gray-400">$</span>
                                            <input
                                                type="number"
                                                step="0.01"
                                                value={inputVal}
                                                onChange={e => setTotalsInput(prev => ({ ...prev, [field]: e.target.value }))}
                                                onBlur={e => handleSumEdit(field, e.target.value, calc, allSums)}
                                                className={`w-24 text-right text-xs font-bold rounded px-1 py-0.5 border focus:outline-none focus:ring-1
                                                    ${isErr ? 'border-red-500 bg-red-50 focus:ring-red-400' :
                                                        isDiff ? 'border-yellow-400 bg-yellow-50 focus:ring-yellow-300' :
                                                            'border-transparent bg-transparent focus:ring-blue-300'}`}
                                                title={isErr ? 'El ajuste máximo es ±$0.02' : isDiff ? `Calculado: $${fmt(calc)}` : ''}
                                            />
                                        </div>
                                    </td>
                                );
                            };

                            return (
                                <tfoot className="bg-gray-100 border-t-2 border-gray-300">
                                    <tr>
                                        <td colSpan={2} className="px-3 py-2 text-right text-xs font-bold text-gray-700 uppercase">
                                            Totales
                                            <div className="text-[9px] font-normal text-gray-400 normal-case">Ajuste máx. ±$0.02</div>
                                        </td>
                                        {editCell('invoice_subtotal')}
                                        <td className="px-3 py-2 text-right text-xs font-bold text-orange-600">
                                            {allSums.invoice_discount > 0 ? `- $ ${fmt(getAdjusted('invoice_discount', allSums.invoice_discount))}` : '-'}
                                        </td>
                                        {editCell('invoice_iva')}
                                        <td className="px-3 py-2 text-right text-xs font-bold text-purple-600">
                                            {allSums.invoice_ieps > 0 ? `$ ${fmt(getAdjusted('invoice_ieps', allSums.invoice_ieps))}` : '-'}
                                        </td>
                                        <td className="px-3 py-2 text-right text-xs font-bold text-red-600">
                                            {allSums.invoice_retention_isr > 0 ? `- $ ${fmt(getAdjusted('invoice_retention_isr', allSums.invoice_retention_isr))}` : '-'}
                                        </td>
                                        <td className="px-3 py-2 text-right text-xs font-bold text-red-600">
                                            {allSums.invoice_retention_iva > 0 ? `- $ ${fmt(getAdjusted('invoice_retention_iva', allSums.invoice_retention_iva))}` : '-'}
                                        </td>
                                        <td className="px-1 py-1 text-right">
                                            <div className="flex items-center justify-end gap-0.5">
                                                <span className="text-xs text-gray-400">$</span>
                                                <span className={`w-24 text-right text-xs font-bold px-1 py-0.5 rounded ${Math.abs(Math.round((adjTotal - allSums.amount) * 100)) > 0
                                                    ? 'bg-yellow-50 text-yellow-700'
                                                    : 'text-indigo-700'
                                                    }`}>
                                                    {fmt(adjTotal)}
                                                </span>
                                            </div>
                                        </td>
                                        <td />
                                    </tr>
                                </tfoot>
                            );
                        })()}
                    </table>
                </div>

                {data.items.length === 0 && (
                    <p className="text-center py-4 text-gray-500 text-sm">No hay conceptos cargados. Suba un archivo XML para comenzar.</p>
                )}
            </div>
        </div>
    );
}
