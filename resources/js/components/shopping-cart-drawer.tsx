import { Trash2, Minus, Plus } from 'lucide-react';
import { useEffect, useState } from 'react';

import { ShoppingCartIcon } from '@/components/shopping-cart-icon';
import { Button } from '@/components/ui/button';
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetHeader,
    SheetTitle,
} from '@/components/ui/sheet';

interface CartItem {
    id: number;
    productId: number;
    product: {
        id: number;
        name: string;
        unit: string;
        price: number;
    };
    quantity: number;
}

interface ShoppingCartData {
    id: number;
    sum: number | string;
    state: string;
    products: CartItem[];
}

interface ShoppingCartDrawerProps {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    onCartUpdate?: () => void;
    onProductsRefresh?: () => void;
    onProductUpdate?: (productId: number, stockQuantity: number) => void;
}

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

export function ShoppingCartDrawer({ open, onOpenChange, onCartUpdate, onProductsRefresh, onProductUpdate }: ShoppingCartDrawerProps) {
    const [cart, setCart] = useState<ShoppingCartData | null>(null);
    const [loading, setLoading] = useState(false);
    const [finishingOrder, setFinishingOrder] = useState(false);

    const fetchCart = async () => {
        setLoading(true);
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
                const data = await response.json();
                setCart(data);
            }
        } catch (error) {
            console.error('Error fetching cart:', error);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        if (open) {
            fetchCart();
        }
    }, [open]);


    const handleFinishOrder = async () => {
        if (!cart) return;

        setFinishingOrder(true);
        try {
            const csrfToken = getCsrfToken();
            const headers: HeadersInit = {
                'Content-Type': 'application/json',
                Accept: 'application/json',
            };
            if (csrfToken) {
                headers['X-XSRF-TOKEN'] = csrfToken;
            }

            const response = await fetch('/api/cart/finish', {
                method: 'POST',
                headers,
                credentials: 'include',
            });

            if (response.ok) {
                await fetchCart();
                if (onCartUpdate) {
                    onCartUpdate();
                }
                if (onProductsRefresh) {
                    onProductsRefresh();
                }
                // Close drawer after finishing order
                onOpenChange(false);
            }
        } catch (error) {
            console.error('Error finishing order:', error);
        } finally {
            setFinishingOrder(false);
        }
    };

    // Since we ensure only one record per product, we can use items directly
    const cartItems = cart?.products || [];

    const handleUpdateQuantity = async (cartProductId: number, newQuantity: number) => {
        if (newQuantity < 1) {
            return;
        }

        try {
            const csrfToken = getCsrfToken();
            const headers: HeadersInit = {
                'Content-Type': 'application/json',
                Accept: 'application/json',
            };
            if (csrfToken) {
                headers['X-XSRF-TOKEN'] = csrfToken;
            }

            const response = await fetch(`/api/cart/products/${cartProductId}`, {
                method: 'PUT',
                headers,
                credentials: 'include',
                body: JSON.stringify({ quantity: newQuantity }),
            });

            if (response.ok) {
                const data = await response.json();
                await fetchCart();
                
                // Update the specific product's stock quantity if provided
                if (data.updatedProduct && onProductUpdate) {
                    onProductUpdate(data.updatedProduct.id, data.updatedProduct.stockQuantity);
                }
                
                if (onCartUpdate) {
                    onCartUpdate();
                }
                if (onProductsRefresh) {
                    onProductsRefresh();
                }
            } else {
                const data = await response.json();
                alert(data.message || 'Failed to update quantity');
            }
        } catch (error) {
            console.error('Error updating quantity:', error);
        }
    };

    const handleRemoveProduct = async (cartProductId: number) => {
        try {
            const csrfToken = getCsrfToken();
            const headers: HeadersInit = {
                Accept: 'application/json',
            };
            if (csrfToken) {
                headers['X-XSRF-TOKEN'] = csrfToken;
            }

            const response = await fetch(`/api/cart/products/${cartProductId}`, {
                method: 'DELETE',
                headers,
                credentials: 'include',
            });

            if (response.ok) {
                const data = await response.json();
                
                // Update the specific product's stock quantity if provided
                if (data.updatedProduct && onProductUpdate) {
                    onProductUpdate(data.updatedProduct.id, data.updatedProduct.stockQuantity);
                }
            }

            await fetchCart();
            if (onCartUpdate) {
                onCartUpdate();
            }
            if (onProductsRefresh) {
                onProductsRefresh();
            }
        } catch (error) {
            console.error('Error removing product:', error);
        }
    };

    return (
        <Sheet open={open} onOpenChange={onOpenChange}>
            <SheetContent
                side="right"
                className="w-[420px] sm:w-[420px]"
            >
                <SheetHeader>
                    <SheetTitle className="text-lg font-semibold">
                        Shopping Cart #{cart?.id || ''}
                    </SheetTitle>
                    <SheetDescription className="sr-only">
                        Shopping cart items and order summary
                    </SheetDescription>
                </SheetHeader>

                <div className="flex-1 overflow-y-auto py-4">
                    {loading ? (
                        <div className="flex items-center justify-center py-8">
                            <div className="text-muted-foreground text-sm">Loading cart...</div>
                        </div>
                    ) : cartItems.length === 0 ? (
                        <div className="flex flex-col items-center justify-center py-12">
                            <ShoppingCartIcon size={48} className="text-muted-foreground mb-4" />
                            <p className="text-muted-foreground text-sm">Your cart is empty</p>
                        </div>
                    ) : (
                        <div className="space-y-4">
                            {cartItems.map((item) => (
                                <div
                                    key={item.id}
                                    className="flex items-center justify-between rounded-lg border p-4"
                                >
                                    <div className="flex-1">
                                        <div className="font-medium">{item.product.name}</div>
                                        <div className="text-muted-foreground text-sm">
                                            {item.quantity} {item.product.unit} × $
                                            {(
                                                typeof item.product.price === 'string'
                                                    ? parseFloat(item.product.price)
                                                    : item.product.price
                                            ).toFixed(2)}{' '}
                                            per {item.product.unit}
                                        </div>
                                        <div className="text-muted-foreground text-sm font-medium mt-1">
                                            Total: $
                                            {(
                                                (typeof item.product.price === 'string'
                                                    ? parseFloat(item.product.price)
                                                    : item.product.price) * item.quantity
                                            ).toFixed(2)}
                                        </div>
                                    </div>
                                    <div className="flex items-center gap-2">
                                        {/* Quantity controls */}
                                        <div className="flex items-center gap-1 border rounded-md">
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                                className="h-8 w-8"
                                                onClick={() => handleUpdateQuantity(item.id, item.quantity - 1)}
                                                disabled={item.quantity <= 1}
                                            >
                                                <Minus className="h-4 w-4" />
                                            </Button>
                                            <span className="px-3 py-1 text-sm font-medium min-w-[2rem] text-center">
                                                {item.quantity}
                                            </span>
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                                className="h-8 w-8"
                                                onClick={() => handleUpdateQuantity(item.id, item.quantity + 1)}
                                            >
                                                <Plus className="h-4 w-4" />
                                            </Button>
                                        </div>
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            className="h-8 w-8 text-destructive hover:text-destructive"
                                            onClick={() => handleRemoveProduct(item.id)}
                                        >
                                            <Trash2 className="h-4 w-4" />
                                        </Button>
                                    </div>
                                </div>
                            ))}
                        </div>
                    )}
                </div>

                {cartItems.length > 0 && (
                    <div className="border-t pt-4">
                        <div className="flex items-center justify-between mb-4 px-4">
                            <span className="text-lg font-semibold">Total:</span>
                            <span className="text-lg font-bold">
                                $
                                {cart?.sum
                                    ? (
                                          typeof cart.sum === 'string'
                                              ? parseFloat(cart.sum)
                                              : cart.sum
                                      ).toFixed(2)
                                    : '0.00'}
                            </span>
                        </div>
                        <div className="px-4 pb-4">
                            <Button
                                onClick={handleFinishOrder}
                                disabled={finishingOrder || cart?.state !== 'active'}
                                className="w-full"
                                size="lg"
                            >
                                {finishingOrder ? 'Processing...' : 'Finish Order'}
                            </Button>
                        </div>
                    </div>
                )}
            </SheetContent>
        </Sheet>
    );
}
