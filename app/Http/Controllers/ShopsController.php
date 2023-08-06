<?php

namespace App\Http\Controllers;

use App\Models\Items;
use App\Models\Shops;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;


class ShopsController extends Controller
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

    public function create(Request $request) {

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'desc' => 'required|max:200',
            'image' => 'file|mimes:jpg,bmp,png',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->all(), 200);
        };


        $fileName = Str::uuid();

        Storage::disk('public')->put($fileName, $request->image);

        $shop = new Shops();
        $shop->admin_id = Auth::user()->id;
        $shop->name = $request->name;
        $shop->mark = 0;
        $shop->desc = $request->desc;
        $shop->pic_path = $fileName;


        $shop->save();

        return response()->json($shop);

    }

    public function deleteShop($id) {
        $shop = Shops::find($id)->items->delete();
        $shop = Shops::find($id)->delete();
        return response()->json(['message' => 'deletion successful'], 200);

    }

    public function listMy() {
        return response()->json(Shops::where('admin_id', Auth::user()->id)->get());
    }

    public function getItemsInShop($id) {
        $shop = Shops::find($id)->items;
        return response()->json($shop);
    }

    //
}
