import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm, router } from '@inertiajs/react';
import InputLabel from '@/Components/InputLabel';
import TextInput from '@/Components/TextInput';
import PrimaryButton from '@/Components/PrimaryButton';
import InputError from '@/Components/InputError';
import { useState } from 'react';

export default function Create({ materiales, materialesDefault = [], hasDefaults = false, manager, empleadoActual }) {
    const { data, setData, post, processing, errors } = useForm({
        fecha: new Date().toISOString().split('T')[0],
        destinatario_nombre: manager ? manager.nombre : '',
        destinatario_cargo: manager ? manager.puesto : '',

        // Items list - Initialize with default materials if available
        items: materialesDefault.length > 0
            ? materialesDefault.map(mat => ({
                material_id: mat.id,
                cantidad: mat.cantidad,
                custom_articulo: mat.articulo,
                custom_unidad: mat.unidad
            }))
            : [{ material_id: '', cantidad: '', custom_articulo: '', custom_unidad: '' }],

        // Solicitante from authenticated employee
        solicitante_nombre: empleadoActual ? empleadoActual.nombre : '',
        solicitante_cargo: empleadoActual ? empleadoActual.puesto : '',
        solicitante_departamento: empleadoActual ? empleadoActual.departamento : '',
    });

    const addItem = () => {
        setData('items', [
            ...data.items,
            { material_id: '', cantidad: '', custom_articulo: '', custom_unidad: '' }
        ]);
    };

    const removeItem = (index) => {
        const newItems = [...data.items];
        newItems.splice(index, 1);
        setData('items', newItems);
    };

    const updateItem = (index, field, value) => {
        const newItems = [...data.items];
        newItems[index][field] = value;

        // If material selected, auto-fill unit or name if needed (optional visual helper)
        if (field === 'material_id') {
            const selected = materiales.find(m => m.id == value);
            if (selected) {
                newItems[index]['custom_articulo'] = selected.articulo;
                newItems[index]['custom_unidad'] = selected.unidad;
            }
        }

        setData('items', newItems);
    };

    const submit = (e) => {
        e.preventDefault();

        // Use a standard form submission for file downloads to avoid Inertia handling the binary response
        // We'll construct a form defined data object and submit it via a temporary form or standard fetch/window.location if it was GET.
        // Since it is POST, we can't use window.open easily with complex data.
        // However, Inertia has a problem with downloads.
        // Best approach for POST download: Submit the form natively.

        if (!data.destinatario_nombre || !data.destinatario_cargo || !data.solicitante_nombre || !data.solicitante_cargo) {
            alert('Por favor, complete todos los campos de nombres y cargos (Solicitante y Destinatario).');
            return;
        }

        // Filter valid items
        const validItems = data.items.filter(item => item.material_id && item.cantidad > 0);

        if (validItems.length === 0) {
            alert('Debe agregar al menos un material válido con cantidad mayor a 0.');
            return;
        }

        // We can create a temporary form and submit it.
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = route('reportes.material-request.print');
        form.target = '_blank'; // Open in new tab? Or _self for download. _blank is safer for PDF.

        // CSRF Token
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (token) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = '_token';
            input.value = token;
            form.appendChild(input);
        }

        // Add all data fields
        // Simple fields
        ['fecha', 'destinatario_nombre', 'destinatario_cargo', 'solicitante_nombre', 'solicitante_cargo', 'solicitante_departamento'].forEach(key => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = data[key] || '';
            form.appendChild(input);
        });

        // Items array - complex structure
        validItems.forEach((item, index) => {
            Object.keys(item).forEach(key => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = `items[${index}][${key}]`;
                input.value = item[key] || '';
                form.appendChild(input);
            });
        });

        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
    };

    const [showDefaultsModal, setShowDefaultsModal] = useState(!hasDefaults);
    const [selectedDefaults, setSelectedDefaults] = useState([]);

    // Initialize selectedDefaults from materialesDefault if present
    const initDefaults = () => {
        if (materialesDefault.length > 0) {
            setSelectedDefaults(materialesDefault.map(m => m.id));
        }
    };
    // Call init on load/change
    // actually, better to handle state manually.

    const saveDefaults = () => {
        // Prepare items array for backend
        const itemsToSave = selectedDefaults.map(id => ({
            material_id: id,
            cantidad: 1 // Default quantity
        }));

        router.post(route('reportes.material-request.defaults'), {
            items: itemsToSave
        }, {
            preserveScroll: true,
            onSuccess: () => {
                setShowDefaultsModal(false);
            },
        });
    };

    const toggleDefaultSelection = (id) => {
        if (selectedDefaults.includes(id)) {
            setSelectedDefaults(selectedDefaults.filter(itemId => itemId !== id));
        } else {
            setSelectedDefaults([...selectedDefaults, id]);
        }
    };


    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Nueva Solicitud de Material
                </h2>
            }
        >
            <Head title="Solicitud de Material" />

            {/* Modal de Configuración Inicial */}
            {showDefaultsModal && (
                <div className="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                    <div className="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                        <div className="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
                        <span className="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                        <div className="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                            <div className="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                <div className="sm:flex sm:items-start">
                                    <div className="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100 sm:mx-0 sm:h-10 sm:w-10">
                                        <svg className="h-6 w-6 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                        </svg>
                                    </div>
                                    <div className="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                        <h3 className="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                            Configura tus Materiales Favoritos
                                        </h3>
                                        <div className="mt-2">
                                            <p className="text-sm text-gray-500 mb-4">
                                                Selecciona los materiales que solicitas frecuentemente para que aparezcan automáticamente en tus futuros reportes.
                                            </p>
                                            <div className="max-h-60 overflow-y-auto border rounded-md p-2">
                                                {materiales.map(m => (
                                                    <div key={m.id} className="flex items-center py-2 border-b last:border-0 hover:bg-gray-50 px-2 cursor-pointer" onClick={() => toggleDefaultSelection(m.id)}>
                                                        <input
                                                            type="checkbox"
                                                            checked={selectedDefaults.includes(m.id)}
                                                            onChange={() => { }}
                                                            className="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                                        />
                                                        <span className="ml-3 text-sm text-gray-700">{m.articulo} <span className="text-xs text-gray-500">({m.unidad})</span></span>
                                                    </div>
                                                ))}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div className="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                <button
                                    type="button"
                                    className="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm"
                                    onClick={saveDefaults}
                                >
                                    Guardar Favoritos
                                </button>
                                <button
                                    type="button"
                                    className="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                                    onClick={() => setShowDefaultsModal(false)}
                                >
                                    Omitir
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            )}

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="bg-white p-6 shadow-sm sm:rounded-lg">
                        <div className="flex justify-between mb-4">
                            <div></div>
                            <button
                                type="button"
                                onClick={() => {
                                    // Pre-select current defaults
                                    if (materialesDefault.length > 0) {
                                        setSelectedDefaults(materialesDefault.map(m => m.id));
                                    } else {
                                        setSelectedDefaults([]);
                                    }
                                    setShowDefaultsModal(true);
                                }}
                                className="text-indigo-600 hover:text-indigo-800 text-sm font-medium flex items-center"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                Editar Mis Favoritos
                            </button>
                        </div>

                        <form onSubmit={submit}>
                            {/* Cabecera del Documento */}
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8 border-b pb-6">
                                <div>
                                    <InputLabel value="Fecha del Documento" />
                                    <TextInput
                                        type="date"
                                        className="mt-1 block w-full"
                                        value={data.fecha}
                                        onChange={(e) => setData('fecha', e.target.value)}
                                    />
                                    <InputError message={errors.fecha} className="mt-2" />
                                </div>
                            </div>

                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8 border-b pb-6">
                                <div>
                                    <h4 className="font-medium text-gray-700 mb-4">Destinatario (Gerente)</h4>
                                    {!manager && (
                                        <div className="mb-4 p-3 bg-yellow-50 border border-yellow-200 rounded-md">
                                            ⚠️ No hay gerente activo configurado. Puede ingresar el nombre manualmente.
                                        </div>
                                    )}
                                    <div className="mb-4">
                                        <InputLabel value="Nombre" />
                                        <TextInput
                                            className="mt-1 block w-full uppercase"
                                            value={data.destinatario_nombre}
                                            onChange={(e) => setData('destinatario_nombre', e.target.value)}
                                        />
                                        <InputError message={errors.destinatario_nombre} className="mt-2" />
                                    </div>
                                    <div>
                                        <InputLabel value="Cargo" />
                                        <TextInput
                                            className="mt-1 block w-full uppercase"
                                            value={data.destinatario_cargo}
                                            onChange={(e) => setData('destinatario_cargo', e.target.value)}
                                        />
                                        <InputError message={errors.destinatario_cargo} className="mt-2" />
                                    </div>
                                </div>

                                <div>
                                    <h4 className="font-medium text-gray-700 mb-4">Solicitante (Firma)</h4>
                                    <div className="mb-4">
                                        <InputLabel value="Nombre" />
                                        <TextInput
                                            className="mt-1 block w-full uppercase"
                                            value={data.solicitante_nombre}
                                            onChange={(e) => setData('solicitante_nombre', e.target.value)}
                                        />
                                    </div>
                                    <div>
                                        <InputLabel value="Cargo" />
                                        <TextInput
                                            className="mt-1 block w-full uppercase"
                                            value={data.solicitante_cargo}
                                            onChange={(e) => setData('solicitante_cargo', e.target.value)}
                                        />
                                    </div>
                                </div>
                            </div>

                            {/* Lista de Materiales */}
                            <div className="mb-8">
                                <div className="flex justify-between items-center mb-4">
                                    <h4 className="text-lg font-medium text-gray-900">Materiales a Solicitar</h4>
                                    <PrimaryButton type="button" onClick={addItem} className="bg-green-600 hover:bg-green-700">
                                        + Agregar Fila
                                    </PrimaryButton>
                                </div>

                                <div className="border rounded-lg overflow-hidden">
                                    <table className="min-w-full divide-y divide-gray-200">
                                        <thead className="bg-gray-50">
                                            <tr>
                                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Articulo</th>
                                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-32">Cantidad</th>
                                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unidad</th>
                                                <th className="px-6 py-3 text-right"></th>
                                            </tr>
                                        </thead>
                                        <tbody className="bg-white divide-y divide-gray-200">
                                            {data.items.map((item, index) => (
                                                <tr key={index}>
                                                    <td className="px-4 py-2">
                                                        <select
                                                            className="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full"
                                                            value={item.material_id}
                                                            onChange={(e) => updateItem(index, 'material_id', e.target.value)}
                                                        >
                                                            <option value="">-- Seleccionar Material --</option>
                                                            {materiales.map(m => (
                                                                <option key={m.id} value={m.id}>{m.articulo}</option>
                                                            ))}
                                                        </select>
                                                        {errors[`items.${index}.material_id`] &&
                                                            <div className="text-red-600 text-xs mt-1">{errors[`items.${index}.material_id`]}</div>
                                                        }
                                                    </td>
                                                    <td className="px-4 py-2">
                                                        <TextInput
                                                            type="number"
                                                            className="w-full"
                                                            value={item.cantidad}
                                                            onChange={(e) => updateItem(index, 'cantidad', e.target.value)}
                                                        />
                                                        {errors[`items.${index}.cantidad`] &&
                                                            <div className="text-red-600 text-xs mt-1">{errors[`items.${index}.cantidad`]}</div>
                                                        }
                                                    </td>
                                                    <td className="px-4 py-2 text-sm text-gray-600">
                                                        {item.custom_unidad || '-'}
                                                    </td>
                                                    <td className="px-4 py-2 text-right">
                                                        {data.items.length > 1 && (
                                                            <button
                                                                type="button"
                                                                onClick={() => removeItem(index)}
                                                                className="text-red-600 hover:text-red-900 font-bold"
                                                            >
                                                                X
                                                            </button>
                                                        )}
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                                <InputError message={errors.items} className="mt-2" />
                            </div>

                            <div className="flex justify-end">
                                <PrimaryButton disabled={processing}>
                                    Generar Reporte (Vista Previa)
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
