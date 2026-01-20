import { type ReactNode } from 'react';

import AppLayoutTemplate from '@/layouts/app/app-sidebar-layout';
import { type BreadcrumbItem } from '@/types';

interface AppLayoutProps {
    children: ReactNode;
    breadcrumbs?: BreadcrumbItem[];
    cartQuantity?: number;
    onCartClick?: () => void;
}

export default ({
    children,
    breadcrumbs,
    cartQuantity,
    onCartClick,
    ...props
}: AppLayoutProps) => (
    <AppLayoutTemplate
        breadcrumbs={breadcrumbs}
        cartQuantity={cartQuantity}
        onCartClick={onCartClick}
        {...props}
    >
        {children}
    </AppLayoutTemplate>
);
