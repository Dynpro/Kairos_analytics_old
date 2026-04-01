<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Looker_data;
use App\Models\Looker;
use App\Models\Groups;
use App\Models\Client;
use App\Models\users_folder_access;
use App\Models\Looker_parent_dashboards;
use App\Models\Looker_parent_phm;
use App\Models\Snowflake_schema;
use App\Models\Client_Category;
use App\Models\Client_SubCategory;
use App\Libraries\Helpers;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response as Download;
use Illuminate\Validation\Rule;

class ClientController extends Controller
{
	public function __construct()
    {
		$this->helper = new Helpers;		
    }
	protected function buildFailedValidationResponse(Request $request, array $errors) {
        return response()->json(["Code"=> 406 , 'Status' => 'Failed' ,"Message" => "forbidden" , "Errors" =>$errors]);
    }
    public function index()
    {
        try {
            $clientData = DB::select("select id,folder_name,schema_name,category,subcategory,is_approved,IF(id IN(select id from(
            select distinct a.id,IF(b.id is NOT null,true,false) as phm from(
            (select id,folder_id from client_folder_mapping where type='Client') a
            left JOIN
            (select id,parent_folder_id from client_folder_mapping WHERE type='PHM'
            and parent_folder_id is not null) b
            on a.`folder_id`=b.`parent_folder_id`)) derived where phm=true),true,false) phm
            from client_folder_mapping where type='Client' AND entity_id='".env('env_entity_id')."' AND is_active='1'");
            return response()->json(['Code' => 200,'Status' => "Success", 'Message' => 'Client List.','Response' =>$clientData]);
        } catch (\Exception $e) {
            return response()->json(['Status' => 401,'Error' =>'Failed', 'Message' => $e->getMessage()]);
        }
    }
    public function store(Request $request)
    {
            $filePath = "";        
            if ($request->hasFile('file')) {
                $this->validate($request, [
                 'file' => 'dimensions:max_width=225,max_height=50',
                 'folder_name'  => ['required', 'string', 'max:255',
                 Rule::unique('client_folder_mapping')->where(function ($query) use ($request) {
                    return $query->where('is_active', 1);
                    }),
                ],
                 'schema_name'  => ['required'],
                 'folder_id'    => ['required'],
                 'group_id'     => ['required']
                 ]);

            $file = $request->file('file');
            $name = time() .'_'. $file->getClientOriginalName();
            $filePath = 'CLIENT-LOGOS/' . $name;
            Storage::disk('s3')->put($filePath, file_get_contents($file)); 
            }
            else
            {
               $this->validate($request, [
                 'folder_name'  => ['required', 'string', 'max:255',
                 Rule::unique('client_folder_mapping')->where(function ($query) use ($request) {
                    return $query->where('is_active', 1);
                    }),
                ],
                 'schema_name'  => ['required'],
                 'folder_id'    => ['required'],
                 'group_id'     => ['required']
                 ]); 
            }

            

        try {
            // return $request->all();

            $client_id = Client::create([
            'folder_id' => $request->folder_id,
            'entity_id' => env('env_entity_id'),               
            'subcategory' => (isset($request->subcategory) && $request->subcategory != "")?$request->subcategory:null,             
            'folder_name' => $request->folder_name,                    
            'schema_name' => $request->schema_name,            
            'contact_email' => $request->contact_email,
            'external_group_id' => $request->external_group_id,               
            'group_id' => $request->group_id,               
            'models' => $request->models,               
            'access_filters' => $request->access_filters,   
            'logo' =>  $filePath,  
            'type' => "Client", 
            'created_by' => auth()->user()->id,         
            ])->id;

            if(!empty($request->phm_folder_id)){
                foreach ($request->phm_folder_id as $key => $value) {
                    if(!empty($value)){
                         Client::insert([               
                            'subcategory' => (isset($request->subcategory) && $request->subcategory != "")?$request->subcategory:null,
                            'folder_id' => $value,
                            'parent_folder_id' => $request->folder_id,
                            'folder_name' => $request->folder_name,                    
                            'schema_name' => $request->schema_name,            
                            'contact_email' => $request->contact_email, 
                            'external_group_id' => $request->external_group_id,               
                            'group_id' => $request->group_id,               
                            'models' => $request->models,               
                            'access_filters' => $request->access_filters,    
                            'type' => 'PHM',
                            'created_by' => auth()->user()->id,     
                        ]);
                    }
                }
            }
            $Client_Details = $this->show($client_id);
            return response()->json(['Code' => 200,'Status' => "Success", 'Message' => 'Client has been successfully created!!','Response' =>$Client_Details]);
        } catch (\Exception $e) {
            return response()->json(['Status' => 401,'Error' =>'Failed', 'Message' => $e->getMessage()]);
        }
    }
    public function show($id)
    {
        $clientData = Client::find($id);
        $phmfolderdata= \DB::table('client_folder_mapping')
        ->select('client_folder_mapping.folder_id')
        ->where('parent_folder_id',$clientData->folder_id)
        ->get();
        $client_details['client_details']=$clientData;
        $client_details['client_details']['phm_folder_id']=$phmfolderdata;
        return $client_details;
    }
    public function edit($id)
    {
        try {
        $clientData = Client::where('id',$id)->where('is_active',1)->get();
            if(!empty($clientData[0])){
                $phmfolderdata= \DB::table('client_folder_mapping')
                ->select('client_folder_mapping.folder_id')
                ->where('parent_folder_id',$clientData[0]->folder_id)
                ->get();
                // unset($clientData[0]['logo']);
                $clientData[0]['phm_folder_id']=$phmfolderdata;
                $logopath ="";
                if(!empty($clientData[0]->logo) && $clientData[0]->logo != Null)
                {
                    $s3 = \Storage::disk('s3');
                    $client = $s3->getDriver()->getAdapter()->getClient();
                    $expiry = "+1 minutes";

                    $command = $client->getCommand('GetObject', [
                      'Bucket' => 'kairos-next-gen-storage', // bucket name
                      'Key'    => $clientData[0]->logo
                    ]);

                    $request = $client->createPresignedRequest($command, $expiry);
                    $logopath =  (string) $request->getUri(); // it will return signed URL
                }
                $clientData[0]['logo']=$logopath;
                $client_details['client_details']=$clientData[0];
                return response()->json(['Code' => 200,'Status' => "Success", 'Message' => 'Client Details.','Response' =>$client_details]);
            }
            else
            {
                return response()->json(['Status' => 401,'Error' =>'Failed', 'Message' => 'Data not found']);
            }        
        } catch (\Exception $e) {
            return response()->json(['Status' => 401,'Error' =>'Failed', 'Message' => $e->getMessage()]);
        }
    }
    public function update(Request $request)
    {
        if(isset($request->id))
        {
            $clientdata= \DB::table('client_folder_mapping')
            ->select('client_folder_mapping.folder_id')
            ->where('id',$request->id)
            ->get();
        }else
        {
            return response()->json(['Status' => 401,'Error' =>'Failed', 'Message' => "Please Provide id"]);
        }
        
            $filePath ="";
            $client = Client::find($request->id);
            if ($request->hasFile('file')) {
                $this->validate($request, [
                 'file' => 'dimensions:max_width=225,max_height=50',
                 'folder_name'  => ['required', 'string', 'max:255',
                    Rule::unique('client_folder_mapping')->where(function ($query) use ($request,$clientdata) {
                        return $query->where('is_active', 1)->where('id','!=', $request->id)->where('parent_folder_id','!=', $clientdata[0]->folder_id);
                    }),
                ],
                 'schema_name'  => ['required'],
                 'folder_id'    => ['required'],
                 'group_id'     => ['required']
                 ]);

                $file = $request->file('file');
                $name = time() .'_'. $file->getClientOriginalName();
                $filePath = 'CLIENT-LOGOS/' . $name;
                Storage::disk('s3')->put($filePath, file_get_contents($file)); 
                $client->logo                 = $filePath;  
            }  
            else
            {
               $this->validate($request, [
                 'folder_name'  => ['required', 'string', 'max:255',
                    Rule::unique('client_folder_mapping')->where(function ($query) use ($request,$clientdata) {
                        return $query->where('is_active', 1)->where('id','!=', $request->id)->where('parent_folder_id','!=', $clientdata[0]->folder_id);
                    }),
                ],
                 'schema_name'  => ['required'],
                 'folder_id'    => ['required'],
                 'group_id'     => ['required']
                 ]); 
            }

        try{                       
            $client->subcategory         = (isset($request->subcategory) && $request->subcategory != "")?$request->subcategory:null;
            $client->folder_id            = $request->folder_id;
            $client->folder_name          = $request->folder_name;
            $client->schema_name          = $request->schema_name;
            $client->contact_email        = $request->contact_email;
            $client->external_group_id    = $request->external_group_id;              
            $client->group_id             = $request->group_id;             
            $client->models               = $request->models;             
            $client->access_filters       = $request->access_filters;  
            $client->updated_by           = auth()->user()->id;     
            $client->save();

            Client::where('type', 'PHM')->where('parent_folder_id', $clientdata[0]->folder_id)->delete();

            if(!empty($request->phm_folder_id)){
                foreach ($request->phm_folder_id as $key => $value) {
                    if(!empty($value)){
                         Client::insert([
                            'category' => $request->category,                    
                            'subcategory' => (isset($request->subcategory) && $request->subcategory != "")?$request->subcategory:null,
                            'folder_id' => $value,
                            'parent_folder_id' => $request->folder_id,
                            'folder_name' => $request->folder_name,                    
                            'schema_name' => $request->schema_name,            
                            'contact_email' => $request->contact_email, 
                            'external_group_id' => $request->external_group_id,               
                            'group_id' => $request->group_id,               
                            'models' => $request->models,               
                            'access_filters' => $request->access_filters,    
                            'type' => 'PHM',
                            'created_by' => auth()->user()->id,     
                        ]);
                    }
                }
            }
            $client_details = $this->show($request->id);

            return response()->json(['Code' => 200,'Status' => "Success", 'Message' => 'Client updated successfully.','Response' =>$client_details]);
        } catch (\Exception $e) {
            return response()->json(['Status' => 401,'Error' =>'Failed', 'Message' => $e->getMessage()]);
        }
    }    
    public function destroy($id)
    {
        try{
            DB::table('client_folder_mapping')
            ->where('id', $id)
            ->update(['is_active' => '0']);
            return response()->json(['Code' => 200,'Status' => "Success", 'Message' => 'Client has been deleted successfully!!']);
        } catch (\Exception $e) {
            return response()->json(['Status' => 401,'Error' =>'Failed', 'Message' => $e->getMessage()]);
        }
    }
    public function getclient_categort()
    {
        try{
            $cat= \DB::table('client_category')
            ->select('*')
            ->get();
            return response()->json(['Code' => 200,'Status' => "Success", 'Message' => 'Client Category List','Response' =>$cat]);
        } catch (\Exception $e) {
            return response()->json(['Status' => 401,'Error' =>'Failed', 'Message' => $e->getMessage()]);
        }
        
    }
    public function getclient_subcategort()
    {
        try{
            $subcat= \DB::table('client_subcategory')
            ->select('*')
            ->get();
            return response()->json(['Code' => 200,'Status' => "Success", 'Message' => 'Client Subcategory List','Response' =>$subcat]);
        } catch (\Exception $e) {
            return response()->json(['Status' => 401,'Error' =>'Failed', 'Message' => $e->getMessage()]);
        }
        
    }
}