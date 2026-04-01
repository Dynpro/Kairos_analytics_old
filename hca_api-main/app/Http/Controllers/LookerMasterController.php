<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Looker;

class LookerMasterController extends Controller
{

	protected function buildFailedValidationResponse(Request $request, array $errors) {
        return response()->json(["Code"=> 406 , 'Status' => 'Failed' ,"Message" => "forbidden" , "Errors" =>$errors]);
    }   
    public function index(){
        try {
            $lookerData = \DB::table('looker')
            ->select('*')
            ->get();
        return response()->json(['Code' => 200,'Status' => "Success", 'Message' => 'List','Response' =>$lookerData]);
        } catch (\Exception $e) {
            return response()->json(['Status' => 401,'Error' =>'Failed', 'Message' => $e->getMessage()]);
        }
    }
    public function edit($id){
        try {
            $lookerData = Looker::find($id);
        return response()->json(['Code' => 200,'Status' => "Success", 'Message' => 'List','Response' =>$lookerData]);
        } catch (\Exception $e) {
            return response()->json(['Status' => 401,'Error' =>'Failed', 'Message' => $e->getMessage()]);
        }
    }
    public function update(Request $request,$id)
    {     
        $validator = $this->validate($request, [
            'api_url'       => ['required'],
            'client_id'     => ['required'],
            'client_secret' => ['required'],
            'secret'        => ['required'],
            'host'          => ['required']
        ]);
        try {
            $flight = Looker::updateOrCreate(
                ['id' => $id],
                ['api_url' => $request->api_url, 
                'client_id' => $request->client_id,
                'client_secret' => $request->client_secret,
                'secret' => $request->secret,
                'host' => $request->host
                ]
            );
            return response()->json(['Code' => 200,'Status' => "Success", 'Message' => 'Deatils Updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['Status' => 401,'Error' =>'Failed', 'Message' => $e->getMessage()]);
        }
    }
   
}
   