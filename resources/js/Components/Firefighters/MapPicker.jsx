import React, { useState, useEffect } from 'react';
import { MapContainer, TileLayer, Marker, useMapEvents, useMap } from 'react-leaflet';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

// Fix for default marker icons in Leaflet with React/Webpack/Vite
import markerIcon from 'leaflet/dist/images/marker-icon.png';
import markerShadow from 'leaflet/dist/images/marker-shadow.png';

let DefaultIcon = L.icon({
    iconUrl: markerIcon,
    shadowUrl: markerShadow,
    iconSize: [25, 41],
    iconAnchor: [12, 41]
});

L.Marker.prototype.options.icon = DefaultIcon;

function MapAutoCenter({ position }) {
    const map = useMap();
    useEffect(() => {
        if (position) {
            map.setView(position, map.getZoom());
        }
    }, [position, map]);
    return null;
}

function LocationMarker({ position, setPosition, isReadOnly }) {
    useMapEvents({
        click(e) {
            if (!isReadOnly) {
                setPosition(e.latlng);
            }
        },
    });

    return position ? (
        <Marker position={position} />
    ) : null;
}

export default function MapPicker({ value, onChange, isReadOnly = false }) {
    const [position, setPosition] = useState(null);

    // Initial value like "Lat, Lng"
    useEffect(() => {
        if (value && typeof value === 'string' && value.includes(',')) {
            const [lat, lng] = value.split(',').map(coord => parseFloat(coord.trim()));
            if (!isNaN(lat) && !isNaN(lng)) {
                setPosition({ lat, lng });
            }
        } else if (!value) {
            setPosition(null);
        }
    }, [value]);

    const handleSetPosition = (pos) => {
        setPosition(pos);
        if (onChange) {
            onChange(`${pos.lat.toFixed(6)}, ${pos.lng.toFixed(6)}`);
        }
    };

    const initialCenter = position || { lat: 19.4326, lng: -99.1332 }; // Default to Mexico City

    return (
        <div className="h-64 w-full rounded-md overflow-hidden border border-gray-300 shadow-inner mt-2">
            <MapContainer
                center={initialCenter}
                zoom={13}
                scrollWheelZoom={false}
                style={{ height: '100%', width: '100%' }}
            >
                <TileLayer
                    attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                    url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
                />
                <MapAutoCenter position={position} />
                <LocationMarker
                    position={position}
                    setPosition={handleSetPosition}
                    isReadOnly={isReadOnly}
                />
            </MapContainer>
            {!isReadOnly && (
                <p className="text-[10px] text-gray-500 mt-1 pl-1">Haz clic en el mapa para marcar la ubicación</p>
            )}
        </div>
    );
}
