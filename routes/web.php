<?php

use App\Http\Controllers\{
    DashboardController,
    KategoriController,
    LaporanController,
    ProdukController,
    MemberController,
    PengeluaranController,
    PembelianController,
    PembelianDetailController,
    PenjualanController,
    PenjualanDetailController,
    SettingController,
    SupplierController,
    UserController,
};
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    // Check license status before redirecting to login
    $license = \App\Models\License::first();
    
    if (!$license) {
        return redirect()->route('license.required');
    }
    
    if ($license->isGracePeriodEnded()) {
        return redirect()->route('license.expired');
    }
    
    return redirect()->route('login');
});

// License routes - accessible even when expired (NO middleware)
Route::prefix('license')->name('license.')->group(function () {
    Route::get('/required', [App\Http\Controllers\LicenseController::class, 'showRequired'])->name('required');
    Route::get('/expired', [App\Http\Controllers\LicenseController::class, 'showExpired'])->name('expired');
    Route::get('/renew', [App\Http\Controllers\LicenseController::class, 'showRenewalForm'])->name('renew');
    Route::post('/renew', [App\Http\Controllers\LicenseController::class, 'processRenewal']);
    
    // New routes for enhanced renewal system
    Route::post('/renew-manual', [App\Http\Controllers\LicenseController::class, 'renewWithManualKey'])->name('renew.manual');
    Route::post('/renew-payment', [App\Http\Controllers\LicenseController::class, 'renewWithPayment'])->name('renew.payment');
    Route::post('/validate-manual', [App\Http\Controllers\LicenseController::class, 'validateManualKey']);
    
    Route::get('/status', [App\Http\Controllers\LicenseController::class, 'checkStatus'])->name('status'); // For debugging
});

// Admin Authentication routes for License Management (NO license check middleware)
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [App\Http\Controllers\AdminAuthController::class, 'showAdminLogin'])->name('login');
    Route::post('/login', [App\Http\Controllers\AdminAuthController::class, 'adminLogin'])->name('login.submit');
    Route::post('/logout', [App\Http\Controllers\AdminAuthController::class, 'adminLogout'])->name('logout');
});

// License Management routes - accessible even when expired (admin only, NO license check)
Route::group(['middleware' => ['auth', 'admin'], 'prefix' => 'license-management', 'as' => 'license-management.'], function () {
    Route::get('/', [App\Http\Controllers\LicenseManagementController::class, 'index'])->name('index');
    Route::get('/data', [App\Http\Controllers\LicenseManagementController::class, 'data'])->name('data');
    Route::get('/keys', [App\Http\Controllers\LicenseManagementController::class, 'viewKeys'])->name('keys');
    Route::get('/create', [App\Http\Controllers\LicenseManagementController::class, 'create'])->name('create');
    Route::post('/store', [App\Http\Controllers\LicenseManagementController::class, 'store'])->name('store');
    Route::get('/show/{id}', [App\Http\Controllers\LicenseManagementController::class, 'show'])->name('show');
    Route::get('/search', [App\Http\Controllers\LicenseManagementController::class, 'search'])->name('search');
    Route::get('/regenerate/{id}', [App\Http\Controllers\LicenseManagementController::class, 'regenerate'])->name('regenerate');
    Route::get('/extend/{id}', [App\Http\Controllers\LicenseManagementController::class, 'extend'])->name('extend');
    Route::post('/extend/{id}', [App\Http\Controllers\LicenseManagementController::class, 'extend']);
    Route::get('/export', [App\Http\Controllers\LicenseManagementController::class, 'exportCsv'])->name('export');
    Route::get('/manual-renewal', [App\Http\Controllers\LicenseManagementController::class, 'manualRenewal'])->name('manual-renewal');
    Route::get('/current-license', [App\Http\Controllers\LicenseManagementController::class, 'currentLicense'])->name('current-license');
});

