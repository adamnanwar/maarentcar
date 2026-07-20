<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\TourPackage;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AdminReportController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:reports.view_full'),
        ];
    }

    public function index(Request $request): Response
    {
        [$from, $to] = $this->range($request);

        return Inertia::render('Admin/Laporan/Index', [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            ...$this->buildReport($from, $to),
        ]);
    }

    public function export(Request $request): HttpResponse
    {
        [$from, $to] = $this->range($request);
        $daily = $this->dailyRevenue($from, $to);

        return response()->streamDownload(function () use ($daily) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Tanggal', 'Pendapatan']);
            foreach ($daily as $row) {
                fputcsv($handle, [$row['date'], $row['total']]);
            }
            fclose($handle);
        }, 'laporan-pendapatan.csv', ['Content-Type' => 'text/csv']);
    }

    private function range(Request $request): array
    {
        $from = $request->filled('from') ? Carbon::parse($request->string('from')->value())->startOfDay() : now()->startOfMonth();
        $to = $request->filled('to') ? Carbon::parse($request->string('to')->value())->endOfDay() : now()->endOfDay();

        return [$from, $to];
    }

    private function dailyRevenue(Carbon $from, Carbon $to): array
    {
        return Payment::query()
            ->where('status', Payment::STATUS_TERVERIFIKASI)
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('DATE(created_at) as date, SUM(amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn ($row) => ['date' => $row->date, 'total' => (float) $row->total])
            ->all();
    }

    private function buildReport(Carbon $from, Carbon $to): array
    {
        $daily = $this->dailyRevenue($from, $to);
        $totalRevenue = array_sum(array_column($daily, 'total'));

        $bookingsByStatus = Booking::query()
            ->whereBetween('created_at', [$from, $to])
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $activeVehicles = Vehicle::where('is_active', true)->count();
        $vehiclesBerlangsung = Booking::where('booking_type', Booking::TYPE_MOBIL)
            ->where('status', Booking::STATUS_BERLANGSUNG)
            ->distinct('vehicle_id')
            ->count('vehicle_id');

        $topVehicles = Vehicle::query()
            ->withCount(['bookings' => fn ($q) => $q->whereBetween('created_at', [$from, $to])])
            ->orderByDesc('bookings_count')
            ->take(5)
            ->get(['id', 'name'])
            ->map(fn ($v) => ['name' => $v->name, 'total' => $v->bookings_count]);

        $topPackages = TourPackage::query()
            ->withCount(['bookings' => fn ($q) => $q->whereBetween('created_at', [$from, $to])])
            ->orderByDesc('bookings_count')
            ->take(5)
            ->get(['id', 'name'])
            ->map(fn ($p) => ['name' => $p->name, 'total' => $p->bookings_count]);

        return [
            'stats' => [
                'total_revenue' => $totalRevenue,
                'total_bookings' => array_sum($bookingsByStatus->all()),
                'active_vehicles' => $activeVehicles,
                'vehicles_berlangsung' => $vehiclesBerlangsung,
            ],
            'dailyRevenue' => $daily,
            'bookingsByStatus' => $bookingsByStatus,
            'topVehicles' => $topVehicles,
            'topPackages' => $topPackages,
        ];
    }
}
