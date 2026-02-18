import React from 'react';
import InputLabel from '@/Components/InputLabel';
import TextInput from '@/Components/TextInput';
import InputError from '@/Components/InputError';
import PrimaryButton from '@/Components/PrimaryButton';
import { Link } from '@inertiajs/react';
import Checkbox from '@/Components/Checkbox';

export default function Form({ data, setData, errors, processing, submitLabel, onSubmit, partidas, niveles }) {
    return (
        <form onSubmit={onSubmit} className="space-y-6">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                {/* Partida Presupuestal */}
                <div>
                    <InputLabel htmlFor="partida_id" value="Partida Presupuestal *" />
                    <select
                        id="partida_id"
                        value={data.partida_id || ''}
                        onChange={(e) => setData('partida_id', e.target.value)}
                        className="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full"
                        required
                    >
                        <option value="">Seleccione una partida...</option>
                        {partidas.map((partida) => (
                            <option key={partida.id} value={partida.id}>
                                {partida.codigo} - {partida.nombre}
                            </option>
                        ))}
                    </select>
                    <InputError message={errors.partida_id} className="mt-2" />
                </div>

                {/* Tipo */}
                <div>
                    <InputLabel htmlFor="rate_type" value="Tipo *" />
                    <select
                        id="rate_type"
                        value={data.rate_type || ''}
                        onChange={(e) => setData('rate_type', e.target.value)}
                        className="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full"
                        required
                    >
                        <option value="">Seleccione tipo...</option>
                        <option value="viaticos">Viáticos</option>
                        <option value="pasajes">Pasajes</option>
                        <option value="hospedaje">Hospedaje</option>
                    </select>
                    <InputError message={errors.rate_type} className="mt-2" />
                </div>

                {/* Cargo */}
                <div>
                    <InputLabel htmlFor="cargo" value="Cargo *" />
                    <TextInput
                        id="cargo"
                        type="text"
                        value={data.cargo || ''}
                        onChange={(e) => setData('cargo', e.target.value)}
                        className="mt-1 block w-full"
                        required
                    />
                    <InputError message={errors.cargo} className="mt-2" />
                </div>

                {/* Nivel */}
                <div>
                    <InputLabel htmlFor="nivel" value="Nivel *" />
                    {/* Render different inputs based on mode (creation implies array, edit implies string) */}
                    {Array.isArray(data.nivel) ? (
                        <div className="grid grid-cols-2 md:grid-cols-4 gap-2 mt-1 border border-gray-300 rounded-md p-2 max-h-60 overflow-y-auto">
                            {niveles && niveles.map((nivel, index) => (
                                <label key={index} className="flex items-center space-x-2 cursor-pointer hover:bg-gray-50 p-1 rounded">
                                    <Checkbox
                                        checked={data.nivel.includes(nivel)}
                                        onChange={(e) => {
                                            const isChecked = e.target.checked;
                                            const currentLevels = [...data.nivel];
                                            if (isChecked) {
                                                setData('nivel', [...currentLevels, nivel]);
                                            } else {
                                                setData('nivel', currentLevels.filter(n => n !== nivel));
                                            }
                                        }}
                                    />
                                    <span className="text-sm text-gray-700">{nivel}</span>
                                </label>
                            ))}
                        </div>
                    ) : (
                        <select
                            id="nivel"
                            value={data.nivel || ''}
                            onChange={(e) => setData('nivel', e.target.value)}
                            className="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full"
                            required
                        >
                            <option value="">Seleccione nivel...</option>
                            {niveles && niveles.map((nivel, index) => (
                                <option key={index} value={nivel}>
                                    {nivel}
                                </option>
                            ))}
                        </select>
                    )}
                    <span className="text-xs text-gray-500">
                        {Array.isArray(data.nivel) ? 'Selecciona uno o más niveles.' : ''}
                    </span>
                    <InputError message={errors.nivel} className="mt-2" />
                </div>

                {/* Zona I Amount */}
                <div>
                    <InputLabel htmlFor="zona_1_amount" value="Importe Zona I *" />
                    <TextInput
                        id="zona_1_amount"
                        type="number"
                        step="0.01"
                        min="0"
                        value={data.zona_1_amount || ''}
                        onChange={(e) => setData('zona_1_amount', e.target.value)}
                        className="mt-1 block w-full"
                        required
                    />
                    <InputError message={errors.zona_1_amount} className="mt-2" />
                </div>

                {/* Zona II Amount */}
                <div>
                    <InputLabel htmlFor="zona_2_amount" value="Importe Zona II *" />
                    <TextInput
                        id="zona_2_amount"
                        type="number"
                        step="0.01"
                        min="0"
                        value={data.zona_2_amount || ''}
                        onChange={(e) => setData('zona_2_amount', e.target.value)}
                        className="mt-1 block w-full"
                        required
                    />
                    <InputError message={errors.zona_2_amount} className="mt-2" />
                </div>

                {/* Year */}
                <div>
                    <InputLabel htmlFor="year" value="Año *" />
                    <TextInput
                        id="year"
                        type="number"
                        min="2020"
                        max="2100"
                        value={data.year || new Date().getFullYear()}
                        onChange={(e) => setData('year', e.target.value)}
                        className="mt-1 block w-full"
                        required
                    />
                    <InputError message={errors.year} className="mt-2" />
                </div>

                {/* Active */}
                <div className="flex items-center mt-6">
                    <input
                        id="active"
                        type="checkbox"
                        checked={data.active || false}
                        onChange={(e) => setData('active', e.target.checked)}
                        className="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                    />
                    <label htmlFor="active" className="ml-2 text-sm text-gray-700">
                        Activo
                    </label>
                    <InputError message={errors.active} className="mt-2" />
                </div>
            </div>

            <div className="flex items-center justify-end gap-4 mt-6">
                <Link
                    href={route('travel-allowance-rates.index')}
                    className="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                    Cancelar
                </Link>
                <PrimaryButton disabled={processing}>
                    {submitLabel}
                </PrimaryButton>
            </div>
        </form>
    );
}
