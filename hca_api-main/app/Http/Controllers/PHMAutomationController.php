<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Report;
use App\Models\Report_look;
use App\Libraries\Helpers;
use PDF;
use File;
use Response;
class PHMAutomationController extends Controller
{
	public function index()
	{
		try {
			$ReportData= \DB::table('report')
	        ->select('report.*','client_folder_mapping.folder_name')
	        ->join('phm','report.phm_folder_id','=','phm.client_id')
	        ->join('client_folder_mapping','phm.client_id','=','client_folder_mapping.folder_id')
	       	->where(['report.is_active' => '1','phm.is_active' => '1','client_folder_mapping.is_active' => '1','phm.entity_id' => env('env_entity_id'),'report.user_id' => auth()->user()->id])
	        ->get();

		return response()->json(['Code' => 200,'Status' => "Success", 'Message' => 'PHM Automation Report List.','Response' =>$ReportData]);
        } catch (\Exception $e) {
            return response()->json(['Status' => 401,'Error' =>'Failed', 'Message' => $e->getMessage()]);
        }
	}
	public function client_list()
	{
		try {
			$folderChildArr = DB::select("SELECT b.folder_name,a.folder_primary_id,b.folder_id,b.phm_folder_id,b.parent_folder_id,c.folder_id as phm_folder_id,b.schema_name FROM users_folder_access as a INNER JOIN client_folder_mapping as b ON a.folder_primary_id = b.id and b.is_parent_phm =1 INNER JOIN client_folder_mapping as c ON b.folder_id= c.parent_folder_id and c.type='PHM' and c.is_parent_phm =1 WHERE a.user_id ='".auth()->user()->id."' AND b.entity_id='".env('env_entity_id')."' ORDER BY b.folder_name");

			return response()->json(['Code' => 200,'Status' => "Success", 'Message' => 'Client List.','Response' =>$folderChildArr]);
        } catch (\Exception $e) {
            return response()->json(['Status' => 401,'Error' =>'Failed', 'Message' => $e->getMessage()]);
        }
	}
	public function year_list(Request $request)
	{
		try {
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
	           "query":"SELECT DISTINCT YEAR(DIAGNOSIS_DATE) as \\"name\\" FROM STG_TAB_MEDICAL_DATA WHERE DIAGNOSIS_DATE IS NOT NULL ORDER BY \\"name\\" DESC",
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
	        return response()->json(['Code' => 200,'Status' => "Success", 'Message' => 'Year List.','Response' =>$resultset]);
        } catch (\Exception $e) {
            return response()->json(['Status' => 401,'Error' =>'Failed', 'Message' => $e->getMessage()]);
        }
	}
	public function store(Request $request)
    {
    	try {
	    	$data = $request->all();
	        $cnt= count($data['years']);
	        $years = implode(",",$data['years']);
	        $message = "Schedule Report request has been successfully created!!";
	        $name ="";
	        if($data['frequency'] == 1){
	            $name = $data['client_name']."_".$cnt."_Years_PHM_Report_".date('mdy');
	            $message = "Report will be available after 2 Hours!!";
	        }
	        elseif($data['frequency'] == 2){
	            $name = $data['client_name']."_".$cnt."_Years_PHM_Report_".date('mdy')."_WeeklySchedule";
	        }
	        elseif($data['frequency'] == 3){
	            $name = $data['client_name']."_".$cnt."_Years_PHM_Report_".date('mdy')."_MonthlySchedule";
	        }
	        elseif($data['frequency'] == 4){
	            $name = $data['client_name']."_".$cnt."_Years_PHM_Report_".date('mdy')."_QuarterlySchedule";
	        }

	        $id = Report::create([
	        'name'            		=> $name,
	        'year'            		=> $years,
	        'user_id'         		=> auth()->user()->id,
	        'phm_folder_id'  		=> $data['client_id'],
	        'reporting_year'  		=> $data['reporting_year'],
	        'frequency'      		=> $data['frequency'],
	        'storeLook_folder_id'  	=> 2926,
	        'schedule_time'  		=> date('Y-m-d H:i', strtotime(date("Y-m-d H:i"). ' +5 minutes')),  
	        ])->report_id; 
	        $response = $this->show($id);
	        return response()->json(['Code' => 200,'Status' => "Success", 'Message' => $message,'Response' =>$response]);
        } catch (\Exception $e) {
            return response()->json(['Status' => 401,'Error' =>'Failed', 'Message' => $e->getMessage()]);
        }
    }
    public function show($id)
    {
    	try {
	    	return $reportData = \DB::table('report')
	        ->select('*')
	        ->where('is_active',1)
	        ->where('report_id',$id)
	        ->get();
        } catch (\Exception $e) {
            return response()->json(['Status' => 401,'Error' =>'Failed', 'Message' => $e->getMessage()]);
        }
    }
    public function destroy($id)
    {
    	try {
	    	DB::table('report')
	            ->where('report_id', $id)
	            ->update(['is_active' => '0']);
	        Report_look::where('report_id',$id)->delete();
	        return response()->json(['Code' => 200,'Status' => "Success", 'Message' => 'Report has been deleted successfully!!']);
        } catch (\Exception $e) {
            return response()->json(['Status' => 401,'Error' =>'Failed', 'Message' => $e->getMessage()]);
        }
    }
    public function download(Request $request)
    {
       $filepath='Generated_PHM/'.$request->file_name;
       $file =  Storage::disk('s3')->get($filepath);
       $assetPath=Storage::disk('s3')->url($filepath);
        $headers = [
            'Content-Type'        => 'Content-Type: application/zip',
            'Content-Disposition' => 'attachment; filename='.$request->file_name,
        ];
        // return response()->json(['Code' => 200,'Status' => "Success", 'Message' => 'Report has been deleted successfully!!','Response' =>Storage::disk('s3')->get($filepath)]);
        // return \Response::make(Storage::disk('s3')->get($filepath), 200, $headers);
         // return readfile($assetPath);
        // return response()->download($file, 'filename.pdf', $headers);
        //  return response()->download($assetPath);
         return response($file, 200, $headers);
    }
}
