<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    public function __construct(private ReportService $reportService) {}

    public function index(Request $request): Response
    {
        [$from, $to] = $this->resolvePeriod($request);

        return Inertia::render('Owner/Report/Index', [
            'summary'    => $this->reportService->revenueByPeriod($from, $to),
            'chartData'  => $this->reportService->revenueByDay($from, $to),
            'topItems'   => $this->reportService->topMenuItems($from, $to),
            'byCategory' => $this->reportService->revenueByCategory($from, $to),
            'shifts'     => $this->reportService->shiftSummary($from, $to),
            'comparison' => $this->reportService->periodComparison($from, $to),
            'filters'    => [
                'period' => $request->get('period', 'month'),
                'from'   => $from->format('Y-m-d'),
                'to'     => $to->format('Y-m-d'),
            ],
        ]);
    }

    public function exportPdf(Request $request): HttpResponse
    {
        [$from, $to] = $this->resolvePeriod($request);

        $data = [
            'summary'    => $this->reportService->revenueByPeriod($from, $to),
            'topItems'   => $this->reportService->topMenuItems($from, $to),
            'byCategory' => $this->reportService->revenueByCategory($from, $to),
            'shifts'     => $this->reportService->shiftSummary($from, $to),
            'from'       => $from->format('d M Y'),
            'to'         => $to->format('d M Y'),
        ];

        $pdf = Pdf::loadView('reports.pdf', $data)->setPaper('a4');

        return $pdf->download("laporan-{$from->format('Ymd')}-{$to->format('Ymd')}.pdf");
    }

    public function exportCsv(Request $request): HttpResponse
    {
        [$from, $to] = $this->resolvePeriod($request);

        $rows    = [];
        $rows[]  = ['Laporan Keuangan Niti Noto'];
        $rows[]  = ["Periode: {$from->format('d M Y')} s/d {$to->format('d M Y')}"];
        $rows[]  = [];

        // Summary
        $summary = $this->reportService->revenueByPeriod($from, $to);
        $rows[]  = ['RINGKASAN'];
        $rows[]  = ['Revenue', $this->formatCsv($summary['revenue'])];
        $rows[]  = ['Total Expense', $this->formatCsv($summary['expenses'])];
        $rows[]  = ['Laba Bersih', $this->formatCsv($summary['laba_bersih'])];
        $rows[]  = [];

        // Top items
        $rows[]  = ['TOP MENU ITEM'];
        $rows[]  = ['Nama', 'Kategori', 'Total Terjual', 'Total Revenue'];
        foreach ($this->reportService->topMenuItems($from, $to) as $item) {
            $rows[] = [$item['name'], $item['category'], $item['total_qty'], $this->formatCsv($item['total_revenue'])];
        }
        $rows[] = [];

        // Category
        $rows[] = ['REVENUE PER KATEGORI'];
        $rows[] = ['Kategori', 'Total'];
        foreach ($this->reportService->revenueByCategory($from, $to) as $cat) {
            $rows[] = [$cat['category'], $this->formatCsv($cat['total'])];
        }
        $rows[] = [];

        // Shifts
        $rows[] = ['RINGKASAN SHIFT'];
        $rows[] = ['Kasir', 'Mulai', 'Selesai', 'Order', 'Revenue'];
        foreach ($this->reportService->shiftSummary($from, $to) as $s) {
            $rows[] = [$s['cashier'], $s['started_at'], $s['ended_at'], $s['orders'], $this->formatCsv($s['revenue'])];
        }

        $csv = '';
        foreach ($rows as $row) {
            $csv .= implode(',', array_map(fn ($v) => '"' . str_replace('"', '""', $v) . '"', $row)) . "\n";
        }

        $filename = "laporan-{$from->format('Ymd')}-{$to->format('Ymd')}.csv";

        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    private function resolvePeriod(Request $request): array
    {
        $period = $request->get('period', 'month');

        return match ($period) {
            'today'  => [Carbon::today(), Carbon::today()],
            'week'   => [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()],
            'custom' => [
                Carbon::parse($request->get('from', Carbon::today()->format('Y-m-d'))),
                Carbon::parse($request->get('to',   Carbon::today()->format('Y-m-d'))),
            ],
            default  => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
        };
    }

    private function formatCsv(float $value): string
    {
        return number_format($value, 0, ',', '.');
    }
}
