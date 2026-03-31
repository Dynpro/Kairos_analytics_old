<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Looker_data;
use App\Models\Looker;
use App\Models\Looker_parent_dashboards;
use App\Models\Looker_parent_phm;
use App\Models\Snowflake_schema;

class LookerDataController extends Controller
{
    public function getLookerClientCategoryWise()
    {
        $dataset = looker_data::groupBy('looker_data.client_primary_id', 'looker_data.client_id', 'looker_data.client_name', 'looker_data.category_id', 'client_category.category', 'looker_data.subcategory_id', 'client_subcategory.subcategory')
            ->select('looker_data.client_primary_id', 'looker_data.client_id', 'looker_data.client_name', 'looker_data.category_id', 'client_category.category', 'looker_data.subcategory_id', 'client_subcategory.subcategory')
            ->join('client_category', 'looker_data.category_id', '=', 'client_category.category_id')
            ->leftJoin('client_subcategory', 'looker_data.subcategory_id', '=', 'client_subcategory.subcategory_id')
            ->join('client_folder_mapping', 'looker_data.client_primary_id', '=', 'client_folder_mapping.id')
            ->where('client_folder_mapping.entity_id', auth()->user()->entity_id)
            ->orderBy('looker_data.category_id')
            ->orderBy('looker_data.client_name')
            ->get();

        $finalArray = [];
        foreach ($dataset as $key => $value) {
            if ($value->subcategory_id == null) {
                $finalArray[$value->category][null][] = $value;
            }
            else {
                $finalArray[$value->category][$value->subcategory][] = $value;
            }
            $finalArray[$value->category]['category_id'] = $value->category_id;

        }
        return response()->json(['status' => 'Success', 'data' => $finalArray]);
    }
    public function getLookerClientCategoryWiseNew()
    {
        $dataset = looker_data::groupBy('looker_data.client_primary_id', 'looker_data.client_id', 'looker_data.client_name', 'looker_data.subcategory_id', 'client_subcategory.subcategory')
            ->select('looker_data.client_primary_id', 'looker_data.client_id', 'looker_data.client_name', 'looker_data.subcategory_id', 'client_subcategory.subcategory')
            ->leftJoin('client_subcategory', 'looker_data.subcategory_id', '=', 'client_subcategory.subcategory_id')
            ->join('client_folder_mapping', 'looker_data.client_primary_id', '=', 'client_folder_mapping.id')
            ->where('client_folder_mapping.entity_id', auth()->user()->entity_id)
            ->orderBy('looker_data.client_name')
            ->get();

        $finalArray = [];
        foreach ($dataset as $key => $value) {
            if ($value->subcategory_id == null) {
                $finalArray[null][] = $value;
            }
            else {
                $finalArray[$value->subcategory][] = $value;
            }

        }
        return response()->json(['status' => 'Success', 'data' => $finalArray]);
    }
    public function getLookerClient()
    {
        $dataset = looker_data::groupBy('client_primary_id', 'client_id', 'client_name', 'category_id', 'subcategory_id')->select('client_primary_id', 'client_id', 'client_name', 'category_id', 'subcategory_id')->orderBy('client_name')->get();
        return response()->json(['status' => 'Success', 'data' => $dataset]);
    }
    public function getLookerClientFolder()
    {
        $dataset = looker_data::groupBy('client_id', 'folder_id', 'folder_name')->select('client_id', 'folder_id', 'folder_name')->whereNotNull('folder_id')->orderBy('folder_name')->get();
        $result = [];
        foreach ($dataset as $key => $value) {
            if ($value->folder_name != "") {
                $folder_name = explode(".", $value->folder_name);
                if (isset($folder_name[1])) {
                    $result[$value->client_id][$value->folder_id]['folder_id'] = $value->folder_id;
                    $result[$value->client_id][$value->folder_id]['folder_name'] = trim($folder_name[1]);
                }
                else {
                    $result[$value->client_id][$value->folder_id]['folder_id'] = $value->folder_id;
                    $result[$value->client_id][$value->folder_id]['folder_name'] = trim($folder_name[0]);
                }
            }
            else {
                $result[$value->client_id][$value->folder_id]['folder_id'] = $value->folder_id;
                $result[$value->client_id][$value->folder_id]['folder_name'] = "";
            }
        }
        return response()->json(['status' => 'Success', 'data' => $result]);
    }
    public function getLookerFolderDashboard()
    {
        $dataset = looker_data::orderBy('client_id')->get();
        $result = [];
        foreach ($dataset as $key => $value) {
            $result[$value->client_id][$value->folder_id][$value->dash_id]['dash_id'] = $value->dash_id;
            $result[$value->client_id][$value->folder_id][$value->dash_id]['title'] = $value->title;
        }
        return response()->json(['status' => 'Success', 'data' => $result]);
    }
    public function getUserAccessClientsCategoryWise()
    {
        $dataset = \DB::table('grp_role_usr_mapping')
            ->select('looker_data.client_primary_id', 'looker_data.client_id', 'looker_data.client_name', 'looker_data.category_id', 'client_category.category', 'looker_data.subcategory_id', 'client_subcategory.subcategory')
            ->join('users_dasboards_mapping', 'grp_role_usr_mapping.grp_usr_mapping_id', '=', 'users_dasboards_mapping.grp_usr_mapping_id')
            ->join('looker_data', 'users_dasboards_mapping.client_primary_id', '=', 'looker_data.client_primary_id')
            ->join('client_category', 'looker_data.category_id', '=', 'client_category.category_id')
            ->leftJoin('client_subcategory', 'looker_data.subcategory_id', '=', 'client_subcategory.subcategory_id')
            ->where('grp_role_usr_mapping.is_active', 1)
            ->where('grp_role_usr_mapping.user_id', auth()->user()->id)
            ->groupBy('looker_data.client_primary_id', 'looker_data.client_id', 'looker_data.client_name', 'looker_data.category_id', 'client_category.category', 'looker_data.subcategory_id', 'client_subcategory.subcategory')
            ->orderBy('looker_data.category_id')
            ->orderBy('looker_data.client_name', 'asc')
            ->get();
        $finalArray = [];
        foreach ($dataset as $key => $value) {
            if ($value->subcategory_id == null) {
                $finalArray[$value->category][null][] = $value;
            }
            else {
                $finalArray[$value->category][$value->subcategory][] = $value;
            }
            $finalArray[$value->category]['category_id'] = $value->category_id;

        }
        return response()->json(['status' => 'Success', 'data' => $finalArray]);
    }
    public function getUserAccessClientsCategoryWiseNew()
    {
        $dataset = \DB::table('grp_role_usr_mapping')
            ->select('looker_data.client_primary_id', 'looker_data.client_id', 'looker_data.client_name', 'looker_data.subcategory_id', 'client_subcategory.subcategory')
            ->join('users_dasboards_mapping', 'grp_role_usr_mapping.grp_usr_mapping_id', '=', 'users_dasboards_mapping.grp_usr_mapping_id')
            ->join('looker_data', 'users_dasboards_mapping.client_primary_id', '=', 'looker_data.client_primary_id')
            ->leftJoin('client_subcategory', 'looker_data.subcategory_id', '=', 'client_subcategory.subcategory_id')
            ->where('grp_role_usr_mapping.is_active', 1)
            ->where('grp_role_usr_mapping.user_id', auth()->user()->id)
            ->groupBy('looker_data.client_primary_id', 'looker_data.client_id', 'looker_data.client_name', 'looker_data.subcategory_id', 'client_subcategory.subcategory')
            ->orderBy('looker_data.client_name')
            ->orderBy('looker_data.client_name', 'asc')
            ->get();
        $finalArray = [];
        foreach ($dataset as $key => $value) {
            if ($value->subcategory_id == null) {
                $finalArray[null][] = $value;
            }
            else {
                $finalArray[$value->subcategory][] = $value;
            }

        }
        return response()->json(['status' => 'Success', 'data' => $finalArray]);
    }
    public function getUserAccessClients()
    {
        $dataset = \DB::table('grp_role_usr_mapping')
            ->select('looker_data.client_primary_id', 'looker_data.client_id', 'looker_data.client_name', 'looker_data.category_id', 'client_category.category', 'looker_data.subcategory_id', 'client_subcategory.subcategory')
            ->join('users_dasboards_mapping', 'grp_role_usr_mapping.grp_usr_mapping_id', '=', 'users_dasboards_mapping.grp_usr_mapping_id')
            ->join('looker_data', 'users_dasboards_mapping.client_primary_id', '=', 'looker_data.client_primary_id')
            ->join('client_category', 'looker_data.category_id', '=', 'client_category.category_id')
            ->leftJoin('client_subcategory', 'looker_data.subcategory_id', '=', 'client_subcategory.subcategory_id')
            ->where('grp_role_usr_mapping.is_active', 1)
            ->where('grp_role_usr_mapping.user_id', auth()->user()->id)
            ->groupBy('looker_data.client_primary_id', 'looker_data.client_id', 'looker_data.client_name', 'looker_data.category_id', 'client_category.category', 'looker_data.subcategory_id', 'client_subcategory.subcategory')
            ->orderBy('looker_data.client_name', 'asc')
            ->get();
        return response()->json(['status' => 'Success', 'data' => $dataset]);
    }
    public function getUserAccessClientsNew()
    {
        $dataset = \DB::table('grp_role_usr_mapping')
            ->select('looker_data.client_primary_id', 'looker_data.client_id', 'looker_data.client_name', 'looker_data.subcategory_id', 'client_subcategory.subcategory')
            ->join('users_dasboards_mapping', 'grp_role_usr_mapping.grp_usr_mapping_id', '=', 'users_dasboards_mapping.grp_usr_mapping_id')
            ->join('looker_data', 'users_dasboards_mapping.client_primary_id', '=', 'looker_data.client_primary_id')
            ->leftJoin('client_subcategory', 'looker_data.subcategory_id', '=', 'client_subcategory.subcategory_id')
            ->where('grp_role_usr_mapping.is_active', 1)
            ->where('grp_role_usr_mapping.user_id', auth()->user()->id)
            ->groupBy('looker_data.client_primary_id', 'looker_data.client_id', 'looker_data.client_name', 'looker_data.subcategory_id', 'client_subcategory.subcategory')
            ->orderBy('looker_data.client_name', 'asc')
            ->get();
        return response()->json(['status' => 'Success', 'data' => $dataset]);
    }
    public function getUserAccessFolders()
    {
        $dataset = \DB::table('grp_role_usr_mapping')
            ->select('looker_data.client_id', 'looker_data.client_name', 'looker_data.folder_id', 'looker_data.folder_name')
            ->join('users_dasboards_mapping', 'grp_role_usr_mapping.grp_usr_mapping_id', '=', 'users_dasboards_mapping.grp_usr_mapping_id')
            ->join('looker_data', 'users_dasboards_mapping.dashboard_id', '=', 'looker_data.folder_id')
            ->where('grp_role_usr_mapping.is_active', 1)
            ->where('grp_role_usr_mapping.user_id', auth()->user()->id)
            ->groupBy('looker_data.client_id', 'looker_data.client_name', 'looker_data.folder_id', 'looker_data.folder_name')
            ->orderBy('looker_data.client_name', 'asc')
            ->get();
        $result = [];
        foreach ($dataset as $key => $value) {
            if ($value->folder_name != "") {
                $folder_name = explode(".", $value->folder_name);
                if (isset($folder_name[1])) {
                    $result[$value->client_id][$value->folder_id]['folder_id'] = $value->folder_id;
                    $result[$value->client_id][$value->folder_id]['folder_name'] = trim($folder_name[1]);
                }
                else {
                    $result[$value->client_id][$value->folder_id]['folder_id'] = $value->folder_id;
                    $result[$value->client_id][$value->folder_id]['folder_name'] = trim($folder_name[0]);
                }
            }
            else {
                $result[$value->client_id][$value->folder_id]['folder_id'] = $value->folder_id;
                $result[$value->client_id][$value->folder_id]['folder_name'] = "";
            }
        }
        return response()->json(['status' => 'Success', 'data' => $result]);
    }
    public function getUserAccessdashboards()
    {
        $dataset = \DB::table('grp_role_usr_mapping')
            ->select('looker_data.client_id', 'looker_data.client_name', 'looker_data.folder_id', 'looker_data.folder_name', 'looker_data.dash_id', 'looker_data.title')
            ->join('users_dasboards_mapping', 'grp_role_usr_mapping.grp_usr_mapping_id', '=', 'users_dasboards_mapping.grp_usr_mapping_id')
            ->join('looker_data', 'users_dasboards_mapping.sub_dashboard_id', '=', 'looker_data.dash_id')
            ->where('grp_role_usr_mapping.is_active', 1)
            ->where('grp_role_usr_mapping.user_id', auth()->user()->id)
            ->groupBy('looker_data.client_id', 'looker_data.client_name', 'looker_data.folder_id', 'looker_data.folder_name', 'looker_data.dash_id', 'looker_data.title')
            ->orderBy('looker_data.client_name', 'asc')
            ->get();
        $result = [];
        foreach ($dataset as $key => $value) {
            $result[$value->client_id][$value->folder_id][$value->dash_id]['dash_id'] = $value->dash_id;
            $result[$value->client_id][$value->folder_id][$value->dash_id]['title'] = $value->title;
        }
        return response()->json(['status' => 'Success', 'data' => $result]);
    }
    public function getDashboard(Request $request)
    {
        $dashboard_id = $request->dashboard_id;
        $client_id = $request->client_id;

        // Load settings from our database (same table: looker)
        // We assume the settings record has been updated with Looker Studio Pro details
        $lookerSetting = Looker::find('1');

        // Base URL for Looker Studio embedding
        $host = $lookerSetting->host ?? 'lookerstudio.google.com';
        $baseUrl = "https://" . $host . "/embed/reporting/";

        // 1. Handle "Default Dashboard" (flag == 1)
        if ($request->flag == 1) {
            // Fetch the first available dashboard for this client from our local mapping
            $defaultDash = \DB::table('looker_data')
                ->where('client_id', $client_id)
                ->first();

            if ($defaultDash) {
                $dashboard_id = $defaultDash->dash_id;
            }
        }

        if (!$dashboard_id) {
            return response()->json(['Status' => 401, 'Error' => 'Dashboard Not Found']);
        }

        // 2. Construct the Base Embed URL
        // format: https://lookerstudio.google.com/embed/reporting/<DASHBOARD_ID>
        $final = $baseUrl . $dashboard_id;

        // 3. User & Client Filtering (Row Level Security via Parameters)
        // Looker Studio Pro allows passing parameters via the 'params' query string.
        // Requires 'Allow report viewers to change parameter values in the URL' to be enabled in Report Settings.
        $params = [
            "ds0.client_id" => (string)$client_id,
            "ds0.user_name" => auth()->user()->name,
            "ds0.user_id" => (string)auth()->user()->id
        ];

        // Append the Google Client ID if available in settings
        if ($lookerSetting->client_id) {
        // In some Pro configurations, the client_id is used for authorized domain verification
        // though it is primarily a cloud console setting.
        }

        // Final URL construction with URL-encoded JSON parameters
        if (!empty($params)) {
            $final .= "?params=" . urlencode(json_encode((object)$params));
            $final .= "&showVizHeader=true&header=true&nav=full";
        }
        else {
            $final .= "?showVizHeader=true&header=true&nav=full";
        }

        return response()->json([
            'success' => 'Data is successfully fetched',
            'url' => $final,
            'LicenceMessage' => "" // License sharing logic is no longer required for Looker Studio Pro
        ]);
    }
    public function getFolderDtl($folder_id)
    {
        $FolderDtl = \DB::table('client_folder_mapping')
            ->select('*')
            ->where('folder_id', '=', $folder_id)
            ->get();

        return $FolderDtl;
    }
    public function getLookerFolderStructure()
    {
        try {
            $LookerStructure = Looker_parent_dashboards::select('*')->get()->toArray();
            return response()->json(['Code' => 200, 'Status' => "Success", 'Message' => 'Client Folder Dashboard List.', 'Response' => $LookerStructure]);
        }
        catch (\Exception $e) {
            return response()->json(['Status' => 401, 'Error' => 'Failed', 'Message' => $e->getMessage()]);
        }
    }
    public function getParentPHM()
    {
        try {
            $PHM = Looker_parent_phm::select('*')->get()->toArray();
            return response()->json(['Code' => 200, 'Status' => "Success", 'Message' => 'Parent PHM List.', 'Response' => $PHM]);
        }
        catch (\Exception $e) {
            return response()->json(['Status' => 401, 'Error' => 'Failed', 'Message' => $e->getMessage()]);
        }
    }
    public function getSchema()
    {
        try {
            $schemas = Snowflake_schema::select('*')->get();
            return response()->json(['Code' => 200, 'Status' => "Success", 'Message' => 'Schema List.', 'Response' => $schemas]);
        }
        catch (\Exception $e) {
            return response()->json(['Status' => 401, 'Error' => 'Failed', 'Message' => $e->getMessage()]);
        }
    }
    public function getLookerGroups()
    {
        try {
            $lookerSetting = Looker::find('1');
            $api_url = $lookerSetting->api_url;
            $client_id = $lookerSetting->client_id;
            $client_secret = $lookerSetting->client_secret;

            $url = $api_url . "login?client_id=" . $client_id . "&client_secret=" . $client_secret;
            $method = "POST";
            $resp = $this->curlCall($url, $method);
            $responseData = json_decode($resp, true);
            //echo $responseData['access_token'];

            //call to lookers folder api
            $query = array('access_token' => $responseData['access_token']);
            //$url1 = "https://dynpro.cloud.looker.com:443/api/3.1/groups";
            $url2 = $api_url . "groups";
            $method2 = "GET";
            $groups = $this->curlCall($url2, $method2, $query);
            $groups = json_decode($groups, true);

            $groupData = array();
            $folderDataArr = array();
            foreach ($groups as $group) {
                $groupData['id'] = $group['id'];
                $groupData['name'] = $group['name'];
                $groupDataArr[] = $groupData;
            }
            return response()->json(['Code' => 200, 'Status' => "Success", 'Message' => 'Looker Group List.', 'Response' => $groupDataArr]);
        }
        catch (\Exception $e) {
            return response()->json(['Status' => 401, 'Error' => 'Failed', 'Message' => $e->getMessage()]);
        }
    }
    public function curlCall($url, $method, $query = null)
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
            // CURLOPT_HTTPHEADER => array(
            //  "Content-Type: application/x-www-form-urlencoded"
            // ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);

        //echo $responseData['access_token'];
        return $response;
    }
}