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
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{	
	protected function buildFailedValidationResponse(Request $request, array $errors) {
        return response()->json(["Code"=> 406 , 'Status' => 'Failed' ,"Message" => "forbidden" , "Errors" =>$errors]);
    }
	public function profile(){
		try {
        $user_details =\DB::table('users')
            ->select('users.id','users.entity_id','users.name','users.last_name','users.email','users.external_user_id','users.permissions','users.profile_pic','users.role','roles.role as role_name','users.user_group_id','groups.group_name','permissions')
            ->leftjoin('roles','users.role','=','roles.role_id')
            ->leftjoin('groups','users.user_group_id','=','groups.group_id')
            ->where('users.is_active',1)
            ->where('users.id',auth()->user()->id)
            ->where('users.is_signup',0)
            ->get();
            $user['id']            = $user_details[0]->id;
                $user['first_name']    = $user_details[0]->name;
                $user['last_name']     = $user_details[0]->last_name;
                $user['email']         = $user_details[0]->email;
                $user['entity']        = $user_details[0]->entity_id;
                $user['group_code']    = $user_details[0]->user_group_id;
                $user['group']          = $user_details[0]->group_name;
                $user['role_id']          = $user_details[0]->role;
                $user['role']          = $user_details[0]->role_name;
                $user['user_external_id']     = $user_details[0]->external_user_id;
                $user['permissions']   = $user_details[0]->permissions;
                
                $logopath ="";
                if(!empty($user_details[0]->profile_pic) && $user_details[0]->profile_pic != Null)
                {
                    $s3 = \Storage::disk('s3');
                    $client = $s3->getDriver()->getAdapter()->getClient();
                    $expiry = "+30 minutes";

                    $command = $client->getCommand('GetObject', [
                      'Bucket' => 'kairos-next-gen-storage', // bucket name
                      'Key'    => $user_details[0]->profile_pic
                    ]);

                    $request = $client->createPresignedRequest($command, $expiry);
                    $logopath =  (string) $request->getUri(); // it will return signed URL
                }
                $user['profile_pic']   = $logopath;
                return response()->json(['Code' => 200,'Status' => "Success", 'Message' => 'User Profile Details.','Response' =>$user]);
            
        } catch (\Exception $e) {
            return response()->json(['Status' => 401,'Error' =>'Failed', 'Message' => $e->getMessage()]);
        }
	}
	public function update(Request $request){

		$id = $request->user_id;
		$filePath="";
        $flag=0;
		if ($request->hasFile('file')) {
            $flag = 1;
			$validator = $this->validate($request, [
			'file' => 'mimes:jpeg,png,jpg,gif,svg|max:1024',
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string',Rule::unique('users')->where(function ($query) use ($request,$id) {
                    return $query->where('is_active', 1)->where('entity_id', env('env_entity_id'))->where('id','!=', $id);
                }),
            'email','max:255']
        	],
        	[	
            'password.regex' => 'Password must contain at least one number and both uppercase and lowercase letters and one special character.'
        	]);

        	$file = $request->file('file');
	        $name = time() .'_'. $file->getClientOriginalName();
	        $filePath = 'PROFILE-PHOTOS/' . $name;
	        Storage::disk('s3')->put($filePath, file_get_contents($file)); 
		}
		else{

			$validator = $this->validate($request, [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string',Rule::unique('users')->where(function ($query) use ($request,$id) {
                    return $query->where('is_active', 1)->where('entity_id', env('env_entity_id'))->where('id','!=', $id);
                }),
            'email','max:255']
        	],
        	[	
            'password.regex' => 'Password must contain at least one number and both uppercase and lowercase letters and one special character.'
        	]);
		}
		$validator = $this->validate($request, [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string',Rule::unique('users')->where(function ($query) use ($request,$id) {
                    return $query->where('is_active', 1)->where('id','!=', $id);
                }),
            'email','max:255']
        ],
        [
            'password.regex' => 'Password must contain at least one number and both uppercase and lowercase letters and one special character.'
        ]);



		try {
	        $user = User::find($id);        
	        $user->name = $request->first_name;
	        $user->last_name = $request->last_name;
	        $user->email = $request->email;
            if($flag == 1)
            {
	        $user->profile_pic = $filePath;                
            }
            if($request->is_deleted == 1)
            {
            $user->profile_pic = Null;                
            }

	        if($request->password !=''){
	            $user->password = Hash::make($request->password);
	        }
	        $user->updated_by = auth()->user()->id;
			$user->save();

			return response()->json(['Code' => 200,'Status' => "Success", 'Message' => 'Profile Updated successfully!!']);
            
        } catch (\Exception $e) {
            return response()->json(['Status' => 401,'Error' =>'Failed', 'Message' => $e->getMessage()]);
        }

		
	}
    public function remove_photo()
    {
        try {
            $user = User::find(auth()->user()->id);  
            $user->profile_pic = Null;
            $user->updated_by = auth()->user()->id;
            $user->save();
            return response()->json(['Code' => 200,'Status' => "Success", 'Message' => 'Profile photo removed successfully!!']);
            
        } catch (\Exception $e) {
            return response()->json(['Status' => 401,'Error' =>'Failed', 'Message' => $e->getMessage()]);
        }

    }
}