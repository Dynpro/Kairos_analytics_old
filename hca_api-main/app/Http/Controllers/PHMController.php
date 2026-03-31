<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Report;
use App\Models\Report_look;
use App\Models\Phm;
use App\Models\Section;
use App\Models\Subsection;
use App\Libraries\Helpers;
use App\Models\Looker;
use Illuminate\Support\Facades\Response as Download;
use PDF;
use File;
use Response;
class PHMController extends Controller
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
            $phmData= \DB::table('phm')
            ->select('phm.*','client_folder_mapping.folder_name','report_type.report_type')
            ->join('client_folder_mapping','phm.client_id','=','client_folder_mapping.folder_id')
            ->join('report_type','phm.report_type','=','report_type.report_type_id')
            ->where(['phm.is_active' => '1','client_folder_mapping.is_active' => '1','phm.entity_id' => env('env_entity_id')])
            ->orwhere(['phm.is_master' => '1'])
            ->get();

            return response()->json(['Code' => 200,'Status' => "Success", 'Message' => 'PHM List.','Response' =>$phmData]);
        } catch (\Exception $e) {
            return response()->json(['Status' => 401,'Error' =>'Failed', 'Message' => $e->getMessage()]);
        }
    }
    public function uploadDoc(Request $request)
    {
        // return $request->all();
        try {
            $this->validate($request, [
                'file' => ['required']
               ]
            );
            $file = $request->file('file');
            $name = time() .'_'. $file->getClientOriginalName();
            $filePath = 'PHM/' . $name;
            Storage::disk('s3')->put($filePath, file_get_contents($file));
            $phm = Phm::find($request->phm_id);
            $phm->file_path = $filePath;
            $phm->save();

            return response()->json(['Code' => 200,'Status' => "Success", 'Message' => 'File has been successfully uploaded!!']);
        } catch (\Exception $e) {
            return response()->json(['Status' => 401,'Error' =>'Failed', 'Message' => $e->getMessage()]);
        }

    }
    public function downloadFormattedDoc(Request $request)
    {
        $filepath=$request->file_path;
        $file =  Storage::disk('s3')->get($filepath);
        $assetPath=Storage::disk('s3')->url($filepath);
         $headers = [
             'Content-Type'        => 'Content-Type: application/zip',
             'Content-Disposition' => 'attachment; filename='.$request->file_name,
         ];
          return response($file, 200, $headers);
    }
    public function getFormattedCopy()
    {
        try {
            $PhmReportData= \DB::table('users')
            ->select('client_folder_mapping.parent_folder_id','client_folder_mapping.folder_name','client_folder_mapping.folder_id','phm.name','phm.file_path')
            ->join('users_folder_access','users.id','=','users_folder_access.user_id')
            ->join('client_folder_mapping','users_folder_access.folder_id','=','client_folder_mapping.parent_folder_id')
            ->join('phm','client_folder_mapping.folder_id','=','phm.client_id')
            ->where('users.id',auth()->user()->id)
            ->whereNotNull('phm.file_path')
            ->groupBy('client_folder_mapping.parent_folder_id','client_folder_mapping.folder_name','users_folder_access.folder_id','client_folder_mapping.folder_id','phm.name','phm.file_path')
            ->get();  
            $resultset = [];
            $index_counters = [];
            foreach($PhmReportData as $key => $value)
            {
                if (!isset($index_counters[$value->folder_name])) {
                    // If not, initialize it to 0
                    $index_counters[$value->folder_name] = 0;
                }
                $resultset[$value->folder_name][$index_counters[$value->folder_name]]['file_path'] =  $value->file_path;
                $resultset[$value->folder_name][$index_counters[$value->folder_name]]['phm_name'] =  $value->name;

                $index_counters[$value->folder_name]++;
            }
            return response()->json(['Code' => 200,'Status' => "Success", 'Message' => 'PHM Formmated copy list.','Response' =>$resultset]);
        } catch (\Exception $e) {
            return response()->json(['Status' => 401,'Error' =>'Failed', 'Message' => $e->getMessage()]);
        }
    }
    public function getReportFormattedCopy()
    {
        try {
            $PhmReportData= \DB::table('users')
            ->select('client_folder_mapping.parent_folder_id','client_folder_mapping.folder_name','client_folder_mapping.folder_id','phm.name','phm.file_path')
            ->join('users_folder_access','users.id','=','users_folder_access.user_id')
            ->join('client_folder_mapping','users_folder_access.folder_id','=','client_folder_mapping.parent_folder_id')
            ->join('phm','client_folder_mapping.folder_id','=','phm.client_id')
            ->where('users.id',auth()->user()->id)
            ->whereNotNull('phm.file_path')
            ->groupBy('client_folder_mapping.parent_folder_id','client_folder_mapping.folder_name','users_folder_access.folder_id','client_folder_mapping.folder_id','phm.name','phm.file_path')
            ->get();  
            $resultset = [];
            $index_counters = [];
            foreach($PhmReportData as $key => $value)
            {
                if (!isset($index_counters[$value->folder_name])) {
                    // If not, initialize it to 0
                    $index_counters[$value->folder_name] = 0;
                }
                $resultset[$value->folder_name][$index_counters[$value->folder_name]]['file_path'] =  $value->file_path;
                $resultset[$value->folder_name][$index_counters[$value->folder_name]]['phm_name'] =  $value->name;

                $index_counters[$value->folder_name]++;
            }
            return response()->json(['Code' => 200,'Status' => "Success", 'Message' => 'PHM Formmated copy list.','Response' =>$resultset]);
        } catch (\Exception $e) {
            return response()->json(['Status' => 401,'Error' =>'Failed', 'Message' => $e->getMessage()]);
        }
    }
    public function PHM_clientlist()
    {
        try {
            // $lookerSetting = Looker::find('1');
            // $api_url = $lookerSetting->api_url;
            // $looker_id = $lookerSetting->client_id;
            // $looker_secret = $lookerSetting->client_secret;

            // $url = $api_url . "login?client_id=".$looker_id."&client_secret=".$looker_secret;
            // $method = "POST";
            // $resp= $this->curlCall($url, $method);
            // $responseData = json_decode($resp,true);
            // //call to lookers folder api
            // $query = array('access_token' => $responseData['access_token']);

            // $method1 = 'GET';

            // $folderChildUrl = $api_url . "folders/2433/children";
            // $folderChildUrl1 = $api_url . "folders/88/children";

            // $childData= $this->curlCall($folderChildUrl, $method1,$query);

            // $childData= json_decode($childData, true);

            // $childData1= $this->curlCall($folderChildUrl1, $method1,$query);
            // $childData1= json_decode($childData1, true);

            // $folderChild = array();
            // $folderChildArr = array();

            // foreach ($childData as $fldr){
            //     $folderChild['id']= $fldr['id'];
            //     $folderChild['name']= $fldr['name'];
            //     //$folderChild['embed_url']= $fldr['embed_url'];
            //     $folderChildArr[] = $folderChild;
            // }

            // foreach ($childData1 as $fldr1){
            //     $folderChild1['id']= $fldr1['id'];
            //     $folderChild1['name']= $fldr1['name'];
            //     //$folderChild['embed_url']= $fldr['embed_url'];
            //     $folderChildArr[] = $folderChild1;
            // }
            $clients = \DB::table('looker_parent_phm')
                    ->select('*')
                    ->get();
            return response()->json(['Code' => 200,'Status' => "Success", 'Message' => 'PHM Client List.','Response' =>$clients]);
        } catch (\Exception $e) {
            return response()->json(['Status' => 401,'Error' =>'Failed', 'Message' => $e->getMessage()]);
        }
    }
    public function curlCall($url, $method, $query=null){
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS => $query,
            // CURLOPT_HTTPHEADER => array(
            //  "Content-Type: application/x-www-form-urlencoded"
            // ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        
        //echo $responseData['access_token'];
        return $response;
    }
    public function getLooks($id)
    {
        
        $lookerSetting = Looker::find('1');
		$api_url = $lookerSetting->api_url;
		$client_id = $lookerSetting->client_id;
        $client_secret = $lookerSetting->client_secret;
        
        $folder_id= $id;
        $url = $api_url . "login?client_id=".$client_id."&client_secret=".$client_secret;
        $method = "POST";
        $resp= $this->curlCall($url, $method);
        $responseData = json_decode($resp,true);
       
  
        //call to lookers folder api
        $query = array('access_token' => $responseData['access_token']);
        $url1 = $api_url . "folders/".$folder_id;
        $method1 = "GET";
        $lookerData= $this->curlCall($url1, $method1,$query);
        $lookerData= json_decode($lookerData, true);

        $lookerDashboards = array();
        // $lookerDashboardsArr = array();
        // foreach ($lookerData['dashboards'] as $dash){
        //     $lookerDashboards['id']= $dash['id'];
        //     $lookerDashboards['name']= $dash['title'];
        //     $lookerDashboardsArr[] = $lookerDashboards;
        // }
        $lookerLooks = array();
        $lookerLooksArr = array();
        foreach ($lookerData['looks'] as $look){
            $lookerLooks['id']= $look['id'];
            $lookerLooks['name']= $look['title'];
            $lookerLooks['embed_url']= $look['embed_url'];
            $lookerLooks['image_embed_url']= $look['image_embed_url'];
            $lookerLooksArr[] = $lookerLooks;
        }
            //print_r($lookerLooksArr);
            //print_r($lookerDashboardsArr);
        
        return response()->json(['Code' => 200,'Status' => "Success", 'Message' => 'PHM Looks List.','Response' =>$lookerLooksArr]);    
        $structure = array (
        
        array (
            'name' => 'Looks',
            'open' => false,
            //'type'=> 'Tree.FOLDER',
            'selected' => true,
            'children' => $lookerLooksArr,
            
        ),
        
        array (
            'name' => 'Dashboards',
            'open' => false,
            //'type'=> 'Tree.FOLDER',
            'selected' => true,
            'children' => $lookerDashboardsArr,
            
        ),
        
        // array (
        //     'name' => 'folder 2 (asynced)',
        //     //'type'=> 'Tree.FOLDER',
        //     'asynced' => true,
        // ),
        );

        //print_r(json_decode($structure,true));		
                
                
                
            //echo json_encode(array("looks"=>$lookerLooksArr,"dashboard"=>$lookerDashboardsArr));
            echo json_encode($structure);
    }
    public function store(Request $request)
    {
        try {
            $validator = $this->validate($request, [
                'reportName' => ['required'],
                'clientName' => ['required'],
                'reportType' => ['required'],
                'medStartDate' => ['required'],
                'medEndDate' => ['required'],
                'pharmaStartDate' => ['required'],
                'pharmaEndDate' => ['required'],
            ]);

            $phm_id = Phm::create([
                'name'              => $request->reportName,
                'client_id'         => $request->clientName,
                'report_type'       => $request->reportType,
                'start_date'        => $request->medStartDate,
                'end_date'          => $request->medEndDate,
                'pharma_start_date' => $request->pharmaStartDate,
                'pharma_end_date'   => $request->pharmaEndDate,
                'entity_id'         => env('env_entity_id'),
                'created_by'        => auth()->user()->id,            
            ])->id;


            if(!empty($request->sections))
            {
                foreach($request->sections as $key => $value)
                {
                    $section_id = Section::create([
                        'section_title'     => $value['title'],
                        'section_text'      => $value['description'],
                        'section_no'        => $value['sectionNo'],
                        'phm_id'            => $phm_id,     
                    ])->id;
                    foreach($value['subsections'] as $k => $val)
                    {
                        $subsection_id = Subsection::create([
                            'sub_section_title'     => $val['title'],
                            'sub_section_text'      => $val['description'],
                            'sub_section_no'        => $val['subSectionNo'],
                            'section_id'            => $section_id,
                            'look_img_url'          => $val['lookUrl'],
                            'phm_id'                => $phm_id,
                            'look_id'               => $val['lookId'],     
                        ])->id;
                    }
                }
            }
            return response()->json(['Code' => 200,'Status' => "Success", 'Message' => 'PHM has been successfully created!!','Response' =>$phm_id]);
        } catch (\Exception $e) {
            return response()->json(['Status' => 401,'Error' =>'Failed', 'Message' => $e->getMessage()]);
        }
       
    }
    public function edit($id)
    {
        try {
            // Fetch PHM data
            $phmdata = \DB::table('phm')
                ->select('*')
                ->where('is_active', 1)
                ->where('id', $id)
                ->get();
    
            $resultset = [];
            if (!empty($phmdata)) {
                $resultset['phm'] = $phmdata;
    
                // Fetch Sections data
                $sections = \DB::table('sections')
                    ->select('*')
                    ->where('is_active', 1)
                    ->where('phm_id', $id)
                    ->orderBy('section_no', 'ASC')
                    ->get();
    
                if (!empty($sections)) {
                    $resultset['sections'] = [];
    
                    // Loop through each section and add subsections
                    foreach ($sections as $section) {
                        // Fetch Subsections related to this section
                        $sub_sections = \DB::table('sub_sections')
                            ->select('sub_sections.*', 'sections.section_no')
                            ->join('sections', 'sub_sections.section_id', '=', 'sections.id')
                            ->where('sub_sections.is_active', 1)
                            ->where('sub_sections.phm_id', $id)
                            ->where('sub_sections.section_id', $section->id) // Only fetch subsections for the current section
                            ->orderBy('sub_sections.section_id', 'ASC')
                            ->get();
    
                        // Add subsections to the section
                        if (!empty($sub_sections)) {
                            $section->subsections = $sub_sections;
                        }
    
                        // Add section with its subsections to the result
                        $resultset['sections'][] = $section;
                    }
                }
            }
    
            return response()->json([
                'Code' => 200,
                'Status' => 'Success',
                'Message' => 'PHM Data.',
                'Response' => $resultset
            ], 200, [], JSON_UNESCAPED_SLASHES);
        } catch (\Exception $e) {
            return response()->json(['Status' => 401, 'Error' => 'Failed', 'Message' => $e->getMessage()]);
        }
    }
    
    public function update(Request $request, $id)
    {
        try {
            $validator = $this->validate($request, [
                'reportName' => ['required'],
                'clientName' => ['required'],
                'reportType' => ['required'],
                'medStartDate' => ['required'],
                'medEndDate' => ['required'],
                'pharmaStartDate' => ['required'],
                'pharmaEndDate' => ['required'],
            ]);

            $phm = Phm::find($id);
            $phm->name                = $request->reportName;
            $phm->client_id           = $request->clientName;
            $phm->report_type         = $request->reportType;
            $phm->start_date          = $request->medStartDate;
            $phm->end_date            = $request->medEndDate;
            $phm->pharma_start_date   = $request->pharmaStartDate;
            $phm->pharma_end_date     = $request->pharmaEndDate;
            $phm->entity_id           = env('env_entity_id');
            $phm->updade_by          = auth()->user()->id;
            $phm->save();

            if(!empty($request->sections))
            {
                DB::table('sections')->where('phm_id', $id)->delete();
                DB::table('sub_sections')->where('phm_id', $id)->delete();
                foreach($request->sections as $key => $value)
                {                    
                    $section_id = Section::create([
                        'section_title'     => $value['title'],
                        'section_text'      => $value['description'],
                        'section_no'        => $value['sectionNo'],
                        'phm_id'            => $id,     
                    ])->id;
                    
                    foreach($value['subsections'] as $k => $val)
                    {
                        $subsection_id = Subsection::create([
                            'sub_section_title'     => $val['title'],
                            'sub_section_text'      => $val['description'],
                            'sub_section_no'        => $val['subSectionNo'],
                            'section_id'            => $section_id,
                            'look_img_url'          => $val['lookUrl'],
                            'phm_id'                => $id,
                            'look_id'               => $val['lookId'],     
                        ])->id;
                    }
                }
            }
            return response()->json(['Code' => 200,'Status' => "Success", 'Message' => 'PHM has been successfully Updated!!','Response' =>$id]);
        } catch (\Exception $e) {
            return response()->json(['Status' => 401,'Error' =>'Failed', 'Message' => $e->getMessage()]);
        }
       
    }
    public function destroy($id)
    {
       try {
	    	DB::table('phm')
            ->where('id', $id)
            ->update(['is_active' => '0']);

            DB::table('sections')
            ->where('phm_id', $id)
            ->update(['is_active' => '0']);

            DB::table('sub_sections')
            ->where('phm_id', $id)
            ->update(['is_active' => '0']);

	        return response()->json(['Code' => 200,'Status' => "Success", 'Message' => 'PHM has been deleted successfully!!']);
        } catch (\Exception $e) {
            return response()->json(['Status' => 401,'Error' =>'Failed', 'Message' => $e->getMessage()]);
        }
    }	
    public function copyPHM(Request $request)
    {
        try {
            // Validate the request
            $this->validate($request, [
                'reportName' => ['required'],
                'clientName' => ['required'],
                'reportType' => ['required'],
                'medStartDate' => ['required'],
                'medEndDate' => ['required'],
                'pharmaStartDate' => ['required'],
                'pharmaEndDate' => ['required'],
            ]);
    
            DB::beginTransaction();
    
            // Create the new PHM record
            $phm = Phm::create([
                'name'              => $request->reportName,
                'client_id'         => $request->clientName,
                'report_type'       => $request->reportType,
                'start_date'        => $request->medStartDate,
                'end_date'          => $request->medEndDate,
                'pharma_start_date' => $request->pharmaStartDate,
                'pharma_end_date'   => $request->pharmaEndDate,
                'entity_id'         => env('env_entity_id'),
                'created_by'        => auth()->user()->id,
            ]);
    
            $newPhmId = $phm->id;
            $copyPhmId = $request->rowToCopy['id'];
    
            // Duplicate Sections
            $sections = DB::table('sections')->where('phm_id', $copyPhmId)->get();
            foreach ($sections as $section) {
                $newSection = (array) $section;
                unset($newSection['id']); // Remove the original ID
                $newSection['phm_id'] = $newPhmId; // Set the new PHM ID
    
                // Insert the section and get the new section ID
                $newSectionId = DB::table('sections')->insertGetId($newSection);
    
                // Duplicate Subsections
                $subsections = DB::table('sub_sections')
                    ->where('phm_id', $copyPhmId)
                    ->where('section_id', $section->id)
                    ->get();
    
                $newSubsections = [];
                foreach ($subsections as $subsection) {
                    $newSubsection = (array) $subsection;
                    unset($newSubsection['id']); // Remove the original ID
                    $newSubsection['phm_id'] = $newPhmId; // Set the new PHM ID
                    $newSubsection['section_id'] = $newSectionId; // Set the new Section ID
                    $newSubsections[] = $newSubsection;
                }
    
                // Insert all new subsections in bulk
                if (!empty($newSubsections)) {
                    DB::table('sub_sections')->insert($newSubsections);
                }
            }
    
            DB::commit();
    
            return response()->json([
                'Code' => 200,
                'Status' => 'Success',
                'Message' => 'PHM Copy has been successfully created!',
                'Response' => $newPhmId,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'Status' => 401,
                'Error' => 'Failed',
                'Message' => $e->getMessage(),
            ]);
        }
    }
    
}
