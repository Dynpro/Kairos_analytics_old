<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Looker_data;
use App\Models\Looker;
use App\Models\Looker_parent_dashboards;
use App\Models\Looker_parent_phm;
use App\Models\Snowflake_schema;

class LookerDataController extends Controller
{
    public function getLookerClientCategoryWise()
    {
        $dataset = looker_data::groupBy('looker_data.client_primary_id','looker_data.client_id','looker_data.client_name','looker_data.category_id','client_category.category','looker_data.subcategory_id','client_subcategory.subcategory')
        ->select('looker_data.client_primary_id','looker_data.client_id','looker_data.client_name','looker_data.category_id','client_category.category','looker_data.subcategory_id','client_subcategory.subcategory')
        ->join('client_category','looker_data.category_id','=','client_category.category_id')
        ->leftJoin('client_subcategory','looker_data.subcategory_id','=','client_subcategory.subcategory_id')
        ->join('client_folder_mapping','looker_data.client_primary_id','=','client_folder_mapping.id')
        ->where('client_folder_mapping.entity_id',auth()->user()->entity_id)
        ->orderBy('looker_data.category_id')
        ->orderBy('looker_data.client_name')
        ->get();  

        $finalArray = [];
        foreach($dataset as $key => $value)
        {
            if($value->subcategory_id == null)
            {
                $finalArray[$value->category][null][] = $value;
            }
            else
            {
                $finalArray[$value->category][$value->subcategory][] = $value;                
            }
            $finalArray[$value->category]['category_id'] = $value->category_id;

        }      
        return response()->json(['status' => 'Success', 'data' => $finalArray]);
    }
        public function getLookerClientCategoryWiseNew()
    {
        $dataset = looker_data::groupBy('looker_data.client_primary_id','looker_data.client_id','looker_data.client_name','looker_data.subcategory_id','client_subcategory.subcategory')
        ->select('looker_data.client_primary_id','looker_data.client_id','looker_data.client_name','looker_data.subcategory_id','client_subcategory.subcategory')
        ->leftJoin('client_subcategory','looker_data.subcategory_id','=','client_subcategory.subcategory_id')
        ->join('client_folder_mapping','looker_data.client_primary_id','=','client_folder_mapping.id')
        ->where('client_folder_mapping.entity_id',auth()->user()->entity_id)
        ->orderBy('looker_data.client_name')
        ->get();  

        $finalArray = [];
        foreach($dataset as $key => $value)
        {
            if($value->subcategory_id == null)
            {
                $finalArray[null][] = $value;
            }
            else
            {
                $finalArray[$value->subcategory][] = $value;                
            }

        }      
        return response()->json(['status' => 'Success', 'data' => $finalArray]);
    }
    public function getLookerClient()
    {
        $dataset = looker_data::groupBy('client_primary_id','client_id','client_name','category_id','subcategory_id')->select('client_primary_id','client_id','client_name','category_id','subcategory_id')->orderBy('client_name')->get();        
        return response()->json(['status' => 'Success', 'data' => $dataset]);
    }
    public function getLookerClientFolder()
    {
        $dataset = looker_data::groupBy('client_id','folder_id','folder_name')->select('client_id','folder_id','folder_name')->whereNotNull('folder_id')->orderBy('folder_name')->get();  
        $result = [];
        foreach($dataset as $key => $value)
        {
            if($value->folder_name != "")
            {
                $folder_name = explode(".",$value->folder_name);
                if(isset($folder_name[1])){
                    $result[$value->client_id][$value->folder_id]['folder_id']     = $value->folder_id;
                    $result[$value->client_id][$value->folder_id]['folder_name']   = trim($folder_name[1]);
                }
                else
                {
                    $result[$value->client_id][$value->folder_id]['folder_id']     = $value->folder_id;
                    $result[$value->client_id][$value->folder_id]['folder_name']   = trim($folder_name[0]);
                }                
            }
            else
            {
                $result[$value->client_id][$value->folder_id]['folder_id']     = $value->folder_id;
                $result[$value->client_id][$value->folder_id]['folder_name']   = "";
            }
        }      
        return response()->json(['status' => 'Success', 'data' => $result]);
    }
    public function getLookerFolderDashboard()
    {
        $dataset = looker_data::orderBy('client_id')->get();
        $result = [];
        foreach($dataset as $key => $value)
        {
            $result[$value->client_id][$value->folder_id][$value->dash_id]['dash_id']     = $value->dash_id;
            $result[$value->client_id][$value->folder_id][$value->dash_id]['title']       = $value->title;
            $result[$value->client_id][$value->folder_id][$value->dash_id]['embed_url']   = $value->embed_url;
        }
        return response()->json(['status' => 'Success', 'data' => $result]);
    }
    public function getUserAccessClientsCategoryWise()
    {
        $dataset = \DB::table('grp_role_usr_mapping')
        ->select('looker_data.client_primary_id','looker_data.client_id','looker_data.client_name','looker_data.category_id','client_category.category','looker_data.subcategory_id','client_subcategory.subcategory')
        ->join('users_dasboards_mapping','grp_role_usr_mapping.grp_usr_mapping_id','=','users_dasboards_mapping.grp_usr_mapping_id')
        ->join('looker_data','users_dasboards_mapping.client_primary_id','=','looker_data.client_primary_id')  
        ->join('client_category','looker_data.category_id','=','client_category.category_id')
        ->leftJoin('client_subcategory','looker_data.subcategory_id','=','client_subcategory.subcategory_id')      
        ->where('grp_role_usr_mapping.is_active',1)
        ->where('grp_role_usr_mapping.user_id',auth()->user()->id)
        ->groupBy('looker_data.client_primary_id','looker_data.client_id','looker_data.client_name','looker_data.category_id','client_category.category','looker_data.subcategory_id','client_subcategory.subcategory')
        ->orderBy('looker_data.category_id')
        ->orderBy('looker_data.client_name','asc')
        ->get();
        $finalArray = [];
        foreach($dataset as $key => $value)
        {
            if($value->subcategory_id == null)
            {
                $finalArray[$value->category][null][] = $value;
            }
            else
            {
                $finalArray[$value->category][$value->subcategory][] = $value;                
            }
            $finalArray[$value->category]['category_id'] = $value->category_id;

        }        
        return response()->json(['status' => 'Success', 'data' => $finalArray]);
    }
    public function getUserAccessClientsCategoryWiseNew()
    {
        $dataset = \DB::table('grp_role_usr_mapping')
        ->select('looker_data.client_primary_id','looker_data.client_id','looker_data.client_name','looker_data.subcategory_id','client_subcategory.subcategory')
        ->join('users_dasboards_mapping','grp_role_usr_mapping.grp_usr_mapping_id','=','users_dasboards_mapping.grp_usr_mapping_id')
        ->join('looker_data','users_dasboards_mapping.client_primary_id','=','looker_data.client_primary_id')  
        ->leftJoin('client_subcategory','looker_data.subcategory_id','=','client_subcategory.subcategory_id')      
        ->where('grp_role_usr_mapping.is_active',1)
        ->where('grp_role_usr_mapping.user_id',auth()->user()->id)
        ->groupBy('looker_data.client_primary_id','looker_data.client_id','looker_data.client_name','looker_data.subcategory_id','client_subcategory.subcategory')
        ->orderBy('looker_data.client_name')
        ->orderBy('looker_data.client_name','asc')
        ->get();
        $finalArray = [];
        foreach($dataset as $key => $value)
        {
            if($value->subcategory_id == null)
            {
                $finalArray[null][] = $value;
            }
            else
            {
                $finalArray[$value->subcategory][] = $value;                
            }

        }      
        return response()->json(['status' => 'Success', 'data' => $finalArray]);
    }
    public function getUserAccessClients()
    {
        $dataset = \DB::table('grp_role_usr_mapping')
        ->select('looker_data.client_primary_id','looker_data.client_id','looker_data.client_name','looker_data.category_id','client_category.category','looker_data.subcategory_id','client_subcategory.subcategory')
        ->join('users_dasboards_mapping','grp_role_usr_mapping.grp_usr_mapping_id','=','users_dasboards_mapping.grp_usr_mapping_id')
        ->join('looker_data','users_dasboards_mapping.client_primary_id','=','looker_data.client_primary_id')      
        ->join('client_category','looker_data.category_id','=','client_category.category_id')
        ->leftJoin('client_subcategory','looker_data.subcategory_id','=','client_subcategory.subcategory_id')        
        ->where('grp_role_usr_mapping.is_active',1)
        ->where('grp_role_usr_mapping.user_id',auth()->user()->id)
        ->groupBy('looker_data.client_primary_id','looker_data.client_id','looker_data.client_name','looker_data.category_id','client_category.category','looker_data.subcategory_id','client_subcategory.subcategory')
        ->orderBy('looker_data.client_name','asc')
        ->get();   
        return response()->json(['status' => 'Success', 'data' => $dataset]);
    }
    public function getUserAccessClientsNew()
    {
        $dataset = \DB::table('grp_role_usr_mapping')
        ->select('looker_data.client_primary_id','looker_data.client_id','looker_data.client_name','looker_data.subcategory_id','client_subcategory.subcategory')
        ->join('users_dasboards_mapping','grp_role_usr_mapping.grp_usr_mapping_id','=','users_dasboards_mapping.grp_usr_mapping_id')
        ->join('looker_data','users_dasboards_mapping.client_primary_id','=','looker_data.client_primary_id')     
        ->leftJoin('client_subcategory','looker_data.subcategory_id','=','client_subcategory.subcategory_id')        
        ->where('grp_role_usr_mapping.is_active',1)
        ->where('grp_role_usr_mapping.user_id',auth()->user()->id)
        ->groupBy('looker_data.client_primary_id','looker_data.client_id','looker_data.client_name','looker_data.subcategory_id','client_subcategory.subcategory')
        ->orderBy('looker_data.client_name','asc')
        ->get();   
        return response()->json(['status' => 'Success', 'data' => $dataset]);
    }
    public function getUserAccessFolders()
    {
        $dataset = \DB::table('grp_role_usr_mapping')
        ->select('looker_data.client_id','looker_data.client_name','looker_data.folder_id','looker_data.folder_name')
        ->join('users_dasboards_mapping','grp_role_usr_mapping.grp_usr_mapping_id','=','users_dasboards_mapping.grp_usr_mapping_id')
        ->join('looker_data','users_dasboards_mapping.dashboard_id','=','looker_data.folder_id')        
        ->where('grp_role_usr_mapping.is_active',1)
        ->where('grp_role_usr_mapping.user_id',auth()->user()->id)
        ->groupBy('looker_data.client_id','looker_data.client_name','looker_data.folder_id','looker_data.folder_name')
        ->orderBy('looker_data.client_name','asc')
        ->get();   
        $result = [];
        foreach($dataset as $key => $value)
        {
            if($value->folder_name != "")
            {
                $folder_name = explode(".",$value->folder_name);
                if(isset($folder_name[1])){
                    $result[$value->client_id][$value->folder_id]['folder_id']     = $value->folder_id;
                    $result[$value->client_id][$value->folder_id]['folder_name']   = trim($folder_name[1]);
                }
                else
                {
                    $result[$value->client_id][$value->folder_id]['folder_id']     = $value->folder_id;
                    $result[$value->client_id][$value->folder_id]['folder_name']   = trim($folder_name[0]);
                }   
            }
            else
            {
                $result[$value->client_id][$value->folder_id]['folder_id']     = $value->folder_id;
                $result[$value->client_id][$value->folder_id]['folder_name']   = "";
            }
        }      
        return response()->json(['status' => 'Success', 'data' => $result]);
    }
    public function getUserAccessdashboards()
    {
        $dataset = \DB::table('grp_role_usr_mapping')
        ->select('looker_data.client_id','looker_data.client_name','looker_data.folder_id','looker_data.folder_name','looker_data.dash_id','looker_data.title','looker_data.embed_url')
        ->join('users_dasboards_mapping','grp_role_usr_mapping.grp_usr_mapping_id','=','users_dasboards_mapping.grp_usr_mapping_id')
        ->join('looker_data','users_dasboards_mapping.sub_dashboard_id','=','looker_data.dash_id')
        ->where('grp_role_usr_mapping.is_active',1)
        ->where('grp_role_usr_mapping.user_id',auth()->user()->id)
        ->groupBy('looker_data.client_id','looker_data.client_name','looker_data.folder_id','looker_data.folder_name','looker_data.dash_id','looker_data.title','looker_data.embed_url')
        ->orderBy('looker_data.client_name','asc')
        ->get();   
        $result = [];
        foreach($dataset as $key => $value)
        {
            $result[$value->client_id][$value->folder_id][$value->dash_id]['dash_id']     = $value->dash_id;
            $result[$value->client_id][$value->folder_id][$value->dash_id]['title']       = $value->title;
            $result[$value->client_id][$value->folder_id][$value->dash_id]['embed_url']   = $value->embed_url;
        }
        return response()->json(['status' => 'Success', 'data' => $result]);
    }
    public function getDashboard(Request $request)
    {
        $dashboard_id   = "";
        $user_id        =auth()->user()->id;
        $user_group_id  = auth()->user()->user_group_id;    
        $client_id      = $request->client_id;
        if($request->flag == 1) // get default dashboard using client id
        {
            $lookerSetting = Looker::find('1');
            $api_url = $lookerSetting->api_url;
            $looker_id = $lookerSetting->client_id;
            $looker_secret = $lookerSetting->client_secret;

            $url = $api_url . "login?client_id=".$looker_id."&client_secret=".$looker_secret;
            $method = "POST";
            $resp= $this->curlCall($url, $method);
            $responseData = json_decode($resp,true);

            $query = array('access_token' => $responseData['access_token']);
            $url1 = $api_url . "folders/".$client_id;
            $method1 = "GET";
            $lookerData= $this->curlCall($url1, $method1,$query);
            $lookerData= json_decode($lookerData, true);
            $dashboard_id = $lookerData['dashboards'][0]['id'];        
        }
        else 
        {           
            $dashboard_id   = $request->dashboard_id;
        }

        $FolderDtl      = $this->getFolderDtl($client_id); 
            if(empty($FolderDtl[0]))  
            {
                 return response()->json(['Status' => 401,'Error' =>'Client Not Found']);
            }
        $ToggleAccess   = $request->permission;
        $models = $FolderDtl[0]->models;
        $group_id = intval($FolderDtl[0]->group_id);
        $external_group_id = $FolderDtl[0]->external_group_id;
        $user_attributes = "UA";
        if(isset($request->permission))
        {
            $user_permission=$request->permission;
        }
        else
        {
        $user_permission = "viewer";        
        }



        /* ========Pull Tabe Logic Start============== */
        $PullTable_Group_Id = 0;
        $permission = "";
        $first_name = "";
        $last_name = "";
        $external_user_id = "";
        $LicenceMessage = "";

        // if($user_group_id == "1001" || $user_group_id == "1003" || $user_group_id == "1011"){$PullTable_Group_Id =1;}
        // else{$PullTable_Group_Id =2;}


        if($user_permission == "explorer" || $user_permission == "schedular"){$permission = "explorer";}else{   $permission = "viewer";}

        /* ========Check User Already Logged in or Not============== */
        $CheckUserLogin = \DB::table('account_master')
            ->select('*')
            ->where(['actual_user'=>$user_id,'flag' =>1,'permission' =>$permission,'is_active'=>1])
            ->get();
          

        if(!empty($CheckUserLogin[0]))
        {
            /* ========If user Already Logged In set same user from Pull Table=================== */
            $first_name = $CheckUserLogin[0]->first_name;
            $last_name = $CheckUserLogin[0]->last_name;
            $external_user_id = $CheckUserLogin[0]->external_user_id;
            \DB::table('account_master')->where('account_id', $CheckUserLogin[0]->account_id)->update(['updated_at' => date('Y-m-d G:i:s')]);
        }
        else
        {
            /* ========else user Already Not Logged In set New user from Pull Table============== */
            $AvailableUsers = \DB::table('account_master')
            ->select('*')
            ->where(['flag' =>0,'permission' =>$permission,'is_active'=>1])
            ->get();

            if(!empty($AvailableUsers[0]))
            {
            /* ========Assign User from Pull Table============== */
                $first_name = $AvailableUsers[0]->first_name;
                $last_name = $AvailableUsers[0]->last_name;
                $external_user_id = $AvailableUsers[0]->external_user_id;
                \DB::table('account_master')->where('account_id', $AvailableUsers[0]->account_id)->update(['flag' => 1,'actual_user'=>$user_id,'updated_at' => date('Y-m-d G:i:s')]);
            }
            else
            {
            /* ========User Not Avaible Show Message============== */
                $LicenceMessage = "All user licences are being used at the current moment. Please try again after sometime.";
                return response()->json(['success'=>'Data is successfully added','url'=>"",'LicenceMessage'=>$LicenceMessage]);
            }

           
        }
        
        /* ========Pull Tabe Logic END================ */

        $api_url = config('looker_setting.api_url');
        if($permission =='viewer'){
            $permission1 = config('looker_setting.viewer');
        }
        else if($permission =='schedular'){
            $permission1 = config('looker_setting.schedular');
        }
        else{
            $permission1 = config('looker_setting.explorer');
        }

        $lookerSetting = Looker::find('1');
        $host = $lookerSetting->host;
        $secret = $lookerSetting->secret;
        //$secret = "e9294885fad0c1790f85af3b547b54cc70ea7040badd7341f84678928785103b";
        $embedpath= "/embed/dashboards/".$dashboard_id;
        //$host = "dynpro.cloud.looker.com";
        $path = "/login/embed/" . urlencode($embedpath);

        $json_nonce = json_encode(md5(uniqid()));
        $json_current_time = json_encode(time());
        $json_session_length = json_encode(3600);
        $json_external_user_id = json_encode($external_user_id);
        $json_first_name = json_encode($first_name);
        $json_last_name = json_encode($last_name);
        //$json_permissions = json_encode( array ( "see_user_dashboards", "see_lookml_dashboards", "access_data", "see_looks" ) );
        $json_permissions = json_encode( $permission1 );
        $json_models = json_encode( array ( $models ) );
        $json_group_ids = json_encode( array ( $group_id ) );  // just some example group ids
        $json_external_group_id = json_encode($external_group_id);
        $json_user_attributes = json_encode( array ( "an_attribute_name" => "my_value") );  // just some example attributes
        // NOTE: accessfilters must be present and be a json hash. If you don't need access filters then the php
        // way to make an empty json hash as an alternative to the below seems to be:
         $accessfilters = new \stdClass();
         //$accessfilters = (object) ['a' => 'new object'];
        /* $accessfilters = array (
          "<your_model_name>"  =>  array ( "view_name.dimension_name" => "<value>" )
        ); */
        $json_accessfilters = json_encode($accessfilters);

        $stringtosign = "";
        $stringtosign .= $host . "\n";
        $stringtosign .= $path . "\n";
        $stringtosign .= $json_nonce . "\n";
        $stringtosign .= $json_current_time . "\n";
        $stringtosign .= $json_session_length . "\n";
        $stringtosign .= $json_external_user_id . "\n";
        $stringtosign .= $json_permissions . "\n";
        $stringtosign .= $json_models . "\n";
        $stringtosign .= $json_group_ids . "\n";
        $stringtosign .= $json_external_group_id . "\n";
        $stringtosign .= $json_user_attributes . "\n";
        $stringtosign .= $json_accessfilters;

        $signature = trim(base64_encode(hash_hmac("sha1", utf8_encode($stringtosign), $secret, $raw_output = true)));
        // , $raw_output = true

        $queryparams = array (
            'nonce' =>  $json_nonce,
            'time'  =>  $json_current_time,
            'session_length'  =>  $json_session_length,
            'external_user_id'  =>  $json_external_user_id,
            'permissions' =>  $json_permissions,
            'models'  =>  $json_models,
            'group_ids' => $json_group_ids,
            'external_group_id' => $json_external_group_id,
            'user_attributes' => $json_user_attributes,
            'access_filters'  =>  $json_accessfilters,
            'first_name'  =>  $json_first_name,
            'last_name' =>  $json_last_name,
            'force_logout_login'  =>  false,
            'signature' =>  $signature
        );

        $querystring = "";
        foreach ($queryparams as $key => $value) {
          if (strlen($querystring) > 0) {
            $querystring .= "&";
          }
          if ($key == "force_logout_login") {
            $value = "true";
          }
          $querystring .= "$key=" . urlencode($value);
        }

        $final = "https://" . $host . $path . "?" . $querystring;
        return response()->json(['success'=>'Data is successfully fetch','url'=>$final,'LicenceMessage'=>$LicenceMessage]);
    }
    public function getFolderDtl($folder_id)
    {
        $FolderDtl = \DB::table('client_folder_mapping')
        ->select('*')
        ->where('folder_id' ,'=', $folder_id)
        ->get();

        return $FolderDtl;
    }
    public function getLookerFolderStructure()
    {
        try {
            $LookerStructure = Looker_parent_dashboards::select('*')->get()->toArray();
            return response()->json(['Code' => 200,'Status' => "Success", 'Message' => 'Client Folder Dashboard List.','Response' =>$LookerStructure]);
        } catch (\Exception $e) {
            return response()->json(['Status' => 401,'Error' =>'Failed', 'Message' => $e->getMessage()]);
        }
    }
    public function getParentPHM()
    {
        try {
            $PHM = Looker_parent_phm::select('*')->get()->toArray();
            return response()->json(['Code' => 200,'Status' => "Success", 'Message' => 'Parent PHM List.','Response' =>$PHM]);
        } catch (\Exception $e) {
            return response()->json(['Status' => 401,'Error' =>'Failed', 'Message' => $e->getMessage()]);
        }
    }
    public function getSchema()
    {
        try {
            $schemas = Snowflake_schema::select('*')->get();
            return response()->json(['Code' => 200,'Status' => "Success", 'Message' => 'Schema List.','Response' =>$schemas]);
        } catch (\Exception $e) {
            return response()->json(['Status' => 401,'Error' =>'Failed', 'Message' => $e->getMessage()]);
        }
    }
    public function getLookerGroups(){
        try {
        $lookerSetting = Looker::find('1');
        $api_url = $lookerSetting->api_url;
        $client_id = $lookerSetting->client_id;
        $client_secret = $lookerSetting->client_secret;

        $url = $api_url . "login?client_id=".$client_id."&client_secret=".$client_secret;
        $method = "POST";
        $resp= $this->curlCall($url, $method);
        $responseData = json_decode($resp,true);
        //echo $responseData['access_token'];

        //call to lookers folder api
        $query = array('access_token' => $responseData['access_token']);
        //$url1 = "https://dynpro.cloud.looker.com:443/api/3.1/groups";
        $url2 = $api_url."groups";
        $method2 = "GET";
        $groups= $this->curlCall($url2, $method2,$query);
        $groups= json_decode($groups, true);
                
        $groupData = array();
        $folderDataArr = array();
        foreach ($groups as $group){
            $groupData['id']= $group['id'];
            $groupData['name']= $group['name'];
            $groupDataArr[] = $groupData;
        }
        return response()->json(['Code' => 200,'Status' => "Success", 'Message' => 'Looker Group List.','Response' =>$groupDataArr]);
        } catch (\Exception $e) {
            return response()->json(['Status' => 401,'Error' =>'Failed', 'Message' => $e->getMessage()]);
        }
    }
    public function curlCall($url, $method, $query=null){
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS => $query,
            // CURLOPT_HTTPHEADER => array(
            //  "Content-Type: application/x-www-form-urlencoded"
            // ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        
        //echo $responseData['access_token'];
        return $response;
    }

