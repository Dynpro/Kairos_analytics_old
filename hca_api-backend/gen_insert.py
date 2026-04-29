import re

# Read mapping from older script
with open('repopulate_ordered_v2.php', 'r') as f:
    php_content = f.read()

blocks = re.findall(r"'folder_id' => (\d+),\s*'folder_name' => '(.*?)',\s*'dash_id' => '.*?',\s*'title' => '(.*?)'", php_content)
mapping = {title: (int(fid), fname) for fid, fname, title in blocks}

results = [
    ("Medical & Pharmacy Claims Summary", "b8c07a7a-32db-4e62-8c43-c8d026f24198"),
    ("Preventive Claims Summary", "s/ltoHxOaGM9Y"),
    ("Medical summary - Care Coordination", "s/nrXqnsahTbA"),
    ("Diagnostic Category Summary", "s/mEGINX8eOXM"),
    ("Lifestyle Modifiable & Preventive Summary", "s/qu8LHQQoGRQ"),
    ("Chronic Conditions Summary", "a42d2340-3018-4a5e-9155-6e29af389bf6"),
    ("Demographic & Claims Summary", "s/uopjReUhoCQ"),
    ("Total Lost Days Summary", "ad1f3cb4-cff1-4e05-8d9f-bc7daba14234/page/c0DrF"),
    ("Overall Population Demographic Summary", "0334398c-0f7d-4e3a-9481-1824b8d34ad7/page/HqCrF"),
    ("Members with Claims above Average Paid Amount", "142ac897-91ad-4e2d-9e4c-f8904410da55"),
    ("Ad Hoc Query Tool", "cdd12ab1-e2bf-4cee-b09f-226307ad758d"),
    ("Ad Hoc Query Tool 2.0", "6f2243e3-016d-48b0-a7e8-aba8b78f2969"),
    ("Medical MSK - Overall Demographic & Economic Insights", "94febcad-6fc2-42c2-93ca-7bdc63412f16"),
    ("Medical MSK - Work Related Disorders", "30854a03-9a10-43a7-aa7b-c31f7d8b0136/page/a5YrF"),
    ("Medical MSK - Provider Insights", "e187b01a-888a-4f91-9997-2a53ef6460ce"),
    ("MSK MED/PHARMA Analysis", "81bd2b0a-ed50-454a-a3e1-5098b2acfc50"),
    ("Medical MSK - Productivity and Absenteeism Insights", "38def230-e743-48df-bb7f-761e7adbc89d"),
    ("TRUE MSK Cost Analysis", "c4386014-392e-49e8-ba4d-c9cb69f168b9"),
    ("Hip ICD Codes Analysis", "e8d44de7-523d-42bb-9768-36cd43daab40"),
    ("Knee ICD Codes Analysis", "48ad9390-6e83-4437-8f26-c02cbebcb978"),
    ("Shoulder ICD Codes Analysis", "e6f97bb9-c053-4c83-b84f-d0e9b89b31c0"),
    ("Spine ICD Codes Analysis", "e5b9770f-7bdd-44b4-9120-ce576216b631"),
    ("MRS Modifiable ICD Codes - Overall Analysis", "90f0e070-9b11-4034-8344-513e30d7ccd3/page/7RStF"),
    ("Heart Disease - Medical & Pharmacy Claims Summary", "fc4e190a-1dc0-4911-8994-95b8d99dbe1e"),
    ("Heart Disease - Demographic & Economic Insights", "af862975-cca2-4457-8769-23570d13f40f"),
    ("Hypertension - Medical & Pharmacy Claims Summary", "c5c9ece6-34af-46cd-96f6-57080cc988e3/page/N26rF"),
    ("Hypertension - Demographic & Economic Insights", "48eb81f4-b1ef-4053-b160-2e3e7e443d03/page/dAyrF"),
    ("Diabetes - Medical & Pharmacy Claims Summary", "bb19f877-be8d-46d5-8f0a-9972afe8151f"),
    ("Diabetes - Demographic & Economic Insights", "cd86b02c-55a4-44d1-a635-804848a969ed"),
    ("Medication Compliance Summary", "90e4a1d7-e3a7-4b8a-8f56-be45cff395ce"),
    ("Pharmacy Claims Overview", "343d5047-005d-448f-8602-fa69251642b4"),
    ("Drug Class (Member-Level Analysis)", "71d2743e-7002-4480-976c-262b83e22e08/page/uqgrF"),
    ("Drug Class Summary", "a19b0a75-102f-4f0e-a70a-d393e86422a4/page/5HYrF"),
    ("Proportion of Days Covered (Member-Level Summary)", "596c34ed-f288-4368-bcbe-ed0f41a59a3a"),
    ("Risk Groups Stratification Overview", "73c33d5c-576f-47fe-845b-892af7a851da"),
    ("Risk Groups (Member-Level Analysis)", "ae9843b1-4d25-4119-8038-9acf1c1ab235"),
    ("Risk Groups Migration (Detailed Analysis)", "3e1b27d4-53b4-4b82-8259-b56c03a41cd2"),
    ("Risk Groups Migration (Summary)", "bc7edbc6-584a-4c98-875e-467505a95819"),
    ("Cohort Analysis (Compare 2 Groups)", "c147daa4-623c-4ed2-9d0a-cdacdf5624a4"),
    ("Cancer - Preventive Screening Compliance", "d97ff5be-233c-4c67-ac0a-ccb4b6a44f33"),
    ("Diabetes - Evidence-Based Rules Compliance", "23ffd42b-3f34-4da9-a440-b6b44f243baa"),
    ("Evidence-Based Rules Compliance (Member-Level Analysis)", "5ad3029b-4cc1-49b4-b455-76632543b112/page/uKjrF"),
    ("Additional Risk Factors (Member-Level Analysis)", "f141a4d4-4ec4-4334-b4b3-4fc8257595a2"),
    ("Overall Compliance to Evidence-Based Rules - Percentage", "0fb3cdff-6fee-4baf-a3fa-8178b009ad09"),
    ("Evidence-Based Rules Compliance Summary", "23558699-9a01-4645-b048-4f7a5815ecf0"),
    ("Preventive Screening Compliance (Member-Level Analysis)", "0fb3cdff-6fee-4baf-a3fa-8178b009ad09"),
    ("Demographic & Claims Summary", "7c416038-6a2a-4871-bf0f-59dfb862f0ff"),
    ("Chronic Conditions Summary", "06081a4e-d5c3-4b5a-ac29-4640fcad03a4/page/LD3pF"),
    ("Claims Analysis Summary (Filter by Calendar Year)", "40a282cd-a638-4d9d-a118-1e8bb3177fcf/page/VxqrF"),
    ("Quarterly Report", "4a99e41e-559d-4ecc-af28-4d20df8b081e"),
    ("Members with Claims above Average Paid Amount", "dd47879a-9083-4ef7-a32a-40c098f026ec"),
    ("Overall Population Demographic Summary", "0334398c-0f7d-4e3a-9481-1824b8d34ad7/page/HqCrF"),
    ("Claims Analysis Summary (Filter by Plan Year)", "79d48e3b-2b09-4d39-a446-5ff85e4d6eed/page/DsorF"),
    ("Referral List (New Eligible Members)", "7267098f-c2c1-4d2e-a89a-eebb14bfdfba/page/OxYrF"),
    ("Referral List (All Members)", "e64461ae-904c-4858-9c46-96416cd74629/page/W2ErF"),
    ("Monthly Report - Summary", "e8cc9825-ac8e-4be6-95eb-1c0715cb7a09/page/WivqF"),
    ("Monthly Report - Member Data", "54ad8fd2-2aeb-44a5-baef-2f0d8c5e8083/page/Se3qF"),
    ("Executive Summary Report", "47063557-d4ce-4759-a35a-f3ad48e7fc3d/page/q2frF"),
    ("Executive Summary Report NEW", "a48e31f8-1694-48cf-ad9d-e345b4088daf/page/q2frF"),
    ("Member Summary (Additional Details)", "e5b4dcb1-b170-44e8-9ffd-a929cf3d2ee9/page/TRCrF"),
    ("Risk Groups Migration (Detailed Analysis)", "064898c2-6d58-4e31-8556-8befbd5f21e1/page/kIFrF"),
    ("Member Data Summary (Filter by Plan Year)", "73c06507-70a6-45bc-a7fa-17e467109d4b/page/xzYrF"),
    ("Chronic Conditions Summary", "6b044f85-1007-4818-b53a-f8fb56b61844"),
    ("Diabetes - Evidence-Based Rules Compliance", "3fe2bdf3-c121-4e19-9f36-dfdaf1648831"),
    ("Heart Disease - Demographic & Economic Insights", "69ea3c08-c9d6-4931-9555-1f841b8d907a"),
    ("Hypertension - Demographic & Economic Insights", "adbe15cb-290e-462f-ab97-2322c6a121ee"),
    ("Member Data Summary (Filter by Calendar Year)", "s/pNyj_yFAdrk"),
    ("Eligibility History Report", "s/uaMYQ7CmzVY"),
    ("Referral Data - Demographic information", "16986e3c-13e1-4318-8845-a333bcbfdd5d"),
    ("All Disease Variable Trend", "bfec70c9-7cb1-4854-9a1c-b656e9b9382d"),
    ("Health Score & Risk Group Overview", "3a49c605-dbf8-41c1-b1dd-a85973ddfb37/page/4wvqF"),
    ("Health Score Decile & Quartile Analysis", "f0be7979-521b-43bb-b759-6d7f737ffd3b"),
    ("Data Science Overview", "51d47db0-d0b9-480d-a45f-c01df0c8488e/page/eNoqF"),
    ("Data Science Predictive Analysis (Overview & Member-Level Analysis)", "8758e0a2-e25e-46be-afee-841ae094954c"),
    ("Health Score Summary", "4a91a614-142d-4415-9b2f-6eb3ba9dc20e"),
    ("Health Score (Member-Level Analysis)", "e4940603-026a-4f36-87b0-fb6b8939ba1d/page/QmpqF"),
    ("Spend Analysis", "e58aba4a-2ff1-4b38-8d0b-4677eecafe72"),
    ("Evidence-Based Rules Compliance Statistical Analysis", "6e0e34b2-0f08-4ca2-8a77-80fe0891ccb8")
]

