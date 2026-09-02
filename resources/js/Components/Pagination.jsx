import React from 'react';
import { Link } from '@inertiajs/react';

export default function Pagination({ links }) {
    if (links.length <= 3) return null;

    const label = (value) => {
        const plainValue = value.replace(/<[^>]*>/g, '').trim();
        const translations = {
            'pagination.previous': 'Anterior',
            'pagination.next': 'Siguiente',
        };

        return translations[plainValue] || value;
    };

    return (
        <div className="flex flex-wrap -mb-1">
            {links.map((link, key) => {
                if (link.url === null) {
                    return (
                        <div
                            key={key}
                            className="mr-1 mb-1 px-4 py-3 text-sm leading-4 text-gray-400 border rounded"
                            dangerouslySetInnerHTML={{ __html: label(link.label) }}
                        />
                    );
                }

                return (
                    <Link
                        key={key}
                        className={
                            'mr-1 mb-1 px-4 py-3 text-sm leading-4 border rounded hover:bg-white focus:border-indigo-500 focus:text-indigo-500 ' +
                            (link.active ? 'bg-indigo-600 text-white' : 'bg-white')
                        }
                        href={link.url}
                        dangerouslySetInnerHTML={{ __html: label(link.label) }}
                    />
                );
            })}
        </div>
    );
}