    public function getStudioDashboard()
    {
        try {
            $groupedDashboards = \Cache::remember('studio_dashboards_grouped', 300, function () {
                $studioDashboards = \DB::table('studio_dashboards')
                    ->select('studio_dashboards.id', 'studio_dashboards.dash_id', 'studio_dashboards.title',
                             'studio_dashboards.embed_url', 'studio_dashboards.folder_name',
                             'studio_dashboards.is_active', 'studio_dashboards.client_id',
                             'studio_dashboards.client_name', 'studio_dashboards.folder_id')
                    ->where('studio_dashboards.is_active', 1)
                    ->orderBy('studio_dashboards.folder_name')
                    ->orderBy('studio_dashboards.title')
                    ->get();

                $grouped = [];
                foreach ($studioDashboards as $dashboard) {
                    $folderName = $dashboard->folder_name ?: 'Uncategorized';
                    $grouped[$folderName][] = [
                        'dash_id'     => $dashboard->dash_id,
                        'title'       => $dashboard->title,
                        'embed_url'   => $dashboard->embed_url,
                        'url'         => $dashboard->embed_url,
                        'folder_name' => $dashboard->folder_name,
                        'is_active'   => $dashboard->is_active,
                    ];
                }
                return $grouped;
            });

            return response()->json([
                'Code'     => 200,
                'Status'   => "Success",
                'Message'  => "Studio Dashboard List",
                'Response' => $groupedDashboards
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'Code'    => 500,
                'Status'  => "Failed",
                'Message' => $e->getMessage()
            ]);
        }
    }

}
