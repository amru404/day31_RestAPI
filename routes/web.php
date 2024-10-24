<?php
use Illuminate\Http\Request;
use App\Http\Controllers\PaymentController;
use App\Models\Product;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Midtrans\Snap;
use Midtrans\Config;

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/


$router->post('/get-snap-token', function (Request $request) {

    // Konfigurasi Midtrans
    Config::$serverKey = config('midtrans.server_key');
    Config::$isProduction = config('midtrans.is_production');
    Config::$isSanitized = config('midtrans.is_sanitized');
    Config::$is3ds = config('midtrans.is_3ds');

    // Data untuk transaksi
    $params = [
        'transaction_details' => [
            'order_id' => $request->order_id,
            'gross_amount' => $request->amount,
        ],
        'customer_details' => [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phone' => '08123456789',
        ],
    ];

    try {
        // Mendapatkan Snap Token dari Midtrans
        $snapToken = Snap::getSnapToken($params);
        
        // Mengembalikan JSON response dengan Snap Token
        return response()->json([
            'msg' => 'Snap Token berhasil dihasilkan',
            'snap_token' => $snapToken,
        ], 200);
    } catch (\Exception $e) {
        // Mengembalikan JSON response jika terjadi error
        return response()->json([
            'msg' => 'Gagal mendapatkan Snap Token',
            'error' => $e->getMessage(),
        ], 500);
    }
});


$router->group(['prefix' => 'product'], function () use ($router) {
    
    
    $router->get('', function () {
        $product = Product::all();
        return response()->json($product);
    });


    
    $router->get('/{id}', function ($id) {
        $product = Product::findOrFail($id);
        return response()->json($product);
    });

    $router->post('/', function (Request $request) {
        $kode_product = $request->input('kode_product');
        $nama = $request->input('nama');
        $harga = $request->input('harga');
        $stock = $request->input('stock');

        Product::create([
            'kode_product'    => $kode_product,
            'nama'     => $nama,
            'harga'    => $harga,
            'stock'    => $stock,
        ]);

        return response()->json([
            [
                'nama' => $nama,
                'kode_product' => $kode_product,
                'stock' => $stock,
                'harga' => $harga,
            ],
            [
                'msg' => 'data berhasil di tambahkan',
            ],
        ]);
    });


    $router->put('/{id}', function (Request $request, string $id) {
        $kode_product = $request->input('kode_product');
        $nama = $request->input('nama');
        $harga = $request->input('harga');
        $stock = $request->input('stock');

        $product = Product::findOrFail($id);


        $product->update([
            'kode_product' => $kode_product,
            'nama'     => $nama,
            'harga'    => $harga,
            'stock'    => $stock,
        ]);


        return response()->json([
            [
                'nama' => $nama,
                'kode_product' => $kode_product,
                'stock' => $stock,
                'harga' => $harga,
            ],
            [
                'msg' => 'data berhasil di edit',
            ],
        ]);
    });


    $router->delete('/{id}', function ($id) {
        $product = Product::findOrFail($id);
        $product->delete();

        return response()->json([
            "msg" => "data telah di hapus"
        ]);
    });


});


$router->group(['prefix' => 'order'], function () use ($router) {
    
    
    $router->get('', function () {
        $order = Order::all();
        return response()->json($order);
    });


    
    $router->get('/{id}', function ($id) {
        $order = Order::findOrFail($id);
        return response()->json($order);
    });

    $router->post('/', function (Request $request) {
        $products_id = $request->input('products_id');
        $qty = $request->input('qty');
        $product = Product::findOrFail($products_id);
        $harga = $product->harga * $qty;
        $total_harga = $harga;

        Order::create([
            'products_id'    => $products_id,
            'qty'     => $qty,
            'total_harga'    => $total_harga,
        ]);

        return response()->json([
            [
                'products_id' => $products_id,
                'qty' => $qty,
                'harga_satuan' => $product->harga,
                'total_harga' => $total_harga,
            ],
            [
                'msg' => 'data berhasil di tambahkan',
            ],
        ]);
    });


    $router->put('/{id}', function (Request $request, string $id) {
        $products_id = $request->input('products_id');
        $qty = $request->input('qty');
        $product = Product::findOrFail($products_id);
        $harga = $product->harga * $qty;
        $total_harga = $harga;

        $order = Order::findOrFail($id);
        $order->update([
            'products_id'    => $products_id,
            'qty'     => $qty,
            'total_harga'    => $total_harga,
        ]);

        return response()->json([
            [
                'products_id' => $products_id,
                'qty' => $qty,
                'harga_satuan' => $product->harga,
                'total_harga' => $total_harga,
            ],
            [
                'msg' => 'data berhasil di Edit',
            ],
        ]);
    });


    $router->delete('/{id}', function ($id) {
        $order = Order::findOrFail($id);
        $order->delete();

        return response()->json([
            "msg" => "data dengan id $id telah di hapus"
        ]);
    });


});


