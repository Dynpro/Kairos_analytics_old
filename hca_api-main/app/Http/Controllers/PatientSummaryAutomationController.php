<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use App\Models\PatientSummary;
use App\Models\PatientSummaryList;
use App\Libraries\Helpers;
use PDF;
use File;
class PatientSummaryAutomationController extends Controller
{
	public function index()
	{
		try {
			$PS_ReportData= \DB::table('patient_summary_report')
	        ->select('patient_summary_report.ps_report_id','patient_summary_report.name','patient_summary_report.folder_id','patient_summary_report.user_id','patient_summary_report.frequency','patient_summary_report.file_path','patient_summary_report.status','patient_summary_report.is_active','client_folder_mapping.folder_name',DB::raw("count('patient_summary_list.ps_list_id') as count"))
	        ->leftjoin('patient_summary_list','patient_summary_report.ps_report_id','=','patient_summary_list.ps_report_id')
	        ->join('client_folder_mapping','patient_summary_report.folder_id','=','client_folder_mapping.folder_id')
	        ->join('users_folder_access','client_folder_mapping.id','=','users_folder_access.folder_primary_id')
	        ->distinct()
	        ->where(['patient_summary_report.is_active' => '1','client_folder_mapping.is_active' => '1','patient_summary_report.user_id' => auth()->user()->id,'users_folder_access.user_id' => auth()->user()->id])
            ->whereIn('client_folder_mapping.folder_id', [1511,2173,2532,3089,2383,2502,2491])
	        ->groupBy('patient_summary_report.ps_report_id','patient_summary_report.name','patient_summary_report.folder_id','patient_summary_report.user_id','patient_summary_report.frequency','patient_summary_report.file_path','patient_summary_report.status','patient_summary_report.is_active','client_folder_mapping.folder_name')
	        ->get();

		return response()->json(['Code' => 200,'Status' => "Success", 'Message' => 'Patient Summary Automation Report List.','Response' =>$PS_ReportData]);
        } catch (\Exception $e) {
            return response()->json(['Status' => 401,'Error' =>'Failed', 'Message' => $e->getMessage()]);
        }
	}
	public function client_list()
	{
		try {
			$ClientFolders = \DB::table('client_folder_mapping')
                ->select('client_folder_mapping.id','client_folder_mapping.folder_id','client_folder_mapping.folder_name','client_folder_mapping.schema_name')
                ->join('users_folder_access','client_folder_mapping.id','=','users_folder_access.folder_primary_id')
                ->distinct()
                ->where(['users_folder_access.user_id' => auth()->user()->id,'client_folder_mapping.entity_id' => env('env_entity_id'),'client_folder_mapping.is_active' => 1])
                ->whereIn('client_folder_mapping.folder_id', [1511,2173,2532,3089,2383,2502,2491])
                ->orderBy('client_folder_mapping.folder_name')
                ->get();
        	return response()->json(['Code' => 200,'Status' => "Success", 'Message' => 'Client List.','Response' =>$ClientFolders]);
        } catch (\Exception $e) {
            return response()->json(['Status' => 401,'Error' =>'Failed', 'Message' => $e->getMessage()]);
        }

	}
	public function get_patient(Request $request)
    {
       try {
        $query = "CALL SCH_COMMON.DEMOGRAPHIC_VALUES('".$request->schema_name."',TO_ARRAY('name'),'5000','1')";
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
           "schema":"'.$request->schema_name.'"
        }',
          CURLOPT_HTTPHEADER => array(
            'x-api-key: nBCbDwJZYe8pLENWbFEjvaWH6tzdOklh5vLXWCVJ',
            'Content-Type: application/json'
          ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        $resultset = json_decode($response);
        return response()->json(['Code' => 200,'Status' => "Success", 'Message' => 'Client List.','Response' =>$resultset]);
        } catch (\Exception $e) {
            return response()->json(['Status' => 401,'Error' =>'Failed', 'Message' => $e->getMessage()]);
        }
        // return response()->json(['success'=>$request->schema_name,'patientlist'=>$resultset]);
    }
    public function store(Request $request)
    {
    	$results =  \DB::table('mapping')
        ->select('*')
        ->where(['category' => "PatientSummary",'ids' =>$request->client_id])
        ->get();
        $name ="";
        if($request->frequency == 1){
            $name = $request->client_name."_PatientReport_".date('mdyHi');
        }
        elseif($request->frequency == 2){
            $name =$request->client_name."__PatientReport_Weekly_".date('mdyHi');
        }
        elseif($request->frequency == 3){
            $name =$request->client_name."__PatientReport_Monthly_".date('mdyHi');
        }
        elseif($request->frequency == 4){
            $name =$request->client_name."__PatientReport_Quaterly_".date('mdyHi');
        }

        $ps_report_id = PatientSummary::create([
            'name'                    => $name,
            'folder_id'               => $request->client_id,
            'user_id'                 => auth()->user()->id,
            'dash_id'                 => $results[0]->patient_summary_dash_id,
            'frequency'               => $request->frequency,
            'created_by'              => auth()->user()->id,  
            ])->ps_report_id;
        $patient_list=[];
        foreach ($request->patientlist as $key => $value) {        	
            $arr['ps_report_id'] = $ps_report_id;
            $arr['patient_name'] = $value;
            array_push($patient_list,$arr);
        }
        PatientSummaryList::insert($patient_list);
        $response = $this->show($ps_report_id);
	        return response()->json(['Code' => 200,'Status' => "Success", 'Message' => 'Schedule Report request has been successfully created!!','Response' =>$response]); 
    }
    public function show($id)
    {
    	try {
	    	return $reportData = \DB::table('patient_summary_report')
	        ->select('*')
	        ->where('is_active',1)
	        ->where('ps_report_id',$id)
	        ->get();
        } catch (\Exception $e) {
            return response()->json(['Status' => 401,'Error' =>'Failed', 'Message' => $e->getMessage()]);
        }
    }
    public function list($id)
    {
      	try {  
	        $results =  \DB::table('patient_summary_list')
	        ->select('*')
	        ->where(['ps_report_id' => $id])
	        ->get();
	        return response()->json(['Code' => 200,'Status' => "Success", 'Message' => 'Patient List','Response' =>$results]); 
       	} catch (\Exception $e) {
            return response()->json(['Status' => 401,'Error' =>'Failed', 'Message' => $e->getMessage()]);
        }
    }
    public function download_zip(Request $request)
    {
        $filepath='PatientSummary_Reports/'.$request->file_name;
        $headers = [
            'Content-Type'        => 'Content-Type: application/zip',
            'Content-Disposition' => 'attachment; filename='.$request->file_name,
        ];
        // return Storage::disk('s3')->get($filepath);
        // return response()->download(Storage::disk('s3')->get($filepath), 200, $headers);
        return response(Storage::disk('s3')->get($filepath), 200, $headers);
    }
    public function destroy($id)
    {
        try {
         DB::table('patient_summary_report')
            ->where('ps_report_id', $id)
            ->update(['is_active' => '0']);
            return response()->json(['Code' => 200,'Status' => "Success", 'Message' => 'Report has been deleted successfully!!']);
        } catch (\Exception $e) {
            return response()->json(['Status' => 401,'Error' =>'Failed', 'Message' => $e->getMessage()]);
        }
    }
}