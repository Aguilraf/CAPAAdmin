import ApplicationLogo from '@/Components/ApplicationLogo';
import Dropdown from '@/Components/Dropdown';
import NavLink from '@/Components/NavLink';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink';
import { Link, usePage } from '@inertiajs/react';
import { useState } from 'react';

export default function AuthenticatedLayout({ header, children }) {
    const user = usePage().props.auth.user;
    const [showingNavigationDropdown, setShowingNavigationDropdown] = useState(false);

    return (
        <div className="min-h-screen bg-gray-100 font-sans antialiased">
            {/* Navbar Horizontal Superior */}
            <nav className="bg-white border-b border-gray-100 shadow-sm sticky top-0 z-50">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="flex justify-between h-16">
                        <div className="flex">
                            {/* Logo */}
                            <div className="shrink-0 flex items-center">
                                <Link href="/">
                                    <ApplicationLogo className="block h-9 w-auto fill-current text-blue-600" />
                                </Link>
                                <span className="ml-3 font-bold text-xl text-gray-800 hidden md:block uppercase tracking-tight">
                                    CAPA
                                </span>
                            </div>

                            {/* Links Principales */}
                            <div className="hidden space-x-4 lg:space-x-8 sm:-my-px sm:ml-10 sm:flex">
                                <NavLink href={route('dashboard')} active={route().current('dashboard')}>
                                    Dashboard
                                </NavLink>
                                <NavLink href={route('commissions.index')} active={route().current('commissions.*')}>
                                    Comisiones
                                </NavLink>
                                {user.roles?.some(r => r.name === 'Administrador') && (
                                    <NavLink href={route('requirements.index')} active={route().current('requirements.*')}>
                                        Requerimientos
                                    </NavLink>
                                )}
                                <NavLink href={route('payments.index')} active={route().current('payments.*')}>
                                    Cobro / Pagos
                                </NavLink>

                                <div className="inline-flex items-center">
                                    <Dropdown>
                                        <Dropdown.Trigger>
                                            <button className="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out cursor-pointer">
                                                Ingresos
                                                <svg className="ml-2 -mr-0.5 h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path fillRule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clipRule="evenodd" /></svg>
                                            </button>
                                        </Dropdown.Trigger>
                                        <Dropdown.Content align="right" width="48">
                                            <Dropdown.Link href={route('incomes.index')}>Ingresos Bancarios</Dropdown.Link>
                                            <Dropdown.Link href={route('daily-incomes.index')}>Cobranza del Día</Dropdown.Link>
                                            <Dropdown.Link href={route('daily-incomes.create')}>Nueva cobranza</Dropdown.Link>
                                            <Dropdown.Link href={route('income-policies.create')}>Agregar Póliza de Ingreso</Dropdown.Link>
                                            <Dropdown.Link href={route('income-accounts.index')}>Catálogo de cuentas</Dropdown.Link>
                                        </Dropdown.Content>
                                    </Dropdown>
                                </div>

                                {/* Dropdown Reportes */}
                                {(user.permissions?.includes('generar reportes') || user.permissions?.includes('gestionar reportes') || user.roles?.some(r => r.name === 'Administrador')) && (
                                    <div className="inline-flex items-center">
                                        <Dropdown>
                                            <Dropdown.Trigger>
                                                <button className="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out cursor-pointer">
                                                    Reportes
                                                    <svg className="ml-2 -mr-0.5 h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path fillRule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clipRule="evenodd" /></svg>
                                                </button>
                                            </Dropdown.Trigger>
                                            <Dropdown.Content align="right" width="48">
                                                <Dropdown.Link href={route('reportes.index')}>Inicio Reportes</Dropdown.Link>
                                                <Dropdown.Link href={route('reportes.revolvente.index')}>Fondo Revolvente</Dropdown.Link>
                                                <Dropdown.Link href={route('reportes.material-request.create')}>Nueva Solicitud</Dropdown.Link>
                                                <Dropdown.Link href={route('reportes.historial')}>Historial</Dropdown.Link>
                                                <Dropdown.Link href={route('cfe.query')}>Consulta Historial CFE</Dropdown.Link>
                                            </Dropdown.Content>
                                        </Dropdown>
                                    </div>
                                )}

                                {/* Dropdown Bomberos */}
                                {(user.permissions?.includes('capturar bomberos') || user.permissions?.includes('recibir bomberos') || user.roles?.some(r => r.name === 'Administrador')) && (
                                    <div className="inline-flex items-center">
                                        <Dropdown>
                                            <Dropdown.Trigger>
                                                <button className="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out cursor-pointer">
                                                    Bomberos
                                                    <svg className="ml-2 -mr-0.5 h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path fillRule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clipRule="evenodd" /></svg>
                                                </button>
                                            </Dropdown.Trigger>
                                            <Dropdown.Content align="right" width="48">
                                                <Dropdown.Link href="/firefighters/capture">Capturar</Dropdown.Link>
                                                <Dropdown.Link href="/firefighters/receive">Recibir</Dropdown.Link>
                                                <Dropdown.Link href="/firefighters/report">Reportes</Dropdown.Link>
                                                <Dropdown.Link href="/firefighters/query">Consulta Historial</Dropdown.Link>
                                                <Dropdown.Link href="/firefighters/communities">Comunidades</Dropdown.Link>
                                                <Dropdown.Link href="/firefighters/list">Lista Bomberos</Dropdown.Link>
                                                <Dropdown.Link href="/firefighters/import">Importar Bomberos</Dropdown.Link>
                                            </Dropdown.Content>
                                        </Dropdown>
                                    </div>
                                )}

                                <NavLink href={route('vacations.index')} active={route().current('vacations.*')}>
                                    Vacaciones
                                </NavLink>

                                {/* Dropdown Administración (Si es Admin) */}
                                {user.roles?.some(r => r.name === 'Administrador') && (
                                    <div className="inline-flex items-center">
                                        <Dropdown>
                                            <Dropdown.Trigger>
                                                <button className="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out cursor-pointer">
                                                    Administración
                                                    <svg className="ml-2 -mr-0.5 h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path fillRule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clipRule="evenodd" /></svg>
                                                </button>
                                            </Dropdown.Trigger>
                                            <Dropdown.Content align="right" width="56">
                                                <div className="px-4 py-2 text-xs text-gray-400 uppercase tracking-widest font-semibold bg-gray-50">Catálogos</div>
                                                <Dropdown.Link href={route('empleados.index')}>Empleados</Dropdown.Link>
                                                <Dropdown.Link href={route('puestos.index')}>Puestos</Dropdown.Link>
                                                <Dropdown.Link href={route('organismos.index')}>Organismos</Dropdown.Link>
                                                <Dropdown.Link href={route('providers.index')}>Proveedores</Dropdown.Link>
                                                <Dropdown.Link href={route('banks.index')}>Bancos</Dropdown.Link>
                                                <Dropdown.Link href={route('incomes.index')}>Ingresos</Dropdown.Link>

                                                <Dropdown.Link href={route('vehicles.index')}>Parque Vehicular</Dropdown.Link>
                                                <Dropdown.Link href={route('travel-allowance-rates.index')}>Viáticos y Pasajes</Dropdown.Link>
                                                <Dropdown.Link href={route('materiales.index')}>Materiales</Dropdown.Link>
                                                <Dropdown.Link href={route('unidades-medida.index')}>Unidades de Medida</Dropdown.Link>
                                                <Dropdown.Link href={route('capitulos.index')}>Capítulos</Dropdown.Link>
                                                <Dropdown.Link href={route('partidas.index')}>Partidas</Dropdown.Link>
                                                <Dropdown.Link href={route('leyendas.index')}>Leyendas</Dropdown.Link>

                                                <div className="border-t border-gray-100"></div>
                                                <div className="px-4 py-2 text-xs text-gray-400 uppercase tracking-widest font-semibold bg-gray-50">Sistema</div>
                                                <Dropdown.Link href={route('import.index')}>Importar/Exportar Datos</Dropdown.Link>
                                                <Dropdown.Link href={route('settings.index')}>Configuración</Dropdown.Link>
                                                <Dropdown.Link href={route('users.index')}>Usuarios</Dropdown.Link>
                                                <Dropdown.Link href={route('roles.index')}>Roles y Permisos</Dropdown.Link>
                                            </Dropdown.Content>
                                        </Dropdown>
                                    </div>
                                )}
                            </div>
                        </div>

                        {/* Menú Usuario Derecha */}
                        <div className="hidden sm:flex sm:items-center sm:ml-6">
                            <div className="ml-3 relative">
                                <Dropdown>
                                    <Dropdown.Trigger>
                                        <span className="inline-flex rounded-md">
                                            <button
                                                type="button"
                                                className="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150 cursor-pointer"
                                            >
                                                <div className="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold text-xs mr-2">
                                                    {user.name.charAt(0)}
                                                </div>
                                                <div className="text-left hidden lg:block mr-2">
                                                    <p className="font-semibold text-gray-800 leading-none">{user.name}</p>
                                                    <p className="text-[10px] text-gray-400 uppercase mt-0.5">{user.roles?.[0]?.name || 'Usuario'}</p>
                                                </div>
                                                <svg className="ml-2 -mr-0.5 h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path fillRule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clipRule="evenodd" /></svg>
                                            </button>
                                        </span>
                                    </Dropdown.Trigger>
                                    <Dropdown.Content align="right" width="48">
                                        <Dropdown.Link href={route('profile.edit')}>Cambiar Contraseña</Dropdown.Link>
                                        <Dropdown.Link href={route('logout')} method="post" as="button">
                                            Cerrar Sesión
                                        </Dropdown.Link>
                                    </Dropdown.Content>
                                </Dropdown>
                            </div>
                        </div>

                        {/* Botón menú móvil */}
                        <div className="-mr-2 flex items-center sm:hidden">
                            <button
                                onClick={() => setShowingNavigationDropdown((previousState) => !previousState)}
                                className="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out"
                            >
                                <svg className="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path
                                        className={!showingNavigationDropdown ? 'inline-flex' : 'hidden'}
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        className={showingNavigationDropdown ? 'inline-flex' : 'hidden'}
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                {/* Menú Móvil (Responsive) */}
                <div className={(showingNavigationDropdown ? 'block' : 'hidden') + ' sm:hidden'}>
                    <div className="pt-2 pb-3 space-y-1">
                        <ResponsiveNavLink href={route('dashboard')} active={route().current('dashboard')}>
                            Dashboard
                        </ResponsiveNavLink>
                        {/* Agrega aquí los demás links para móvil si es necesario */}
                    </div>

                    <div className="pt-4 pb-1 border-t border-gray-200">
                        <div className="px-4">
                            <div className="font-medium text-base text-gray-800">{user.name}</div>
                            <div className="font-medium text-sm text-gray-500">{user.email}</div>
                        </div>
                        <div className="mt-3 space-y-1">
                            <ResponsiveNavLink href={route('profile.edit')}>Cambiar Contraseña</ResponsiveNavLink>
                            <ResponsiveNavLink method="post" href={route('logout')} as="button">
                                Cerrar Sesión
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>

            {/* Cabecera de Página */}
            {header && (
                <header className="bg-white shadow">
                    <div className="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {header}
                    </div>
                </header>
            )}

            {/* Contenido Principal */}
            <main>
                <div className="py-8">
                    <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                        {/* Flash Messages */}
                        {usePage().props.flash.success && (
                            <div className="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                                <span className="block sm:inline">{usePage().props.flash.success}</span>
                            </div>
                        )}
                        {usePage().props.flash.error && (
                            <div className="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                                <span className="block sm:inline">{usePage().props.flash.error}</span>
                            </div>
                        )}
                        {children}
                    </div>
                </div>
            </main>

            <footer className="bg-white border-t border-gray-100 py-6 text-center text-xs text-gray-400">
                &copy; {new Date().getFullYear()} CAPA - Sistema de Gestión Administrativa
            </footer>
        </div>
    );
}
