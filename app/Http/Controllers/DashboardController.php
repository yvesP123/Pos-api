<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use App\Models\Member;
use App\Models\Pembelian;
use App\Models\Pengeluaran;
use App\Models\Penjualan;
use App\Models\Produk;
use App\Models\Supplier;
// ADD THESE TWO IMPORTS
use App\Models\License;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $kategori = Kategori::count();
        $produk = Produk::count();
        $supplier = Supplier::count();
        $member = Member::count();
        $penjualan = Penjualan::sum('diterima');
        $pengeluaran = Pengeluaran::sum('nominal');
        $pembelian = Pembelian::sum('bayar');

        $tanggal_awal = date('Y-m-01');
        $tanggal_akhir = date('Y-m-d');

        $data_tanggal = array();
        $data_pendapatan = array();
        

        while (strtotime($tanggal_awal) <= strtotime($tanggal_akhir)) {
            $data_tanggal[] = (int) substr($tanggal_awal, 8, 2);

            $total_penjualan = Penjualan::where('created_at', 'LIKE', "%$tanggal_awal%")->sum('bayar');
            $total_pembelian = Pembelian::where('created_at', 'LIKE', "%$tanggal_awal%")->sum('bayar');
            $total_pengeluaran = Pengeluaran::where('created_at', 'LIKE', "%$tanggal_awal%")->sum('nominal');

            $pendapatan = $total_penjualan - $total_pembelian - $total_pengeluaran;
            $data_pendapatan[] += $pendapatan;

            $tanggal_awal = date('Y-m-d', strtotime("+1 day", strtotime($tanggal_awal)));
        }

        $tanggal_awal = date('Y-m-01');

        // ADD THIS LICENSE CHECK CODE
        $license = License::first(); // Assuming you have one license record
        $showLicenseWarning = false;
        $daysUntilExpiry = null;
        $licenseExpiryDate = null;

        if ($license) {
            $showLicenseWarning = $license->isAboutToExpire(3);
            if ($showLicenseWarning) {
                $now = Carbon::now()->startOfDay();
                $expiryDate = Carbon::parse($license->expiry_date)->startOfDay();
                $daysUntilExpiry = $now->diffInDays($expiryDate, false);
                $licenseExpiryDate = $expiryDate->format('M d, Y');
            }
        }

        // MODIFY THE RETURN STATEMENTS TO INCLUDE THE NEW VARIABLES
        if (auth()->user()->level == 1) {
            return view('admin.dashboard', compact(
                'kategori', 'produk', 'supplier', 'member', 'penjualan', 
                'pengeluaran', 'pembelian', 'tanggal_awal', 'tanggal_akhir', 
                'data_tanggal', 'data_pendapatan', 'showLicenseWarning', 
                'daysUntilExpiry', 'licenseExpiryDate'
            ));
        } else {
            return view('kasir.dashboard', compact(
                'showLicenseWarning', 'daysUntilExpiry', 'licenseExpiryDate'
            ));
        }
    }
}
// visit "codeastro" for more projects!