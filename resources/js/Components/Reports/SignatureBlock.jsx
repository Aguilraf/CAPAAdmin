import React from 'react';

export default function SignatureBlock({ signatories = [] }) {
    return (
        <div className="w-full mt-16 mb-8 text-center break-inside-avoid">
            <p className="font-bold mb-16">ATENTAMENTE</p>

            <div className="flex justify-center gap-16">
                {signatories.map((person, index) => (
                    <div key={index} className="flex flex-col items-center">
                        <div className="w-64 border-t border-black pt-2">
                            <p className="font-bold uppercase text-sm">{person.name}</p>
                            <p className="text-xs uppercase">{person.position}</p>
                        </div>
                    </div>
                ))}
            </div>
        </div>
    );
}
