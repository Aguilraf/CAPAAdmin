import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import TextInput from '@/Components/TextInput';
import PrimaryButton from '@/Components/PrimaryButton';
import TextArea from '@/Components/TextArea'; // Assuming you might have one, or use TextInput as textarea if adjusted, but sticking to basics first.
// Actually standard TextInput usually is type="text", let's use TextInput or a standard textarea if needed.
// Checking EmpleadoForm, it uses standard components. I'll stick to that.

export default function CapituloForm({ data, setData, errors, processing, submit, isEditing }) {
    return (
        <form onSubmit={submit} className="space-y-6">
            <div className="grid grid-cols-1 gap-6">
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
                        autoFocus
                    />
                    <InputError message={errors.codigo} className="mt-2" />
                </div>

                {/* Nombre */}
                <div>
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
                <div>
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
                <div className="flex items-center">
                    <label className="flex items-center">
                        <input
                            type="checkbox"
                            checked={data.activo}
                            onChange={(e) => setData('activo', e.target.checked)}
                            className="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500"
                        />
                        <span className="ml-2 text-sm text-gray-600">Capítulo Activo</span>
                    </label>
                </div>
            </div>

            <div className="flex items-center gap-4 pt-4">
                <PrimaryButton disabled={processing}>
                    {isEditing ? 'Actualizar Capítulo' : 'Crear Capítulo'}
                </PrimaryButton>
            </div>
        </form>
    );
}
