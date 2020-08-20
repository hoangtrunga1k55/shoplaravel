<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Auth;
use Session;
use App\Http\Requests;

use Illuminate\Support\Facades\Redirect;
use Cart;

session_start();

class CartController extends Controller
{
    public  function getSession($customer_ss){
        if($customer_ss==null){
            $ss = session()->get('cart.index');
        }
        else{
            $ss = session()->get($customer_ss);
        }
        return $ss;
    }
    public  function pushSession($customer_ss,$data){
        if ($customer_ss !=null) {
            session()->push($customer_ss, $data);
        } else {
            session()->push('cart.index', $data);
        }
    }
    public  function putSession($customer_ss,$data){
        if (session()->has('customer_id')) {
            $customer_id = session()->get('customer_id');
            $customer_ss = 'cart' . $customer_id;
            session()->put($customer_ss, $data);
        } else {
            session()->put('cart.index', $data);
        }
    }



    public function save_cart(Request $request)
    {
        $productId = $request->productid_hidden;
        $quantity = $request->qty;
        $product_info = DB::table('tbl_product')->where('product_id', $productId)->first();
        $data['id'] = $product_info->product_id;
        $data['qty'] = $quantity;
        $data['name'] = $product_info->product_name;
        $data['price'] = $product_info->product_price;
        $data['weight'] = $product_info->product_price;
        $data['image'] = $product_info->product_image;
        if (session()->has('customer_id')) {
            $customer_id = session()->get('customer_id');
            $customer_ss = 'cart' . $customer_id;
        } else {
            $customer_ss =null;
        }
        $ss=$this->getSession($customer_ss);
        if ($ss == null) {
            $this->pushSession($customer_ss,$data);
        } else {
            $ss = $this->getSession($customer_ss);
            $check =0;
            foreach ($ss as $key => $s) {
                if ($s['id'] == $data['id']) {
                    $check = 1;
                    $data['qty'] += $s['qty'];
                    unset($ss[$key]);
                    array_push($ss, $data);
                    $this->putSession($customer_ss,$ss);
                }
            }
            if($check==0){
                $this->pushSession($customer_ss,$data);
            }
        }
//        var_dump(session()->get('cart.index'));
        return Redirect::to('/show-cart');
    }

    public function show_cart()
    {
        $cate_product = DB::table('tbl_category_product')->where('category_status', '0')->orderby('category_id', 'desc')->get();
        $brand_product = DB::table('tbl_brand')->where('brand_status', '0')->orderby('brand_id', 'desc')->get();
        return view('pages.cart.show_cart')->with('category', $cate_product)->with('brand', $brand_product);
    }

    public function delete_to_cart($rowId, Request $request)
    {
//        Cart::update($rowId,0);
        if (session()->has('customer_id')) {
            $customer_id = session()->get('customer_id');
            $customer_ss = 'cart' . $customer_id;
            $cart = session()->get($customer_ss);
        } else {
            $cart = session('cart.index');
        }
        foreach ($cart as $key => $car) {
            if ($car['id'] == $rowId) {
                unset($cart[$key]);
                if (session()->has('customer_id')) {
                    $customer_id = session()->get('customer_id');
                    $customer_ss = 'cart' . $customer_id;
                    session()->put($customer_ss, $cart);
                } else {
                    session()->put('cart.index', $cart);
                }
            }
        }
            return Redirect::to(url()->previous());
    }

    public function update_cart_quantity(Request $request,$rowId)
    {
        $qty = $request->cart_quantity;
        if (session()->has('customer_id')) {
            $customer_id = session()->get('customer_id');
            $customer_ss = 'cart' . $customer_id;
            $cart = session()->get($customer_ss);
        } else {
            $cart = session('cart.index');
        }
        foreach ($cart as $key => $car) {
            if ($car['id'] == $rowId) {
                $car['qty'] = $qty;
                array_push($cart,$car);
                unset($cart[$key]);
                if (session()->has('customer_id')) {
                    $customer_id = session()->get('customer_id');
                    $customer_ss = 'cart' . $customer_id;
                    session()->put($customer_ss, $cart);
                } else {
                    session()->put('cart.index', $cart);
                }
            }
        }

        return Redirect::to(url()->previous());
    }

}