$router->group(['prefix' => 'payment'], function () use ($router) {
    
    
    $router->get('', function (Request $request) {
        $user = $request->user();

        if ($user == null) {
            return response()->json(['error' => 'Unauthorized'], 401, ['X-Header-One' => 'Header Value']);
        }
        $payment = Payment::all();
        return response()->json($payment);
        
    });
       

    
    $router->get('/{id}', function (Request $request, string $id) {
        $user = $request->user();

        if ($user == null) {
            return response()->json(['error' => 'Unauthorized'], 401, ['X-Header-One' => 'Header Value']);
        }

        $payment = Payment::findOrFail($id);
        return response()->json($payment);
    });

    $router->post('/', function (Request $request) {
        $user = $request->user();

        if ($user == null) {
            return response()->json(['error' => 'Unauthorized'], 401, ['X-Header-One' => 'Header Value']);
        }

        $orders_id = $request->input('orders_id');
        $order = order::findOrFail($orders_id);
        $total_harga = $order->total_harga;

        Payment::create([
            'orders_id'    => $orders_id,
            'total_harga'    => $total_harga,
            'status_pembayaran'     => 'Lunas',

        ]);

        return response()->json([
            [
                'orders_id'    => $orders_id,
                'total_harga'    => $total_harga,
                'status_pembayaran'     => 'Lunas',
            ],
            [
                'msg' => 'data berhasil di tambahkan',
            ],
        ]);
    });


    $router->put('/{id}', function (Request $request, string $id) {

        $orders_id = $request->input('orders_id');
        $order = order::findOrFail($orders_id);
        $total_harga = $order->total_harga;

        $user = $request->user();

        if ($user == null) {
            return response()->json(['error' => 'Unauthorized'], 401, ['X-Header-One' => 'Header Value']);
        }

        $payment = Payment::findOrFail($id);
        $payment->update([
            'orders_id'    => $orders_id,
            'total_harga'    => $total_harga,
            'status_pembayaran'     => 'Lunas',

        ]);

        return response()->json([
            [
                'orders_id'    => $orders_id,
                'total_harga'    => $total_harga,
                'status_pembayaran'     => 'Lunas',
            ],
            [
                'msg' => 'data berhasil di Edit',
            ],
        ]);
    });


    $router->delete('/{id}', function (Request $request, $id) {

        $user = $request->user();

        if ($user == null) {
            return response()->json(['error' => 'Unauthorized'], 401, ['X-Header-One' => 'Header Value']);
        }

        $payment = Payment::findOrFail($id);
        $payment->delete();

        return response()->json([
            "msg" => "data dengan id $id telah di hapus"
        ]);
    });


});



$router->group(['prefix' => 'penjualan'], function () use ($router) {
    $router->get('', function () {
        return response()->json([
            [
                'nama' => 'amru',
                'nomor' => 'A001',
            ],
            [
                'nama' => 'azzam',
                'nomor' => 'A002',
            ],
            [
                'nama' => 'abdurrahman',
                'nomor' => 'A003',
            ],
            [
                'nama' => 'danti',
                'nomor' => 'A004',
            ],
        ]);
    });

    $router->get('/{id}', function ($id) {
        return response()->json(['data' => [
            'id' => $id,
            'nama' => 'jaki',
            'nomor' => 'A004',
            'total_harga' => 70000,
            'diskon' => 20000,
        ],]);
    });

    
    $router->post('/', function () {

        return response()->json([
            'msg' => 'data berhasil',
            'id' => 4
        ]);
    });


    $router->PUT('/{id}', function (Request $request,string $id) {
        $nomor = $request->input('nomor');
        return response()->json(['data' => [
            'id' => $id,
            'nama' => 'jaki',
            'nomor' => $nomor,
            'total_harga' => 70000,
            'diskon' => 20000,
        ],]);
    });


    $router->DELETE('/{id}', function (Request $request,string $id) {
        return response()->json([
            'msg'=>'berhasil delete'
        ]);
    });



    $router->post('/{id}/confirm', function (Request $request, string $id) {
        $user = $request->user();
        Log::debug("<<<<<<<<");
        Log::debug($user);

        if ($user == null) {
            return response()->json(['error' => 'Unauthorized'], 401, ['X-Header-One' => 'Header Value']);
        }
        return response()->json([
            'msg'=>'berhasil Konfirmasi'
        ]);
    });

    $router->post('/{id}/sendEmail', function (Request $request, string $id) {
        Mail::raw('This is the email body.', function ($message) {
            $message->to('amru.azzam3@gmail.com')
                ->subject('Lumen email test');
            });
    
        return response()->json([
            'msg'=>'berhasil kirim email'
        ]);
    });


    

});