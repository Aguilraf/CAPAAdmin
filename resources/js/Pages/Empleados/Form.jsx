import { useForm } from '@inertiajs/react';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import TextInput from '@/Components/TextInput';
import PrimaryButton from '@/Components/PrimaryButton';

export default function EmpleadoForm({ empleado, onSubmit, processing }) {
    const { data, setData, errors } = useForm({
        clave: empleado?.clave || '',
        nombre: empleado?.nombre || '',
        puesto: empleado?.puesto || '',
        departamento: empleado?.departamento || '',
        rfc: empleado?.rfc || '',
        categoria: empleado?.categoria || '',
        fecha_alta: empleado?.fecha_alta || '',
        salario_diario: empleado?.salario_diario || '',
        salario_mensual: empleado?.salario_mensual || '',
        curp: empleado?.curp || '',
        email: empleado?.email || '',
        telefono: empleado?.telefono || '',
        numero_empleado: empleado?.numero_empleado || '',
        activo: empleado?.activo ?? true,
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        onSubmit(data);
    };

    return (
        <form onSubmit={handleSubmit} className="space-y-6">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                {/* Clave */}
                <div>
                    <InputLabel htmlFor="clave" value="Clave *" />
                    <TextInput
                        id="clave"
                        type="text"
                        value={data.clave}
                        onChange={(e) => setData('clave', e.target.value)}
                        className="mt-1 block w-full"
                        required
                    />
                    <InputError message={errors.clave} className="mt-2" />
                </div>

                {/* Nombre */}
                <div>
                    <InputLabel htmlFor="nombre" value="Nombre Completo *" />
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

                {/* Puesto */}
                <div>
                    <InputLabel htmlFor="puesto" value="Puesto *" />
                    <TextInput
                        id="puesto"
                        type="text"
                        value={data.puesto}
                        onChange={(e) => setData('puesto', e.target.value)}
                        className="mt-1 block w-full"
                        required
                    />
                    <InputError message={errors.puesto} className="mt-2" />
                </div>

                {/* Departamento */}
                <div>
                    <InputLabel htmlFor="departamento" value="Departamento *" />
                    <TextInput
                        id="departamento"
                        type="text"
                        value={data.departamento}
                        onChange={(e) => setData('departamento', e.target.value)}
                        className="mt-1 block w-full"
                        required
                    />
                    <InputError message={errors.departamento} className="mt-2" />
                </div>

                {/* RFC */}
                <div>
                    <InputLabel htmlFor="rfc" value="RFC" />
                    <TextInput
                        id="rfc"
                        type="text"
                        value={data.rfc}
                        onChange={(e) => setData('rfc', e.target.value.toUpperCase())}
                        className="mt-1 block w-full"
                        maxLength={13}
                    />
                    <InputError message={errors.rfc} className="mt-2" />
                </div>

                {/* CURP */}
                <div>
                    <InputLabel htmlFor="curp" value="CURP" />
                    <TextInput
                        id="curp"
                        type="text"
                        value={data.curp}
                        onChange={(e) => setData('curp', e.target.value.toUpperCase())}
                        className="mt-1 block w-full"
                        maxLength={18}
                    />
                    <InputError message={errors.curp} className="mt-2" />
                </div>

                {/* Categoría */}
                <div>
                    <InputLabel htmlFor="categoria" value="Categoría" />
                    <TextInput
                        id="categoria"
                        type="text"
                        value={data.categoria}
                        onChange={(e) => setData('categoria', e.target.value)}
                        className="mt-1 block w-full"
                    />
                    <InputError message={errors.categoria} className="mt-2" />
                </div>

                {/* Número de Empleado */}
                <div>
                    <InputLabel htmlFor="numero_empleado" value="Número de Empleado" />
                    <TextInput
                        id="numero_empleado"
                        type="text"
                        value={data.numero_empleado}
                        onChange={(e) => setData('numero_empleado', e.target.value)}
                        className="mt-1 block w-full"
                    />
                    <InputError message={errors.numero_empleado} className="mt-2" />
                </div>

                {/* Fecha de Alta */}
                <div>
                    <InputLabel htmlFor="fecha_alta" value="Fecha de Alta" />
                    <TextInput
                        id="fecha_alta"
                        type="date"
                        value={data.fecha_alta}
                        onChange={(e) => setData('fecha_alta', e.target.value)}
                        className="mt-1 block w-full"
                    />
                    <InputError message={errors.fecha_alta} className="mt-2" />
                </div>

                {/* Salario Diario */}
                <div>
                    <InputLabel htmlFor="salario_diario" value="Salario Diario" />
                    <TextInput
                        id="salario_diario"
                        type="number"
                        step="0.01"
                        value={data.salario_diario}
                        onChange={(e) => setData('salario_diario', e.target.value)}
                        className="mt-1 block w-full"
                    />
                    <InputError message={errors.salario_diario} className="mt-2" />
                </div>

                {/* Salario Mensual */}
                <div>
                    <InputLabel htmlFor="salario_mensual" value="Salario Mensual" />
                    <TextInput
                        id="salario_mensual"
                        type="number"
                        step="0.01"
                        value={data.salario_mensual}
                        onChange={(e) => setData('salario_mensual', e.target.value)}
                        className="mt-1 block w-full"
                    />
                    <InputError message={errors.salario_mensual} className="mt-2" />
                </div>

                {/* Email */}
                <div>
                    <InputLabel htmlFor="email" value="Email" />
                    <TextInput
                        id="email"
                        type="email"
                        value={data.email}
                        onChange={(e) => setData('email', e.target.value)}
                        className="mt-1 block w-full"
                    />
                    <InputError message={errors.email} className="mt-2" />
                </div>

                {/* Teléfono */}
                <div>
                    <InputLabel htmlFor="telefono" value="Teléfono" />
                    <TextInput
                        id="telefono"
                        type="tel"
                        value={data.telefono}
                        onChange={(e) => setData('telefono', e.target.value)}
                        className="mt-1 block w-full"
                    />
                    <InputError message={errors.telefono} className="mt-2" />
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
                        <span className="ml-2 text-sm text-gray-600">Empleado Activo</span>
                    </label>
                </div>
            </div>

            <div className="flex items-center gap-4 pt-4">
                <PrimaryButton disabled={processing}>
                    {empleado ? 'Actualizar Empleado' : 'Crear Empleado'}
                </PrimaryButton>
            </div>
        </form>
    );
}
