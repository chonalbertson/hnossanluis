<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Services\ActivityLoggerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function __construct(
        protected ActivityLoggerService $activityLogger
    ) {}

    public function index(Request $request): View
    {
        abort_unless($request->user()?->hasPermission('customers.manage'), 403);

        $query = Customer::query()->withCount('quotes');

        if ($request->filled('q')) {
            $term = '%'.trim($request->string('q')->toString()).'%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('company', 'like', $term)
                    ->orWhere('ruc', 'like', $term)
                    ->orWhere('phone', 'like', $term);
            });
        }

        if ($request->filled('authorized')) {
            $query->where('is_authorized_contact', $request->string('authorized')->toString() === '1');
        }

        $customers = $query->orderByDesc('updated_at')->paginate(15)->withQueryString();

        return view('admin.customers.index', [
            'customers' => $customers,
        ]);
    }

    public function create(Request $request): View
    {
        abort_unless($request->user()?->hasPermission('customers.manage'), 403);

        return view('admin.customers.create', [
            'customer' => new Customer(['is_authorized_contact' => true]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->hasPermission('customers.manage'), 403);

        $validated = $this->validateCustomer($request);

        $customer = Customer::query()->create([
            'name' => trim($validated['name']),
            'email' => strtolower(trim($validated['email'])),
            'phone' => $validated['phone'] ?? null,
            'company' => $validated['company'] ?? null,
            'ruc' => $validated['ruc'] ?? null,
            'address' => $validated['address'] ?? null,
            'sector' => $validated['sector'] ?? null,
            'is_authorized_contact' => $request->boolean('is_authorized_contact', true),
            'notes' => $validated['notes'] ?? null,
        ]);

        $this->activityLogger->log(
            'customer.created',
            'customers',
            sprintf('Cliente "%s" (%s) registrado.', $customer->name, $customer->company ?: $customer->email),
            $customer
        );

        return redirect()
            ->route('admin.customers.show', $customer)
            ->with('success', sprintf('Cliente "%s" registrado correctamente.', $customer->name));
    }

    public function show(Request $request, Customer $customer): View
    {
        abort_unless($request->user()?->hasPermission('customers.manage'), 403);

        $customer->load(['user', 'quotes' => fn ($q) => $q->with(['items', 'assignedSeller'])->latest()]);

        return view('admin.customers.show', [
            'customer' => $customer,
        ]);
    }

    public function edit(Request $request, Customer $customer): View
    {
        abort_unless($request->user()?->hasPermission('customers.manage'), 403);

        return view('admin.customers.edit', [
            'customer' => $customer,
        ]);
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        abort_unless($request->user()?->hasPermission('customers.manage'), 403);

        $validated = $this->validateCustomer($request, $customer);

        $customer->update([
            'name' => trim($validated['name']),
            'email' => strtolower(trim($validated['email'])),
            'phone' => $validated['phone'] ?? null,
            'company' => $validated['company'] ?? null,
            'ruc' => $validated['ruc'] ?? null,
            'address' => $validated['address'] ?? null,
            'sector' => $validated['sector'] ?? null,
            'is_authorized_contact' => $request->boolean('is_authorized_contact'),
            'notes' => $validated['notes'] ?? null,
        ]);

        $this->activityLogger->log(
            'customer.updated',
            'customers',
            sprintf('Ficha del cliente "%s" actualizada.', $customer->name),
            $customer
        );

        return redirect()
            ->route('admin.customers.show', $customer)
            ->with('success', sprintf('Datos del cliente "%s" actualizados.', $customer->name));
    }

    public function destroy(Request $request, Customer $customer): RedirectResponse
    {
        abort_unless($request->user()?->hasPermission('customers.manage'), 403);

        if ($customer->quotes()->exists()) {
            return back()->with('error', 'No se puede eliminar un cliente que posee historial de cotizaciones.');
        }

        $name = $customer->name;
        $customer->delete();

        $this->activityLogger->log(
            'customer.deleted',
            'customers',
            sprintf('Cliente "%s" eliminado.', $name)
        );

        return redirect()
            ->route('admin.customers.index')
            ->with('success', sprintf('Cliente "%s" eliminado.', $name));
    }

    protected function validateCustomer(Request $request, ?Customer $customer = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:180'],
            'email' => [
                'required',
                'email',
                'max:180',
                Rule::unique('customers', 'email')->ignore($customer?->id),
            ],
            'phone' => ['nullable', 'string', 'max:30'],
            'company' => ['nullable', 'string', 'max:200'],
            'ruc' => ['nullable', 'digits:11'],
            'address' => ['nullable', 'string', 'max:255'],
            'sector' => ['nullable', 'string', 'max:150'],
            'is_authorized_contact' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
    }
}
