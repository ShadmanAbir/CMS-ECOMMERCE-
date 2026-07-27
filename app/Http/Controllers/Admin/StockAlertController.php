<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StockAlert;
use App\Services\StockAlertService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StockAlertController extends Controller
{
    public function __construct(protected StockAlertService $alerts) {}

    /**
     * List stock alerts with an optional status filter.
     */
    public function index(Request $request)
    {
        $filter = $request->query('filter', 'unresolved');

        $alerts = $this->alerts->list($filter);
        $counts = $this->alerts->counts();

        return view('admin.alerts.index', compact('alerts', 'filter', 'counts'));
    }

    /**
     * Mark an alert as resolved.
     */
    public function resolve(StockAlert $alert)
    {
        $this->alerts->resolve($alert);

        return redirect()->back()->with('success', 'Stock alert resolved.');
    }

    /**
     * Resolve all open alerts at once.
     */
    public function resolveAll()
    {
        $count = $this->alerts->resolveAll();

        return redirect()->back()->with('success', "Resolved {$count} stock " . Str::plural('alert', $count) . '.');
    }

    /**
     * Delete an alert permanently.
     */
    public function destroy(StockAlert $alert)
    {
        $alert->delete();

        return redirect()->back()->with('success', 'Stock alert deleted.');
    }
}
