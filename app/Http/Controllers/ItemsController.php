<?php

namespace App\Http\Controllers;

use App\Models\Items;
use App\Models\Shops;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ItemsController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    public function index($count = null) {
        $items = Items::paginate($count);
        return response()->json($items);
    }

    public function create(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'desc' => 'required',
            'shop_id' => 'required',
            'price' => 'required|numeric',
            'images.*' => 'required|mimes:jpg,bmp,png',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->all(), 200);
        };



        $shops = Shops::where('id', $request->shop_id)->where('admin_id', Auth::user()->id)->first();
        if (!$shops) {
            return response()->json(['message' => 'forbidden'], 403);
        }

        $files = [];
        $images = $request->file('images');

        foreach ($images as $image) {
            $fileName = Str::uuid();
            Storage::disk('public')->put($fileName, $image);

            $files[] = $fileName;
        }


        $item = new Items();
        $item->shop_id = $request->shop_id;
        $item->name = $request->name;
        $item->desc = $request->desc;
        $item->price = $request->price;
        $item->photo_paths = json_encode($files);

        $item->save();


        return response()->json($item);
    }

    public function metrics() {

    }

    public function delete($id) {
        $item = Items::find($id)->delete();
        return response()->json(['message' => 'deletion successful'], 200);
    }

    public function view($id) {
        $item = Items::find($id);
        return response()->json($item);
    }

    //
}
