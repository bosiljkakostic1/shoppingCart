Daily Sales Report - {{ $date }}

Dear Admin,

Please find below the daily sales report for {{ $date }}:

SUMMARY:
--------
Total Revenue: ${{ number_format($totalRevenue, 2) }}
Total Items Sold: {{ $totalItemsSold }}
Total Orders: {{ $salesData['totalOrders'] ?? 0 }}

PRODUCTS SOLD:
--------------
@if(count($products) > 0)
@foreach($products as $product)
- {{ $product['name'] }}: {{ $product['quantity'] }} {{ $product['unit'] }} - ${{ number_format($product['revenue'], 2) }}
@endforeach
@else
No products were sold today.
@endif

Thank you,
Shopping Cart System
