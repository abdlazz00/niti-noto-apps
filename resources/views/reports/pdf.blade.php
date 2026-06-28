<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Helvetica', sans-serif; font-size: 12px; color: #1e293b; padding: 32px; }
    h1 { font-size: 20px; font-weight: bold; color: #92400e; margin-bottom: 4px; }
    .subtitle { font-size: 11px; color: #64748b; margin-bottom: 24px; }
    h2 { font-size: 13px; font-weight: bold; color: #334155; border-bottom: 1px solid #e2e8f0; padding-bottom: 4px; margin-bottom: 10px; margin-top: 20px; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
    th { background: #f8fafc; text-align: left; padding: 6px 8px; font-size: 10px; font-weight: bold; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; }
    td { padding: 6px 8px; border-bottom: 1px solid #f1f5f9; font-size: 11px; }
    .summary-table { width: 100%; margin-bottom: 20px; border-collapse: collapse; }
    .summary-card { border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; }
    .summary-label { font-size: 9px; text-transform: uppercase; letter-spacing: 0.05em; color: #94a3b8; margin-bottom: 4px; }
    .summary-value { font-size: 16px; font-weight: bold; color: #1e293b; }
    .amber { color: #d97706; }
    .green { color: #16a34a; }
    .footer { margin-top: 32px; font-size: 9px; color: #94a3b8; text-align: center; }
</style>
</head>
<body>
    <h1>Laporan Keuangan — Niti Noto</h1>
    <p class="subtitle">Periode: {{ $from }} s/d {{ $to }} · Dicetak: {{ now()->format('d M Y H:i') }}</p>

    <!-- Summary cards -->
    <table class="summary-table">
        <tr>
            <td style="width: 33%; border: none; padding: 0 8px 0 0;">
                <div class="summary-card">
                    <div class="summary-label">Revenue</div>
                    <div class="summary-value amber">Rp {{ number_format($summary['revenue'], 0, ',', '.') }}</div>
                </div>
            </td>
            <td style="width: 33%; border: none; padding: 0 8px;">
                <div class="summary-card">
                    <div class="summary-label">Total Expense</div>
                    <div class="summary-value">Rp {{ number_format($summary['expenses'], 0, ',', '.') }}</div>
                </div>
            </td>
            <td style="width: 33%; border: none; padding: 0 0 0 8px;">
                <div class="summary-card">
                    <div class="summary-label">Laba Bersih</div>
                    <div class="summary-value green">Rp {{ number_format($summary['laba_bersih'], 0, ',', '.') }}</div>
                </div>
            </td>
        </tr>
    </table>

    <!-- Top menu items -->
    <h2>Top Menu Item</h2>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Menu</th>
                <th>Kategori</th>
                <th style="text-align:right">Terjual</th>
                <th style="text-align:right">Revenue</th>
            </tr>
        </thead>
        <tbody>
            @foreach($topItems as $i => $item)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $item['name'] }}</td>
                <td>{{ $item['category'] }}</td>
                <td style="text-align:right">{{ $item['total_qty'] }}</td>
                <td style="text-align:right">Rp {{ number_format($item['total_revenue'], 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Revenue by category -->
    <h2>Revenue per Kategori</h2>
    <table>
        <thead>
            <tr>
                <th>Kategori</th>
                <th style="text-align:right">Total Revenue</th>
            </tr>
        </thead>
        <tbody>
            @foreach($byCategory as $cat)
            <tr>
                <td>{{ $cat['category'] }}</td>
                <td style="text-align:right">Rp {{ number_format($cat['total'], 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Shift summary -->
    <h2>Ringkasan Shift</h2>
    <table>
        <thead>
            <tr>
                <th>Kasir</th>
                <th>Mulai</th>
                <th>Selesai</th>
                <th style="text-align:right">Order</th>
                <th style="text-align:right">Revenue</th>
            </tr>
        </thead>
        <tbody>
            @foreach($shifts as $shift)
            <tr>
                <td>{{ $shift['cashier'] }}</td>
                <td>{{ $shift['started_at'] }}</td>
                <td>{{ $shift['ended_at'] }}</td>
                <td style="text-align:right">{{ $shift['orders'] }}</td>
                <td style="text-align:right">Rp {{ number_format($shift['revenue'], 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">Niti Noto Coffee Shop · Laporan ini digenerate otomatis oleh sistem</div>
</body>
</html>
