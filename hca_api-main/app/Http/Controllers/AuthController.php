<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Signup_OTP;
use App\Models\password_resets;
use App\Models\GetInTouch;
use Mail;
use PHPMailer\PHPMailer;
use App\Mail\sendEmail;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    // public function __construct()
    // {
    //     $this->middleware('auth:api', ['except' => ['login']]);
    // }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function buildFailedValidationResponse(Request $request, array $errors) {
        return response()->json(["Code"=> 406 , 'Status' => 'Failed' ,"Message" => "forbidden" , "Errors" =>$errors]);
    }
    public function register(Request $request)
    {

        $validator = $this->validate($request, [
            'firstname' => ['required', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'email' => ['required',
                Rule::unique('users')->where(function ($query) use ($request) {
                    return $query->where('is_active', 1);
                }),
                'string','email','max:255'],
            'password' => ['required', 'string', 'min:8', 'max:12',
                            'regex:/[a-z]/',      // must contain at least one lowercase letter
                            'regex:/[A-Z]/',      // must contain at least one uppercase letter
                            'regex:/[0-9]/',      // must contain at least one digit
                            'regex:/[@$!%*#?&]/'], // must contain a special character],

            'confirm_password' =>['required','string','min:8','same:password','max:12',
                                'regex:/[a-z]/',      // must contain at least one lowercase letter
                                'regex:/[A-Z]/',      // must contain at least one uppercase letter
                                'regex:/[0-9]/',      // must contain at least one digit
                                'regex:/[@$!%*#?&]/'],

            'group_code' => ['required','exists:groups,group_id'],
            'entity_id' => ['required']
        ],
        [
            'password.regex' => 'Password must contain at least one number and both uppercase and lowercase letters and one special character.'
        ]);


        //`Create new user
        try {
            $otp = rand(1000,9999);
            $signup_OTP         = new Signup_otp();
            $signup_OTP->first_name   = $request->firstname;
            $signup_OTP->last_name    = $request->lastname;
            $signup_OTP->email        = $request->email;
            $signup_OTP->group_code   = $request->group_code;
            $signup_OTP->otp          = $otp;
            $signup_OTP->otp_count    = 1;
            

            if ($signup_OTP->save()) {
                $this->SendOtp($otp,$request->email,$request->firstname);
                return response()->json(['Code' => 200,'Status' => "Success", 'Message' => 'One time password send successfully!!','Response' =>$request->all()]);
            }
        } catch (\Exception $e) {
            return response()->json(['Status' => 401,'Error' =>'Failed', 'Message' => $e->getMessage()]);
        }
    }
    public function validateOtp(Request $request)
    {
        $validator = $this->validate($request, [
            'firstname' => ['required', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'email' => ['required',
            Rule::unique('users')->where(function ($query) use ($request) {
                    return $query->where('is_active', 1);
                }),
            'string','email','max:255'],
            'password' => ['required', 'string', 'min:8', 'max:12',
                            'regex:/[a-z]/',      // must contain at least one lowercase letter
                            'regex:/[A-Z]/',      // must contain at least one uppercase letter
                            'regex:/[0-9]/',      // must contain at least one digit
                            'regex:/[@$!%*#?&]/'], // must contain a special character],

            'confirm_password' =>['required','string','min:8','same:password','max:12',
                                'regex:/[a-z]/',      // must contain at least one lowercase letter
                                'regex:/[A-Z]/',      // must contain at least one uppercase letter
                                'regex:/[0-9]/',      // must contain at least one digit
                                'regex:/[@$!%*#?&]/'],

            'group_code' => ['required','exists:groups,group_id'],
            'entity_id' => ['required']
        ],
        [
            'password.regex' => 'Password must contain at least one number and both uppercase and lowercase letters and one special character.'
        ]);


        $Check = Signup_OTP::select('*')->where('email',$request->email)->where('otp',$request->otp)->get();
        if(!empty($Check[0])){
        //`Create new user
            try {

                $last_user = User::all()->last();
                $external_user_id = $last_user->external_user_id + 1;
                $user = new User();
                $user->name = $request->firstname;
                $user->last_name = $request->lastname;
                $user->email = $request->email;
                $user->password = app('hash')->make($request->password);
                $user->entity_id = $request->entity_id;
                $user->user_group_id = $request->group_code;
                $user->external_user_id = $external_user_id;
                $user->is_signup = 1;
                $user->created_by = 1;

                if ($user->save()) {
                    $user = new User();
                    DB::table('signup_otp')->where('email',$request->email)->update(['flag'=> 1]);  
                    $user_details = User::find($user->id);
                    $this->Register_request_mail($request->firstname,$request->lastname,$request->email,$request->group_code,$request->entity_id);
                    return response()->json(['Code' => 200,'Status' => "Success", 'Message' => 'Your Details has been successfully saved & Email Sent to admin for approval!!','Response' => $user_details]);
                }
            } catch (\Exception $e) {
                return response()->json(['Status' => 401,'Error' =>'Failed', 'Message' => $e->getMessage()]);
            }
        }
        else
        {
            return response()->json(['Status' => 401,'Error' =>'Failed', 'Message' => "Invalid OTP. Please enter the OTP sent to your email"]);
        }
    }
    public function update_registration(Request $request)
    {
        $validator = $this->validate($request, [
            'firstname' => ['required', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string','email','max:255'],
            'password' => ['required', 'string', 'min:8', 'max:12',
                            'regex:/[a-z]/',      // must contain at least one lowercase letter
                            'regex:/[A-Z]/',      // must contain at least one uppercase letter
                            'regex:/[0-9]/',      // must contain at least one digit
                            'regex:/[@$!%*#?&]/'], // must contain a special character],

            'confirm_password' =>['required','string','min:8','same:password','max:12',
                                'regex:/[a-z]/',      // must contain at least one lowercase letter
                                'regex:/[A-Z]/',      // must contain at least one uppercase letter
                                'regex:/[0-9]/',      // must contain at least one digit
                                'regex:/[@$!%*#?&]/'],

            'group_code' => ['required','exists:groups,group_id'],
            'entity_id' => ['required']
        ],
        [
            'password.regex' => 'Password must contain at least one number and both uppercase and lowercase letters and one special character.'
        ]);
        $Check = User::select('id')->where('email',$request->email)->get();
        if(!empty($Check[0])){
            try {
                $last_user = User::all()->last();
                $external_user_id = $last_user->external_user_id + 1;
                $user = User::find($Check[0]->id);   
                $user->name = $request->firstname;
                $user->last_name = $request->lastname;
                $user->password = app('hash')->make($request->password);
                $user->entity_id = $request->entity_id;
                $user->user_group_id = $request->group_code;
                $user->external_user_id = $external_user_id;
                $user->is_signup = 1;
                $user->created_by = 1;

                if ($user->save()) {
                    $user = new User();
                    DB::table('signup_otp')->where('email',$request->email)->update(['flag'=> 1]);  
                    $user_details = User::find($user->id);
                    $this->Register_request_mail($request->firstname,$request->lastname,$request->email,$request->group_code,$request->entity_id);
                    return response()->json(['Code' => 200,'Status' => "Success", 'Message' => 'Your Details has been successfully saved & Email Sent to admin for approval!!','Response' => $user_details]);
                }
            } catch (\Exception $e) {
                return response()->json(['Status' => 401,'Error' =>'Failed', 'Message' => $e->getMessage()]);
            }
        }
        else
        {
            return response()->json(['Status' => 401,'Error' =>'Failed', 'Message' => "2FA Not enabled"]);
        }
     
    }
    public function ResendOtp(Request $request)
    {
        try {
            $otp = rand(1000,9999);         
            $ReotpData = \DB::table('signup_otp')->select('*')->where('email',$request->email)->where('flag',0)->get();                
            if(!empty($ReotpData[0])){
                $otp_count = ($ReotpData[0]->otp_count);     
                    if($otp_count <= 4){                     
                        DB::table('signup_otp')->where('email', $request->email)->update(['otp' => $otp , 'otp_count'=> $otp_count + 1]);
                        $this->ReSendOtp_mail($otp, $request->email,$ReotpData[0]->first_name);
                        return response()->json(['Code' => 200,'Status' => "Success", 'Message' => 'One Time Password send successfully.']);    
                    }
                    else
                    {       
                         DB::table('signup_otp')->where('email',$request->email)->update(['flag'=> 1]);          
                        return response()->json(['Status' => 402,'Error' =>'Failed', 'Message' => 'Number of attempts exhausted']);
                    }  
            }
            else
            {
                return response()->json(['Status' => 401,'Error' =>'Failed', 'Message' => 'Invalid Email Address...!!!']); 
            }
        }catch (\Exception $e) {
                return response()->json(['Status' => 401,'Error' =>'Failed', 'Message' => $e->getMessage()]);
        }
    }
    Public function login_otp(Request $request){
        $validator = $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $credentials = ['email' => $request->email, 'password' => $request->password, 'is_active' => 1];
            if (! $token = auth()->attempt($credentials)) {
                return response()->json(['Code'=>401,'Status' => 'Failed','Error' => 'Invalid Credentials','Message'=>'Invalid Username or Password'], 401);
            }
            else
            {
                Signup_OTP::where('email', $request->email)->delete();
                try {
                    $otp = rand(1000,9999);
                    $signup_OTP         = new Signup_otp();
                    $signup_OTP->first_name   = "";
                    $signup_OTP->last_name    = "";
                    $signup_OTP->email        = $request->email;
                    $signup_OTP->group_code   = 0;
                    $signup_OTP->otp          = $otp;
                    $signup_OTP->otp_count    = 1;
                    

                    if ($signup_OTP->save()) {
                        $this->SignInOtp($otp,$request->email,$request->firstname);
                        return response()->json(['Code' => 200,'Status' => "Success", 'Message' => 'One time password send successfully!!','Response' =>$request->all()]);
                    }
                } catch (\Exception $e) {
                    return response()->json(['Status' => 401,'Error' =>'Failed', 'Message' => $e->getMessage()]);
                }
            }

    }
    Public function resend_login_otp(Request $request){
        try {
            $otp = rand(1000,9999);         
            $ReotpData = \DB::table('signup_otp')->select('*')->where('email',$request->email)->where('flag',0)->get();                
            if(!empty($ReotpData[0])){
                $otp_count = ($ReotpData[0]->otp_count);     
                    if($otp_count <= 4){                     
                        DB::table('signup_otp')->where('email', $request->email)->update(['otp' => $otp , 'otp_count'=> $otp_count + 1]);
                        $this->ReSendSignInOtp($otp, $request->email);
                        return response()->json(['Code' => 200,'Status' => "Success", 'Message' => 'One Time Password send successfully.']);    
                    }
                    else
                    {       
                         DB::table('signup_otp')->where('email',$request->email)->update(['flag'=> 1]);          
                        return response()->json(['Code' => 410,'Status' => "Failed",'Error' =>'Failed', 'Message' => 'Number of attempts exhausted'],410);
                    }  
            }
            else
            {
                return response()->json(['Code' => 401,'Status' => "Failed",'Error' =>'Failed', 'Message' => 'Invalid Email Address...!!!'],401); 
            }
        }catch (\Exception $e) {
                return response()->json(['Status' => "Failed",'Error' =>'Failed', 'Message' => $e->getMessage()]);
        }

    }
    public function login(Request $request)
    {
        Log::info('This is a test log entry.');
        $validator = $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required'
        ]);
        $credentials = ['email' => $request->email, 'password' => $request->password, 'is_active' => 1];
            if (! $token = auth()->attempt($credentials)) {
                return response()->json(['Code'=>401,'Status' => 'Failed','Error' => 'Invalid Credentials','Message'=>'Invalid Username or Password'], 401);
            }
            else
            {
                $user_details = \DB::table('users')
                ->select('users.*','groups.group_name','roles.role')
                ->join('groups','users.user_group_id','=','groups.group_id')
                ->join('roles','users.role','=','roles.role_id')
                ->where(['users.id' => auth()->user()->id])
                ->get();
                $user['id']            = $user_details[0]->id;
                $user['name']    = $user_details[0]->name;
                $user['last_name']     = $user_details[0]->last_name;
                $user['email']         = $user_details[0]->email;
                $user['entity']        = $user_details[0]->entity_id;
                $user['group_code']    = $user_details[0]->user_group_id;
                $user['group']          = $user_details[0]->group_name;
                $user['role']          = $user_details[0]->role;
                $user['user_external_id']     = $user_details[0]->external_user_id;
                $user['permissions']   = $user_details[0]->permissions;
                $user['theme']   = $user_details[0]->theme;
                
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
                $user_access_details = \DB::table('grp_role_usr_mapping')
                ->select('*')
                ->where(['user_id' => auth()->user()->id])
                ->get();
                $user_access = [];
                foreach($user_access_details as $value)
                {
                    foreach($value as $key => $val)
                    {
                        $user_access[$key] = $val;
                    }
                }
                $user = Auth::user();
                $user->last_login_at = date('Y-m-d H:i:s');
                $user->save();
                return $this->respondWithToken($token,$user,$user_access);
            }
    }
    public function loginWithOtp(Request $request)
    {
        $validator = $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required'
        ]);
        $Check = Signup_OTP::select('*')->where('email',$request->email)->where('otp',$request->otp)->get();
        if(!empty($Check[0])){
        $credentials = ['email' => $request->email, 'password' => $request->password, 'is_active' => 1];
            if (! $token = auth()->attempt($credentials)) {
                return response()->json(['Code'=>401,'Status' => 'Failed','Error' => 'Invalid Credentials','Message'=>'Invalid Username or Password'], 401);
            }
            else
            {
                $user_details = \DB::table('users')
                ->select('users.*','groups.group_name','roles.role')
                ->join('groups','users.user_group_id','=','groups.group_id')
                ->join('roles','users.role','=','roles.role_id')
                ->where(['users.id' => auth()->user()->id])
                ->get();
                $user['id']            = $user_details[0]->id;
                $user['name']    = $user_details[0]->name;
                $user['last_name']     = $user_details[0]->last_name;
                $user['email']         = $user_details[0]->email;
                $user['entity']        = $user_details[0]->entity_id;
                $user['group_code']    = $user_details[0]->user_group_id;
                $user['group']          = $user_details[0]->group_name;
                $user['role']          = $user_details[0]->role;
                $user['user_external_id']     = $user_details[0]->external_user_id;
                $user['permissions']   = $user_details[0]->permissions;
                $user['theme']   = $user_details[0]->theme;
                
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
                $user_access_details = \DB::table('grp_role_usr_mapping')
                ->select('*')
                ->where(['user_id' => auth()->user()->id])
                ->get();
                $user_access = [];
                foreach($user_access_details as $value)
                {
                    foreach($value as $key => $val)
                    {
                        $user_access[$key] = $val;
                    }
                }
                return $this->respondWithToken($token,$user,$user_access);
            }
        }
        else
        {
            return response()->json(['Code'=>401,'Status' => "Failed",'Error' =>'Failed', 'Message' => "Invalid OTP. Please enter the OTP sent to your email"],401);
        }       
    }
    public function ForgetPassword(Request $request)
    {
        try {
            $userArray= \DB::table('users')
            ->select('*')
            ->where(['email' => $request->email])
            ->where(['entity_id' => $request->entity_id])
            ->where(['is_active' => 1])
            ->where(['is_signup' => 0])
            ->get()->toArray();

            $six_digit_random_number = mt_rand(100000, 999999);
            if(count($userArray)==1){
                
                //create new record
                password_resets::updateOrCreate(
                    ['email' => $request->email,'user_id' => $userArray[0]->id],
                    [
                    'token' => $six_digit_random_number  
                ]);
                // die();
            

                $text             = 'Your One Time Password For Resetting Password is : '."\r\n".$six_digit_random_number;
                $mail             = new PHPMailer\PHPMailer(); // create a n
                $mail->IsSMTP();
                // $mail->SMTPDebug  = 1; // debugging: 1 = errors and messages, 2 = messages only
                $mail->SMTPAuth   = true; // authentication enabled
                $mail->SMTPSecure = 'tls'; // secure transfer enabled REQUIRED for Gmail
                $mail->Host       = "email-smtp.us-east-1.amazonaws.com";
                $mail->Port       = 587; // or 587
                $mail->IsHTML(true);
                $mail->Username = env("AWS_SMTP_USERNAME");
                $mail->Password = env("AWS_SMTP_PASSWORD");
                $mail->SetFrom("hca@kairosrp.com", 'MyCharlie Analytics');
                $mail->Subject = "OTP For Password Change";
                $mail->Body    = $text;
                $mail->AddAddress($request->email, "Kairos User");
                $check=1;
                if ($mail->Send()) {
                    $email = $request->email;
                    $user_id=$userArray[0]->id;
                    return response()->json(['Code' => 200,'Status' => "Success", 'Message' => 'Your One Time Password For Resetting Password is sent on your email.']);  
                } else {
                    return response()->json(['Status' => 401,'Error' =>'Failed', 'Message' => 'Failed to send email...!!!']); 
                }
            }
            else
            {
                return response()->json(['Status' => 401,'Error' =>'Failed', 'Message' => 'Email Not Avaialble...!!!']); 
            }
        }catch (\Exception $e) {
                return response()->json(['Status' => 401,'Error' =>'Failed', 'Message' => $e->getMessage()]);
        }
    }
    public function ForgetPasswordChange(Request $request)
    {
        try {
            $otp = $request->otp;
            $email = $request->email;

            $otp_fetch= \DB::table('password_resets')
            ->select('*')
           ->where(['email' => $email,'token' => $otp])
            ->get()->toArray();
            if(count($otp_fetch)==1)
            {
                $user = User::find($otp_fetch[0]->user_id);
                $user->password = Hash::make($request->password);
                $user->save();
                return response()->json(['Code' => 200,'Status' => "Success", 'Message' => 'Password Change successfully please login...!!!']);  
            }
            else
            {
                return response()->json(['Status' => 401,'Error' =>'Failed', 'Message' => "Invalid OTP"]);
            }
        }catch (\Exception $e) {
                return response()->json(['Status' => 401,'Error' =>'Failed', 'Message' => $e->getMessage()]);
        }
    }
    public function ChangePassword(Request $request)
    {
        $users = User::select('*')->where('email',$request->email)->where('entity_id',$request->entity_id)->get();
        if(!empty($users[0]))
        {
            if(Hash::check($request->old_password, $users[0]->password))
            {
                $hashed_pass=app('hash')->make($request->new_password);
                $user = User::find($users[0]->id);
                $user->password = $hashed_pass;
                $user->save();
                return response()->json(['Code' => 200,'Status' => "Success", 'Message' => 'Password change successfully...!!!']);
            }
            else
            {
                return response()->json(['Status' => 401,'Error' =>'Failed', 'Message' => "Old password does not match..."]);
            }
        }
        else
        {
            return response()->json(['Status' => 401,'Error' =>'Failed', 'Message' => "Invalid Email Address..."]);
        }
        
        // return $result = User::select('*')->where('email',$request->email)->where('password',$request->old_password)->get();
    }
    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(auth()->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
    \DB::table('account_master')->where('actual_user', auth()->user()->id)->update(['flag' => 0,'actual_user'=>Null]);
        auth()->logout();
        return response()->json(['Code' => 200,'Status' => 'Success' ,'Message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        
        $user_details = \DB::table('users')
                ->select('users.*','groups.group_name','roles.role')
                ->join('groups','users.user_group_id','=','groups.group_id')
                ->join('roles','users.role','=','roles.role_id')
                ->where(['users.id' => auth()->user()->id])
                ->get();
                $user['id']            = $user_details[0]->id;
                $user['name']    = $user_details[0]->name;
                $user['last_name']     = $user_details[0]->last_name;
                $user['email']         = $user_details[0]->email;
                $user['entity']        = $user_details[0]->entity_id;
                $user['group_code']    = $user_details[0]->user_group_id;
                $user['group']          = $user_details[0]->group_name;
                $user['role']          = $user_details[0]->role;
                $user['user_external_id']     = $user_details[0]->external_user_id;
                $user['permissions']   = $user_details[0]->permissions;
                $user['theme']   = $user_details[0]->theme;
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

                $user_access_details = \DB::table('grp_role_usr_mapping')
                ->select('*')
                ->where(['user_id' => auth()->user()->id])
                ->get();
                $user_access = [];
                foreach($user_access_details as $value)
                {
                    foreach($value as $key => $val)
                    {
                        $user_access[$key] = $val;
                    }
                }
        return $this->respondWithToken(auth()->refresh(),$user,$user_access);
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token,$user,$user_access)
    {
        $Response = [
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => auth()->factory()->getTTL() * 30,
            'user'         => $user,
            'user_access'  => (object)$user_access
        ];
        return response()->json([
            'Code'      => 200,
            'Status'    => "Success",
            'Message'   => "Login Successfully",
            'Response'  =>$Response
        ]);
    }
    public function SignInOtp($otp, $email){ 
            
            $text             = "Hello <br/><br/>";
            $text             = $text.'We thank you for signin on the MyCharlie Analytics.'."<br/><br/>";
            $text             = $text.'Kindly enter below provided OTP (valid up to 1 hour) on the sign in page.'."<br/>";
            $text             = $text.'OTP: '.$otp."<br/>";
            $text             = $text.'Email id: '.$email."<br/>";
            $text             = $text.'After entering OTP click on submit.'."<br/><br/>";
            $text             = $text.'Thank You,'."<br/><br/>";
            $text             = $text. 'With Regards,'."<br/>";
            $text             = $text.'MyCharlie Analytics Support';

            $mail             = new PHPMailer\PHPMailer(); // create a n
            $mail->IsSMTP();
            
           // $mail->SMTPDebug  = 2; // debugging: 1 = errors and messages, 2 = messages only
            $mail->SMTPAuth   = true; // authentication enabled
            $mail->SMTPSecure = 'tls'; // secure transfer enabled REQUIRED for Gmail
            $mail->Host       = "email-smtp.us-east-1.amazonaws.com";
            $mail->Port       = 587; ; // or 587
            $mail->IsHTML(true);
            $mail->Username = env("AWS_SMTP_USERNAME");
            $mail->Password = env("AWS_SMTP_PASSWORD");
            $mail->SetFrom("hca@kairosrp.com", 'MyCharlie Analytics');
            $mail->Subject = "Signin OTP";
            $mail->Body    = $text;
            $mail->AddAddress($email);
            
            if($mail->Send()){    
            
              return response(["status" => 200, "message" => "OTP sent successfully"]);
            }
            else{
                return response(["status" => 401, 'message' => 'Invalid']);
            }
      
  }
  public function ReSendSignInOtp($otp, $email){ 
            
            $text             = "Hello <br/><br/>";
            $text             = $text.'We thank you for signin on the MyCharlie Analytics.'."<br/><br/>";
            $text             = $text.'Kindly enter below provided OTP (valid up to 1 hour) on the sign in page.'."<br/>";
            $text             = $text.'OTP: '.$otp."<br/>";
            $text             = $text.'Email id: '.$email."<br/>";
            $text             = $text.'After entering OTP click on submit.'."<br/><br/>";
            $text             = $text.'Thank You,'."<br/><br/>";
            $text             = $text. 'With Regards,'."<br/>";
            $text             = $text.'MyCharlie Analytics Support';

            $mail             = new PHPMailer\PHPMailer(); // create a n
            $mail->IsSMTP();
            
           // $mail->SMTPDebug  = 2; // debugging: 1 = errors and messages, 2 = messages only
            $mail->SMTPAuth   = true; // authentication enabled
            $mail->SMTPSecure = 'tls'; // secure transfer enabled REQUIRED for Gmail
            $mail->Host       = "email-smtp.us-east-1.amazonaws.com";
            $mail->Port       = 587; ; // or 587
            $mail->IsHTML(true);
            $mail->Username = env("AWS_SMTP_USERNAME");
            $mail->Password = env("AWS_SMTP_PASSWORD");
            $mail->SetFrom("hca@kairosrp.com", 'MyCharlie Analyticsp');
            $mail->Subject = "Signin OTP";
            $mail->Body    = $text;
            $mail->AddAddress($email);
            
            if($mail->Send()){    
            
              return response(["status" => 200, "message" => "OTP sent successfully"]);
            }
            else{
                return response(["status" => 401, 'message' => 'Invalid']);
            }
      
  }
    public function SendOtp($otp, $email, $first_name){ 
            
            $text             = 'Hello '.$first_name."<br/><br/>";
            $text             = $text.'We thank you for signing up on the MyCharlie Analytics.'."<br/><br/>";
            $text             = $text.'Kindly enter below provided OTP (valid up to 1 hour) on the sign up page.'."<br/>";
            $text             = $text.'OTP: '.$otp."<br/>";
            $text             = $text.'Email id: '.$email."<br/>";
            $text             = $text.'After entering OTP click on signup , your membership request is sent for approval to our Administrator.'."<br/><br/>";
            $text             = $text.'Once we approve it you will recieve another confirmation mail with instructions to login.'."<br/><br/>";
            $text             = $text.'Thank You,'."<br/><br/>";
            $text             = $text. 'With Regards,'."<br/>";
            $text             = $text.'MyCharlie Analytics Support';

            $mail             = new PHPMailer\PHPMailer(); // create a n
            $mail->IsSMTP();
            
           // $mail->SMTPDebug  = 2; // debugging: 1 = errors and messages, 2 = messages only
            $mail->SMTPAuth   = true; // authentication enabled
            $mail->SMTPSecure = 'tls'; // secure transfer enabled REQUIRED for Gmail
            $mail->Host       = "email-smtp.us-east-1.amazonaws.com";
            $mail->Port       = 587; ; // or 587
            $mail->IsHTML(true);
            $mail->Username = env("AWS_SMTP_USERNAME");
            $mail->Password = env("AWS_SMTP_PASSWORD");
            $mail->SetFrom("hca@kairosrp.com", 'MyCharlie Analytics');
            $mail->Subject = "Registration OTP";
            $mail->Body    = $text;
            $mail->AddAddress($email);
            
            if($mail->Send()){    
            
              return response(["status" => 200, "message" => "OTP sent successfully"]);
            }
            else{
                return response(["status" => 401, 'message' => 'Invalid']);
            }
      
  }
  public function Register_request_mail($firstname,$lastname,$email,$user_group_id,$entity_id){
    if($entity_id == 1){$entity="<a href='https://hca.kairosrp.com'>HCA</a>";}
    elseif($entity_id == 2){$entity="<a href='https://mrs.kairosrp.com'>MRS</a>";}
        $text             = 'Hello Admin,'."<br/>"."<br/>";
        $text             = $text.'New user registerd on '.$entity.' Platform.'."<br/>";
        $text             = $text.'User Details:'."<br/>";
        $text             = $text.'Name: '.$firstname.' '.$lastname. "<br/>";
        $text             = $text.'Email id: '.$email."<br/>";
        $text             = $text.'Group Code: '.$user_group_id."<br/>"."<br/>";
        $text             = $text.'Thank You,'."<br/>";
        $text             = $text.'Team Kairos';
        $mail             = new PHPMailer\PHPMailer(); // create a n
        $mail->IsSMTP();
        
        // $mail->SMTPDebug  = 1; // debugging: 1 = errors and messages, 2 = messages only
        $mail->SMTPAuth   = true; // authentication enabled
        $mail->SMTPSecure = 'tls'; // secure transfer enabled REQUIRED for Gmail
        $mail->Host       = "email-smtp.us-east-1.amazonaws.com";
        $mail->Port       = 587; ; // or 587
        $mail->IsHTML(true);
        $mail->Username = env("AWS_SMTP_USERNAME");
        $mail->Password = env("AWS_SMTP_PASSWORD");
        $mail->SetFrom("hca@kairosrp.com", 'Kairos App');
        $mail->Subject = "New User Registration";
        $mail->Body    = $text;
        $mail->AddAddress('mahesh.bhalchandra@dynpro.com');
       // $mail->AddAddress('vaibhav.dwivedi@us.dynpro.com');
        //$mail->AddCC('himanshu.s@us.dynpro.com');
        //$mail->AddCC('mahesh.bhalchandra@us.dynpro.com');
        
        $check=1;
        // $mail->Send();
        if ($mail->Send()) {
            
            return true;
        }
        else{
            return false;
        }
    }
    public function ReSendOtp_mail($otp, $email, $first_name){ 
            
            $text             = 'Hello '.$first_name."<br/><br/>";
            $text             = $text.'We thank you for signing up on the MyCharlie Analytics.'."<br/><br/>";
            $text             = $text.'Kindly enter below provided OTP (valid up to 1 hour) on the sign up page.'."<br/>";
            $text             = $text.'OTP: '.$otp."<br/>";
            $text             = $text.'Email id: '.$email."<br/>";
            $text             = $text.'After entering OTP click on signup , your membership request is sent for approval to our Administrator.'."<br/><br/>";
            $text             = $text.'Once we approve it you will recieve another confirmation mail with instructions to login.'."<br/><br/>";
            $text             = $text.'Thank You,'."<br/><br/>";
            $text             = $text. 'With Regards,'."<br/>";
            $text             = $text.'MyCharlie Analytics Support';

            $mail             = new PHPMailer\PHPMailer(); // create a n
            $mail->IsSMTP();
            
           // $mail->SMTPDebug  = 2; // debugging: 1 = errors and messages, 2 = messages only
            $mail->SMTPAuth   = true; // authentication enabled
            $mail->SMTPSecure = 'tls'; // secure transfer enabled REQUIRED for Gmail
            $mail->Host       = "email-smtp.us-east-1.amazonaws.com";
            $mail->Port       = 587; ; // or 587
            $mail->IsHTML(true);
            $mail->Username = env("AWS_SMTP_USERNAME");
            $mail->Password = env("AWS_SMTP_PASSWORD");
            $mail->SetFrom("hca@kairosrp.com", 'MyCharlie Analytics');
            $mail->Subject = "Registration OTP";
            $mail->Body    = $text;
            $mail->AddAddress($email);
            
            if($mail->Send()){    
            
              return response(["status" => 200, "message" => "OTP sent successfully"]);
            }
            else{
                return response(["status" => 401, 'message' => 'Invalid']);
            }
      
  }
    public function get_in_touch(Request $request){
        try {
            $validator = $this->validate($request, [
                'subject' => ['required', 'string', 'max:255'],
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required'],
                'contact' => ['required'],
                'message' => ['required']
            ]);
            $id = GetInTouch::create([
                'name'              => $request->name,
                'email'             => $request->email,
                'subject'           => $request->subject,
                'contact'           => $request->contact,
                'message'           => $request->message,
            ])->id;
            $this->get_in_touch_mail($request);
            return response()->json(['Code' => 200,'Status' => "Success", 'Message' => 'Details added successfully']);
            
        } catch (\Exception $e) {
            return response()->json(['Status' => 401,'Error' =>'Failed', 'Message' => $e->getMessage()]);
        }
    }
    public function get_in_touch_mail($request){ 
            
            $text             = 'Hello Admin,'."<br/>"."<br/>";
            $text             = $text.'New request coming from `GET IN TOUCH` form.'."<br/>";
            $text             = $text.'Please find below details:'."<br/>";
            $text             = $text.'Name: '.$request->name. "<br/>";
            $text             = $text.'Email id: '.$request->email."<br/>";
            $text             = $text.'Contact: '.$request->contact."<br/>";
            $text             = $text.'Subject: '.$request->subject."<br/>"."<br/>";
            $text             = $text.'Messsage: '.$request->message."<br/>"."<br/>";
            $text             = $text.'Thank You,'."<br/>";
            $text             = $text.'Team Kairos';

            $mail             = new PHPMailer\PHPMailer(); // create a n
            $mail->IsSMTP();
            
           // $mail->SMTPDebug  = 2; // debugging: 1 = errors and messages, 2 = messages only
            $mail->SMTPAuth   = true; // authentication enabled
            $mail->SMTPSecure = 'tls'; // secure transfer enabled REQUIRED for Gmail
            $mail->Host       = "email-smtp.us-east-1.amazonaws.com";
            $mail->Port       = 587; ; // or 587
            $mail->IsHTML(true);
            $mail->Username = env("AWS_SMTP_USERNAME");
            $mail->Password = env("AWS_SMTP_PASSWORD");
            $mail->SetFrom("hca@kairosrp.com", 'MyCharlie Analytics');
            $mail->Subject = "GET IN TOUCH Request";
            $mail->Body    = $text;
            $mail->AddAddress('info@allhealthchoice.com');
            $mail->AddCC('mahesh.bhalchandra@dynpro.com');
            
            if($mail->Send()){    
            
              return response(["status" => 200, "message" => "OTP sent successfully"]);
            }
            else{
                return response(["status" => 401, 'message' => 'Invalid']);
            }
      
  }
  public function twofa_check(Request $request)
    {
        try {
            $user_dtl = DB::table('users')
            ->select('google2fa_enable')
                        ->where('email', $request->email)
                        ->where('is_active', 1)
                        ->get();
            return response()->json(['Code' => 200,'Status' => "Success", 'Message' => 'Users Details.','Response' =>$user_dtl[0]]);
        } catch (\Exception $e) {
            return response()->json(['Status' => 401,'Error' =>'Failed', 'Message' => $e->getMessage()]);
        }
    }
    public function ValidateOTP_NewReg(Request $request)
	{
		try {
			$curl = curl_init();  
	        curl_setopt_array($curl, array(
	          CURLOPT_URL => 'https://1aho0jxdq4.execute-api.us-east-1.amazonaws.com/default/New_2fa',
	          CURLOPT_RETURNTRANSFER => true,
	          CURLOPT_ENCODING => '',
	          CURLOPT_MAXREDIRS => 10,
	          CURLOPT_TIMEOUT => 0,
	          CURLOPT_FOLLOWLOCATION => true,
	          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	          CURLOPT_CUSTOMREQUEST => 'POST',
	          CURLOPT_POSTFIELDS =>'{
	           "email":"'.$request->email.'",
			   "otp":"'.$request->otp.'"
	        }',
	          CURLOPT_HTTPHEADER => array(
	            'Content-Type: application/json'
	          ),
	        ));
	        $response = curl_exec($curl);
	        curl_close($curl);
	        $resultset = json_decode($response);
	        return response()->json(['Code' => 200,'Status' => "Success", 'Message' => 'OTP Validate Successfully.','Response' =>$resultset]);
        } catch (\Exception $e) {
            return response()->json(['Status' => 401,'Error' =>'Failed', 'Message' => $e->getMessage()]);
        }
	}
    public function QrCode(Request $request)
	{
		try {
			$curl = curl_init();  
	        curl_setopt_array($curl, array(
	          CURLOPT_URL => 'https://910gjqvfi5.execute-api.us-east-1.amazonaws.com/qrcode/',
	          CURLOPT_RETURNTRANSFER => true,
	          CURLOPT_ENCODING => '',
	          CURLOPT_MAXREDIRS => 10,
	          CURLOPT_TIMEOUT => 0,
	          CURLOPT_FOLLOWLOCATION => true,
	          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	          CURLOPT_CUSTOMREQUEST => 'POST',
	          CURLOPT_POSTFIELDS =>'{
	           "email":"'.$request->email.'"
	        }',
	          CURLOPT_HTTPHEADER => array(
	            'x-api-key: env_AWS_API_KEY',
	            'Content-Type: application/json'
	          ),
	        ));
	        $response = curl_exec($curl);
	        curl_close($curl);
	        $resultset = json_decode($response);
	        return response()->json(['Code' => 200,'Status' => "Success", 'Message' => 'QRCode Generated.','Response' =>$resultset]);
        } catch (\Exception $e) {
            return response()->json(['Status' => 401,'Error' =>'Failed', 'Message' => $e->getMessage()]);
        }
	}
}