import { useEffect, useState } from 'react';

import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter, CardHeader } from '@/components/ui/card';
import { ShoppingCart, Minus, Plus } from 'lucide-react';

interface Product {
    id: number;
    name: string;
    price: number | string;
    stockQuantity: number | string;
    minStockQuantity: number | string;
    unit: string;
    updatedAt: string;
}

interface ProductCardProps {
    product: Product;
    onAddToCart: (productId: number, quantity: number) => Promise<void>;
    isAdding?: boolean;
}

export function ProductCard({ product, onAddToCart, isAdding = false }: ProductCardProps) {
    // Safely convert price and stockQuantity to numbers
    const price = typeof product.price === 'string' ? parseFloat(product.price) : product.price;
    const stockQuantity = typeof product.stockQuantity === 'string' ? parseInt(product.stockQuantity, 10) : product.stockQuantity;
    const minStockQuantity = typeof product.minStockQuantity === 'string' ? parseInt(product.minStockQuantity, 10) : product.minStockQuantity;

    const maxQuantity = Math.max(1, Math.floor(stockQuantity / 2));
    const [quantity, setQuantity] = useState(1);
    const [isDisabled, setIsDisabled] = useState(stockQuantity === 0);

    useEffect(() => {
        setIsDisabled(stockQuantity === 0);
        // Reset quantity if it exceeds max when stock changes
        setQuantity((prevQuantity) => {
            const newMax = Math.max(1, Math.floor(stockQuantity / 2));
            return prevQuantity > newMax ? newMax : prevQuantity;
        });
    }, [stockQuantity]);

    const handleDecrement = () => {
        if (quantity > 1) {
            setQuantity(quantity - 1);
        }
    };

    const handleIncrement = () => {
        if (quantity < maxQuantity) {
            setQuantity(quantity + 1);
        }
    };

    const handleAddToCart = async () => {
        if (isDisabled || isAdding) return;

        try {
            await onAddToCart(product.id, quantity);
            // Reset quantity to default (1) after successful add
            setQuantity(1);
        } catch (error) {
            // Error handling is done in parent component
        }
    };

    return (
        <Card className="flex flex-col h-full transition-all duration-200 hover:shadow-md hover:border-primary/20">
            <CardHeader className="flex-1 flex items-center justify-center min-h-[120px] px-6 py-8">
                <h3 className="text-lg font-semibold text-center text-foreground break-words">
                    {product.name}
                </h3>
            </CardHeader>
            <CardContent className="flex flex-col gap-3 px-6 py-4">
                <div className="text-center">
                    <span className="text-3xl font-bold text-primary">
                        ${isNaN(price) ? '0.00' : price.toFixed(2)}
                    </span>
                    <span className="text-sm text-muted-foreground ml-2">
                        / {product.unit}
                    </span>
                </div>
            </CardContent>
            <CardFooter className="flex flex-col gap-3 pt-4 pb-6 px-6">
                <div className="text-sm text-muted-foreground text-center w-full">
                    <span className="inline-block">
                        Stock:{' '}
                        <span
                            className={`font-semibold ${stockQuantity === 0
                                    ? 'text-destructive'
                                    : stockQuantity <= minStockQuantity
                                        ? 'text-yellow-600 dark:text-yellow-500'
                                        : 'text-foreground'
                                }`}
                        >
                            {stockQuantity}
                        </span>{' '}
                        {product.unit}
                    </span>
                </div>
                <div className="flex items-center gap-2 w-full">
                    <div className="flex items-center border border-input rounded-lg overflow-hidden bg-background mt-2 w-full justify-center">
                        <Button
                            type="button"
                            variant="ghost"
                            size="icon"
                            className="h-9 w-9 rounded-none hover:bg-accent"
                            onClick={handleDecrement}
                            disabled={quantity <= 1 || isDisabled || isAdding}
                        >
                            <Minus className="h-4 w-4" />
                        </Button>
                        <div className="h-9 min-w-[3rem] flex items-center justify-center px-3 border-x border-input bg-background">
                            <span className="text-sm font-medium text-foreground">
                                {quantity}
                            </span>
                        </div>
                        <Button
                            type="button"
                            variant="ghost"
                            size="icon"
                            className="h-9 w-9 rounded-none hover:bg-accent"
                            onClick={handleIncrement}
                            disabled={quantity >= maxQuantity || isDisabled || isAdding}
                        >
                            <Plus className="h-4 w-4" />
                        </Button>
                    </div>
                </div>
                <div className="flex items-center gap-2 w-full">
                    <Button
                        onClick={handleAddToCart}
                        disabled={isDisabled || isAdding}
                        className="w-full mt-2 transition-all duration-200"
                        variant={isDisabled ? 'outline' : 'default'}
                        size="default"
                    >
                        <ShoppingCart className="mr-2 h-4 w-4" />
                        {isAdding ? 'Adding...' : isDisabled ? 'Out of Stock' : 'Add to Cart'}
                    </Button>
                </div>
            </CardFooter>
        </Card>
    );
}
