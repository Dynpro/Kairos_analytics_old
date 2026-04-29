<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Looker_data;
use App\Models\Looker;
use App\Models\Roles;
use App\Libraries\Helpers;
use Illuminate\Validation\Rule;

class RolesController extends Controller
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
	    	$roleData = \DB::table('roles')
	        ->select('*')
	        ->where('is_active',1)
	        ->get();
	       return response()->json(['Code' => 200,'Status' => "Success", 'Message' => 'Roles List.','Response' =>$roleData]);
        } catch (\Exception $e) {
            return response()->json(['Status' => 401,'Error' =>'Failed', 'Message' => $e->getMessage()]);
        }
    }
    public function store(Request $request)
    {

    	$validator = $this->validate($request, [
            'role' => ['required', 'string', 'max:255',Rule::unique('roles')->where(function ($query) use ($request) {
                    return $query->where('is_active', 1);
                })
            ]
        ]);
    	$unique_ids =  $this->helper->generateRandomString(4); 
    	try {
    	$id = Roles::create([
            'role_unique_id'    => $unique_ids,
            'role'              => $request->role,
            'users'             => $request->users,
            'looker'            => $request->looker,
            'matillion'         => $request->snowflake,
            'roles'             => $request->roles,
            'clients'           => $request->clients,
            'group_module'      => $request->group_module,
            'phm'               => $request->reports,
            'generate_report'   => $request->generate_report,
            'user_add'          => $request->user_add,
            'user_edit'         => $request->user_edit,
            'user_delete'       => $request->user_delete,
            'user_view'         => $request->user_view,            
            'role_add'          => $request->role_add,
            'role_edit'         => $request->role_edit,
            'role_delete'       => $request->role_delete,
            'role_view'         => $request->role_view,
            'client_add'        => $request->client_add,
            'client_edit'       => $request->client_edit,
            'client_delete'     => $request->client_delete,
            'client_view'       => $request->client_view,
            'group_add'         => $request->group_add,
            'group_edit'        => $request->group_edit,
            'group_delete'      => $request->group_delete,
            'group_view'        => $request->group_view,
            'report_add'        => $request->report_add,
            'report_edit'       => $request->report_edit,
            'report_delete'     => $request->report_delete,
            'report_view'       => $request->report_view,
            'generate_report_add'        => $request->generate_report_add,
            'generate_report_edit'       => $request->generate_report_edit,
            'generate_report_delete'     => $request->generate_report_delete,
            'generate_report_view'       => $request->generate_report_view,
            'referral'                   => $request->referral,
            'permission_btn'              => $request->permission_btn,
            'approval_pending_user'       => $request->approval_pending_user,

            'invite_user'       => $request->invite_user,
            'created_by'        => auth()->user()->id,             
        ])->role_id;
        $Role_Details = $this->show($id);
        return response()->json(['Code' => 200,'Status' => "Success", 'Message' => 'Role Added successfully!!','Response' =>$Role_Details]);
        }
        catch (\Exception $e) {
            return response()->json(['Status' => 401,'Error' =>'Failed', 'Message' => $e->getMessage()]);
        }
    }
    public function show($id)
    {
    	try {
	    	return $roleData = \DB::table('roles')
	        ->select('*')
	        ->where('is_active',1)
	        ->where('role_id',$id)
	        ->get();
        } catch (\Exception $e) {
            return response()->json(['Status' => 401,'Error' =>'Failed', 'Message' => $e->getMessage()]);
        }
    }
    public function edit($id)
    {
    	try {
	    	 $roleData = \DB::table('roles')
	        ->select('roles.role_id','roles.role','roles.users','roles.user_add','roles.user_edit','roles.user_delete','roles.user_view','roles.looker','roles.matillion as snowflake','roles.dashboards','roles.clients','roles.client_add','roles.client_edit','roles.client_delete','roles.client_view','roles.roles','roles.role_add',
        'roles.role_edit',
        'roles.role_delete',
        'roles.role_view',
        'roles.group_module',
        'roles.group_add',
        'roles.group_edit',
        'roles.group_delete',
        'roles.group_view',
        'roles.phm as reports',
        'roles.report_add',
        'roles.report_edit',
        'roles.report_delete',
        'roles.report_view',
        'roles.generate_report',
        'roles.generate_report_add',
        'roles.generate_report_edit',
        'roles.generate_report_delete',
        'roles.generate_report_view',
        'roles.invite_user',
        'roles.permission_btn',
        'roles.referral',
        'roles.approval_pending_user'
        )
	        ->where('is_active',1)
	        ->where('role_id',$id)
	        ->get();
	        return response()->json(['Code' => 200,'Status' => "Success", 'Message' => 'Role Details.','Response' =>$roleData]);
        } catch (\Exception $e) {
            return response()->json(['Status' => 401,'Error' =>'Failed', 'Message' => $e->getMessage()]);
        }
    }
    public function update(Request $request, $id)
    {
    	$validator = $this->validate($request, [
            'role' => ['required', 'string', 'max:255',Rule::unique('roles')->where(function ($query) use ($request,$id) {
                    return $query->where('is_active', 1)->where('role_id','!=', $id);
                })]
        ]);

    	try {
    	$roles = Roles::find($id);
		
		$roles->role              = $request->role;
        $roles->users             = $request->users;
        $roles->looker            = $request->looker;
        $roles->matillion         = $request->snowflake;
        $roles->roles             = $request->roles;
        $roles->clients           = $request->clients;   
        $roles->group_module      = $request->group_module;  
        $roles->phm               = $request->reports;      
        $roles->generate_report   = $request->generate_report;      
        $roles->user_add          = $request->user_add;      
        $roles->user_edit         = $request->user_edit;      
        $roles->user_delete       = $request->user_delete;      
        $roles->user_view         = $request->user_view;      
        $roles->role_add          = $request->role_add;      
        $roles->role_edit         = $request->role_edit;      
        $roles->role_delete       = $request->role_delete;      
        $roles->role_view         = $request->role_view;      
        $roles->client_add        = $request->client_add;      
        $roles->client_edit       = $request->client_edit;      
        $roles->client_delete     = $request->client_delete;      
        $roles->client_view       = $request->client_view; 
        $roles->group_add         = $request->group_add;
        $roles->group_edit        = $request->group_edit;
        $roles->group_delete      = $request->group_delete;
        $roles->group_view        = $request->group_view;
        $roles->report_add        = $request->report_add;
        $roles->report_edit       = $request->report_edit;
        $roles->report_delete     = $request->report_delete;
        $roles->report_view       = $request->report_view;
        $roles->generate_report_add        = $request->generate_report_add;
        $roles->generate_report_edit       = $request->generate_report_edit;
        $roles->generate_report_delete     = $request->generate_report_delete;
        $roles->generate_report_view       = $request->generate_report_view;    
        $roles->permission_btn             = $request->permission_btn;    
        $roles->referral                   = $request->referral;    
        $roles->approval_pending_user      = $request->approval_pending_user;    
        $roles->invite_user       = $request->invite_user;    
        $roles->updated_by       = auth()->user()->id;     
        $roles->save();
        $Role_Details = $this->show($id);
        return response()->json(['Code' => 200,'Status' => "Success", 'Message' => 'Role Updated successfully!!','Response' =>$Role_Details]);
        } catch (\Exception $e) {
            return response()->json(['Status' => 401,'Error' =>'Failed', 'Message' => $e->getMessage()]);
        }
    }
    public function destroy($id)
    {
       try {
	    	DB::table('roles')
            ->where('role_id', $id)
            ->update(['is_active' => '0']);
	        return response()->json(['Code' => 200,'Status' => "Success", 'Message' => 'Role has been deleted successfully!!']);
        } catch (\Exception $e) {
            return response()->json(['Status' => 401,'Error' =>'Failed', 'Message' => $e->getMessage()]);
        }
    }	
}