import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { Plus, Edit, Trash, MapPin, X } from 'lucide-react';
import MapPicker from '../../Components/Firefighters/MapPicker';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';

export default function Communities({ auth, communities: initialCommunities }) {
    const [communities, setCommunities] = useState(initialCommunities || []);
    const [isModalOpen, setIsModalOpen] = useState(false);
    const [editingId, setEditingId] = useState(null);
    const [formData, setFormData] = useState({
        name: '',
        geolocation: ''
    });
    const [importMessage, setImportMessage] = useState(null);
    const [viewMapLocation, setViewMapLocation] = useState(null);
    const fileInputRef = React.useRef(null);

    const fetchData = () => {
        axios.get('/api/communities').then(res => setCommunities(res.data));
    };

    const handleOpenModal = (community = null) => {
        if (community) {
            setEditingId(community.id);
            setFormData({
                name: community.name,
                geolocation: community.geolocation || ''
            });
        } else {
            setEditingId(null);
            setFormData({
                name: '',
                geolocation: ''
            });
        }
        setIsModalOpen(true);
    };

    const handleCloseModal = () => {
        setIsModalOpen(false);
        setEditingId(null);
    };

    const handleChange = (e) => {
        const { name, value } = e.target;
        setFormData(prev => ({ ...prev, [name]: value }));
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        if (editingId) {
            await axios.put(`/api/communities/${editingId}`, formData);
        } else {
            await axios.post('/api/communities', formData);
        }
        fetchData();
        handleCloseModal();
    };

    const handleDelete = async (id) => {
        if (confirm('¿Estás seguro de eliminar esta comunidad?')) {
            await axios.delete(`/api/communities/${id}`);
            fetchData();
        }
    };

    const handleDownloadTemplate = () => {
        window.location.href = '/api/communities/import/template';
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
            const response = await axios.post('/api/communities/import', formData, {
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
            setImportMessage({
                type: 'error',
                text: 'Error al importar el archivo'
            });
        }
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Catálogo de Comunidades</h2>}
        >
            <Head title="Comunidades" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900">

                            <div className="flex justify-between items-center mb-6">
                                <h2 className="text-2xl font-bold text-gray-800">Catálogo de Comunidades</h2>
                                <div className="flex gap-2">
                                    <button
                                        onClick={handleDownloadTemplate}
                                        className="flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700"
                                    >
                                        Descargar Plantilla
                                    </button>
                                    <button
                                        onClick={handleImportClick}
                                        className="flex items-center px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700"
                                    >
                                        Importar CSV
                                    </button>
                                    <input
                                        ref={fileInputRef}
                                        type="file"
                                        accept=".csv"
                                        onChange={handleFileChange}
                                        className="hidden"
                                    />
                                    <button
                                        onClick={() => handleOpenModal()}
                                        className="flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                                    >
                                        <Plus className="w-5 h-5 mr-2" />
                                        Nueva Comunidad
                                    </button>
                                </div>
                            </div>

                            {importMessage && (
                                <div className={`p-4 mb-4 rounded ${importMessage.type === 'success' ? 'bg-green-100 text-green-700' :
                                    importMessage.type === 'warning' ? 'bg-yellow-100 text-yellow-700' :
                                        'bg-red-100 text-red-700'
                                    }`}>
                                    {importMessage.text}
                                </div>
                            )}

                            <div className="bg-white shadow rounded-lg overflow-hidden">
                                <table className="min-w-full divide-y divide-gray-200">
                                    <thead className="bg-gray-50">
                                        <tr>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Geolocalización</th>
                                            <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody className="bg-white divide-y divide-gray-200">
                                        {communities.map((c) => (
                                            <tr key={c.id}>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{c.id}</td>
                                                <td className="px-6 py-4 whitespace-nowrap font-medium text-gray-900">{c.name}</td>
                                                <td className="px-6 py-4 whitespace-nowrap text-gray-500">{c.geolocation}</td>
                                                <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                    <button onClick={() => setViewMapLocation(c.geolocation)} className="text-green-600 hover:text-green-900 mr-4" title="Ver ubicación">
                                                        <MapPin className="w-5 h-5" />
                                                    </button>
                                                    <button onClick={() => handleOpenModal(c)} className="text-indigo-600 hover:text-indigo-900 mr-4"><Edit className="w-5 h-5" /></button>
                                                    <button onClick={() => handleDelete(c.id)} className="text-red-600 hover:text-red-900"><Trash className="w-5 h-5" /></button>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>

                            {isModalOpen && (
                                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
                                    <div className="bg-white rounded-lg p-6 w-full max-w-md">
                                        <h3 className="text-lg font-bold mb-4">{editingId ? 'Editar Comunidad' : 'Nueva Comunidad'}</h3>
                                        <form onSubmit={handleSubmit} className="space-y-4">
                                            <div>
                                                <label className="block text-sm font-medium text-gray-700">Nombre</label>
                                                <input type="text" name="name" value={formData.name} onChange={handleChange} className="mt-1 block w-full rounded-md border-gray-300 shadow-sm border p-2" required />
                                            </div>
                                            <div>
                                                <label className="block text-sm font-medium text-gray-700">Ubicación de la Comunidad (Mapa)</label>
                                                <MapPicker
                                                    value={formData.geolocation}
                                                    onChange={(val) => setFormData({ ...formData, geolocation: val })}
                                                />
                                                <input
                                                    type="text"
                                                    name="geolocation"
                                                    value={formData.geolocation}
                                                    onChange={handleChange}
                                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm border p-1 text-xs text-gray-400 bg-gray-50"
                                                    placeholder="Lat, Lng"
                                                    readOnly
                                                />
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
