import { Breadcrumbs } from '@/components/breadcrumbs';
import { ShoppingCartIcon } from '@/components/shopping-cart-icon';
import { SidebarTrigger } from '@/components/ui/sidebar';
import { type BreadcrumbItem as BreadcrumbItemType } from '@/types';

interface AppSidebarHeaderProps {
    breadcrumbs?: BreadcrumbItemType[];
    cartQuantity?: number;
    onCartClick?: () => void;
}

export function AppSidebarHeader({
    breadcrumbs = [],
    cartQuantity = 0,
    onCartClick,
}: AppSidebarHeaderProps) {
    return (
        <header className="flex h-16 shrink-0 items-center justify-between gap-2 border-b border-sidebar-border/50 px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4">
            <div className="flex items-center gap-2">
                <SidebarTrigger className="-ml-1" />
                <Breadcrumbs breadcrumbs={breadcrumbs} />
            </div>
            {onCartClick && (
                <button
                    onClick={onCartClick}
                    className="relative flex items-center justify-center rounded-md p-2 hover:bg-accent transition-colors"
                    aria-label="Shopping cart"
                >
                    <ShoppingCartIcon size={20} />
                    {cartQuantity > 0 && (
                        <span className="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-primary text-primary-foreground text-xs font-bold">
                            {cartQuantity > 99 ? '99+' : cartQuantity}
                        </span>
                    )}
                </button>
            )}
        </header>
    );
}
