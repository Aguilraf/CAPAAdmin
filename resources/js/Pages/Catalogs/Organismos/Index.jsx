import React, { useState } from 'react';
import { router } from '@inertiajs/react'; // Import router
import { Plus, Edit, Trash2, X } from 'lucide-react'; // Use Trash2
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';

export default function Index({ auth, organismos }) { // Accept auth prop
    const [isModalOpen, setIsModalOpen] = useState(false);
    const [editingId, setEditingId] = useState(null);
    const [formData, setFormData] = useState({
        nombre: '',
        direccion: '',
        telefono: '',
        correo: '',
        ubicacion: '',
        foto: null
    });
    const [error, setError] = useState(null);

    // No need for fetchData or local organismos state - rely on Inertia props

    const handleOpenModal = (organismo = null) => {
        setError(null);
        if (organismo) {
            setEditingId(organismo.id);
            setFormData({
                nombre: organismo.nombre,
                direccion: organismo.direccion || '',
                telefono: organismo.telefono || '',
                correo: organismo.correo || '',
                ubicacion: organismo.ubicacion || '',
                foto: null // Don't preload file
            });
        } else {
            setEditingId(null);
            setFormData({
                nombre: '',
                direccion: '',
                telefono: '',
                correo: '',
                ubicacion: '',
                foto: null
            });
        }
        setIsModalOpen(true);
    };

    const handleCloseModal = () => {
        setIsModalOpen(false);
        setEditingId(null);
        setError(null);
    };

    const handleChange = (e) => {
        const { name, value, files } = e.target;
        if (name === 'foto') {
            setFormData(prev => ({ ...prev, [name]: files[0] }));
        } else {
            setFormData(prev => ({ ...prev, [name]: value }));
        }
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        setError(null);

        // Using router manually to handle FormData and file uploads easier while keeping modal open logic simple
        // Alternatively could use useForm() from inertia, but this refactor keeps the existing structure mostly intact

        const data = new FormData();
        data.append('nombre', formData.nombre);
        if (formData.direccion) data.append('direccion', formData.direccion);
        if (formData.telefono) data.append('telefono', formData.telefono);
        if (formData.correo) data.append('correo', formData.correo);
        if (formData.ubicacion) data.append('ubicacion', formData.ubicacion);
        if (formData.foto) data.append('foto', formData.foto);

        if (editingId) {
            data.append('_method', 'PUT'); // Method spoofing for FormData
            router.post(`/organismos/${editingId}`, data, {
                forceFormData: true,
                onSuccess: () => handleCloseModal(),
                onError: (errors) => {
                    console.error("Error saving", errors);
                    setError("Error al guardar. Verifique los datos.");
                }
            });
        } else {
            router.post('/organismos', data, {
                forceFormData: true,
                onSuccess: () => handleCloseModal(),
                onError: (errors) => {
                    console.error("Error saving", errors);
                    setError("Error al guardar. Verifique los datos.");
                }
            });
        }
    };

    const handleDelete = (id) => {
        if (confirm('¿Estás seguro de eliminar este organismo?')) {
            router.delete(route('organismos.destroy', id), {
                onError: (errors) => {
                    alert("Error al eliminar el organismo.");
                }
            });
        }
    };

    return (
        <AuthenticatedLayout
            user={auth.user} // Pass user prop
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Catálogo de Organismos</h2>}
        >
            <Head title="Organismos" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900">

                            <div className="flex justify-between items-center mb-6">
                                <h2 className="text-2xl font-bold text-gray-800">Catálogo de Organismos</h2>
                                <button
                                    onClick={() => handleOpenModal()}
                                    className="flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                                >
                                    <Plus className="w-5 h-5 mr-2" />
                                    Nuevo Organismo
                                </button>
                            </div>

                            <div className="bg-white shadow rounded-lg overflow-hidden">
                                <table className="min-w-full divide-y divide-gray-200">
                                    <thead className="bg-gray-50">
                                        <tr>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Correo</th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Teléfono</th>
                                            <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody className="bg-white divide-y divide-gray-200">
                                        {organismos.map((org) => (
                                            <tr key={org.id}>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{org.id}</td>
                                                <td className="px-6 py-4 whitespace-nowrap font-medium text-gray-900">
                                                    <div className="flex items-center">
                                                        {org.foto && (
                                                            <img src={`/media/${org.foto}`} alt={org.nombre} className="h-8 w-8 rounded-full mr-2 object-cover" />
                                                        )}
                                                        {org.nombre}
                                                    </div>
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{org.correo}</td>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{org.telefono}</td>
                                                <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                    <button onClick={() => handleOpenModal(org)} className="text-indigo-600 hover:text-indigo-900 mr-4"><Edit className="w-5 h-5" /></button>
                                                    <button onClick={() => handleDelete(org.id)} className="text-red-600 hover:text-red-900"><Trash2 className="w-5 h-5" /></button>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>

                            {isModalOpen && (
                                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
                                    <div className="bg-white rounded-lg p-6 w-full max-w-md max-h-[90vh] overflow-y-auto">
                                        <h3 className="text-lg font-bold mb-4">{editingId ? 'Editar Organismo' : 'Nuevo Organismo'}</h3>
                                        {error && <div className="bg-red-100 text-red-700 p-2 rounded mb-4">{error}</div>}
                                        <form onSubmit={handleSubmit} className="space-y-4">
                                            <div>
                                                <label className="block text-sm font-medium text-gray-700">Nombre</label>
                                                <input type="text" name="nombre" value={formData.nombre} onChange={handleChange} className="mt-1 block w-full rounded-md border-gray-300 shadow-sm border p-2" required />
                                            </div>
                                            <div>
                                                <label className="block text-sm font-medium text-gray-700">Dirección</label>
                                                <textarea name="direccion" value={formData.direccion} onChange={handleChange} className="mt-1 block w-full rounded-md border-gray-300 shadow-sm border p-2" rows="2" />
                                            </div>
                                            <div>
                                                <label className="block text-sm font-medium text-gray-700">Teléfono</label>
                                                <input type="text" name="telefono" value={formData.telefono} onChange={handleChange} className="mt-1 block w-full rounded-md border-gray-300 shadow-sm border p-2" />
                                            </div>
                                            <div>
                                                <label className="block text-sm font-medium text-gray-700">Correo Electrónico</label>
                                                <input type="email" name="correo" value={formData.correo} onChange={handleChange} className="mt-1 block w-full rounded-md border-gray-300 shadow-sm border p-2" />
                                            </div>
                                            <div>
                                                <label className="block text-sm font-medium text-gray-700">Ubicación (URL o Coordenadas)</label>
                                                <input type="text" name="ubicacion" value={formData.ubicacion} onChange={handleChange} className="mt-1 block w-full rounded-md border-gray-300 shadow-sm border p-2" />
                                            </div>
                                            <div>
                                                <label className="block text-sm font-medium text-gray-700">Logo / Foto</label>
                                                <input type="file" name="foto" onChange={handleChange} className="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" accept="image/*" />
                                            </div>

                                            <div className="flex justify-end space-x-3 mt-6">
                                                <button type="button" onClick={handleCloseModal} className="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">Cancelar</button>
                                                <button type="submit" className="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Guardar</button>
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
