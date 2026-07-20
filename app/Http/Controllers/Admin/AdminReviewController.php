<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;
use Inertia\Response;

class AdminReviewController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:reviews.moderate'),
        ];
    }

    public function index(Request $request): Response
    {
        $query = Review::query()->with(['user', 'vehicle', 'package']);

        if ($search = $request->string('search')->trim()->value()) {
            $query->where(function ($q) use ($search) {
                $q->where('comment', 'ilike', "%{$search}%")
                    ->orWhereHas('user', fn ($q2) => $q2->where('name', 'ilike', "%{$search}%"));
            });
        }

        if ($rating = $request->integer('rating')) {
            $query->where('rating', $rating);
        }

        if (($status = $request->string('status')->value()) !== '') {
            $query->where('is_hidden', $status === 'hidden');
        }

        return Inertia::render('Admin/Ulasan/Index', [
            'reviews' => $query->latest()->paginate(15)->withQueryString(),
            'filters' => $request->only(['search', 'rating', 'status']),
        ]);
    }

    public function toggleVisibility(Review $ulasan): RedirectResponse
    {
        $ulasan->update(['is_hidden' => ! $ulasan->is_hidden]);

        return back()->with('success', $ulasan->is_hidden ? 'Ulasan disembunyikan.' : 'Ulasan ditampilkan kembali.');
    }
}
