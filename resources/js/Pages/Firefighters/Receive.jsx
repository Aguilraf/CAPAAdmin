import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { Edit, Trash, Filter, Calendar } from 'lucide-react';
import { formatCurrency } from '../../firefighters_helpers';
import Swal from 'sweetalert2';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';

export default function Receive(props) {
    const [captures, setCaptures] = useState([]);
    const [communities, setCommunities] = useState([]);
    const [filters, setFilters] = useState({
        from_date: new Date().toISOString().split('T')[0],
        to_date: new Date().toISOString().split('T')[0],
        pending_requirement: true
    });
    const [requirementNumber, setRequirementNumber] = useState('');
    const [assignYear, setAssignYear] = useState(new Date().getFullYear());
    const [assignmentType, setAssignmentType] = useState('Reposición');
    const [isAssigning, setIsAssigning] = useState(false);
    const [fundAmount, setFundAmount] = useState(0);

    const [editingCapture, setEditingCapture] = useState(null);
    const [isEditModalOpen, setIsEditModalOpen] = useState(false);
    const [editFormData, setEditFormData] = useState({
        subtotal: '',
        rounding_commission: '',
        rounding_total: ''
    });

    useEffect(() => {
        fetchCaptures();
        axios.get('/communities').then(res => setCommunities(res.data));
        axios.get('/firefighter-settings-json').then(res => {
            const amount = res.data.report_fondo_amount || 0;
            setFundAmount(parseFloat(amount));
        });
    }, []);

    useEffect(() => {
        fetchNextReqNumber();
    }, [assignYear]);

    const fetchNextReqNumber = () => {
        axios.get(`/captures/next-requirement?year=${assignYear}`)
            .then(res => setRequirementNumber(res.data.next_requirement_number))
            .catch(err => console.error(err));
    };

    const fetchCaptures = () => {
        const params = new URLSearchParams(filters).toString();
        axios.get(`/captures?${params}`).then(res => setCaptures(res.data));
    };

    const handleFilterChange = (e) => {
        const { name, value } = e.target;
        setFilters(prev => {
            const newFilters = { ...prev, [name]: value };
            // Auto sync to_date if from_date changes
            if (name === 'from_date') {
                newFilters.to_date = value;
            }
            return newFilters;
        });
    };

    const handleDelete = async (id) => {
        if (confirm('¿Está seguro de eliminar esta captura?')) {
            await axios.delete(`/captures/${id}`);
            fetchCaptures();
        }
    };

    const handleEditClick = (capture) => {
        setEditingCapture(capture);
        setEditFormData({
            subtotal: capture.subtotal,
            rounding_commission: capture.rounding_commission,
            rounding_total: capture.rounding_total
        });
        setIsEditModalOpen(true);
    };

    const handleEditSubmit = async (e) => {
        e.preventDefault();

        // Recalculate values like in Capture page
        const subtotal = parseFloat(editFormData.subtotal) || 0;
        const roundingComm = parseFloat(editFormData.rounding_commission) || 0;
        const roundingTot = parseFloat(editFormData.rounding_total) || 0;
        const commission = (subtotal * 0.15) + roundingComm;
        const total = (subtotal - commission) + roundingTot;

        await axios.put(`/captures/${editingCapture.id}`, {
            ...editingCapture,
            ...editFormData,
            commission: commission.toFixed(2),
            total: total.toFixed(2)
        });

        setIsEditModalOpen(false);
        fetchCaptures();
    };

    const handleBulkAssign = async () => {
        if (captures.length === 0) {
            Swal.fire('Atención', 'No hay registros para asignar', 'warning');
            return;
        }

        if (!requirementNumber || !assignYear) {
            Swal.fire('Atención', 'Por favor ingresa número de requerimiento y año', 'warning');
            return;
        }

        let confirmationHtml = `
            <div class="text-left text-sm space-y-2">
                <p>Vas a asignar a <b>${captures.length}</b> registros:</p>
                <ul class="list-disc pl-5">
                   <li><b>Tipo:</b> ${assignmentType}</li>
                   <li><b>Requerimiento:</b> ${requirementNumber}</li>
                   <li><b>Año:</b> ${assignYear}</li>
                </ul>
            </div>
        `;

        if (assignmentType === 'Cancelación') {
            const sumCommissions = captures.reduce((acc, curr) => acc + (parseFloat(curr.commission) || 0), 0);
            const difference = fundAmount - sumCommissions;

            confirmationHtml += `
                <div class="mt-4 p-3 bg-red-50 border border-red-100 rounded text-red-900 text-sm">
                    <p class="font-bold border-b border-red-200 pb-1 mb-2">Cálculo de Fondo:</p>
                    <div class="grid grid-cols-2 gap-1">
                        <span>Fondo Configurado:</span> <span class="text-right font-mono">${formatCurrency(fundAmount)}</span>
                        <span>Suma Comisiones:</span> <span class="text-right font-mono">${formatCurrency(sumCommissions)}</span>
                        <span class="font-bold mt-1">Diferencia (Faltante):</span> <span class="text-right font-bold font-mono text-red-700 mt-1">${formatCurrency(difference)}</span>
                    </div>
                    <p class="mt-2 text-xs italic">
                        Se generará automáticamente un registro de <b>"TRANSFERENCIA ELECTRONICA"</b> por <b>${formatCurrency(difference)}</b>.
                    </p>
                </div>
            `;
        } else {
            confirmationHtml += `<p class="mt-2 text-gray-600 font-style:italic">Esta acción actualizará todos los registros listados.</p>`;
        }

        const result = await Swal.fire({
            title: 'Confirmar Asignación Masiva',
            html: confirmationHtml,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, Asignar',
            cancelButtonText: 'Cancelar'
        });

        if (result.isConfirmed) {
            try {
                setIsAssigning(true);
                await axios.post('/captures/assign-requirement', {
                    capture_ids: captures.map(c => c.id),
                    requirement_number: requirementNumber,
                    year: assignYear,
                    fund_type: assignmentType
                });

                Swal.fire('Éxito', 'Registros asignados correctamente', 'success');
                fetchCaptures();
                setRequirementNumber('');
            } catch (error) {
                console.error(error);
                Swal.fire('Error', error.response?.data?.message || 'Error al asignar', 'error');
            } finally {
                setIsAssigning(false);
            }
        }
    };

    const totals = React.useMemo(() => {
        return captures.reduce((acc, curr) => {
            acc.subtotal += parseFloat(curr.subtotal) || 0;
            acc.commission += parseFloat(curr.commission) || 0;
            acc.total += parseFloat(curr.total) || 0;
            return acc;
        }, { subtotal: 0, commission: 0, total: 0 });
    }, [captures]);

    return (
        <AuthenticatedLayout
            user={props.auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Recepción de Capturas (Bomberos)</h2>}
        >
            <Head title="Recepción" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900">

                            <div className="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
                                <h2 className="text-2xl font-bold text-gray-800">Recibe (Resumen)</h2>

                                <div className="flex flex-wrap items-center gap-3 bg-white p-3 rounded-lg shadow-sm border border-gray-100">
                                    <div className="flex items-center gap-2">
                                        <label className="text-sm font-medium text-gray-600">Desde:</label>
                                        <input
                                            type="date"
                                            name="from_date"
                                            value={filters.from_date}
                                            onChange={handleFilterChange}
                                            className="text-sm border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 p-1 border"
                                        />
                                    </div>
                                    <div className="flex items-center gap-2">
                                        <label className="text-sm font-medium text-gray-600">Hasta:</label>
                                        <input
                                            type="date"
                                            name="to_date"
                                            value={filters.to_date}
                                            onChange={handleFilterChange}
                                            className="text-sm border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 p-1 border"
                                        />
                                    </div>
                                    <button
                                        onClick={fetchCaptures}
                                        className="bg-blue-600 text-white px-4 py-1.5 rounded-md hover:bg-blue-700 flex items-center gap-2 transition-colors text-sm"
                                    >
                                        <Filter className="w-4 h-4" />
                                        Filtrar
                                    </button>
                                </div>
                            </div>

                            {/* Bulk Assignment Area */}
                            <div className="bg-blue-50 border border-blue-100 rounded-xl p-4 mb-6 flex flex-col md:flex-row items-center gap-4 shadow-sm">
                                <div className="flex-1">
                                    <h3 className="text-sm font-bold text-blue-900 mb-1">Asignación Masiva de Requerimientos</h3>
                                    <p className="text-xs text-blue-700 italic">Solo se muestran registros pendientes de requerimiento.</p>
                                </div>
                                <div className="flex items-center gap-3 w-full md:w-auto">
                                    <div className="relative flex-1 md:w-32">
                                        <input
                                            type="number"
                                            placeholder="Año"
                                            value={assignYear}
                                            onChange={(e) => setAssignYear(e.target.value)}
                                            min="2020"
                                            max="2030"
                                            className="w-full pl-3 pr-3 py-2 border border-blue-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm"
                                        />
                                    </div>
                                    <div className="relative flex-1 md:w-48">
                                        <input
                                            type="text"
                                            placeholder="Número de Requerimiento..."
                                            value={requirementNumber}
                                            onChange={(e) => setRequirementNumber(e.target.value)}
                                            className="w-full pl-3 pr-3 py-2 border border-blue-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm"
                                        />
                                    </div>
                                    <div className="relative flex-1 md:w-32">
                                        <select
                                            value={assignmentType}
                                            onChange={(e) => setAssignmentType(e.target.value)}
                                            className="w-full pl-3 pr-3 py-2 border border-blue-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm bg-white"
                                        >
                                            <option value="Reposición">Reposición</option>
                                            <option value="Inicial">Inicial</option>
                                            <option value="Cancelación">Cancelación</option>
                                        </select>
                                    </div>
                                    <button
                                        onClick={handleBulkAssign}
                                        disabled={isAssigning || captures.length === 0}
                                        className={`px-5 py-2 bg-blue-700 text-white rounded-lg text-sm font-bold shadow-md hover:bg-blue-800 transition-all flex items-center gap-2 ${(isAssigning || captures.length === 0) ? 'opacity-50 cursor-not-allowed' : ''}`}
                                    >
                                        {isAssigning ? 'Procesando...' : 'Asignar a Consulta Actual'}
                                    </button>
                                </div>
                            </div>

                            {/* Totals Section */}
                            <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                                <div className="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                                    <p className="text-sm text-gray-500 uppercase font-semibold">Subtotal General</p>
                                    <p className="text-2xl font-bold text-gray-800">{formatCurrency(totals.subtotal)}</p>
                                </div>
                                <div className="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                                    <p className="text-sm text-gray-500 uppercase font-semibold">Comisión General</p>
                                    <p className="text-2xl font-bold text-gray-800">{formatCurrency(totals.commission)}</p>
                                </div>
                                <div className="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                                    <p className="text-sm text-gray-500 uppercase font-semibold">Total General</p>
                                    <p className="text-2xl font-bold text-green-700">{formatCurrency(totals.total)}</p>
                                </div>
                            </div>

                            <div className="bg-white shadow rounded-lg overflow-hidden border border-gray-200">
                                <table className="min-w-full divide-y divide-gray-200">
                                    <thead className="bg-gray-50">
                                        <tr>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Comunidad</th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bombero</th>
                                            <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                                            <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Comisión</th>
                                            <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                            <th className="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody className="bg-white divide-y divide-gray-200">
                                        {captures.length > 0 ? captures.map((c) => (
                                            <tr key={c.id} className="hover:bg-gray-50 transition-colors">
                                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{c.date}</td>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{c.community?.name}</td>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{c.firefighter?.name}</td>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-600">{formatCurrency(c.subtotal)}</td>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500 italic">{formatCurrency(c.commission)}</td>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm text-right text-green-700 font-bold">{formatCurrency(c.total)}</td>
                                                <td className="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                                    <div className="flex justify-center gap-3">
                                                        <button onClick={() => handleEditClick(c)} className="text-blue-600 hover:text-blue-900 transition-colors">
                                                            <Edit className="w-4 h-4" />
                                                        </button>
                                                        <button onClick={() => handleDelete(c.id)} className="text-red-600 hover:text-red-900 transition-colors">
                                                            <Trash className="w-4 h-4" />
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        )) : (
                                            <tr>
                                                <td colSpan="7" className="px-6 py-10 text-center text-gray-500">
                                                    No se encontraron capturas en este rango de fechas.
                                                </td>
                                            </tr>
                                        )}
                                    </tbody>
                                </table>
                            </div>

                            {/* Edit Modal */}
                            {isEditModalOpen && (
                                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4">
                                    <div className="bg-white rounded-lg shadow-xl w-full max-w-md overflow-hidden animate-in fade-in zoom-in duration-200">
                                        <div className="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                                            <h3 className="text-lg font-bold text-gray-800">Editar Captura</h3>
                                            <span className="text-xs text-gray-500 px-2 py-1 bg-white rounded border">{editingCapture?.date}</span>
                                        </div>
                                        <form onSubmit={handleEditSubmit} className="p-6 space-y-4">
                                            <div>
                                                <p className="text-sm font-medium text-gray-700 mb-1">Comunidad: <span className="text-gray-900">{editingCapture?.community?.name}</span></p>
                                                <p className="text-sm font-medium text-gray-700">Bombero: <span className="text-gray-900">{editingCapture?.firefighter?.name}</span></p>
                                            </div>

                                            <hr className="my-2" />

                                            <div>
                                                <label className="block text-sm font-medium text-gray-700">Subtotal</label>
                                                <div className="mt-1 relative rounded-md shadow-sm">
                                                    <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                        <span className="text-gray-500 sm:text-xs">$</span>
                                                    </div>
                                                    <input
                                                        type="number"
                                                        step="0.01"
                                                        value={editFormData.subtotal}
                                                        onChange={(e) => {
                                                            const val = e.target.value;
                                                            if (val.includes('.') && val.split('.')[1].length > 2) return;
                                                            setEditFormData({ ...editFormData, subtotal: val });
                                                        }}
                                                        className="block w-full pl-7 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2 text-sm"
                                                        required
                                                    />
                                                </div>
                                            </div>

                                            <div className="grid grid-cols-2 gap-4">
                                                <div>
                                                    <label className="block text-sm font-medium text-gray-700">Red. Comisión</label>
                                                    <input
                                                        type="number"
                                                        step="0.01"
                                                        value={editFormData.rounding_commission}
                                                        onChange={(e) => {
                                                            const val = e.target.value;
                                                            if (val.includes('.') && val.split('.')[1].length > 2) return;
                                                            setEditFormData({ ...editFormData, rounding_commission: val });
                                                        }}
                                                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2 text-sm"
                                                    />
                                                </div>
                                                <div>
                                                    <label className="block text-sm font-medium text-gray-700">Red. Total</label>
                                                    <input
                                                        type="number"
                                                        step="0.01"
                                                        value={editFormData.rounding_total}
                                                        onChange={(e) => {
                                                            const val = e.target.value;
                                                            if (val.includes('.') && val.split('.')[1].length > 2) return;
                                                            setEditFormData({ ...editFormData, rounding_total: val });
                                                        }}
                                                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2 text-sm"
                                                    />
                                                </div>
                                            </div>

                                            <div className="space-y-3 pt-2 border-t border-gray-100">
                                                <div>
                                                    <label className="block text-xs font-semibold text-gray-500 uppercase">Comisión Calculada (15% + Red):</label>
                                                    <div className="text-md font-bold text-blue-700">
                                                        {formatCurrency((parseFloat(editFormData.subtotal || 0) * 0.15) + parseFloat(editFormData.rounding_commission || 0))}
                                                    </div>
                                                </div>

                                                <div className="bg-green-50 p-3 rounded-md flex justify-between items-center border border-green-100">
                                                    <span className="text-xs font-bold text-green-800 uppercase tracking-wider">Total Final:</span>
                                                    <span className="text-xl font-black text-green-900">
                                                        {formatCurrency(
                                                            (parseFloat(editFormData.subtotal || 0) -
                                                                ((parseFloat(editFormData.subtotal || 0) * 0.15) + parseFloat(editFormData.rounding_commission || 0))) +
                                                            parseFloat(editFormData.rounding_total || 0)
                                                        )}
                                                    </span>
                                                </div>
                                            </div>

                                            <div className="flex justify-end gap-3 mt-6">
                                                <button
                                                    type="button"
                                                    onClick={() => setIsEditModalOpen(false)}
                                                    className="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 text-sm font-medium transition-colors"
                                                >
                                                    Cancelar
                                                </button>
                                                <button
                                                    type="submit"
                                                    className="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm font-medium transition-colors shadow-sm"
                                                >
                                                    Guardar Cambios
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
