import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import PrimaryButton from '@/Components/PrimaryButton';
import { Head, Link, router, useForm } from '@inertiajs/react';

const money = new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' });
const date = (value) => value ? String(value).slice(0, 10).split('-').reverse().join('/') : '';

export default function Index({ rows = [], banks = [], filters = {}, draefTotal = 0, draefSubtotalTotal = 0, draefIvaTotal = 0, policyTotal = 0, draefPayments = [] }) {
    const { data, setData, get, processing, errors } = useForm({
        fecha_desde: filters.fecha_desde || '',
        fecha_hasta: filters.fecha_hasta || '',
    });

    const submit = (event) => {
        event.preventDefault();
        get(route('reportes.cobranza'), { preserveState: true, replace: true });
    };

    const handleFechaDesdeChange = (value) => {
        const shouldSyncHasta = !data.fecha_hasta || data.fecha_hasta === data.fecha_desde;
        setData((current) => ({
            ...current,
            fecha_desde: value,
            fecha_hasta: shouldSyncHasta ? value : current.fecha_hasta,
        }));
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
                    <form onSubmit={submit} className="mt-5 grid gap-4 md:grid-cols-[1fr_1fr_auto] md:items-end"><div><label className="block text-sm font-medium text-slate-700">Fecha inicial</label><input type="date" value={data.fecha_desde} onChange={(e) => handleFechaDesdeChange(e.target.value)} className="mt-1 w-full rounded-md border-gray-300" required /></div><div><label className="block text-sm font-medium text-slate-700">Fecha final</label><input type="date" value={data.fecha_hasta} onChange={(e) => setData('fecha_hasta', e.target.value)} className="mt-1 w-full rounded-md border-gray-300" required /><div className="text-sm text-red-600">{errors.fecha_hasta}</div></div><PrimaryButton disabled={processing}>Consultar</PrimaryButton></form>
                </div>

                <div className="rounded-xl border border-slate-300 bg-white p-4 shadow-sm print:border-0 print:shadow-none">
                    <div className="mb-4 flex items-start justify-between gap-4 border-b-2 border-slate-800 pb-3"><div><h1 className="text-xl font-bold uppercase text-slate-800">Comisión de Agua Potable y Alcantarillado del Estado de Quintana Roo</h1><p className="text-sm font-semibold uppercase text-slate-600">Relación de ingresos por recaudación</p><p className="text-sm text-slate-600">Del {date(filters.fecha_desde)} al {date(filters.fecha_hasta)}</p></div>{filters.fecha_desde && filters.fecha_hasta && <a href={route('reportes.cobranza.pdf', { fecha_desde: filters.fecha_desde, fecha_hasta: filters.fecha_hasta })} className="rounded-md bg-slate-800 px-4 py-2 text-sm font-semibold text-white print:hidden">Generar PDF</a>}</div>
                    <div className="overflow-x-auto"><table className="w-full border-collapse text-xs" style={{ tableLayout: 'fixed' }}><thead><tr className="bg-slate-100 text-left uppercase text-slate-700"><th className="border border-slate-400 px-2 py-2" style={{ width: '80px' }}>Fecha</th><th className="border border-slate-400 px-2 py-2" style={{ width: '90px' }}>Concepto</th><th className="border border-slate-400 px-2 py-2 text-right" style={{ width: '70px' }}>Dif. cajera</th>{banks.map((bank) => <th key={bank.id} className="border border-slate-400 px-2 py-2 text-right">{bank.name}{bank.account_number ? <><br /><span className="font-normal">{bank.account_number}</span></> : null}</th>)}<th className="border border-slate-400 px-2 py-2 text-right">Total bancos</th><th className="border border-slate-400 px-2 py-2 text-right" style={{ width: '110px' }}>Póliza ing</th></tr></thead><tbody>
                        {rows.length === 0 ? <tr><td colSpan={banks.length + 5} className="border border-slate-300 px-4 py-10 text-center text-slate-500">Selecciona un rango de fechas para consultar las cobranzas.</td></tr> : rows.map((row) => <tr key={row.id} className="align-top"><td className="border border-slate-300 px-2 py-2 whitespace-nowrap">{date(row.date)}</td><td className="border border-slate-300 px-2 py-2 whitespace-nowrap">{row.concept}</td><td className="border border-slate-300 px-2 py-2 text-right">{row.cashier_difference === null ? '' : money.format(row.cashier_difference)}</td>{banks.map((bank) => <td key={bank.id} className="border border-slate-300 px-2 py-2 text-right">{row.banks[bank.id] === null ? '' : money.format(row.banks[bank.id] || 0)}</td>)}<td className="border border-slate-300 px-2 py-2 text-right font-semibold">{row.total_banks === null ? '' : money.format(row.total_banks)}</td><td className="border border-slate-300 px-2 py-2 text-right">{row.policy_line?.label ? <span className="font-semibold">{row.policy_line.text}</span> : money.format(row.policy_line?.value || 0)}</td></tr>)}
                        {rows.length > 0 && <tr className="bg-yellow-100 font-bold"><td colSpan="3" className="border border-slate-400 px-2 py-2 text-right">TOTALES</td>{banks.map((bank) => <td key={bank.id} className="border border-slate-400 px-2 py-2 text-right">{money.format(totals.banks[bank.id] || 0)}</td>)}<td className="border border-slate-400 px-2 py-2 text-right">{money.format(totals.totalBanks)}</td><td className="border border-slate-400 px-2 py-2 text-right">{money.format(policyTotal)}</td></tr>}
                    </tbody></table></div>
                    {rows.length > 0 && (() => {
                        const diferencia = totals.totalBanks - Number(policyTotal);
                        return (
                        <div className="mt-4 grid gap-4 border-t-2 border-slate-700 pt-3 text-xs md:grid-cols-2">
                            <table className="w-full border-collapse">
                                <tbody>
                                    {banks.filter((bank) => !bank.id.startsWith('sin-banco-')).map((bank) => <tr key={bank.id}><td className="border border-slate-300 px-2 py-1">TOTAL ING. {bank.name} {bank.account_number} DEL {date(filters.fecha_desde)} AL {date(filters.fecha_hasta)}</td><td className="border border-slate-300 px-2 py-1 text-right">{money.format(totals.banks[bank.id] || 0)}</td></tr>)}
                                    <tr><td className="border border-slate-300 px-2 py-1">ING. DE OTRAS CUENTAS DEL {date(filters.fecha_desde)} AL {date(filters.fecha_hasta)}</td><td className="border border-slate-300 px-2 py-1 text-right">{money.format(0)}</td></tr>
                                    <tr className="font-bold"><td className="border border-slate-300 px-2 py-1">SUMA DE LOS INGRESOS DEPOSITADOS</td><td className="border border-slate-300 px-2 py-1 text-right">{money.format(totals.totalBanks)}</td></tr>
                                    <tr className="font-bold"><td className="border border-slate-300 px-2 py-1">MENOS: INGRESOS SEGÚN LAYOUT FACTURADO</td><td className="border border-slate-300 px-2 py-1 text-right">{money.format(policyTotal)}</td></tr>
                                    <tr className="bg-yellow-100 font-bold"><td className="border border-slate-400 px-2 py-1">DIF, ENTRE LO COBRADO Y GENERADO AL {date(filters.fecha_hasta)}</td><td className="border border-slate-400 px-2 py-1 text-right">{money.format(diferencia)}</td></tr>
                                </tbody>
                            </table>
                            <div>
                                <table className="w-full border-collapse">
                                    <tbody>
                                        <tr className="bg-yellow-100 font-bold"><td className="border border-slate-400 px-2 py-1" colSpan="2">DIFERENCIA PARA AJUSTE DEL MES</td><td className="border border-slate-400 px-2 py-1 text-right">{money.format(diferencia)}</td></tr>
                                    </tbody>
                                </table>
                                <table className="mt-2 w-full border-collapse">
                                    <thead><tr className="bg-slate-100"><th className="border border-slate-400 px-2 py-1">MENOS PAGOS DE LA DRAEF: FECHA</th><th className="border border-slate-400 px-2 py-1">FACTURACIÓN</th><th className="border border-slate-400 px-2 py-1">IVA</th><th className="border border-slate-400 px-2 py-1">TOTAL</th></tr></thead>
                                    <tbody>
                                        {draefPayments.length === 0 ? <tr><td className="border border-slate-300 px-2 py-1" colSpan="4">Sin pagos DRAEF en el periodo.</td></tr> : draefPayments.map((payment, index) => <tr key={index}><td className="border border-slate-300 px-2 py-1">{date(payment.date)}</td><td className="border border-slate-300 px-2 py-1 text-right">{money.format(payment.subtotal)}</td><td className="border border-slate-300 px-2 py-1 text-right">{money.format(payment.iva)}</td><td className="border border-slate-300 px-2 py-1 text-right">{money.format(payment.amount)}</td></tr>)}
                                        <tr className="font-bold"><td className="border border-slate-300 px-2 py-1">SUMAS</td><td className="border border-slate-300 px-2 py-1 text-right">{money.format(draefSubtotalTotal)}</td><td className="border border-slate-300 px-2 py-1 text-right">{money.format(draefIvaTotal)}</td><td className="border border-slate-300 px-2 py-1 text-right">{money.format(draefTotal)}</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        );
                    })()}
                </div>
            </div></div>
        </AuthenticatedLayout>
    );
}
