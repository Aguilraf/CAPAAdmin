import React, { useState, useEffect } from 'react';
import { MapContainer, TileLayer, CircleMarker, useMapEvents, useMap } from 'react-leaflet';
import 'leaflet/dist/leaflet.css';

// Component to handle map center updates
function MapController({ center }) {
    const map = useMap();
    useEffect(() => {
        if (center) {
            map.flyTo(center, map.getZoom());
        }
    }, [center, map]);
    return null;
}

// Component to handle map clicks
function MapEvents({ onLocationSelect, isReadOnly }) {
    useMapEvents({
        click(e) {
            if (!isReadOnly) {
                onLocationSelect(e.latlng);
            }
        },
    });
    return null;
}

export default function MapPicker({ value, onChange, isReadOnly = false }) {
    const [position, setPosition] = useState(null);

    useEffect(() => {
        if (value && typeof value === 'string' && value.includes(',')) {
            const parts = value.split(',');
            const lat = parseFloat(parts[0]);
            const lng = parseFloat(parts[1]);

            if (!isNaN(lat) && !isNaN(lng)) {
                setPosition({ lat, lng });
            }
        }
    }, [value]);

    const handleLocationSelect = (latlng) => {
        setPosition(latlng);
        if (onChange) {
            onChange(`${latlng.lat.toFixed(6)}, ${latlng.lng.toFixed(6)}`);
        }
    };

    const defaultCenter = { lat: 19.4326, lng: -99.1332 };

    return (
        <div className="h-64 w-full rounded-md overflow-hidden border border-gray-300 shadow-inner mt-2 relative z-0">
            <MapContainer
                center={defaultCenter}
                zoom={13}
                scrollWheelZoom={false}
                style={{ height: '100%', width: '100%' }}
            >
                <TileLayer
                    attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                    url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
                />

                {/* Controller to move map when position changes externally or initially */}
                {position && <MapController center={position} />}

                {/* Event listener for clicks */}
                <MapEvents onLocationSelect={handleLocationSelect} isReadOnly={isReadOnly} />

                {/* The marker itself */}
                {position && (
                    <CircleMarker center={position} radius={8} pathOptions={{ color: 'red', fillColor: '#f87171', fillOpacity: 0.8 }} />
                )}
            </MapContainer>
            {!isReadOnly && (
                <p className="text-[10px] text-gray-500 mt-1 pl-1">Haz clic en el mapa para marcar la ubicación</p>
            )}
        </div>
    );
}
