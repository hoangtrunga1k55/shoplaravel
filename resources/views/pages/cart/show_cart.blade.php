@extends('layout')
@section('content')

	<section id="cart_items">
		<div class="container">
			<div class="breadcrumbs">
				<ol class="breadcrumb">
				  <li><a href="{{URL::to('/')}}">Trang chủ</a></li>
				  <li class="active">Giỏ hàng của bạn</li>
				</ol>
			</div>
			<div class="table-responsive cart_info">
				<?php
                $con =session('cart.index');
                if (session()->has('customer_id')){
                    $customer_id = session()->get('customer_id');
                    $customer_ss = 'cart'.$customer_id;
                    $con = session()->get($customer_ss);
                }
                else{
                    $con =session('cart.index');
                }
                $total =0;
				?>
				<table class="table table-condensed">
					<thead>
						<tr class="cart_menu">
							<td class="image">Hình ảnh</td>
							<td class="description">Tên sp</td>
							<td class="price">Giá</td>
							<td class="quantity">Số lượng</td>
							<td class="total">Tổng</td>
							<td></td>
						</tr>
					</thead>
					<tbody>
                    @if($con !=null)
						@foreach($con as $k => $content)
{{--                            @if($k!=0)--}}
{{--                        @for ($key=0;$key<sizeof($content)-1;$key++)--}}
						<tr>
							<td class="cart_product">
{{--                                $v_content->options->image--}}
								<a href=""><img src="{{URL::to('public/uploads/product/'. $content['image'])}}" width="90" alt="" /></a>
							</td>
							<td class="cart_description">
{{--                                {{$v_content->name}}--}}
								<h4><a href="">{{ $content['name']}}</a></h4>
								<p>Web ID: 1089772</p>
							</td>
							<td class="cart_price">
{{--                                $v_content->price--}}
								<p>{{number_format( $content['price']).' '.'vnđ'}}</p>
							</td>
							<td class="cart_quantity">
								<div class="cart_quantity_button">
									<form action="{{URL::to('/update-cart-quantity/'.$content['id'])}}" method="POST">
									{{ csrf_field() }}
{{--                                        $v_content->qty--}}
									<input class="cart_quantity_input" type="text" name="cart_quantity" value="{{ $content['qty']}}"  >
{{--                                        $v_content->rowId--}}
									<input type="hidden" value="{{$content['id']}}" name="rowId_cart" class="form-control">
									<input type="submit" value="Cập nhật" name="update_qty" class="btn btn-default btn-sm">
									</form>
								</div>
							</td>
                            <p hidden>{{$total+=$content['price'] *  $content['qty']}}</p>
							<td class="cart_total">
								<p class="cart_total_price">
                                    {{number_format( $content['price'] *  $content['qty'])}}
								</p>
							</td>
							<td class="cart_delete">
								<a class="cart_quantity_delete" href="{{URL::to('/delete-to-cart/'. $content['id'])}}"><i class="fa fa-times"></i></a>
							</td>
						</tr>
{{--                        @endif--}}
						@endforeach
                        @endif
{{--                        @endfor--}}
					</tbody>
				</table>
			</div>
		</div>
	</section> <!--/#cart_items-->

	<section id="do_action">
		<div class="container">
			<div class="row">
				<div class="col-sm-6">
					<div class="total_area">
						<ul>
{{--                            {{Cart::total().' '.'vnđ'}}--}}
							<li>Tổng <span>{{number_format($total).' '.'vnđ'}}</span></li>
{{--                            Cart::tax()--}}
                            <li>Thuế <span>10 vnd</span></li>
							<li>Phí vận chuyển <span>Free</span></li>
{{--                            {{Cart::total().' '.'vnđ'}}--}}
							<li>Thành tiền <span>{{number_format($total).' '.'vnđ'}}</span></li>
						</ul>
						{{-- 	<a class="btn btn-default update" href="">Update</a> --}}
							  <?php
                                   $customer_id = Session::get('customer_id');
                                   if($customer_id!=NULL){
                             ?>
                                <a class="btn btn-default check_out" href="{{URL::to('/checkout')}}">Thanh toán</a>
                                <?php
                            }else{
                                 ?>
                                 <a class="btn btn-default check_out" href="{{URL::to('/login-checkout')}}">Thanh toán</a>
                                 <?php
                             }
                                 ?>
					</div>
				</div>
			</div>
		</div>
	</section><!--/#do_action-->
{{--    @include('pages.layout.relate_item')--}}


@endsection
