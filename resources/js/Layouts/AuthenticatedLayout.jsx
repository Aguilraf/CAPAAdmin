import ApplicationLogo from '@/Components/ApplicationLogo';
import Dropdown from '@/Components/Dropdown';
import NavLink from '@/Components/NavLink';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink';
import { Link, usePage } from '@inertiajs/react';
import { useState } from 'react';

export default function AuthenticatedLayout({ header, children }) {
    const user = usePage().props.auth.user;

    const [showingNavigationDropdown, setShowingNavigationDropdown] =
        useState(false);

    const [sidebarOpen, setSidebarOpen] = useState(true);

    const NavItem = ({ href, active, children, icon }) => (
        <Link
            href={href}
            className={`flex items-center px-4 py-3 text-sm font-medium transition-colors duration-150 rounded-lg mb-1 ${active
                ? 'bg-blue-50 text-blue-700 shadow-sm'
                : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'
                }`}
        >
            {icon && <span className="mr-3">{icon}</span>}
            {children}
        </Link>
    );

    return (
        <div className="min-h-screen bg-gray-50 flex">
            {/* Sidebar */}
            <aside
                className={`bg-white border-r border-gray-200 transition-all duration-300 ease-in-out fixed inset-y-0 left-0 z-50 flex flex-col ${sidebarOpen ? 'w-64' : 'w-20'
                    }`}
            >
                <div className="h-16 flex items-center px-6 border-b border-gray-100 shrink-0">
                    <Link href="/" className="flex items-center gap-3 overflow-hidden">
                        <ApplicationLogo className="h-8 w-auto fill-current text-blue-600 shrink-0" />
                        <span className={`font-bold text-xl text-gray-800 transition-opacity duration-300 ${sidebarOpen ? 'opacity-100' : 'opacity-0 w-0'}`}>
                            CAPA
                        </span>
                    </Link>
                </div>

                <div className="flex-1 overflow-y-auto py-4 px-3 custom-scrollbar">
                    <nav className="space-y-1">
                        <NavItem
                            href={route('dashboard')}
                            active={route().current('dashboard')}
                            icon={<svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>}
                        >
                            {sidebarOpen && 'Dashboard'}
                        </NavItem>

                        <NavItem
                            href={route('commissions.index')}
                            active={route().current('commissions.*')}
                            icon={<svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>}
                        >
                            {sidebarOpen && 'Comisiones'}
                        </NavItem>

                        <NavItem
                            href={route('requirements.index')}
                            active={route().current('requirements.*')}
                            icon={<svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg>}
                        >
                            {sidebarOpen && 'Requerimientos'}
                        </NavItem>

                        <NavItem
                            href={route('payments.index')}
                            active={route().current('payments.*')}
                            icon={<svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>}
                        >
                            {sidebarOpen && 'Cobro / Pagos'}
                        </NavItem>

                        <div className="pt-4 pb-2">
                            <p className={`px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2 ${!sidebarOpen && 'hidden'}`}>
                                Módulos
                            </p>
                        </div>

                        {/* Dropdown for Reportes in Sidebar */}
                        {user.permissions?.includes('generar reportes') && (
                            <div className="px-2">
                                <Dropdown>
                                    <Dropdown.Trigger>
                                        <button className={`w-full flex items-center px-4 py-3 text-sm font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900 rounded-lg transition-colors outline-none cursor-pointer ${!sidebarOpen && 'justify-center'}`}>
                                            <svg className="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                            {sidebarOpen && (
                                                <>
                                                    <span className="ml-3 flex-1 text-left">Reportes</span>
                                                    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 9l-7 7-7-7" /></svg>
                                                </>
                                            )}
                                        </button>
                                    </Dropdown.Trigger>
                                    <Dropdown.Content align="right" width="48" contentClasses="py-1 bg-white">
                                        <Dropdown.Link href={route('reportes.index')}>Inicio Reportes</Dropdown.Link>
                                        <Dropdown.Link href={route('reportes.material-request.create')}>Nueva Solicitud</Dropdown.Link>
                                        <div className="border-t border-gray-100"></div>
                                        <Dropdown.Link href={route('reportes.historial')}>Historial</Dropdown.Link>
                                        <Dropdown.Link href={route('cfe.query')}>Consulta Historial CFE</Dropdown.Link>
                                    </Dropdown.Content>
                                </Dropdown>
                            </div>
                        )}

                        {/* Dropdown for Bomberos in Sidebar */}
                        {(user.permissions?.includes('capturar bomberos') || user.permissions?.includes('recibir bomberos') || user.roles?.some(r => r.name === 'Administrador')) && (
                            <div className="px-2">
                                <Dropdown>
                                    <Dropdown.Trigger>
                                        <button className={`w-full flex items-center px-4 py-3 text-sm font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900 rounded-lg transition-colors outline-none cursor-pointer ${!sidebarOpen && 'justify-center'}`}>
                                            <svg className="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z" /></svg>
                                            {sidebarOpen && (
                                                <>
                                                    <span className="ml-3 flex-1 text-left">Bomberos</span>
                                                    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 9l-7 7-7-7" /></svg>
                                                </>
                                            )}
                                        </button>
                                    </Dropdown.Trigger>
                                    <Dropdown.Content align="right" width="48" contentClasses="py-1 bg-white">
                                        {(user.permissions?.includes('capturar bomberos') || user.roles?.some(r => r.name === 'Administrador')) && (
                                            <Dropdown.Link href="/firefighters/capture">Capturar</Dropdown.Link>
                                        )}
                                        {(user.permissions?.includes('recibir bomberos') || user.roles?.some(r => r.name === 'Administrador')) && (
                                            <Dropdown.Link href="/firefighters/receive">Recibir</Dropdown.Link>
                                        )}
                                        <Dropdown.Link href="/firefighters/report">Reportes</Dropdown.Link>
                                        <Dropdown.Link href="/firefighters/query">Consulta Historial</Dropdown.Link>
                                        <div className="border-t border-gray-100"></div>
                                        <Dropdown.Link href="/firefighters/communities">Comunidades</Dropdown.Link>
                                        <Dropdown.Link href="/firefighters/list">Lista Bomberos</Dropdown.Link>
                                        {(user.permissions?.includes('importar bomberos') || user.roles?.some(r => r.name === 'Administrador')) && (
                                            <Dropdown.Link href="/firefighters/import">Importar Bomberos</Dropdown.Link>
                                        )}
                                    </Dropdown.Content>
                                </Dropdown>
                            </div>
                        )}

                        <NavItem
                            href={route('vacations.index')}
                            active={route().current('vacations.*')}
                            icon={<svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>}
                        >
                            {sidebarOpen && (
                                <div className="flex justify-between items-center w-full">
                                    <span>Vacaciones</span>
                                    {user.roles?.some(r => r.name === 'Administrador') && (
                                        <span className="bg-blue-100 text-blue-600 px-1.5 py-0.5 rounded text-[10px] font-bold">ADMIN</span>
                                    )}
                                </div>
                            )}
                        </NavItem>

                        {user.roles?.some(r => r.name === 'Administrador') && (
                            <>
                                <div className="pt-4 pb-2">
                                    <p className={`px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2 ${!sidebarOpen && 'hidden'}`}>
                                        Administración
                                    </p>
                                </div>

                                <div className="px-2">
                                    <Dropdown>
                                        <Dropdown.Trigger>
                                            <button className={`w-full flex items-center px-4 py-3 text-sm font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900 rounded-lg transition-colors outline-none cursor-pointer ${!sidebarOpen && 'justify-center'}`}>
                                                <svg className="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                                                {sidebarOpen && (
                                                    <>
                                                        <span className="ml-3 flex-1 text-left">Catálogos</span>
                                                        <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 9l-7 7-7-7" /></svg>
                                                    </>
                                                )}
                                            </button>
                                        </Dropdown.Trigger>
                                        <Dropdown.Content align="right" width="48" contentClasses="py-1 bg-white">
                                            <Dropdown.Link href={route('empleados.index')}>Empleados</Dropdown.Link>
                                            <Dropdown.Link href={route('puestos.index')}>Puestos</Dropdown.Link>
                                            <Dropdown.Link href={route('organismos.index')}>Organismos</Dropdown.Link>
                                            <Dropdown.Link href={route('providers.index')}>Proveedores</Dropdown.Link>
                                            <div className="border-t border-gray-100"></div>
                                            <Dropdown.Link href={route('vehicles.index')}>Parque Vehicular</Dropdown.Link>
                                            <Dropdown.Link href={route('travel-allowance-rates.index')}>Viáticos y Pasajes</Dropdown.Link>
                                            <div className="border-t border-gray-100"></div>
                                            <Dropdown.Link href={route('materiales.index')}>Materiales</Dropdown.Link>
                                            <Dropdown.Link href={route('unidades-medida.index')}>Unidades de Medida</Dropdown.Link>
                                            <div className="border-t border-gray-100"></div>
                                            <Dropdown.Link href={route('capitulos.index')}>Capítulos</Dropdown.Link>
                                            <Dropdown.Link href={route('partidas.index')}>Partidas</Dropdown.Link>
                                            <Dropdown.Link href={route('leyendas.index')}>Leyendas</Dropdown.Link>
                                        </Dropdown.Content>
                                    </Dropdown>
                                </div>

                                <NavItem
                                    href={route('import.index')}
                                    active={route().current('import.*')}
                                    icon={<svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>}
                                >
                                    {sidebarOpen && 'Importar/Exportar Datos'}
                                </NavItem>

                                <NavItem
                                    href={route('settings.index')}
                                    active={route().current('settings.*')}
                                    icon={<svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>}
                                >
                                    {sidebarOpen && 'Configuración'}
                                </NavItem>

                                <div className="px-2">
                                    <Dropdown>
                                        <Dropdown.Trigger>
                                            <button className={`w-full flex items-center px-4 py-3 text-sm font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900 rounded-lg transition-colors outline-none cursor-pointer ${!sidebarOpen && 'justify-center'}`}>
                                                <svg className="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                                                {sidebarOpen && (
                                                    <>
                                                        <span className="ml-3 flex-1 text-left">Seguridad</span>
                                                        <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 9l-7 7-7-7" /></svg>
                                                    </>
                                                )}
                                            </button>
                                        </Dropdown.Trigger>
                                        <Dropdown.Content align="right" width="48" contentClasses="py-1 bg-white">
                                            <Dropdown.Link href={route('users.index')}>Usuarios</Dropdown.Link>
                                            <Dropdown.Link href={route('roles.index')}>Roles y Permisos</Dropdown.Link>
                                        </Dropdown.Content>
                                    </Dropdown>
                                </div>
                            </>
                        )}
                    </nav>
                </div>

                <div className="p-4 border-t border-gray-100 shrink-0">
                    <button
                        onClick={() => setSidebarOpen(!sidebarOpen)}
                        className="w-full flex items-center justify-center p-2 rounded-lg bg-gray-50 text-gray-500 hover:bg-gray-100 hover:text-gray-700 transition-colors"
                    >
                        <svg className={`w-5 h-5 transition-transform duration-300 ${sidebarOpen ? 'rotate-180' : ''}`} fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M13 5l7 7-7 7M5 5l7 7-7 7" /></svg>
                    </button>
                </div>
            </aside>

            {/* Main Content Area */}
            <div className={`flex-1 flex flex-col min-w-0 transition-all duration-300 ${sidebarOpen ? 'ml-64' : 'ml-20'}`}>
                {/* Top Header */}
                <header className="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-8 sticky top-0 z-40">
                    <div className="flex-1">
                        {header}
                    </div>

                    <div className="flex items-center space-x-4">
                        <Dropdown>
                            <Dropdown.Trigger>
                                <button className="flex items-center space-x-3 p-2 rounded-lg hover:bg-gray-100 transition-colors duration-150">
                                    <div className="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold text-xs">
                                        {user.name.charAt(0)}
                                    </div>
                                    <div className="hidden md:block text-left">
                                        <p className="text-sm font-semibold text-gray-800 leading-none">{user.name}</p>
                                        <p className="text-xs text-gray-500 mt-1 uppercase">{user.roles?.[0]?.name || 'Usuario'}</p>
                                    </div>
                                    <svg className="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 9l-7 7-7-7" /></svg>
                                </button>
                            </Dropdown.Trigger>
                            <Dropdown.Content align="right" width="48" contentClasses="py-1 bg-white">
                                <Dropdown.Link href={route('profile.edit')}>Cambiar Contraseña</Dropdown.Link>
                                <div className="border-t border-gray-100"></div>
                                <Dropdown.Link href={route('logout')} method="post" as="button">Cerrar Sesión</Dropdown.Link>
                            </Dropdown.Content>
                        </Dropdown>
                    </div>
                </header>

                <main className="flex-1 py-8">
                    <div className="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8">
                        {/* Flash Messages */}
                        <div className="mb-6">
                            {usePage().props.flash.success && (
                                <div className="bg-green-50 border-l-4 border-green-400 p-4 rounded-r-lg shadow-sm animate-fade-out" role="alert">
                                    <div className="flex items-center">
                                        <div className="shrink-0 text-green-400">
                                            <svg className="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clipRule="evenodd" /></svg>
                                        </div>
                                        <div className="ml-3">
                                            <p className="text-sm font-medium text-green-800">{usePage().props.flash.success}</p>
                                        </div>
                                    </div>
                                </div>
                            )}
                            {usePage().props.flash.error && (
                                <div className="bg-red-50 border-l-4 border-red-400 p-4 rounded-r-lg shadow-sm animate-fade-out" role="alert">
                                    <div className="flex items-center">
                                        <div className="shrink-0 text-red-400">
                                            <svg className="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clipRule="evenodd" /></svg>
                                        </div>
                                        <div className="ml-3">
                                            <p className="text-sm font-medium text-red-800">{usePage().props.flash.error}</p>
                                        </div>
                                    </div>
                                </div>
                            )}
                        </div>

                        {children}
                    </div>
                </main>

                <footer className="py-4 px-8 border-t border-gray-100 bg-white text-center text-xs text-gray-400">
                    &copy; {new Date().getFullYear()} CAPA - Sistema de Gestión Administrativa
                </footer>
            </div>
        </div>
    );
}
