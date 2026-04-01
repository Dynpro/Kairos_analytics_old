<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\InviteUser;;
use App\Libraries\Helpers;
use Mail;
use PHPMailer\PHPMailer;
use App\Mail\sendEmail;

class InviteUserController extends Controller
{
	public function __construct()
    {
		$this->helper = new Helpers;		
    }
	protected function buildFailedValidationResponse(Request $request, array $errors) {
        return response()->json(["Code"=> 406 , 'Status' => 'Failed' ,"Message" => "forbidden" , "Errors" =>$errors]);
    }   
    public function store(Request $request)
    {     
        $validator = $this->validate($request, [
            'user_list' => ['required']
        ]);
        try {
            $userData = \DB::table('users')
            ->select('name','last_name')
            ->where('id',auth()->user()->id)
            ->get();      

            foreach($request->user_list as $key => $row){ 
                InviteUser::create([
                    'user_id' => auth()->user()->id, 
                    'user_email' => $row['email_id'],
                    'group_code'=> $row['group_code'],
                    'created_by' => auth()->user()->id,            
                ]); 
                $this->SendInvite($row['email_id'],$userData[0]->name,$userData[0]->last_name,$row['group_code']);                
            } 
            return response()->json(['Code' => 200,'Status' => "Success", 'Message' => 'User invition send successfully']);
        } catch (\Exception $e) {
            return response()->json(['Status' => 401,'Error' =>'Failed', 'Message' => $e->getMessage()]);
        }
  }

    public function SendInvite($useremail,$name,$last_name,$groupcode)
    {
        
        $text             = 'Hello,'."<br/>"."<br/>";
        $text             = $text.'You'."'".'re invited to Join Kairos Analytics Platform by '.$name.' '.$last_name.".<br/>";
        $text             = $text.$groupcode. " ".'is your group code please enter at the time of registration.'."<br/>";
        $text             = $text.'Please click on below link and use Signup to register your account.'."<br/>";
        $text             = $text.'https://hca.kairosrp.com/signup'."<br/><br/>";
        $text             = $text. 'Thanks & Regards,'."<br/>";
        $text             = $text.'Kairos Admin';

        $mail             = new PHPMailer\PHPMailer(); // create a n
        $mail->IsSMTP();
        
        //$mail->SMTPDebug  = 2; // debugging: 1 = errors and messages, 2 = messages only
        $mail->SMTPAuth   = true; // authentication enabled
        $mail->SMTPSecure = 'tls'; // secure transfer enabled REQUIRED for Gmail
        $mail->Host       = "email-smtp.us-east-1.amazonaws.com";
        $mail->Port       = 587; ; // or 587
        $mail->IsHTML(true);
        $mail->Username = env("AWS_SMTP_USERNAME");
        $mail->Password = env("AWS_SMTP_PASSWORD");
        $mail->SetFrom("hca@kairosrp.com", 'Kairos App');
        $mail->Subject = "Invite User";
        $mail->Body    = $text;
       $mail->AddBCC('himanshu.s@dynpro.com');
        $mail->AddBCC('mahesh.bhalchandra@dynpro.com');
        $mail->AddAddress($useremail);
        
        $mail->Send();
    }
}
   