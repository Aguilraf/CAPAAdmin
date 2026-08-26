import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import InputError from '@/Components/InputError';
import Pagination from '@/Components/Pagination';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import { Head, router, useForm } from '@inertiajs/react';
import { useState, useEffect } from 'react';

const money = new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' });

export default function Index({ banks, movements, filters = {} }) {
    const [search, setSearch] = useState(filters.search || '');
    const { data, setData, post, processing, errors, reset } = useForm({ bank_id: filters.bank_id || '', file: null });
    const selectedBank = banks.find((bank) => String(bank.id) === String(data.bank_id));

    useEffect(() => {
        setData('bank_id', filters.bank_id || '');
    }, [filters.bank_id]);

    const filter = (changes) => router.get(route('incomes.index'), { bank_id: changes.bank_id ?? filters.bank_id ?? '', search: changes.search ?? search }, { preserveState: true, replace: true });
    
    const handleBankChange = (event) => {
        const val = event.target.value;
        setData('bank_id', val);
        filter({ bank_id: val });
    };

    const submit = (event) => {
        event.preventDefault();
        post(route('incomes.import'), { forceFormData: true, onSuccess: () => reset('file') });
    };

    return (
        <AuthenticatedLayout header={<h2 className="text-xl font-semibold text-gray-800">Ingresos bancarios</h2>}>
            <Head title="Ingresos" />
            <div className="py-8"><div className="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg"><form onSubmit={submit} className="p-6"><div className="mb-5"><h3 className="text-lg font-semibold text-gray-900">Importar reporte bancario</h3><p className="mt-1 text-sm text-gray-500">Selecciona la cuenta a la que corresponde el archivo. Se guardarán abonos, cargos, comisiones y cualquier otro movimiento.</p></div><div className="grid grid-cols-1 items-end gap-5 md:grid-cols-3">
                    <div><label htmlFor="bank_id" className="block text-sm font-medium text-gray-700">Banco y cuenta *</label><select id="bank_id" value={data.bank_id} onChange={handleBankChange} className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required><option value="">Selecciona una cuenta</option>{banks.map((bank) => <option key={bank.id} value={bank.id}>{bank.name} · {bank.account_number}</option>)}</select><InputError className="mt-2" message={errors.bank_id} /></div>
                    <div><label htmlFor="file" className="block text-sm font-medium text-gray-700">Reporte Excel *</label><input id="file" type="file" accept=".xlsx,.xls,.csv" onChange={(event) => setData('file', event.target.files[0])} className="mt-1 block w-full text-sm text-gray-600 file:mr-4 file:rounded-md file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100" required /><InputError className="mt-2" message={errors.file} /></div>
                    <PrimaryButton disabled={processing || !data.file || !data.bank_id}>{processing ? 'Importando...' : 'Importar movimientos'}</PrimaryButton>
                </div>
                {selectedBank && (
                    <div className="mt-4 rounded-md bg-blue-50 px-4 py-3 text-sm text-blue-800 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                        <div>
                            Formato configurado para este banco: <strong className="uppercase">{selectedBank.import_template === 'hsbc' ? 'HSBC' : selectedBank.import_template === 'azteca' ? 'Banco Azteca' : 'Plantilla Estándar para otros bancos'}</strong>.
                            {selectedBank.import_template === 'custom' && (
                                <p className="mt-1 text-xs text-blue-600">
                                    Este banco usa nuestra plantilla de importación unificada. Completa la plantilla con los movimientos del banco antes de subirla.
                                </p>
                            )}
                        </div>
                        {selectedBank.import_template === 'custom' && (
                            <a
                                href={route('incomes.template')}
                                className="inline-flex items-center gap-1.5 font-semibold text-blue-700 hover:text-blue-900 underline whitespace-nowrap"
                                target="_blank"
                                rel="noreferrer"
                            >
                                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                Descargar plantilla estándar
                            </a>
                        )}
                    </div>
                )}
                </form></div>

                <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg"><div className="flex flex-col gap-4 p-6 md:flex-row md:items-end md:justify-between"><div><h3 className="text-lg font-semibold text-gray-900">Movimientos importados</h3><p className="text-sm text-gray-500">Los cargos y comisiones se conservan como información bancaria.</p></div><div className="flex flex-col gap-3 md:flex-row"><select value={filters.bank_id || ''} onChange={(event) => filter({ bank_id: event.target.value })} className="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"><option value="">Todos los bancos</option>{banks.map((bank) => <option key={bank.id} value={bank.id}>{bank.name} · {bank.account_number}</option>)}</select><TextInput value={search} onChange={(event) => { setSearch(event.target.value); filter({ search: event.target.value }); }} className="w-full md:w-64" placeholder="Buscar concepto o referencia..." /></div></div>
                    <div className="overflow-x-auto"><table className="min-w-full divide-y divide-gray-200"><thead className="bg-gray-50"><tr><th className="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Fecha</th><th className="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Banco</th><th className="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Concepto / movimiento</th><th className="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Abono</th><th className="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Cargo</th><th className="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Saldo</th></tr></thead><tbody className="divide-y divide-gray-200 bg-white">
                        {movements.data.map((movement) => <tr key={movement.id}><td className="whitespace-nowrap px-4 py-3 text-sm text-gray-700">{movement.operation_date}</td><td className="px-4 py-3 text-sm text-gray-700"><div>{movement.bank.name}</div><div className="text-xs text-gray-500">{movement.bank.account_number}</div></td><td className="max-w-md px-4 py-3 text-sm text-gray-700"><div className="font-medium">{movement.description || 'Sin concepto'}</div><div className="text-xs text-gray-500">{[movement.movement_number, movement.reference, movement.transaction_type].filter(Boolean).join(' · ')}</div></td><td className="whitespace-nowrap px-4 py-3 text-right text-sm font-medium text-green-700">{Number(movement.credit_amount) ? money.format(movement.credit_amount) : '—'}</td><td className="whitespace-nowrap px-4 py-3 text-right text-sm font-medium text-red-700">{Number(movement.debit_amount) ? money.format(movement.debit_amount) : '—'}</td><td className="whitespace-nowrap px-4 py-3 text-right text-sm text-gray-700">{movement.balance !== null ? money.format(movement.balance) : '—'}</td></tr>)}
                        {!movements.data.length && <tr><td colSpan="6" className="px-6 py-10 text-center text-gray-500">Aún no hay movimientos importados.</td></tr>}
                    </tbody></table></div><div className="p-4"><Pagination links={movements.links} /></div>
                </div>
            </div></div>
        </AuthenticatedLayout>
    );
}
