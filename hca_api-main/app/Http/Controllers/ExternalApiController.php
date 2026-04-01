<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Libraries\Helpers;
use Illuminate\Validation\Rule;

class ExternalApiController extends Controller
{
	public function referral_uuid(Request $request)
	{
		try {
			$curl = curl_init();  
	        curl_setopt_array($curl, array(
	          CURLOPT_URL => 'https://lerhga30fb.execute-api.us-east-1.amazonaws.com/test_referral/test_referral',
	          CURLOPT_RETURNTRANSFER => true,
	          CURLOPT_ENCODING => '',
	          CURLOPT_MAXREDIRS => 10,
	          CURLOPT_TIMEOUT => 0,
	          CURLOPT_FOLLOWLOCATION => true,
	          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	          CURLOPT_CUSTOMREQUEST => 'POST',
	          CURLOPT_POSTFIELDS =>'{
	           "firstname":"'.$request->firstname.'",
	           "lastname":"'.$request->lastname.'",
	           "dob":"'.$request->dob.'",
	           "gender":"'.$request->gender.'",
	           "clientname":"'.$request->clientname.'",
	           "loadby":"'.$request->loadby.'",
	           "MEMBER_ID":"'.$request->member_id.'",
	           "MEMBER_SSN":"'.$request->member_ssn.'",
	           "CLASS_CODE":"'.$request->class_code.'",
	           "member_phone_number":"'.$request->member_phone_number.'",
	           "member_address":"'.$request->member_address.'",
	           "member_city":"'.$request->member_city.'",
	           "member_state":"'.$request->member_state.'",
	           "member_zip_code":"'.$request->member_zip_code.'",
	           "primary_insured_first_name":"'.$request->primary_insured_first_name.'",
	           "primary_insured_last_name":"'.$request->primary_insured_last_name.'",
	           "primary_insured_dob":"'.$request->primary_insured_dob.'",
	           "member_diagnoses":"'.$request->member_diagnoses.'",
	           "eligibility_effective_date":"'.$request->eligibility_effective_date.'",
	           "eligibility_termination_date":"'.$request->eligibility_termination_date.'",
	           "relationship_to_employee":"'.$request->relationship_to_employee.'",
	           "recommended_program":"'.$request->recommended_program.'",
			   "group_id":"'.$request->group_id.'",
			   "group_name":"'.$request->group_name.'",
			   "ICD10_CODE":"'.$request->icd10_code.'"
	        }',
	          CURLOPT_HTTPHEADER => array(
	            'x-api-key: f5Dkd4riGc7gS8rPEufnP707il0asZ7s9onbgUmF',
	            'Content-Type: application/json'
	          ),
	        ));
	        $response = curl_exec($curl);
	        curl_close($curl);
	        $resultset = json_decode($response);
	        return response()->json(['Code' => 200,'Status' => "Success", 'Message' => 'Referal Test Done.','Response' =>$resultset]);
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
	public function ValidateOTP(Request $request)
	{
		try {
			$curl = curl_init();  
	        curl_setopt_array($curl, array(
	          CURLOPT_URL => 'https://q4b3kchue2.execute-api.us-east-1.amazonaws.com/default/2fa',
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
	            'x-api-key: vXMSa5wKXT57V2DA6ZKgJ6I4TcV5MIRU1ezNMvOa',
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
	public function Disable2fa(Request $request)
	{
		try {
			$curl = curl_init();  
	        curl_setopt_array($curl, array(
	          CURLOPT_URL => 'https://276r0h25b1.execute-api.us-east-1.amazonaws.com/default/disable2fa',
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
	            'x-api-key: PruT5FBxqL9YNjjtHpjXE65uI4WN3D4z3VWoPvhc',
	            'Content-Type: application/json'
	          ),
	        ));
	        $response = curl_exec($curl);
	        curl_close($curl);
	        $resultset = json_decode($response);
	        return response()->json(['Code' => 200,'Status' => "Success", 'Message' => '2FA Disable Successfully.','Response' =>$resultset]);
        } catch (\Exception $e) {
            return response()->json(['Status' => 401,'Error' =>'Failed', 'Message' => $e->getMessage()]);
        }
	}
}