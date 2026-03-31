import json

structure = {
    "Care Coordination (Evidence-Based Rules)": [
        "Additional Risk Factors (Member-Level Analysis)",
        "Cancer - Preventive Screening Compliance",
        "Diabetes - Evidence-Based Rules Compliance",
        "Evidence-Based Rules Compliance (Member-Level Analysis)",
        "Evidence-Based Rules Compliance Summary",
        "Overall Compliance to Evidence-Based Rules - Percentage",
        "Preventive Screening Compliance (Member-Level Analysis)"
    ],
    "Chronic Condition Reports": [
        "Diabetes - Demographic & Economic Insights",
        "Diabetes - Medical & Pharmacy Claims Summary",
        "Heart Disease - Demographic & Economic Insights",
        "Heart Disease - Medical & Pharmacy Claims Summary",
        "Hypertension - Demographic & Economic Insights",
        "Hypertension - Medical & Pharmacy Claims Summary"
    ],
    "Client Services": [
        "Chronic Conditions Summary",
        "Claims Analysis Summary (Filter by Calendar Year)",
        "Claims Analysis Summary (Filter by Plan Year)",
        "Demographic & Claims Summary",
        "Members with Claims above Average Paid Amount",
        "Monthly Report - Member Data",
        "Monthly Report - Summary",
        "Overall Population Demographic Summary",
        "Quarterly Report",
        "Referral List (All Members)",
        "Referral List (New Eligible Members)"
    ],
    "Clinical": [
        "Chronic Conditions Summary",
        "Diabetes - Evidence-Based Rules Compliance",
        "Heart Disease - Demographic & Economic Insights",
        "Hypertension - Demographic & Economic Insights",
        "Member Data Summary (Filter by Calendar Year)",
        "Member Data Summary (Filter by Plan Year)",
        "Member Summary (Additional Details)",
        "Risk Groups Migration (Detailed Analysis)"
    ],
    "Cohort Analysis": [
        "Cohort Analysis (Compare 2 Groups)"
    ],
    "Executive Summary Report": [
        "Executive Summary Report",
        "Executive Summary Report NEW"
    ],
    "Health Score & Predictive Reports": [
        "All Disease Variable Trend",
        "Data Science Overview",
        "Data Science Predictive Analysis (Overview & Member-Level Analysis)",
        "Evidence-Based Rules Compliance Statistical Analysis",
        "Health Score (Member-Level Analysis)",
        "Health Score & Risk Group Overview",
        "Health Score Decile & Quartile Analysis",
        "Health Score Summary",
        "Spend Analysis"
    ],
    "Medical Reports": [
        "Ad Hoc Query Tool",
        "Ad Hoc Query Tool 2.0",
        "Chronic Conditions Summary",
        "Demographic & Claims Summary",
        "Diagnostic Category Summary",
        "Lifestyle Modifiable & Preventive Summary",
        "Medical summary - Care Coordination",
        "Members with Claims above Average Paid Amount",
        "Overall Population Demographic Summary",
        "Preventive Claims Summary",
        "Total Lost Days Summary"
    ],
    "MSK Reports": [
        "Hip ICD Codes Analysis",
        "Knee ICD Codes Analysis",
        "Medical MSK - Overall Demographic & Economic Insights",
        "Medical MSK - Productivity and Absenteeism Insights",
        "Medical MSK - Provider Insights",
        "Medical MSK - Work Related Disorders",
        "MRS Modifiable ICD Codes - Overall Analysis",
        "MSK MED/PHARMA Analysis",
        "Shoulder ICD Codes Analysis",
        "Spine ICD Codes Analysis",
        "TRUE MSK Cost Analysis"
    ],
    "Operations": [
        "Eligibility History Report",
        "Referral Data - Demographic information"
    ],
    "Pharmacy Reports": [
        "Drug Class (Member-Level Analysis)",
        "Drug Class Summary",
        "Medication Compliance Summary",
        "Pharmacy Claims Overview",
        "Proportion of Days Covered (Member-Level Summary)"
    ],
    "Risk Groups": [
        "Risk Groups (Member-Level Analysis)",
        "Risk Groups Migration (Detailed Analysis)",
        "Risk Groups Migration (Summary)",
        "Risk Groups Stratification Overview"
    ]
}

