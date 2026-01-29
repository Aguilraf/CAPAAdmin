import { Head, Link } from '@inertiajs/react';

export default function Welcome({ auth }) {
    return (
        <>
            <Head title="Welcome" />
            <div className="relative min-h-screen flex flex-col items-center justify-center bg-gray-100 dark:bg-gray-900 selection:bg-red-500 selection:text-white">
                <div className="relative w-full max-w-2xl px-6 lg:max-w-7xl">
                    <header className="grid grid-cols-2 items-center gap-2 py-10 lg:grid-cols-3">
                        <div className="flex lg:col-start-2 lg:justify-center">
                            <h1 className="text-4xl font-bold text-gray-900 dark:text-white">CAPAAdmin</h1>
                        </div>
                        <nav className="-mx-3 flex flex-1 justify-end">
                            {auth.user ? (
                                <Link
                                    href={route('dashboard')}
                                    className="rounded-md px-3 py-2 text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white"
                                >
                                    Dashboard
                                </Link>
                            ) : (
                                <>
                                    <Link
                                        href={route('login')}
                                        className="rounded-md px-3 py-2 text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white"
                                    >
                                        Log in
                                    </Link>
                                    <Link
                                        href={route('register')}
                                        className="rounded-md px-3 py-2 text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white"
                                    >
                                        Register
                                    </Link>
                                </>
                            )}
                        </nav>
                    </header>

                    <main className="mt-6 flex flex-col items-center justify-center text-center">
                        <p className="text-xl text-gray-600 dark:text-gray-400">
                            Welcome to the CAPA Administration System.
                        </p>
                    </main>

                    <footer className="py-16 text-center text-sm text-gray-500 dark:text-gray-400">
                        &copy; {new Date().getFullYear()} CAPAAdmin. All rights reserved.
                    </footer>
                </div>
            </div>
        </>
    );
}
