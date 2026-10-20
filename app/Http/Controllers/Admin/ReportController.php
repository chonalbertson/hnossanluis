<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->hasPermission('reports.view'), 403);

        $dateFrom = $request->input('date_from', now()->subDays(90)->toDateString());
        $dateTo = $request->input('date_to', now()->toDateString());

        $baseQuery = Quote::query()
            ->when($dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->whereDate('created_at', '<=', $dateTo));

        $totalQuotes = (clone $baseQuery)->count();
        $respondedQuotes = (clone $baseQuery)->whereIn('status', ['respondida', 'aceptada'])->count();
        $acceptedQuotes = (clone $baseQuery)->where('status', 'aceptada')->count();
        $totalAmountOfferedPen = (float) (clone $baseQuery)
            ->where('currency', 'PEN')
            ->whereNotNull('total_offered')
            ->sum('total_offered');

        $responseRate = $totalQuotes > 0 ? round(($respondedQuotes / $totalQuotes) * 100, 1) : 0.0;

        $quotesByStatus = (clone $baseQuery)
            ->select('status', DB::raw('COUNT(*) as total'), DB::raw('SUM(COALESCE(total_offered, 0)) as sum_offered'))
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        $quotesBySeller = (clone $baseQuery)
            ->select(
                'assigned_seller_id',
                DB::raw('COUNT(*) as total_assigned'),
                DB::raw("SUM(CASE WHEN status IN ('respondida', 'aceptada') THEN 1 ELSE 0 END) as responded_count"),
                DB::raw('SUM(COALESCE(total_offered, 0)) as sum_offered')
            )
            ->groupBy('assigned_seller_id')
            ->get();

        $sellerUsers = User::query()
            ->whereIn('id', $quotesBySeller->pluck('assigned_seller_id')->filter())
            ->get()
            ->keyBy('id');

        $topProducts = QuoteItem::query()
            ->whereHas('quote', function ($q) use ($dateFrom, $dateTo) {
                $q->when($dateFrom, fn ($sq) => $sq->whereDate('created_at', '>=', $dateFrom))
                    ->when($dateTo, fn ($sq) => $sq->whereDate('created_at', '<=', $dateTo));
            })
            ->select([
                'sku_snapshot',
                'product_name_snapshot',
                'sale_unit_snapshot',
                DB::raw('COUNT(DISTINCT quote_id) as quotes_count'),
                DB::raw('SUM(quantity) as total_quantity'),
            ])
            ->groupBy('sku_snapshot', 'product_name_snapshot', 'sale_unit_snapshot')
            ->orderByDesc('quotes_count')
            ->orderByDesc('total_quantity')
            ->limit(15)
            ->get();

        return view('admin.reports.index', [
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'totalQuotes' => $totalQuotes,
            'respondedQuotes' => $respondedQuotes,
            'acceptedQuotes' => $acceptedQuotes,
            'responseRate' => $responseRate,
            'totalAmountOfferedPen' => $totalAmountOfferedPen,
            'statuses' => Quote::availableStatuses(),
            'quotesByStatus' => $quotesByStatus,
            'quotesBySeller' => $quotesBySeller,
            'sellerUsers' => $sellerUsers,
            'topProducts' => $topProducts,
        ]);
    }

    public function export(Request $request): Response
    {
        abort_unless($request->user()?->hasPermission('reports.view'), 403);

        $type = $request->string('type', 'top_products')->toString();
        $dateFrom = $request->input('date_from', now()->subDays(90)->toDateString());
        $dateTo = $request->input('date_to', now()->toDateString());

        $stream = fopen('php://temp', 'r+');
        fwrite($stream, "\xEF\xBB\xBF");

        if ($type === 'quotes_by_status') {
            fputcsv($stream, ['estado_codigo', 'estado_nombre', 'cantidad_cotizaciones', 'monto_ofertado_acumulado']);
            $rows = Quote::query()
                ->when($dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $dateFrom))
                ->when($dateTo, fn ($q) => $q->whereDate('created_at', '<=', $dateTo))
                ->select('status', DB::raw('COUNT(*) as total'), DB::raw('SUM(COALESCE(total_offered, 0)) as sum_offered'))
                ->groupBy('status')
                ->get();

            foreach ($rows as $r) {
                fputcsv($stream, [
                    $r->status,
                    Quote::availableStatuses()[$r->status] ?? $r->status,
                    $r->total,
                    number_format((float) $r->sum_offered, 2, '.', ''),
                ]);
            }
            $filename = sprintf('informe-cotizaciones-estado-%s.csv', now()->format('Ymd'));
        } else {
            fputcsv($stream, ['sku', 'producto', 'unidad_venta', 'cotizaciones_distintas', 'cantidad_total_solicitada']);
            $rows = QuoteItem::query()
                ->whereHas('quote', function ($q) use ($dateFrom, $dateTo) {
                    $q->when($dateFrom, fn ($sq) => $sq->whereDate('created_at', '>=', $dateFrom))
                        ->when($dateTo, fn ($sq) => $sq->whereDate('created_at', '<=', $dateTo));
                })
                ->select([
                    'sku_snapshot',
                    'product_name_snapshot',
                    'sale_unit_snapshot',
                    DB::raw('COUNT(DISTINCT quote_id) as quotes_count'),
                    DB::raw('SUM(quantity) as total_quantity'),
                ])
                ->groupBy('sku_snapshot', 'product_name_snapshot', 'sale_unit_snapshot')
                ->orderByDesc('quotes_count')
                ->orderByDesc('total_quantity')
                ->get();

            foreach ($rows as $r) {
                fputcsv($stream, [
                    $r->sku_snapshot,
                    $r->product_name_snapshot,
                    $r->sale_unit_snapshot,
                    $r->quotes_count,
                    number_format((float) $r->total_quantity, 2, '.', ''),
                ]);
            }
            $filename = sprintf('informe-productos-mas-cotizados-%s.csv', now()->format('Ymd'));
        }

        rewind($stream);
        $csv = stream_get_contents($stream) ?: '';
        fclose($stream);

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => sprintf('attachment; filename="%s"', $filename),
        ]);
    }
}
