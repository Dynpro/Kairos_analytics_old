<?php
require_once __DIR__.'/bootstrap/app.php';
use Illuminate\Support\Facades\DB;
try {
    echo "Cleaning existing mappings...\n";
    DB::statement('TRUNCATE TABLE looker_data');
    DB::statement('TRUNCATE TABLE users_dasboards_mapping');
    DB::table('looker_data')->insert([
        'client_primary_id' => 1,
        'client_id' => 1,
        'client_name' => 'Demo',
        'category_id' => 1,
        'subcategory_id' => 1,
        'folder_id' => 1,
        'folder_name' => 'Care Coordination (Evidence-Based Rules)',
        'dash_id' => 'f141a4d4-4ec4-4334-b4b3-4fc8257595a2/page/BwhrF',
        'title' => 'Additional Risk Factors (Member-Level Analysis)'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => 1,
        'client_primary_id' => 1,
        'dashboard_id' => 1,
        'sub_dashboard_id' => 'f141a4d4-4ec4-4334-b4b3-4fc8257595a2/page/BwhrF',
        'looker_dash_id' => 'f141a4d4-4ec4-4334-b4b3-4fc8257595a2/page/BwhrF'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => 1,
        'client_id' => 1,
        'client_name' => 'Demo',
        'category_id' => 1,
        'subcategory_id' => 1,
        'folder_id' => 1,
        'folder_name' => 'Care Coordination (Evidence-Based Rules)',
        'dash_id' => '3e897e25-9aa2-43f3-ad7e-72efab31be26/page/6igsF',
        'title' => 'Cancer - Preventive Screening Compliance'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => 1,
        'client_primary_id' => 1,
        'dashboard_id' => 1,
        'sub_dashboard_id' => '3e897e25-9aa2-43f3-ad7e-72efab31be26/page/6igsF',
        'looker_dash_id' => '3e897e25-9aa2-43f3-ad7e-72efab31be26/page/6igsF'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => 1,
        'client_id' => 1,
        'client_name' => 'Demo',
        'category_id' => 1,
        'subcategory_id' => 1,
        'folder_id' => 1,
        'folder_name' => 'Care Coordination (Evidence-Based Rules)',
        'dash_id' => '23ffd42b-3f34-4da9-a440-b6b44f243baa/page/wa6sF',
        'title' => 'Diabetes - Evidence-Based Rules Compliance'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => 1,
        'client_primary_id' => 1,
        'dashboard_id' => 1,
        'sub_dashboard_id' => '23ffd42b-3f34-4da9-a440-b6b44f243baa/page/wa6sF',
        'looker_dash_id' => '23ffd42b-3f34-4da9-a440-b6b44f243baa/page/wa6sF'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => 1,
        'client_id' => 1,
        'client_name' => 'Demo',
        'category_id' => 1,
        'subcategory_id' => 1,
        'folder_id' => 1,
        'folder_name' => 'Care Coordination (Evidence-Based Rules)',
        'dash_id' => '5ad3029b-4cc1-49b4-b455-76632543b112/page/uKjrF',
        'title' => 'Evidence-Based Rules Compliance (Member-Level Analysis)'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => 1,
        'client_primary_id' => 1,
        'dashboard_id' => 1,
        'sub_dashboard_id' => '5ad3029b-4cc1-49b4-b455-76632543b112/page/uKjrF',
        'looker_dash_id' => '5ad3029b-4cc1-49b4-b455-76632543b112/page/uKjrF'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => 1,
        'client_id' => 1,
        'client_name' => 'Demo',
        'category_id' => 1,
        'subcategory_id' => 1,
        'folder_id' => 1,
        'folder_name' => 'Care Coordination (Evidence-Based Rules)',
        'dash_id' => '23558699-9a01-4645-b048-4f7a5815ecf0/page/6uZrF',
        'title' => 'Evidence-Based Rules Compliance Summary'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => 1,
        'client_primary_id' => 1,
        'dashboard_id' => 1,
        'sub_dashboard_id' => '23558699-9a01-4645-b048-4f7a5815ecf0/page/6uZrF',
        'looker_dash_id' => '23558699-9a01-4645-b048-4f7a5815ecf0/page/6uZrF'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => 1,
        'client_id' => 1,
        'client_name' => 'Demo',
        'category_id' => 1,
        'subcategory_id' => 1,
        'folder_id' => 1,
        'folder_name' => 'Care Coordination (Evidence-Based Rules)',
        'dash_id' => '0fb3cdff-6fee-4baf-a3fa-8178b009ad09/page/LlcrF',
        'title' => 'Overall Compliance to Evidence-Based Rules - Percentage'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => 1,
        'client_primary_id' => 1,
        'dashboard_id' => 1,
        'sub_dashboard_id' => '0fb3cdff-6fee-4baf-a3fa-8178b009ad09/page/LlcrF',
        'looker_dash_id' => '0fb3cdff-6fee-4baf-a3fa-8178b009ad09/page/LlcrF'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => 1,
        'client_id' => 1,
        'client_name' => 'Demo',
        'category_id' => 1,
        'subcategory_id' => 1,
        'folder_id' => 1,
        'folder_name' => 'Care Coordination (Evidence-Based Rules)',
        'dash_id' => 'b7d03c29-c802-4cb8-b615-d81ab34f4123/page/8rYrF',
        'title' => 'Preventive Screening Compliance (Member-Level Analysis)'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => 1,
        'client_primary_id' => 1,
        'dashboard_id' => 1,
        'sub_dashboard_id' => 'b7d03c29-c802-4cb8-b615-d81ab34f4123/page/8rYrF',
        'looker_dash_id' => 'b7d03c29-c802-4cb8-b615-d81ab34f4123/page/8rYrF'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => 1,
        'client_id' => 1,
        'client_name' => 'Demo',
        'category_id' => 1,
        'subcategory_id' => 1,
        'folder_id' => 2,
        'folder_name' => 'Chronic Condition Reports',
        'dash_id' => 'cd86b02c-55a4-44d1-a635-804848a969ed/page/qmSsF',
        'title' => 'Diabetes - Demographic & Economic Insights'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => 1,
        'client_primary_id' => 1,
        'dashboard_id' => 2,
        'sub_dashboard_id' => 'cd86b02c-55a4-44d1-a635-804848a969ed/page/qmSsF',
        'looker_dash_id' => 'cd86b02c-55a4-44d1-a635-804848a969ed/page/qmSsF'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => 1,
        'client_id' => 1,
        'client_name' => 'Demo',
        'category_id' => 1,
        'subcategory_id' => 1,
        'folder_id' => 2,
        'folder_name' => 'Chronic Condition Reports',
        'dash_id' => 'bb19f877-be8d-46d5-8f0a-9972afe8151f/page/ALxqF',
        'title' => 'Diabetes - Medical & Pharmacy Claims Summary'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => 1,
        'client_primary_id' => 1,
        'dashboard_id' => 2,
        'sub_dashboard_id' => 'bb19f877-be8d-46d5-8f0a-9972afe8151f/page/ALxqF',
        'looker_dash_id' => 'bb19f877-be8d-46d5-8f0a-9972afe8151f/page/ALxqF'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => 1,
        'client_id' => 1,
        'client_name' => 'Demo',
        'category_id' => 1,
        'subcategory_id' => 1,
        'folder_id' => 2,
        'folder_name' => 'Chronic Condition Reports',
        'dash_id' => 'af862975-cca2-4457-8769-23570d13f40f/page/GdgrF',
        'title' => 'Heart Disease - Demographic & Economic Insights'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => 1,
        'client_primary_id' => 1,
        'dashboard_id' => 2,
        'sub_dashboard_id' => 'af862975-cca2-4457-8769-23570d13f40f/page/GdgrF',
        'looker_dash_id' => 'af862975-cca2-4457-8769-23570d13f40f/page/GdgrF'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => 1,
        'client_id' => 1,
        'client_name' => 'Demo',
        'category_id' => 1,
        'subcategory_id' => 1,
        'folder_id' => 2,
        'folder_name' => 'Chronic Condition Reports',
        'dash_id' => '56378c09-07b3-42fb-b77b-212faeaf0236/page/GdgrF',
        'title' => 'Heart Disease - Medical & Pharmacy Claims Summary'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => 1,
        'client_primary_id' => 1,
        'dashboard_id' => 2,
        'sub_dashboard_id' => '56378c09-07b3-42fb-b77b-212faeaf0236/page/GdgrF',
        'looker_dash_id' => '56378c09-07b3-42fb-b77b-212faeaf0236/page/GdgrF'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => 1,
        'client_id' => 1,
        'client_name' => 'Demo',
        'category_id' => 1,
        'subcategory_id' => 1,
        'folder_id' => 2,
        'folder_name' => 'Chronic Condition Reports',
        'dash_id' => '48eb81f4-b1ef-4053-b160-2e3e7e443d03/page/dAyrF',
        'title' => 'Hypertension - Demographic & Economic Insights'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => 1,
        'client_primary_id' => 1,
        'dashboard_id' => 2,
        'sub_dashboard_id' => '48eb81f4-b1ef-4053-b160-2e3e7e443d03/page/dAyrF',
        'looker_dash_id' => '48eb81f4-b1ef-4053-b160-2e3e7e443d03/page/dAyrF'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => 1,
        'client_id' => 1,
        'client_name' => 'Demo',
        'category_id' => 1,
        'subcategory_id' => 1,
        'folder_id' => 2,
        'folder_name' => 'Chronic Condition Reports',
        'dash_id' => 'c5c9ece6-34af-46cd-96f6-57080cc988e3/page/N26rF',
        'title' => 'Hypertension - Medical & Pharmacy Claims Summary'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => 1,
        'client_primary_id' => 1,
        'dashboard_id' => 2,
        'sub_dashboard_id' => 'c5c9ece6-34af-46cd-96f6-57080cc988e3/page/N26rF',
        'looker_dash_id' => 'c5c9ece6-34af-46cd-96f6-57080cc988e3/page/N26rF'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => 1,
        'client_id' => 1,
        'client_name' => 'Demo',
        'category_id' => 1,
        'subcategory_id' => 1,
        'folder_id' => 3,
        'folder_name' => 'Client Services',
        'dash_id' => '6b552946-9e2f-4057-b447-d6b3d8a1eecb/page/KJutF',
        'title' => 'Chronic Conditions Summary'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => 1,
        'client_primary_id' => 1,
        'dashboard_id' => 3,
        'sub_dashboard_id' => '6b552946-9e2f-4057-b447-d6b3d8a1eecb/page/KJutF',
        'looker_dash_id' => '6b552946-9e2f-4057-b447-d6b3d8a1eecb/page/KJutF'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => 1,
        'client_id' => 1,
        'client_name' => 'Demo',
        'category_id' => 1,
        'subcategory_id' => 1,
        'folder_id' => 3,
        'folder_name' => 'Client Services',
        'dash_id' => '40a282cd-a638-4d9d-a118-1e8bb3177fcf/page/VxqrF',
        'title' => 'Claims Analysis Summary (Filter by Calendar Year)'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => 1,
        'client_primary_id' => 1,
        'dashboard_id' => 3,
        'sub_dashboard_id' => '40a282cd-a638-4d9d-a118-1e8bb3177fcf/page/VxqrF',
        'looker_dash_id' => '40a282cd-a638-4d9d-a118-1e8bb3177fcf/page/VxqrF'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => 1,
        'client_id' => 1,
        'client_name' => 'Demo',
        'category_id' => 1,
        'subcategory_id' => 1,
        'folder_id' => 3,
        'folder_name' => 'Client Services',
        'dash_id' => '79d48e3b-2b09-4d39-a446-5ff85e4d6eed/page/DsorF',
        'title' => 'Claims Analysis Summary (Filter by Plan Year)'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => 1,
        'client_primary_id' => 1,
        'dashboard_id' => 3,
        'sub_dashboard_id' => '79d48e3b-2b09-4d39-a446-5ff85e4d6eed/page/DsorF',
        'looker_dash_id' => '79d48e3b-2b09-4d39-a446-5ff85e4d6eed/page/DsorF'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => 1,
        'client_id' => 1,
        'client_name' => 'Demo',
        'category_id' => 1,
        'subcategory_id' => 1,
        'folder_id' => 3,
        'folder_name' => 'Client Services',
        'dash_id' => 'ff856ed9-9931-4748-8a69-e2bcb5c25ecc/page/DTErF',
        'title' => 'Demographic & Claims Summary'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => 1,
        'client_primary_id' => 1,
        'dashboard_id' => 3,
        'sub_dashboard_id' => 'ff856ed9-9931-4748-8a69-e2bcb5c25ecc/page/DTErF',
        'looker_dash_id' => 'ff856ed9-9931-4748-8a69-e2bcb5c25ecc/page/DTErF'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => 1,
        'client_id' => 1,
        'client_name' => 'Demo',
        'category_id' => 1,
        'subcategory_id' => 1,
        'folder_id' => 3,
        'folder_name' => 'Client Services',
        'dash_id' => '142ac897-91ad-4e2d-9e4c-f8904410da55/page/bLNqF',
        'title' => 'Members with Claims above Average Paid Amount'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => 1,
        'client_primary_id' => 1,
        'dashboard_id' => 3,
        'sub_dashboard_id' => '142ac897-91ad-4e2d-9e4c-f8904410da55/page/bLNqF',
        'looker_dash_id' => '142ac897-91ad-4e2d-9e4c-f8904410da55/page/bLNqF'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => 1,
        'client_id' => 1,
        'client_name' => 'Demo',
        'category_id' => 1,
        'subcategory_id' => 1,
        'folder_id' => 3,
        'folder_name' => 'Client Services',
        'dash_id' => '7a0b5df2-abf6-4be3-b8d2-f72c15cf5b4c/page/V3ntF',
        'title' => 'Monthly Report - Member Data'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => 1,
        'client_primary_id' => 1,
        'dashboard_id' => 3,
        'sub_dashboard_id' => '7a0b5df2-abf6-4be3-b8d2-f72c15cf5b4c/page/V3ntF',
        'looker_dash_id' => '7a0b5df2-abf6-4be3-b8d2-f72c15cf5b4c/page/V3ntF'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => 1,
        'client_id' => 1,
        'client_name' => 'Demo',
        'category_id' => 1,
        'subcategory_id' => 1,
        'folder_id' => 3,
        'folder_name' => 'Client Services',
        'dash_id' => 'e8cc9825-ac8e-4be6-95eb-1c0715cb7a09/page/WivqF',
        'title' => 'Monthly Report - Summary'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => 1,
        'client_primary_id' => 1,
        'dashboard_id' => 3,
        'sub_dashboard_id' => 'e8cc9825-ac8e-4be6-95eb-1c0715cb7a09/page/WivqF',
        'looker_dash_id' => 'e8cc9825-ac8e-4be6-95eb-1c0715cb7a09/page/WivqF'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => 1,
        'client_id' => 1,
        'client_name' => 'Demo',
        'category_id' => 1,
        'subcategory_id' => 1,
        'folder_id' => 3,
        'folder_name' => 'Client Services',
        'dash_id' => '0334398c-0f7d-4e3a-9481-1824b8d34ad7/page/HqCrF',
        'title' => 'Overall Population Demographic Summary'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => 1,
        'client_primary_id' => 1,
        'dashboard_id' => 3,
        'sub_dashboard_id' => '0334398c-0f7d-4e3a-9481-1824b8d34ad7/page/HqCrF',
        'looker_dash_id' => '0334398c-0f7d-4e3a-9481-1824b8d34ad7/page/HqCrF'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => 1,
        'client_id' => 1,
        'client_name' => 'Demo',
        'category_id' => 1,
        'subcategory_id' => 1,
        'folder_id' => 3,
        'folder_name' => 'Client Services',
        'dash_id' => '4a99e41e-559d-4ecc-af28-4d20df8b081e/page/FyDrF',
        'title' => 'Quarterly Report'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => 1,
        'client_primary_id' => 1,
        'dashboard_id' => 3,
        'sub_dashboard_id' => '4a99e41e-559d-4ecc-af28-4d20df8b081e/page/FyDrF',
        'looker_dash_id' => '4a99e41e-559d-4ecc-af28-4d20df8b081e/page/FyDrF'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => 1,
        'client_id' => 1,
        'client_name' => 'Demo',
        'category_id' => 1,
        'subcategory_id' => 1,
        'folder_id' => 3,
        'folder_name' => 'Client Services',
        'dash_id' => 'e64461ae-904c-4858-9c46-96416cd74629/page/W2ErF',
        'title' => 'Referral List (All Members)'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => 1,
        'client_primary_id' => 1,
        'dashboard_id' => 3,
        'sub_dashboard_id' => 'e64461ae-904c-4858-9c46-96416cd74629/page/W2ErF',
        'looker_dash_id' => 'e64461ae-904c-4858-9c46-96416cd74629/page/W2ErF'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => 1,
        'client_id' => 1,
        'client_name' => 'Demo',
        'category_id' => 1,
        'subcategory_id' => 1,
        'folder_id' => 3,
        'folder_name' => 'Client Services',
        'dash_id' => '7267098f-c2c1-4d2e-a89a-eebb14bfdfba/page/OxYrF',
        'title' => 'Referral List (New Eligible Members)'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => 1,
        'client_primary_id' => 1,
        'dashboard_id' => 3,
        'sub_dashboard_id' => '7267098f-c2c1-4d2e-a89a-eebb14bfdfba/page/OxYrF',
        'looker_dash_id' => '7267098f-c2c1-4d2e-a89a-eebb14bfdfba/page/OxYrF'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => 1,
        'client_id' => 1,
        'client_name' => 'Demo',
        'category_id' => 1,
        'subcategory_id' => 1,
        'folder_id' => 4,
        'folder_name' => 'Clinical',
        'dash_id' => '06081a4e-d5c3-4b5a-ac29-4640fcad03a4/page/LD3pF',
        'title' => 'Chronic Conditions Summary'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => 1,
        'client_primary_id' => 1,
        'dashboard_id' => 4,
        'sub_dashboard_id' => '06081a4e-d5c3-4b5a-ac29-4640fcad03a4/page/LD3pF',
        'looker_dash_id' => '06081a4e-d5c3-4b5a-ac29-4640fcad03a4/page/LD3pF'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => 1,
        'client_id' => 1,
        'client_name' => 'Demo',
        'category_id' => 1,
        'subcategory_id' => 1,
        'folder_id' => 4,
        'folder_name' => 'Clinical',
        'dash_id' => '3fe2bdf3-c121-4e19-9f36-dfdaf1648831/page/LodrF',
        'title' => 'Diabetes - Evidence-Based Rules Compliance'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => 1,
        'client_primary_id' => 1,
        'dashboard_id' => 4,
        'sub_dashboard_id' => '3fe2bdf3-c121-4e19-9f36-dfdaf1648831/page/LodrF',
        'looker_dash_id' => '3fe2bdf3-c121-4e19-9f36-dfdaf1648831/page/LodrF'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => 1,
        'client_id' => 1,
        'client_name' => 'Demo',
        'category_id' => 1,
        'subcategory_id' => 1,
        'folder_id' => 4,
        'folder_name' => 'Clinical',
        'dash_id' => '69ea3c08-c9d6-4931-9555-1f841b8d907a/page/Pa5rF',
        'title' => 'Heart Disease - Demographic & Economic Insights'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => 1,
        'client_primary_id' => 1,
        'dashboard_id' => 4,
        'sub_dashboard_id' => '69ea3c08-c9d6-4931-9555-1f841b8d907a/page/Pa5rF',
        'looker_dash_id' => '69ea3c08-c9d6-4931-9555-1f841b8d907a/page/Pa5rF'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => 1,
        'client_id' => 1,
        'client_name' => 'Demo',
        'category_id' => 1,
        'subcategory_id' => 1,
        'folder_id' => 4,
        'folder_name' => 'Clinical',
        'dash_id' => 'adbe15cb-290e-462f-ab97-2322c6a121ee/page/RI7rF',
        'title' => 'Hypertension - Demographic & Economic Insights'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => 1,
        'client_primary_id' => 1,
        'dashboard_id' => 4,
        'sub_dashboard_id' => 'adbe15cb-290e-462f-ab97-2322c6a121ee/page/RI7rF',
        'looker_dash_id' => 'adbe15cb-290e-462f-ab97-2322c6a121ee/page/RI7rF'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => 1,
        'client_id' => 1,
        'client_name' => 'Demo',
        'category_id' => 1,
        'subcategory_id' => 1,
        'folder_id' => 4,
        'folder_name' => 'Clinical',
        'dash_id' => 'a447804d-7d9c-430d-ae13-e5b413f8f3ca/page/TGvsF?s=pNyj_yFAdrk',
        'title' => 'Member Data Summary (Filter by Calendar Year)'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => 1,
        'client_primary_id' => 1,
        'dashboard_id' => 4,
        'sub_dashboard_id' => 'a447804d-7d9c-430d-ae13-e5b413f8f3ca/page/TGvsF?s=pNyj_yFAdrk',
        'looker_dash_id' => 'a447804d-7d9c-430d-ae13-e5b413f8f3ca/page/TGvsF?s=pNyj_yFAdrk'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => 1,
        'client_id' => 1,
        'client_name' => 'Demo',
        'category_id' => 1,
        'subcategory_id' => 1,
        'folder_id' => 4,
        'folder_name' => 'Clinical',
        'dash_id' => '73c06507-70a6-45bc-a7fa-17e467109d4b/page/xzYrF',
        'title' => 'Member Data Summary (Filter by Plan Year)'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => 1,
        'client_primary_id' => 1,
        'dashboard_id' => 4,
        'sub_dashboard_id' => '73c06507-70a6-45bc-a7fa-17e467109d4b/page/xzYrF',
        'looker_dash_id' => '73c06507-70a6-45bc-a7fa-17e467109d4b/page/xzYrF'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => 1,
        'client_id' => 1,
        'client_name' => 'Demo',
        'category_id' => 1,
        'subcategory_id' => 1,
        'folder_id' => 4,
        'folder_name' => 'Clinical',
        'dash_id' => 'e5b4dcb1-b170-44e8-9ffd-a929cf3d2ee9/page/TRCrF',
        'title' => 'Member Summary (Additional Details)'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => 1,
        'client_primary_id' => 1,
        'dashboard_id' => 4,
        'sub_dashboard_id' => 'e5b4dcb1-b170-44e8-9ffd-a929cf3d2ee9/page/TRCrF',
        'looker_dash_id' => 'e5b4dcb1-b170-44e8-9ffd-a929cf3d2ee9/page/TRCrF'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => 1,
        'client_id' => 1,
        'client_name' => 'Demo',
        'category_id' => 1,
        'subcategory_id' => 1,
        'folder_id' => 4,
        'folder_name' => 'Clinical',
        'dash_id' => '3e1b27d4-53b4-4b82-8259-b56c03a41cd2/page/277sF',
        'title' => 'Risk Groups Migration (Detailed Analysis)'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => 1,
        'client_primary_id' => 1,
        'dashboard_id' => 4,
        'sub_dashboard_id' => '3e1b27d4-53b4-4b82-8259-b56c03a41cd2/page/277sF',
        'looker_dash_id' => '3e1b27d4-53b4-4b82-8259-b56c03a41cd2/page/277sF'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => 1,
        'client_id' => 1,
        'client_name' => 'Demo',
        'category_id' => 1,
        'subcategory_id' => 1,
        'folder_id' => 5,
        'folder_name' => 'Cohort Analysis',
        'dash_id' => 'c147daa4-623c-4ed2-9d0a-cdacdf5624a4/page/jKbtF',
        'title' => 'Cohort Analysis (Compare 2 Groups)'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => 1,
        'client_primary_id' => 1,
        'dashboard_id' => 5,
        'sub_dashboard_id' => 'c147daa4-623c-4ed2-9d0a-cdacdf5624a4/page/jKbtF',
        'looker_dash_id' => 'c147daa4-623c-4ed2-9d0a-cdacdf5624a4/page/jKbtF'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => 1,
        'client_id' => 1,
        'client_name' => 'Demo',
        'category_id' => 1,
        'subcategory_id' => 1,
        'folder_id' => 6,
        'folder_name' => 'Executive Summary Report',
        'dash_id' => '47063557-d4ce-4759-a35a-f3ad48e7fc3d/page/q2frF',
        'title' => 'Executive Summary Report'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => 1,
        'client_primary_id' => 1,
        'dashboard_id' => 6,
        'sub_dashboard_id' => '47063557-d4ce-4759-a35a-f3ad48e7fc3d/page/q2frF',
        'looker_dash_id' => '47063557-d4ce-4759-a35a-f3ad48e7fc3d/page/q2frF'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => 1,
        'client_id' => 1,
        'client_name' => 'Demo',
        'category_id' => 1,
        'subcategory_id' => 1,
        'folder_id' => 6,
        'folder_name' => 'Executive Summary Report',
        'dash_id' => '38a89819-13fd-4bdb-8798-065d8b192caa/page/q2frF',
        'title' => 'Executive Summary Report NEW'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => 1,
        'client_primary_id' => 1,
        'dashboard_id' => 6,
        'sub_dashboard_id' => '38a89819-13fd-4bdb-8798-065d8b192caa/page/q2frF',
        'looker_dash_id' => '38a89819-13fd-4bdb-8798-065d8b192caa/page/q2frF'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => 1,
        'client_id' => 1,
        'client_name' => 'Demo',
        'category_id' => 1,
        'subcategory_id' => 1,
        'folder_id' => 7,
        'folder_name' => 'Health Score & Predictive Reports',
        'dash_id' => 'bfec70c9-7cb1-4854-9a1c-b656e9b9382d/page/wjvqF',
        'title' => 'All Disease Variable Trend'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => 1,
        'client_primary_id' => 1,
        'dashboard_id' => 7,
        'sub_dashboard_id' => 'bfec70c9-7cb1-4854-9a1c-b656e9b9382d/page/wjvqF',
        'looker_dash_id' => 'bfec70c9-7cb1-4854-9a1c-b656e9b9382d/page/wjvqF'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => 1,
        'client_id' => 1,
        'client_name' => 'Demo',
        'category_id' => 1,
        'subcategory_id' => 1,
        'folder_id' => 7,
        'folder_name' => 'Health Score & Predictive Reports',
        'dash_id' => '51d47db0-d0b9-480d-a45f-c01df0c8488e/page/eNoqF',
        'title' => 'Data Science Overview'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => 1,
        'client_primary_id' => 1,
        'dashboard_id' => 7,
        'sub_dashboard_id' => '51d47db0-d0b9-480d-a45f-c01df0c8488e/page/eNoqF',
        'looker_dash_id' => '51d47db0-d0b9-480d-a45f-c01df0c8488e/page/eNoqF'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => 1,
        'client_id' => 1,
        'client_name' => 'Demo',
        'category_id' => 1,
        'subcategory_id' => 1,
        'folder_id' => 7,
        'folder_name' => 'Health Score & Predictive Reports',
        'dash_id' => '8758e0a2-e25e-46be-afee-841ae094954c/page/wQarF',
        'title' => 'Data Science Predictive Analysis (Overview & Member-Level Analysis)'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => 1,
        'client_primary_id' => 1,
        'dashboard_id' => 7,
        'sub_dashboard_id' => '8758e0a2-e25e-46be-afee-841ae094954c/page/wQarF',
        'looker_dash_id' => '8758e0a2-e25e-46be-afee-841ae094954c/page/wQarF'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => 1,
        'client_id' => 1,
        'client_name' => 'Demo',
        'category_id' => 1,
        'subcategory_id' => 1,
        'folder_id' => 7,
        'folder_name' => 'Health Score & Predictive Reports',
        'dash_id' => 'c9bb5a3b-19cd-41a2-88a6-48dce309150a/page/9TQsF',
        'title' => 'Evidence-Based Rules Compliance Statistical Analysis'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => 1,
        'client_primary_id' => 1,
        'dashboard_id' => 7,
        'sub_dashboard_id' => 'c9bb5a3b-19cd-41a2-88a6-48dce309150a/page/9TQsF',
        'looker_dash_id' => 'c9bb5a3b-19cd-41a2-88a6-48dce309150a/page/9TQsF'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => 1,
        'client_id' => 1,
        'client_name' => 'Demo',
        'category_id' => 1,
        'subcategory_id' => 1,
        'folder_id' => 7,
        'folder_name' => 'Health Score & Predictive Reports',
        'dash_id' => 'f8cb318d-3ed3-4fc2-aacd-07186782f951/page/QmpqF',
        'title' => 'Health Score (Member-Level Analysis)'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => 1,
        'client_primary_id' => 1,
        'dashboard_id' => 7,
        'sub_dashboard_id' => 'f8cb318d-3ed3-4fc2-aacd-07186782f951/page/QmpqF',
        'looker_dash_id' => 'f8cb318d-3ed3-4fc2-aacd-07186782f951/page/QmpqF'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => 1,
        'client_id' => 1,
        'client_name' => 'Demo',
        'category_id' => 1,
        'subcategory_id' => 1,
        'folder_id' => 7,
        'folder_name' => 'Health Score & Predictive Reports',
        'dash_id' => '3a49c605-dbf8-41c1-b1dd-a85973ddfb37/page/4wvqF',
        'title' => 'Health Score & Risk Group Overview'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => 1,
        'client_primary_id' => 1,
        'dashboard_id' => 7,
        'sub_dashboard_id' => '3a49c605-dbf8-41c1-b1dd-a85973ddfb37/page/4wvqF',
        'looker_dash_id' => '3a49c605-dbf8-41c1-b1dd-a85973ddfb37/page/4wvqF'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => 1,
        'client_id' => 1,
        'client_name' => 'Demo',
        'category_id' => 1,
        'subcategory_id' => 1,
        'folder_id' => 7,
        'folder_name' => 'Health Score & Predictive Reports',
        'dash_id' => 'f0be7979-521b-43bb-b759-6d7f737ffd3b/page/hvrrF',
        'title' => 'Health Score Decile & Quartile Analysis'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => 1,
        'client_primary_id' => 1,
        'dashboard_id' => 7,
        'sub_dashboard_id' => 'f0be7979-521b-43bb-b759-6d7f737ffd3b/page/hvrrF',
        'looker_dash_id' => 'f0be7979-521b-43bb-b759-6d7f737ffd3b/page/hvrrF'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => 1,
        'client_id' => 1,
        'client_name' => 'Demo',
        'category_id' => 1,
        'subcategory_id' => 1,
        'folder_id' => 7,
        'folder_name' => 'Health Score & Predictive Reports',
        'dash_id' => '4a91a614-142d-4415-9b2f-6eb3ba9dc20e/page/vbqrF',
        'title' => 'Health Score Summary'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => 1,
        'client_primary_id' => 1,
        'dashboard_id' => 7,
        'sub_dashboard_id' => '4a91a614-142d-4415-9b2f-6eb3ba9dc20e/page/vbqrF',
        'looker_dash_id' => '4a91a614-142d-4415-9b2f-6eb3ba9dc20e/page/vbqrF'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => 1,
        'client_id' => 1,
        'client_name' => 'Demo',
        'category_id' => 1,
        'subcategory_id' => 1,
        'folder_id' => 7,
        'folder_name' => 'Health Score & Predictive Reports',
        'dash_id' => 'e58aba4a-2ff1-4b38-8d0b-4677eecafe72/page/xzorF',
        'title' => 'Spend Analysis'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => 1,
        'client_primary_id' => 1,
        'dashboard_id' => 7,
        'sub_dashboard_id' => 'e58aba4a-2ff1-4b38-8d0b-4677eecafe72/page/xzorF',
        'looker_dash_id' => 'e58aba4a-2ff1-4b38-8d0b-4677eecafe72/page/xzorF'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => 1,
        'client_id' => 1,
        'client_name' => 'Demo',
        'category_id' => 1,
        'subcategory_id' => 1,
        'folder_id' => 8,
        'folder_name' => 'Medical Reports',
        'dash_id' => 'cdd12ab1-e2bf-4cee-b09f-226307ad758d/page/khDqF',
        'title' => 'Ad Hoc Query Tool'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => 1,
        'client_primary_id' => 1,
        'dashboard_id' => 8,
        'sub_dashboard_id' => 'cdd12ab1-e2bf-4cee-b09f-226307ad758d/page/khDqF',
        'looker_dash_id' => 'cdd12ab1-e2bf-4cee-b09f-226307ad758d/page/khDqF'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => 1,
        'client_id' => 1,
        'client_name' => 'Demo',
        'category_id' => 1,
        'subcategory_id' => 1,
        'folder_id' => 8,
        'folder_name' => 'Medical Reports',
        'dash_id' => '6f2243e3-016d-48b0-a7e8-aba8b78f2969/page/O3YrF',
        'title' => 'Ad Hoc Query Tool 2.0'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => 1,
        'client_primary_id' => 1,
        'dashboard_id' => 8,
        'sub_dashboard_id' => '6f2243e3-016d-48b0-a7e8-aba8b78f2969/page/O3YrF',
        'looker_dash_id' => '6f2243e3-016d-48b0-a7e8-aba8b78f2969/page/O3YrF'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => 1,
        'client_id' => 1,
        'client_name' => 'Demo',
        'category_id' => 1,
        'subcategory_id' => 1,
        'folder_id' => 8,
        'folder_name' => 'Medical Reports',
        'dash_id' => '3fe2bdf3-c121-4e19-9f36-dfdaf1648831/page/LodrF',
        'title' => 'Chronic Conditions Summary'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => 1,
        'client_primary_id' => 1,
        'dashboard_id' => 8,
        'sub_dashboard_id' => '3fe2bdf3-c121-4e19-9f36-dfdaf1648831/page/LodrF',
        'looker_dash_id' => '3fe2bdf3-c121-4e19-9f36-dfdaf1648831/page/LodrF'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => 1,
        'client_id' => 1,
        'client_name' => 'Demo',
        'category_id' => 1,
        'subcategory_id' => 1,
        'folder_id' => 8,
        'folder_name' => 'Medical Reports',
        'dash_id' => '7c416038-6a2a-4871-bf0f-59dfb862f0ff/page/OZLrF',
        'title' => 'Demographic & Claims Summary'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => 1,
        'client_primary_id' => 1,
        'dashboard_id' => 8,
        'sub_dashboard_id' => '7c416038-6a2a-4871-bf0f-59dfb862f0ff/page/OZLrF',
        'looker_dash_id' => '7c416038-6a2a-4871-bf0f-59dfb862f0ff/page/OZLrF'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => 1,
        'client_id' => 1,
        'client_name' => 'Demo',
        'category_id' => 1,
        'subcategory_id' => 1,
        'folder_id' => 8,
        'folder_name' => 'Medical Reports',
        'dash_id' => '039a2081-96d4-4b1c-b5b9-838179aca217?s=mEGINX8eOXM',
        'title' => 'Diagnostic Category Summary'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => 1,
        'client_primary_id' => 1,
        'dashboard_id' => 8,
        'sub_dashboard_id' => '039a2081-96d4-4b1c-b5b9-838179aca217?s=mEGINX8eOXM',
        'looker_dash_id' => '039a2081-96d4-4b1c-b5b9-838179aca217?s=mEGINX8eOXM'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => 1,
        'client_id' => 1,
        'client_name' => 'Demo',
        'category_id' => 1,
        'subcategory_id' => 1,
        'folder_id' => 8,
        'folder_name' => 'Medical Reports',
        'dash_id' => 'ce22cf0a-e84f-45a6-8e17-467df3a3f7b5?s=qu8LHQQoGRQ',
        'title' => 'Lifestyle Modifiable & Preventive Summary'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => 1,
        'client_primary_id' => 1,
        'dashboard_id' => 8,
        'sub_dashboard_id' => 'ce22cf0a-e84f-45a6-8e17-467df3a3f7b5?s=qu8LHQQoGRQ',
        'looker_dash_id' => 'ce22cf0a-e84f-45a6-8e17-467df3a3f7b5?s=qu8LHQQoGRQ'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => 1,
        'client_id' => 1,
        'client_name' => 'Demo',
        'category_id' => 1,
        'subcategory_id' => 1,
        'folder_id' => 8,
        'folder_name' => 'Medical Reports',
        'dash_id' => 'cb34fc42-7e4d-4543-aaed-4c927851650a/page/6lYrF?s=nrXqnsahTbA',
        'title' => 'Medical summary - Care Coordination'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => 1,
        'client_primary_id' => 1,
        'dashboard_id' => 8,
        'sub_dashboard_id' => 'cb34fc42-7e4d-4543-aaed-4c927851650a/page/6lYrF?s=nrXqnsahTbA',
        'looker_dash_id' => 'cb34fc42-7e4d-4543-aaed-4c927851650a/page/6lYrF?s=nrXqnsahTbA'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => 1,
        'client_id' => 1,
        'client_name' => 'Demo',
        'category_id' => 1,
        'subcategory_id' => 1,
        'folder_id' => 8,
        'folder_name' => 'Medical Reports',
        'dash_id' => 'dd47879a-9083-4ef7-a32a-40c098f026ec/page/8xrsF',
        'title' => 'Members with Claims above Average Paid Amount'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => 1,
        'client_primary_id' => 1,
        'dashboard_id' => 8,
        'sub_dashboard_id' => 'dd47879a-9083-4ef7-a32a-40c098f026ec/page/8xrsF',
        'looker_dash_id' => 'dd47879a-9083-4ef7-a32a-40c098f026ec/page/8xrsF'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => 1,
        'client_id' => 1,
        'client_name' => 'Demo',
        'category_id' => 1,
        'subcategory_id' => 1,
        'folder_id' => 8,
        'folder_name' => 'Medical Reports',
        'dash_id' => '0334398c-0f7d-4e3a-9481-1824b8d34ad7/page/HqCrF',
        'title' => 'Overall Population Demographic Summary'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => 1,
        'client_primary_id' => 1,
        'dashboard_id' => 8,
        'sub_dashboard_id' => '0334398c-0f7d-4e3a-9481-1824b8d34ad7/page/HqCrF',
        'looker_dash_id' => '0334398c-0f7d-4e3a-9481-1824b8d34ad7/page/HqCrF'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => 1,
        'client_id' => 1,
        'client_name' => 'Demo',
        'category_id' => 1,
        'subcategory_id' => 1,
        'folder_id' => 8,
        'folder_name' => 'Medical Reports',
        'dash_id' => 'd0afceba-9d8b-4015-8e3a-f66c8e8da421/page/PcrrF?s=ltoHxOaGM9Y',
        'title' => 'Preventive Claims Summary'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => 1,
        'client_primary_id' => 1,
        'dashboard_id' => 8,
        'sub_dashboard_id' => 'd0afceba-9d8b-4015-8e3a-f66c8e8da421/page/PcrrF?s=ltoHxOaGM9Y',
        'looker_dash_id' => 'd0afceba-9d8b-4015-8e3a-f66c8e8da421/page/PcrrF?s=ltoHxOaGM9Y'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => 1,
        'client_id' => 1,
        'client_name' => 'Demo',
        'category_id' => 1,
        'subcategory_id' => 1,
        'folder_id' => 8,
        'folder_name' => 'Medical Reports',
        'dash_id' => 'ad1f3cb4-cff1-4e05-8d9f-bc7daba14234/page/c0DrF',
        'title' => 'Total Lost Days Summary'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => 1,
        'client_primary_id' => 1,
        'dashboard_id' => 8,
        'sub_dashboard_id' => 'ad1f3cb4-cff1-4e05-8d9f-bc7daba14234/page/c0DrF',
        'looker_dash_id' => 'ad1f3cb4-cff1-4e05-8d9f-bc7daba14234/page/c0DrF'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => 1,
        'client_id' => 1,
        'client_name' => 'Demo',
        'category_id' => 1,
        'subcategory_id' => 1,
        'folder_id' => 9,
        'folder_name' => 'MSK Reports',
        'dash_id' => 'e8d44de7-523d-42bb-9768-36cd43daab40/page/O7grF',
        'title' => 'Hip ICD Codes Analysis'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => 1,
        'client_primary_id' => 1,
        'dashboard_id' => 9,
        'sub_dashboard_id' => 'e8d44de7-523d-42bb-9768-36cd43daab40/page/O7grF',
        'looker_dash_id' => 'e8d44de7-523d-42bb-9768-36cd43daab40/page/O7grF'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => 1,
        'client_id' => 1,
        'client_name' => 'Demo',
        'category_id' => 1,
        'subcategory_id' => 1,
        'folder_id' => 9,
        'folder_name' => 'MSK Reports',
        'dash_id' => '48ad9390-6e83-4437-8f26-c02cbebcb978/page/O7grF',
        'title' => 'Knee ICD Codes Analysis'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => 1,
        'client_primary_id' => 1,
        'dashboard_id' => 9,
        'sub_dashboard_id' => '48ad9390-6e83-4437-8f26-c02cbebcb978/page/O7grF',
        'looker_dash_id' => '48ad9390-6e83-4437-8f26-c02cbebcb978/page/O7grF'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => 1,
        'client_id' => 1,
        'client_name' => 'Demo',
        'category_id' => 1,
        'subcategory_id' => 1,
        'folder_id' => 9,
        'folder_name' => 'MSK Reports',
        'dash_id' => '94febcad-6fc2-42c2-93ca-7bdc63412f16/page/DbErF',
        'title' => 'Medical MSK - Overall Demographic & Economic Insights'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => 1,
        'client_primary_id' => 1,
        'dashboard_id' => 9,
        'sub_dashboard_id' => '94febcad-6fc2-42c2-93ca-7bdc63412f16/page/DbErF',
        'looker_dash_id' => '94febcad-6fc2-42c2-93ca-7bdc63412f16/page/DbErF'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => 1,
        'client_id' => 1,
        'client_name' => 'Demo',
        'category_id' => 1,
        'subcategory_id' => 1,
        'folder_id' => 9,
        'folder_name' => 'MSK Reports',
        'dash_id' => '38def230-e743-48df-bb7f-761e7adbc89d/page/O7grF',
        'title' => 'Medical MSK - Productivity and Absenteeism Insights'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => 1,
        'client_primary_id' => 1,
        'dashboard_id' => 9,
        'sub_dashboard_id' => '38def230-e743-48df-bb7f-761e7adbc89d/page/O7grF',
        'looker_dash_id' => '38def230-e743-48df-bb7f-761e7adbc89d/page/O7grF'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => 1,
        'client_id' => 1,
        'client_name' => 'Demo',
        'category_id' => 1,
        'subcategory_id' => 1,
        'folder_id' => 9,
        'folder_name' => 'MSK Reports',
        'dash_id' => 'e187b01a-888a-4f91-9997-2a53ef6460ce/page/CDEqF',
        'title' => 'Medical MSK - Provider Insights'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => 1,
        'client_primary_id' => 1,
        'dashboard_id' => 9,
        'sub_dashboard_id' => 'e187b01a-888a-4f91-9997-2a53ef6460ce/page/CDEqF',
        'looker_dash_id' => 'e187b01a-888a-4f91-9997-2a53ef6460ce/page/CDEqF'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => 1,
        'client_id' => 1,
        'client_name' => 'Demo',
        'category_id' => 1,
        'subcategory_id' => 1,
        'folder_id' => 9,
        'folder_name' => 'MSK Reports',
        'dash_id' => '30854a03-9a10-43a7-aa7b-c31f7d8b0136/page/a5YrF',
        'title' => 'Medical MSK - Work Related Disorders'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => 1,
        'client_primary_id' => 1,
        'dashboard_id' => 9,
        'sub_dashboard_id' => '30854a03-9a10-43a7-aa7b-c31f7d8b0136/page/a5YrF',
        'looker_dash_id' => '30854a03-9a10-43a7-aa7b-c31f7d8b0136/page/a5YrF'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => 1,
        'client_id' => 1,
        'client_name' => 'Demo',
        'category_id' => 1,
        'subcategory_id' => 1,
        'folder_id' => 9,
        'folder_name' => 'MSK Reports',
        'dash_id' => '90f0e070-9b11-4034-8344-513e30d7ccd3/page/7RStF',
        'title' => 'MRS Modifiable ICD Codes - Overall Analysis'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => 1,
        'client_primary_id' => 1,
        'dashboard_id' => 9,
        'sub_dashboard_id' => '90f0e070-9b11-4034-8344-513e30d7ccd3/page/7RStF',
        'looker_dash_id' => '90f0e070-9b11-4034-8344-513e30d7ccd3/page/7RStF'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => 1,
        'client_id' => 1,
        'client_name' => 'Demo',
        'category_id' => 1,
        'subcategory_id' => 1,
        'folder_id' => 9,
        'folder_name' => 'MSK Reports',
        'dash_id' => '81bd2b0a-ed50-454a-a3e1-5098b2acfc50/page/O7grF',
        'title' => 'MSK MED/PHARMA Analysis'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => 1,
        'client_primary_id' => 1,
        'dashboard_id' => 9,
        'sub_dashboard_id' => '81bd2b0a-ed50-454a-a3e1-5098b2acfc50/page/O7grF',
        'looker_dash_id' => '81bd2b0a-ed50-454a-a3e1-5098b2acfc50/page/O7grF'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => 1,
        'client_id' => 1,
        'client_name' => 'Demo',
        'category_id' => 1,
        'subcategory_id' => 1,
        'folder_id' => 9,
        'folder_name' => 'MSK Reports',
        'dash_id' => 'e6f97bb9-c053-4c83-b84f-d0e9b89b31c0/page/O7grF',
        'title' => 'Shoulder ICD Codes Analysis'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => 1,
        'client_primary_id' => 1,
        'dashboard_id' => 9,
        'sub_dashboard_id' => 'e6f97bb9-c053-4c83-b84f-d0e9b89b31c0/page/O7grF',
        'looker_dash_id' => 'e6f97bb9-c053-4c83-b84f-d0e9b89b31c0/page/O7grF'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => 1,
        'client_id' => 1,
        'client_name' => 'Demo',
        'category_id' => 1,
        'subcategory_id' => 1,
        'folder_id' => 9,
        'folder_name' => 'MSK Reports',
        'dash_id' => 'e5b9770f-7bdd-44b4-9120-ce576216b631/page/n5xrF',
        'title' => 'Spine ICD Codes Analysis'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => 1,
        'client_primary_id' => 1,
        'dashboard_id' => 9,
        'sub_dashboard_id' => 'e5b9770f-7bdd-44b4-9120-ce576216b631/page/n5xrF',
        'looker_dash_id' => 'e5b9770f-7bdd-44b4-9120-ce576216b631/page/n5xrF'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => 1,
        'client_id' => 1,
        'client_name' => 'Demo',
        'category_id' => 1,
        'subcategory_id' => 1,
        'folder_id' => 9,
        'folder_name' => 'MSK Reports',
        'dash_id' => 'c4386014-392e-49e8-ba4d-c9cb69f168b9/page/O7grF',
        'title' => 'TRUE MSK Cost Analysis'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => 1,
        'client_primary_id' => 1,
        'dashboard_id' => 9,
        'sub_dashboard_id' => 'c4386014-392e-49e8-ba4d-c9cb69f168b9/page/O7grF',
        'looker_dash_id' => 'c4386014-392e-49e8-ba4d-c9cb69f168b9/page/O7grF'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => 1,
        'client_id' => 1,
        'client_name' => 'Demo',
        'category_id' => 1,
        'subcategory_id' => 1,
        'folder_id' => 10,
        'folder_name' => 'Operations',
        'dash_id' => '7b758ca9-c8d5-4a88-867b-b154ec0d5b5d/page/QPMrF',
        'title' => 'Eligibility History Report'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => 1,
        'client_primary_id' => 1,
        'dashboard_id' => 10,
        'sub_dashboard_id' => '7b758ca9-c8d5-4a88-867b-b154ec0d5b5d/page/QPMrF',
        'looker_dash_id' => '7b758ca9-c8d5-4a88-867b-b154ec0d5b5d/page/QPMrF'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => 1,
        'client_id' => 1,
        'client_name' => 'Demo',
        'category_id' => 1,
        'subcategory_id' => 1,
        'folder_id' => 10,
        'folder_name' => 'Operations',
        'dash_id' => '16986e3c-13e1-4318-8845-a333bcbfdd5d/page/aVxqF',
        'title' => 'Referral Data - Demographic information'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => 1,
        'client_primary_id' => 1,
        'dashboard_id' => 10,
        'sub_dashboard_id' => '16986e3c-13e1-4318-8845-a333bcbfdd5d/page/aVxqF',
        'looker_dash_id' => '16986e3c-13e1-4318-8845-a333bcbfdd5d/page/aVxqF'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => 1,
        'client_id' => 1,
        'client_name' => 'Demo',
        'category_id' => 1,
        'subcategory_id' => 1,
        'folder_id' => 11,
        'folder_name' => 'Pharmacy Reports',
        'dash_id' => '71d2743e-7002-4480-976c-262b83e22e08/page/uqgrF',
        'title' => 'Drug Class (Member-Level Analysis)'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => 1,
        'client_primary_id' => 1,
        'dashboard_id' => 11,
        'sub_dashboard_id' => '71d2743e-7002-4480-976c-262b83e22e08/page/uqgrF',
        'looker_dash_id' => '71d2743e-7002-4480-976c-262b83e22e08/page/uqgrF'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => 1,
        'client_id' => 1,
        'client_name' => 'Demo',
        'category_id' => 1,
        'subcategory_id' => 1,
        'folder_id' => 11,
        'folder_name' => 'Pharmacy Reports',
        'dash_id' => 'a19b0a75-102f-4f0e-a70a-d393e86422a4/page/5HYrF',
        'title' => 'Drug Class Summary'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => 1,
        'client_primary_id' => 1,
        'dashboard_id' => 11,
        'sub_dashboard_id' => 'a19b0a75-102f-4f0e-a70a-d393e86422a4/page/5HYrF',
        'looker_dash_id' => 'a19b0a75-102f-4f0e-a70a-d393e86422a4/page/5HYrF'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => 1,
        'client_id' => 1,
        'client_name' => 'Demo',
        'category_id' => 1,
        'subcategory_id' => 1,
        'folder_id' => 11,
        'folder_name' => 'Pharmacy Reports',
        'dash_id' => '90e4a1d7-e3a7-4b8a-8f56-be45cff395ce',
        'title' => 'Medication Compliance Summary'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => 1,
        'client_primary_id' => 1,
        'dashboard_id' => 11,
        'sub_dashboard_id' => '90e4a1d7-e3a7-4b8a-8f56-be45cff395ce',
        'looker_dash_id' => '90e4a1d7-e3a7-4b8a-8f56-be45cff395ce'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => 1,
        'client_id' => 1,
        'client_name' => 'Demo',
        'category_id' => 1,
        'subcategory_id' => 1,
        'folder_id' => 11,
        'folder_name' => 'Pharmacy Reports',
        'dash_id' => '343d5047-005d-448f-8602-fa69251642b4/page/laxrF',
        'title' => 'Pharmacy Claims Overview'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => 1,
        'client_primary_id' => 1,
        'dashboard_id' => 11,
        'sub_dashboard_id' => '343d5047-005d-448f-8602-fa69251642b4/page/laxrF',
        'looker_dash_id' => '343d5047-005d-448f-8602-fa69251642b4/page/laxrF'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => 1,
        'client_id' => 1,
        'client_name' => 'Demo',
        'category_id' => 1,
        'subcategory_id' => 1,
        'folder_id' => 11,
        'folder_name' => 'Pharmacy Reports',
        'dash_id' => '596c34ed-f288-4368-bcbe-ed0f41a59a3a/page/bCEqF',
        'title' => 'Proportion of Days Covered (Member-Level Summary)'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => 1,
        'client_primary_id' => 1,
        'dashboard_id' => 11,
        'sub_dashboard_id' => '596c34ed-f288-4368-bcbe-ed0f41a59a3a/page/bCEqF',
        'looker_dash_id' => '596c34ed-f288-4368-bcbe-ed0f41a59a3a/page/bCEqF'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => 1,
        'client_id' => 1,
        'client_name' => 'Demo',
        'category_id' => 1,
        'subcategory_id' => 1,
        'folder_id' => 12,
        'folder_name' => 'Risk Groups',
        'dash_id' => 'ae9843b1-4d25-4119-8038-9acf1c1ab235/page/8yZrF',
        'title' => 'Risk Groups (Member-Level Analysis)'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => 1,
        'client_primary_id' => 1,
        'dashboard_id' => 12,
        'sub_dashboard_id' => 'ae9843b1-4d25-4119-8038-9acf1c1ab235/page/8yZrF',
        'looker_dash_id' => 'ae9843b1-4d25-4119-8038-9acf1c1ab235/page/8yZrF'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => 1,
        'client_id' => 1,
        'client_name' => 'Demo',
        'category_id' => 1,
        'subcategory_id' => 1,
        'folder_id' => 12,
        'folder_name' => 'Risk Groups',
        'dash_id' => '064898c2-6d58-4e31-8556-8befbd5f21e1/page/kIFrF',
        'title' => 'Risk Groups Migration (Detailed Analysis)'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => 1,
        'client_primary_id' => 1,
        'dashboard_id' => 12,
        'sub_dashboard_id' => '064898c2-6d58-4e31-8556-8befbd5f21e1/page/kIFrF',
        'looker_dash_id' => '064898c2-6d58-4e31-8556-8befbd5f21e1/page/kIFrF'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => 1,
        'client_id' => 1,
        'client_name' => 'Demo',
        'category_id' => 1,
        'subcategory_id' => 1,
        'folder_id' => 12,
        'folder_name' => 'Risk Groups',
        'dash_id' => '5e63f667-efd6-4277-ae67-32d219a74516/page/mJprF',
        'title' => 'Risk Groups Migration (Summary)'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => 1,
        'client_primary_id' => 1,
        'dashboard_id' => 12,
        'sub_dashboard_id' => '5e63f667-efd6-4277-ae67-32d219a74516/page/mJprF',
        'looker_dash_id' => '5e63f667-efd6-4277-ae67-32d219a74516/page/mJprF'
    ]);
    DB::table('looker_data')->insert([
        'client_primary_id' => 1,
        'client_id' => 1,
        'client_name' => 'Demo',
        'category_id' => 1,
        'subcategory_id' => 1,
        'folder_id' => 12,
        'folder_name' => 'Risk Groups',
        'dash_id' => '73c33d5c-576f-47fe-845b-892af7a851da/page/vFirF',
        'title' => 'Risk Groups Stratification Overview'
    ]);
    DB::table('users_dasboards_mapping')->insert([
        'grp_usr_mapping_id' => 1,
        'client_primary_id' => 1,
        'dashboard_id' => 12,
        'sub_dashboard_id' => '73c33d5c-576f-47fe-845b-892af7a851da/page/vFirF',
        'looker_dash_id' => '73c33d5c-576f-47fe-845b-892af7a851da/page/vFirF'
    ]);
    echo "Inserted 77 dashboards across 12 folders...\n";
    echo "Done.\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}