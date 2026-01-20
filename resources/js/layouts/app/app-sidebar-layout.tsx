import { type PropsWithChildren } from 'react';

import { AppContent } from '@/components/app-content';
import { AppShell } from '@/components/app-shell';
import { AppSidebar } from '@/components/app-sidebar';
import { AppSidebarHeader } from '@/components/app-sidebar-header';
import { type BreadcrumbItem } from '@/types';

interface AppSidebarLayoutProps {
    children: React.ReactNode;
    breadcrumbs?: BreadcrumbItem[];
    cartQuantity?: number;
    onCartClick?: () => void;
}

export default function AppSidebarLayout({
    children,
    breadcrumbs = [],
    cartQuantity = 0,
    onCartClick,
}: AppSidebarLayoutProps) {
    return (
        <AppShell variant="sidebar">
            <AppSidebar />
            <AppContent variant="sidebar" className="overflow-x-hidden">
                <AppSidebarHeader
                    breadcrumbs={breadcrumbs}
                    cartQuantity={cartQuantity}
                    onCartClick={onCartClick}
                />
                {children}
            </AppContent>
        </AppShell>
    );
}
