<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\ProfileController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Public\CatalogController;
use App\Http\Controllers\Public\ContactController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\PageController;
use App\Http\Controllers\Public\ProductController;
use App\Http\Controllers\Public\QuoteCartController;
use App\Http\Controllers\Public\SeoController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Portal Público & Catálogo Técnico — Import Hns San Luis
|--------------------------------------------------------------------------
*/

// Inicio
Route::get('/', [HomeController::class, 'index'])->name('home');

// SEO Técnico (Sitemap XML & Robots.txt dinámicos)
Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('seo.sitemap');
Route::get('/robots.txt', [SeoController::class, 'robots'])->name('seo.robots');

// Catálogo Técnico, Autocompletado, Comparador y Detalle de Producto
Route::get('/catalogo', [CatalogController::class, 'index'])->name('catalog.index');
Route::get('/catalogo/sugerencias', [CatalogController::class, 'suggestions'])->name('catalog.suggestions');
Route::get('/catalogo/comparar', [CatalogController::class, 'compare'])->name('catalog.compare');
Route::post('/catalogo/comparar/agregar', [CatalogController::class, 'addToCompare'])->name('catalog.compare.add');
Route::match(['post', 'delete'], '/catalogo/comparar/eliminar/{productId?}', [CatalogController::class, 'removeFromCompare'])
    ->whereNumber('productId')
    ->name('catalog.compare.remove');
Route::match(['post', 'delete'], '/catalogo/comparar/vaciar', [CatalogController::class, 'clearCompare'])->name('catalog.compare.clear');
Route::get('/categoria/{categorySlug}', [CatalogController::class, 'index'])->name('catalog.category');
Route::get('/catalogo/{slug}/ficha-tecnica', [ProductController::class, 'downloadPdf'])->name('catalog.download-pdf');
Route::get('/catalogo/{slug}', [ProductController::class, 'show'])->name('catalog.show');

// Sistema de Cotizaciones (Individual, Carrito Múltiple y Seguimiento Público)
Route::prefix('cotizacion')->group(function () {
    Route::get('/', [QuoteCartController::class, 'index'])->name('quote.index');
    Route::get('/seguimiento', [QuoteCartController::class, 'track'])->name('quote.track');
    Route::get('/seguimiento/{reference_code}/pdf', [QuoteCartController::class, 'downloadTrackedPdf'])->name('quote.track.pdf');
    Route::post('/agregar', [QuoteCartController::class, 'add'])->name('quote.add');
    Route::match(['post', 'put', 'patch'], '/actualizar', [QuoteCartController::class, 'update'])->name('quote.update');
    Route::match(['post', 'delete'], '/eliminar/{productId}', [QuoteCartController::class, 'remove'])
        ->where('productId', '[A-Za-z0-9_-]+')
        ->name('quote.remove');
    Route::match(['post', 'delete'], '/vaciar', [QuoteCartController::class, 'clear'])->name('quote.clear');
    Route::post('/enviar', [QuoteCartController::class, 'submit'])->name('quote.submit');
    Route::get('/confirmacion/{reference_code}', [QuoteCartController::class, 'confirmation'])->name('quote.confirmation');
});

// Contacto Comercial
Route::get('/contacto', [ContactController::class, 'index'])->name('contact.index');
Route::post('/contacto', [ContactController::class, 'submit'])->name('contact.submit');

// Páginas Institucionales, Empresa B2B, Legales y Libro de Reclamaciones (Ley N° 29733 e INDECOPI Perú)
Route::get('/empresa/{section?}', [PageController::class, 'company'])
    ->whereIn('section', [
        'nosotros-experiencia',
        'especialidades-b2b',
        'certificaciones',
        'cobertura-nacional',
        'nuestro-equipo',
        'marcas-representadas',
        'politicas-atencion-corporativa',
        'casos-y-proyectos-reales',
    ])
    ->name('pages.company');
Route::get('/nosotros', [PageController::class, 'about'])->name('pages.about');
Route::get('/clientes-y-testimonios', [PageController::class, 'testimonials'])->name('pages.testimonials');
Route::get('/politica-de-privacidad', [PageController::class, 'privacy'])->name('pages.privacy');
Route::get('/politica-de-cookies', [PageController::class, 'cookies'])->name('pages.cookies');
Route::get('/terminos-y-condiciones', [PageController::class, 'terms'])->name('pages.terms');
Route::get('/condiciones-de-cotizacion', [PageController::class, 'quoteConditions'])->name('pages.quote-conditions');
Route::get('/libro-de-reclamaciones', [PageController::class, 'complaintsBook'])->name('pages.complaints-book');
Route::post('/libro-de-reclamaciones', [PageController::class, 'submitComplaintsBook'])->name('pages.complaints-book.submit');

/*
|--------------------------------------------------------------------------
| Autenticación y Perfil de Usuario / Cliente
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.submit');

    Route::get('/registro', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/registro', [RegisteredUserController::class, 'store'])->name('register.submit');

    Route::get('/recuperar-contrasena', [PasswordResetController::class, 'create'])->name('password.request');
    Route::post('/recuperar-contrasena', [PasswordResetController::class, 'store'])->name('password.email');
    Route::get('/recuperar-contrasena/{token}', [PasswordResetController::class, 'edit'])->name('password.reset');
    Route::post('/recuperar-contrasena/restablecer', [PasswordResetController::class, 'update'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('/mi-cuenta', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::match(['post', 'put', 'patch'], '/mi-cuenta', [ProfileController::class, 'update'])->name('profile.update');
    Route::match(['post', 'put', 'patch'], '/mi-cuenta/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
});
