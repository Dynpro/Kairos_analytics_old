<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Looker_data;
use App\Models\Looker;
use App\Models\Groups;
use App\Models\User;
use App\Models\GroupMasterDashboards;
use App\Models\users_folder_access;
use App\Models\Grp_role_usr_mapping;
use App\Models\Users_dasboards_mapping;
use App\Models\Roles;
use App\Libraries\Helpers;

class UserAccessControlController extends Controller
{
	public function __construct()
    {
		$this->helper = new Helpers;		
    }
	protected function buildFailedValidationResponse(Request $request, array $errors) {
        return response()->json(["Code"=> 406 , 'Status' => 'Failed' ,"Message" => "forbidden" , "Errors" =>$errors]);
    }
    public function index(Request $request)
    {
    	try {
	    	 $AccssData = \DB::table('groups as a')
	        ->select('a.group_id','a.group_name','b.grp_usr_mapping_id','b.user_id','b.role_id','c.role','d.name','d.last_name')
	        ->join('grp_role_usr_mapping as b','a.group_id','=','b.group_id')
	        ->join('roles as c','b.role_id','=','c.role_id')
	        ->join('users as d','b.user_id','=','d.id')
	        ->where('d.entity_id',env('env_entity_id'))
	        ->where('d.is_active',1)
	        ->get(); 
        	return response()->json(['Code' => 200,'Status' => "Success", 'Message' => 'Users Access List.','Response' =>$AccssData]);
        } catch (\Exception $e) {
            return response()->json(['Status' => 401,'Error' =>'Failed', 'Message' => $e->getMessage()]);
        } 
    }
    public function store(Request $request)
    {
        $validator = $this->validate($request, [
            'group_id'      => ['required'],
            'role_id'       => ['required'],
            'role_access'   => ['required'],
            'Userslist'     => ['required']
        ]);
        try {
    	$group_id = $request->group_id;       
        $RoleDtl =$this->getRoleDtl($request->role_id);
            if(!empty($request->Userslist)){
                foreach($request->Userslist as $val){
                /*============Remove existing records==============*/ 
                $query = 'DELETE grp_role_usr_mapping,users_dasboards_mapping FROM grp_role_usr_mapping 
                INNER JOIN users_dasboards_mapping ON grp_role_usr_mapping.grp_usr_mapping_id = users_dasboards_mapping.grp_usr_mapping_id  
                WHERE grp_role_usr_mapping.user_id = ?';
                \DB::delete($query, array($val));
                users_folder_access::where('user_id', $val)->delete();


                /*============Generate Unique Id==============*/ 

                $user_unique_id = $group_id.'-'.$RoleDtl[0]->role_unique_id.'-'.$val;

                /*============Store Group Role User Mapping==============*/ 
                $grp_usr_mapping_id = Grp_role_usr_mapping::create([
                    'user_id'           => $val,
                    'group_id'          => $group_id,
                    'role_id'           => $request->role_id,
                    'user_unique_id'    => $user_unique_id,
                    'users'             => $request->role_access['users'],
                    'user_add'          => $request->role_access['user_add'],
                    'user_edit'         => $request->role_access['user_edit'],
                    'user_delete'       => $request->role_access['user_delete'],
                    'user_view'         => $request->role_access['user_view'],

                    'looker'            => $request->role_access['looker'],
                    'snowflake'         => $request->role_access['snowflake'],
                    'invite_user'       => $request->role_access['invite_user'],

                    'roles'             => $request->role_access['roles'],
                    'role_add'          => $request->role_access['role_add'],
                    'role_edit'         => $request->role_access['role_edit'],
                    'role_delete'       => $request->role_access['role_delete'],
                    'role_view'         => $request->role_access['role_view'],

                    'clients'           => $request->role_access['clients'],
                    'client_add'        => $request->role_access['client_add'],
                    'client_edit'       => $request->role_access['client_edit'],
                    'client_delete'     => $request->role_access['client_delete'],
                    'client_view'       => $request->role_access['client_view'],

                    'group_module'      => $request->role_access['group_module'],
                    'group_add'         => $request->role_access['group_add'],
                    'group_edit'        => $request->role_access['group_edit'],
                    'group_delete'      => $request->role_access['group_delete'],
                    'group_view'        => $request->role_access['group_view'],

                    'reports'           => $request->role_access['reports'],
                    'report_add'        => $request->role_access['report_add'],
                    'report_edit'       => $request->role_access['report_edit'],
                    'report_delete'     => $request->role_access['report_delete'],
                    'report_view'       => $request->role_access['report_view'],

                    'generate_report'       => $request->role_access['generate_report'],
                    'generate_report_add'   => $request->role_access['generate_report_add'],
                    'generate_report_edit'  => $request->role_access['generate_report_edit'],
                    'generate_report_delete'=> $request->role_access['generate_report_delete'],
                    'generate_report_view'  => $request->role_access['generate_report_view'],

                    'permission_btn'         => $request->role_access['permission_btn'],
                    'approval_pending_user'  => $request->role_access['approval_pending_user'],
                    'referral'               => $request->role_access['referral'],
                    
                    'created_by'        => auth()->user()->id,             
                ])->grp_usr_mapping_id;


                /*============Store User Dashboard Mapping==============*/ 
                foreach($request->dashboard_access as $key => $value)
                {                
                    $usr_dash_id = Users_dasboards_mapping::create([
                        'grp_usr_mapping_id'           => $grp_usr_mapping_id,
                        'subcategory_id'               => (isset($value['subcategory_id']) && $value['subcategory_id'] != "")?$value['subcategory_id']:null,
                        'client_primary_id'            => (isset($value['client_primary_id']))?$value['client_primary_id']:null,
                        'client_id'                    => (isset($value['client_id']))?$value['client_id']:null,
                        'dashboard_id'                 => (isset($value['folder_id']))?$value['folder_id']:null,
                        'sub_dashboard_id'             => (isset($value['dashboard_id']))?$value['dashboard_id']:null,
                        'created_by'                   => auth()->user()->id,             
                        ])->usr_dash_id;
                }
                foreach($request->Client_List as $keyy => $valuee)
                {
                    $usr_fdr_id = users_folder_access::create([
                    'user_id'               => $val,
                    'subcategory_id'        => (isset($value['subcategory_id']) && $value['subcategory_id'] != "")?$value['subcategory_id']:null,
                    'folder_primary_id'     => (isset($valuee['client_primary_id']))?$valuee['client_primary_id']:null,
                    'folder_id'             => (isset($valuee['client_id']))?$valuee['client_id']:null,
                    'created_by'            => auth()->user()->id,             
                    ])->usr_fdr_id;
                }            
                $user                   = User::find($val);        
                $user->unique_id        = $user_unique_id;
                $user->user_group_id    = $group_id;
                $user->role             = $request->role_id;
                $user->save();
            }

            }
        
        return response()->json(['Code' => 200,'Status' => "Success", 'Message' => 'User Access Added successfully!!']);
        }
        catch (\Exception $e) {
            return response()->json(['Status' => 401,'Error' =>'Failed', 'Message' => $e->getMessage()]);
        }
    }
    public function show($group_id,$role_id)
    {
    }
    public function edit($id)
    {
        try{
        $Role_Access = \DB::table('grp_role_usr_mapping')
        ->select('grp_role_usr_mapping.user_id','users.name','users.last_name','groups.group_id','groups.group_name','roles.role_id','roles.role','grp_role_usr_mapping.grp_usr_mapping_id','grp_role_usr_mapping.users','grp_role_usr_mapping.user_add','grp_role_usr_mapping.user_edit','grp_role_usr_mapping.user_delete','grp_role_usr_mapping.user_view','grp_role_usr_mapping.looker','grp_role_usr_mapping.snowflake','grp_role_usr_mapping.processing','grp_role_usr_mapping.dashboards','grp_role_usr_mapping.clients','grp_role_usr_mapping.client_add','grp_role_usr_mapping.client_edit','grp_role_usr_mapping.client_delete','grp_role_usr_mapping.client_view','grp_role_usr_mapping.roles','grp_role_usr_mapping.role_add',
        'grp_role_usr_mapping.role_edit',
        'grp_role_usr_mapping.role_delete',
        'grp_role_usr_mapping.role_view',
        'grp_role_usr_mapping.group_module',
        'grp_role_usr_mapping.group_add',
        'grp_role_usr_mapping.group_edit',
        'grp_role_usr_mapping.group_delete',
        'grp_role_usr_mapping.group_view',
        'grp_role_usr_mapping.reports',
        'grp_role_usr_mapping.report_add',
        'grp_role_usr_mapping.report_edit',
        'grp_role_usr_mapping.report_delete',
        'grp_role_usr_mapping.report_view',
        'grp_role_usr_mapping.generate_report',
        'grp_role_usr_mapping.generate_report_add',
        'grp_role_usr_mapping.generate_report_edit',
        'grp_role_usr_mapping.generate_report_delete',
        'grp_role_usr_mapping.generate_report_view',
        'grp_role_usr_mapping.invite_user',
        'grp_role_usr_mapping.permission_btn',
        'grp_role_usr_mapping.approval_pending_user',
        'grp_role_usr_mapping.referral'
    )
        ->leftjoin('users','grp_role_usr_mapping.user_id','=','users.id')
        ->leftjoin('groups','grp_role_usr_mapping.group_id','=','groups.group_id')
        ->leftjoin('roles','grp_role_usr_mapping.role_id','=','roles.role_id')
        ->where(['grp_role_usr_mapping.grp_usr_mapping_id' => $id])
        ->get();

         $userDashAccess = \DB::table('users_dasboards_mapping')
        ->select('subcategory_id','client_primary_id','client_id','dashboard_id as folder_id','sub_dashboard_id as dashboard_id')
        ->where('users_dasboards_mapping.grp_usr_mapping_id',$Role_Access[0]->grp_usr_mapping_id)
        ->get(); 

        

       $response['role_access']         = $Role_Access;
       $response['dashboard_access']    = $userDashAccess;
        return response()->json(['Code' => 200,'Status' => "Success", 'Message' => 'User Access Details.','Response' =>$response]);
        } catch (\Exception $e) {
            return response()->json(['Status' => 401,'Error' =>'Failed', 'Message' => $e->getMessage()]);
        }
    }
    public function update(Request $request, $id)
    {
        $validator = $this->validate($request, [
            'group_id'      => ['required'],
            'role_id'       => ['required'],
            'role_access'   => ['required'],
            'user_id'     => ['required']
        ]);
        try{
        $group_id = $request->group_id;
        $RoleDtl =$this->getRoleDtl($request->role_id);
        $user_unique_id = $group_id.'-'.$RoleDtl[0]->role_unique_id.'-'.$request->user_id;

        $roles = Grp_role_usr_mapping::find($id);
        
        $roles->group_id          = $group_id;
        $roles->role_id           = $request->role_id;

        $roles->users             = $request->role_access['users'];
        $roles->user_add          = $request->role_access['user_add'];      
        $roles->user_edit         = $request->role_access['user_edit'];      
        $roles->user_delete       = $request->role_access['user_delete'];      
        $roles->user_view         = $request->role_access['user_view'];     

        $roles->looker            = $request->role_access['looker'];
        $roles->snowflake         = $request->role_access['snowflake'];
        $roles->invite_user       = $request->role_access['invite_user'];  

        $roles->roles             = $request->role_access['roles'];
        $roles->role_add          = $request->role_access['role_add'];
        $roles->role_edit         = $request->role_access['role_edit'];
        $roles->role_delete       = $request->role_access['role_delete'];
        $roles->role_view         = $request->role_access['role_view'];

        $roles->clients           = $request->role_access['clients'];            
        $roles->client_add        = $request->role_access['client_add'];      
        $roles->client_edit       = $request->role_access['client_edit'];      
        $roles->client_delete     = $request->role_access['client_delete'];      
        $roles->client_view       = $request->role_access['client_view'];
        
        $roles->group_module      = $request->role_access['group_module'];
        $roles->group_add         = $request->role_access['group_add'];
        $roles->group_edit        = $request->role_access['group_edit'];
        $roles->group_delete      = $request->role_access['group_delete'];
        $roles->group_view        = $request->role_access['group_view'];

        $roles->reports           = $request->role_access['reports'];      
        $roles->report_add        = $request->role_access['report_add'];
        $roles->report_edit       = $request->role_access['report_edit'];
        $roles->report_delete     = $request->role_access['report_delete'];
        $roles->report_view       = $request->role_access['report_view'];   

        $roles->generate_report   = $request->role_access['generate_report']; 
        $roles->generate_report_add        = $request->role_access['generate_report_add'];
        $roles->generate_report_edit       = $request->role_access['generate_report_edit'];
        $roles->generate_report_delete     = $request->role_access['generate_report_delete'];
        $roles->generate_report_view       = $request->role_access['generate_report_view'];  
        
        $roles->permission_btn              = $request->role_access['permission_btn'];  
        $roles->approval_pending_user       = $request->role_access['approval_pending_user'];
        $roles->referral                    = $request->role_access['referral'];  

        $roles->updated_by       = auth()->user()->id;     
        $roles->save();

        Users_dasboards_mapping::where('grp_usr_mapping_id', $id)->delete();
        users_folder_access::where('user_id', $request->user_id)->delete();

        /*============Store User Dashboard Mapping==============*/ 
        foreach($request->dashboard_access as $key => $value)
        {                
            $usr_dash_id = Users_dasboards_mapping::create([
                'grp_usr_mapping_id'           => $id,
                'subcategory_id'               => (isset($value['subcategory_id']) && $value['subcategory_id'] != "")?$value['subcategory_id']:null,
                'client_primary_id'            => (isset($value['client_primary_id']))?$value['client_primary_id']:null,
                'client_id'                    => (isset($value['client_id']))?$value['client_id']:null,
                'dashboard_id'                 => (isset($value['folder_id']))?$value['folder_id']:null,
                'sub_dashboard_id'             => (isset($value['dashboard_id']))?$value['dashboard_id']:null,
                'created_by'                   => auth()->user()->id,             
                ])->usr_dash_id;
        }
        foreach($request->Client_List as $keyy => $valuee)
        {
            $usr_fdr_id = users_folder_access::create([
            'user_id'               => $request->user_id,
            'subcategory_id'        => (isset($value['subcategory_id']) && $value['subcategory_id'] != "")?$value['subcategory_id']:null,
            'folder_primary_id'     => (isset($valuee['client_primary_id']))?$valuee['client_primary_id']:null,
            'folder_id'             => (isset($valuee['client_id']))?$valuee['client_id']:null,
            'created_by'            => auth()->user()->id,             
            ])->usr_fdr_id;
        }     
        $user                   = User::find($request->user_id);        
        $user->unique_id        = $user_unique_id;
        $user->user_group_id    = $group_id;
        $user->role             = $request->role_id;
        $user->save(); 
        return response()->json(['Code' => 200,'Status' => "Success", 'Message' => 'User Access updated successfully.']);
        } catch (\Exception $e) {
            return response()->json(['Status' => 401,'Error' =>'Failed', 'Message' => $e->getMessage()]);
        }     


    }
    public function destroy($id)
    {
        try {
        $query = 'DELETE grp_role_usr_mapping,users_dasboards_mapping FROM grp_role_usr_mapping 
            INNER JOIN users_dasboards_mapping ON grp_role_usr_mapping.grp_usr_mapping_id = users_dasboards_mapping.grp_usr_mapping_id  
            WHERE grp_role_usr_mapping.user_id = ?';
            \DB::delete($query, array($id));
            users_folder_access::where('user_id', $id)->delete();
        return response()->json(['Code' => 200,'Status' => "Success", 'Message' => 'User Access has been deleted successfully!!']);
        } catch (\Exception $e) {
            return response()->json(['Status' => 401,'Error' =>'Failed', 'Message' => $e->getMessage()]);
        }
    }
    public function getGroupRole(Request $request)
    {
        $group_id = $request->group_id;
        $rolesData= \DB::table('group_role_dashboards_mapping as a')
        ->select('a.group_id','a.role_id','b.role')
        ->select('a.group_id','a.role_id','b.role')
        ->join('roles as b','a.role_id','=','b.role_id')
        ->join('groups as c','a.group_id','=','c.group_id')
        ->where('a.is_active',1)
        ->where('a.group_id',$group_id)
        ->where('c.entity_id',env('env_entity_id'))
        ->groupBy('a.group_id','a.role_id','b.role')
        ->get();     
        return response()->json(['Code' => 200,'Status' => "Success", 'Message' => 'Role List.','Response' =>$rolesData]);
    }
    public function getGroupRoleUser(Request $request)
    {
        $role_id = $request->role_id;
        $group_id = $request->group_id; 
        $userRoleData= \DB::table('users')
        ->select('users.id','users.name','users.last_name','roles.role as role_name','roles.role_id')
        ->leftjoin('roles','users.role','=','roles.role_id')
         ->where('users.entity_id',env('env_entity_id'))
         ->where('users.role',$role_id)
         ->where('users.user_group_id',$group_id)
         ->where('users.is_active',1)
        ->get();

        $dashobardMapping = \DB::table('group_role_dashboards_mapping')
        ->select('category_id','subcategory_id','client_primary_id','client_id','dashboard_id as folder_id','sub_dashboard_id as dashboard_id')
         ->where('group_role_dashboards_mapping.group_id',$group_id)
         ->where('group_role_dashboards_mapping.role_id',$role_id)
        ->get();
      	
      	$RolesData = DB::table('roles')
                    ->where('role_id', $role_id)
                    ->get();

        $response['user_role_mapping'] 	= $userRoleData;
        $response['dashboard_mapping'] 	= $dashobardMapping;
        $response['role_access'] 		= $RolesData;

        return response()->json(['Code' => 200,'Status' => "Success", 'Message' => 'Data Fetch successfully.','Response' =>$response]);
    }
    public function getRoleDtl($id)
    {
        $RolesData = DB::table('roles')
        ->select('role_unique_id')
                    ->where('role_id', $id)
                    ->get();
        return $RolesData;
    }
}