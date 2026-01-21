import { Head, usePage } from '@inertiajs/react';
import { AlertCircle } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';

import { ProductCard } from '@/components/product-card';
import { ShoppingCartDrawer } from '@/components/shopping-cart-drawer';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';

// Helper function to get CSRF token from cookies
function getCsrfToken(): string | null {
    const cookies = document.cookie.split(';');
    for (const cookie of cookies) {
        const [name, value] = cookie.trim().split('=');
        if (name === 'XSRF-TOKEN') {
            return decodeURIComponent(value);
        }
    }
    return null;
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

interface Product {
    id: number;
    name: string;
    price: number | string;
    stockQuantity: number | string;
    minStockQuantity: number | string;
    unit: string;
    updatedAt: string;
}

export default function Dashboard() {
    const [products, setProducts] = useState<Product[]>([]);
    const [loading, setLoading] = useState(true);
    const [addingProductId, setAddingProductId] = useState<number | null>(null);
    const [alert, setAlert] = useState<{ message: string; type: 'error' | 'success' } | null>(null);
    const [cartOpen, setCartOpen] = useState(false);
    const [cartQuantity, setCartQuantity] = useState(0);
    const intervalRef = useRef<NodeJS.Timeout | null>(null);
    // eslint-disable-next-line @typescript-eslint/no-unused-vars
    const { auth } = usePage().props as { auth: { user: { id: number } } };

    const fetchProducts = async () => {
        try {
            const csrfToken = getCsrfToken();
            const headers: HeadersInit = {
                Accept: 'application/json',
            };
            if (csrfToken) {
                headers['X-XSRF-TOKEN'] = csrfToken;
            }

            const response = await fetch('/api/products', {
                headers,
                credentials: 'include',
            });

            if (!response.ok) {
                throw new Error('Failed to fetch products');
            }

            const data = await response.json();
            setProducts(data);
            setLoading(false);
        } catch (error) {
            console.error('Error fetching products:', error);
            setLoading(false);
        }
    };

    const refreshProduct = async (productId: number) => {
        try {
            const csrfToken = getCsrfToken();
            const headers: HeadersInit = {
                Accept: 'application/json',
            };
            if (csrfToken) {
                headers['X-XSRF-TOKEN'] = csrfToken;
            }

            const response = await fetch(`/api/products/${productId}`, {
                headers,
                credentials: 'include',
            });

            if (response.ok) {
                const product = await response.json();
                setProducts((prev) =>
                    prev.map((p) => (p.id === productId ? product : p))
                );
            }
        } catch (error) {
            console.error('Error refreshing product:', error);
        }
    };

    const updateProductStock = (productId: number, stockQuantity: number) => {
        setProducts((prev) =>
            prev.map((p) =>
                p.id === productId
                    ? { ...p, stockQuantity }
                    : p
            )
        );
    };

    // eslint-disable-next-line @typescript-eslint/no-unused-vars
    const getAvailableQuantity = async (productId: number): Promise<number> => {
        try {
            const csrfToken = getCsrfToken();
            const headers: HeadersInit = {
                Accept: 'application/json',
            };
            if (csrfToken) {
                headers['X-XSRF-TOKEN'] = csrfToken;
            }

            const response = await fetch(`/api/products/${productId}/available-quantity`, {
                headers,
                credentials: 'include',
            });

            if (response.ok) {
                const data = await response.json();
                return data.availableQuantity || 0;
            }
            return 0;
        } catch (error) {
            console.error('Error fetching available quantity:', error);
            return 0;
        }
    };

    const fetchCartQuantity = async () => {
        try {
            const csrfToken = getCsrfToken();
            const headers: HeadersInit = {
                Accept: 'application/json',
            };
            if (csrfToken) {
                headers['X-XSRF-TOKEN'] = csrfToken;
            }

            const response = await fetch('/api/cart', {
                headers,
                credentials: 'include',
            });

            if (response.ok) {
                const cart = await response.json();
                const totalQuantity = cart.products?.reduce(
                    (sum: number, item: { quantity: number }) => sum + item.quantity,
                    0
                ) || 0;
                setCartQuantity(totalQuantity);
            }
        } catch (error) {
            console.error('Error fetching cart quantity:', error);
        }
    };

    const handleAddToCart = async (productId: number, quantity: number) => {
        // Refresh product data before adding to cart
        await refreshProduct(productId);

        // Check if product is still available
        const product = products.find((p) => p.id === productId);
        if (product && product.stockQuantity === 0) {
            setAlert({
                message: 'Product is no longer available.',
                type: 'error',
            });
            setTimeout(() => setAlert(null), 5000);
            return;
        }

        setAddingProductId(productId);
        setAlert(null);

        try {
            const csrfToken = getCsrfToken();
            const headers: HeadersInit = {
                'Content-Type': 'application/json',
                Accept: 'application/json',
            };
            if (csrfToken) {
                headers['X-XSRF-TOKEN'] = csrfToken;
            }

            const response = await fetch('/api/cart/add', {
                method: 'POST',
                headers,
                credentials: 'include',
                body: JSON.stringify({
                    productId,
                    quantity,
                }),
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Failed to add product to cart');
            }

            // Refresh all products after adding to cart to ensure consistency
            await fetchProducts();
            await fetchCartQuantity();

            setAlert({
                message: 'Product added to cart successfully!',
                type: 'success',
            });
            setTimeout(() => setAlert(null), 3000);
        } catch (error: unknown) {
            const errorMessage =
                error instanceof Error ? error.message : 'Failed to add product to cart';
            setAlert({
                message: errorMessage,
                type: 'error',
            });
            setTimeout(() => setAlert(null), 5000);

            // Refresh products to get latest stock
            await fetchProducts();
        } finally {
            setAddingProductId(null);
        }
    };

    useEffect(() => {
        // Initial fetch
        fetchProducts();
        fetchCartQuantity();

        // Set up auto-refresh every 3 seconds
        intervalRef.current = setInterval(() => {
            fetchProducts();
            fetchCartQuantity();
        }, 3000);

        // Cleanup interval on unmount
        return () => {
            if (intervalRef.current) {
                clearInterval(intervalRef.current);
            }
        };
    }, []);

    return (
        <AppLayout
            breadcrumbs={breadcrumbs}
            cartQuantity={cartQuantity}
            onCartClick={() => setCartOpen(!cartOpen)}
        >
            <Head title="Dashboard" />
            <ShoppingCartDrawer
                open={cartOpen}
                onOpenChange={setCartOpen}
                onCartUpdate={fetchCartQuantity}
                onProductsRefresh={fetchProducts}
                onProductUpdate={updateProductStock}
            />
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4 md:p-6">
                {alert && (
                    <Alert
                        variant={alert.type === 'error' ? 'destructive' : 'default'}
                        className="mb-2 animate-in fade-in slide-in-from-top-2 duration-300"
                    >
                        <AlertCircle className="h-4 w-4" />
                        <AlertTitle className="font-semibold">
                            {alert.type === 'error' ? 'Error' : 'Success'}
                        </AlertTitle>
                        <AlertDescription className="text-sm">
                            {alert.message}
                        </AlertDescription>
                    </Alert>
                )}

                {loading ? (
                    <div className="flex items-center justify-center min-h-[400px]">
                        <div className="flex flex-col items-center gap-3">
                            <div className="h-8 w-8 animate-spin rounded-full border-4 border-primary border-t-transparent" />
                            <div className="text-muted-foreground text-sm font-medium">
                                Loading products...
                            </div>
                        </div>
                    </div>
                ) : (
                    <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-4 md:gap-6">
                        {products.map((product) => (
                            <ProductCard
                                key={product.id}
                                product={product}
                                onAddToCart={handleAddToCart}
                                isAdding={addingProductId === product.id}
                            />
                        ))}
                    </div>
                )}

                {!loading && products.length === 0 && (
                    <div className="flex items-center justify-center min-h-[400px]">
                        <div className="flex flex-col items-center gap-3 text-center">
                            <div className="text-muted-foreground text-lg font-medium">
                                No products available
                            </div>
                            <div className="text-muted-foreground text-sm">
                                Check back later for new products
                            </div>
                        </div>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
