<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\UserActivityLog;
use App\Libraries\Helpers;
use Illuminate\Validation\Rule;

class UserActivityLogController extends Controller
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
            $logs = \DB::table('user_activity_logs as a')
            ->select('a.user_id', 'b.name','b.email', 'b.last_name', 'a.login_datetime', 'a.ip', 'a.browser', 'a.country', 'a.state', 'a.city','c.group_name','d.role')
            ->join('users as b', 'a.user_id', '=', 'b.id')
            ->join('groups as c', 'b.user_group_id', '=', 'c.group_id')
            ->join('roles as d', 'b.role', '=', 'd.role_id')
            ->where('b.is_active', 1)
            ->orderBy('a.id', 'DESC')
            ->get();
        
	       return response()->json(['Code' => 200,'Status' => "Success", 'Message' => 'user Logs.','Response' =>$logs]);
        } catch (\Exception $e) {
            return response()->json(['Status' => 401,'Error' =>'Failed', 'Message' => $e->getMessage()]);
        }
    }
    public function store(Request $request)
    {
    	$this->validate($request, [
            'user_id' => ['required']
           ]
        );
    	try {
    	$id = UserActivityLog::create([
            'user_id'        => $request->user_id,
            'ip'             => $request->ip,
            'browser'        => $request->browser,
            'country'        => $request->country,
            'state'          => $request->state,
            'city'           => $request->city,      
        ])->id;
        return response()->json(['Code' => 200,'Status' => "Success", 'Message' => 'Log Added Successfully']);
        }
        catch (\Exception $e) {
            return response()->json(['Status' => 401,'Error' =>'Failed', 'Message' => $e->getMessage()]);
        }
    }
}