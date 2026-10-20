<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class HeroBanner extends Model
{
    use HasFactory;

    public const RECOMMENDED_DESKTOP_DIMENSIONS = '1920 x 720 px';

    public const RECOMMENDED_MOBILE_DIMENSIONS = '1080 x 1350 px';

    public const RECOMMENDED_MAX_SIZE_MB = 4;

    public const RECOMMENDED_FORMATS = 'JPG, PNG, WEBP';

    protected $fillable = [
        'label',
        'eyebrow',
        'badge_metric',
        'title',
        'highlight',
        'suffix',
        'description',
        'background_image_path',
        'mobile_background_image_path',
        'overlay_opacity',
        'primary_cta_label',
        'primary_cta_url',
        'secondary_cta_label',
        'secondary_cta_url',
        'accent',
        'product_source',
        'show_products_showcase',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'overlay_opacity' => 'integer',
            'show_products_showcase' => 'boolean',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function backgroundImageUrl(): ?string
    {
        if (! $this->background_image_path) {
            return null;
        }

        if (
            str_starts_with($this->background_image_path, 'http://')
            || str_starts_with($this->background_image_path, 'https://')
            || str_starts_with($this->background_image_path, '/')
        ) {
            return $this->background_image_path;
        }

        return Storage::disk('public')->url($this->background_image_path);
    }

    public function mobileBackgroundImageUrl(): ?string
    {
        if (! $this->mobile_background_image_path) {
            return null;
        }

        if (
            str_starts_with($this->mobile_background_image_path, 'http://')
            || str_starts_with($this->mobile_background_image_path, 'https://')
            || str_starts_with($this->mobile_background_image_path, '/')
        ) {
            return $this->mobile_background_image_path;
        }

        return Storage::disk('public')->url($this->mobile_background_image_path);
    }

    /**
     * Default hero slides seeded or used as fallback when no active banners exist in DB.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function defaultSlidesDefinition(): array
    {
        return [
            [
                'label' => 'Catálogo General',
                'eyebrow' => 'Import Hnos San Luis S.A.C. • Mayorista y Minorista',
                'badge_metric' => '+1,330 SKUs Verificados',
                'title' => 'Herrajes, Tiradores y',
                'highlight' => 'Ferretería Profesional',
                'suffix' => 'para tus Proyectos',
                'description' => 'Abastecimiento técnico con cotizaciones por volumen (RUC) y por unidad para mueblería, carpintería en melamina, contratistas y obras en todo el Perú.',
                'primary_cta_label' => 'Explorar Catálogo',
                'primary_cta_url' => '/catalogo',
                'secondary_cta_label' => 'Cotizar Lista',
                'secondary_cta_url' => '/cotizacion',
                'accent' => 'red',
                'product_source' => 'destacados',
                'show_products_showcase' => true,
                'overlay_opacity' => 75,
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'label' => 'Herrajes y Corredizos',
                'eyebrow' => 'Ingeniería en Movimiento para Mobiliario',
                'badge_metric' => 'Cierre Suave & Push Open',
                'title' => 'Bisagras, Correderas y',
                'highlight' => 'Sistemas Corredizos',
                'suffix' => 'de Alta Precisión',
                'description' => 'Bisagras cazoleta cierre lento, correderas telescópicas y ocultas, sistemas corredizos Ducasse, pistones a gas y tapacantos con stock permanente.',
                'primary_cta_label' => 'Ver Herrajes y Bisagras',
                'primary_cta_url' => '/catalogo?category=herrajes-para-muebles',
                'secondary_cta_label' => 'Sistemas Corredizos',
                'secondary_cta_url' => '/catalogo?category=sistemas-corredizos',
                'accent' => 'blue',
                'product_source' => 'herrajes',
                'show_products_showcase' => true,
                'overlay_opacity' => 75,
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'label' => 'Tiradores y Cocina',
                'eyebrow' => 'Diseño Arquitectónico e Interiorismo',
                'badge_metric' => '+480 Diseños en Stock',
                'title' => 'Tiradores, Perfiles Gola y',
                'highlight' => 'Organización de Cocina',
                'suffix' => '& Clóset',
                'description' => 'Colección exclusiva de jaladores en acero inoxidable y aluminio, perillas, condimenteros extraíbles, basureros, pantaloneros y accesorios de clóset.',
                'primary_cta_label' => 'Tiradores y Jaladores',
                'primary_cta_url' => '/catalogo?category=tiradores-jaladores-y-perillas',
                'secondary_cta_label' => 'Cocina y Clóset',
                'secondary_cta_url' => '/catalogo?category=organizacion-cocina-y-closet',
                'accent' => 'amber',
                'product_source' => 'tiradores',
                'show_products_showcase' => true,
                'overlay_opacity' => 75,
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'label' => 'Cerraduras, LED y Taller',
                'eyebrow' => 'Seguridad, Iluminación LED y Ensamble',
                'badge_metric' => 'Grado Industrial B2B',
                'title' => 'Cerraduras, Fijaciones,',
                'highlight' => 'Iluminación LED y Taller',
                'suffix' => '',
                'description' => 'Cerraduras y topes en acero inoxidable, conectores Minifix, cintas LED y tomas multimedia empotrables, pegamentos MDF y plantillas de carpintería.',
                'primary_cta_label' => 'Cerraduras y Fijación',
                'primary_cta_url' => '/catalogo?category=chapas-cerraduras-y-otros',
                'secondary_cta_label' => 'Iluminación y Tomas',
                'secondary_cta_url' => '/catalogo?category=electricidad',
                'accent' => 'emerald',
                'product_source' => 'cerraduras',
                'show_products_showcase' => true,
                'overlay_opacity' => 75,
                'sort_order' => 4,
                'is_active' => true,
            ],
        ];
    }
}
