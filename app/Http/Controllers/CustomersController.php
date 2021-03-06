<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class CustomersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($id = false)
    {
        if ($id) {
            $users = User::find($id);
            $users->storage_path = url("/storage/");
        }else {
            $users = User::all();
        }
        
        return response()->json([
            'success' => true,
            'message' =>'berhasil mendapatkan data user',
            'data'    => $users
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $rules = [
            'name' => 'required',
            'contact' => 'required',
            'email' => 'required|unique:users|email:rfc,dns',
            'address' => 'required',
            'discount_amount' => 'required|integer',
            'discount_type' => 'required',
            'ktp' => 'required',
        ];
        $validator = validation_request($request,$rules);
        if(!$validator->success) return response()->json($validator,422);
        // dd($request->contact);
        // return response()->json(["errors"=>[["param"=>"njok","msg"=>$request->contact]]],422);
        
        $customer = new User;
        $customer->name = $request->input('name');
        $customer->contact = $request->contact;
        $customer->email = $request->input('email');
        $customer->address = $request->input('address');
        $customer->discount_amount = $request->input('discount_amount');
        $customer->discount_type = $request->input('discount_type');
        $customer->ktp = $request->input('ktp');

        if ($customer->save()) {
            return response()->json([
                'success' => true,
                'message' => 'customer Berhasil Disimpan!',
                'data' => $customer
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'customer Gagal Disimpan!',
            ], 400);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $rules = [
            'name' => 'required',
            'contact' => 'required',
            'email' => 'required|unique:users',
            'address' => 'required',
            'discount_amount' => 'required|integer',
            'discount_type' => 'required',
        ];
        $validator = validation_request($request,$rules);
        if(!$validator->success) return response()->json($validator,422);

        $customer = User::where('id',$id)->update([
            'name'     => $request->input('name'),
            'contact'   => $request->input('contact'),
            'email'   => $request->input('email'),
            'address'   => $request->input('address'),
            'discount_amount'   => $request->input('discount_amount'),
            'discount_type'   => $request->input('discount_type'),
            'ktp'   => $request->input('ktp'),
        ]);

        if ($customer) {
            return response()->json([
                'success' => true,
                'message' => 'customer Berhasil Disimpan!',
                'data' => $customer
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'customer Gagal Disimpan!',
            ], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if($user = User::find($id)){
            remove_file($user->ktp);
            $user->delete();
            return response()->json(['message'=>'success']);
        }
        return response()->json(['message'=>'failed'],501);
    }

    function upload_ktp(Request $request, $user_id=null)
    {
        $rules = [
            'image' => 'required|image',
        ];
        $validator = validation_request($request,$rules);
        if(!$validator->success) return response()->json($validator,422);

        $path = "ktp_files";
        $uploaded = upload_file($request->file('image'),$path);
        if ($user_id) {
            $item = User::findOrFail($user_id);
            $item->ktp = $uploaded;
            $item->save();
        }

        if ($request->remove) {
            $request->request->add(['path'=>$request->remove]);
            $this->remove_ktp($request);
        }
        
        return response()->json([
            'success' => true,
            'message' => $uploaded ? 'ktp berhasil diupload!' : 'ktp gagal diupload!',
            'path'    => $uploaded
        ], $uploaded ? 200 : 400);
    }

    function remove_ktp(Request $request, $user_id=null)
    {
        $rules = [
            'path' => 'required',
        ];
        $validator = validation_request($request,$rules);
        if(!$validator->success) return response()->json($validator,422);

        return response()->json([
            'success' => true,
            'message' => remove_file($request->path) ? 'success' : 'failed'
        ], 200);
    }
}
