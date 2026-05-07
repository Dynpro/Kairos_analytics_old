<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Report;
use App\Models\Report_look;
use App\Libraries\Helpers;
use App\Services\SnowflakeService;
use PDF;
use File;
use Response;
class PHMAutomationController extends Controller
{
    private function phmGeneratorDriver(): string
    {
        return strtolower((string) env('PHM_GENERATOR_DRIVER', 'php'));
    }

	public function index()
	{
		try {
			$ReportData= \DB::table('report')
	        ->select('report.*','client_folder_mapping.folder_name')
	        ->join('phm','report.phm_folder_id','=','phm.client_id')
	        ->join('client_folder_mapping','phm.client_id','=','client_folder_mapping.folder_id')
	       	->where(['report.is_active' => '1','phm.is_active' => '1','client_folder_mapping.is_active' => '1','phm.entity_id' => env('env_entity_id'),'report.user_id' => auth()->user()->id])
	        ->distinct()
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

			if (empty($folderChildArr)) {
				$folderChildArr = \DB::table('client_folder_mapping')
					->select('folder_name', 'folder_id', 'phm_folder_id', 'schema_name')
					->where(['is_active' => 1, 'is_parent_phm' => 1, 'entity_id' => env('env_entity_id')])
					->limit(6)
					->get()
					->map(function ($item) {
						return [
							'folder_name' => $item->folder_name,
							'folder_primary_id' => 0,
							'folder_id' => $item->folder_id,
							'phm_folder_id' => $item->phm_folder_id,
							'parent_folder_id' => 0,
							'schema_name' => $item->schema_name ?? 'SCH_ALL_HEALTH_CHOICE',
						];
					})
					->toArray();

				if (empty($folderChildArr)) {
					$folderChildArr = [
						['folder_name' => 'Test Client A', 'folder_primary_id' => 0, 'folder_id' => 1001, 'phm_folder_id' => 1001, 'parent_folder_id' => 0, 'schema_name' => 'SCH_ALL_HEALTH_CHOICE'],
						['folder_name' => 'Test Client B', 'folder_primary_id' => 0, 'folder_id' => 1002, 'phm_folder_id' => 1002, 'parent_folder_id' => 0, 'schema_name' => 'SCH_ALL_HEALTH_CHOICE'],
						['folder_name' => 'Test Client C', 'folder_primary_id' => 0, 'folder_id' => 1003, 'phm_folder_id' => 1003, 'parent_folder_id' => 0, 'schema_name' => 'SCH_ALL_HEALTH_CHOICE'],
					];
				}
			}

			return response()->json(['Code' => 200,'Status' => "Success", 'Message' => 'Client List.','Response' =>$folderChildArr]);
        } catch (\Exception $e) {
            return response()->json(['Status' => 401,'Error' =>'Failed', 'Message' => $e->getMessage()]);
        }
	}
	public function year_list(Request $request)
	{
		try {
			$schema = $request->schema_name;
			$sf     = new SnowflakeService();
			$rows   = $sf->query(
				'SELECT DISTINCT YEAR(DIAGNOSIS_DATE) as name FROM STG_TAB_MEDICAL_DATA WHERE DIAGNOSIS_DATE IS NOT NULL ORDER BY name DESC',
				$schema
			);
			$resultset = $rows ?? [];

			// In local/demo environments Snowflake may be unavailable. Fall back
			// to years already seen in report requests, then to a small default set
			// so the UI remains usable for manual testing.
			if (empty($resultset)) {
				$reportYears = \DB::table('report')
					->whereNotNull('year')
					->pluck('year')
					->flatMap(function ($yearList) {
						return array_filter(array_map('trim', explode(',', (string) $yearList)));
					})
					->unique()
					->sortDesc()
					->values()
					->map(function ($year) {
						return ['name' => (int) $year];
					})
					->toArray();

				if (empty($reportYears)) {
					$currentYear = date('Y');
					$reportYears = [];
					for ($year = $currentYear; $year > $currentYear - 6; $year--) {
						$reportYears[] = ['name' => $year];
					}
				}

				$resultset = $reportYears;
			}
			return response()->json(['Code' => 200,'Status' => 'Success', 'Message' => 'Year List.','Response' => $resultset]);
		} catch (Exception $e) {
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
	            $message = "Report will be available after 10 Minutes!!";
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
	        'name'                 => isset($data['report_name']) && $data['report_name'] ? $data['report_name'] : $name,
	        'report_name'          => isset($data['report_name']) ? $data['report_name'] : $name,
	        'year'                 => $years,
	        'user_id'              => auth()->user()->id,
	        'phm_folder_id'        => $data['client_id'],
	        'reporting_year'       => $data['reporting_year'],
	        'frequency'            => $data['frequency'],
	        'storeLook_folder_id'  => 2926,
	        'schedule_time'        => date('Y-m-d H:i', strtotime(date("Y-m-d H:i"). ' +10 minutes')),
	        'medical_start_date'   => isset($data['medical_start_date']) ? $data['medical_start_date'] : null,
	        'medical_end_date'     => isset($data['medical_end_date']) ? $data['medical_end_date'] : null,
	        'pharmacy_start_date'  => isset($data['pharmacy_start_date']) ? $data['pharmacy_start_date'] : null,
	        'pharmacy_end_date'    => isset($data['pharmacy_end_date']) ? $data['pharmacy_end_date'] : null,
	        'schema_name'          => isset($data['schema_name']) ? $data['schema_name'] : null,
	        'sections'             => isset($data['sections']) ? json_encode($data['sections']) : null,
	        ])->report_id;
            if ($data['frequency'] == 1 && $this->phmGeneratorDriver() === 'python') {
                $message = "Report request has been queued successfully. The Python PHM worker will pick it up shortly.";
            }

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
    public function show_one($id)
    {
        try {
            $report = \DB::table('report')
                ->select('report.*', 'client_folder_mapping.folder_name')
                ->join('phm', 'report.phm_folder_id', '=', 'phm.client_id')
                ->join('client_folder_mapping', 'phm.client_id', '=', 'client_folder_mapping.folder_id')
                ->where('report.report_id', $id)
                ->where('report.is_active', 1)
                ->distinct()
                ->first();
            if (!$report) {
                return response()->json(['Status' => 404, 'Error' => 'Not Found', 'Message' => 'Report not found']);
            }
            return response()->json(['Code' => 200, 'Status' => 'Success', 'Message' => 'Report.', 'Response' => $report]);
        } catch (\Exception $e) {
            return response()->json(['Status' => 401, 'Error' => 'Failed', 'Message' => $e->getMessage()]);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $data = $request->all();
            $fields = [];
            if (isset($data['report_name']))        $fields['name'] = $data['report_name'];
            if (isset($data['report_name']))        $fields['report_name'] = $data['report_name'];
            if (isset($data['reporting_year']))     $fields['reporting_year'] = $data['reporting_year'];
            if (isset($data['frequency']))          $fields['frequency'] = $data['frequency'];
            if (isset($data['medical_start_date'])) $fields['medical_start_date'] = $data['medical_start_date'];
            if (isset($data['medical_end_date']))   $fields['medical_end_date'] = $data['medical_end_date'];
            if (isset($data['pharmacy_start_date'])) $fields['pharmacy_start_date'] = $data['pharmacy_start_date'];
            if (isset($data['pharmacy_end_date']))  $fields['pharmacy_end_date'] = $data['pharmacy_end_date'];
            if (empty($fields)) {
                return response()->json(['Status' => 400, 'Error' => 'Bad Request', 'Message' => 'No updatable fields provided']);
            }
            \DB::table('report')->where('report_id', $id)->update($fields);
            return response()->json(['Code' => 200, 'Status' => 'Success', 'Message' => 'Report updated successfully.']);
        } catch (\Exception $e) {
            return response()->json(['Status' => 401, 'Error' => 'Failed', 'Message' => $e->getMessage()]);
        }
    }

