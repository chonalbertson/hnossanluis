<?php

namespace Tests\Feature;

use App\Models\HeroBanner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HeroBannerManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_hero_banners_index_with_recommended_dimensions_and_auto_seeded_defaults(): void
    {
        $admin = $this->createUserWithRole('admin');

        $response = $this->actingAs($admin)->get(route('admin.hero-banners.index'));

        $response->assertOk();
        $response->assertSee('1920 x 720 px');
        $response->assertSee('1080 x 1350 px');
        $response->assertSee('Catálogo General');
        $this->assertGreaterThanOrEqual(4, HeroBanner::query()->count());
    }

    public function test_admin_and_catalog_editor_can_upload_background_image_create_update_toggle_and_delete_banner(): void
    {
        Storage::fake('public');

        $editor = $this->createUserWithRole('catalog_editor');

        $desktopImage = UploadedFile::fake()->image('banner-desktop.jpg', 1920, 720);
        $mobileImage = UploadedFile::fake()->image('banner-mobile.jpg', 1080, 1350);

        $storeResponse = $this->actingAs($editor)->post(route('admin.hero-banners.store'), [
            'label' => 'Promoción Industrial',
            'eyebrow' => 'Abastecimiento Corporativo',
            'badge_metric' => 'Envío Express',
            'title' => 'Sistemas de',
            'highlight' => 'Cierre Suave',
            'suffix' => 'en Stock',
            'description' => 'Descuentos por volumen en bisagras y correderas telescópicas.',
            'background_image' => $desktopImage,
            'mobile_background_image' => $mobileImage,
            'overlay_opacity' => 70,
            'primary_cta_label' => 'Ver Promoción',
            'primary_cta_url' => '/catalogo',
            'secondary_cta_label' => 'Solicitar Cotización',
            'secondary_cta_url' => '/cotizacion',
            'accent' => 'blue',
            'product_source' => 'herrajes',
            'show_products_showcase' => '1',
            'sort_order' => 1,
            'is_active' => '1',
        ]);

        $storeResponse->assertRedirect(route('admin.hero-banners.index'));

        $banner = HeroBanner::query()->where('label', 'Promoción Industrial')->firstOrFail();
        $this->assertNotNull($banner->background_image_path);
        $this->assertNotNull($banner->mobile_background_image_path);
        Storage::disk('public')->assertExists($banner->background_image_path);
        Storage::disk('public')->assertExists($banner->mobile_background_image_path);

        // Verify banner renders on the public Home page with its uploaded background image
        $homeResponse = $this->get(route('home'));
        $homeResponse->assertOk();
        $homeResponse->assertSee('Promoción Industrial');
        $homeResponse->assertSee($banner->backgroundImageUrl(), false);

        // Update banner and remove mobile background image
        $oldMobilePath = $banner->mobile_background_image_path;
        $updateResponse = $this->actingAs($editor)->put(route('admin.hero-banners.update', $banner), [
            'label' => 'Promoción Industrial Editada',
            'eyebrow' => 'Abastecimiento Corporativo',
            'badge_metric' => 'Envío Express',
            'title' => 'Sistemas de',
            'highlight' => 'Cierre Suave Pro',
            'suffix' => 'en Stock',
            'description' => 'Descripción actualizada.',
            'remove_mobile_background_image' => '1',
            'overlay_opacity' => 65,
            'primary_cta_label' => 'Ver Promoción',
            'primary_cta_url' => '/catalogo',
            'secondary_cta_label' => 'Cotizar',
            'secondary_cta_url' => '/cotizacion',
            'accent' => 'amber',
            'product_source' => 'none',
            'sort_order' => 2,
            'is_active' => '1',
        ]);

        $updateResponse->assertRedirect(route('admin.hero-banners.index'));
        $banner->refresh();
        $this->assertSame('Promoción Industrial Editada', $banner->label);
        $this->assertNull($banner->mobile_background_image_path);
        Storage::disk('public')->assertMissing($oldMobilePath);

        // Toggle visibility
        $this->actingAs($editor)->patch(route('admin.hero-banners.toggle', $banner))
            ->assertRedirect();
        $this->assertFalse($banner->fresh()->is_active);

        // Destroy banner and clean up file from storage
        $desktopPath = $banner->background_image_path;
        $this->actingAs($editor)->delete(route('admin.hero-banners.destroy', $banner))
            ->assertRedirect(route('admin.hero-banners.index'));

        $this->assertDatabaseMissing('hero_banners', ['id' => $banner->id]);
        Storage::disk('public')->assertMissing($desktopPath);
    }

    public function test_unauthorized_roles_cannot_manage_hero_banners(): void
    {
        $seller = $this->createUserWithRole('seller');
        $supervisor = $this->createUserWithRole('supervisor');

        $this->actingAs($seller)->get(route('admin.hero-banners.index'))->assertForbidden();
        $this->actingAs($supervisor)->get(route('admin.hero-banners.create'))->assertForbidden();
        $this->actingAs($seller)->post(route('admin.hero-banners.store'), [])->assertForbidden();
    }
}
