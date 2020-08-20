<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Session;
use Cart;
use App\Http\Requests;
use Illuminate\Support\Facades\Redirect;
session_start();

class CheckoutController extends Controller
{
     public function AuthLogin(){
        $admin_id = Session::get('admin_id');
        if($admin_id){
            return Redirect::to('dashboard');
        }else{
            return Redirect::to('admin')->send();
        }
    }
    public function view_order($orderId){
        $this->AuthLogin();
        $order_by_id = DB::table('tbl_order')
        ->join('tbl_customers','tbl_order.customer_id','=','tbl_customers.customer_id')
        ->join('tbl_shipping','tbl_order.shipping_id','=','tbl_shipping.shipping_id')
        ->join('tbl_order_details','tbl_order.order_id','=','tbl_order_details.order_id')
        ->select('tbl_order.*','tbl_customers.*','tbl_shipping.*','tbl_order_details.*')->first();

        $manager_order_by_id  = view('admin.view_order')->with('order_by_id',$order_by_id);
        return view('admin_layout')->with('admin.view_order', $manager_order_by_id);

    }
    public function login_checkout(){
    	$cate_product = DB::table('tbl_category_product')->where('category_status','0')->orderby('category_id','desc')->get();
        $brand_product = DB::table('tbl_brand')->where('brand_status','0')->orderby('brand_id','desc')->get();
    	return view('pages.checkout.login_checkout')->with('category',$cate_product)->with('brand',$brand_product);
    }
    public function add_customer(Request $request){
    	$data = array();
    	$data['customer_name'] = $request->customer_name;
    	$data['customer_phone'] = $request->customer_phone;
    	$data['customer_email'] = $request->customer_email;
    	$data['customer_password'] = md5($request->customer_password);
    	$customer_id = DB::table('tbl_customers')->insertGetId($data);
    	Session::put('customer_id',$customer_id);
    	Session::put('customer_name',$request->customer_name);
    	return Redirect::to('/checkout');
    }
    public function checkout(){
    	$cate_product = DB::table('tbl_category_product')->where('category_status','0')->orderby('category_id','desc')->get();
        $brand_product = DB::table('tbl_brand')->where('brand_status','0')->orderby('brand_id','desc')->get();

    	return view('pages.checkout.show_checkout')->with('category',$cate_product)->with('brand',$brand_product);
    }
    public function save_checkout_customer(Request $request){
    	$data = array();
    	$data['shipping_name'] = $request->shipping_name;
    	$data['shipping_phone'] = $request->shipping_phone;
    	$data['shipping_email'] = $request->shipping_email;
    	$data['shipping_notes'] = $request->shipping_notes;
    	$data['shipping_address'] = $request->shipping_address;
        $data['customer_id'] = $request->session()->get('customer_id');;

    	$shipping_id = DB::table('tbl_shipping')->insertGetId($data);

    	Session::put('shipping_id',$shipping_id);

    	return Redirect::to('/payment');
    }
    public function payment(){

        $cate_product = DB::table('tbl_category_product')->where('category_status','0')->orderby('category_id','desc')->get();
        $brand_product = DB::table('tbl_brand')->where('brand_status','0')->orderby('brand_id','desc')->get();
        return view('pages.checkout.payment')->with('category',$cate_product)->with('brand',$brand_product);

    }
    public function order_place(Request $request){
        //insert payment_method
        $data = array();
        $data['payment_method'] = $request->payment_option;
        $data['payment_status'] = 'Đang chờ xử lý';
        $payment_id = DB::table('tbl_payment')->insertGetId($data);

        //insert order
        $order_data = array();
        $order_data['customer_id'] = Session::get('customer_id');
        $order_data['shipping_id'] = Session::get('shipping_id');
        $order_data['payment_id'] = $payment_id;
        $order_data['order_total'] = $request->total;
        $order_data['order_status'] = 'Đang chờ xử lý';
        $order_id = DB::table('tbl_order')->insertGetId($order_data);

        //insert order_details
        $cusmorer_id = session()->get('customer_id');
        $customer_cart = 'cart'.$cusmorer_id;
        $content = session()->get($customer_cart);
        foreach($content as $v_content){
            $order_d_data['order_id'] = $order_id;
            $order_d_data['product_id'] = $v_content['id'];
            $order_d_data['product_name'] = $v_content['name'];
            $order_d_data['product_price'] = $v_content['price'];
            $order_d_data['product_sales_quantity'] = $v_content['qty'];
            $product = DB::table('tbl_product')->where('product_id', $v_content['id'])->first();
            $product->amount -= $v_content['qty'];
            if ($product->amount<0){

            }
            else{
                $product = DB::table('tbl_product')->where('product_id', $v_content['id'])->update(['amount'=>$product->amount]);
                DB::table('tbl_order_details')->insert($order_d_data);
            }
        }
        if($data['payment_method']==1){
            echo 'Thanh toán thẻ ATM';
        }elseif($data['payment_method']==2){
//                Cart::destroy();
            $cusmorer_id = session()->get('customer_id');
            $cus = 'cart'.$cusmorer_id;
            session()->pull($cus,null);
            session()->pull('total',null);
            session()->pull('cart.index',null);
            $cate_product = DB::table('tbl_category_product')->where('category_status','0')->orderby('category_id','desc')->get();
            $brand_product = DB::table('tbl_brand')->where('brand_status','0')->orderby('brand_id','desc')->get();
            return view('pages.checkout.handcash')->with('category',$cate_product)->with('brand',$brand_product);

        }else{
            echo 'Thẻ ghi nợ';
        }
        return Redirect::to('/payment');
    }
    public function pushSesionFromIndextoCustomer($customer_ss){
        $index = session()->get('cart.index');
        $customer = session()->get($customer_ss);
        if ($index!=null){
            foreach ($index as $in){
                $check =0;
                if ($customer!=null){
                  foreach ($customer as $key => $cus){
                     if ($in['id']==$cus['id']){
                         $in['qty'] += $cus['qty'];
                         array_push($customer,$in);
                         unset($customer[$key]);
                         $check =1;
                     }
                  }
                  if ($check==1){
                      session()->put($customer_ss,$customer);
                  }
                  else{
                      session()->push($customer_ss,$in);
                  }
              }
              else{
                  session()->push($customer_ss,$in);
              }
            }
        }
    }
    public function logout_checkout(Request $request){
//    	Session::flush();
        $request->session()->pull('customer_id', 'default');
        $request->session()->pull('customer_name', 'default');
        session()->pull('cart.index');
        $customer_id = session()->get('customer_id');
        $cart = 'cart'.$customer_id;
        $request->session()->pull($cart, 'default');
        $request->session()->pull('cart.index', 'default');
    	return Redirect::to('/login-checkout');
    }
    public function login_customer(Request $request){
    	$email = $request->email_account;
    	$password = md5($request->password_account);
    	$result = DB::table('tbl_customers')->where('customer_email',$email)->where('customer_password',$password)->first();
    	if($result){
    		Session::put('customer_id',$result->customer_id);
    		$customer_ss = 'cart'.$result->customer_id;
    		$this->pushSesionFromIndextoCustomer($customer_ss);
    		return Redirect::to('/checkout');
    	}else{
    		return Redirect::to('/login-checkout');
    	}
    }
    public function manage_order(){

        $this->AuthLogin();
        $all_order = DB::table('tbl_order')
        ->join('tbl_customers','tbl_order.customer_id','=','tbl_customers.customer_id')
        ->select('tbl_order.*','tbl_customers.customer_name')
        ->orderby('tbl_order.order_id','desc')->get();
        $manager_order  = view('admin.manage_order')->with('all_order',$all_order);
        return view('admin_layout')->with('admin.manage_order', $manager_order);
    }
}
