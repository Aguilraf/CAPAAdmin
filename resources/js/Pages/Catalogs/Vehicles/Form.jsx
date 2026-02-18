
import React from 'react';
import InputLabel from '@/Components/InputLabel';
import TextInput from '@/Components/TextInput';
import InputError from '@/Components/InputError';
import PrimaryButton from '@/Components/PrimaryButton';
import { Link } from '@inertiajs/react';

export default function Form({ data, setData, errors, processing, submitLabel, onSubmit, organismos }) {

    // Handle file input change specifically
    const handleFileChange = (e) => {
        setData('photo', e.target.files[0]);
    };

    return (
        <form onSubmit={onSubmit} className="space-y-6" encType="multipart/form-data">

            {/* Main Info Section */}
            <div className="bg-gray-50 p-4 rounded-lg border border-gray-200">
                <h3 className="text-lg font-medium text-gray-900 mb-4 border-b pb-2">Información General</h3>
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    {/* Organismo */}
                    <div>
                        <InputLabel htmlFor="organismo_id" value="Organismo Assignado" />
                        <select
                            id="organismo_id"
                            value={data.organismo_id || ''}
                            onChange={(e) => setData('organismo_id', e.target.value)}
                            className="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full"
                        >
                            <option value="">Seleccione un organismo...</option>
                            {organismos.map((org) => (
                                <option key={org.id} value={org.id}>{org.nombre}</option>
                            ))}
                        </select>
                        <InputError message={errors.organismo_id} className="mt-2" />
                    </div>

                    {/* Inventory Number */}
                    <div>
                        <InputLabel htmlFor="inventory_number" value="Inventario *" />
                        <TextInput
                            id="inventory_number"
                            type="text"
                            value={data.inventory_number || ''}
                            onChange={(e) => setData('inventory_number', e.target.value)}
                            className="mt-1 block w-full"
                            required
                        />
                        <InputError message={errors.inventory_number} className="mt-2" />
                    </div>

                    {/* Unit Type (UNIDAD) */}
                    <div>
                        <InputLabel htmlFor="unit_type" value="Unidad (Ej. Camioneta) *" />
                        <TextInput
                            id="unit_type"
                            type="text"
                            value={data.unit_type || ''}
                            onChange={(e) => setData('unit_type', e.target.value)}
                            className="mt-1 block w-full"
                            required
                        />
                        <InputError message={errors.unit_type} className="mt-2" />
                    </div>

                    {/* Brand (MARCA) */}
                    <div>
                        <InputLabel htmlFor="brand" value="Marca *" />
                        <TextInput
                            id="brand"
                            type="text"
                            value={data.brand || ''}
                            onChange={(e) => setData('brand', e.target.value)}
                            className="mt-1 block w-full"
                            required
                        />
                        <InputError message={errors.brand} className="mt-2" />
                    </div>

                    {/* Vehicle Type (TIPO) */}
                    <div>
                        <InputLabel htmlFor="vehicle_type" value="Tipo (Ej. Estacas) *" />
                        <TextInput
                            id="vehicle_type"
                            type="text"
                            value={data.vehicle_type || ''}
                            onChange={(e) => setData('vehicle_type', e.target.value)}
                            className="mt-1 block w-full"
                            required
                        />
                        <InputError message={errors.vehicle_type} className="mt-2" />
                    </div>

                    {/* Color */}
                    <div>
                        <InputLabel htmlFor="color" value="Color" />
                        <TextInput
                            id="color"
                            type="text"
                            value={data.color || ''}
                            onChange={(e) => setData('color', e.target.value)}
                            className="mt-1 block w-full"
                        />
                        <InputError message={errors.color} className="mt-2" />
                    </div>

                    {/* Model Year (MOD) */}
                    <div>
                        <InputLabel htmlFor="model_year" value="Modelo (Año) *" />
                        <TextInput
                            id="model_year"
                            type="text"
                            value={data.model_year || ''}
                            onChange={(e) => setData('model_year', e.target.value)}
                            className="mt-1 block w-full"
                            required
                        />
                        <InputError message={errors.model_year} className="mt-2" />
                    </div>
                </div>
            </div>

            {/* Technical Details Section */}
            <div className="bg-gray-50 p-4 rounded-lg border border-gray-200">
                <h3 className="text-lg font-medium text-gray-900 mb-4 border-b pb-2">Detalles Técnicos y Legales</h3>
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    {/* Serial Number */}
                    <div>
                        <InputLabel htmlFor="serial_number" value="No. de Serie *" />
                        <TextInput
                            id="serial_number"
                            type="text"
                            value={data.serial_number || ''}
                            onChange={(e) => setData('serial_number', e.target.value)}
                            className="mt-1 block w-full uppercase"
                            required
                        />
                        <InputError message={errors.serial_number} className="mt-2" />
                    </div>

                    {/* Engine Number */}
                    <div>
                        <InputLabel htmlFor="engine_number" value="No. de Motor" />
                        <TextInput
                            id="engine_number"
                            type="text"
                            value={data.engine_number || ''}
                            onChange={(e) => setData('engine_number', e.target.value)}
                            className="mt-1 block w-full uppercase"
                        />
                        <InputError message={errors.engine_number} className="mt-2" />
                    </div>

                    {/* Plate Number */}
                    <div>
                        <InputLabel htmlFor="plate_number" value="Placa Actual" />
                        <TextInput
                            id="plate_number"
                            type="text"
                            value={data.plate_number || ''}
                            onChange={(e) => setData('plate_number', e.target.value)}
                            className="mt-1 block w-full uppercase"
                        />
                        <InputError message={errors.plate_number} className="mt-2" />
                    </div>

                    {/* Invoice Number */}
                    <div>
                        <InputLabel htmlFor="invoice_number" value="Factura" />
                        <TextInput
                            id="invoice_number"
                            type="text"
                            value={data.invoice_number || ''}
                            onChange={(e) => setData('invoice_number', e.target.value)}
                            className="mt-1 block w-full"
                        />
                        <InputError message={errors.invoice_number} className="mt-2" />
                    </div>

                    {/* Supplier */}
                    <div>
                        <InputLabel htmlFor="supplier" value="Proveedor" />
                        <TextInput
                            id="supplier"
                            type="text"
                            value={data.supplier || ''}
                            onChange={(e) => setData('supplier', e.target.value)}
                            className="mt-1 block w-full"
                        />
                        <InputError message={errors.supplier} className="mt-2" />
                    </div>

                    {/* Policy Number */}
                    <div>
                        <InputLabel htmlFor="policy_number" value="Póliza" />
                        <TextInput
                            id="policy_number"
                            type="text"
                            value={data.policy_number || ''}
                            onChange={(e) => setData('policy_number', e.target.value)}
                            className="mt-1 block w-full"
                        />
                        <InputError message={errors.policy_number} className="mt-2" />
                    </div>
                </div>
            </div>

            {/* Assignment Section */}
            <div className="bg-gray-50 p-4 rounded-lg border border-gray-200">
                <h3 className="text-lg font-medium text-gray-900 mb-4 border-b pb-2">Ubicación y Resguardo</h3>
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    {/* Area */}
                    <div>
                        <InputLabel htmlFor="area" value="Área" />
                        <TextInput
                            id="area"
                            type="text"
                            value={data.area || ''}
                            onChange={(e) => setData('area', e.target.value)}
                            className="mt-1 block w-full"
                        />
                        <InputError message={errors.area} className="mt-2" />
                    </div>

                    {/* Location (UBICACION) */}
                    <div>
                        <InputLabel htmlFor="location" value="Ubicación" />
                        <TextInput
                            id="location"
                            type="text"
                            value={data.location || ''}
                            onChange={(e) => setData('location', e.target.value)}
                            className="mt-1 block w-full"
                        />
                        <InputError message={errors.location} className="mt-2" />
                    </div>

                    {/* Sub Location (LUGAR) */}
                    <div>
                        <InputLabel htmlFor="sub_location" value="Lugar (Sub-ubicación)" />
                        <TextInput
                            id="sub_location"
                            type="text"
                            value={data.sub_location || ''}
                            onChange={(e) => setData('sub_location', e.target.value)}
                            className="mt-1 block w-full"
                        />
                        <InputError message={errors.sub_location} className="mt-2" />
                    </div>

                    {/* Custodian (RESGUARDANTE) */}
                    <div className="md:col-span-2">
                        <InputLabel htmlFor="custodian" value="Resguardante" />
                        <TextInput
                            id="custodian"
                            type="text"
                            value={data.custodian || ''}
                            onChange={(e) => setData('custodian', e.target.value)}
                            className="mt-1 block w-full"
                        />
                        <InputError message={errors.custodian} className="mt-2" />
                    </div>
                </div>
            </div>

            {/* Photo & Active Section */}
            <div className="bg-gray-50 p-4 rounded-lg border border-gray-200">
                <h3 className="text-lg font-medium text-gray-900 mb-4 border-b pb-2">Fotografía y Estado</h3>
                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {/* Photo Upload */}
                    <div>
                        <InputLabel htmlFor="photo" value="Fotografía del Vehículo" />
                        <input
                            id="photo"
                            type="file"
                            onChange={handleFileChange}
                            accept="image/*"
                            className="mt-1 block w-full text-sm text-gray-500
                                file:mr-4 file:py-2 file:px-4
                                file:rounded-full file:border-0
                                file:text-sm file:font-semibold
                                file:bg-indigo-50 file:text-indigo-700
                                hover:file:bg-indigo-100"
                        />
                        <InputError message={errors.photo} className="mt-2" />
                        <p className="mt-1 text-xs text-gray-500">JPG, PNG hasta 2MB.</p>
                    </div>

                    {/* Active Checkbox */}
                    <div className="flex items-center justify-start md:justify-center mt-6">
                        <label className="flex items-center">
                            <input
                                type="checkbox"
                                checked={data.active}
                                onChange={(e) => setData('active', e.target.checked)}
                                className="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 h-5 w-5"
                            />
                            <span className="ml-2 text-sm text-gray-700 font-medium">Vehículo Activo</span>
                        </label>
                        <InputError message={errors.active} className="mt-2" />
                    </div>
                </div>
            </div>

            {/* Actions */}
            <div className="flex items-center justify-end gap-4 mt-6">
                <Link
                    href={route('vehicles.index')}
                    className="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:bg-gray-300 active:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                    Cancelar
                </Link>
                <PrimaryButton disabled={processing} className="w-full md:w-auto justify-center">
                    {submitLabel}
                </PrimaryButton>
            </div>
        </form>
    );
}
