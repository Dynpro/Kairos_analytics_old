<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Report;
use App\Models\Report_look;
use App\Models\Looker;
use App\Http\Requests;
use PDF;
use Illuminate\Support\Facades\DB;
use File;
use Illuminate\Support\Facades\Log;
use Exception;

class GenerateReportController extends Controller
{
    public function handle()
    {
        ini_set('max_execution_time', 1500);
        ini_set("pcre.backtrack_limit", "10000000");
        $lookerSetting = Looker::find('1');
        $api_url = $lookerSetting->api_url;
        $client_id = $lookerSetting->client_id;
        $client_secret = $lookerSetting->client_secret;

        $ReportData = \DB::table('report')
            ->select('report.*')
            // ->where(['report.schedule_time' => date('Y-m-d H:i:s')])
            ->where(['report.looks_generated' => 0])
            ->where(['report.frequency' => 1])
            ->where(['report.is_active' => 1])
            ->orderBy('report.report_id', 'DESC')
            ->get();

        if (isset($ReportData) && !empty($ReportData)) {

            if (isset($ReportData[0]->phm_folder_id)) {

                DB::table('report')->where('report_id', $ReportData[0]->report_id)->update(['looks_generated' => 1]);
                $url = $api_url . "login?client_id=" . $client_id . "&client_secret=" . $client_secret;
                $method = "POST";
                $resp = $this->curlCall($url, $method);
                $responseData = json_decode($resp, true);
                //call to lookers folder api
                $query = array('access_token' => $responseData['access_token']);

                $SectionData = \DB::table('sections')
                    ->select('sections.id', 'sections.section_title', 'sections.section_text', 'sections.section_no')
                    ->join('phm', 'sections.phm_id', '=', 'phm.id')
                    ->where(['phm.client_id' => $ReportData[0]->phm_folder_id])
                    ->where(['phm.is_active' => 1])
                    ->groupBy('sections.id', 'sections.section_title', 'sections.section_text', 'sections.section_no')
                    ->orderBy('section_no')
                    ->get();

                $SubSecData = \DB::table('sub_sections')
                    ->select('sub_sections.id', 'sub_sections.sub_section_title', 'sub_sections.sub_section_text', 'sub_sections.sub_section_no', 'sub_sections.section_id', 'sub_sections.look_id')
                    ->join('sections', 'sub_sections.section_id', '=', 'sections.id')
                    ->join('phm', 'sections.phm_id', '=', 'phm.id')
                    ->where(['phm.client_id' => $ReportData[0]->phm_folder_id])
                    ->where(['phm.is_active' => 1])
                    ->groupBy('sub_sections.id', 'sub_sections.sub_section_title', 'sub_sections.sub_section_text', 'sub_sections.sub_section_no', 'sub_sections.section_id', 'sub_sections.look_id')
                    ->orderBy('section_id')
                    ->get();


                $SubSectionData = [];
                $looks_data = [];
                $look = [];
                foreach ($SubSecData as $key => $value) {
                    if (isset($value->look_id) && !empty($value->look_id)) {
                        $url1 = $api_url . "looks/" . $value->look_id;
                        $method1 = "GET";
                        $lookerData = $this->curlCall($url1, $method1, $query);
                        $lookerData1 = json_decode($lookerData, true);

                        $folder_id = $lookerData1['folder_id'];
                        $model = $lookerData1['model']['id'];

                        $title = $lookerData1['title'] . "_" . $ReportData[0]->report_id . "_" . $ReportData[0]->user_id . "_" . $ReportData[0]->year . "_" . rand(1000, 9999);

                        $view = $lookerData1['query']['view'];

                        $create_look_arr = [
                            "model" => $model,
                            "view" => $view,
                            "title" => $title,
                            "folder_id" => $ReportData[0]->storeLook_folder_id,
                            "public" => true
                        ];


                        $QueriesJson = $lookerData1['query'];
                        unset($QueriesJson['id']);
                        unset($QueriesJson['can']);
                        unset($QueriesJson['url']);
                        unset($QueriesJson['expanded_share_url']);
                        unset($QueriesJson['share_url']);
                        unset($QueriesJson['client_id']);
                        unset($QueriesJson['slug']);

                        foreach ($QueriesJson['filters'] as $k => $val) {
                            if ($k == "vw_medical.diagnosis_date" || $k == "vw_med_and_pharma_summary_1.PAID_YEAR" || $k == "vw_risk_group_migration.File_year" || $k == "vw_medical.Paid_year" || $k == "vw_medication_possession_ratio.year" || $k == "vw_medical.diagnosis_year" || $k == "vw_pharmacy.service_year" || $k == "vw_medical.reporting_year" || $k == "vw_pharmacy.reporting_year" || $k == "ebr_measures.year" || $k == "vw_preventive_screening.year" || $k == "hedis_measure.year") {
                                $QueriesJson['filters'][$k] = "" . $ReportData[0]->year . "";
                            }
                            if ($k == "vw_medical.reporting_date_filter" || $k == "vw_pharmacy.reporting_date_filter") {
                                $QueriesJson['filters'][$k] = "" . $ReportData[0]->reporting_year . "";

                            }

                        }



                        $payload = json_encode($QueriesJson);
                        $url2 = $api_url . "queries?fields=id";
                        $authorization = "Authorization: Bearer " . $responseData['access_token'];
                        $lookerData2 = $this->curlCall1($url2, $method, $authorization, $payload);
                        $lookerData3 = json_decode($lookerData2, true);

                        $create_look_arr['query_id'] = $lookerData3['id'];
                        $create_Look_payload = json_encode($create_look_arr);

                        $url3 = $api_url . "looks";
                        $lookerData4 = $this->curlCall1($url3, $method, $authorization, $create_Look_payload);
                        $lookerData5 = json_decode($lookerData4, true);

                        $SubSectionData[$value->section_id][$key]['section_id'] = $value->section_id;
                        $SubSectionData[$value->section_id][$key]['sub_section_title'] = $value->sub_section_title;
                        $SubSectionData[$value->section_id][$key]['sub_section_text'] = $value->sub_section_text;
                        $SubSectionData[$value->section_id][$key]['sub_section_no'] = $value->sub_section_no;
                        $SubSectionData[$value->section_id][$key]['section_no'] = $value->section_id;
                        $SubSectionData[$value->section_id][$key]['look_id'] = $value->look_id;
                        $SubSectionData[$value->section_id][$key]['chart_type'] = $lookerData5['query']['vis_config']['type'];
                        $SubSectionData[$value->section_id][$key]['look_img_url'] = (isset($lookerData5['image_embed_url'])) ? $lookerData5['image_embed_url'] : "";
                        $SubSectionData[$value->section_id][$key]['embed_url'] = (isset($lookerData5['embed_url']) && !empty($lookerData5['embed_url'])) ? $lookerData5['embed_url'] : "";

                        if (isset($lookerData5['id']) && !empty($lookerData5['id'])) {
                            $looks_data['report_id'] = $ReportData[0]->report_id;
                            $looks_data['section_id'] = $value->section_id;
                            $looks_data['sub_section_id'] = $value->id;
                            $looks_data['sub_section_no'] = $value->sub_section_no;
                            $looks_data['look_id'] = $lookerData5['id'];
                            $looks_data['chart_type'] = $lookerData5['query']['vis_config']['type'];
                            $looks_data['embed_url'] = (isset($lookerData5['embed_url']) && !empty($lookerData5['embed_url'])) ? $lookerData5['embed_url'] : "";
                            $looks_data['look_url'] = (isset($lookerData5['image_embed_url']) && !empty($lookerData5['image_embed_url'])) ? $lookerData5['image_embed_url'] : "";
                            Report_look::insert($looks_data);
                        }
                    }
                    else {
                        $looks_data1['report_id'] = $ReportData[0]->report_id;
                        $looks_data1['section_id'] = $value->section_id;
                        $looks_data1['sub_section_id'] = $value->id;
                        $looks_data1['sub_section_no'] = $value->sub_section_no;
                        Report_look::insert($looks_data1);
                    }
                }

                $id = $ReportData[0]->report_id;
                // return view('reports.view_look',compact('SectionData','SubSectionData','id'));
                $pdf = PDF::loadView('reports.view_look', compact('SectionData', 'SubSectionData', 'id'));
                $path = 'public/pdf/' . $ReportData[0]->name . '_' . time() . '.pdf';
                \DB::table('report')->where('report_id', $ReportData[0]->report_id)->update(['looks_generated' => 2]);
                Storage::put($path, $pdf->output());

            }

        }
    }
    public function down__pdf()
    {
        $ReportData = \DB::table('report')
            ->select('report.*')
            ->whereNull('report.file_path')
            ->where(['report.looks_generated' => 2])
            ->where(['report.frequency' => 1])
            ->where(['report.is_active' => 1])
            ->limit(1)
            ->get();

        if (!empty($ReportData[0])) {
            $id = $ReportData[0]->report_id;
            $name = $ReportData[0]->name;
            \DB::table('report')->where('report_id', $id)->update(['looks_generated' => 3]);

            $lookerSetting = Looker::find('1');
            $api_url = $lookerSetting->api_url;
            $client_id = $lookerSetting->client_id;
            $client_secret = $lookerSetting->client_secret;

            $url = $api_url . "login?client_id=" . $client_id . "&client_secret=" . $client_secret;
            $method = "POST";
            $resp = $this->curlCall($url, $method);
            $responseData = json_decode($resp, true);
            //call to lookers folder api
            $query = array('access_token' => $responseData['access_token']);


            $SectionData = \DB::table('sections')
                ->select('sections.*')
                ->join('phm', 'sections.phm_id', '=', 'phm.id')
                ->join('report', 'phm.client_id', '=', 'report.phm_folder_id')
                ->where(['report.report_id' => $id])
                ->where(['phm.is_active' => 1])
                ->orderBy('section_no')
                ->get();
            $phmData = \DB::table('phm')
                ->select('phm.name')
                ->join('report', 'phm.client_id', '=', 'report.phm_folder_id')
                ->where(['report.report_id' => $id])
                ->where(['phm.is_active' => 1])
                ->get();

            $SubSecData = DB::select("SELECT
        sub_sections.id,
        sub_sections.sub_section_title,
        sub_sections.sub_section_text,
        sub_sections.sub_section_no,
        sub_sections.section_id,
        sub_sections.phm_id,
        sub_sections.long_table,
        sections.section_no,
        report_look.look_url,
        report_look.look_id,
        report_look.chart_type,
        report_look.embed_url
        FROM
        report INNER JOIN report_look ON
        report.report_id = report_look.report_id
        INNER JOIN sub_sections on report_look.sub_section_id = sub_sections.id
        INNER JOIN sections on sub_sections.section_id = sections.id
        WHERE report.report_id = $id
        ORDER by sections.section_no ASC, sub_sections.sub_section_no ASC");

            $SubSectionData = [];

            foreach ($SubSecData as $key => $value) {
                $SubSectionData[$value->section_id][$key]['section_id'] = $value->section_id;
                $SubSectionData[$value->section_id][$key]['sub_section_title'] = $value->sub_section_title;
                $SubSectionData[$value->section_id][$key]['sub_section_text'] = $value->sub_section_text;
                $SubSectionData[$value->section_id][$key]['sub_section_no'] = $value->sub_section_no;
                $SubSectionData[$value->section_id][$key]['look_id'] = $value->look_id;
                $SubSectionData[$value->section_id][$key]['sub_section_id'] = $value->id;
                $SubSectionData[$value->section_id][$key]['chart_type'] = $value->chart_type;
                $SubSectionData[$value->section_id][$key]['embed_url'] = $value->embed_url;
                $SubSectionData[$value->section_id][$key]['long_look'] = $value->long_table;
                if (isset($value->look_url) && $value->look_url != "") {
                    if ($value->chart_type == "looker_bar" || $value->chart_type == "looker_column") {
                        try {
                            $img = imagecreatefromstring($this->file_get_contents_curl($value->look_url)); // Load and instantiate the image
                            imagepng($img);
                            if ($img) {
                                $cropped = imagecropauto($img, IMG_CROP_WHITE);
                                if ($cropped !== false) {
                                    imagedestroy($img);
                                    ob_start();
                                    imagepng($cropped);
                                    $image = ob_get_contents();
                                    $imgname = $value->look_id . '.png';
                                    $filePath = 'phm_look/' . $imgname;
                                    Storage::disk('s3')->put($filePath, $image);
                                    ob_end_clean();

                                    $s3 = \Storage::disk('s3');
                                    $client = $s3->getDriver()->getAdapter()->getClient();
                                    $expiry = "+10 minutes";
                                    $imgGetPath = 'phm_look/' . $value->look_id . ".png";
                                    $command = $client->getCommand('GetObject', [
                                        'Bucket' => 'kairos-next-gen-storage', // bucket name
                                        'Key' => $imgGetPath
                                    ]);

                                    $request = $client->createPresignedRequest($command, $expiry);
                                    $imagepath = (string)$request->getUri();
                                    $SubSectionData[$value->section_id][$key]['look_img_url'] = $imagepath;
                                }
                                else {
                                    $SubSectionData[$value->section_id][$key]['look_img_url'] = "";
                                }
                            }
                        }
                        catch (\Exception $e) {
                            DB::table('report')->where('report_id', $id)->update(['looks_generated' => 7]);
                            echo 'Message: ' . $e->getMessage();
                        }
                    }
                    else {
                        $url4 = $api_url . "looks/" . $value->look_id . "/run/html?apply_formatting=true";
                        $method1 = "GET";
                        $htmlData = $this->curlCall($url4, $method1, $query);
                        $SubSectionData[$value->section_id][$key]['look_img_url'] = $htmlData;
                    }

                }

            }
            \DB::table('report')->where('report_id', $id)->update(['looks_generated' => 4]);
            // return view('reports.view',compact('SectionData','SubSectionData','phmData','id'));
            $this->generate_pdf($name, $id, $SectionData, $SubSectionData, $phmData);
        }
    }

    public function generate_pdf($name, $id, $SectionData, $SubSectionData, $phmData)
    {
        ini_set('max_execution_time', 2400);
        ini_set("pcre.backtrack_limit", "10000000");
        $ReportData = \DB::table('report')
            ->select('report.year', 'report.phm_folder_id', 'client_folder_mapping.folder_name', 'client_folder_mapping.phm_logo', 'users.email', 'users.name', 'report.reporting_year')
            ->join('phm', 'report.phm_folder_id', '=', 'phm.client_id')
            ->join('client_folder_mapping', 'phm.client_id', '=', 'client_folder_mapping.folder_id')
            ->join('users', 'report.user_id', '=', 'users.id')
            ->where(['report.report_id' => $id])
            ->get();

        $SchemaData = \DB::table('client_folder_mapping')
            ->select('client_folder_mapping.schema_name')
            ->where(['client_folder_mapping.folder_id' => $ReportData[0]->phm_folder_id])
            ->get();
        $years = rtrim($ReportData[0]->year, ',');
        // $date_range=$this->get_dates($years,$SchemaData[0]->schema_name,$ReportData[0]->reporting_year);

        if (!empty($SchemaData)) {
            // $date_range_data = json_decode($date_range);

            try {
                $pdf = PDF::loadView('reports.view', compact('SectionData', 'SubSectionData', 'phmData', 'id', 'ReportData'));

                $path = 'public/pdf/' . $name . '_' . time() . '.pdf';
                Storage::put($path, $pdf->output());

                $filePath = 'Generated_PHM/' . $name . '_' . date('mdy') . '.pdf';
                Storage::disk('s3')->put($filePath, $pdf->output());

                // $this->send_notification($ReportData[0]->name,$ReportData[0]->email,$path);

                //Remove from Local Storage
                unlink(storage_path('app/' . $path));
                \DB::table('report')->where('report_id', $id)->update(['file_path' => $filePath, 'looks_generated' => 6]);
            }
            catch (Exception $e) {
                echo 'Message: ' . $e->getMessage();
                \DB::table('report')->where('report_id', $id)->update(['looks_generated' => 7]);
            }
        }
        //update table

        return $pdf->download($name . '_' . time() . '.pdf');
    }
    public function curlCall($url, $method, $query = null)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS => $query,
            // CURLOPT_HTTPHEADER => array(
            //  "Content-Type: application/x-www-form-urlencoded"
            // ),
        ));
        $response = curl_exec($curl);
        if ($response === false) {
            \Illuminate\Support\Facades\Log::error("CURL Error for URL $url: " . curl_error($curl));
        }
        curl_close($curl);

        //echo $responseData['access_token'];
        return $response;
    }

    public function curlCall1($url, $method, $authorization, $query = null)
    {
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
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json",
                $authorization
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);

        //echo $responseData['access_token'];
        return $response;
    }

    function file_get_contents_curl($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //Set curl to return the data instead of printing it to the browser.
        curl_setopt($ch, CURLOPT_URL, $url);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }
    public function update_flag(Request $request)
    {
        $flag = $request->flag;
        $report_id = $request->report_id;
        \DB::table('report')
            ->where('report_id', $report_id)
            ->update(['looks_generated' => $flag]);
        return response()->json(['success' => 'Data is updated successfully']);
    }

    public function directDownloadByClient(Request $request)
    {
        ini_set('max_execution_time', 2400);
        ini_set("pcre.backtrack_limit", "10000000");

        $clientId = $request->client_id;
        if (!$clientId)
            return response()->json(['Status' => 'Failed', 'Message' => 'Client ID is required']);

        $lookerSetting = Looker::find('1');
        $api_url = $lookerSetting->api_url;
        $client_id = $lookerSetting->client_id;
        $client_secret = $lookerSetting->client_secret;

        $url = $api_url . "login?client_id=" . $client_id . "&client_secret=" . $client_secret;
        $method = "POST";
        $resp = $this->curlCall($url, $method);
        $responseData = json_decode($resp, true);

        if (!$responseData || !isset($responseData['access_token'])) {
            \Illuminate\Support\Facades\Log::error("Looker login failed for client $clientId. URL: $url Response: " . ($resp ?? 'No response'));
            return \response()->json(['Status' => 'Failed', 'Message' => 'Could not authenticate with Looker. Check your Looker settings.'], 500);
        }

        $query = array('access_token' => $responseData['access_token']);

        // Fetch Section and Subsection data for this PHM client
        $SectionData = \DB::table('sections')
            ->select('sections.*')
            ->join('phm', 'sections.phm_id', '=', 'phm.id')
            ->where(['phm.client_id' => $clientId, 'phm.is_active' => 1])
            ->orderBy('section_no')
            ->get();

        if ($SectionData->isEmpty()) {
            return response()->json(['Status' => 'Failed', 'Message' => 'No PHM report configuration found for this client.']);
        }

        $phmData = \DB::table('phm')
            ->select('phm.name')
            ->where(['phm.client_id' => $clientId, 'phm.is_active' => 1])
            ->get();

        $SubSecData = \DB::select("SELECT
            sub_sections.id, sub_sections.sub_section_title, sub_sections.sub_section_text,
            sub_sections.sub_section_no, sub_sections.section_id, sub_sections.phm_id, sub_sections.look_id,
            sub_sections.long_table, sections.section_no
            FROM sub_sections 
            INNER JOIN sections on sub_sections.section_id = sections.id
            INNER JOIN phm on sections.phm_id = phm.id
            WHERE phm.client_id = ? AND phm.is_active = 1
            ORDER by sections.section_no ASC, sub_sections.sub_section_no ASC", [$clientId]);

        $SubSectionData = [];
        foreach ($SubSecData as $key => $value) {
            $SubSectionData[$value->section_id][$key]['section_id'] = $value->section_id;
            $SubSectionData[$value->section_id][$key]['sub_section_title'] = $value->sub_section_title;
            $SubSectionData[$value->section_id][$key]['sub_section_text'] = $value->sub_section_text;
            $SubSectionData[$value->section_id][$key]['sub_section_no'] = $value->sub_section_no;
            $SubSectionData[$value->section_id][$key]['look_id'] = $value->look_id;

            // Re-use logic to get HTML/Image representation
            $url4 = $api_url . "looks/" . $value->look_id . "/run/html?apply_formatting=true";
            $method1 = "GET";
            $htmlData = $this->curlCall($url4, $method1, $query);
            $SubSectionData[$value->section_id][$key]['look_img_url'] = $htmlData;
        }

        $ReportData = null; // We don't have a report entry for direct downloads

        $pdf = PDF::loadView('reports.view', compact('SectionData', 'SubSectionData', 'phmData', 'ReportData'))
            ->setPaper('a4', 'portrait')
            ->setOption('margin-top', 30)
            ->setOption('margin-bottom', 30);

        return $pdf->download(($phmData[0]->name ?? 'Dashboard') . '.pdf');
    }
}