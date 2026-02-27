import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { Plus, Edit, Trash, MapPin, X, Image as ImageIcon } from 'lucide-react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';

export default function Communities({ auth, communities: initialCommunities }) {
    const [communities, setCommunities] = useState(initialCommunities || []);
    const [isModalOpen, setIsModalOpen] = useState(false);
    const [editingId, setEditingId] = useState(null);
    const [formData, setFormData] = useState({
        name: '',
        geolocation: '', // Mantenemos por compatibilidad o eliminamos si no se usa
        location_image: null
    });
    const [previewImage, setPreviewImage] = useState(null);
    const [importMessage, setImportMessage] = useState(null);
    const [viewImageLocation, setViewImageLocation] = useState(null);
    const fileInputRef = React.useRef(null);

    const fetchData = () => {
        axios.get('/communities').then(res => setCommunities(res.data));
    };

    const handleOpenModal = (community = null) => {
        if (community) {
            setEditingId(community.id);
            setFormData({
                name: community.name,
                geolocation: community.geolocation || '',
                percentage: community.percentage || '',
                location_image: null // Reset file input
            });
            // Mostrar imagen existente si la hay (Plan Nuclear: usar /media/)
            setPreviewImage(community.location_image_path ? `/media/${community.location_image_path}` : null);
        } else {
            setEditingId(null);
            setFormData({
                name: '',
                geolocation: '',
                percentage: '',
                location_image: null
            });
            setPreviewImage(null);
        }
        setIsModalOpen(true);
    };

    const handleCloseModal = () => {
        setIsModalOpen(false);
        setEditingId(null);
        setPreviewImage(null);
    };

    const handleChange = (e) => {
        const { name, value } = e.target;
        setFormData(prev => ({ ...prev, [name]: value }));
    };

    const handleImageChange = (e) => {
        const file = e.target.files[0];
        if (file) {
            setFormData(prev => ({ ...prev, location_image: file }));
            setPreviewImage(URL.createObjectURL(file));
        }
    };

    const handleSubmit = async (e) => {
        e.preventDefault();

        const data = new FormData();
        data.append('name', formData.name);
        if (formData.geolocation) data.append('geolocation', formData.geolocation);
        if (formData.percentage) data.append('percentage', formData.percentage);
        if (formData.location_image) data.append('location_image', formData.location_image);

        try {
            if (editingId) {
                // Laravel requiere _method: PUT para FormData en updates
                data.append('_method', 'PUT');
                await axios.post(`/communities/${editingId}`, data, {
                    headers: { 'Content-Type': 'multipart/form-data' }
                });
            } else {
                await axios.post('/communities', data, {
                    headers: { 'Content-Type': 'multipart/form-data' }
                });
            }
            fetchData();
            handleCloseModal();
        } catch (error) {
            console.error("Error saving community", error);
            // Mostrar error al usuario
        }
    };

    const handleDelete = async (id) => {
        if (confirm('¿Estás seguro de eliminar esta comunidad?')) {
            await axios.delete(`/communities/${id}`);
            fetchData();
        }
    };

    const handleDownloadTemplate = () => {
        window.location.href = '/communities/import/template';
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
            const response = await axios.post('/communities/import', formData, {
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
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Porcentaje</th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ubicación (Imagen)</th>
                                            <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody className="bg-white divide-y divide-gray-200">
                                        {communities.map((c) => (
                                            <tr key={c.id}>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{c.id}</td>
                                                <td className="px-6 py-4 whitespace-nowrap font-medium text-gray-900">{c.name}</td>
                                                <td className="px-6 py-4 whitespace-nowrap text-gray-900">{c.percentage ? `${c.percentage}%` : '0%'}</td>
                                                <td className="px-6 py-4 whitespace-nowrap text-gray-500">
                                                    {c.location_image_path ? (
                                                        <button
                                                            onClick={() => setViewImageLocation(`/media/${c.location_image_path}`)}
                                                            className="flex items-center text-blue-600 hover:text-blue-900"
                                                        >
                                                            <ImageIcon className="w-5 h-5 mr-1" />
                                                            Ver Croquis
                                                        </button>
                                                    ) : (
                                                        <span className="text-gray-400 text-xs italic">Sin imagen</span>
                                                    )}
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
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
                                    <div className="bg-white rounded-lg p-6 w-full max-w-md max-h-[90vh] overflow-y-auto">
                                        <h3 className="text-lg font-bold mb-4">{editingId ? 'Editar Comunidad' : 'Nueva Comunidad'}</h3>
                                        <form onSubmit={handleSubmit} className="space-y-4">
                                            <div>
                                                <label className="block text-sm font-medium text-gray-700">Nombre</label>
                                                <input type="text" name="name" value={formData.name} onChange={handleChange} className="mt-1 block w-full rounded-md border-gray-300 shadow-sm border p-2" required />
                                            </div>

                                            <div>
                                                <label className="block text-sm font-medium text-gray-700">Porcentaje de Comisión (%)</label>
                                                <input
                                                    type="number"
                                                    name="percentage"
                                                    value={formData.percentage || ''}
                                                    onChange={handleChange}
                                                    min="0"
                                                    max="100"
                                                    step="0.01"
                                                    placeholder="Ej. 15"
                                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm border p-2"
                                                />
                                                <p className="text-xs text-gray-500 mt-1">Si se deja vacío, se asumirá 0%.</p>
                                            </div>

                                            <div>
                                                <label className="block text-sm font-medium text-gray-700 mb-2">Imagen de Ubicación (Croquis)</label>

                                                {previewImage && (
                                                    <div className="mb-3 relative rounded-lg overflow-hidden border border-gray-200">
                                                        <img src={previewImage} alt="Vista previa" className="w-full h-48 object-cover" />
                                                        <button
                                                            type="button"
                                                            onClick={() => {
                                                                setFormData(prev => ({ ...prev, location_image: null }));
                                                                setPreviewImage(null);
                                                            }}
                                                            className="absolute top-1 right-1 bg-red-600 text-white p-1 rounded-full shadow-md hover:bg-red-700"
                                                        >
                                                            <X className="w-4 h-4" />
                                                        </button>
                                                    </div>
                                                )}

                                                <div className="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-blue-400 transition-colors cursor-pointer" onClick={() => document.getElementById('image-upload').click()}>
                                                    <div className="space-y-1 text-center">
                                                        <ImageIcon className="mx-auto h-12 w-12 text-gray-400" />
                                                        <div className="flex text-sm text-gray-600">
                                                            <label htmlFor="image-upload" className="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none">
                                                                <span>Sube un archivo</span>
                                                                <input id="image-upload" name="location_image" type="file" className="sr-only" accept="image/*" onChange={handleImageChange} />
                                                            </label>
                                                            <p className="pl-1">o arrástralo y suéltalo</p>
                                                        </div>
                                                        <p className="text-xs text-gray-500">PNG, JPG, GIF hasta 5MB</p>
                                                    </div>
                                                </div>
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
