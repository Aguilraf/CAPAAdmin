import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import PrimaryButton from '@/Components/PrimaryButton';
import { Head, Link, router, useForm } from '@inertiajs/react';

const money = new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' });
const date = (value) => value ? String(value).slice(0, 10).split('-').reverse().join('/') : '';

export default function Index({ rows = [], banks = [], filters = {}, draefTotal = 0, policyTotal = 0, draefPayments = [] }) {
    const { data, setData, get, processing, errors } = useForm({
        fecha_desde: filters.fecha_desde || '',
        fecha_hasta: filters.fecha_hasta || '',
    });

    const submit = (event) => {
        event.preventDefault();
        get(route('reportes.cobranza'), { preserveState: true, replace: true });
    };

    const totals = rows.reduce((result, row) => {
        Object.keys(row.banks || {}).forEach((bankId) => { result.banks[bankId] = (result.banks[bankId] || 0) + Number(row.banks[bankId] || 0); });
        result.totalBanks += Number(row.total_banks || 0);
        return result;
    }, { banks: {}, totalBanks: 0 });

    return (
        <AuthenticatedLayout header={<h2 className="text-xl font-semibold text-gray-800">Reporte de cobranza</h2>}>
            <Head title="Reporte de cobranza" />
            <div className="py-8 print:py-0"><div className="mx-auto max-w-[1600px] space-y-6 px-4 sm:px-6 lg:px-8">
                <div className="rounded-xl border border-slate-200 bg-white p-6 shadow-sm print:hidden">
                    <div className="flex flex-col gap-4 md:flex-row md:items-end md:justify-between"><div><h3 className="text-lg font-semibold text-slate-800">Generar reporte</h3><p className="mt-1 text-sm text-slate-500">Selecciona el rango para consultar cobranzas, pólizas y DRAEF.</p></div><Link href={route('reportes.index')} className="text-sm font-semibold text-slate-600 underline">Volver a reportes</Link></div>
                    <form onSubmit={submit} className="mt-5 grid gap-4 md:grid-cols-[1fr_1fr_auto] md:items-end"><div><label className="block text-sm font-medium text-slate-700">Fecha inicial</label><input type="date" value={data.fecha_desde} onChange={(e) => setData('fecha_desde', e.target.value)} className="mt-1 w-full rounded-md border-gray-300" required /></div><div><label className="block text-sm font-medium text-slate-700">Fecha final</label><input type="date" value={data.fecha_hasta} onChange={(e) => setData('fecha_hasta', e.target.value)} className="mt-1 w-full rounded-md border-gray-300" required /><div className="text-sm text-red-600">{errors.fecha_hasta}</div></div><PrimaryButton disabled={processing}>Consultar</PrimaryButton></form>
                </div>

                <div className="rounded-xl border border-slate-300 bg-white p-4 shadow-sm print:border-0 print:shadow-none">
                    <div className="mb-4 flex items-start justify-between gap-4 border-b-2 border-slate-800 pb-3"><div><h1 className="text-xl font-bold uppercase text-slate-800">Comisión de Agua Potable y Alcantarillado del Estado de Quintana Roo</h1><p className="text-sm font-semibold uppercase text-slate-600">Relación de ingresos por recaudación</p><p className="text-sm text-slate-600">Del {date(filters.fecha_desde)} al {date(filters.fecha_hasta)}</p></div>{filters.fecha_desde && filters.fecha_hasta && <a href={route('reportes.cobranza.pdf', { fecha_desde: filters.fecha_desde, fecha_hasta: filters.fecha_hasta })} className="rounded-md bg-slate-800 px-4 py-2 text-sm font-semibold text-white print:hidden">Generar PDF</a>}</div>
                    <div className="overflow-x-auto"><table className="min-w-full border-collapse text-xs"><thead><tr className="bg-slate-100 text-left uppercase text-slate-700"><th className="border border-slate-400 px-2 py-2">Fecha</th><th className="border border-slate-400 px-2 py-2">Concepto</th><th className="border border-slate-400 px-2 py-2 text-right">Diferencia capturado de más por cajera</th>{banks.map((bank) => <th key={bank.id} className="border border-slate-400 px-2 py-2 text-right">{bank.name}{bank.account_number ? <><br /><span className="font-normal">{bank.account_number}</span></> : null}</th>)}<th className="border border-slate-400 px-2 py-2 text-right">Total bancos</th><th className="border border-slate-400 px-2 py-2 text-right">Póliza ing</th></tr></thead><tbody>
                        {rows.length === 0 ? <tr><td colSpan={banks.length + 5} className="border border-slate-300 px-4 py-10 text-center text-slate-500">Selecciona un rango de fechas para consultar las cobranzas.</td></tr> : rows.map((row) => <tr key={row.id} className="align-top"><td className="border border-slate-300 px-2 py-2 whitespace-nowrap">{date(row.date)}</td><td className="border border-slate-300 px-2 py-2">{row.concept}</td><td className="border border-slate-300 px-2 py-2 text-right">{money.format(row.cashier_difference)}</td>{banks.map((bank) => <td key={bank.id} className="border border-slate-300 px-2 py-2 text-right">{money.format(row.banks[bank.id] || 0)}</td>)}<td className="border border-slate-300 px-2 py-2 text-right font-semibold">{money.format(row.total_banks)}</td><td className="border border-slate-300 px-2 py-2 text-right"><div>{money.format(row.policy_amount || 0)}</div><div className="text-[10px] font-normal">{row.policy_number}</div></td></tr>)}
                        {rows.length > 0 && <tr className="bg-yellow-100 font-bold"><td colSpan="3" className="border border-slate-400 px-2 py-2 text-right">TOTALES</td>{banks.map((bank) => <td key={bank.id} className="border border-slate-400 px-2 py-2 text-right">{money.format(totals.banks[bank.id] || 0)}</td>)}<td className="border border-slate-400 px-2 py-2 text-right">{money.format(totals.totalBanks)}</td><td className="border border-slate-400 px-2 py-2 text-right">{money.format(policyTotal)}</td></tr>}
                    </tbody></table></div>
                    {rows.length > 0 && <div className="mt-4 border-t-2 border-slate-700 pt-3 text-sm"><div className="grid gap-2 md:grid-cols-3"><div><strong>Suma de ingresos depositados:</strong> {money.format(totals.totalBanks)}</div><div><strong>Total pólizas de ingreso:</strong> {money.format(policyTotal)}</div><div><strong>Diferencia bancos - pólizas:</strong> {money.format(totals.totalBanks - Number(policyTotal))}</div></div><div className="mt-3 grid gap-2 border-t border-slate-300 pt-3 md:grid-cols-2">{banks.map((bank) => <div key={bank.id}><strong>Total ingreso {bank.name} del {date(filters.fecha_desde)} al {date(filters.fecha_hasta)}:</strong> {money.format(totals.banks[bank.id] || 0)}</div>)}<div><strong>ING. DE OTRAS CUENTAS AL {date(filters.fecha_hasta)}:</strong> {money.format(totals.banks.otros || 0)}</div><div><strong>MENOS: INGRESOS SEGÚN LAYOUT FACTURADO:</strong> {money.format(0)}</div><div><strong>DIF. ENTRE LO COBRADO Y GENERADO AL {date(filters.fecha_hasta)}:</strong> {money.format(totals.totalBanks - Number(policyTotal))}</div><div><strong>DIFERENCIA PARA AJUSTE DEL MES:</strong> {money.format(0)}</div><div><strong>PAGOS DE LA DRAEF:</strong> {draefPayments.length ? draefPayments.map((payment) => `${date(payment.date)} ${money.format(payment.amount)}`).join(' · ') : money.format(0)}</div></div></div>}
                </div>
            </div></div>
        </AuthenticatedLayout>
    );
}
