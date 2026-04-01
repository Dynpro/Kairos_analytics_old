<?php
require_once __DIR__.'/bootstrap/app.php';
use Illuminate\Support\Facades\DB;
try {
    echo "Cleaning existing mappings...\n";
    DB::statement('TRUNCATE TABLE looker_data');
    DB::statement('TRUNCATE TABLE users_dasboards_mapping');

    $client_primary_id = 1;
    $client_id = 1;
    $client_name = 'Demo';
    $category_id = 1;
    $subcategory_id = 1;
    $grp_usr_mapping_id = 1;

    DB::table('looker_data')->insert([
        'client_primary_id' => $client_primary_id,
        'client_id' => $client_id,
        'client_name' => '$client_name',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 1,
        'folder_name' => 'Care Coordination (Evidence-Based Rules)',
        'dash_id' => 'f141a4d4-4ec4-4334-b4b3-4fc8257595a2',
        'title' => 'Additional Risk Factors (Member-Level Analysis)'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => $grp_usr_mapping_id,
        'client_primary_id' => $client_primary_id,
        'dashboard_id' => 1,
        'sub_dashboard_id' => 'f141a4d4-4ec4-4334-b4b3-4fc8257595a2',
        'looker_dash_id' => 'f141a4d4-4ec4-4334-b4b3-4fc8257595a2'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => $client_primary_id,
        'client_id' => $client_id,
        'client_name' => '$client_name',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 1,
        'folder_name' => 'Care Coordination (Evidence-Based Rules)',
        'dash_id' => '',
        'title' => 'Cancer - Preventive Screening Compliance'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => $grp_usr_mapping_id,
        'client_primary_id' => $client_primary_id,
        'dashboard_id' => 1,
        'sub_dashboard_id' => '',
        'looker_dash_id' => ''
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => $client_primary_id,
        'client_id' => $client_id,
        'client_name' => '$client_name',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 1,
        'folder_name' => 'Care Coordination (Evidence-Based Rules)',
        'dash_id' => '3fe2bdf3-c121-4e19-9f36-dfdaf1648831/page/LodrF',
        'title' => 'Diabetes - Evidence-Based Rules Compliance'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => $grp_usr_mapping_id,
        'client_primary_id' => $client_primary_id,
        'dashboard_id' => 1,
        'sub_dashboard_id' => '3fe2bdf3-c121-4e19-9f36-dfdaf1648831/page/LodrF',
        'looker_dash_id' => '3fe2bdf3-c121-4e19-9f36-dfdaf1648831/page/LodrF'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => $client_primary_id,
        'client_id' => $client_id,
        'client_name' => '$client_name',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 1,
        'folder_name' => 'Care Coordination (Evidence-Based Rules)',
        'dash_id' => '',
        'title' => 'Evidence-Based Rules Compliance (Member-Level Analysis)'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => $grp_usr_mapping_id,
        'client_primary_id' => $client_primary_id,
        'dashboard_id' => 1,
        'sub_dashboard_id' => '',
        'looker_dash_id' => ''
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => $client_primary_id,
        'client_id' => $client_id,
        'client_name' => '$client_name',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 1,
        'folder_name' => 'Care Coordination (Evidence-Based Rules)',
        'dash_id' => '89b7f365-574a-4088-a02c-031abfa9afa6',
        'title' => 'Evidence-Based Rules Compliance Summary'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => $grp_usr_mapping_id,
        'client_primary_id' => $client_primary_id,
        'dashboard_id' => 1,
        'sub_dashboard_id' => '89b7f365-574a-4088-a02c-031abfa9afa6',
        'looker_dash_id' => '89b7f365-574a-4088-a02c-031abfa9afa6'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => $client_primary_id,
        'client_id' => $client_id,
        'client_name' => '$client_name',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 1,
        'folder_name' => 'Care Coordination (Evidence-Based Rules)',
        'dash_id' => 'de145d0a-0171-403b-8909-634ef2a4b294',
        'title' => 'Overall Compliance to Evidence-Based Rules - Percentage'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => $grp_usr_mapping_id,
        'client_primary_id' => $client_primary_id,
        'dashboard_id' => 1,
        'sub_dashboard_id' => 'de145d0a-0171-403b-8909-634ef2a4b294',
        'looker_dash_id' => 'de145d0a-0171-403b-8909-634ef2a4b294'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => $client_primary_id,
        'client_id' => $client_id,
        'client_name' => '$client_name',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 1,
        'folder_name' => 'Care Coordination (Evidence-Based Rules)',
        'dash_id' => 'abf634b7-6787-4cda-bd1c-399d7c91380e',
        'title' => 'Preventive Screening Compliance (Member-Level Analysis)'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => $grp_usr_mapping_id,
        'client_primary_id' => $client_primary_id,
        'dashboard_id' => 1,
        'sub_dashboard_id' => 'abf634b7-6787-4cda-bd1c-399d7c91380e',
        'looker_dash_id' => 'abf634b7-6787-4cda-bd1c-399d7c91380e'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => $client_primary_id,
        'client_id' => $client_id,
        'client_name' => '$client_name',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 1,
        'folder_name' => 'Care Coordination (Evidence-Based Rules)',
        'dash_id' => '',
        'title' => 'Evidence-Based Rules Compliance Statistical Analysis'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => $grp_usr_mapping_id,
        'client_primary_id' => $client_primary_id,
        'dashboard_id' => 1,
        'sub_dashboard_id' => '',
        'looker_dash_id' => ''
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => $client_primary_id,
        'client_id' => $client_id,
        'client_name' => '$client_name',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 2,
        'folder_name' => 'Chronic Condition Reports',
        'dash_id' => '',
        'title' => 'Diabetes - Demographic & Economic Insights'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => $grp_usr_mapping_id,
        'client_primary_id' => $client_primary_id,
        'dashboard_id' => 2,
        'sub_dashboard_id' => '',
        'looker_dash_id' => ''
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => $client_primary_id,
        'client_id' => $client_id,
        'client_name' => '$client_name',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 2,
        'folder_name' => 'Chronic Condition Reports',
        'dash_id' => '',
        'title' => 'Diabetes - Medical & Pharmacy Claims Summary'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => $grp_usr_mapping_id,
        'client_primary_id' => $client_primary_id,
        'dashboard_id' => 2,
        'sub_dashboard_id' => '',
        'looker_dash_id' => ''
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => $client_primary_id,
        'client_id' => $client_id,
        'client_name' => '$client_name',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 2,
        'folder_name' => 'Chronic Condition Reports',
        'dash_id' => '',
        'title' => 'Heart Disease - Demographic & Economic Insights'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => $grp_usr_mapping_id,
        'client_primary_id' => $client_primary_id,
        'dashboard_id' => 2,
        'sub_dashboard_id' => '',
        'looker_dash_id' => ''
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => $client_primary_id,
        'client_id' => $client_id,
        'client_name' => '$client_name',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 2,
        'folder_name' => 'Chronic Condition Reports',
        'dash_id' => '',
        'title' => 'Heart Disease - Medical & Pharmacy Claims Summary'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => $grp_usr_mapping_id,
        'client_primary_id' => $client_primary_id,
        'dashboard_id' => 2,
        'sub_dashboard_id' => '',
        'looker_dash_id' => ''
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => $client_primary_id,
        'client_id' => $client_id,
        'client_name' => '$client_name',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 2,
        'folder_name' => 'Chronic Condition Reports',
        'dash_id' => '',
        'title' => 'Hypertension - Demographic & Economic Insights'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => $grp_usr_mapping_id,
        'client_primary_id' => $client_primary_id,
        'dashboard_id' => 2,
        'sub_dashboard_id' => '',
        'looker_dash_id' => ''
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => $client_primary_id,
        'client_id' => $client_id,
        'client_name' => '$client_name',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 2,
        'folder_name' => 'Chronic Condition Reports',
        'dash_id' => '',
        'title' => 'Hypertension - Medical & Pharmacy Claims Summary'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => $grp_usr_mapping_id,
        'client_primary_id' => $client_primary_id,
        'dashboard_id' => 2,
        'sub_dashboard_id' => '',
        'looker_dash_id' => ''
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => $client_primary_id,
        'client_id' => $client_id,
        'client_name' => '$client_name',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 3,
        'folder_name' => 'Client Services',
        'dash_id' => 'a42d2340-3018-4a5e-9155-6e29af389bf6',
        'title' => 'Chronic Conditions Summary'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => $grp_usr_mapping_id,
        'client_primary_id' => $client_primary_id,
        'dashboard_id' => 3,
        'sub_dashboard_id' => 'a42d2340-3018-4a5e-9155-6e29af389bf6',
        'looker_dash_id' => 'a42d2340-3018-4a5e-9155-6e29af389bf6'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => $client_primary_id,
        'client_id' => $client_id,
        'client_name' => '$client_name',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 3,
        'folder_name' => 'Client Services',
        'dash_id' => '',
        'title' => 'Claims Analysis Summary (Filter by Calendar Year)'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => $grp_usr_mapping_id,
        'client_primary_id' => $client_primary_id,
        'dashboard_id' => 3,
        'sub_dashboard_id' => '',
        'looker_dash_id' => ''
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => $client_primary_id,
        'client_id' => $client_id,
        'client_name' => '$client_name',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 3,
        'folder_name' => 'Client Services',
        'dash_id' => '',
        'title' => 'Claims Analysis Summary (Filter by Plan Year)'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => $grp_usr_mapping_id,
        'client_primary_id' => $client_primary_id,
        'dashboard_id' => 3,
        'sub_dashboard_id' => '',
        'looker_dash_id' => ''
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => $client_primary_id,
        'client_id' => $client_id,
        'client_name' => '$client_name',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 3,
        'folder_name' => 'Client Services',
        'dash_id' => 's/vljOiXT2Uhs',
        'title' => 'Demographic & Claims Summary'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => $grp_usr_mapping_id,
        'client_primary_id' => $client_primary_id,
        'dashboard_id' => 3,
        'sub_dashboard_id' => 's/vljOiXT2Uhs',
        'looker_dash_id' => 's/vljOiXT2Uhs'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => $client_primary_id,
        'client_id' => $client_id,
        'client_name' => '$client_name',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 3,
        'folder_name' => 'Client Services',
        'dash_id' => '',
        'title' => 'Eligibility Termination Report'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => $grp_usr_mapping_id,
        'client_primary_id' => $client_primary_id,
        'dashboard_id' => 3,
        'sub_dashboard_id' => '',
        'looker_dash_id' => ''
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => $client_primary_id,
        'client_id' => $client_id,
        'client_name' => '$client_name',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 3,
        'folder_name' => 'Client Services',
        'dash_id' => '03dbe03b-4a0e-470c-810f-0c87eddf9800',
        'title' => 'Members with Claims above Average Paid Amount'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => $grp_usr_mapping_id,
        'client_primary_id' => $client_primary_id,
        'dashboard_id' => 3,
        'sub_dashboard_id' => '03dbe03b-4a0e-470c-810f-0c87eddf9800',
        'looker_dash_id' => '03dbe03b-4a0e-470c-810f-0c87eddf9800'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => $client_primary_id,
        'client_id' => $client_id,
        'client_name' => '$client_name',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 3,
        'folder_name' => 'Client Services',
        'dash_id' => '54ad8fd2-2aeb-44a5-baef-2f0d8c5e8083',
        'title' => 'Monthly Report - Member Data'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => $grp_usr_mapping_id,
        'client_primary_id' => $client_primary_id,
        'dashboard_id' => 3,
        'sub_dashboard_id' => '54ad8fd2-2aeb-44a5-baef-2f0d8c5e8083',
        'looker_dash_id' => '54ad8fd2-2aeb-44a5-baef-2f0d8c5e8083'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => $client_primary_id,
        'client_id' => $client_id,
        'client_name' => '$client_name',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 3,
        'folder_name' => 'Client Services',
        'dash_id' => 'e8cc9825-ac8e-4be6-95eb-1c0715cb7a09',
        'title' => 'Monthly Report - Summary'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => $grp_usr_mapping_id,
        'client_primary_id' => $client_primary_id,
        'dashboard_id' => 3,
        'sub_dashboard_id' => 'e8cc9825-ac8e-4be6-95eb-1c0715cb7a09',
        'looker_dash_id' => 'e8cc9825-ac8e-4be6-95eb-1c0715cb7a09'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => $client_primary_id,
        'client_id' => $client_id,
        'client_name' => '$client_name',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 3,
        'folder_name' => 'Client Services',
        'dash_id' => '',
        'title' => 'Overall Population Demographic Summary'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => $grp_usr_mapping_id,
        'client_primary_id' => $client_primary_id,
        'dashboard_id' => 3,
        'sub_dashboard_id' => '',
        'looker_dash_id' => ''
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => $client_primary_id,
        'client_id' => $client_id,
        'client_name' => '$client_name',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 3,
        'folder_name' => 'Client Services',
        'dash_id' => '',
        'title' => 'Participant vs. Non-Participant Claims'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => $grp_usr_mapping_id,
        'client_primary_id' => $client_primary_id,
        'dashboard_id' => 3,
        'sub_dashboard_id' => '',
        'looker_dash_id' => ''
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => $client_primary_id,
        'client_id' => $client_id,
        'client_name' => '$client_name',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 3,
        'folder_name' => 'Client Services',
        'dash_id' => 'e06fa944-f9c8-43f6-a074-d578b4262926',
        'title' => 'Quarterly Report'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => $grp_usr_mapping_id,
        'client_primary_id' => $client_primary_id,
        'dashboard_id' => 3,
        'sub_dashboard_id' => 'e06fa944-f9c8-43f6-a074-d578b4262926',
        'looker_dash_id' => 'e06fa944-f9c8-43f6-a074-d578b4262926'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => $client_primary_id,
        'client_id' => $client_id,
        'client_name' => '$client_name',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 3,
        'folder_name' => 'Client Services',
        'dash_id' => '36cf12bb-c377-4743-af9f-5115e699ab25/page/W2ErF',
        'title' => 'Referral List (All Members)'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => $grp_usr_mapping_id,
        'client_primary_id' => $client_primary_id,
        'dashboard_id' => 3,
        'sub_dashboard_id' => '36cf12bb-c377-4743-af9f-5115e699ab25/page/W2ErF',
        'looker_dash_id' => '36cf12bb-c377-4743-af9f-5115e699ab25/page/W2ErF'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => $client_primary_id,
        'client_id' => $client_id,
        'client_name' => '$client_name',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 3,
        'folder_name' => 'Client Services',
        'dash_id' => '',
        'title' => 'Referral List (New Eligible Members)'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => $grp_usr_mapping_id,
        'client_primary_id' => $client_primary_id,
        'dashboard_id' => 3,
        'sub_dashboard_id' => '',
        'looker_dash_id' => ''
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => $client_primary_id,
        'client_id' => $client_id,
        'client_name' => '$client_name',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 4,
        'folder_name' => 'Clinical',
        'dash_id' => '',
        'title' => 'At-Risk Alerts (Member-Level Data)'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => $grp_usr_mapping_id,
        'client_primary_id' => $client_primary_id,
        'dashboard_id' => 4,
        'sub_dashboard_id' => '',
        'looker_dash_id' => ''
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => $client_primary_id,
        'client_id' => $client_id,
        'client_name' => '$client_name',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 4,
        'folder_name' => 'Clinical',
        'dash_id' => '',
        'title' => 'At-Risk Alerts Summary'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => $grp_usr_mapping_id,
        'client_primary_id' => $client_primary_id,
        'dashboard_id' => 4,
        'sub_dashboard_id' => '',
        'looker_dash_id' => ''
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => $client_primary_id,
        'client_id' => $client_id,
        'client_name' => '$client_name',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 4,
        'folder_name' => 'Clinical',
        'dash_id' => '',
        'title' => 'Biometric Averages'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => $grp_usr_mapping_id,
        'client_primary_id' => $client_primary_id,
        'dashboard_id' => 4,
        'sub_dashboard_id' => '',
        'looker_dash_id' => ''
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => $client_primary_id,
        'client_id' => $client_id,
        'client_name' => '$client_name',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 4,
        'folder_name' => 'Clinical',
        'dash_id' => 'a42d2340-3018-4a5e-9155-6e29af389bf6',
        'title' => 'Chronic Conditions Summary'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => $grp_usr_mapping_id,
        'client_primary_id' => $client_primary_id,
        'dashboard_id' => 4,
        'sub_dashboard_id' => 'a42d2340-3018-4a5e-9155-6e29af389bf6',
        'looker_dash_id' => 'a42d2340-3018-4a5e-9155-6e29af389bf6'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => $client_primary_id,
        'client_id' => $client_id,
        'client_name' => '$client_name',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 4,
        'folder_name' => 'Clinical',
        'dash_id' => '',
        'title' => 'Coach Member Summary'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => $grp_usr_mapping_id,
        'client_primary_id' => $client_primary_id,
        'dashboard_id' => 4,
        'sub_dashboard_id' => '',
        'looker_dash_id' => ''
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => $client_primary_id,
        'client_id' => $client_id,
        'client_name' => '$client_name',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 4,
        'folder_name' => 'Clinical',
        'dash_id' => '',
        'title' => 'Daily Red Zone Alerts'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => $grp_usr_mapping_id,
        'client_primary_id' => $client_primary_id,
        'dashboard_id' => 4,
        'sub_dashboard_id' => '',
        'looker_dash_id' => ''
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => $client_primary_id,
        'client_id' => $client_id,
        'client_name' => '$client_name',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 4,
        'folder_name' => 'Clinical',
        'dash_id' => '3fe2bdf3-c121-4e19-9f36-dfdaf1648831/page/LodrF',
        'title' => 'Diabetes - Evidence-Based Rules Compliance'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => $grp_usr_mapping_id,
        'client_primary_id' => $client_primary_id,
        'dashboard_id' => 4,
        'sub_dashboard_id' => '3fe2bdf3-c121-4e19-9f36-dfdaf1648831/page/LodrF',
        'looker_dash_id' => '3fe2bdf3-c121-4e19-9f36-dfdaf1648831/page/LodrF'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => $client_primary_id,
        'client_id' => $client_id,
        'client_name' => '$client_name',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 4,
        'folder_name' => 'Clinical',
        'dash_id' => '',
        'title' => 'Heart Disease - Demographic & Economic Insights'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => $grp_usr_mapping_id,
        'client_primary_id' => $client_primary_id,
        'dashboard_id' => 4,
        'sub_dashboard_id' => '',
        'looker_dash_id' => ''
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => $client_primary_id,
        'client_id' => $client_id,
        'client_name' => '$client_name',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 4,
        'folder_name' => 'Clinical',
        'dash_id' => '',
        'title' => 'Hypertension - Demographic & Economic Insights'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => $grp_usr_mapping_id,
        'client_primary_id' => $client_primary_id,
        'dashboard_id' => 4,
        'sub_dashboard_id' => '',
        'looker_dash_id' => ''
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => $client_primary_id,
        'client_id' => $client_id,
        'client_name' => '$client_name',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 4,
        'folder_name' => 'Clinical',
        'dash_id' => '7ea34041-4e3e-473f-afb8-e1ddca019689',
        'title' => 'Member Data Summary (Filter by Calendar Year)'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => $grp_usr_mapping_id,
        'client_primary_id' => $client_primary_id,
        'dashboard_id' => 4,
        'sub_dashboard_id' => '7ea34041-4e3e-473f-afb8-e1ddca019689',
        'looker_dash_id' => '7ea34041-4e3e-473f-afb8-e1ddca019689'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => $client_primary_id,
        'client_id' => $client_id,
        'client_name' => '$client_name',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 4,
        'folder_name' => 'Clinical',
        'dash_id' => '',
        'title' => 'Member Data Summary (Filter by Plan Year)'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => $grp_usr_mapping_id,
        'client_primary_id' => $client_primary_id,
        'dashboard_id' => 4,
        'sub_dashboard_id' => '',
        'looker_dash_id' => ''
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => $client_primary_id,
        'client_id' => $client_id,
        'client_name' => '$client_name',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 4,
        'folder_name' => 'Clinical',
        'dash_id' => '',
        'title' => 'Member Summary (Additional Details)'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => $grp_usr_mapping_id,
        'client_primary_id' => $client_primary_id,
        'dashboard_id' => 4,
        'sub_dashboard_id' => '',
        'looker_dash_id' => ''
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => $client_primary_id,
        'client_id' => $client_id,
        'client_name' => '$client_name',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 4,
        'folder_name' => 'Clinical',
        'dash_id' => '',
        'title' => 'Program Migration'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => $grp_usr_mapping_id,
        'client_primary_id' => $client_primary_id,
        'dashboard_id' => 4,
        'sub_dashboard_id' => '',
        'looker_dash_id' => ''
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => $client_primary_id,
        'client_id' => $client_id,
        'client_name' => '$client_name',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 4,
        'folder_name' => 'Clinical',
        'dash_id' => '',
        'title' => 'Risk Groups Migration (Detailed Analysis)'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => $grp_usr_mapping_id,
        'client_primary_id' => $client_primary_id,
        'dashboard_id' => 4,
        'sub_dashboard_id' => '',
        'looker_dash_id' => ''
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => $client_primary_id,
        'client_id' => $client_id,
        'client_name' => '$client_name',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 5,
        'folder_name' => 'Cohort Analysis',
        'dash_id' => '',
        'title' => 'Cohort Analysis (Compare 2 Groups)'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => $grp_usr_mapping_id,
        'client_primary_id' => $client_primary_id,
        'dashboard_id' => 5,
        'sub_dashboard_id' => '',
        'looker_dash_id' => ''
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => $client_primary_id,
        'client_id' => $client_id,
        'client_name' => '$client_name',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 6,
        'folder_name' => 'Executive Summary Report',
        'dash_id' => '',
        'title' => 'Executive Summary Report'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => $grp_usr_mapping_id,
        'client_primary_id' => $client_primary_id,
        'dashboard_id' => 6,
        'sub_dashboard_id' => '',
        'looker_dash_id' => ''
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => $client_primary_id,
        'client_id' => $client_id,
        'client_name' => '$client_name',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 6,
        'folder_name' => 'Executive Summary Report',
        'dash_id' => '',
        'title' => 'Executive Summary Report NEW'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => $grp_usr_mapping_id,
        'client_primary_id' => $client_primary_id,
        'dashboard_id' => 6,
        'sub_dashboard_id' => '',
        'looker_dash_id' => ''
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => $client_primary_id,
        'client_id' => $client_id,
        'client_name' => '$client_name',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 7,
        'folder_name' => 'Health Score & Predictive Reports',
        'dash_id' => '5883088f-5094-4e17-a019-70f361c6240b/page/4wvqF',
        'title' => 'Health Score & Risk Group Overview'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => $grp_usr_mapping_id,
        'client_primary_id' => $client_primary_id,
        'dashboard_id' => 7,
        'sub_dashboard_id' => '5883088f-5094-4e17-a019-70f361c6240b/page/4wvqF',
        'looker_dash_id' => '5883088f-5094-4e17-a019-70f361c6240b/page/4wvqF'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => $client_primary_id,
        'client_id' => $client_id,
        'client_name' => '$client_name',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 7,
        'folder_name' => 'Health Score & Predictive Reports',
        'dash_id' => '04fd13a5-ab37-4083-b5f4-3bb43eccc70e/page/hvrrF',
        'title' => 'Health Score Decile & Quartile Analysis'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => $grp_usr_mapping_id,
        'client_primary_id' => $client_primary_id,
        'dashboard_id' => 7,
        'sub_dashboard_id' => '04fd13a5-ab37-4083-b5f4-3bb43eccc70e/page/hvrrF',
        'looker_dash_id' => '04fd13a5-ab37-4083-b5f4-3bb43eccc70e/page/hvrrF'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => $client_primary_id,
        'client_id' => $client_id,
        'client_name' => '$client_name',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 7,
        'folder_name' => 'Health Score & Predictive Reports',
        'dash_id' => '0b258260-92b2-4698-9077-576d29548d28/page/eNoqF',
        'title' => 'Data Science Overview'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => $grp_usr_mapping_id,
        'client_primary_id' => $client_primary_id,
        'dashboard_id' => 7,
        'sub_dashboard_id' => '0b258260-92b2-4698-9077-576d29548d28/page/eNoqF',
        'looker_dash_id' => '0b258260-92b2-4698-9077-576d29548d28/page/eNoqF'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => $client_primary_id,
        'client_id' => $client_id,
        'client_name' => '$client_name',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 7,
        'folder_name' => 'Health Score & Predictive Reports',
        'dash_id' => '7f373b3f-99f6-4922-87e0-ef41c061f55b',
        'title' => 'Data Science Predictive Analysis (Overview & Member-Level Analysis)'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => $grp_usr_mapping_id,
        'client_primary_id' => $client_primary_id,
        'dashboard_id' => 7,
        'sub_dashboard_id' => '7f373b3f-99f6-4922-87e0-ef41c061f55b',
        'looker_dash_id' => '7f373b3f-99f6-4922-87e0-ef41c061f55b'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => $client_primary_id,
        'client_id' => $client_id,
        'client_name' => '$client_name',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 7,
        'folder_name' => 'Health Score & Predictive Reports',
        'dash_id' => '5404c4da-35ae-429d-b81a-00e941091686',
        'title' => 'Health Score Summary'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => $grp_usr_mapping_id,
        'client_primary_id' => $client_primary_id,
        'dashboard_id' => 7,
        'sub_dashboard_id' => '5404c4da-35ae-429d-b81a-00e941091686',
        'looker_dash_id' => '5404c4da-35ae-429d-b81a-00e941091686'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => $client_primary_id,
        'client_id' => $client_id,
        'client_name' => '$client_name',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 7,
        'folder_name' => 'Health Score & Predictive Reports',
        'dash_id' => 'e4940603-026a-4f36-87b0-fb6b8939ba1d/page/QmpqF',
        'title' => 'Health Score (Member-Level Analysis)'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => $grp_usr_mapping_id,
        'client_primary_id' => $client_primary_id,
        'dashboard_id' => 7,
        'sub_dashboard_id' => 'e4940603-026a-4f36-87b0-fb6b8939ba1d/page/QmpqF',
        'looker_dash_id' => 'e4940603-026a-4f36-87b0-fb6b8939ba1d/page/QmpqF'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => $client_primary_id,
        'client_id' => $client_id,
        'client_name' => '$client_name',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 8,
        'folder_name' => 'Medical Reports',
        'dash_id' => '9efc5ba7-ac10-45e6-ae80-36a46762efe7',
        'title' => 'Medical & Pharmacy Claims Summary'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => $grp_usr_mapping_id,
        'client_primary_id' => $client_primary_id,
        'dashboard_id' => 8,
        'sub_dashboard_id' => '9efc5ba7-ac10-45e6-ae80-36a46762efe7',
        'looker_dash_id' => '9efc5ba7-ac10-45e6-ae80-36a46762efe7'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => $client_primary_id,
        'client_id' => $client_id,
        'client_name' => '$client_name',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 8,
        'folder_name' => 'Medical Reports',
        'dash_id' => 's/jOhMvFShn1U',
        'title' => 'Preventive Claims Summary'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => $grp_usr_mapping_id,
        'client_primary_id' => $client_primary_id,
        'dashboard_id' => 8,
        'sub_dashboard_id' => 's/jOhMvFShn1U',
        'looker_dash_id' => 's/jOhMvFShn1U'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => $client_primary_id,
        'client_id' => $client_id,
        'client_name' => '$client_name',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 8,
        'folder_name' => 'Medical Reports',
        'dash_id' => 's/llWNFD-3p00',
        'title' => 'Medical summary - Care Coordination'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => $grp_usr_mapping_id,
        'client_primary_id' => $client_primary_id,
        'dashboard_id' => 8,
        'sub_dashboard_id' => 's/llWNFD-3p00',
        'looker_dash_id' => 's/llWNFD-3p00'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => $client_primary_id,
        'client_id' => $client_id,
        'client_name' => '$client_name',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 8,
        'folder_name' => 'Medical Reports',
        'dash_id' => 's/oMnntF6o9oc',
        'title' => 'Diagnostic Category Summary'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => $grp_usr_mapping_id,
        'client_primary_id' => $client_primary_id,
        'dashboard_id' => 8,
        'sub_dashboard_id' => 's/oMnntF6o9oc',
        'looker_dash_id' => 's/oMnntF6o9oc'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => $client_primary_id,
        'client_id' => $client_id,
        'client_name' => '$client_name',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 8,
        'folder_name' => 'Medical Reports',
        'dash_id' => 's/rvO2IoO1oA4',
        'title' => 'Lifestyle Modifiable & Preventive Summary'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => $grp_usr_mapping_id,
        'client_primary_id' => $client_primary_id,
        'dashboard_id' => 8,
        'sub_dashboard_id' => 's/rvO2IoO1oA4',
        'looker_dash_id' => 's/rvO2IoO1oA4'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => $client_primary_id,
        'client_id' => $client_id,
        'client_name' => '$client_name',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 8,
        'folder_name' => 'Medical Reports',
        'dash_id' => 'a42d2340-3018-4a5e-9155-6e29af389bf6',
        'title' => 'Chronic Conditions Summary'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => $grp_usr_mapping_id,
        'client_primary_id' => $client_primary_id,
        'dashboard_id' => 8,
        'sub_dashboard_id' => 'a42d2340-3018-4a5e-9155-6e29af389bf6',
        'looker_dash_id' => 'a42d2340-3018-4a5e-9155-6e29af389bf6'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => $client_primary_id,
        'client_id' => $client_id,
        'client_name' => '$client_name',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 8,
        'folder_name' => 'Medical Reports',
        'dash_id' => 's/vljOiXT2Uhs',
        'title' => 'Demographic & Claims Summary'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => $grp_usr_mapping_id,
        'client_primary_id' => $client_primary_id,
        'dashboard_id' => 8,
        'sub_dashboard_id' => 's/vljOiXT2Uhs',
        'looker_dash_id' => 's/vljOiXT2Uhs'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => $client_primary_id,
        'client_id' => $client_id,
        'client_name' => '$client_name',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 8,
        'folder_name' => 'Medical Reports',
        'dash_id' => '',
        'title' => 'Total Lost Days Summary'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => $grp_usr_mapping_id,
        'client_primary_id' => $client_primary_id,
        'dashboard_id' => 8,
        'sub_dashboard_id' => '',
        'looker_dash_id' => ''
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => $client_primary_id,
        'client_id' => $client_id,
        'client_name' => '$client_name',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 8,
        'folder_name' => 'Medical Reports',
        'dash_id' => '',
        'title' => 'Overall Population Demographic Summary'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => $grp_usr_mapping_id,
        'client_primary_id' => $client_primary_id,
        'dashboard_id' => 8,
        'sub_dashboard_id' => '',
        'looker_dash_id' => ''
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => $client_primary_id,
        'client_id' => $client_id,
        'client_name' => '$client_name',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 8,
        'folder_name' => 'Medical Reports',
        'dash_id' => '03dbe03b-4a0e-470c-810f-0c87eddf9800',
        'title' => 'Members with Claims above Average Paid Amount'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => $grp_usr_mapping_id,
        'client_primary_id' => $client_primary_id,
        'dashboard_id' => 8,
        'sub_dashboard_id' => '03dbe03b-4a0e-470c-810f-0c87eddf9800',
        'looker_dash_id' => '03dbe03b-4a0e-470c-810f-0c87eddf9800'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => $client_primary_id,
        'client_id' => $client_id,
        'client_name' => '$client_name',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 9,
        'folder_name' => 'MSK Reports',
        'dash_id' => 'dff3c5ac-56bd-4f23-bb29-83d63921acfc',
        'title' => 'Medical MSK - Overall Demographic & Economic Insights'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => $grp_usr_mapping_id,
        'client_primary_id' => $client_primary_id,
        'dashboard_id' => 9,
        'sub_dashboard_id' => 'dff3c5ac-56bd-4f23-bb29-83d63921acfc',
        'looker_dash_id' => 'dff3c5ac-56bd-4f23-bb29-83d63921acfc'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => $client_primary_id,
        'client_id' => $client_id,
        'client_name' => '$client_name',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 9,
        'folder_name' => 'MSK Reports',
        'dash_id' => '',
        'title' => 'Medical MSK - Work Related Disorders'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => $grp_usr_mapping_id,
        'client_primary_id' => $client_primary_id,
        'dashboard_id' => 9,
        'sub_dashboard_id' => '',
        'looker_dash_id' => ''
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => $client_primary_id,
        'client_id' => $client_id,
        'client_name' => '$client_name',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 9,
        'folder_name' => 'MSK Reports',
        'dash_id' => 'b3b15958-6464-408d-9320-52d319afb219',
        'title' => 'Medical MSK - Provider Insights'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => $grp_usr_mapping_id,
        'client_primary_id' => $client_primary_id,
        'dashboard_id' => 9,
        'sub_dashboard_id' => 'b3b15958-6464-408d-9320-52d319afb219',
        'looker_dash_id' => 'b3b15958-6464-408d-9320-52d319afb219'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => $client_primary_id,
        'client_id' => $client_id,
        'client_name' => '$client_name',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 9,
        'folder_name' => 'MSK Reports',
        'dash_id' => '8a0957d3-a916-4fea-9abd-d1b9081b651d',
        'title' => 'MSK MED/PHARMA Analysis'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => $grp_usr_mapping_id,
        'client_primary_id' => $client_primary_id,
        'dashboard_id' => 9,
        'sub_dashboard_id' => '8a0957d3-a916-4fea-9abd-d1b9081b651d',
        'looker_dash_id' => '8a0957d3-a916-4fea-9abd-d1b9081b651d'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => $client_primary_id,
        'client_id' => $client_id,
        'client_name' => '$client_name',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 9,
        'folder_name' => 'MSK Reports',
        'dash_id' => '',
        'title' => 'Medical MSK - Productivity and Absenteeism Insights'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => $grp_usr_mapping_id,
        'client_primary_id' => $client_primary_id,
        'dashboard_id' => 9,
        'sub_dashboard_id' => '',
        'looker_dash_id' => ''
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => $client_primary_id,
        'client_id' => $client_id,
        'client_name' => '$client_name',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 9,
        'folder_name' => 'MSK Reports',
        'dash_id' => '',
        'title' => 'TRUE MSK Cost Analysis'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => $grp_usr_mapping_id,
        'client_primary_id' => $client_primary_id,
        'dashboard_id' => 9,
        'sub_dashboard_id' => '',
        'looker_dash_id' => ''
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => $client_primary_id,
        'client_id' => $client_id,
        'client_name' => '$client_name',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 9,
        'folder_name' => 'MSK Reports',
        'dash_id' => '',
        'title' => 'Hip ICD Codes Analysis'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => $grp_usr_mapping_id,
        'client_primary_id' => $client_primary_id,
        'dashboard_id' => 9,
        'sub_dashboard_id' => '',
        'looker_dash_id' => ''
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => $client_primary_id,
        'client_id' => $client_id,
        'client_name' => '$client_name',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 9,
        'folder_name' => 'MSK Reports',
        'dash_id' => '',
        'title' => 'Knee ICD Codes Analysis'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => $grp_usr_mapping_id,
        'client_primary_id' => $client_primary_id,
        'dashboard_id' => 9,
        'sub_dashboard_id' => '',
        'looker_dash_id' => ''
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => $client_primary_id,
        'client_id' => $client_id,
        'client_name' => '$client_name',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 9,
        'folder_name' => 'MSK Reports',
        'dash_id' => '',
        'title' => 'Shoulder ICD Codes Analysis'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => $grp_usr_mapping_id,
        'client_primary_id' => $client_primary_id,
        'dashboard_id' => 9,
        'sub_dashboard_id' => '',
        'looker_dash_id' => ''
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => $client_primary_id,
        'client_id' => $client_id,
        'client_name' => '$client_name',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 9,
        'folder_name' => 'MSK Reports',
        'dash_id' => '',
        'title' => 'Spine ICD Codes Analysis'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => $grp_usr_mapping_id,
        'client_primary_id' => $client_primary_id,
        'dashboard_id' => 9,
        'sub_dashboard_id' => '',
        'looker_dash_id' => ''
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => $client_primary_id,
        'client_id' => $client_id,
        'client_name' => '$client_name',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 9,
        'folder_name' => 'MSK Reports',
        'dash_id' => '47ed2672-1bf7-4d52-a96d-5766c73f98fb/page/p0yrF',
        'title' => 'MRS Modifiable ICD Codes - Overall Analysis'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => $grp_usr_mapping_id,
        'client_primary_id' => $client_primary_id,
        'dashboard_id' => 9,
        'sub_dashboard_id' => '47ed2672-1bf7-4d52-a96d-5766c73f98fb/page/p0yrF',
        'looker_dash_id' => '47ed2672-1bf7-4d52-a96d-5766c73f98fb/page/p0yrF'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => $client_primary_id,
        'client_id' => $client_id,
        'client_name' => '$client_name',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 10,
        'folder_name' => 'Operations',
        'dash_id' => 'cdd12ab1-e2bf-4cee-b09f-226307ad758d',
        'title' => 'Ad Hoc Query Tool'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => $grp_usr_mapping_id,
        'client_primary_id' => $client_primary_id,
        'dashboard_id' => 10,
        'sub_dashboard_id' => 'cdd12ab1-e2bf-4cee-b09f-226307ad758d',
        'looker_dash_id' => 'cdd12ab1-e2bf-4cee-b09f-226307ad758d'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => $client_primary_id,
        'client_id' => $client_id,
        'client_name' => '$client_name',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 10,
        'folder_name' => 'Operations',
        'dash_id' => '62db8241-d1cc-4558-8965-4f5eaaf0ac14',
        'title' => 'Ad Hoc Query Tool 2.0'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => $grp_usr_mapping_id,
        'client_primary_id' => $client_primary_id,
        'dashboard_id' => 10,
        'sub_dashboard_id' => '62db8241-d1cc-4558-8965-4f5eaaf0ac14',
        'looker_dash_id' => '62db8241-d1cc-4558-8965-4f5eaaf0ac14'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => $client_primary_id,
        'client_id' => $client_id,
        'client_name' => '$client_name',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 10,
        'folder_name' => 'Operations',
        'dash_id' => '',
        'title' => 'Hospital Visit Statistics'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => $grp_usr_mapping_id,
        'client_primary_id' => $client_primary_id,
        'dashboard_id' => 10,
        'sub_dashboard_id' => '',
        'looker_dash_id' => ''
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => $client_primary_id,
        'client_id' => $client_id,
        'client_name' => '$client_name',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 10,
        'folder_name' => 'Operations',
        'dash_id' => 's/jOspiuyB9ek',
        'title' => 'Eligibility History Report'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => $grp_usr_mapping_id,
        'client_primary_id' => $client_primary_id,
        'dashboard_id' => 10,
        'sub_dashboard_id' => 's/jOspiuyB9ek',
        'looker_dash_id' => 's/jOspiuyB9ek'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => $client_primary_id,
        'client_id' => $client_id,
        'client_name' => '$client_name',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 10,
        'folder_name' => 'Operations',
        'dash_id' => 'a3ba6b3a-00b9-4454-8909-829793bc6b8c',
        'title' => 'Referral Data - Demographic information'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => $grp_usr_mapping_id,
        'client_primary_id' => $client_primary_id,
        'dashboard_id' => 10,
        'sub_dashboard_id' => 'a3ba6b3a-00b9-4454-8909-829793bc6b8c',
        'looker_dash_id' => 'a3ba6b3a-00b9-4454-8909-829793bc6b8c'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => $client_primary_id,
        'client_id' => $client_id,
        'client_name' => '$client_name',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 10,
        'folder_name' => 'Operations',
        'dash_id' => '022cb0f0-5976-4fbe-8f6e-1baeec69a4b0',
        'title' => 'All Disease Variable Trend'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => $grp_usr_mapping_id,
        'client_primary_id' => $client_primary_id,
        'dashboard_id' => 10,
        'sub_dashboard_id' => '022cb0f0-5976-4fbe-8f6e-1baeec69a4b0',
        'looker_dash_id' => '022cb0f0-5976-4fbe-8f6e-1baeec69a4b0'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => $client_primary_id,
        'client_id' => $client_id,
        'client_name' => '$client_name',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 10,
        'folder_name' => 'Operations',
        'dash_id' => '03187d1b-f3fd-44de-96c4-7703d0703f01',
        'title' => 'Spend Analysis'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => $grp_usr_mapping_id,
        'client_primary_id' => $client_primary_id,
        'dashboard_id' => 10,
        'sub_dashboard_id' => '03187d1b-f3fd-44de-96c4-7703d0703f01',
        'looker_dash_id' => '03187d1b-f3fd-44de-96c4-7703d0703f01'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => $client_primary_id,
        'client_id' => $client_id,
        'client_name' => '$client_name',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 11,
        'folder_name' => 'Pharmacy Reports',
        'dash_id' => '4e13ea17-1c3c-4940-9b64-321d342faeb1',
        'title' => 'Medication Compliance Summary'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => $grp_usr_mapping_id,
        'client_primary_id' => $client_primary_id,
        'dashboard_id' => 11,
        'sub_dashboard_id' => '4e13ea17-1c3c-4940-9b64-321d342faeb1',
        'looker_dash_id' => '4e13ea17-1c3c-4940-9b64-321d342faeb1'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => $client_primary_id,
        'client_id' => $client_id,
        'client_name' => '$client_name',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 11,
        'folder_name' => 'Pharmacy Reports',
        'dash_id' => 'fc862a61-78d9-416b-a65a-ef462c419dce',
        'title' => 'Pharmacy Claims Overview'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => $grp_usr_mapping_id,
        'client_primary_id' => $client_primary_id,
        'dashboard_id' => 11,
        'sub_dashboard_id' => 'fc862a61-78d9-416b-a65a-ef462c419dce',
        'looker_dash_id' => 'fc862a61-78d9-416b-a65a-ef462c419dce'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => $client_primary_id,
        'client_id' => $client_id,
        'client_name' => '$client_name',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 11,
        'folder_name' => 'Pharmacy Reports',
        'dash_id' => '',
        'title' => 'Drug Class (Member-Level Analysis)'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => $grp_usr_mapping_id,
        'client_primary_id' => $client_primary_id,
        'dashboard_id' => 11,
        'sub_dashboard_id' => '',
        'looker_dash_id' => ''
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => $client_primary_id,
        'client_id' => $client_id,
        'client_name' => '$client_name',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 11,
        'folder_name' => 'Pharmacy Reports',
        'dash_id' => '',
        'title' => 'Drug Class Summary'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => $grp_usr_mapping_id,
        'client_primary_id' => $client_primary_id,
        'dashboard_id' => 11,
        'sub_dashboard_id' => '',
        'looker_dash_id' => ''
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => $client_primary_id,
        'client_id' => $client_id,
        'client_name' => '$client_name',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 11,
        'folder_name' => 'Pharmacy Reports',
        'dash_id' => '',
        'title' => 'Proportion of Days Covered (Member-Level Summary)'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => $grp_usr_mapping_id,
        'client_primary_id' => $client_primary_id,
        'dashboard_id' => 11,
        'sub_dashboard_id' => '',
        'looker_dash_id' => ''
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => $client_primary_id,
        'client_id' => $client_id,
        'client_name' => '$client_name',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 12,
        'folder_name' => 'Risk Groups',
        'dash_id' => '3ff58fa1-efed-44ba-a3c7-c64ee307ac76',
        'title' => 'Risk Groups Stratification Overview'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => $grp_usr_mapping_id,
        'client_primary_id' => $client_primary_id,
        'dashboard_id' => 12,
        'sub_dashboard_id' => '3ff58fa1-efed-44ba-a3c7-c64ee307ac76',
        'looker_dash_id' => '3ff58fa1-efed-44ba-a3c7-c64ee307ac76'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => $client_primary_id,
        'client_id' => $client_id,
        'client_name' => '$client_name',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 12,
        'folder_name' => 'Risk Groups',
        'dash_id' => '',
        'title' => 'Risk Groups (Member-Level Analysis)'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => $grp_usr_mapping_id,
        'client_primary_id' => $client_primary_id,
        'dashboard_id' => 12,
        'sub_dashboard_id' => '',
        'looker_dash_id' => ''
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => $client_primary_id,
        'client_id' => $client_id,
        'client_name' => '$client_name',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 12,
        'folder_name' => 'Risk Groups',
        'dash_id' => '',
        'title' => 'Risk Groups Migration (Detailed Analysis)'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => $grp_usr_mapping_id,
        'client_primary_id' => $client_primary_id,
        'dashboard_id' => 12,
        'sub_dashboard_id' => '',
        'looker_dash_id' => ''
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => $client_primary_id,
        'client_id' => $client_id,
        'client_name' => '$client_name',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 12,
        'folder_name' => 'Risk Groups',
        'dash_id' => '',
        'title' => 'Risk Groups Migration (Summary)'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => $grp_usr_mapping_id,
        'client_primary_id' => $client_primary_id,
        'dashboard_id' => 12,
        'sub_dashboard_id' => '',
        'looker_dash_id' => ''
    ]);
    echo "Done.\n";
} catch (\Exception $e) {
    echo "Error: " . $e. "\n";
}