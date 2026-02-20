import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';
import PrimaryButton from '@/Components/PrimaryButton';
import InputLabel from '@/Components/InputLabel';
import TextInput from '@/Components/TextInput';
import InputError from '@/Components/InputError';
import { useEffect, useState } from 'react';

// Basic helper to convert numbers to letters in Spanish
const unidad = (num) => {
    switch (num) {
        case 1: return 'UN';
        case 2: return 'DOS';
        case 3: return 'TRES';
        case 4: return 'CUATRO';
        case 5: return 'CINCO';
        case 6: return 'SEIS';
        case 7: return 'SIETE';
        case 8: return 'OCHO';
        case 9: return 'NUEVE';
        default: return '';
    }
};

const decena = (num) => {
    let d = Math.floor(num / 10);
    let u = num % 10;
    switch (d) {
        case 1:
            switch (u) {
                case 0: return 'DIEZ';
                case 1: return 'ONCE';
                case 2: return 'DOCE';
                case 3: return 'TRECE';
                case 4: return 'CATORCE';
                case 5: return 'QUINCE';
                default: return 'DIECI' + unidad(u);
            }
        case 2:
            if (u === 0) return 'VEINTE';
            return 'VEINTI' + unidad(u);
        case 3: return 'TREINTA' + (u > 0 ? ' Y ' + unidad(u) : '');
        case 4: return 'CUARENTA' + (u > 0 ? ' Y ' + unidad(u) : '');
        case 5: return 'CINCUENTA' + (u > 0 ? ' Y ' + unidad(u) : '');
        case 6: return 'SESENTA' + (u > 0 ? ' Y ' + unidad(u) : '');
        case 7: return 'SETENTA' + (u > 0 ? ' Y ' + unidad(u) : '');
        case 8: return 'OCHENTA' + (u > 0 ? ' Y ' + unidad(u) : '');
        case 9: return 'NOVENTA' + (u > 0 ? ' Y ' + unidad(u) : '');
        default: return unidad(u);
    }
};

const centena = (num) => {
    let c = Math.floor(num / 100);
    let d = num % 100;
    switch (c) {
        case 1:
            if (d === 0) return 'CIEN';
            return 'CIENTO ' + decena(d);
        case 2: return 'DOSCIENTOS ' + decena(d);
        case 3: return 'TRESCIENTOS ' + decena(d);
        case 4: return 'CUATROCIENTOS ' + decena(d);
        case 5: return 'QUINIENTOS ' + decena(d);
        case 6: return 'SEISCIENTOS ' + decena(d);
        case 7: return 'SETECIENTOS ' + decena(d);
        case 8: return 'OCHOCIENTOS ' + decena(d);
        case 9: return 'NOVECIENTOS ' + decena(d);
        default: return decena(d);
    }
};

const miles = (num) => {
    let m = Math.floor(num / 1000);
    let r = num % 1000;
    let str = '';
    if (m === 1) str = 'MIL ';
    else if (m > 1) str = centena(m) + ' MIL ';
    return str + centena(r);
};

const convertToLetters = (amount) => {
    if (!amount || amount === 0) return '';
    const parts = amount.toString().split('.');
    const integer = parseInt(parts[0]);
    const decimal = parts[1] ? parts[1].padEnd(2, '0').substring(0, 2) : '00';

    let result = '';
    if (integer === 0) result = 'CERO';
    else if (integer < 1000000) result = miles(integer);
    else {
        let mill = Math.floor(integer / 1000000);
        let resto = integer % 1000000;
        result = centena(mill) + (mill === 1 ? ' MILLON ' : ' MILLONES ') + miles(resto);
    }

    return result.trim() + ' PESOS ' + decimal + '/100 MN';
};

