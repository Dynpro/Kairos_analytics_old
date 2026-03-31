<?php

namespace App\Libraries;
use Tymon\JWTAuth\Facades\JWTAuth; //use this library
use Illuminate\Support\Facades\Crypt;
use App\User;
use DB;
use PHPMailer\PHPMailer;
class Helpers
{
	public function AuthenticateUser($token){
        // env('JWT_PUBLIC_KEY');
        $now = time();
        // $secret = env('JWT_SECRET');
        $secret = env('MRS_JWT_SECRET');
        $tokenParts = explode(".", $token);  
        $tokenHeader = base64_decode($tokenParts[0]);
        $tokenPayload = base64_decode($tokenParts[1]);
        $jwtHeader =  json_decode($tokenHeader);
        $jwtPayload =  json_decode($tokenPayload);
        $algo = $this->getalgo($jwtHeader->alg);
        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($tokenHeader));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($tokenPayload));
        $signature = hash_hmac($algo, $base64UrlHeader . "." . $base64UrlPayload, $secret, true);
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        
        if($base64UrlSignature == $tokenParts[2])
        {                   

            if($jwtPayload->exp > $now){                   
                    return array(
                    'unique_id'  => $jwtPayload->unique_id,
                    'error' => '',
                    'status' => 200
                   );
            }
            else
            {
            		//'error' => 'Token expired'
                    return array(
                    'unique_id'  => "",
                    'error' => 'Token expired',
                    'status' => 440
                   );
            }
            
                
        }
        else
        {
        	// 'error' => 'Unauthorized',
            return array(
                    'unique_id'  => "",
                    'error' => 'Unauthorized',
                    'status' => 401
                   );
        }
     
    }
    public function AuthenticatedUserDtl($token){
        // env('JWT_PUBLIC_KEY');
        $now = time();
        // $secret = env('JWT_SECRET');
        $secret = env('MRS_JWT_SECRET');
        $tokenParts = explode(".", $token);  
        $tokenHeader = base64_decode($tokenParts[0]);
        $tokenPayload = base64_decode($tokenParts[1]);
        $jwtHeader =  json_decode($tokenHeader);
        $jwtPayload =  json_decode($tokenPayload);
        $algo = $this->getalgo($jwtHeader->alg);
        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($tokenHeader));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($tokenPayload));
        $signature = hash_hmac($algo, $base64UrlHeader . "." . $base64UrlPayload, $secret, true);
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        
        if($base64UrlSignature == $tokenParts[2])
        {                   

            if($jwtPayload->exp > $now){                   
                    return array(
                    'data'  => $jwtPayload,
                    'error' => '',
                    'status' => 200
                   );
            }
            else
            {
                    //'error' => 'Token expired'
                    return array(
                    'unique_id'  => "",
                    'error' => 'Token expired',
                    'status' => 440
                   );
            }
            
                
        }
        else
        {
            // 'error' => 'Unauthorized',
            return array(
                    'unique_id'  => "",
                    'error' => 'Unauthorized',
                    'status' => 401
                   );
        }
     
    }
    public function getalgo($alg)
    {
        switch ($alg) {
            case 'HS256':
                return "sha256";
                break;

            case 'HS512':
                return "sha512";
                break;
            
            default:
                # code...
                break;
        }
    }
    public function encrypt_id($id)
    {
        return $encrypted = Crypt::encryptString($id);
    }
    public static function encrypt_decrypt($string, $action = 'encrypt')
    {
        $encrypt_method = "AES-256-CBC";
        $secret_key = 'AA74CDCC2BB'; // user define private key
        $secret_iv = '5fgf5HJ5g27'; // user define secret key
        $key = hash('sha256', $secret_key);
        $iv = substr(hash('sha256', $secret_iv), 0, 16); // sha256 is hash_hmac_algo
        if ($action == 'encrypt') {
            $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
            $output = base64_encode($output);
        } else if ($action == 'decrypt') {
            $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
        }
        return $output;
    }
    public function success($data=NULL){
        return response()->json([
                                'status'    => 200,
                                'message'   => 'Success',
                                'error'     => false,
                                'data'      => $data
                                ]);
    }

    public function error($code, $message, $data=NULL, $reponseCode = 200){
         return response()->json([
                                'status'    => $code,
                                'message'   => $message,
                                'error'     => true,
                                'data'      => $data
                                ], $reponseCode);
    }
    public function get_userid($unique_id)
    {
        return DB::table('users')
            ->select('users.id')            
            ->where('unique_id', $unique_id)
            ->get()
            ->toArray();
        if (!empty($getData)) {
            return $getData;
        }
        else{
            DB::rollBack();
            return false;
        }
    }
    public function check_user_client_mapping($user_id,$folder_id)
    {
        return DB::table('users_folder_access')
            ->select('id')            
            ->where('user_id', $user_id)
            ->where('folder_id', $folder_id)
            ->get()
            ->toArray();
        if (!empty($getData)) {
            return $getData;
        }
        else{
            DB::rollBack();
            return false;
        }
    }
    public function check_client($unique_id)
    {
        return DB::table('client_folder_mapping')
            ->select('id')            
            ->where('organization_unique_id', $unique_id)
            ->get()
            ->toArray();
        if (!empty($getData)) {
            return $getData;
        }
        else{
            DB::rollBack();
            return false;
        }
    }
    public function check_orgn($orgn_id,$orgn_name)
    {
        return DB::table('client_folder_mapping')
            ->select('id')            
            ->where('organization_unique_id', $orgn_id)
            ->where('folder_name', $orgn_name)
            ->get()
            ->toArray();
        if (!empty($getData)) {
            return $getData;
        }
        else{
            DB::rollBack();
            return false;
        }
    }
    public function new_client_req_mail($client){

        $text             = 'Hi'."<br/>"."<br/>";
        $text             = $text.' '.$client." new client request generated by 909 healthcare.<br/><br/><br/>";
        $text             = $text.'Thank You,'."<br/>";
        $text             = $text.'Team Kairos';
        // echo $text;
        // exit();
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
        $mail->Subject = "New Client Request";
        $mail->Body    = $text;
        $mail->AddAddress('mahesh.bhalchandra@dynproindia.com', "system admin");
        $check=1;
        // $mail->Send();
        if ($mail->Send()) {
        
            return true;
        }
        else{
            return false;
        }

    }
    public function get_client_Details($orgn_unique_id)
    {
        return DB::table('client_folder_mapping')
            ->select('folder_id','schema_name','organization_unique_id')            
            ->where('organization_unique_id', $orgn_unique_id)
            ->get()
            ->toArray();
        if (!empty($getData)) {
            return $getData;
        }
        else{
            DB::rollBack();
            return false;
        }
    }
    public function SnowFlack_Call($query,$schema)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://8gw1bd7nnd.execute-api.us-east-1.amazonaws.com/sf-deploy',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS =>'{
           "query":"'.$query.'",
           "schema":"'.$schema.'"
        }',
          CURLOPT_HTTPHEADER => array(
            'x-api-key: ' . env('AWS_API_GATEWAY_KEY', ''),
            'Content-Type: application/json'
          ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        
        //echo $responseData['access_token'];
        return $response;
    }
    public function autologout()
    {
        if(!auth()->check()) { 
            return redirect()->route('login');
        }
    }
    function generateRandomString($length = 25) {
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
    }
}