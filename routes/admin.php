<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CommercialSettingController;
use App\Http\Controllers\Admin\CommunicationSettingController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\HeroBannerController;
use App\Http\Controllers\Admin\ImportExportController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\PageContentController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\QuoteController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SecurityCenterController;
use App\Http\Controllers\Admin\SeoSettingController;
use App\Http\Controllers\Admin\TestimonialController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

// Rutas del Panel Administrativo (16 módulos protegidos por autenticación y RBAC en servidor)
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'permission:dashboard.view'])
    ->group(function (): void {

        // 1. Dashboard con KPIs reales y accesos rápidos
        Route::get('/', [DashboardController::class, 'index'])
            ->name('dashboard');

        // 2. Productos (Catálogo Técnico)
        Route::middleware('permission:catalog.manage')->group(function (): void {
            Route::get('/productos/crear', [ProductController::class, 'create'])->name('products.create');
            Route::post('/productos', [ProductController::class, 'store'])->name('products.store');
            Route::get('/productos/{product}/editar', [ProductController::class, 'edit'])->name('products.edit');
            Route::put('/productos/{product}', [ProductController::class, 'update'])->name('products.update');
            Route::patch('/productos/{product}/toggle-precio', [ProductController::class, 'togglePriceVisibility'])->name('products.toggle-price');
            Route::delete('/productos/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
            Route::delete('/productos/{id}/eliminar', [ProductController::class, 'forceDestroy'])
                ->whereNumber('id')
                ->name('products.force-destroy');
            Route::post('/productos/{id}/restaurar', [ProductController::class, 'restore'])->name('products.restore');
            Route::delete('/productos/{product}/imagen/{image}', [ProductController::class, 'destroyImage'])->name('products.images.destroy');
            Route::post('/productos/{product}/imagen/{image}/principal', [ProductController::class, 'setPrimaryImage'])->name('products.images.primary');
        });

        Route::middleware('permission:catalog.view|catalog.manage')->group(function (): void {
            Route::get('/productos', [ProductController::class, 'index'])->name('products.index');
            Route::get('/productos/{product}', [ProductController::class, 'show'])->name('products.show');
        });

        // 3. Categorías y Subcategorías
        Route::middleware('permission:categories.manage')->group(function (): void {
            Route::get('/categorias', [CategoryController::class, 'index'])->name('categories.index');
            Route::get('/categorias/crear', [CategoryController::class, 'create'])->name('categories.create');
            Route::post('/categorias', [CategoryController::class, 'store'])->name('categories.store');
            Route::get('/categorias/{category}/editar', [CategoryController::class, 'edit'])->name('categories.edit');
            Route::put('/categorias/{category}', [CategoryController::class, 'update'])->name('categories.update');
            Route::patch('/categorias/{category}/toggle', [CategoryController::class, 'toggle'])->name('categories.toggle');
            Route::delete('/categorias/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
        });

        // 4. Marcas
        Route::middleware('permission:brands.manage')->group(function (): void {
            Route::get('/marcas', [BrandController::class, 'index'])->name('brands.index');
            Route::get('/marcas/crear', [BrandController::class, 'create'])->name('brands.create');
            Route::post('/marcas', [BrandController::class, 'store'])->name('brands.store');
            Route::get('/marcas/{brand}/editar', [BrandController::class, 'edit'])->name('brands.edit');
            Route::put('/marcas/{brand}', [BrandController::class, 'update'])->name('brands.update');
            Route::patch('/marcas/{brand}/toggle', [BrandController::class, 'toggle'])->name('brands.toggle');
            Route::delete('/marcas/{brand}', [BrandController::class, 'destroy'])->name('brands.destroy');
        });

        // 5. Imágenes y Archivos (Gestor de Medios y Fichas Técnicas PDF)
        Route::middleware('permission:media.manage')->group(function (): void {
            Route::get('/archivos', [MediaController::class, 'index'])->name('media.index');
            Route::post('/archivos/imagen', [MediaController::class, 'storeImage'])->name('media.store-image');
            Route::post('/archivos/documento', [MediaController::class, 'storeDocument'])->name('media.store-document');
            Route::delete('/archivos/imagen/{image}', [MediaController::class, 'destroyImage'])->name('media.destroy-image');
            Route::delete('/archivos/documento/{document}', [MediaController::class, 'destroyDocument'])->name('media.destroy-document');
        });

        // 6. Importación y Exportación Masiva CSV
        Route::middleware('permission:imports.manage')->group(function (): void {
            Route::get('/importacion', [ImportExportController::class, 'index'])->name('imports.index');
            Route::post('/importacion/analizar', [ImportExportController::class, 'analyze'])->name('imports.analyze');
            Route::get('/importacion/plantilla', [ImportExportController::class, 'downloadTemplate'])->name('imports.template');
            Route::get('/importacion/exportar', [ImportExportController::class, 'export'])->name('imports.export');
            Route::get('/importacion/{batch}/vista-previa', [ImportExportController::class, 'preview'])->name('imports.preview');
            Route::post('/importacion/{batch}/ejecutar', [ImportExportController::class, 'execute'])->name('imports.execute');
            Route::get('/importacion/{batch}/errores', [ImportExportController::class, 'downloadErrors'])->name('imports.errors');
        });

        // 7. Solicitudes de Cotización (incluye creación manual B2B / WhatsApp)
        Route::middleware('permission:quotes.manage|quotes.respond')->group(function (): void {
            Route::get('/cotizaciones/crear', [QuoteController::class, 'create'])->name('quotes.create');
            Route::post('/cotizaciones', [QuoteController::class, 'store'])->name('quotes.store');
        });

        Route::middleware('permission:quotes.view|quotes.manage|quotes.respond')->group(function (): void {
            Route::get('/cotizaciones', [QuoteController::class, 'index'])->name('quotes.index');
            Route::get('/cotizaciones/exportar', [QuoteController::class, 'export'])->name('quotes.export');
            Route::get('/cotizaciones/{quote}', [QuoteController::class, 'show'])->name('quotes.show');
            Route::get('/cotizaciones/{quote}/pdf', [QuoteController::class, 'pdf'])->name('quotes.pdf');
        });

        Route::middleware('permission:quotes.manage')->group(function (): void {
            Route::post('/cotizaciones/{quote}/asignar', [QuoteController::class, 'assign'])->name('quotes.assign');
            Route::post('/cotizaciones/{quote}/estado', [QuoteController::class, 'changeStatus'])->name('quotes.status');
            Route::post('/cotizaciones/{quote}/notas', [QuoteController::class, 'storeNote'])->name('quotes.notes');
            Route::delete('/cotizaciones/{quote}/notas/{note}', [QuoteController::class, 'destroyNote'])->name('quotes.notes.destroy');
        });

        Route::middleware('permission:quotes.respond')->group(function (): void {
            Route::post('/cotizaciones/{quote}/responder', [QuoteController::class, 'respond'])->name('quotes.respond');
        });

        // Bandeja de Mensajes de Contacto Comercial
        Route::middleware('permission:quotes.view|quotes.manage|quotes.respond')->group(function (): void {
            Route::get('/mensajes-contacto', [ContactMessageController::class, 'index'])->name('contact-messages.index');
            Route::get('/mensajes-contacto/{contactMessage}', [ContactMessageController::class, 'show'])->name('contact-messages.show');
            Route::match(['post', 'patch'], '/mensajes-contacto/{contactMessage}/estado', [ContactMessageController::class, 'updateStatus'])->name('contact-messages.status');
        });

        Route::middleware('permission:quotes.manage')->group(function (): void {
            Route::delete('/mensajes-contacto/{contactMessage}', [ContactMessageController::class, 'destroy'])->name('contact-messages.destroy');
        });

        // 8. Clientes y Contactos Autorizados
        Route::middleware('permission:customers.manage')->group(function (): void {
            Route::get('/clientes', [CustomerController::class, 'index'])->name('customers.index');
            Route::get('/clientes/crear', [CustomerController::class, 'create'])->name('customers.create');
            Route::post('/clientes', [CustomerController::class, 'store'])->name('customers.store');
            Route::get('/clientes/{customer}', [CustomerController::class, 'show'])->name('customers.show');
            Route::get('/clientes/{customer}/editar', [CustomerController::class, 'edit'])->name('customers.edit');
            Route::put('/clientes/{customer}', [CustomerController::class, 'update'])->name('customers.update');
            Route::delete('/clientes/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');
        });

        // 9. Usuarios y Permisos RBAC
        Route::middleware('permission:users.manage')->group(function (): void {
            Route::get('/usuarios', [UserController::class, 'index'])->name('users.index');
            Route::get('/usuarios/crear', [UserController::class, 'create'])->name('users.create');
            Route::post('/usuarios', [UserController::class, 'store'])->name('users.store');
            Route::get('/usuarios/{user}/editar', [UserController::class, 'edit'])->name('users.edit');
            Route::put('/usuarios/{user}', [UserController::class, 'update'])->name('users.update');
            Route::patch('/usuarios/{user}/toggle', [UserController::class, 'toggle'])->name('users.toggle');
            Route::delete('/usuarios/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        });

        // 10. Contenido de Páginas Institucionales, Legales y Banners del Slider
        Route::middleware('permission:pages.manage')->group(function (): void {
            Route::get('/paginas', [PageContentController::class, 'index'])->name('pages.index');
            Route::get('/paginas/{pageContent}/editar', [PageContentController::class, 'edit'])->name('pages.edit');
            Route::put('/paginas/{pageContent}', [PageContentController::class, 'update'])->name('pages.update');
        });

        Route::middleware('permission:pages.manage|media.manage')->group(function (): void {
            Route::get('/banners-slider', [HeroBannerController::class, 'index'])->name('hero-banners.index');
            Route::get('/banners-slider/crear', [HeroBannerController::class, 'create'])->name('hero-banners.create');
            Route::post('/banners-slider', [HeroBannerController::class, 'store'])->name('hero-banners.store');
            Route::get('/banners-slider/{heroBanner}/editar', [HeroBannerController::class, 'edit'])->name('hero-banners.edit');
            Route::put('/banners-slider/{heroBanner}', [HeroBannerController::class, 'update'])->name('hero-banners.update');
            Route::patch('/banners-slider/{heroBanner}/toggle', [HeroBannerController::class, 'toggle'])->name('hero-banners.toggle');
            Route::delete('/banners-slider/{heroBanner}', [HeroBannerController::class, 'destroy'])->name('hero-banners.destroy');
        });

        // 11. Configuración Comercial
        Route::middleware('permission:settings.manage')->group(function (): void {
            Route::get('/configuracion-comercial', [CommercialSettingController::class, 'edit'])->name('settings.commercial');
            Route::put('/configuracion-comercial', [CommercialSettingController::class, 'update'])->name('settings.commercial.update');
        });

        // 12. Configuración de WhatsApp y Correo (con Probador SMTP) + Centro de Copias de Seguridad
        Route::middleware('permission:settings.manage')->group(function (): void {
            Route::get('/comunicaciones', [CommunicationSettingController::class, 'edit'])->name('settings.communication');
            Route::put('/comunicaciones', [CommunicationSettingController::class, 'update'])->name('settings.communication.update');
            Route::post('/comunicaciones/probar-correo', [CommunicationSettingController::class, 'testMail'])->name('settings.communication.test-mail');

            Route::get('/respaldos', [BackupController::class, 'index'])->name('backups.index');
            Route::post('/respaldos', [BackupController::class, 'create'])->name('backups.create');
            Route::get('/respaldos/{filename}/descargar', [BackupController::class, 'download'])->name('backups.download');
            Route::delete('/respaldos/{filename}', [BackupController::class, 'destroy'])->name('backups.destroy');

            // Centro Administrativo de Seguridad Antimalware, WAF y Cuarentena
            Route::get('/seguridad', [SecurityCenterController::class, 'index'])->name('security.index');
            Route::post('/seguridad/escanear', [SecurityCenterController::class, 'scan'])->name('security.scan');
            Route::get('/seguridad/incidentes/{incident}', [SecurityCenterController::class, 'showIncident'])->name('security.incidents.show');
            Route::patch('/seguridad/incidentes/{incident}/resolver', [SecurityCenterController::class, 'resolveIncident'])->name('security.incidents.resolve');
            Route::delete('/seguridad/incidentes/{incident}/cuarentena', [SecurityCenterController::class, 'deleteQuarantine'])->name('security.incidents.delete-quarantine');
        });

        // 13. SEO y Analítica GA4
        Route::middleware('permission:seo.manage')->group(function (): void {
            Route::get('/seo', [SeoSettingController::class, 'edit'])->name('settings.seo');
            Route::put('/seo', [SeoSettingController::class, 'update'])->name('settings.seo.update');
        });

        // 14. Testimonios Verificados
        Route::middleware('permission:testimonials.manage')->group(function (): void {
            Route::get('/testimonios', [TestimonialController::class, 'index'])->name('testimonials.index');
            Route::get('/testimonios/crear', [TestimonialController::class, 'create'])->name('testimonials.create');
            Route::post('/testimonios', [TestimonialController::class, 'store'])->name('testimonials.store');
            Route::get('/testimonios/{testimonial}/editar', [TestimonialController::class, 'edit'])->name('testimonials.edit');
            Route::put('/testimonios/{testimonial}', [TestimonialController::class, 'update'])->name('testimonials.update');
            Route::patch('/testimonios/{testimonial}/toggle', [TestimonialController::class, 'toggle'])->name('testimonials.toggle');
            Route::delete('/testimonios/{testimonial}', [TestimonialController::class, 'destroy'])->name('testimonials.destroy');
        });

        // 15. Registro de Actividad (Auditoría Inmutable)
        Route::middleware('permission:activity_logs.view')->group(function (): void {
            Route::get('/actividad', [ActivityLogController::class, 'index'])->name('activity-logs.index');
            Route::get('/actividad/{activityLog}', [ActivityLogController::class, 'show'])->name('activity-logs.show');
        });

        // 16. Informes Comerciales Básicos
        Route::middleware('permission:reports.view')->group(function (): void {
            Route::get('/informes', [ReportController::class, 'index'])->name('reports.index');
            Route::get('/informes/exportar', [ReportController::class, 'export'])->name('reports.export');
        });
    });
