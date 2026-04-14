<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Roles;
use App\Models\users_folder_access;
use App\Models\Grp_role_usr_mapping;
use App\Models\Users_dasboards_mapping;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    protected function buildFailedValidationResponse(Request $request, array $errors) {
        return response()->json(["Code"=> 406 , 'Status' => 'Failed' ,"Message" => "forbidden" , "Errors" =>$errors]);
    }

    public function index(Request $request)
    {
        
       try {
        if($request->flag == 0)
        {
            $userData =\DB::table('users')
            ->select('users.id','users.entity_id','users.name','users.last_name','users.email','users.external_user_id','users.permissions','users.profile_pic','users.role','users.google2fa_enable','roles.role as role_name','users.user_group_id','groups.group_name','permissions')
            ->leftjoin('roles','users.role','=','roles.role_id')
            ->leftjoin('groups','users.user_group_id','=','groups.group_id')
            ->where('users.is_active',1)
            ->where('users.entity_id',env('env_entity_id'))
            ->where('users.is_signup',0)
            ->get();
        }
        else
        {
            $userData =\DB::table('users')
            ->select('users.id','users.entity_id','users.name','users.last_name','users.email','users.external_user_id','users.permissions','users.profile_pic','users.role','users.google2fa_enable','roles.role as role_name','users.user_group_id','groups.group_name','permissions')
            ->leftjoin('roles','users.role','=','roles.role_id')
            ->leftjoin('groups','users.user_group_id','=','groups.group_id')
            ->where('users.is_active',1)
            ->where('users.entity_id',env('env_entity_id'))
            ->where('users.is_signup',1)
            ->get();
        }
           
            return response()->json(['Code' => 200,'Status' => "Success", 'Message' => 'Users List.','Response' =>$userData]);
        } catch (\Exception $e) {
            return response()->json(['Status' => 401,'Error' =>'Failed', 'Message' => $e->getMessage()]);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = $this->validate($request, [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required',Rule::unique('users')->where(function ($query) use ($request) {
                    return $query->where('is_active', 1)->where('entity_id', env('env_entity_id'));
                }),
                'string','email','max:255'],
            'password' => ['required', 'string', 'min:8', 'max:16',
                            'regex:/[a-z]/',      // must contain at least one lowercase letter
                            'regex:/[A-Z]/',      // must contain at least one uppercase letter
                            'regex:/[0-9]/',      // must contain at least one digit
                            'regex:/[@$!%*#?&]/'], // must contain a special character],

            'group_id' => ['required','exists:groups,group_id'],
            'entity_id' => ['required']
        ],
        [
            'password.regex' => 'Password must contain at least one number and both uppercase and lowercase letters and one special character.'
        ]);


        $group_id = $request->group_id;
        $RoleDtl =$this->getRoleDtl($request->role);
        $last_user = User::limit(1)->orderBy('id','desc')->get();
        $extr_usr_id = $last_user[0]['external_user_id'] +1;
        try {
        $id = User::create([
            'name'              => $request->first_name,
            'last_name'         => $request->last_name,
            'email'             => $request->email,
            'role'              => $request->role,
            'user_group_id'     => $group_id,
            'entity_id'         => $request->entity_id,
            'external_user_id'  => $extr_usr_id,
            'permissions'       => $request->permissions,
            'user_attributes'   => $request->user_attributes,
            'password'          => Hash::make($request->password),
            'created_by'        => auth()->user()->id,            
        ])->id;
        $user_unique_id = $group_id.'-'.$RoleDtl[0]->role_unique_id.'-'.$id;

        $user                   = User::find($id);        
        $user->unique_id        = $user_unique_id;
        $user->save();

        /*============Store Group Role User Mapping==============*/ 
        $grp_usr_mapping_id = Grp_role_usr_mapping::create([
            'user_id'           => $id,
            'group_id'          => $group_id,
            'role_id'           => $request->role,
            'user_unique_id'    => $user_unique_id,
            'users'             => $request->User_Access['users'],
            'looker'            => $request->User_Access['looker'],
            'snowflake'         => $request->User_Access['snowflake'],
            'roles'             => $request->User_Access['roles'],
            'group_module'      => $request->User_Access['group_module'],
            'clients'           => $request->User_Access['clients'],
            'reports'           => $request->User_Access['reports'],
            'user_add'          => $request->User_Access['user_add'],
            'user_edit'         => $request->User_Access['user_edit'],
            'user_delete'       => $request->User_Access['user_delete'],
            'user_view'         => $request->User_Access['user_view'],
            'client_add'        => $request->User_Access['client_add'],
            'client_edit'       => $request->User_Access['client_edit'],
            'client_delete'     => $request->User_Access['client_delete'],
            'client_view'       => $request->User_Access['client_view'],
            'role_add'          => $request->User_Access['role_add'],
            'role_edit'         => $request->User_Access['role_edit'],
            'role_delete'       => $request->User_Access['role_delete'],
            'role_view'         => $request->User_Access['role_view'],
            'group_add'         => $request->User_Access['group_add'],
            'group_edit'        => $request->User_Access['group_edit'],
            'group_delete'      => $request->User_Access['group_delete'],
            'group_view'        => $request->User_Access['group_view'],
            'report_add'        => $request->User_Access['report_add'],
            'report_edit'       => $request->User_Access['report_edit'],
            'report_delete'     => $request->User_Access['report_delete'],
            'report_view'       => $request->User_Access['report_view'],
            'generate_report'       => $request->User_Access['generate_report'],
            'generate_report_add'   => $request->User_Access['generate_report_add'],
            'generate_report_edit'  => $request->User_Access['generate_report_edit'],
            'generate_report_delete'=> $request->User_Access['generate_report_delete'],
            'generate_report_view'  => $request->User_Access['generate_report_view'],
            'invite_user'           => $request->User_Access['invite_user'],
            'permission_btn'        => $request->User_Access['permission_btn'],
            'approval_pending_user' => $request->User_Access['approval_pending_user'],
            'referral'              => $request->User_Access['referral'],
            'created_by'            => auth()->user()->id,             
        ])->grp_usr_mapping_id;

        /*============Store User Dashboard Mapping==============*/ 
        if(!empty($request->Dashboard_Access))
        {
            foreach($request->Dashboard_Access as $key => $value)
            {
         
                $usr_dash_id = Users_dasboards_mapping::create([
                'grp_usr_mapping_id'           => $grp_usr_mapping_id,
                'subcategory_id'                => (isset($value['subcategory_id']) && $value['subcategory_id'] != "")?$value['subcategory_id']:null,
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
                'user_id'               => $id,
                'subcategory_id'        => (isset($value['subcategory_id']) && $value['subcategory_id'] != "")?$value['subcategory_id']:null,
                'folder_id'             => (isset($valuee['client_id']))?$valuee['client_id']:null,
                'folder_primary_id'     => (isset($valuee['client_primary_id']))?$valuee['client_primary_id']:null,
                'created_by'            => auth()->user()->id,             
                ])->usr_fdr_id;
            }
        }
            $users = $this->show($id);
            return response()->json(['Code' => 200,'Status' => "Success", 'Message' => 'User Added successfully!!','Response' =>$users]);
        }
        catch (\Exception $e) {
            return response()->json(['Status' => 401,'Error' =>'Failed', 'Message' => $e->getMessage()]);
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
        try {
        return $userData =\DB::table('users')
            ->select('users.id','users.entity_id','users.name','users.last_name','users.email','users.external_user_id','users.permissions','users.profile_pic','users.role','roles.role as role_name','users.user_group_id','groups.group_name','permissions')
            ->leftjoin('roles','users.role','=','roles.role_id')
            ->leftjoin('groups','users.user_group_id','=','groups.group_id')
            ->where('users.is_active',1)
            ->where('users.id',$id)
            ->where('users.is_signup',0)
            ->get();
            
        } catch (\Exception $e) {
            return response()->json(['Status' => 401,'Error' =>'Failed', 'Message' => $e->getMessage()]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        try {
         $userData =\DB::table('users')
            ->select('users.id','users.entity_id','users.name','users.last_name','users.email','users.external_user_id','users.permissions','users.profile_pic','users.role','roles.role as role_name','users.user_group_id','groups.group_name','permissions','is_signup')
            ->leftjoin('roles','users.role','=','roles.role_id')
            ->leftjoin('groups','users.user_group_id','=','groups.group_id')
            ->where('users.is_active',1)
            ->where('users.id',$id)
            // ->where('users.is_signup',0)
            ->get();
            $RoleAccessData = [];
            $userDashAccess = [];
            if(!empty($userData[0])){

                if(!empty($userData[0]->profile_pic) && $userData[0]->profile_pic != Null)
                {
                $s3 = \Storage::disk('s3');
                    $client = $s3->getDriver()->getAdapter()->getClient();
                    $expiry = "+20 minutes";

                    $command = $client->getCommand('GetObject', [
                      'Bucket' => 'kairos-next-gen-storage', // bucket name
                      'Key'    => $userData[0]->profile_pic
                    ]);

                    $request = $client->createPresignedRequest($command, $expiry);
                    $profile_pic =  (string) $request->getUri(); // it will return signed URL
                    $userData[0]->profile_pic = $profile_pic;
                }

                if($userData[0]->role !="" && $userData[0]->role != Null){
                $RoleAccessData = \DB::table('grp_role_usr_mapping')
                ->select('grp_role_usr_mapping.grp_usr_mapping_id','grp_role_usr_mapping.users','grp_role_usr_mapping.looker','grp_role_usr_mapping.snowflake','grp_role_usr_mapping.group_module','grp_role_usr_mapping.processing','grp_role_usr_mapping.roles','grp_role_usr_mapping.clients','grp_role_usr_mapping.dashboards','grp_role_usr_mapping.reports','grp_role_usr_mapping.user_add','grp_role_usr_mapping.user_edit','grp_role_usr_mapping.user_delete','grp_role_usr_mapping.user_view','grp_role_usr_mapping.client_add','grp_role_usr_mapping.client_edit','grp_role_usr_mapping.client_delete','grp_role_usr_mapping.client_view','grp_role_usr_mapping.user_id','users.name','users.last_name','groups.group_id','groups.group_name','roles.role_id','roles.role',
                    'grp_role_usr_mapping.role_add',
                    'grp_role_usr_mapping.role_edit',
                    'grp_role_usr_mapping.role_delete',
                    'grp_role_usr_mapping.role_view',
                    'grp_role_usr_mapping.group_add',
                    'grp_role_usr_mapping.group_edit',
                    'grp_role_usr_mapping.group_delete',
                    'grp_role_usr_mapping.group_view',
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
                ->where(['grp_role_usr_mapping.user_id' => $id])
                ->get();
                // echo "<pre>";
            
                if(!empty($RoleAccessData[0]))
                {
                    $userDashAccess = \DB::table('users_dasboards_mapping')
                    ->select('subcategory_id','client_primary_id','client_id','dashboard_id as folder_id','sub_dashboard_id as dashboard_id')
                     ->where('users_dasboards_mapping.grp_usr_mapping_id',$RoleAccessData[0]->grp_usr_mapping_id)
                    ->get(); 
                }
            }            
        }
        else
        {
            return response()->json(['Status' => 401,'Error' =>'Failed', 'Message' => 'Data not found']);
        }
        $response['user_details']=$userData;
        $response['role_access']=$RoleAccessData;
        $response['dashboard_access']=$userDashAccess;
            return response()->json(['Code' => 200,'Status' => "Success", 'Message' => 'Users Details.','Response' =>$response]);
        } catch (\Exception $e) {
            return response()->json(['Status' => 401,'Error' =>'Failed', 'Message' => $e->getMessage()]);
        }
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
        $validator = $this->validate($request, [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string',Rule::unique('users')->where(function ($query) use ($request,$id) {
                    return $query->where('is_active', 1)->where('id','!=', $id)->where('entity_id', env('env_entity_id'));
                }),
            'email','max:255'],
            'group_id' => ['required','exists:groups,group_id']
        ],
        [
            'password.regex' => 'Password must contain at least one number and both uppercase and lowercase letters and one special character.'
        ]);



        $group_id = $request->group_id;
        $RoleDtl =$this->getRoleDtl($request->role);
        //Create user unique id
        $user_unique_id = $group_id.'-'.$RoleDtl[0]->role_unique_id.'-'.$id;

        //update user details
        $user = User::find($id);        
        $user->unique_id = $user_unique_id;
        $user->name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->email = $request->email;
        $user->permissions = $request->permissions;
        $user->user_attributes = $request->user_attributes;
        $user->user_group_id = $group_id;
        if(isset($request->role))
        {
        $user->role = $request->role;            
        }
        if($request->password !=''){
            $user->password = Hash::make($request->password);
        }
        if(isset($request->is_active))
        {
        $user->is_active = $request->is_active;            
        }
        if(isset($request->is_active) && $request->is_active == 1)
        {
        $user->is_signup = 0;            
        }
        
        $user->updated_by = auth()->user()->id;
        //$user->folders =  $folders;
               
        $user->save();
        $grumDtl =$this->Get_GroupRoleUserMapId($id);
       
        $grp_usr_mapping_id = "";
        if(isset($grumDtl[0]) && !empty($grumDtl[0]))
        {
             
        $grp_usr_mapping_id = $grumDtl[0]->grp_usr_mapping_id;
        $roles = Grp_role_usr_mapping::find($grumDtl[0]->grp_usr_mapping_id);
        
        $roles->group_id          = $group_id;
        $roles->role_id           = $request->role;
        $roles->users             = $request->User_Access['users'];
        $roles->looker            = $request->User_Access['looker'];
        $roles->snowflake         = $request->User_Access['snowflake'];
        $roles->roles             = $request->User_Access['roles'];
        $roles->clients           = $request->User_Access['clients'];    
        $roles->reports           = $request->User_Access['reports'];      
        $roles->user_add          = $request->User_Access['user_add'];      
        $roles->user_edit         = $request->User_Access['user_edit'];      
        $roles->user_delete       = $request->User_Access['user_delete'];      
        $roles->user_view         = $request->User_Access['user_view'];      
        $roles->client_add        = $request->User_Access['client_add'];      
        $roles->client_edit       = $request->User_Access['client_edit'];      
        $roles->client_delete     = $request->User_Access['client_delete'];      
        $roles->client_view       = $request->User_Access['client_view'];      
        $roles->role_add          = $request->User_Access['role_add'];     
        $roles->role_edit         = $request->User_Access['role_edit'];     
        $roles->role_delete       = $request->User_Access['role_delete'];     
        $roles->role_view         = $request->User_Access['role_view'];     
        $roles->group_module      = $request->User_Access['group_module'];     
        $roles->group_add         = $request->User_Access['group_add'];     
        $roles->group_edit        = $request->User_Access['group_edit'];     
        $roles->group_delete      = $request->User_Access['group_delete'];     
        $roles->group_view        = $request->User_Access['group_view'];     
        $roles->report_add        = $request->User_Access['report_add'];     
        $roles->report_edit       = $request->User_Access['report_edit'];     
        $roles->report_delete     = $request->User_Access['report_delete'];     
        $roles->report_view       = $request->User_Access['report_view'];     
        $roles->generate_report   = $request->User_Access['generate_report']; 
        $roles->generate_report_add        = $request->User_Access['generate_report_add'];
        $roles->generate_report_edit       = $request->User_Access['generate_report_edit'];
        $roles->generate_report_delete     = $request->User_Access['generate_report_delete'];
        $roles->generate_report_view       = $request->User_Access['generate_report_view'];
        $roles->invite_user                = $request->User_Access['invite_user'];
        $roles->permission_btn             = $request->User_Access['permission_btn'];
        $roles->approval_pending_user      = $request->User_Access['approval_pending_user'];
        $roles->referral                   = $request->User_Access['referral'];
        $roles->updated_by                 = auth()->user()->id;     
        $roles->save();
        Users_dasboards_mapping::where('grp_usr_mapping_id', $grumDtl[0]->grp_usr_mapping_id)->delete();
        users_folder_access::where('user_id', $id)->delete();
        }
        else
        {
        /*============Store Group Role User Mapping==============*/ 
            $grp_usr_mapping_id = Grp_role_usr_mapping::create([
                'user_id'           => $id,
                'group_id'          => $group_id,
                'role_id'           => $request->role,
                'user_unique_id'    => $user_unique_id,
                'users'             => $request->User_Access['users'],
                'looker'            => $request->User_Access['looker'],
                'snowflake'         => $request->User_Access['snowflake'],
                'roles'             => $request->User_Access['roles'],
                'group_module'      => $request->User_Access['group_module'],
                'clients'           => $request->User_Access['clients'],
                'reports'           => $request->User_Access['reports'],
                'user_add'          => $request->User_Access['user_add'],
                'user_edit'         => $request->User_Access['user_edit'],
                'user_delete'       => $request->User_Access['user_delete'],
                'user_view'         => $request->User_Access['user_view'],
                'client_add'        => $request->User_Access['client_add'],
                'client_edit'       => $request->User_Access['client_edit'],
                'client_delete'     => $request->User_Access['client_delete'],
                'client_view'       => $request->User_Access['client_view'],
                'role_add'          => $request->User_Access['role_add'],
                'role_edit'         => $request->User_Access['role_edit'],
                'role_delete'       => $request->User_Access['role_delete'],
                'role_view'         => $request->User_Access['role_view'],
                'group_add'         => $request->User_Access['group_add'],
                'group_edit'        => $request->User_Access['group_edit'],
                'group_delete'      => $request->User_Access['group_delete'],
                'group_view'        => $request->User_Access['group_view'],
                'report_add'        => $request->User_Access['report_add'],
                'report_edit'       => $request->User_Access['report_edit'],
                'report_delete'     => $request->User_Access['report_delete'],
                'report_view'       => $request->User_Access['report_view'],
                'generate_report'   => $request->User_Access['generate_report'],
            'generate_report_add'   => $request->User_Access['generate_report_add'],
            'generate_report_edit'  => $request->User_Access['generate_report_edit'],
            'generate_report_delete'=> $request->User_Access['generate_report_delete'],
            'generate_report_view'  => $request->User_Access['generate_report_view'],
            'invite_user'           => $request->User_Access['invite_user'],
            'permission_btn'        => $request->User_Access['permission_btn'],
            'approval_pending_user' => $request->User_Access['approval_pending_user'],
            'referral'              => $request->User_Access['referral'],
            'created_by'            => auth()->user()->id,        
            ])->grp_usr_mapping_id;
        }
        /*============Store User Dashboard Mapping==============*/ 
        if(!empty($request->Dashboard_Access))
        {
            foreach($request->Dashboard_Access as $key => $value)
            {
         
                $usr_dash_id = Users_dasboards_mapping::create([
                'grp_usr_mapping_id'           => $grp_usr_mapping_id,
                'subcategory_id'                => (isset($value['subcategory_id']) && $value['subcategory_id'] != "")?$value['subcategory_id']:null,
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
                'user_id'               => $id,
                'subcategory_id'        => (isset($value['subcategory_id']) && $value['subcategory_id'] != "")?$value['subcategory_id']:null,
                'folder_id'             => (isset($valuee['client_id']))?$valuee['client_id']:null,
                'folder_primary_id'     => (isset($valuee['client_primary_id']))?$valuee['client_primary_id']:null,
                'created_by'            => auth()->user()->id,             
                ])->usr_fdr_id;
            }
        }
        // if($request->is_signup == 1 && $request->is_active == 1)
        // {
        //     $this->Welcome_mail($request->email,$request->first_name);
        // }

        $users = $this->show($id);
        return response()->json(['Code' => 200,'Status' => "Success", 'Message' => 'User Updated successfully!!','Response' =>$users]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            DB::table('users')
                ->where('id', $id)
                ->update(['is_active' => '0']);
            DB::table('users_folder_access')->where('user_id', $id)->delete();
            return response()->json(['Code' => 200,'Status' => "Success", 'Message' => 'User has been deleted successfully!!']);
        } catch (\Exception $e) {
            return response()->json(['Status' => 401,'Error' =>'Failed', 'Message' => $e->getMessage()]);
        }
    }

    public function getRoleDtl($id)
    {
        $RolesData = DB::table('roles')->select('role_unique_id')->where('role_id', $id)->get();
        return $RolesData;
    }
    public function Get_GroupRoleUserMapId($id)
    {
        $GRUMData = DB::table('grp_role_usr_mapping')
        ->select('grp_usr_mapping_id')
                    ->where('user_id', $id)
                    ->get();
        return $GRUMData;
    }
    public function user_theme($flag)
    {        
        try {
            $user = User::find(auth()->user()->id);
            $user->theme = $flag;
            $user->save();
            return response()->json(['Code' => 200,'Status' => "Success", 'Message' => 'Theme Changed successfully']);
        } catch (\Exception $e) {
            return response()->json(['Status' => 401,'Error' =>'Failed', 'Message' => $e->getMessage()]);
        }
    }
}
