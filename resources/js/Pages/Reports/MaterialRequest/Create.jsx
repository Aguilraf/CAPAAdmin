import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';
import InputLabel from '@/Components/InputLabel';
import TextInput from '@/Components/TextInput';
import PrimaryButton from '@/Components/PrimaryButton';
import InputError from '@/Components/InputError';
import { useState } from 'react';

export default function Create({ materiales, materialesDefault = [], manager, empleadoActual }) {
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
        data.items.forEach((item, index) => {
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

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Nueva Solicitud de Material
                </h2>
            }
        >
            <Head title="Solicitud de Material" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="bg-white p-6 shadow-sm sm:rounded-lg">
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
