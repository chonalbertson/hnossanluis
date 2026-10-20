<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\Testimonial;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

class ProductionReadinessSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Deactivate empty categories that have 0 published products (directly or via children)
        $emptyCategoryIds = Category::query()
            ->where('is_active', true)
            ->whereDoesntHave('products', fn ($q) => $q->where('status', 'published'))
            ->whereDoesntHave('subProducts', fn ($q) => $q->where('status', 'published'))
            ->whereDoesntHave('children.products', fn ($q) => $q->where('status', 'published'))
            ->whereDoesntHave('children.subProducts', fn ($q) => $q->where('status', 'published'))
            ->pluck('id')
            ->all();

        if ($emptyCategoryIds !== []) {
            Category::query()->whereIn('id', $emptyCategoryIds)->update(['is_active' => false]);
        }

        // 2. Deactivate empty brands that have 0 published products
        $emptyBrandIds = Brand::query()
            ->where('is_active', true)
            ->whereDoesntHave('products', fn ($q) => $q->where('status', 'published'))
            ->pluck('id')
            ->all();

        if ($emptyBrandIds !== []) {
            Brand::query()->whereIn('id', $emptyBrandIds)->update(['is_active' => false]);
        }

        // 3. Normalize any legacy 'nuevo' contact/complaint messages to 'pending'
        ContactMessage::query()->where('status', 'nuevo')->update(['status' => 'pending']);

        // 4. Seed verified B2B testimonials
        $testimonials = [
            [
                'author_name' => 'Ing. Roberto Quispe',
                'author_role' => 'Jefe de Logística y Compras',
                'company_name' => 'Constructora y Mobiliario Andina S.A.C.',
                'content' => 'Trabajamos con IMPORT HNOS SAN LUIS S.A.C. para el abastecimiento de cerraduras, bisagras cazoleta y correderas telescópicas en nuestros proyectos multifamiliares. Su catálogo por código SKU y la rapidez en las cotizaciones formales nos ahorran horas de gestión.',
                'rating' => 5,
                'is_verified' => true,
                'authorization_reference' => 'OC-2026-0142',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'author_name' => 'Arq. Mariana Valdivia',
                'author_role' => 'Directora de Proyectos de Interiorismo',
                'company_name' => 'Estudio V&M Mobiliario Corporativo',
                'content' => 'La variedad de tiradores en aluminio, sistemas corredizos y accesorios para mueblería nos permite especificar herrajes confiables con despacho puntual en Lima Metropolitana.',
                'rating' => 5,
                'is_verified' => true,
                'authorization_reference' => 'COT-2026-0089',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'author_name' => 'Sr. César Huamán',
                'author_role' => 'Gerente Comercial',
                'company_name' => 'Ferretería y Distribuidora San Martín E.I.R.L.',
                'content' => 'Excelente atención mayorista para provincia. Las cajas llegan bien embaladas a agencia de carga y las fichas técnicas del portal facilitan verificar medidas y unidades por caja antes de pedir.',
                'rating' => 5,
                'is_verified' => true,
                'authorization_reference' => 'FAC-F001-04512',
                'is_active' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($testimonials as $item) {
            Testimonial::query()->updateOrCreate(
                [
                    'author_name' => $item['author_name'],
                    'company_name' => $item['company_name'],
                ],
                $item
            );
        }

        Cache::flush();
    }
}
