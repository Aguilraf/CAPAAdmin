import React from 'react';
import { usePage } from '@inertiajs/react';

export default function OfficialFooter() {
    const { settings } = usePage().props;

    const getLogoUrl = (path) => {
        return path ? `/storage/${path}` : null;
    };

    return (
        <div className="w-full mt-auto pt-8 font-sans text-[10px] text-gray-600">
            {/* CCP Minutario */}
            <div className="flex items-end justify-between mb-4">
                <div className="w-2/3">
                    <div className="border-t border-gray-300 pt-1">
                        <p>C.C.P.- MINUTARIO</p>
                    </div>
                </div>

                {/* Contact Info */}
                <div className="w-1/3 text-right">
                    <p className="font-bold text-gray-800">Comisión de Agua Potable y Alcantarillado</p>
                    <p>Organismo Operador: José María Morelos</p>
                    <p>Calle Noh Bec entre Cecilio Chi y Konhulich. Col. Miraflores C.P. 77890</p>
                    <p>Tel.: (997) 97 80179</p>
                    <p>capamorelos@capa.gob.mx</p>
                </div>
            </div>

            {/* Full-Width CAPA Logo */}
            <div className="w-full flex justify-center items-center mt-2">
                {settings.logo_capa ? (
                    <img
                        src={getLogoUrl(settings.logo_capa)}
                        alt="CAPA"
                        className="w-full h-auto max-h-16 object-contain"
                    />
                ) : (
                    <div className="w-full h-16 bg-gray-200 flex items-center justify-center text-[8px] text-gray-500">
                        [LOGO CAPA FULL WIDTH]
                    </div>
                )}
            </div>
        </div>
    );
}
