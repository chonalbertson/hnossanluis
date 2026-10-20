<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PageContent;
use App\Services\ActivityLoggerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PageContentController extends Controller
{
    public function __construct(
        protected ActivityLoggerService $activityLogger
    ) {}

    public function index(Request $request): View
    {
        abort_unless($request->user()?->hasPermission('pages.manage'), 403);

        $pages = PageContent::query()
            ->with('editor')
            ->orderBy('id')
            ->get();

        return view('admin.pages.index', [
            'pages' => $pages,
        ]);
    }

    public function edit(Request $request, PageContent $pageContent): View
    {
        abort_unless($request->user()?->hasPermission('pages.manage'), 403);

        return view('admin.pages.edit', [
            'pageContent' => $pageContent,
        ]);
    }

    public function update(Request $request, PageContent $pageContent): RedirectResponse
    {
        abort_unless($request->user()?->hasPermission('pages.manage'), 403);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:500'],
            'content' => ['required', 'string'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'is_indexable' => ['nullable', 'boolean'],
        ]);

        $pageContent->update([
            'title' => trim($validated['title']),
            'subtitle' => $validated['subtitle'] ?? null,
            'content' => $validated['content'],
            'meta_title' => $validated['meta_title'] ?? null,
            'meta_description' => $validated['meta_description'] ?? null,
            'is_indexable' => $request->boolean('is_indexable'),
            'updated_by' => $request->user()->id,
        ]);

        $this->activityLogger->log(
            'page.updated',
            'cms',
            sprintf('Contenido de la página "%s" (%s) actualizado.', $pageContent->title, $pageContent->page_key),
            $pageContent
        );

        return redirect()
            ->route('admin.pages.index')
            ->with('success', sprintf('Página "%s" actualizada correctamente.', $pageContent->title));
    }
}