// Apply license check to ALL other routes (including auth routes)
Route::group(['middleware' => ['check.license']], function () {
    
    // Authentication routes - using Laravel's default auth routes
    // You can customize these controllers as needed
    Route::get('/login', function() {
        return view('auth.login');
    })->name('login');
    
    Route::post('/login', function(Illuminate\Http\Request $request) {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);
        
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('/dashboard');
        }
        
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    });
    
    Route::post('/logout', function(Illuminate\Http\Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    })->name('logout');
    
    // All authenticated routes
    Route::group(['middleware' => ['auth']], function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::group(['middleware' => 'level:1'], function () {
            Route::get('/kategori/data', [KategoriController::class, 'data'])->name('kategori.data');
            Route::resource('/kategori', KategoriController::class);

            Route::get('/produk/data', [ProdukController::class, 'data'])->name('produk.data');
            Route::post('/produk/delete-selected', [ProdukController::class, 'deleteSelected'])->name('produk.delete_selected');
            Route::post('/produk/cetak-barcode', [ProdukController::class, 'cetakBarcode'])->name('produk.cetak_barcode');
            Route::resource('/produk', ProdukController::class);

            Route::get('/member/data', [MemberController::class, 'data'])->name('member.data');
            Route::post('/member/cetak-member', [MemberController::class, 'cetakMember'])->name('member.cetak_member');
            Route::resource('/member', MemberController::class);

            Route::get('/supplier/data', [SupplierController::class, 'data'])->name('supplier.data');
            Route::resource('/supplier', SupplierController::class);

            Route::get('/pengeluaran/data', [PengeluaranController::class, 'data'])->name('pengeluaran.data');
            Route::resource('/pengeluaran', PengeluaranController::class);

            Route::get('/pembelian/data', [PembelianController::class, 'data'])->name('pembelian.data');
            Route::get('/pembelian/{id}/create', [PembelianController::class, 'create'])->name('pembelian.create');
            Route::resource('/pembelian', PembelianController::class)
                ->except('create');

            Route::get('/pembelian_detail/{id}/data', [PembelianDetailController::class, 'data'])->name('pembelian_detail.data');
            Route::get('/pembelian_detail/loadform/{diskon}/{total}', [PembelianDetailController::class, 'loadForm'])->name('pembelian_detail.load_form');
            Route::resource('/pembelian_detail', PembelianDetailController::class)
                ->except('create', 'show', 'edit');

            Route::get('/penjualan/data', [PenjualanController::class, 'data'])->name('penjualan.data');
            Route::get('/penjualan', [PenjualanController::class, 'index'])->name('penjualan.index');
            Route::get('/penjualan/{id}', [PenjualanController::class, 'show'])->name('penjualan.show');
            Route::delete('/penjualan/{id}', [PenjualanController::class, 'destroy'])->name('penjualan.destroy');
        });

        Route::group(['middleware' => 'level:1,2'], function () {
            Route::get('/transaksi/baru', [PenjualanController::class, 'create'])->name('transaksi.baru');
            Route::post('/transaksi/simpan', [PenjualanController::class, 'store'])->name('transaksi.simpan');
            Route::get('/transaksi/selesai', [PenjualanController::class, 'selesai'])->name('transaksi.selesai');
            Route::get('/transaksi/nota-kecil', [PenjualanController::class, 'notaKecil'])->name('transaksi.nota_kecil');
            Route::get('/transaksi/nota-besar', [PenjualanController::class, 'notaBesar'])->name('transaksi.nota_besar');

            Route::get('/transaksi/{id}/data', [PenjualanDetailController::class, 'data'])->name('transaksi.data');
            Route::get('/transaksi/loadform/{diskon}/{total}/{diterima}', [PenjualanDetailController::class, 'loadForm'])->name('transaksi.load_form');
            Route::resource('/transaksi', PenjualanDetailController::class)
                ->except('create', 'show', 'edit');
        });

        Route::group(['middleware' => 'level:1'], function () {
            Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
            Route::get('/laporan/data/{awal}/{akhir}', [LaporanController::class, 'data'])->name('laporan.data');
            Route::get('/laporan/pdf/{awal}/{akhir}', [LaporanController::class, 'exportPDF'])->name('laporan.export_pdf');

            Route::get('/user/data', [UserController::class, 'data'])->name('user.data');
            Route::resource('/user', UserController::class);

            Route::get('/setting', [SettingController::class, 'index'])->name('setting.index');
            Route::get('/setting/first', [SettingController::class, 'show'])->name('setting.show');
            Route::post('/setting', [SettingController::class, 'update'])->name('setting.update');
        });
     
        Route::group(['middleware' => 'level:1,2'], function () {
            Route::get('/profil', [UserController::class, 'profil'])->name('user.profil');
            Route::post('/profil', [UserController::class, 'updateProfil'])->name('user.update_profil');
        });

        // Routes that are restricted when license is expired
        Route::middleware('restrict.expired.features')->group(function () {
            Route::post('/exports', 'ExportController@run'); 
            Route::post('/save-data', 'DataController@save');
        });
    });
});