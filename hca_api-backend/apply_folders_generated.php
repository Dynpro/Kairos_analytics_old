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
        'client_name' => 'Demo',
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
        'client_name' => 'Demo',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 1,
        'folder_name' => 'Care Coordination (Evidence-Based Rules)',
        'dash_id' => 'd97ff5be-233c-4c67-ac0a-ccb4b6a44f33',
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
        'client_name' => 'Demo',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 1,
        'folder_name' => 'Care Coordination (Evidence-Based Rules)',
        'dash_id' => '3fe2bdf3-c121-4e19-9f36-dfdaf1648831',
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
        'client_name' => 'Demo',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 1,
        'folder_name' => 'Care Coordination (Evidence-Based Rules)',
        'dash_id' => '5ad3029b-4cc1-49b4-b455-76632543b112/page/uKjrF',
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
        'client_name' => 'Demo',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 1,
        'folder_name' => 'Care Coordination (Evidence-Based Rules)',
        'dash_id' => '23558699-9a01-4645-b048-4f7a5815ecf0',
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
        'client_name' => 'Demo',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 1,
        'folder_name' => 'Care Coordination (Evidence-Based Rules)',
        'dash_id' => '0fb3cdff-6fee-4baf-a3fa-8178b009ad09',
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
        'client_name' => 'Demo',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 1,
        'folder_name' => 'Care Coordination (Evidence-Based Rules)',
        'dash_id' => '0fb3cdff-6fee-4baf-a3fa-8178b009ad09',
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
        'client_name' => 'Demo',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 1,
        'folder_name' => 'Care Coordination (Evidence-Based Rules)',
        'dash_id' => '6e0e34b2-0f08-4ca2-8a77-80fe0891ccb8',
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
        'client_name' => 'Demo',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 2,
        'folder_name' => 'Chronic Condition Reports',
        'dash_id' => 'cd86b02c-55a4-44d1-a635-804848a969ed',
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
        'client_name' => 'Demo',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 2,
        'folder_name' => 'Chronic Condition Reports',
        'dash_id' => 'bb19f877-be8d-46d5-8f0a-9972afe8151f',
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
        'client_name' => 'Demo',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 2,
        'folder_name' => 'Chronic Condition Reports',
        'dash_id' => '69ea3c08-c9d6-4931-9555-1f841b8d907a',
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
        'client_name' => 'Demo',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 2,
        'folder_name' => 'Chronic Condition Reports',
        'dash_id' => 'fc4e190a-1dc0-4911-8994-95b8d99dbe1e',
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
        'client_name' => 'Demo',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 2,
        'folder_name' => 'Chronic Condition Reports',
        'dash_id' => 'adbe15cb-290e-462f-ab97-2322c6a121ee',
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
        'client_name' => 'Demo',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 2,
        'folder_name' => 'Chronic Condition Reports',
        'dash_id' => 'c5c9ece6-34af-46cd-96f6-57080cc988e3/page/N26rF',
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
        'client_name' => 'Demo',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 3,
        'folder_name' => 'Client Services',
        'dash_id' => '6b044f85-1007-4818-b53a-f8fb56b61844',
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
        'client_name' => 'Demo',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 3,
        'folder_name' => 'Client Services',
        'dash_id' => '40a282cd-a638-4d9d-a118-1e8bb3177fcf/page/VxqrF',
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
        'client_name' => 'Demo',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 3,
        'folder_name' => 'Client Services',
        'dash_id' => '79d48e3b-2b09-4d39-a446-5ff85e4d6eed/page/DsorF',
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
        'client_name' => 'Demo',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 3,
        'folder_name' => 'Client Services',
        'dash_id' => '7c416038-6a2a-4871-bf0f-59dfb862f0ff',
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
        'client_name' => 'Demo',
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
        'client_name' => 'Demo',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 3,
        'folder_name' => 'Client Services',
        'dash_id' => 'dd47879a-9083-4ef7-a32a-40c098f026ec',
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
        'client_name' => 'Demo',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 3,
        'folder_name' => 'Client Services',
        'dash_id' => '54ad8fd2-2aeb-44a5-baef-2f0d8c5e8083/page/Se3qF',
        'title' => 'Monthly Report - Member Data'
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
        'client_name' => 'Demo',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 3,
        'folder_name' => 'Client Services',
        'dash_id' => 'e8cc9825-ac8e-4be6-95eb-1c0715cb7a09/page/WivqF',
        'title' => 'Monthly Report - Summary'
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
        'client_name' => 'Demo',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 3,
        'folder_name' => 'Client Services',
        'dash_id' => '0334398c-0f7d-4e3a-9481-1824b8d34ad7/page/HqCrF',
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
        'client_name' => 'Demo',
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
        'client_name' => 'Demo',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 3,
        'folder_name' => 'Client Services',
        'dash_id' => '4a99e41e-559d-4ecc-af28-4d20df8b081e',
        'title' => 'Quarterly Report'
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
        'client_name' => 'Demo',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 3,
        'folder_name' => 'Client Services',
        'dash_id' => 'e64461ae-904c-4858-9c46-96416cd74629/page/W2ErF',
        'title' => 'Referral List (All Members)'
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
        'client_name' => 'Demo',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 3,
        'folder_name' => 'Client Services',
        'dash_id' => '7267098f-c2c1-4d2e-a89a-eebb14bfdfba/page/OxYrF',
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
        'client_name' => 'Demo',
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
        'client_name' => 'Demo',
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
        'client_name' => 'Demo',
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
        'client_name' => 'Demo',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 4,
        'folder_name' => 'Clinical',
        'dash_id' => '6b044f85-1007-4818-b53a-f8fb56b61844',
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
        'client_name' => 'Demo',
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
        'client_name' => 'Demo',
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
        'client_name' => 'Demo',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 4,
        'folder_name' => 'Clinical',
        'dash_id' => '3fe2bdf3-c121-4e19-9f36-dfdaf1648831',
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
        'client_name' => 'Demo',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 4,
        'folder_name' => 'Clinical',
        'dash_id' => '69ea3c08-c9d6-4931-9555-1f841b8d907a',
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
        'client_name' => 'Demo',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 4,
        'folder_name' => 'Clinical',
        'dash_id' => 'adbe15cb-290e-462f-ab97-2322c6a121ee',
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
        'client_name' => 'Demo',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 4,
        'folder_name' => 'Clinical',
        'dash_id' => 's/pNyj_yFAdrk',
        'title' => 'Member Data Summary (Filter by Calendar Year)'
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
        'client_name' => 'Demo',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 4,
        'folder_name' => 'Clinical',
        'dash_id' => '73c06507-70a6-45bc-a7fa-17e467109d4b/page/xzYrF',
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
        'client_name' => 'Demo',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 4,
        'folder_name' => 'Clinical',
        'dash_id' => 'e5b4dcb1-b170-44e8-9ffd-a929cf3d2ee9/page/TRCrF',
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
        'client_name' => 'Demo',
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
        'client_name' => 'Demo',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 4,
        'folder_name' => 'Clinical',
        'dash_id' => '064898c2-6d58-4e31-8556-8befbd5f21e1/page/kIFrF',
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
        'client_name' => 'Demo',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 5,
        'folder_name' => 'Cohort Analysis',
        'dash_id' => 'c147daa4-623c-4ed2-9d0a-cdacdf5624a4',
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
        'client_name' => 'Demo',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 6,
        'folder_name' => 'Executive Summary Report',
        'dash_id' => '47063557-d4ce-4759-a35a-f3ad48e7fc3d/page/q2frF',
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
        'client_name' => 'Demo',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 6,
        'folder_name' => 'Executive Summary Report',
        'dash_id' => 'a48e31f8-1694-48cf-ad9d-e345b4088daf/page/q2frF',
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
        'client_name' => 'Demo',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 7,
        'folder_name' => 'Health Score & Predictive Reports',
        'dash_id' => '3a49c605-dbf8-41c1-b1dd-a85973ddfb37/page/4wvqF',
        'title' => 'Health Score & Risk Group Overview'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => $grp_usr_mapping_id,
        'client_primary_id' => $client_primary_id,
        'dashboard_id' => 7,
        'sub_dashboard_id' => '',
        'looker_dash_id' => ''
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => $client_primary_id,
        'client_id' => $client_id,
        'client_name' => 'Demo',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 7,
        'folder_name' => 'Health Score & Predictive Reports',
        'dash_id' => 'f0be7979-521b-43bb-b759-6d7f737ffd3b',
        'title' => 'Health Score Decile & Quartile Analysis'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => $grp_usr_mapping_id,
        'client_primary_id' => $client_primary_id,
        'dashboard_id' => 7,
        'sub_dashboard_id' => '',
        'looker_dash_id' => ''
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => $client_primary_id,
        'client_id' => $client_id,
        'client_name' => 'Demo',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 7,
        'folder_name' => 'Health Score & Predictive Reports',
        'dash_id' => '51d47db0-d0b9-480d-a45f-c01df0c8488e/page/eNoqF',
        'title' => 'Data Science Overview'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => $grp_usr_mapping_id,
        'client_primary_id' => $client_primary_id,
        'dashboard_id' => 7,
        'sub_dashboard_id' => '',
        'looker_dash_id' => ''
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => $client_primary_id,
        'client_id' => $client_id,
        'client_name' => 'Demo',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 7,
        'folder_name' => 'Health Score & Predictive Reports',
        'dash_id' => '8758e0a2-e25e-46be-afee-841ae094954c',
        'title' => 'Data Science Predictive Analysis (Overview & Member-Level Analysis)'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => $grp_usr_mapping_id,
        'client_primary_id' => $client_primary_id,
        'dashboard_id' => 7,
        'sub_dashboard_id' => '',
        'looker_dash_id' => ''
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => $client_primary_id,
        'client_id' => $client_id,
        'client_name' => 'Demo',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 7,
        'folder_name' => 'Health Score & Predictive Reports',
        'dash_id' => '4a91a614-142d-4415-9b2f-6eb3ba9dc20e',
        'title' => 'Health Score Summary'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => $grp_usr_mapping_id,
        'client_primary_id' => $client_primary_id,
        'dashboard_id' => 7,
        'sub_dashboard_id' => '',
        'looker_dash_id' => ''
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => $client_primary_id,
        'client_id' => $client_id,
        'client_name' => 'Demo',
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
        'sub_dashboard_id' => '',
        'looker_dash_id' => ''
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => $client_primary_id,
        'client_id' => $client_id,
        'client_name' => 'Demo',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 8,
        'folder_name' => 'Medical Reports',
        'dash_id' => 'b8c07a7a-32db-4e62-8c43-c8d026f24198',
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
        'client_name' => 'Demo',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 8,
        'folder_name' => 'Medical Reports',
        'dash_id' => 's/ltoHxOaGM9Y',
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
        'client_name' => 'Demo',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 8,
        'folder_name' => 'Medical Reports',
        'dash_id' => 's/nrXqnsahTbA',
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
        'client_name' => 'Demo',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 8,
        'folder_name' => 'Medical Reports',
        'dash_id' => 's/mEGINX8eOXM',
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
        'client_name' => 'Demo',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 8,
        'folder_name' => 'Medical Reports',
        'dash_id' => 's/qu8LHQQoGRQ',
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
        'client_name' => 'Demo',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 8,
        'folder_name' => 'Medical Reports',
        'dash_id' => '6b044f85-1007-4818-b53a-f8fb56b61844',
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
        'client_name' => 'Demo',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 8,
        'folder_name' => 'Medical Reports',
        'dash_id' => '7c416038-6a2a-4871-bf0f-59dfb862f0ff',
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
        'client_name' => 'Demo',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 8,
        'folder_name' => 'Medical Reports',
        'dash_id' => 'ad1f3cb4-cff1-4e05-8d9f-bc7daba14234/page/c0DrF',
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
        'client_name' => 'Demo',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 8,
        'folder_name' => 'Medical Reports',
        'dash_id' => '0334398c-0f7d-4e3a-9481-1824b8d34ad7/page/HqCrF',
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
        'client_name' => 'Demo',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 8,
        'folder_name' => 'Medical Reports',
        'dash_id' => 'dd47879a-9083-4ef7-a32a-40c098f026ec',
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
        'client_name' => 'Demo',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 9,
        'folder_name' => 'MSK Reports',
        'dash_id' => '94febcad-6fc2-42c2-93ca-7bdc63412f16',
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
        'client_name' => 'Demo',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 9,
        'folder_name' => 'MSK Reports',
        'dash_id' => '30854a03-9a10-43a7-aa7b-c31f7d8b0136/page/a5YrF',
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
        'client_name' => 'Demo',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 9,
        'folder_name' => 'MSK Reports',
        'dash_id' => 'e187b01a-888a-4f91-9997-2a53ef6460ce',
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
        'client_name' => 'Demo',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 9,
        'folder_name' => 'MSK Reports',
        'dash_id' => '81bd2b0a-ed50-454a-a3e1-5098b2acfc50',
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
        'client_name' => 'Demo',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 9,
        'folder_name' => 'MSK Reports',
        'dash_id' => '38def230-e743-48df-bb7f-761e7adbc89d',
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
        'client_name' => 'Demo',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 9,
        'folder_name' => 'MSK Reports',
        'dash_id' => 'c4386014-392e-49e8-ba4d-c9cb69f168b9',
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
        'client_name' => 'Demo',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 9,
        'folder_name' => 'MSK Reports',
        'dash_id' => 'e8d44de7-523d-42bb-9768-36cd43daab40',
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
        'client_name' => 'Demo',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 9,
        'folder_name' => 'MSK Reports',
        'dash_id' => '48ad9390-6e83-4437-8f26-c02cbebcb978',
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
        'client_name' => 'Demo',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 9,
        'folder_name' => 'MSK Reports',
        'dash_id' => 'e6f97bb9-c053-4c83-b84f-d0e9b89b31c0',
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
        'client_name' => 'Demo',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 9,
        'folder_name' => 'MSK Reports',
        'dash_id' => 'e5b9770f-7bdd-44b4-9120-ce576216b631',
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
        'client_name' => 'Demo',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 9,
        'folder_name' => 'MSK Reports',
        'dash_id' => '90f0e070-9b11-4034-8344-513e30d7ccd3/page/7RStF',
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
        'client_name' => 'Demo',
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
        'client_name' => 'Demo',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 10,
        'folder_name' => 'Operations',
        'dash_id' => '6f2243e3-016d-48b0-a7e8-aba8b78f2969',
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
        'client_name' => 'Demo',
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
        'client_name' => 'Demo',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 10,
        'folder_name' => 'Operations',
        'dash_id' => 's/uaMYQ7CmzVY',
        'title' => 'Eligibility History Report'
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
        'client_name' => 'Demo',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 10,
        'folder_name' => 'Operations',
        'dash_id' => '16986e3c-13e1-4318-8845-a333bcbfdd5d',
        'title' => 'Referral Data - Demographic information'
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
        'client_name' => 'Demo',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 10,
        'folder_name' => 'Operations',
        'dash_id' => 'bfec70c9-7cb1-4854-9a1c-b656e9b9382d',
        'title' => 'All Disease Variable Trend'
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
        'client_name' => 'Demo',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 10,
        'folder_name' => 'Operations',
        'dash_id' => 'e58aba4a-2ff1-4b38-8d0b-4677eecafe72',
        'title' => 'Spend Analysis'
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
        'client_name' => 'Demo',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 11,
        'folder_name' => 'Pharmacy Reports',
        'dash_id' => '90e4a1d7-e3a7-4b8a-8f56-be45cff395ce',
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
        'client_name' => 'Demo',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 11,
        'folder_name' => 'Pharmacy Reports',
        'dash_id' => '343d5047-005d-448f-8602-fa69251642b4',
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
        'client_name' => 'Demo',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 11,
        'folder_name' => 'Pharmacy Reports',
        'dash_id' => '71d2743e-7002-4480-976c-262b83e22e08/page/uqgrF',
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
        'client_name' => 'Demo',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 11,
        'folder_name' => 'Pharmacy Reports',
        'dash_id' => 'a19b0a75-102f-4f0e-a70a-d393e86422a4/page/5HYrF',
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
        'client_name' => 'Demo',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 11,
        'folder_name' => 'Pharmacy Reports',
        'dash_id' => '596c34ed-f288-4368-bcbe-ed0f41a59a3a',
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
        'client_name' => 'Demo',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 12,
        'folder_name' => 'Risk Groups',
        'dash_id' => '73c33d5c-576f-47fe-845b-892af7a851da',
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
        'client_name' => 'Demo',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 12,
        'folder_name' => 'Risk Groups',
        'dash_id' => 'ae9843b1-4d25-4119-8038-9acf1c1ab235',
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
        'client_name' => 'Demo',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 12,
        'folder_name' => 'Risk Groups',
        'dash_id' => '064898c2-6d58-4e31-8556-8befbd5f21e1/page/kIFrF',
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
        'client_name' => 'Demo',
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'folder_id' => 12,
        'folder_name' => 'Risk Groups',
        'dash_id' => 'bc7edbc6-584a-4c98-875e-467505a95819',
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