print("<?php")
print("require_once __DIR__.'/bootstrap/app.php';")
print("use Illuminate\\Support\\Facades\\DB;")
print("try {")
print("    echo \"Cleaning existing mappings...\\n\";")
print("    DB::statement('TRUNCATE TABLE looker_data');")
print("    DB::statement('TRUNCATE TABLE users_dasboards_mapping');")

client_primary_id = 1
client_id = 1
client_name = 'Demo'
category_id = 1
subcategory_id = 1
grp_usr_mapping_id = 1

print(f"    echo \"Inserting {len(results)} dashboards...\\n\";")

for title, dash_id in results:
    safe_title = title.replace("'", "\\'")
    
    if title in mapping:
        folder_id, folder_name = mapping[title]
    else:
        folder_id = 100
        folder_name = 'KAIROS.Main Dashboards'
        
    safe_folder_name = folder_name.replace("'", "\\'")
        
    print(f"    DB::table('looker_data')->insert([")
    print(f"        'client_primary_id' => {client_primary_id},")
    print(f"        'client_id' => {client_id},")
    print(f"        'client_name' => '{client_name}',")
    print(f"        'category_id' => {category_id},")
    print(f"        'subcategory_id' => {subcategory_id},")
    print(f"        'folder_id' => {folder_id},")
    print(f"        'folder_name' => '{safe_folder_name}',")
    print(f"        'dash_id' => '{dash_id}',")
    print(f"        'title' => '{safe_title}'")
    print(f"    ]);")
    
    print(f"    DB::table('users_dasboards_mapping')->insert([")
    print(f"        'grp_usr_mapping_id' => {grp_usr_mapping_id},")
    print(f"        'client_primary_id' => {client_primary_id},")
    print(f"        'dashboard_id' => {folder_id},")
    print(f"        'sub_dashboard_id' => '{dash_id}',")
    print(f"        'looker_dash_id' => '{dash_id}'")
    print(f"    ]);")

print("    echo \"Done.\\n\";")
print("} catch (\\Exception $e) {")
print("    echo \"Error: \" . $e->getMessage() . \"\\n\";")
print("}")