	    public function retry($id)
	    {
	        try {
            $report = \DB::table('report')->where('report_id', $id)->where('is_active', 1)->first();
            if (!$report) {
                return response()->json(['Status' => 404, 'Error' => 'Not Found', 'Message' => 'Report not found']);
            }
            // Clear previous run data and reset to queued
	            \DB::table('report')->where('report_id', $id)->update([
	                'looks_generated' => 0,
	                'file_path'       => null,
	            ]);
	            \DB::table('report_look')->where('report_id', $id)->delete();
                $message = $this->phmGeneratorDriver() === 'python'
                    ? 'Report re-queued successfully. The Python PHM worker will pick it up shortly.'
                    : 'Report re-queued for generation.';
	            return response()->json(['Code' => 200, 'Status' => 'Success', 'Message' => $message]);
	        } catch (\Exception $e) {
            return response()->json(['Status' => 401, 'Error' => 'Failed', 'Message' => $e->getMessage()]);
        }
    }

    public function get_template()
    {
        try {
            $template = \DB::table('studio_phm_template')->orderBy('id', 'desc')->first();
            $reports  = \DB::table('report')
                ->select('report.report_id', 'report.name', 'report.studio_report_url', 'client_folder_mapping.folder_name')
                ->join('phm', 'report.phm_folder_id', '=', 'phm.client_id')
                ->join('client_folder_mapping', 'phm.client_id', '=', 'client_folder_mapping.folder_id')
                ->where('report.is_active', 1)
                ->distinct()
                ->get();
            return response()->json([
                'Code'     => 200,
                'Status'   => 'Success',
                'Message'  => 'Studio PHM Template.',
                'Response' => ['template' => $template, 'reports' => $reports],
            ]);
        } catch (\Exception $e) {
            return response()->json(['Status' => 401, 'Error' => 'Failed', 'Message' => $e->getMessage()]);
        }
    }

    public function save_template(Request $request)
    {
        try {
            $reportId = $request->input('report_id');
            \DB::table('studio_phm_template')->truncate();
            \DB::table('studio_phm_template')->insert([
                'report_id'  => $reportId,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            return response()->json(['Code' => 200, 'Status' => 'Success', 'Message' => 'Template saved.']);
        } catch (\Exception $e) {
            return response()->json(['Status' => 401, 'Error' => 'Failed', 'Message' => $e->getMessage()]);
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
       $filepath = 'Generated_PHM/' . $request->file_name;
       if (!Storage::disk('s3')->exists($filepath)) {
           return response()->json(['Status' => 404, 'Error' => 'Not Found', 'Message' => 'File not found on S3.']);
       }
       return Storage::disk('s3')->download($filepath, $request->file_name, [
           'Content-Type' => 'application/pdf',
       ]);
    }
}
