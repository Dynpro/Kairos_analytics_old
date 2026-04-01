<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Looker_data;
use App\Models\Looker;
use App\Models\Groups;
use App\Models\GroupMasterDashboards;
use App\Models\Roles;
use App\Libraries\Helpers;
use Illuminate\Validation\Rule;

class GroupController extends Controller
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
    	$groupData = \DB::table('group_role_dashboards_mapping as a')
        ->select('a.group_id','a.role_id','b.group_name','c.role')
        ->join('groups as b','a.group_id','=','b.group_id')
        ->join('roles as c','a.role_id','=','c.role_id')
        ->where('a.is_active',1)
        ->where('b.is_active',1)
        ->where('b.entity_id',env('env_entity_id'))
        ->groupBy('a.group_id','a.role_id','b.group_name','c.role')
        ->orderBy('b.group_name')
        ->get();  


        return response()->json(['Code' => 200,'Status' => "Success", 'Message' => 'Group List.','Response' =>$groupData]);
        } catch (\Exception $e) {
            return response()->json(['Status' => 401,'Error' =>'Failed', 'Message' => $e->getMessage()]);
        }
    }
    public function store(Request $request)
    {	
    	$validator = $this->validate($request, [
            'group_flag' => ['required'],
            'role_id' => ['required'],
            'dashboard_access' => ['required']
        ]);
    	if($request->group_flag == 0)
        {
        	$validator = $this->validate($request, [
	            'group_name' => ['required',Rule::unique('groups')->where(function ($query) use ($request) {
                    return $query->where('is_active', 1);
                })
	        ],
	        ]);
            $group_id = Groups::create([
            'group_name'        => $request->group_name,
            'entity_id'         => env('env_entity_id'),
            'created_by'        => auth()->user()->id,             
            ])->group_id; 
        }
        else
        {
            $group_id = $request->group_id;
        }
        $GroupDtl = DB::table('group_role_dashboards_mapping')
        ->select('group_role_dashboards_mapping.group_id','group_role_dashboards_mapping.role_id')
        ->join('groups','group_role_dashboards_mapping.group_id','=','groups.group_id')
        ->where('group_role_dashboards_mapping.is_active', 1)
        ->where('groups.is_active', 1)
        ->where('group_role_dashboards_mapping.group_id', $group_id)
        ->where('group_role_dashboards_mapping.role_id', $request->role_id)
        ->get();
        if (!empty($GroupDtl[0])) {
        	$exp[] = "Group Role Mapping Already Exits...";
		    $exception = array("Exception" => $exp);
		    return response()->json(['Code' => 401,'Status' => 'Failed',"Message" => "forbidden",'Errors' => $exception]);
        } else {
        
	        try {
		        /*============Store group Dashboard Mapping==============*/ 
		        foreach($request->dashboard_access as $key => $value)
		        {           
		            $usr_dash_id = GroupMasterDashboards::create([
		            'group_id'                      => $group_id,
		            'role_id'                       => $request->role_id,
		            'subcategory_id'            	=> (isset($value['subcategory_id']))?$value['subcategory_id']:null,
		            'client_primary_id'            => (isset($value['client_primary_id']))?$value['client_primary_id']:null,
		            'client_id'                    => (isset($value['client_id']))?$value['client_id']:null,
		            'dashboard_id'                 => (isset($value['folder_id']))?$value['folder_id']:null,
		            'sub_dashboard_id'             => (isset($value['dashboard_id']))?$value['dashboard_id']:null,
		            'created_by'                   => auth()->user()->id,             
		            ])->usr_dash_id;
		            $GroupMaster_Details = $this->show($group_id,$request->role_id);
		        }
		        return response()->json(['Code' => 200,'Status' => "Success", 'Message' => 'Group Master added successfully.','Response' =>$GroupMaster_Details]);
		    } catch (\Exception $e) {
	            return response()->json(['Status' => 401,'Error' =>'Failed', 'Message' => $e->getMessage()]);
	        }
    	}
    }
    public function show($group_id,$role_id)
    {
    	try {
    	$GroudDtl = DB::table('groups')
        ->select('group_id','group_name')
                    ->where('is_active', 1)
                    ->where('group_id', $group_id)
                    ->where('entity_id', env('env_entity_id'))
                    ->get();
            if(!empty($GroudDtl[0]))
            {
            	$GroudDtl[0]->role_id = $role_id;
		        $dashobardMapping = \DB::table('group_role_dashboards_mapping')
		        ->select('*')
		         ->where('group_role_dashboards_mapping.group_id',$group_id)
		         ->where('group_role_dashboards_mapping.role_id',$role_id)
		        ->get();
		        if(!empty($dashobardMapping[0]))
		        {
		        	$SelectedClientData=[];
			        foreach($dashobardMapping as $value)
			        {
			            $SelectedClientData[$value->category_id][$value->subcategory_id][$value->client_primary_id][$value->dashboard_id][]=$value->sub_dashboard_id;
			        }
			        $response['group_details'] = $GroudDtl;
			        $response['dashboard_details'] = $SelectedClientData;
	        		return $response;
		        }
		        else{
		        	$exp[] = "Group Role Mapping Not Found";
		    		$exception = array("Exception" => $exp);
		    		return response()->json(['Code' => 401,'Status' => 'Failed',"Message" => "forbidden",'Errors' => $exception]);
		        }
		        
            }
            else
            {
            	return response()->json(['Status' => 401,'Error' =>'Failed', 'Message' => "Group Not Found"]);
            }
        
        } catch (\Exception $e) {
            return response()->json(['Status' => 401,'Error' =>'Failed', 'Message' => $e->getMessage()]);
        }
    }
    public function edit($group_id,$role_id)
    {
    	try {
    	$GroudDtl = DB::table('groups')
        ->select('group_id','group_name')
                    ->where('is_active', 1)
                    ->where('group_id', $group_id)
                    ->where('entity_id', env('env_entity_id'))
                    ->get();
            if(!empty($GroudDtl[0]))
            {
            	$GroudDtl[0]->role_id = $role_id;
		        $dashobardMapping = \DB::table('group_role_dashboards_mapping')
		        ->select('subcategory_id','client_primary_id','client_id','dashboard_id as folder_id','sub_dashboard_id as dashboard_id')
		         ->where('group_role_dashboards_mapping.group_id',$group_id)
		         ->where('group_role_dashboards_mapping.role_id',$role_id)
		        ->get();
		        if(!empty($dashobardMapping[0]))
		        {
			        $response['group_details'] = $GroudDtl;
			        $response['dashboard_details'] = $dashobardMapping;
	        		return response()->json(['Code' => 200,'Status' => "Success", 'Message' => 'Group List.','Response' =>$response]);
		        }
		        else{
		        	$exp[] = "Group Role Mapping Not Found";
		    		$exception = array("Exception" => $exp);
		    		return response()->json(['Code' => 401,'Status' => 'Failed',"Message" => "forbidden",'Errors' => $exception]);
		        }
		        
            }
            else
            {
            	return response()->json(['Status' => 401,'Error' =>'Failed', 'Message' => "Group Not Found"]);
            }
        
        } catch (\Exception $e) {
            return response()->json(['Status' => 401,'Error' =>'Failed', 'Message' => $e->getMessage()]);
        }
    }
    public function update(Request $request, $id)
    {
    	$datas = DB::table('groups')
		->join('group_role_dashboards_mapping', 'groups.group_id', '=', 'group_role_dashboards_mapping.group_id')
		->select(DB::raw("count(group_role_dashboards_mapping.group_id) as count"))
		->groupBy('group_role_dashboards_mapping.role_id')
		->where('groups.group_id',$request->old_group_id)
		->where('groups.is_active',1)
		->where('group_role_dashboards_mapping.is_active',1)
		->get();
		$GroupRoleCount = sizeof($datas);

		if($request->group_flag == 0){
		    $validator = $this->validate($request, [
		            'group_name' => ['required',Rule::unique('groups')->where(function ($query) use ($request,$id) {
	                    return $query->where('is_active', 1)->where('group_id','!=', $id);
	                })
		        ]
	        ]);
    		
        	$group_id = Groups::create([
            'group_name'        => $request->group_name,
            'entity_id'         => env('env_entity_id'),
            'created_by'        => auth()->user()->id,             
            ])->group_id; 

            if($GroupRoleCount == 1)
    		{	
    		Groups::where('group_id', $request->old_group_id)->update(['is_active' => '0']);    		
    		}
        }
        else
        {
            $group_id = $request->group_id;
        }
	    $role_id = $request->role_id;
	    try {
	    	if($request->group_id  != $request->old_group_id || $request->role_id != $request->old_role_id)
	    	{
	    		$GroupDtl = DB::table('group_role_dashboards_mapping')
		        ->select('group_role_dashboards_mapping.group_id','group_role_dashboards_mapping.role_id')
		        ->join('groups','group_role_dashboards_mapping.group_id','=','groups.group_id')
		        ->where('group_role_dashboards_mapping.is_active', 1)
		        ->where('groups.is_active', 1)
		        ->where('group_role_dashboards_mapping.group_id', $group_id)
		        ->where('group_role_dashboards_mapping.role_id', $request->role_id)
		        ->get();
		        if (!empty($GroupDtl[0])) {
		        	$exp[] = "Group Role Mapping Already Exits...";
		    		$exception = array("Exception" => $exp);
		    		return response()->json(['Code' => 401,'Status' => 'Failed',"Message" => "forbidden",'Errors' => $exception]);
		        } else {
		        GroupMasterDashboards::where('group_id', $request->old_group_id)->where('role_id', $request->old_role_id)->delete();
		        $MainData = [];
		        /*============Store User Dashboard Mapping==============*/ 
		            foreach($request->dashboard_access as $key => $value)
		            {

		                    $subarr['group_id']         =$group_id;
		                    $subarr['role_id']          =$role_id;
		            		$subarr['subcategory_id']	= (isset($value['subcategory_id']))?$value['subcategory_id']:null;
		                    $subarr['client_primary_id']=(isset($value['client_primary_id']))?$value['client_primary_id']:null;
		                    $subarr['client_id']        =(isset($value['client_id']))?$value['client_id']:null;
		                    $subarr['dashboard_id']     =(isset($value['folder_id']))?$value['folder_id']:null;
		                    $subarr['sub_dashboard_id'] =(isset($value['dashboard_id']))?$value['dashboard_id']:null;
		                    $subarr['created_by']       =auth()->user()->id;
		                    array_push($MainData, $subarr);      
		            }

		        	if(!empty($MainData)){GroupMasterDashboards::insert($MainData);}
		        	$GroupMaster_Details = $this->show($group_id,$role_id);
		        	return response()->json(['Code' => 200,'Status' => "Success", 'Message' => 'Group Master updated successfully','Response' =>$GroupMaster_Details]);
		        }
	    	}
	    	else
	    	{
	    		GroupMasterDashboards::where('group_id', $request->old_group_id)->where('role_id', $request->old_role_id)->delete();
		        $MainData = [];
		        /*============Store User Dashboard Mapping==============*/ 
		            foreach($request->dashboard_access as $key => $value)
		            {

		                    $subarr['group_id']         =$group_id;
		                    $subarr['role_id']          =$role_id;
		            		$subarr['subcategory_id']	= (isset($value['subcategory_id']))?$value['subcategory_id']:null;
		                    $subarr['client_primary_id']=(isset($value['client_primary_id']))?$value['client_primary_id']:null;
		                    $subarr['client_id']        =(isset($value['client_id']))?$value['client_id']:null;
		                    $subarr['dashboard_id']     =(isset($value['folder_id']))?$value['folder_id']:null;
		                    $subarr['sub_dashboard_id'] =(isset($value['dashboard_id']))?$value['dashboard_id']:null;
		                    $subarr['created_by']       =auth()->user()->id;
		                    array_push($MainData, $subarr);      
		            }

		        	if(!empty($MainData)){GroupMasterDashboards::insert($MainData);}
		        	$GroupMaster_Details = $this->show($group_id,$role_id);
		        	return response()->json(['Code' => 200,'Status' => "Success", 'Message' => 'Group Master updated successfully','Response' =>$GroupMaster_Details]);
	    	}
	      	
        } catch (\Exception $e) {
        return response()->json(['Status' => 401,'Error' =>'Failed', 'Message' => $e->getMessage()]);
    	}
    	
    }
    public function destroy($group_id,$role_id)
    {
    	try {
    	 $datas = DB::table('groups')
		->join('group_role_dashboards_mapping', 'groups.group_id', '=', 'group_role_dashboards_mapping.group_id')
		->select(DB::raw("count(group_role_dashboards_mapping.group_id) as count"))
		->groupBy('group_role_dashboards_mapping.role_id')
		->where('groups.group_id',$group_id)
		->where('groups.is_active',1)
		->where('group_role_dashboards_mapping.is_active',1)
		->get();
		$GroupRoleCount = sizeof($datas);
    	GroupMasterDashboards::where('group_id', $group_id)->where('role_id', $role_id)->update(['is_active' => '0']);
    	if($GroupRoleCount == 1)
    	{
    		Groups::where('group_id', $group_id)->update(['is_active' => '0']);    		
    	}
    	return response()->json(['Code' => 200,'Status' => "Success", 'Message' => 'Group Master Deleted successfully']);
    	} catch (\Exception $e) {
            return response()->json(['Status' => 401,'Error' =>'Failed', 'Message' => $e->getMessage()]);
        }

    }
    public function getGroupRoleMapping($id)
    {
    	try {
	        $rolesData= \DB::table('group_role_dashboards_mapping as a')
	        ->select('a.group_id','a.role_id','b.role')
	        ->join('roles as b','a.role_id','=','b.role_id')
	        ->join('groups as c','a.group_id','=','c.group_id')
	        ->where('a.is_active',1)
	        ->where('c.is_active',1)
	        ->where('a.group_id',$id)
	        ->where('c.entity_id',env('env_entity_id'))
	        ->groupBy('a.group_id','a.role_id','b.role')
	        ->get();   
	        return response()->json(['Code' => 200,'Status' => "Success", 'Message' => 'Group Role Mapping List.','Response' =>$rolesData]);
        } catch (\Exception $e) {
            return response()->json(['Status' => 401,'Error' =>'Failed', 'Message' => $e->getMessage()]);
        }  
    }
    public function grouplist()
    {
    	try {
    	$GroupDtl = DB::table('groups')
        ->select('group_id','group_name')
        ->where('is_active', 1)
        ->where('entity_id', env('env_entity_id'))
        ->get();
        return response()->json(['Code' => 200,'Status' => "Success", 'Message' => 'Group List.','Response' =>$GroupDtl]);
        
        } catch (\Exception $e) {
            return response()->json(['Status' => 401,'Error' =>'Failed', 'Message' => $e->getMessage()]);
        }  
    }
}