dashboards_list = [
  {
    "title": "Medical & Pharmacy Claims Summary",
    "url": "https://lookerstudio.google.com/reporting/b8c07a7a-32db-4e62-8c43-c8d026f24198"
  },
  {
    "title": "Preventive Claims Summary",
    "url": "https://lookerstudio.google.com/reporting/d0afceba-9d8b-4015-8e3a-f66c8e8da421?s=ltoHxOaGM9Y"
  },
  {
    "title": "Medical summary - Care Coordination",
    "url": "https://lookerstudio.google.com/reporting/cb34fc42-7e4d-4543-aaed-4c927851650a/page/6lYrF?s=nrXqnsahTbA"
  },
  {
    "title": "Diagnostic Category Summary",
    "url": "https://lookerstudio.google.com/reporting/039a2081-96d4-4b1c-b5b9-838179aca217?s=mEGINX8eOXM"
  },
  {
    "title": "Lifestyle Modifiable & Preventive Summary",
    "url": "https://lookerstudio.google.com/reporting/ce22cf0a-e84f-45a6-8e17-467df3a3f7b5?s=qu8LHQQoGRQ"
  },
  {
    "title": "Chronic Conditions Summary",
    "url": "https://lookerstudio.google.com/reporting/15dcf5bb-5bb0-4ac6-855e-eac456e454b8"
  },
  {
    "title": "Demographic & Claims Summary",
    "url": "https://lookerstudio.google.com/reporting/15dcf5bb-5bb0-4ac6-855e-eac456e454b8"
  },
  {
    "title": "Total Lost Days Summary",
    "url": "https://lookerstudio.google.com/embed/reporting/ad1f3cb4-cff1-4e05-8d9f-bc7daba14234/page/c0DrF"
  },
  {
    "title": "Overall Population Demographic Summary",
    "url": "https://lookerstudio.google.com/embed/reporting/0334398c-0f7d-4e3a-9481-1824b8d34ad7/page/HqCrF"
  },
  {
    "title": "Members with Claims above Average Paid Amount",
    "url": "https://lookerstudio.google.com/reporting/142ac897-91ad-4e2d-9e4c-f8904410da55"
  },
  {
    "title": "Ad Hoc Query Tool",
    "url": "https://lookerstudio.google.com/reporting/cdd12ab1-e2bf-4cee-b09f-226307ad758d"
  },
  {
    "title": "Ad Hoc Query Tool 2.0",
    "url": "https://lookerstudio.google.com/reporting/6f2243e3-016d-48b0-a7e8-aba8b78f2969"
  },
  {
    "title": "Medical MSK - Overall Demographic & Economic Insights",
    "url": "https://lookerstudio.google.com/reporting/94febcad-6fc2-42c2-93ca-7bdc63412f16"
  },
  {
    "title": "Medical MSK - Work Related Disorders",
    "url": "https://lookerstudio.google.com/embed/reporting/30854a03-9a10-43a7-aa7b-c31f7d8b0136/page/a5YrF"
  },
  {
    "title": "Medical MSK - Provider Insights",
    "url": "https://lookerstudio.google.com/reporting/e187b01a-888a-4f91-9997-2a53ef6460ce"
  },
  {
    "title": "MSK MED/PHARMA Analysis",
    "url": "https://lookerstudio.google.com/reporting/81bd2b0a-ed50-454a-a3e1-5098b2acfc50/page/O7grF"
  },
  {
    "title": "Medical MSK - Productivity and Absenteeism Insights",
    "url": "https://lookerstudio.google.com/reporting/38def230-e743-48df-bb7f-761e7adbc89d"
  },
  {
    "title": "TRUE MSK Cost Analysis",
    "url": "https://lookerstudio.google.com/reporting/c4386014-392e-49e8-ba4d-c9cb69f168b9"
  },
  {
    "title": "Hip ICD Codes Analysis",
    "url": "https://lookerstudio.google.com/reporting/e8d44de7-523d-42bb-9768-36cd43daab40"
  },
  {
    "title": "Knee ICD Codes Analysis",
    "url": "https://lookerstudio.google.com/reporting/48ad9390-6e83-4437-8f26-c02cbebcb978"
  },
  {
    "title": "Shoulder ICD Codes Analysis",
    "url": "https://lookerstudio.google.com/reporting/e6f97bb9-c053-4c83-b84f-d0e9b89b31c0"
  },
  {
    "title": "Spine ICD Codes Analysis",
    "url": "https://lookerstudio.google.com/reporting/e5b9770f-7bdd-44b4-9120-ce576216b631"
  },
  {
    "title": "MRS Modifiable ICD Codes - Overall Analysis",
    "url": "https://lookerstudio.google.com/reporting/90f0e070-9b11-4034-8344-513e30d7ccd3/page/7RStF"
  },
  {
    "title": "Heart Disease - Medical & Pharmacy Claims Summary",
    "url": "https://lookerstudio.google.com/reporting/fc4e190a-1dc0-4911-8994-95b8d99dbe1e"
  },
  {
    "title": "Heart Disease - Demographic & Economic Insights",
    "url": "https://lookerstudio.google.com/reporting/af862975-cca2-4457-8769-23570d13f40f"
  },
  {
    "title": "Hypertension - Medical & Pharmacy Claims Summary",
    "url": "https://lookerstudio.google.com/embed/reporting/c5c9ece6-34af-46cd-96f6-57080cc988e3/page/N26rF"
  },
  {
    "title": "Hypertension - Demographic & Economic Insights",
    "url": "https://lookerstudio.google.com/embed/reporting/48eb81f4-b1ef-4053-b160-2e3e7e443d03/page/dAyrF"
  },
  {
    "title": "Diabetes - Medical & Pharmacy Claims Summary",
    "url": "https://lookerstudio.google.com/reporting/bb19f877-be8d-46d5-8f0a-9972afe8151f/page/ALxqF"
  },
  {
    "title": "Diabetes - Demographic & Economic Insights",
    "url": "https://lookerstudio.google.com/reporting/cd86b02c-55a4-44d1-a635-804848a969ed/page/qmSsF"
  },
  {
    "title": "Medication Compliance Summary",
    "url": "https://lookerstudio.google.com/reporting/90e4a1d7-e3a7-4b8a-8f56-be45cff395ce"
  },
  {
    "title": "Pharmacy Claims Overview",
    "url": "https://lookerstudio.google.com/reporting/343d5047-005d-448f-8602-fa69251642b4/page/laxrF"
  },
  {
    "title": "Drug Class (Member-Level Analysis)",
    "url": "https://lookerstudio.google.com/embed/reporting/71d2743e-7002-4480-976c-262b83e22e08/page/uqgrF"
  },
  {
    "title": "Drug Class Summary",
    "url": "https://lookerstudio.google.com/embed/reporting/a19b0a75-102f-4f0e-a70a-d393e86422a4/page/5HYrF"
  },
  {
    "title": "Proportion of Days Covered (Member-Level Summary)",
    "url": "https://lookerstudio.google.com/reporting/596c34ed-f288-4368-bcbe-ed0f41a59a3a"
  },
  {
    "title": "Risk Groups Stratification Overview",
    "url": "https://lookerstudio.google.com/reporting/73c33d5c-576f-47fe-845b-892af7a851da"
  },
  {
    "title": "Risk Groups (Member-Level Analysis)",
    "url": "https://lookerstudio.google.com/reporting/ae9843b1-4d25-4119-8038-9acf1c1ab235"
  },
  {
    "title": "Risk Groups Migration (Detailed Analysis)",
    "url": "https://lookerstudio.google.com/reporting/3e1b27d4-53b4-4b82-8259-b56c03a41cd2/page/277sF"
  },
  {
    "title": "Risk Groups Migration (Summary)",
    "url": "https://lookerstudio.google.com/embed/reporting/5e63f667-efd6-4277-ae67-32d219a74516/page/mJprF"
  },
  {
    "title": "Cohort Analysis (Compare 2 Groups)",
    "url": "https://lookerstudio.google.com/reporting/c147daa4-623c-4ed2-9d0a-cdacdf5624a4/page/jKbtF"
  },
  {
    "title": "Cancer - Preventive Screening Compliance",
    "url": "https://lookerstudio.google.com/reporting/d97ff5be-233c-4c67-ac0a-ccb4b6a44f33"
  },
  {
    "title": "Diabetes - Evidence-Based Rules Compliance",
    "url": "https://lookerstudio.google.com/reporting/23ffd42b-3f34-4da9-a440-b6b44f243baa"
  },
  {
    "title": "Evidence-Based Rules Compliance (Member-Level Analysis)",
    "url": "https://lookerstudio.google.com/embed/reporting/5ad3029b-4cc1-49b4-b455-76632543b112/page/uKjrF"
  },
  {
    "title": "Additional Risk Factors (Member-Level Analysis)",
    "url": "https://lookerstudio.google.com/reporting/f141a4d4-4ec4-4334-b4b3-4fc8257595a2/page/BwhrF"
  },
  {
    "title": "Overall Compliance to Evidence-Based Rules - Percentage",
    "url": "https://lookerstudio.google.com/reporting/0fb3cdff-6fee-4baf-a3fa-8178b009ad09/page/LlcrF"
  },
  {
    "title": "Evidence-Based Rules Compliance Summary",
    "url": "https://lookerstudio.google.com/reporting/23558699-9a01-4645-b048-4f7a5815ecf0/page/6uZrF"
  },
  {
    "title": "Preventive Screening Compliance (Member-Level Analysis)",
    "url": "https://lookerstudio.google.com/reporting/0fb3cdff-6fee-4baf-a3fa-8178b009ad09/page/LlcrF"
  },
  {
    "title": "Demographic & Claims Summary",
    "url": "https://lookerstudio.google.com/reporting/7c416038-6a2a-4871-bf0f-59dfb862f0ff"
  },
  {
    "title": "Chronic Conditions Summary",
    "url": "https://lookerstudio.google.com/embed/reporting/06081a4e-d5c3-4b5a-ac29-4640fcad03a4/page/LD3pF"
  },
  {
    "title": "Claims Analysis Summary (Filter by Calendar Year)",
    "url": "https://lookerstudio.google.com/embed/reporting/40a282cd-a638-4d9d-a118-1e8bb3177fcf/page/VxqrF"
  },
  {
    "title": "Quarterly Report",
    "url": "https://lookerstudio.google.com/reporting/4a99e41e-559d-4ecc-af28-4d20df8b081e"
  },
  {
    "title": "Members with Claims above Average Paid Amount",
    "url": "https://lookerstudio.google.com/reporting/dd47879a-9083-4ef7-a32a-40c098f026ec"
  },
  {
    "title": "Overall Population Demographic Summary",
    "url": "https://lookerstudio.google.com/embed/reporting/0334398c-0f7d-4e3a-9481-1824b8d34ad7/page/HqCrF"
  },
  {
    "title": "Claims Analysis Summary (Filter by Plan Year)",
    "url": "https://lookerstudio.google.com/embed/reporting/79d48e3b-2b09-4d39-a446-5ff85e4d6eed/page/DsorF"
  },
  {
    "title": "Referral List (New Eligible Members)",
    "url": "https://lookerstudio.google.com/embed/reporting/7267098f-c2c1-4d2e-a89a-eebb14bfdfba/page/OxYrF"
  },
  {
    "title": "Referral List (All Members)",
    "url": "https://lookerstudio.google.com/embed/reporting/e64461ae-904c-4858-9c46-96416cd74629/page/W2ErF"
  },
  {
    "title": "Monthly Report - Summary",
    "url": "https://lookerstudio.google.com/embed/reporting/e8cc9825-ac8e-4be6-95eb-1c0715cb7a09/page/WivqF"
  },
  {
    "title": "Monthly Report - Member Data",
    "url": "https://lookerstudio.google.com/embed/reporting/54ad8fd2-2aeb-44a5-baef-2f0d8c5e8083/page/Se3qF"
  },
  {
    "title": "Executive Summary Report",
    "url": "https://lookerstudio.google.com/embed/reporting/47063557-d4ce-4759-a35a-f3ad48e7fc3d/page/q2frF"
  },
  {
    "title": "Executive Summary Report NEW",
    "url": "https://lookerstudio.google.com/embed/reporting/38a89819-13fd-4bdb-8798-065d8b192caa/page/q2frF"
  },
  {
    "title": "Member Summary (Additional Details)",
    "url": "https://lookerstudio.google.com/embed/reporting/e5b4dcb1-b170-44e8-9ffd-a929cf3d2ee9/page/TRCrF"
  },
  {
    "title": "Risk Groups Migration (Detailed Analysis)",
    "url": "https://lookerstudio.google.com/embed/reporting/064898c2-6d58-4e31-8556-8befbd5f21e1/page/kIFrF"
  },
  {
    "title": "Member Data Summary (Filter by Plan Year)",
    "url": "https://lookerstudio.google.com/embed/reporting/73c06507-70a6-45bc-a7fa-17e467109d4b/page/xzYrF"
  },
  {
    "title": "Chronic Conditions Summary",
    "url": "https://lookerstudio.google.com/reporting/6b044f85-1007-4818-b53a-f8fb56b61844"
  },
  {
    "title": "Diabetes - Evidence-Based Rules Compliance",
    "url": "https://lookerstudio.google.com/reporting/3fe2bdf3-c121-4e19-9f36-dfdaf1648831"
  },
  {
    "title": "Heart Disease - Demographic & Economic Insights",
    "url": "https://lookerstudio.google.com/reporting/69ea3c08-c9d6-4931-9555-1f841b8d907a"
  },
  {
    "title": "Hypertension - Demographic & Economic Insights",
    "url": "https://lookerstudio.google.com/reporting/adbe15cb-290e-462f-ab97-2322c6a121ee"
  },
  {
    "title": "Member Data Summary (Filter by Calendar Year)",
    "url": "https://lookerstudio.google.com/reporting/adbe15cb-290e-462f-ab97-2322c6a121ee"
  },
  {
    "title": "Eligibility History Report",
    "url": "https://lookerstudio.google.com/reporting/7b758ca9-c8d5-4a88-867b-b154ec0d5b5d/page/QPMrF?s=uaMYQ7CmzVY"
  },
  {
    "title": "Referral Data - Demographic information",
    "url": "https://lookerstudio.google.com/reporting/16986e3c-13e1-4318-8845-a333bcbfdd5d/page/aVxqF"
  },
  {
    "title": "All Disease Variable Trend",
    "url": "https://lookerstudio.google.com/reporting/bfec70c9-7cb1-4854-9a1c-b656e9b9382d"
  },
  {
    "title": "Health Score & Risk Group Overview",
    "url": "https://lookerstudio.google.com/embed/reporting/3a49c605-dbf8-41c1-b1dd-a85973ddfb37/page/4wvqF"
  },
  {
    "title": "Health Score Decile & Quartile Analysis",
    "url": "https://lookerstudio.google.com/reporting/f0be7979-521b-43bb-b759-6d7f737ffd3b/page/hvrrF"
  },
  {
    "title": "Data Science Overview",
    "url": "https://lookerstudio.google.com/embed/reporting/51d47db0-d0b9-480d-a45f-c01df0c8488e/page/eNoqF"
  },
  {
    "title": "Data Science Predictive Analysis (Overview & Member-Level Analysis)",
    "url": "https://lookerstudio.google.com/reporting/8758e0a2-e25e-46be-afee-841ae094954c/page/wQarF"
  },
  {
    "title": "Health Score Summary",
    "url": "https://lookerstudio.google.com/reporting/4a91a614-142d-4415-9b2f-6eb3ba9dc20e/page/vbqrF"
  },
  {
    "title": "Health Score (Member-Level Analysis)",
    "url": "https://lookerstudio.google.com/embed/reporting/f8cb318d-3ed3-4fc2-aacd-07186782f951/page/QmpqF"
  },
  {
    "title": "Spend Analysis",
    "url": "https://lookerstudio.google.com/reporting/e58aba4a-2ff1-4b38-8d0b-4677eecafe72/page/xzorF"
  },
  {
    "title": "Evidence-Based Rules Compliance Statistical Analysis",
    "url": "https://lookerstudio.google.com/reporting/6e0e34b2-0f08-4ca2-8a77-80fe0891ccb8/page/9TQsF"
  }
]

