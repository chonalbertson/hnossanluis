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
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->canAccessAdminPanel(), 403);

        $hasIncidentsTable = Schema::hasTable('security_incidents');
        $hasScansTable = Schema::hasTable('security_scans');

        $kpis = [
            'published_products' => Product::query()->where('status', 'published')->count(),
            'total_products' => Product::query()->count(),
            'on_request_products' => Product::query()->where('availability_status', 'on_request')->count(),
            'total_quotes' => Quote::query()->count(),
            'pending_quotes' => Quote::query()->whereIn('status', ['nueva', 'en_revision', 'pendiente_info', 'en_preparacion'])->count(),
            'new_quotes_today' => Quote::query()->whereDate('created_at', today())->count(),
            'responded_quotes' => Quote::query()->whereIn('status', ['respondida', 'aceptada'])->count(),
            'total_customers' => Customer::query()->count(),
            'completed_imports' => ProductImportBatch::query()->where('status', 'completed')->count(),
            'new_contact_messages' => ContactMessage::query()->where('status', 'nuevo')->count(),
            'total_contact_messages' => ContactMessage::query()->count(),
            'security_pending_incidents' => $hasIncidentsTable
                ? SecurityIncident::query()->whereIn('status', ['blocked', 'quarantined'])->count()
                : 0,
            'security_quarantined_files' => $hasIncidentsTable
                ? SecurityIncident::query()->whereNotNull('quarantine_path')->count()
                : 0,
            'latest_security_scan' => $hasScansTable
                ? SecurityScan::query()->latest()->first()
                : null,
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

        $quotesByStatus = Quote::query()
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();

        $recentActivity = ActivityLog::query()
            ->with('user')
            ->latest()
            ->limit(6)
            ->get();

        $monthStart = now()->startOfMonth();
        $quoteDates = Quote::query()
            ->where('created_at', '>=', $monthStart->copy()->subMonths(5))
            ->pluck('created_at');

        $quotesPerMonth = collect(range(5, 0))->map(function (int $monthsAgo) use ($monthStart, $quoteDates): array {
            $month = $monthStart->copy()->subMonths($monthsAgo);

            return [
                'label' => ucfirst($month->locale('es')->translatedFormat('M')),
                'total' => $quoteDates->filter(fn ($date) => $date->isSameMonth($month))->count(),
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
