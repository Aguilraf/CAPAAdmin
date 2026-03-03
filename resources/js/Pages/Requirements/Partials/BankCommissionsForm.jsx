import { useRef, useState } from 'react';
import axios from 'axios';
import InputLabel from '@/Components/InputLabel';
import TextInput from '@/Components/TextInput';
import SecondaryButton from '@/Components/SecondaryButton';
import PrimaryButton from '@/Components/PrimaryButton';
import InputError from '@/Components/InputError';

export default function BankCommissionsForm({ data, setData, monthsList }) {
    const fileInputRef = useRef(null);
    const [importing, setImporting] = useState(false);
    const [message, setMessage] = useState(null);

    const handleImport = async (e) => {
        const file = e.target.files[0];
        if (!file) return;

        setImporting(true);
        setMessage(null);

        const formData = new FormData();
        formData.append('file', file);

        try {
            const response = await axios.post(route('requirements.import-bank-commissions'), formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            });

            const { items, account_number } = response.data;

            // Map items to the format expected by the main form
            const formattedItems = items.map(item => ({
                capitulo_id: 3, // Usually capitulo 3000
                partida_id: item.partida_id || 141, // 34101
                description: item.description,
                amount: item.amount,
                invoice_date: item.invoice_date,
                invoice_folio: item.invoice_folio,
                temp_id: item.temp_id
            }));

            setData(prev => ({
                ...prev,
                items: formattedItems,
                revolving_fund_number: account_number || prev.revolving_fund_number,
                description: prev.description || 'COMISIONES BANCARIAS DEL MES DE ' + (data.month_billed || '').toUpperCase()
            }));

            setMessage({ type: 'success', text: `Se importaron ${items.length} registros exitosamente.` });
        } catch (error) {
            console.error(error);
            setMessage({ type: 'error', text: error.response?.data?.message || 'Error al procesar el archivo Excel.' });
        } finally {
            setImporting(false);
            if (fileInputRef.current) fileInputRef.current.value = '';
        }
    };

    return (
        <div className="bg-blue-50 p-4 rounded-md border border-blue-200 space-y-4">
            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <InputLabel value="Número de Cuenta Bancaria" />
                    <TextInput
                        value={data.revolving_fund_number}
                        onChange={e => setData('revolving_fund_number', e.target.value)}
                        className="mt-1 block w-full"
                        placeholder="Ej: 4021219811"
                    />
                </div>
                <div>
                    <InputLabel value="Mes Facturado" />
                    <div className="grid grid-cols-2 gap-2">
                        <select
                            value={data.month_billed}
                            onChange={e => setData('month_billed', e.target.value)}
                            className="border-gray-300 rounded-md text-sm mt-1 block w-full"
                        >
                            <option value="">Mes...</option>
                            {monthsList.map(m => <option key={m} value={m}>{m}</option>)}
                        </select>
                        <TextInput
                            type="number"
                            value={data.year_billed}
                            onChange={e => setData('year_billed', e.target.value)}
                            className="mt-1 text-sm block w-full"
                            placeholder="Año"
                        />
                    </div>
                </div>
                <div className="flex items-end h-full">
                    <input
                        type="file"
                        accept=".xlsx,.xls,.csv"
                        onChange={handleImport}
                        className="hidden"
                        ref={fileInputRef}
                    />
                    <SecondaryButton
                        type="button"
                        onClick={() => fileInputRef.current.click()}
                        disabled={importing}
                        className="w-full justify-center py-2"
                    >
                        {importing ? '⌛ Procesando...' : '📥 Cargar Excel Comisiones'}
                    </SecondaryButton>
                </div>
            </div>

            {message && (
                <div className={`p-3 rounded text-sm ${message.type === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}`}>
                    {message.text}
                </div>
            )}

            <div className="text-xs text-blue-700 italic">
                * El sistema importará solo las filas de "I.V.A." y las que inicien con "00CTRANSFENVSPEI". Se ignorarán cargos como "CGO SPEI".
            </div>

            <div className="mt-4 overflow-x-auto">
                <table className="min-width-full divide-y divide-gray-200 text-xs">
                    <thead>
                        <tr className="bg-blue-100">
                            <th className="px-2 py-1 text-left">Fecha</th>
                            <th className="px-2 py-1 text-left">Concepto</th>
                            <th className="px-2 py-1 text-left">Referencia</th>
                            <th className="px-2 py-1 text-right">Importe</th>
                        </tr>
                    </thead>
                    <tbody className="bg-white divide-y divide-gray-100">
                        {data.items.length > 0 ? data.items.map((item, idx) => (
                            <tr key={item.temp_id || idx}>
                                <td className="px-2 py-1">{item.invoice_date}</td>
                                <td className="px-2 py-1 truncate max-w-xs">{item.description}</td>
                                <td className="px-2 py-1">{item.invoice_folio}</td>
                                <td className="px-2 py-1 text-right font-mono">${Number(item.amount).toFixed(2)}</td>
                            </tr>
                        )) : (
                            <tr>
                                <td colSpan="4" className="px-2 py-4 text-center text-gray-400">Sin datos importados</td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>
        </div>
    );
}
