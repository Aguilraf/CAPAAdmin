import React, { useEffect } from 'react';
import { Head } from '@inertiajs/react';
import OfficialHeader from '@/Components/Reports/OfficialHeader';
import OfficialFooter from '@/Components/Reports/OfficialFooter';
import SignatureBlock from '@/Components/Reports/SignatureBlock';

export default function Print({ data, settings }) {

    // Auto-formatting date: "José María Morelos, Quintana Roo, 12 de enero del 2026"
    const formatDate = (dateString) => {
        const date = new Date(dateString);
        // Add one day to fix timezone off-by-one if necessary, or just treat as local
        // Using UTC methods to avoid shift if the string is YYYY-MM-DD
        const day = date.getUTCDate();
        const year = date.getUTCFullYear();
        const monthNames = [
            "enero", "febrero", "marzo", "abril", "mayo", "junio",
            "julio", "agosto", "septiembre", "octubre", "noviembre", "diciembre"
        ];
        const month = monthNames[date.getUTCMonth()];

        return `José María Morelos, Quintana Roo, ${day} de ${month} del ${year}`;
    };

    return (
        <div className="bg-white min-h-screen text-black font-sans text-sm p-8 max-w-[21.59cm] mx-auto print:max-w-none print:p-0">
            <Head title="Solicitud de Material - Imprimir" />
            <style>{`
                @page {
                    size: Letter;
                    margin: 1.5cm;
                }
                @media print {
                    body {
                        -webkit-print-color-adjust: exact;
                        print-color-adjust: exact;
                    }
                }
            `}</style>

            {/* Print Instructions (Hidden when printing) */}
            <div className="mb-8 p-4 bg-blue-50 border border-blue-200 rounded text-blue-800 print:hidden flex justify-between items-center">
                <p>Vista previa del documento. Utiliza Ctrl+P o el botón para imprimir.</p>
                <button
                    onClick={() => window.print()}
                    className="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 font-bold"
                >
                    Imprimir Ahora
                </button>
            </div>

            <div className="print:w-full print:h-full flex flex-col justify-between" style={{ minHeight: '25cm' }}>

                {/* HEAD */}
                <div>
                    <OfficialHeader />

                    {/* Meta Header Info */}
                    <div className="text-right mb-8">
                        <p className="font-bold">Asunto: <span className="font-normal">SOLICITO MATERIAL DE OFICINA</span></p>
                        <p className="mt-2">{formatDate(data.fecha)}</p>
                    </div>

                    {/* Addressee */}
                    <div className="mb-8 font-bold uppercase tracking-wide leading-relaxed">
                        <p>C. {data.destinatario_nombre}</p>
                        <p>{data.destinatario_cargo}</p>
                        <p>JOSE MARIA MORELOS</p>
                        <p>Presente:</p>
                    </div>

                    {/* Body Text */}
                    <div className="mb-6 text-justify leading-relaxed">
                        <p className="indent-8">
                            Con la finalidad de llevar a cabo los trabajos en el Área de {data.solicitante_departamento || 'departamento de Recursos Financieros'}, por lo que le solicito el siguiente material:
                        </p>
                    </div>

                    {/* Table */}
                    <div className="mb-8">
                        <table className="w-full border-collapse border border-gray-400">
                            <thead>
                                <tr className="bg-gray-100">
                                    <th className="border border-gray-400 px-4 py-2 text-left uppercase w-2/3">Articulo</th>
                                    <th className="border border-gray-400 px-4 py-2 text-left uppercase w-1/3">Cantidad</th>
                                </tr>
                            </thead>
                            <tbody>
                                {data.items.map((item, index) => (
                                    <tr key={index}>
                                        <td className="border border-gray-400 px-4 py-2 uppercase">
                                            {item.custom_articulo || 'Material Desconocido'}
                                        </td>
                                        <td className="border border-gray-400 px-4 py-2 uppercase">
                                            {item.cantidad} {item.custom_unidad || 'PIEZAS'}
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>

                    {/* Closing Text */}
                    <div className="mb-12 text-justify leading-relaxed">
                        <p className="indent-8">
                            Sin otro asunto en particular, me es grato hacer propicia la ocasión para enviarle un cordial saludo.
                        </p>
                    </div>

                    {/* Signature */}
                    <SignatureBlock
                        signatories={[
                            {
                                name: data.solicitante_nombre,
                                position: data.solicitante_cargo
                            }
                        ]}
                    />
                </div>

                {/* Dynamic Custom Footer */}
                <div className="mt-auto relative w-full h-48">
                    {/* Background Image if exists */}
                    {settings?.footer_imagen ? (
                        <div
                            className="absolute inset-0 z-0 bg-no-repeat bg-bottom bg-cover"
                            style={{
                                backgroundImage: `url(/media/${settings.footer_imagen})`,
                                // Adjust background size/position as needed to match the wave design
                                opacity: 1,
                            }}
                        />
                    ) : (
                        /* Fallback decorative line if no image */
                        <div className="absolute inset-x-0 bottom-0 h-2 bg-pink-500 z-0"></div>
                    )}

                    {/* Footer Text Content */}
                    <div className="relative z-10 flex flex-col justify-end h-full pb-4 px-8 items-center text-center text-xs text-gray-700">
                        {/* Optional Logo in Footer (CAPA) - positioned absolute right typically, or inline */}
                        {settings?.logo_capa && (
                            <img
                                src={`/media/${settings.logo_capa}`}
                                alt="CAPA Logo"
                                className="h-16 object-contain absolute right-4 bottom-4 opacity-90"
                            />
                        )}

                        <div className="bg-white/60 p-2 rounded backdrop-blur-sm max-w-2xl mx-auto">
                            {settings?.footer_organismo && <p className="font-bold text-gray-800">{settings.footer_organismo}</p>}
                            {settings?.footer_direccion && <p>{settings.footer_direccion}</p>}
                            <p>
                                {settings?.footer_telefono && <span>Tel.: {settings.footer_telefono} </span>}
                                {settings?.footer_email && <span>{settings.footer_email}</span>}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}
