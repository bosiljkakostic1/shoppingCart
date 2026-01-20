import * as React from 'react';

interface ShoppingCartIconProps {
    className?: string;
    size?: number;
}

export function ShoppingCartIcon({ className, size = 20 }: ShoppingCartIconProps) {
    return (
        <svg
            xmlns="http://www.w3.org/2000/svg"
            width={size}
            height={size}
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            strokeWidth="2"
            strokeLinecap="round"
            strokeLinejoin="round"
            className={className}
        >
            {/* Shopping cart icon - basket style */}
            <path d="M5 7h15l-1.5 9H6.5L5 7z" />
            <path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
            <circle cx="9" cy="20" r="1" />
            <circle cx="19" cy="20" r="1" />
        </svg>
    );
}