import re
def extract_id(url):
    if '/s/' in url:
        return 's/' + url.split('/s/')[1]
    match = re.search(r'/reporting/([^/]+(/page/[^/]+)?)', url)
    if match:
        return match.group(1)
    return ""

new_ids_map = {d['title']: extract_id(d['url']) for d in dashboards_list}

php_output = []
php_output.append("<?php")
php_output.append("require_once __DIR__.'/bootstrap/app.php';")
php_output.append("use Illuminate\\Support\\Facades\\DB;")
php_output.append("try {")
php_output.append("    echo \"Cleaning existing mappings...\\n\";")
php_output.append("    DB::statement('TRUNCATE TABLE looker_data');")
php_output.append("    DB::statement('TRUNCATE TABLE users_dasboards_mapping');")

client_primary_id = 1
client_id = 1
client_name = 'Demo'
category_id = 1
subcategory_id = 1
grp_usr_mapping_id = 1

folder_id = 1
total_inserted = 0

for folder_name, titles in structure.items():
    safe_folder_name = folder_name.replace("'", "\\'")
    for title in titles:
        safe_title = title.replace("'", "\\'")
        dash_id = new_ids_map.get(title, "")
        
        php_output.append(f"    DB::table('looker_data')->insert([")
        php_output.append(f"        'client_primary_id' => {client_primary_id},")
        php_output.append(f"        'client_id' => {client_id},")
        php_output.append(f"        'client_name' => '{client_name}',")
        php_output.append(f"        'category_id' => {category_id},")
        php_output.append(f"        'subcategory_id' => {subcategory_id},")
        php_output.append(f"        'folder_id' => {folder_id},")
        php_output.append(f"        'folder_name' => '{safe_folder_name}',")
        php_output.append(f"        'dash_id' => '{dash_id}',")
        php_output.append(f"        'title' => '{safe_title}'")
        php_output.append(f"    ]);")
        
        php_output.append(f"    DB::table('users_dasboards_mapping')->insert([")
        php_output.append(f"        'grp_usr_mapping_id' => {grp_usr_mapping_id},")
        php_output.append(f"        'client_primary_id' => {client_primary_id},")
        php_output.append(f"        'dashboard_id' => {folder_id},")
        php_output.append(f"        'sub_dashboard_id' => '{dash_id}',")
        php_output.append(f"        'looker_dash_id' => '{dash_id}'")
        php_output.append(f"    ]);")
        total_inserted += 1
    folder_id += 1

php_output.append(f"    echo \"Inserted {total_inserted} dashboards across {folder_id-1} folders...\\n\";")
php_output.append("    echo \"Done.\\n\";")
php_output.append("} catch (\\Exception $e) {")
php_output.append("    echo \"Error: \" . $e->getMessage() . \"\\n\";")
php_output.append("}")

with open('final_v3.php', 'w') as f:
    f.write("\n".join(php_output))

print("Created final_v3.php")
