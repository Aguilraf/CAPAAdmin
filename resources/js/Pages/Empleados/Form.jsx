import { useForm } from '@inertiajs/react';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import TextInput from '@/Components/TextInput';
import PrimaryButton from '@/Components/PrimaryButton';
import { useEffect } from 'react';

export default function EmpleadoForm({ empleado, submitUrl, submitMethod = 'post', posiblesJefes = [] }) {
    const { data, setData, post, put, processing, errors } = useForm({
        clave: empleado?.clave || '',
        nombre: empleado?.nombre || '',
        puesto: empleado?.puesto || '',
        cargo: empleado?.cargo || '',
        departamento: empleado?.departamento || '',
        area_adscripcion: empleado?.area_adscripcion || '',
        rfc: empleado?.rfc || '',
        categoria: empleado?.categoria || 'BASE',
        fecha_alta: (empleado?.fecha_alta || '').split('T')[0],
        fecha_nacimiento: (empleado?.fecha_nacimiento || '').split('T')[0],
        sexo: empleado?.sexo || 'H',
        nivel: empleado?.nivel || '',
        salario_diario: empleado?.salario_diario || '',
        salario_mensual: empleado?.salario_mensual || '',
        curp: empleado?.curp || '',
        nss: empleado?.nss || '',
        afiliacion: empleado?.afiliacion || '',
        email: empleado?.email || '',
        telefono: empleado?.telefono || '',
        numero_empleado: empleado?.numero_empleado || '',
        activo: empleado?.activo ?? true,
        es_gerente: empleado?.es_gerente ?? false,
        jefe_inmediato: empleado?.jefe_inmediato || '',
        primer_nombre: empleado?.primer_nombre || '',
        primer_apellido: empleado?.primer_apellido || '',
        segundo_apellido: empleado?.segundo_apellido || '',
        banco: empleado?.banco || '',
        clabe: empleado?.clabe || '',
    });

    // Auto-fill Birth Date and Sex from CURP
    useEffect(() => {
        const curp = data.curp;
        if (curp && curp.length === 18) {
            // Extract Date: YYMMDD (Positions 4-9, index 4-9) -> substring(4, 10)
            const yy = curp.substring(4, 6);
            const mm = curp.substring(6, 8);
            const dd = curp.substring(8, 10);

            // Extract Sex: Position 11 (index 10)
            const sexChar = curp.charAt(10).toUpperCase();

            // Determine Century using Homoclave (Position 17, index 16)
            // RENAPO Rule: 
            // - If 0-9 (Numeric) -> Born in 1900s (19xx)
            // - If A-Z (Letter)  -> Born in 2000s (20xx)
            const homoclave = curp.charAt(16);
            const is1900s = /^[0-9]$/.test(homoclave);
            const century = is1900s ? '19' : '20';

            const birthDate = `${century}${yy}-${mm}-${dd}`;

            // Only update if current values are empty or different to avoid overwrite loop/user conflict (though this is desired automation)
            // We'll update indiscriminately when a valid CURP is fully entered. 
            // Better UX: Update only if it looks like a new entry or user explicitly wants it. 
            // Given the requirement "cada que se ponga la CURP... se llenen", we will overwrite.

            let updates = {};
            if (data.fecha_nacimiento !== birthDate) updates.fecha_nacimiento = birthDate;

            let sexoMap = { 'H': 'H', 'M': 'M' };
            if (sexoMap[sexChar] && data.sexo !== sexoMap[sexChar]) {
                updates.sexo = sexoMap[sexChar];
            }

            if (Object.keys(updates).length > 0) {
                setData(prev => ({ ...prev, ...updates }));
            }
        }
    }, [data.curp]);

    const handleSubmit = (e) => {
        e.preventDefault();

        if (submitMethod === 'put') {
            put(submitUrl);
        } else {
            post(submitUrl);
        }
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

                {/* Nombre Completo */}
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

                {/* Primer Nombre */}
                <div>
                    <InputLabel htmlFor="primer_nombre" value="Nombre(s)" />
                    <TextInput
                        id="primer_nombre"
                        type="text"
                        value={data.primer_nombre}
                        onChange={(e) => setData('primer_nombre', e.target.value)}
                        className="mt-1 block w-full"
                    />
                    <InputError message={errors.primer_nombre} className="mt-2" />
                </div>

                {/* Primer Apellido */}
                <div>
                    <InputLabel htmlFor="primer_apellido" value="Primer Apellido" />
                    <TextInput
                        id="primer_apellido"
                        type="text"
                        value={data.primer_apellido}
                        onChange={(e) => setData('primer_apellido', e.target.value)}
                        className="mt-1 block w-full"
                    />
                    <InputError message={errors.primer_apellido} className="mt-2" />
                </div>

                {/* Segundo Apellido */}
                <div>
                    <InputLabel htmlFor="segundo_apellido" value="Segundo Apellido" />
                    <TextInput
                        id="segundo_apellido"
                        type="text"
                        value={data.segundo_apellido}
                        onChange={(e) => setData('segundo_apellido', e.target.value)}
                        className="mt-1 block w-full"
                    />
                    <InputError message={errors.segundo_apellido} className="mt-2" />
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

                {/* Cargo */}
                <div>
                    <InputLabel htmlFor="cargo" value="Cargo" />
                    <TextInput
                        id="cargo"
                        type="text"
                        value={data.cargo}
                        onChange={(e) => setData('cargo', e.target.value)}
                        className="mt-1 block w-full"
                    />
                    <InputError message={errors.cargo} className="mt-2" />
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

                {/* Área de Adscripción */}
                <div>
                    <InputLabel htmlFor="area_adscripcion" value="Área de Adscripción" />
                    <TextInput
                        id="area_adscripcion"
                        type="text"
                        value={data.area_adscripcion}
                        onChange={(e) => setData('area_adscripcion', e.target.value)}
                        className="mt-1 block w-full"
                    />
                    <InputError message={errors.area_adscripcion} className="mt-2" />
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

                {/* Banco */}
                <div>
                    <InputLabel htmlFor="banco" value="Banco" />
                    <TextInput
                        id="banco"
                        type="text"
                        value={data.banco}
                        onChange={(e) => setData('banco', e.target.value)}
                        className="mt-1 block w-full"
                    />
                    <InputError message={errors.banco} className="mt-2" />
                </div>

                {/* CLABE */}
                <div>
                    <InputLabel htmlFor="clabe" value="CLABE" />
                    <TextInput
                        id="clabe"
                        type="text"
                        value={data.clabe}
                        onChange={(e) => setData('clabe', e.target.value)}
                        className="mt-1 block w-full"
                        maxLength={18}
                    />
                    <InputError message={errors.clabe} className="mt-2" />
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

                {/* Tipo de Plaza (Categoría) */}
                <div>
                    <InputLabel htmlFor="categoria" value="Tipo de Plaza *" />
                    <select
                        id="categoria"
                        value={data.categoria}
                        onChange={(e) => setData('categoria', e.target.value)}
                        className="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                        required
                    >
                        <option value="BASE">BASE</option>
                        <option value="CONFIANZA">CONFIANZA</option>
                    </select>
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
                    <InputLabel htmlFor="fecha_alta" value="Fecha de Alta *" />
                    <TextInput
                        id="fecha_alta"
                        type="date"
                        value={data.fecha_alta}
                        onChange={(e) => setData('fecha_alta', e.target.value)}
                        className="mt-1 block w-full"
                        required
                    />
                    <InputError message={errors.fecha_alta} className="mt-2" />
                </div>

                {/* Fecha de Nacimiento */}
                <div>
                    <InputLabel htmlFor="fecha_nacimiento" value="Fecha de Nacimiento" />
                    <TextInput
                        id="fecha_nacimiento"
                        type="date"
                        value={data.fecha_nacimiento}
                        onChange={(e) => setData('fecha_nacimiento', e.target.value)}
                        className="mt-1 block w-full"
                    />
                    <InputError message={errors.fecha_nacimiento} className="mt-2" />
                </div>

                {/* Sexo */}
                <div>
                    <InputLabel htmlFor="sexo" value="Sexo" />
                    <select
                        id="sexo"
                        value={data.sexo}
                        onChange={(e) => setData('sexo', e.target.value)}
                        className="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                    >
                        <option value="H">Hombre</option>
                        <option value="M">Mujer</option>
                    </select>
                    <InputError message={errors.sexo} className="mt-2" />
                </div>

                {/* Nivel */}
                <div>
                    <InputLabel htmlFor="nivel" value="Nivel" />
                    <TextInput
                        id="nivel"
                        type="text"
                        value={data.nivel}
                        onChange={(e) => setData('nivel', e.target.value)}
                        className="mt-1 block w-full"
                    />
                    <InputError message={errors.nivel} className="mt-2" />
                </div>

                {/* NSS */}
                <div>
                    <InputLabel htmlFor="nss" value="NSS (Número de Seguridad Social)" />
                    <TextInput
                        id="nss"
                        type="text"
                        value={data.nss}
                        onChange={(e) => setData('nss', e.target.value)}
                        className="mt-1 block w-full"
                    />
                    <InputError message={errors.nss} className="mt-2" />
                </div>

                {/* Afiliación */}
                <div>
                    <InputLabel htmlFor="afiliacion" value="Afiliación (ISSSTE, IMSS, etc.)" />
                    <TextInput
                        id="afiliacion"
                        type="text"
                        value={data.afiliacion}
                        onChange={(e) => setData('afiliacion', e.target.value)}
                        className="mt-1 block w-full"
                    />
                    <InputError message={errors.afiliacion} className="mt-2" />
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

                {/* Es Gerente */}
                <div className="flex items-center">
                    <label className="flex items-center">
                        <input
                            type="checkbox"
                            checked={data.es_gerente}
                            onChange={(e) => {
                                if (!data.activo) {
                                    alert('Debe marcar al empleado como activo primero');
                                    return;
                                }
                                setData('es_gerente', e.target.checked);
                            }}
                            className="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                        />
                        <span className="ml-2 text-sm text-gray-600">
                            Es Gerente (solo un empleado activo puede serlo)
                        </span>
                    </label>
                </div>
                {/* Jefe Inmediato */}
                <div className="md:col-span-2">
                    <InputLabel htmlFor="jefe_inmediato" value="Jefe Inmediato (Para Reportes)" />
                    <select
                        id="jefe_inmediato"
                        value={data.jefe_inmediato}
                        onChange={(e) => setData('jefe_inmediato', e.target.value)}
                        className="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                    >
                        <option value="">-- Seleccione un Jefe --</option>
                        {posiblesJefes && posiblesJefes.map((jefe, index) => (
                            <option key={index} value={jefe}>
                                {jefe}
                            </option>
                        ))}
                        {/* Si el jefe actual no está en la lista (e.g. ya no es activo), mostrarlo para no perder el dato */}
                        {data.jefe_inmediato && posiblesJefes && !posiblesJefes.includes(data.jefe_inmediato) && (
                            <option value={data.jefe_inmediato}>{data.jefe_inmediato} (Actual - No listado)</option>
                        )}
                    </select>
                    <InputError message={errors.jefe_inmediato} className="mt-2" />
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