export default function Create({ auth, requirement, employees, organismos, pendingRequirements, providers, defaultSignatories }) {
    const { data, setData, post, processing, errors } = useForm({
        organismo_id: requirement?.organismo_id || organismos[0]?.id || '',
        payment_date: new Date().toISOString().split('T')[0],
        beneficiary_type: 'employee', // 'employee' or 'provider'
        beneficiary_id: '',
        beneficiary: '',
        amount: requirement?.total || 0,
        amount_letters: requirement ? convertToLetters(requirement.total) : '',
        requirement_id: requirement?.id || '',
        concept: requirement ? `PAGO CORRESPONDIENTE AL REQUERIMIENTO ${requirement.requirement_number}/${requirement.year}: ${requirement.description}` : '',
        payment_type: 'transferencia',
        reference: '',
        elaborated_by_id: defaultSignatories.elaborated_by_id || '',
        formulated_by_id: defaultSignatories.formulated_by_id || '',
        authorized_by_id: defaultSignatories.authorized_by_id || '',
    });

    useEffect(() => {
        if (requirement?.type === 'cfe') {
            const cfeProvider = providers.find(p => p.rfc === 'CSS160330CP7' || p.name.includes('CFE'));
            if (cfeProvider) {
                setData(prev => ({
                    ...prev,
                    beneficiary_type: 'provider',
                    beneficiary_id: cfeProvider.id,
                    beneficiary: cfeProvider.name,
                }));
            }
        }
    }, [requirement]);

    useEffect(() => {
        if (data.amount) {
            setData('amount_letters', convertToLetters(data.amount));
        }
    }, [data.amount]);

    useEffect(() => {
        const selectedReq = pendingRequirements.find(r => r.id == data.requirement_id) || (requirement?.id == data.requirement_id ? requirement : null);
        const selectedProv = providers.find(p => p.id == data.beneficiary_id);

        if (selectedReq && (selectedReq.type === 'cfe' || requirement?.type === 'cfe')) {
            const formatDateLong = (dateStr) => {
                if (!dateStr) return '...';
                // Adjust for YYYY-MM-DD input to avoid UTC-1 day shifts
                const [year, month, day] = dateStr.split('-');
                const date = new Date(year, month - 1, day);
                return new Intl.DateTimeFormat('es-MX', {
                    day: 'numeric',
                    month: 'long',
                    year: 'numeric'
                }).format(date).toUpperCase();
            };

            const reqNum = selectedReq.label ? selectedReq.label.split(' - ')[0] : (selectedReq.formatted_number || '');
            const startDate = formatDateLong(selectedReq.start_date);
            const endDate = formatDateLong(selectedReq.end_date);
            const bank = selectedProv ? (selectedProv.bank_name || '...') : '...';
            const tracking = data.reference || '...';

            const newConcept = `PAGO CORRESPONDIENTE AL REQUERIMIENTO ${reqNum} CONSUMO DE LA FACTURACION DE ENERGIA ELECTRICA CORRESPONDIENTE ${startDate} AL ${endDate} CORRESPONDIENTE A LAS CASETAS DE BOMBEO DEL MUNICIPIO DE JOSE MARIA MORELOS, PAGADO AL BANCO ${bank} CON CLAVE DE RASTREO ${tracking}`;

            setData('concept', newConcept);
        }
    }, [data.requirement_id, data.beneficiary_id, data.reference]);

    const handleBeneficiaryChange = (e) => {
        const id = e.target.value;
        if (data.beneficiary_type === 'employee') {
            const emp = employees.find(e => e.id == id);
            if (emp) {
                setData(prev => ({
                    ...prev,
                    beneficiary_id: id,
                    beneficiary: `C. ${emp.nombre} ${emp.primer_apellido} ${emp.segundo_apellido}`
                }));
            }
        } else {
            const prov = providers.find(p => p.id == id);
            if (prov) {
                setData(prev => ({
                    ...prev,
                    beneficiary_id: id,
                    beneficiary: prov.name,
                }));
            }
        }
    };

    const handleRequirementChange = (e) => {
        const reqId = e.target.value;
        const selectedReq = pendingRequirements.find(r => r.id == reqId);

        if (selectedReq) {
            let newData = {
                ...data,
                requirement_id: reqId,
                amount: selectedReq.total,
                concept: `CORRESPONDIENTE AL REQUERIMIENTO ${selectedReq.label.split(' - ')[0]}: ${selectedReq.description}`,
            };

            // Auto-select CFE provider if type is CFE
            if (selectedReq.type === 'cfe') {
                const cfeProvider = providers.find(p => p.rfc === 'CSS160330CP7' || p.name.includes('CFE'));
                if (cfeProvider) {
                    newData.beneficiary_type = 'provider';
                    newData.beneficiary_id = cfeProvider.id;
                    newData.beneficiary = cfeProvider.name;
                }
            }

            setData(newData);
        } else {
            setData({
                ...data,
                requirement_id: '',
                amount: 0,
                concept: '',
            });
        }
    };

    const submit = (e) => {
        e.preventDefault();
        post(route('payments.store'));
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Registrar Pago con Requerimiento</h2>}
        >
            <Head title="Registrar Pago" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <form onSubmit={submit} className="space-y-6">
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                {/* Organismo */}
                                <div>
                                    <InputLabel htmlFor="organismo_id" value="Organismo" />
                                    <select
                                        id="organismo_id"
                                        className="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        value={data.organismo_id}
                                        onChange={(e) => setData('organismo_id', e.target.value)}
                                        required
                                    >
                                        {organismos.map(org => (
                                            <option key={org.id} value={org.id}>{org.nombre}</option>
                                        ))}
                                    </select>
                                    <InputError message={errors.organismo_id} className="mt-2" />
                                </div>

                                {/* Requerimiento Selection */}
                                <div>
                                    <InputLabel htmlFor="requirement_id" value="Seleccionar Requerimiento" />
                                    <select
                                        id="requirement_id"
                                        className="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        value={data.requirement_id}
                                        onChange={handleRequirementChange}
                                        required
                                    >
                                        <option value="">-- Seleccione un requerimiento pendiente --</option>
                                        {pendingRequirements.map(req => (
                                            <option key={req.id} value={req.id}>{req.label}</option>
                                        ))}
                                    </select>
                                    <InputError message={errors.requirement_id} className="mt-2" />
                                </div>
                            </div>

                            <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                                {/* Fecha de Pago */}
                                <div>
                                    <InputLabel htmlFor="payment_date" value="Fecha de Pago" />
                                    <TextInput
                                        id="payment_date"
                                        type="date"
                                        className="mt-1 block w-full"
                                        value={data.payment_date}
                                        onChange={(e) => setData('payment_date', e.target.value)}
                                        required
                                    />
                                    <InputError message={errors.payment_date} className="mt-2" />
                                </div>

                                {/* Tipo de Pago */}
                                <div>
                                    <InputLabel htmlFor="payment_type" value="Tipo de Pago" />
                                    <select
                                        id="payment_type"
                                        className="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        value={data.payment_type}
                                        onChange={(e) => setData('payment_type', e.target.value)}
                                        required
                                    >
                                        <option value="transferencia">Transferencia</option>
                                        <option value="cheque">Cheque</option>
                                    </select>
                                    <InputError message={errors.payment_type} className="mt-2" />
                                </div>

                                {/* Referencia (Num Cheque o Referencia) */}
                                <div>
                                    <InputLabel htmlFor="reference" value={data.payment_type === 'cheque' ? 'Número de Cheque' : 'Referencia / Clave de Rastreo'} />
                                    <TextInput
                                        id="reference"
                                        className="mt-1 block w-full"
                                        value={data.reference}
                                        onChange={(e) => setData('reference', e.target.value)}
                                        required
                                    />
                                    <InputError message={errors.reference} className="mt-2" />
                                </div>
                            </div>

                            <div className="bg-blue-50 p-4 rounded-lg mb-6 border border-blue-100">
                                <h3 className="text-sm font-bold text-blue-800 uppercase mb-3 flex items-center">
                                    <svg className="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    Información del Beneficiario
                                </h3>
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <InputLabel value="Tipo de Beneficiario" />
                                        <div className="flex gap-4 mt-2">
                                            <label className="flex items-center">
                                                <input
                                                    type="radio"
                                                    value="employee"
                                                    checked={data.beneficiary_type === 'employee'}
                                                    onChange={() => setData({ ...data, beneficiary_type: 'employee', beneficiary_id: '', beneficiary: '' })}
                                                    className="mr-2 border-gray-300 text-indigo-600 focus:ring-indigo-600"
                                                />
                                                Trabajador
                                            </label>
                                            <label className="flex items-center">
                                                <input
                                                    type="radio"
                                                    value="provider"
                                                    checked={data.beneficiary_type === 'provider'}
                                                    onChange={() => setData({ ...data, beneficiary_type: 'provider', beneficiary_id: '', beneficiary: '' })}
                                                    className="mr-2 border-gray-300 text-indigo-600 focus:ring-indigo-600"
                                                />
                                                Proveedor (Catálogo)
                                            </label>
                                        </div>
                                    </div>

                                    <div>
                                        <InputLabel value={data.beneficiary_type === 'employee' ? 'Seleccionar Trabajador' : 'Seleccionar Proveedor'} />
                                        <select
                                            className="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            value={data.beneficiary_id}
                                            onChange={handleBeneficiaryChange}
                                        >
                                            <option value="">-- Seleccione... --</option>
                                            {data.beneficiary_type === 'employee' ? (
                                                employees.map(emp => (
                                                    <option key={emp.id} value={emp.id}>{emp.full_name}</option>
                                                ))
                                            ) : (
                                                providers.map(prov => (
                                                    <option key={prov.id} value={prov.id}>{prov.name} ({prov.rfc})</option>
                                                ))
                                            )}
                                        </select>
                                    </div>
                                </div>

                                <div className="mt-4">
                                    <InputLabel htmlFor="beneficiary" value="Beneficiario (Nombre final en recibo)" />
                                    <TextInput
                                        id="beneficiary"
                                        className="mt-1 block w-full bg-white font-semibold"
                                        value={data.beneficiary}
                                        onChange={(e) => setData('beneficiary', e.target.value)}
                                        required
                                        placeholder="Ej: C. Josue Rodriguez Pamplona o CFE SUMINISTRADOR..."
                                    />
                                    <InputError message={errors.beneficiary} className="mt-2" />
                                </div>
                            </div>

                            <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
                                {/* Monto */}
                                <div className="md:col-span-1">
                                    <InputLabel htmlFor="amount" value="Cantidad ($)" />
                                    <TextInput
                                        id="amount"
                                        type="text"
                                        className="mt-1 block w-full font-bold text-lg"
                                        value={data.amount ? new Intl.NumberFormat('es-MX', { minimumFractionDigits: 2 }).format(data.amount) : ''}
                                        onChange={(e) => {
                                            // Extract numbers and one decimal point
                                            const val = e.target.value.replace(/[^0-9.]/g, '');
                                            // Prevent multiple decimal points
                                            const parts = val.split('.');
                                            const finalVal = parts[0] + (parts.length > 1 ? '.' + parts.slice(1).join('') : '');
                                            setData('amount', finalVal);
                                        }}
                                        onBlur={(e) => {
                                            const val = parseFloat(data.amount);
                                            if (!isNaN(val)) {
                                                setData('amount', val.toFixed(2));
                                            }
                                        }}
                                        required
                                    />
                                    <InputError message={errors.amount} className="mt-2" />
                                </div>

                                {/* Cantidad con Letras */}
                                <div className="md:col-span-3">
                                    <InputLabel htmlFor="amount_letters" value="Cantidad con Letra" />
                                    <TextInput
                                        id="amount_letters"
                                        className="mt-1 block w-full bg-gray-50 italic"
                                        value={data.amount_letters}
                                        onChange={(e) => setData('amount_letters', e.target.value)}
                                        required
                                    />
                                    <InputError message={errors.amount_letters} className="mt-2" />
                                </div>
                            </div>

                            {/* Concepto */}
                            <div>
                                <InputLabel htmlFor="concept" value="Concepto del Pago" />
                                <textarea
                                    id="concept"
                                    className="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    rows="3"
                                    value={data.concept}
                                    onChange={(e) => setData('concept', e.target.value)}
                                    required
                                />
                                <InputError message={errors.concept} className="mt-2" />
                            </div>

                            <div className="border-t pt-6 bg-gray-50 p-4 rounded-lg">
                                <h3 className="text-md font-medium text-gray-700 mb-4">Firmas del Recibo</h3>
                                <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    {/* Elaboró */}
                                    <div>
                                        <InputLabel htmlFor="elaborated_by_id" value="Elaboró" />
                                        <select
                                            id="elaborated_by_id"
                                            className="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm"
                                            value={data.elaborated_by_id}
                                            onChange={(e) => setData('elaborated_by_id', e.target.value)}
                                        >
                                            <option value="">Seleccione...</option>
                                            {employees.map(emp => (
                                                <option key={emp.id} value={emp.id}>{emp.nombre} {emp.primer_apellido}</option>
                                            ))}
                                        </select>
                                    </div>

                                    {/* Formuló */}
                                    <div>
                                        <InputLabel htmlFor="formulated_by_id" value="Formuló" />
                                        <select
                                            id="formulated_by_id"
                                            className="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm"
                                            value={data.formulated_by_id}
                                            onChange={(e) => setData('formulated_by_id', e.target.value)}
                                        >
                                            <option value="">Seleccione...</option>
                                            {employees.map(emp => (
                                                <option key={emp.id} value={emp.id}>{emp.nombre} {emp.primer_apellido}</option>
                                            ))}
                                        </select>
                                    </div>

                                    {/* Autorizó */}
                                    <div>
                                        <InputLabel htmlFor="authorized_by_id" value="Autorizó" />
                                        <select
                                            id="authorized_by_id"
                                            className="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm"
                                            value={data.authorized_by_id}
                                            onChange={(e) => setData('authorized_by_id', e.target.value)}
                                        >
                                            <option value="">Seleccione...</option>
                                            {employees.map(emp => (
                                                <option key={emp.id} value={emp.id}>{emp.nombre} {emp.primer_apellido}</option>
                                            ))}
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div className="flex justify-end gap-4">
                                <PrimaryButton disabled={processing}>
                                    Generar Recibo de Pago
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
