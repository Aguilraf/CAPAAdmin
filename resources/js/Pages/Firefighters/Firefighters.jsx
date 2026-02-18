import React, { useState, useEffect, useRef } from 'react';
import axios from 'axios';
import { Plus, Edit, Trash, MapPin, X, Image as ImageIcon, Download, Upload } from 'lucide-react';
import { formatCurrency } from '../../firefighters_helpers';
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
    const [viewImageLocation, setViewImageLocation] = useState(null);
    const [importMessage, setImportMessage] = useState(null);
    const fileInputRef = useRef(null);

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

    const handleDownloadTemplate = () => {
        window.location.href = '/firefighters/import/template';
    };

    const handleImportClick = () => {
        fileInputRef.current?.click();
    };

    const handleFileChange = async (e) => {
        const file = e.target.files?.[0];
        if (!file) return;

        const formData = new FormData();
        formData.append('file', file);

        try {
            const response = await axios.post('/firefighters/import', formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            });

            if (response.data.errors && response.data.errors.length > 0) {
                setImportMessage({
                    type: 'warning',
                    text: `${response.data.message}. Errores: ${response.data.errors.join(', ')}`
                });
            } else {
                setImportMessage({
                    type: 'success',
                    text: response.data.message
                });
            }

            fetchData();
            e.target.value = '';
        } catch (error) {
            console.error(error);
            setImportMessage({
                type: 'error',
                text: 'Error al importar el archivo. Verifique el formato.'
            });
            e.target.value = '';
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
                                <div className="flex gap-2">
                                    <button
                                        onClick={handleDownloadTemplate}
                                        className="flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors"
                                        title="Descargar plantilla CSV para importar"
                                    >
                                        <Download className="w-5 h-5 mr-2" />
                                        Bajar Layout
                                    </button>
                                    <button
                                        onClick={handleImportClick}
                                        className="flex items-center px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition-colors"
                                        title="Subir archivo CSV con bomberos"
                                    >
                                        <Upload className="w-5 h-5 mr-2" />
                                        Subir Bomberos
                                    </button>
                                    <input
                                        ref={fileInputRef}
                                        type="file"
                                        accept=".csv,.xlsx"
                                        onChange={handleFileChange}
                                        className="hidden"
                                    />
                                    <button
                                        onClick={() => handleOpenModal()}
                                        className="flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors"
                                    >
                                        <Plus className="w-5 h-5 mr-2" />
                                        Nuevo Bombero
                                    </button>
                                </div>
                            </div>

                            {importMessage && (
                                <div className={`p-4 mb-4 rounded flex justify-between items-center ${importMessage.type === 'success' ? 'bg-green-100 text-green-700' :
                                        importMessage.type === 'warning' ? 'bg-yellow-100 text-yellow-700' :
                                            'bg-red-100 text-red-700'
                                    }`}>
                                    <span>{importMessage.text}</span>
                                    <button onClick={() => setImportMessage(null)} className="ml-4 hover:opacity-75">
                                        <X className="w-4 h-4" />
                                    </button>
                                </div>
                            )}

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
                                                    {f.community?.location_image_path && (
                                                        <button
                                                            onClick={() => setViewImageLocation(`/media/${f.community.location_image_path}`)}
                                                            className="text-blue-600 hover:text-blue-900 mr-4"
                                                            title="Ver ubicación comunidad"
                                                        >
                                                            <ImageIcon className="w-5 h-5" />
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

                            {/* View Image Modal */}
                            {viewImageLocation && (
                                <div className="fixed inset-0 z-[60] flex items-center justify-center bg-black bg-opacity-70 p-4" onClick={() => setViewImageLocation(null)}>
                                    <div className="bg-white rounded-lg p-2 w-full max-w-3xl shadow-2xl relative" onClick={e => e.stopPropagation()}>
                                        <button onClick={() => setViewImageLocation(null)} className="absolute -top-10 right-0 text-white hover:text-gray-200">
                                            <X className="w-8 h-8" />
                                        </button>
                                        <img src={viewImageLocation} alt="Ubicación de la comunidad" className="w-full h-auto max-h-[80vh] object-contain rounded" />
                                        <div className="text-center mt-2">
                                            <a href={viewImageLocation} target="_blank" rel="noopener noreferrer" className="text-blue-600 text-sm hover:underline">Abrir original en nueva pestaña</a>
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
