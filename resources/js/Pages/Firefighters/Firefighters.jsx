import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { Plus, Edit, Trash, MapPin, X } from 'lucide-react';
import { formatCurrency } from '../../firefighters_helpers';
import MapPicker from '../../Components/Firefighters/MapPicker';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';

export default function Firefighters({ auth, firefighters: initialFirefighters, communities: initialCommunities }) {
    const [firefighters, setFirefighters] = useState(initialFirefighters || []);
    const [communities, setCommunities] = useState(initialCommunities || []);
    const [isModalOpen, setIsModalOpen] = useState(false);
    const [editingId, setEditingId] = useState(null);
    const [formData, setFormData] = useState({
        name: '',
        community_id: '',
        active: true,
        max_rounding_amount: '0'
    });
    const [viewMapLocation, setViewMapLocation] = useState(null);

    const fetchData = () => {
        axios.get('/firefighters').then(res => setFirefighters(res.data));
    };

    const handleOpenModal = (firefighter = null) => {
        if (firefighter) {
            setEditingId(firefighter.id);
            setFormData({
                name: firefighter.name,
                community_id: firefighter.community_id,
                active: Boolean(firefighter.active),
                max_rounding_amount: firefighter.max_rounding_amount || '0'
            });
        } else {
            setEditingId(null);
            setFormData({
                name: '',
                community_id: '',
                active: true,
                max_rounding_amount: '0'
            });
        }
        setIsModalOpen(true);
    };

    const handleCloseModal = () => {
        setIsModalOpen(false);
        setEditingId(null);
    };

    const handleChange = (e) => {
        const { name, value, type, checked } = e.target;
        setFormData(prev => ({
            ...prev,
            [name]: type === 'checkbox' ? checked : value
        }));
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        try {
            if (editingId) {
                await axios.put(`/firefighters/${editingId}`, formData);
            } else {
                await axios.post('/firefighters', formData);
            }
            fetchData();
            handleCloseModal();
        } catch (error) {
            console.error(error);
            alert("Error al guardar");
        }
    };

    const handleDelete = async (id) => {
        if (confirm('¿Estás seguro de eliminar este bombero?')) {
            await axios.delete(`/firefighters/${id}`);
            fetchData();
        }
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Catálogo de Bomberos</h2>}
        >
            <Head title="Bomberos" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900">

                            <div className="flex justify-between items-center mb-6">
                                <h2 className="text-2xl font-bold text-gray-800">Catálogo de Bomberos</h2>
                                <button
                                    onClick={() => handleOpenModal()}
                                    className="flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                                >
                                    <Plus className="w-5 h-5 mr-2" />
                                    Nuevo Bombero
                                </button>
                            </div>

                            <div className="bg-white shadow rounded-lg overflow-hidden">
                                <table className="min-w-full divide-y divide-gray-200">
                                    <thead className="bg-gray-50">
                                        <tr>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Comunidad</th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Límite Redondeo</th>
                                            <th className="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                            <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody className="bg-white divide-y divide-gray-200">
                                        {firefighters.map((f) => (
                                            <tr key={f.id} className={!f.active ? 'bg-gray-50 text-gray-500' : ''}>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{f.id}</td>
                                                <td className="px-6 py-4 whitespace-nowrap font-medium text-gray-900">{f.name}</td>
                                                <td className="px-6 py-4 whitespace-nowrap text-gray-500">{f.community?.name}</td>
                                                <td className="px-6 py-4 whitespace-nowrap text-gray-500">{formatCurrency(f.max_rounding_amount)}</td>
                                                <td className="px-6 py-4 whitespace-nowrap text-center">
                                                    <span className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${f.active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}`}>
                                                        {f.active ? 'Activo' : 'Inactivo'}
                                                    </span>
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                    {f.community?.geolocation && (
                                                        <button onClick={() => setViewMapLocation(f.community.geolocation)} className="text-green-600 hover:text-green-900 mr-4" title="Ver ubicación comunidad">
                                                            <MapPin className="w-5 h-5" />
                                                        </button>
                                                    )}
                                                    <button onClick={() => handleOpenModal(f)} className="text-indigo-600 hover:text-indigo-900 mr-4"><Edit className="w-5 h-5" /></button>
                                                    <button onClick={() => handleDelete(f.id)} className="text-red-600 hover:text-red-900"><Trash className="w-5 h-5" /></button>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>

                            {isModalOpen && (
                                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
                                    <div className="bg-white rounded-lg p-6 w-full max-w-md">
                                        <h3 className="text-lg font-bold mb-4">{editingId ? 'Editar Bombero' : 'Nuevo Bombero'}</h3>
                                        <form onSubmit={handleSubmit} className="space-y-4">
                                            <div>
                                                <label className="block text-sm font-medium text-gray-700">Nombre</label>
                                                <input type="text" name="name" value={formData.name} onChange={handleChange} className="mt-1 block w-full rounded-md border-gray-300 shadow-sm border p-2" required />
                                            </div>
                                            <div>
                                                <label className="block text-sm font-medium text-gray-700">Comunidad</label>
                                                <select name="community_id" value={formData.community_id} onChange={handleChange} className="mt-1 block w-full rounded-md border-gray-300 shadow-sm border p-2" required>
                                                    <option value="">Seleccione una comunidad</option>
                                                    {communities.map(c => <option key={c.id} value={c.id}>{c.name}</option>)}
                                                </select>
                                            </div>
                                            <div>
                                                <label className="block text-sm font-medium text-gray-700">Límite de Redondeo Permitido ($)</label>
                                                <input type="number" step="0.01" name="max_rounding_amount" value={formData.max_rounding_amount} onChange={handleChange} className="mt-1 block w-full rounded-md border-gray-300 shadow-sm border p-2" />
                                                <p className="text-xs text-gray-500 mt-1">Monto máximo que se puede redondear a favor del bombero.</p>
                                            </div>
                                            <div className="flex items-center">
                                                <input type="checkbox" name="active" checked={formData.active} onChange={handleChange} className="h-4 w-4 text-blue-600 border-gray-300 rounded" />
                                                <label className="ml-2 block text-sm text-gray-900">Activo (Visible en captura)</label>
                                            </div>
                                            <div className="flex justify-end space-x-3 mt-6">
                                                <button type="button" onClick={handleCloseModal} className="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">Cancelar</button>
                                                <button type="submit" className="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Guardar</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            )}

                            {/* View Map Modal */}
                            {viewMapLocation && (
                                <div className="fixed inset-0 z-[60] flex items-center justify-center bg-black bg-opacity-50 p-4">
                                    <div className="bg-white rounded-lg p-6 w-full max-w-xl shadow-2xl animate-in fade-in zoom-in duration-200">
                                        <div className="flex justify-between items-center mb-4">
                                            <h3 className="text-lg font-bold text-gray-800 flex items-center gap-2">
                                                <MapPin className="text-red-500" /> Ubicación de la Comunidad
                                            </h3>
                                            <button onClick={() => setViewMapLocation(null)} className="text-gray-400 hover:text-gray-600">
                                                <X className="w-6 h-6" />
                                            </button>
                                        </div>
                                        <MapPicker value={viewMapLocation} isReadOnly={true} />
                                        <div className="text-center mt-4 pt-4 border-t border-gray-100">
                                            <span className="text-sm font-mono text-gray-500 bg-gray-100 px-3 py-1 rounded-full">{viewMapLocation}</span>
                                        </div>
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
