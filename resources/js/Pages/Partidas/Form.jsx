import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import TextInput from '@/Components/TextInput';
import PrimaryButton from '@/Components/PrimaryButton';

export default function PartidaForm({ data, setData, errors, processing, submit, isEditing, capitulos }) {
    return (
        <form onSubmit={submit} className="space-y-6">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                {/* Capítulo */}
                <div>
                    <InputLabel htmlFor="capitulo_id" value="Capítulo *" />
                    <select
                        id="capitulo_id"
                        value={data.capitulo_id}
                        onChange={(e) => setData('capitulo_id', e.target.value)}
                        className="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                        required
                    >
                        <option value="">Seleccione un Capítulo</option>
                        {capitulos.map((capitulo) => (
                            <option key={capitulo.id} value={capitulo.id}>
                                {capitulo.codigo} - {capitulo.nombre}
                            </option>
                        ))}
                    </select>
                    <InputError message={errors.capitulo_id} className="mt-2" />
                </div>

                {/* Código */}
                <div>
                    <InputLabel htmlFor="codigo" value="Código *" />
                    <TextInput
                        id="codigo"
                        type="text"
                        value={data.codigo}
                        onChange={(e) => setData('codigo', e.target.value)}
                        className="mt-1 block w-full"
                        required
                    />
                    <InputError message={errors.codigo} className="mt-2" />
                </div>

                {/* Nombre */}
                <div className="md:col-span-2">
                    <InputLabel htmlFor="nombre" value="Nombre *" />
                    <TextInput
                        id="nombre"
                        type="text"
                        value={data.nombre}
                        onChange={(e) => setData('nombre', e.target.value)}
                        className="mt-1 block w-full"
                        required
                    />
                    <InputError message={errors.nombre} className="mt-2" />
                </div>

                {/* Descripción */}
                <div className="md:col-span-2">
                    <InputLabel htmlFor="descripcion" value="Descripción" />
                    <textarea
                        id="descripcion"
                        value={data.descripcion}
                        onChange={(e) => setData('descripcion', e.target.value)}
                        className="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                        rows="3"
                    />
                    <InputError message={errors.descripcion} className="mt-2" />
                </div>

                {/* Estado Activo */}
                <div className="flex items-center md:col-span-2">
                    <label className="flex items-center">
                        <input
                            type="checkbox"
                            checked={data.activo}
                            onChange={(e) => setData('activo', e.target.checked)}
                            className="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500"
                        />
                        <span className="ml-2 text-sm text-gray-600">Partida Activa</span>
                    </label>
                </div>
            </div>

            <div className="flex items-center gap-4 pt-4">
                <PrimaryButton disabled={processing}>
                    {isEditing ? 'Actualizar Partida' : 'Crear Partida'}
                </PrimaryButton>
            </div>
        </form>
    );
}
