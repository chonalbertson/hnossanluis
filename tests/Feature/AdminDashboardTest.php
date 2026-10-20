<?php

namespace Tests\Feature;

use App\Models\Quote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_charts_group_quotes_by_month_and_compute_trend_and_image_coverage(): void
    {
        $this->travelTo(now()->setDate(2026, 10, 15));
        $admin = $this->createUserWithRole('admin');
        $this->createSampleCatalogItem(['sku' => 'SL-DASH-001']);

        $quoteDates = [
            '2026-10-02', '2026-10-10', '2026-10-12',
            '2026-09-20', '2026-09-21',
            '2026-04-30',
        ];
        foreach ($quoteDates as $index => $date) {
            $quote = Quote::query()->create([
                'reference_code' => sprintf('COT-2026-%05d', $index + 1),
                'contact_name' => 'Cliente Dashboard',
                'contact_email' => 'dashboard@cliente.pe',
                'contact_phone' => '999888777',
                'status' => 'nueva',
                'currency' => 'PEN',
            ]);
            $quote->forceFill(['created_at' => $date.' 10:00:00'])->save();
        }

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertViewHas('quotesPerMonth', fn (array $months) => array_column($months, 'total') === [0, 0, 0, 0, 2, 3]
            && end($months)['is_current'] === true);
        $response->assertViewHas('kpis', fn (array $kpis) => $kpis['current_month_quotes'] === 3
            && $kpis['quotes_trend'] === 50
            && $kpis['image_coverage'] === 0);
        $response->assertSee('Cotizaciones recibidas');
        $response->assertSee('Productos por categoría');
    }

    public function test_admin_settings_pages_render_and_link_to_each_other(): void
    {
        $admin = $this->createUserWithRole('admin');

        $this->actingAs($admin)->get(route('admin.settings.commercial'))
            ->assertOk()
            ->assertSee(route('admin.settings.communication'), false);
        $this->actingAs($admin)->get(route('admin.settings.communication'))->assertOk();
        $this->actingAs($admin)->get(route('admin.settings.seo'))->assertOk();
    }
}
