import React from 'react';
import { usePage } from '@inertiajs/react';

export default function OfficialHeader() {
    const { settings } = usePage().props;

    const getLogoUrl = (path) => {
        return path ? `/media/${path}` : null;
    };

    return (
        <div className="w-full flex justify-between items-start mb-8 font-sans">
            {/* Left Logo - QROO */}
            <div className="flex flex-col items-center w-1/3">
                {settings.logo_qroo ? (
                    <img
                        src={getLogoUrl(settings.logo_qroo)}
                        alt="Gobierno Quintana Roo"
                        className="h-20 w-auto object-contain mb-1"
                    />
                ) : (
                    <div className="h-20 w-auto bg-gray-200 flex items-center justify-center text-xs text-center text-gray-500 mb-1 px-4">
                        [LOGO QROO]
                    </div>
                )}
                <div className="text-[10px] text-center uppercase tracking-widest text-gray-600">
                    Quintana Roo
                    <br />
                    Gobierno del Estado
                </div>
            </div>

            {/* Right Logo - Unidos */}
            <div className="flex flex-col items-center w-1/3">
                {settings.logo_unidos ? (
                    <img
                        src={getLogoUrl(settings.logo_unidos)}
                        alt="Unidos para Transformar"
                        className="h-16 w-auto object-contain"
                    />
                ) : (
                    <div className="h-16 w-auto bg-gray-200 flex items-center justify-center text-xs text-center text-gray-500 px-4">
                        [LOGO UNIDOS]
                    </div>
                )}
            </div>
        </div>
    );
}
