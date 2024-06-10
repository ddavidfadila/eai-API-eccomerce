<?php

namespace App\Http\Controllers\api;

use App\Models\Product;
use App\PublisherService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try{
            $products = Product::all();

            return [
                'code' => 200,
                'message' => "berhasil mendapatkan semua data product",
                'data' => $products
            ];
        }catch(\Exception $e){
            return [
                'code' => 400,
                'message' => "error:". $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try{
            $request->validate([
                "name" => 'required|string',
                "description" => 'required|string',
                "category" => 'required|string',
                "qty" => 'required|int',
                "price" => 'required|int',
                "photo" => 'required|mimes:png,jpg,jpeg',
            ]);

            $filenameExt = $request->file('photo')->getClientOriginalName();
            $filename = pathinfo($filenameExt, PATHINFO_FILENAME);
            $extension = $request->file('photo')->getClientOriginalExtension();
            $filenameSave = $filename.'_'.time().'.'.$extension;
            $request->file('photo')->storeAs('public/product', $filenameSave);

            $product = Product::create([
                'name' => $request->name,
                'description' => $request->description,
                'category' => $request->category,
                'qty' => $request->qty,
                'price' => $request->price,
                'photo' => $filenameSave,
            ]);
            $productPubs = [
                "id" => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'category' => $product->category,
                'qty' => $product->qty,
                'price' => $product->price,
                'photo' => $product->photo,
            ];

            $mqService = new PublisherService();
            $mqService->storePublish(json_encode($productPubs));

            return [
                'code' => 200,
                'message' => "berhasil membuat data product baru",
                'data' => $product
            ];
        }catch(\Exception $e){
            return [
                'code' => 400,
                'message' => "error:". $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try{
            $product = Product::find($id);

            return [
                'code' => 200,
                'message' => "berhasil data product",
                'data' => $product
            ];
        }catch(\Exception $e){
            return [
                'code' => 400,
                'message' => "error:". $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try{
            $request->validate([
                "name" => 'required|string',
                "description" => 'required|string',
                "category" => 'required|string',
                "qty" => 'required|int',
                "price" => 'required|int',
                "photo" => 'mimes:png,jpg,jpeg',
            ]);

            $product = Product::find($id);

            if($request->hasFile('photo')){
                if(Storage::disk('public')->exists('public/product/'. $product->photo)){
                    Storage::disk('public')->delete('public/product/'. $product->photo);
                }
                $filenameExt = $request->file('photo')->getClientOriginalName();
                $filename = pathinfo($filenameExt, PATHINFO_FILENAME);
                $extension = $request->file('photo')->getClientOriginalExtension();
                $filenameSave = $filename.'_'.time().'.'.$extension;
                $request->file('photo')->storeAs('public/product', $filenameSave);
                $product->update(['photo' => $filenameSave]);
            }

            $product->update([
                'name' => $request->name,
                'description' => $request->description,
                'category' => $request->category,
                'qty' => $request->qty,
                'price' => $request->price,
            ]);

            $mqService = new PublisherService();
            $mqService->updatePublish(json_encode($product));

            return [
                'code' => 200,
                'message' => "berhasil mengubah data product",
                'data' => $product
            ];
        }catch(\Exception $e){
            return [
                'code' => 400,
                'message' => "error:". $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try{
            $product = Product::find($id);
                if(Storage::disk('public')->exists('public/product/'. $product->photo)){
                    Storage::disk('public')->delete('public/product/'. $product->photo);
            }
            $product->delete();

            $mqService = new PublisherService();
            $mqService->deletePublish(json_encode($product));

            return[
                "code" => 200,
                "message" => "berhasil menghapus product",
            ];
        }catch(\Exception $e){
            return[
                "code" => 400,
                "message" => "error:". $e->getMessage(),
                "error" => $e
            ];
        }
    }
}
