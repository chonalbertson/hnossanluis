<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Brand;
use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductImportBatch;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\SecurityIncident;
use App\Models\SecurityScan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->canAccessAdminPanel(), 403);

        $quotesByStatus = Quote::query()
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();

        $totalQuotes = (int) array_sum($quotesByStatus);
        $pendingQuotes = (int) (($quotesByStatus['nueva'] ?? 0)
            + ($quotesByStatus['en_revision'] ?? 0)
            + ($quotesByStatus['pendiente_info'] ?? 0)
            + ($quotesByStatus['en_preparacion'] ?? 0));
        $respondedQuotes = (int) (($quotesByStatus['respondida'] ?? 0) + ($quotesByStatus['aceptada'] ?? 0));

        $kpis = [
            'published_products' => Product::query()->where('status', 'published')->count(),
            'total_products' => Product::query()->count(),
            'on_request_products' => Product::query()->where('availability_status', 'on_request')->count(),
            'total_quotes' => $totalQuotes,
            'pending_quotes' => $pendingQuotes,
            'new_quotes_today' => Quote::query()->whereBetween('created_at', [now()->startOfDay(), now()->endOfDay()])->count(),
            'responded_quotes' => $respondedQuotes,
            'total_customers' => Customer::query()->count(),
            'completed_imports' => ProductImportBatch::query()->where('status', 'completed')->count(),
            'new_contact_messages' => ContactMessage::query()->where('status', 'nuevo')->count(),
            'total_contact_messages' => ContactMessage::query()->count(),
            'security_pending_incidents' => SecurityIncident::query()->whereIn('status', ['blocked', 'quarantined'])->count(),
            'security_quarantined_files' => SecurityIncident::query()->whereNotNull('quarantine_path')->count(),
            'latest_security_scan' => SecurityScan::query()->latest()->first(),
        ];

        $recentQuotes = Quote::query()
            ->with(['customer', 'assignedSeller', 'items'])
            ->latest()
            ->limit(7)
            ->get();

        $topQuotedProducts = QuoteItem::query()
            ->select([
                'product_id',
                'sku_snapshot',
                'product_name_snapshot',
                DB::raw('COUNT(*) as quotes_count'),
                DB::raw('SUM(quantity) as total_quantity'),
            ])
            ->groupBy('product_id', 'sku_snapshot', 'product_name_snapshot')
            ->orderByDesc('quotes_count')
            ->orderByDesc('total_quantity')
            ->limit(6)
            ->get();

        $topQuotedProductModels = Product::query()
            ->with('primaryImage')
            ->whereIn('id', $topQuotedProducts->pluck('product_id')->filter())
            ->get()
            ->keyBy('id');

        $recentActivity = ActivityLog::query()
            ->with('user')
            ->latest()
            ->limit(6)
            ->get();

        $monthStart = now()->startOfMonth();
        $yearMonthExpr = DB::connection()->getDriverName() === 'sqlite'
            ? "strftime('%Y-%m', created_at)"
            : "DATE_FORMAT(created_at, '%Y-%m')";

        $monthlyCounts = Quote::query()
            ->where('created_at', '>=', $monthStart->copy()->subMonths(5))
            ->selectRaw("{$yearMonthExpr} as ym, COUNT(*) as total")
            ->groupBy('ym')
            ->pluck('total', 'ym');

        $quotesPerMonth = collect(range(5, 0))->map(function (int $monthsAgo) use ($monthStart, $monthlyCounts): array {
            $month = $monthStart->copy()->subMonths($monthsAgo);
            $ymKey = $month->format('Y-m');

            return [
                'label' => ucfirst($month->locale('es')->translatedFormat('M')),
                'total' => (int) ($monthlyCounts[$ymKey] ?? 0),
                'is_current' => $monthsAgo === 0,
            ];
        })->all();

        $currentMonthQuotes = end($quotesPerMonth)['total'];
        $previousMonthQuotes = $quotesPerMonth[count($quotesPerMonth) - 2]['total'];

        $productsByCategory = Category::query()
            ->whereNull('parent_id')
            ->withCount(['products' => fn ($q) => $q->where('status', 'published')])
            ->orderByDesc('products_count')
            ->limit(8)
            ->get(['id', 'name', 'slug', 'image_path']);

        $latestProducts = Product::query()
            ->with(['primaryImage', 'brand', 'category'])
            ->latest()
            ->limit(8)
            ->get();

        $productsWithImages = Product::query()->whereHas('images')->count();

        return view('admin.dashboard', [
            'kpis' => $kpis + [
                'brands' => Brand::query()->where('is_active', true)->count(),
                'categories' => Category::query()->whereNull('parent_id')->where('is_active', true)->count(),
                'products_with_images' => $productsWithImages,
                'image_coverage' => $kpis['total_products'] > 0 ? (int) round($productsWithImages / $kpis['total_products'] * 100) : 0,
                'current_month_quotes' => $currentMonthQuotes,
                'quotes_trend' => $previousMonthQuotes > 0
                    ? (int) round(($currentMonthQuotes - $previousMonthQuotes) / $previousMonthQuotes * 100)
                    : null,
            ],
            'recentQuotes' => $recentQuotes,
            'topQuotedProducts' => $topQuotedProducts,
            'topQuotedProductModels' => $topQuotedProductModels,
            'quotesByStatus' => $quotesByStatus,
            'recentActivity' => $recentActivity,
            'quotesPerMonth' => $quotesPerMonth,
            'productsByCategory' => $productsByCategory,
            'latestProducts' => $latestProducts,
        ]);
    }
}
