<?php

namespace App\Http\Controllers;

use App\Models\CartItems;
use App\Models\Items;
use App\Models\OrderItems;
use App\Models\Orders;
use App\Models\Users;
use App\Models\Verify;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class UserController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function acceptLink($token) {
        $verify = Verify::where('token', $token)->first();

        if (!$verify) {
            return response()->json(['message' => 'bad token D:'], 403);
        }

        switch($verify->task)
            {
                case 'verify_email':
                    $user = Users::where('id', $verify->user_id)->update(['verified' => true]);
                    $verify->delete();
                    return response("All good, email has been verified");
                case 'password_reset':
                    return $this->passwordReset($verify->user_id);
                case 'order_confirmation':
                    $order = Orders::where('id', $verify->order_id)->update(['pay' => 'payed']);
                    $verify->delete();
                    return response()->json(['message' => 'ok'], 403);
            }
    }

    function passwordReset($user_id) {
        return response($user_id);
    }

    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'login' => 'required|unique:users,login',
            'email' => 'required|email|unique:users,email',
            'password' => 'required',
            'image' => 'file|mimes:jpg,bmp,png',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->all(), 200);
        };

        $user = new Users();
        $user->name = $request->name;
        $user->login = $request->login;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->api_token = Str::random(64);
        $user->role = 'user';

        if ($request->hasFile('image')) {
            $fileName = Str::uuid();

            Storage::disk('public')->put($fileName, $request->image);

            $user->pic_path = $fileName . '/?ext=' . $request->image->extension();

        } else {
            $user->pic_path = null;

        }


        $user->save();

        $emailVerify = new Verify();

        $emailVerify->user_id = $user->id;
        $emailVerify->token = Str::random(48);

        $emailVerify->save();

        return response()->json($user, 200);

    }

    public function login(Request $request) {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->all(), 200);
        };

        $user = Users::where('email', $request->email)->first();
        if($user && Hash::check($request->password, $user->password)) {
            return response()->json($user, 200);
        }
        return response()->json(['message' => "User details incorrect"], 403);
    }

    public function index() {
        return response()->json(Auth::user());
    }



    public function addToCart(Request $request) {
        $cart = CartItems::create([
            'user_id' => Auth::user()->id,
            'item_id' => $request->item_id,
            'count' => $request->count,
        ]);

        return response()->json($cart);
    }

    public function showCart() {
        $cart = Auth::user()->cart;

        $result = [];
        foreach ($cart as $item) {
            $result[] = $item->with('item')->get();
        }

        return response()->json(reset($result));
    }

    public function deleteCart($ids) {
        $ids = explode(',', $ids);
        foreach ($ids as $id) {
            $cart = CartItems::where('user_id', Auth::user()->id)->where('id', $id)->delete();
        }

        return response()->json(['message' => 'deletion successful']);
    }

    public function createOrder(Request $request) {

        $ids = explode(',',$request->item_id);
        $idsArr = [];
        foreach ($ids as $id) {
            $idsArr[] = explode(':', $id);
        }

        // return response()->json($idsArr);

        $order = new Orders();
        $order->user_id = Auth::user()->id;
        $order->save();

        $result = [];

        foreach ($idsArr as $id) {
            $itemsTable = Items::where('id', $id)->first();
            if ($itemsTable) {
                $current = new OrderItems();
                $current->order_id = $order->id;
                $current->price = $itemsTable->price;
                $current->item_id = $id[0];
                $current->count = $id[1];
                $current->save();
                $result[] = $current;
            }
        }

        if (!$result) {
            $orderDel = Orders::find($order->id)->delete();
            return response()->json(['message' => 'items not found'], 422);
        }

        $verify = new Verify();
        $verify->task = 'order_confirmation';
        $verify->token = Str::random(128);
        $verify->order_id = $order->id;
        $verify->save();

        // send request to payment api
        // receive link

        $order['paylink'] = 'rrrrr';

        return response()->json($order);

    }

    public function listOrders() {
        $orders = Auth::user()->order;

        $result = [];
        foreach ($orders as $order) {
            $result[] = $order->with('order_items.item')->get();
        }

        return response()->json(reset($result));
    }

    //
}
