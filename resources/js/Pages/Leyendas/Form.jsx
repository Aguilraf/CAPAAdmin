import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import TextInput from '@/Components/TextInput';
import PrimaryButton from '@/Components/PrimaryButton';

export default function LeyendaForm({ data, setData, errors, processing, submit, isEditing }) {
    return (
        <form onSubmit={submit} className="space-y-6">
            <div className="grid grid-cols-1 gap-6">
                {/* Año */}
                <div>
                    <InputLabel htmlFor="anio" value="Año *" />
                    <TextInput
                        id="anio"
                        type="number"
                        min="2000"
                        max="2100"
                        value={data.anio}
                        onChange={(e) => setData('anio', e.target.value)}
                        className="mt-1 block w-FULL md:w-1/4"
                        required
                    />
                    <InputError message={errors.anio} className="mt-2" />
                </div>

                {/* Texto */}
                <div>
                    <InputLabel htmlFor="texto" value="Texto de la Leyenda *" />
                    <textarea
                        id="texto"
                        value={data.texto}
                        onChange={(e) => setData('texto', e.target.value)}
                        className="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                        rows="4"
                        required
                    />
                    <InputError message={errors.texto} className="mt-2" />
                </div>

                {/* Estado Activa */}
                <div className="flex items-center">
                    <label className="flex items-center">
                        <input
                            type="checkbox"
                            checked={data.activa}
                            onChange={(e) => setData('activa', e.target.checked)}
                            className="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500"
                        />
                        <span className="ml-2 text-sm text-gray-600">
                            Leyenda Activa (Predeterminada)
                        </span>
                    </label>
                </div>
            </div>

            <div className="flex items-center gap-4 pt-4">
                <PrimaryButton disabled={processing}>
                    {isEditing ? 'Actualizar Leyenda' : 'Crear Leyenda'}
                </PrimaryButton>
            </div>
        </form>
    );
}
