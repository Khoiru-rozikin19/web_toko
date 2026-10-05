<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::where('status', 'active')
            ->orderBy('sort_order', 'asc')
            ->with(['products' => function ($q) {
                $q->where('status', 'active')->orderBy('price_selling', 'asc');
            }])
            ->get();

        $selectedType = $request->get('type', 'data');
        $selectedBrand = $request->get('brand', 'Telkomsel');

        $activeProducts = Product::where('status', 'active')
            ->when($selectedType, fn($q) => $q->where('type', $selectedType))
            ->orderBy('price_selling', 'asc')
            ->get();

        $recentTransactions = Auth::check()
            ? Transaction::where('user_id', Auth::id())->latest()->take(5)->get()
            : collect();

        return view('dashboard.index', compact('categories', 'selectedType', 'selectedBrand', 'activeProducts', 'recentTransactions'));
    }

    /**
     * API for live prefix lookup (detecting operator from phone number)
     */
    public function detectOperator(Request $request)
    {
        $phone = preg_replace('/[^0-9]/', '', $request->get('phone', ''));

        // Normalize leading 62 / +62 to 08
        if (str_starts_with($phone, '628')) {
            $phone = '08' . substr($phone, 3);
        } elseif (str_starts_with($phone, '8')) {
            $phone = '08' . substr($phone, 1);
        }

        $prefix4 = substr($phone, 0, 4);

        $brand = $this->resolveBrandFromPrefix($prefix4);
        $type = $request->get('type', 'data');

        $category = Category::where('brand', $brand)->where('type', $type)->first();

        $products = Product::where('status', 'active')
            ->where(function ($q) use ($brand, $category, $type) {
                if ($category) {
                    $q->where('category_id', $category->id);
                } else {
                    $q->where('type', $type)->where('name', 'LIKE', "%{$brand}%");
                }
            })
            ->orderBy('price_selling', 'asc')
            ->get();

        return response()->json([
            'brand' => $brand,
            'phone' => $phone,
            'category' => $category,
            'products' => $products,
        ]);
    }

    protected function resolveBrandFromPrefix(string $prefix): string
    {
        $telkomsel = ['0811', '0812', '0813', '0821', '0822', '0823', '0851', '0852', '0853'];
        $indosat = ['0814', '0815', '0816', '0855', '0856', '0857', '0858'];
        $xl = ['0817', '0818', '0819', '0859', '0877', '0878'];
        $axis = ['0831', '0832', '0833', '0838'];
        $tri = ['0895', '0896', '0897', '0898', '0899'];
        $smartfren = ['0881', '0882', '0883', '0884', '0885', '0886', '0887', '0888', '0889'];

        if (in_array($prefix, $telkomsel)) return 'Telkomsel';
        if (in_array($prefix, $indosat)) return 'Indosat';
        if (in_array($prefix, $xl)) return 'XL';
        if (in_array($prefix, $axis)) return 'Axis';
        if (in_array($prefix, $tri)) return 'Tri';
        if (in_array($prefix, $smartfren)) return 'Smartfren';

        return 'Telkomsel'; // default fallback
    }

    public function history()
    {
        $transactions = Transaction::where('user_id', Auth::id())->latest()->paginate(15);
        return view('dashboard.history', compact('transactions'));
    }
}
