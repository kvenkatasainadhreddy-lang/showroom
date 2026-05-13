<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Invoice {{ $sale->invoice_no }}</title>

<style>
body {
    font-family: DejaVu Sans, sans-serif;
    font-size: 12px;
    color: #222;
    margin: 0;
    padding: 0;
    background: #fff;
}

/* Container */
.invoice-box {
    max-width: 900px;
    margin: auto;
    padding: 20px;
}

/* Header */
.header {
    border-bottom: 3px solid #185FA5;
    padding-bottom: 10px;
    margin-bottom: 20px;
}

.company-name {
    font-size: 22px;
    font-weight: bold;
    color: #185FA5;
}

.company-details {
    font-size: 11px;
    color: #555;
    margin-top: 4px;
}

.invoice-meta {
    text-align: right;
}

.invoice-title {
    font-size: 18px;
    font-weight: bold;
}

.status {
    margin-top: 5px;
    padding: 3px 10px;
    font-size: 11px;
    border-radius: 20px;
    display: inline-block;
}

.paid { background:#d1fae5; color:#065f46; }
.partial { background:#fef3c7; color:#92400e; }
.unpaid { background:#fee2e2; color:#991b1b; }

/* Grid */
.row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 20px;
}

.box {
    width: 48%;
}

.label {
    font-size: 10px;
    text-transform: uppercase;
    color: #777;
    margin-bottom: 4px;
}

.value {
    font-weight: bold;
}

/* Table */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}

table th {
    background: #185FA5;
    color: #fff;
    font-size: 11px;
    padding: 8px;
    text-align: left;
}

table td {
    border: 1px solid #ddd;
    padding: 8px;
}

table td.right {
    text-align: right;
}

/* Totals */
.totals {
    width: 300px;
    margin-left: auto;
    margin-top: 20px;
}

.totals td {
    padding: 6px;
}

.total-final {
    font-size: 14px;
    font-weight: bold;
    border-top: 2px solid #000;
}

/* Footer */
.footer {
    margin-top: 40px;
    font-size: 11px;
    color: #777;
    border-top: 1px solid #ddd;
    padding-top: 10px;
}
</style>
</head>

<body>

<div class="invoice-box">

    <!-- HEADER -->
    <div class="row header">
        <div>
            <div class="company-name">Showroom ERP</div>
            <div class="company-details">
                {{ $sale->branch?->address }}<br>
                {{ $sale->branch?->phone }}
            </div>
        </div>

        <div class="invoice-meta">
            <div class="invoice-title">TAX INVOICE</div>
            <div><strong>No:</strong> {{ $sale->invoice_no }}</div>
            <div><strong>Date:</strong> {{ $sale->sale_date?->format('d M Y') }}</div>

            <div class="status {{ $sale->payment_status }}">
                {{ ucfirst($sale->payment_status) }}
            </div>
        </div>
    </div>

    <!-- BILLING -->
    <div class="row">
        <div class="box">
            <div class="label">Bill To</div>
            <div class="value">
                {{ $sale->customer?->name ?? $sale->customer_name }}
            </div>
            <div>{{ $sale->customer?->phone }}</div>
            <div>{{ $sale->customer?->email }}</div>
            <div>{{ $sale->customer?->address }}</div>
        </div>

        <div class="box">
            <div class="label">Sale Details</div>
            <div><strong>Branch:</strong> {{ $sale->branch?->name }}</div>
            <div><strong>Sold By:</strong> {{ $sale->soldBy?->name }}</div>
            <div><strong>Type:</strong> {{ ucfirst($sale->sale_type) }}</div>
        </div>
    </div>

    <!-- VEHICLE -->
    @if($sale->sale_type === 'vehicle' && $sale->vehicleStock)
    @php $vs = $sale->vehicleStock; @endphp

    <table>
        <tr>
            <th colspan="2">Vehicle Details</th>
        </tr>
        <tr>
            <td>Brand / Model</td>
            <td>{{ $vs->variant?->vehicleModel?->brand?->name }} {{ $vs->variant?->vehicleModel?->name }}</td>
        </tr>
        <tr>
            <td>Variant</td>
            <td>{{ $vs->variant?->name }}</td>
        </tr>
        <tr>
            <td>Color</td>
            <td>{{ $vs->color }}</td>
        </tr>
        <tr>
            <td>Chassis No</td>
            <td>{{ $vs->chassis_number }}</td>
        </tr>
        <tr>
            <td>Engine No</td>
            <td>{{ $vs->engine_number }}</td>
        </tr>
    </table>

    @else

    <!-- ITEMS -->
    <table>
        <thead>
        <tr>
            <th>#</th>
            <th>Item</th>
            <th>Qty</th>
            <th>Price</th>
            <th>Discount</th>
            @if ($sale->exchange>0)
            <th>Exchange</th>
            @endif
            <th>Total</th>
        </tr>
        </thead>

        <tbody>
        @foreach($sale->items as $i => $item)
        <tr>
            <td>{{ $i+1 }}</td>
            <td>{{ $item->product?->name }}</td>
            <td>{{ $item->quantity }}</td>
            <td class="right">₹{{ number_format($item->unit_price,2) }}</td>
            <td class="right">₹{{ number_format($item->discount,2) }}</td>
            @if ($sale->exchange>0)
            <td class="right">₹{{ number_format($item->exchange,2) }}</td>
            @endif
            <td class="right">₹{{ number_format($item->total,2) }}</td>
        </tr>
        @endforeach
        </tbody>
    </table>

    @endif

    <!-- TOTALS -->
    <table class="totals">
        <tr>
            <td>Subtotal</td>
            <td class="right">₹{{ number_format($sale->subtotal ?? $sale->total,2) }}</td>
        </tr>

        <tr>
            <td>Discount</td>
            <td class="right">₹{{ number_format($sale->discount,2) }}</td>
        </tr>

        @if ($sale->exchange>0)
        <tr> 
          <td>Exchange</td>
            <td class="right">₹{{ number_format($sale->exchange,2) }}</td>
        </tr>
        
        @endif

        <tr>
            <td>Tax</td>
            <td class="right">₹{{ number_format($sale->tax,2) }}</td>
        </tr>

        <tr class="total-final">
            <td>Total</td>
            <td class="right">₹{{ number_format($sale->total,2) }}</td>
        </tr>

        <tr>
            <td>Paid</td>
            <td class="right">₹{{ number_format($sale->amount_paid,2) }}</td>
        </tr>

        <tr>
            <td><strong>Balance</strong></td>
            <td class="right"><strong>₹{{ number_format($sale->balance_due,2) }}</strong></td>
        </tr>
    </table>

    <!-- NOTES -->
    @if($sale->notes)
    <div style="margin-top:20px;">
        <strong>Notes:</strong> {{ $sale->notes }}
    </div>
    @endif

    <!-- FOOTER -->
    <div class="footer">
        Thank you for your business! <br>
        Printed on {{ now()->format('d M Y H:i') }}
    </div>

</div>

</body>
</html>