<?php

namespace Database\Seeders;

use App\Models\StudioDashboard;
use Illuminate\Database\Seeder;

class StudioDashboardsSeeder extends Seeder
{
    public function run()
    {
        $rows = [
            ['folder' => 'Demo_Test', 'title' => 'Medical & Pharmacy Claims Summary', 'iframe_url' => 'https://lookerstudio.google.com/embed/reporting/b8c07a7a-32db-4e62-8c43-c8d026f24198/page/GdgrF'],
            ['folder' => '01. Medical Reports', 'title' => 'Preventive Claims Summary', 'iframe_url' => 'https://lookerstudio.google.com/reporting/d0afceba-9d8b-4015-8e3a-f66c8e8da421/page/PcrrF?s=ltoHxOaGM9Y'],
            ['folder' => '01. Medical Reports', 'title' => 'Medical summary - Care Coordination', 'iframe_url' => 'https://lookerstudio.google.com/reporting/cb34fc42-7e4d-4543-aaed-4c927851650a/page/6lYrF?s=nrXqnsahTbA'],
            ['folder' => '01. Medical Reports', 'title' => 'Diagnostic Category Summary', 'iframe_url' => 'https://lookerstudio.google.com/reporting/039a2081-96d4-4b1c-b5b9-838179aca217?s=mEGINX8eOXM'],
            ['folder' => '01. Medical Reports', 'title' => 'Lifestyle Modifiable & Preventive Summary', 'iframe_url' => 'https://lookerstudio.google.com/reporting/ce22cf0a-e84f-45a6-8e17-467df3a3f7b5?s=qu8LHQQoGRQ'],
            ['folder' => '01. Medical Reports', 'title' => 'Chronic Conditions Summary', 'iframe_url' => 'https://lookerstudio.google.com/reporting/15dcf5bb-5bb0-4ac6-855e-eac456e454b8/page/SuIsF'],
            ['folder' => '01. Medical Reports', 'title' => 'Demographic & Claims Summary', 'iframe_url' => 'https://lookerstudio.google.com/reporting/ff856ed9-9931-4748-8a69-e2bcb5c25ecc/page/DTErF'],
            ['folder' => '01. Medical Reports', 'title' => 'Total Lost Days Summary', 'iframe_url' => 'https://lookerstudio.google.com/embed/reporting/ad1f3cb4-cff1-4e05-8d9f-bc7daba14234/page/c0DrF'],
            ['folder' => '01. Medical Reports', 'title' => 'Overall Population Demographic Summary', 'iframe_url' => 'https://lookerstudio.google.com/embed/reporting/0334398c-0f7d-4e3a-9481-1824b8d34ad7/page/HqCrF'],
            ['folder' => '01. Medical Reports', 'title' => 'Members with Claims above Average Paid Amount', 'iframe_url' => 'https://lookerstudio.google.com/reporting/142ac897-91ad-4e2d-9e4c-f8904410da55/page/bLNqF'],
            ['folder' => '01. Medical Reports', 'title' => 'Ad Hoc Query Tool', 'iframe_url' => 'https://lookerstudio.google.com/reporting/cdd12ab1-e2bf-4cee-b09f-226307ad758d/page/khDqF'],
            ['folder' => '01. Medical Reports', 'title' => 'Ad Hoc Query Tool 2.0', 'iframe_url' => 'https://lookerstudio.google.com/reporting/6f2243e3-016d-48b0-a7e8-aba8b78f2969/page/O3YrF'],
            ['folder' => '02. MSK Reports', 'title' => 'Medical MSK - Overall Demographic & Economic Insights', 'iframe_url' => 'https://lookerstudio.google.com/reporting/94febcad-6fc2-42c2-93ca-7bdc63412f16/page/DbErF'],
            ['folder' => '02. MSK Reports', 'title' => 'Medical MSK - Work Related Disorders', 'iframe_url' => 'https://lookerstudio.google.com/embed/reporting/30854a03-9a10-43a7-aa7b-c31f7d8b0136/page/a5YrF'],
            ['folder' => '02. MSK Reports', 'title' => 'Medical MSK - Provider Insights', 'iframe_url' => 'https://lookerstudio.google.com/reporting/e187b01a-888a-4f91-9997-2a53ef6460ce/page/CDEqF'],
            ['folder' => '02. MSK Reports', 'title' => 'MSK MED/PHARMA Analysis', 'iframe_url' => 'https://lookerstudio.google.com/reporting/81bd2b0a-ed50-454a-a3e1-5098b2acfc50/page/O7grF'],
            ['folder' => '02. MSK Reports', 'title' => 'Medical MSK - Productivity and Absenteeism Insights', 'iframe_url' => 'https://lookerstudio.google.com/reporting/38def230-e743-48df-bb7f-761e7adbc89d/page/O7grF'],
            ['folder' => '02. MSK Reports', 'title' => 'TRUE MSK Cost Analysis', 'iframe_url' => 'https://lookerstudio.google.com/reporting/c4386014-392e-49e8-ba4d-c9cb69f168b9/page/O7grF'],
            ['folder' => '02. MSK Reports', 'title' => 'Hip ICD Codes Analysis', 'iframe_url' => 'https://lookerstudio.google.com/reporting/e8d44de7-523d-42bb-9768-36cd43daab40/page/O7grF'],
            ['folder' => '02. MSK Reports', 'title' => 'Knee ICD Codes Analysis', 'iframe_url' => 'https://lookerstudio.google.com/reporting/48ad9390-6e83-4437-8f26-c02cbebcb978/page/O7grF'],
            ['folder' => '02. MSK Reports', 'title' => 'Shoulder ICD Codes Analysis', 'iframe_url' => 'https://lookerstudio.google.com/reporting/e6f97bb9-c053-4c83-b84f-d0e9b89b31c0/page/O7grF'],
            ['folder' => '02. MSK Reports', 'title' => 'Spine ICD Codes Analysis', 'iframe_url' => 'https://lookerstudio.google.com/reporting/e5b9770f-7bdd-44b4-9120-ce576216b631/page/n5xrF'],
            ['folder' => '02. MSK Reports', 'title' => 'MRS Modifiable ICD Codes - Overall Analysis', 'iframe_url' => 'https://lookerstudio.google.com/reporting/90f0e070-9b11-4034-8344-513e30d7ccd3/page/tF7RSver'],
            ['folder' => '03. Chronic Condition Reports', 'title' => 'Heart Disease - Medical & Pharmacy Claims Summary', 'iframe_url' => 'https://lookerstudio.google.com/reporting/56378c09-07b3-42fb-b77b-212faeaf0236/page/GdgrF'],
            ['folder' => '03. Chronic Condition Reports', 'title' => 'Heart Disease - Demographic & Economic Insights', 'iframe_url' => 'https://lookerstudio.google.com/reporting/af862975-cca2-4457-8769-23570d13f40f/page/GdgrF'],
            ['folder' => '03. Chronic Condition Reports', 'title' => 'Hypertension - Medical & Pharmacy Claims Summary', 'iframe_url' => 'https://lookerstudio.google.com/embed/reporting/c5c9ece6-34af-46cd-96f6-57080cc988e3/page/N26rF'],
            ['folder' => '03. Chronic Condition Reports', 'title' => 'Hypertension - Demographic & Economic Insights', 'iframe_url' => 'https://lookerstudio.google.com/embed/reporting/48eb81f4-b1ef-4053-b160-2e3e7e443d03/page/dAyrF'],
            ['folder' => '03. Chronic Condition Reports', 'title' => 'Diabetes - Medical & Pharmacy Claims Summary', 'iframe_url' => 'https://lookerstudio.google.com/reporting/bb19f877-be8d-46d5-8f0a-9972afe8151f/page/ALxqF'],
            ['folder' => '03. Chronic Condition Reports', 'title' => 'Diabetes - Demographic & Economic Insights', 'iframe_url' => 'https://lookerstudio.google.com/reporting/cd86b02c-55a4-44d1-a635-804848a969ed/page/qmSsF'],
            ['folder' => '04. Pharmacy Reports', 'title' => 'Medication Compliance Summary', 'iframe_url' => 'https://lookerstudio.google.com/reporting/90e4a1d7-e3a7-4b8a-8f56-be45cff395ce'],
            ['folder' => '04. Pharmacy Reports', 'title' => 'Pharmacy Claims Overview', 'iframe_url' => 'https://lookerstudio.google.com/reporting/343d5047-005d-448f-8602-fa69251642b4/page/laxrF'],
            ['folder' => '04. Pharmacy Reports', 'title' => 'Drug Class (Member-Level Analysis)', 'iframe_url' => 'https://lookerstudio.google.com/embed/reporting/71d2743e-7002-4480-976c-262b83e22e08/page/uqgrF'],
            ['folder' => '04. Pharmacy Reports', 'title' => 'Drug Class Summary', 'iframe_url' => 'https://lookerstudio.google.com/embed/reporting/a19b0a75-102f-4f0e-a70a-d393e86422a4/page/5HYrF'],
            ['folder' => '04. Pharmacy Reports', 'title' => 'Proportion of Days Covered (Member-Level Summary)', 'iframe_url' => 'https://lookerstudio.google.com/reporting/596c34ed-f288-4368-bcbe-ed0f41a59a3a/page/bCEqF'],
            ['folder' => '05. Risk Groups', 'title' => 'Risk Groups Stratification Overview', 'iframe_url' => 'https://lookerstudio.google.com/reporting/73c33d5c-576f-47fe-845b-892af7a851da/page/vFirF'],
            ['folder' => '05. Risk Groups', 'title' => 'Risk Groups (Member-Level Analysis)', 'iframe_url' => 'https://lookerstudio.google.com/reporting/ae9843b1-4d25-4119-8038-9acf1c1ab235/page/8yZrF'],
            ['folder' => '05. Risk Groups', 'title' => 'Risk Groups Migration (Detailed Analysis)', 'iframe_url' => 'https://lookerstudio.google.com/reporting/3e1b27d4-53b4-4b82-8259-b56c03a41cd2/page/277sF'],
            ['folder' => '05. Risk Groups', 'title' => 'Risk Groups Migration (Summary)', 'iframe_url' => 'https://lookerstudio.google.com/embed/reporting/5e63f667-efd6-4277-ae67-32d219a74516/page/mJprF'],
            ['folder' => '06. Cohort Analysis', 'title' => 'Cohort Analysis (Compare 2 Groups)', 'iframe_url' => 'https://lookerstudio.google.com/reporting/c147daa4-623c-4ed2-9d0a-cdacdf5624a4/page/jKbtF'],
            ['folder' => '07. Care Coordination (Evidence-Based Rules)', 'title' => 'Cancer - Preventive Screening Compliance', 'iframe_url' => 'https://lookerstudio.google.com/embed/reporting/d97ff5be-233c-4c67-ac0a-ccb4b6a44f33/page/BfXtF'],
            ['folder' => '07. Care Coordination (Evidence-Based Rules)', 'title' => 'Diabetes - Evidence-Based Rules Compliance', 'iframe_url' => 'https://lookerstudio.google.com/reporting/23ffd42b-3f34-4da9-a440-b6b44f243baa/page/wa6sF'],
            ['folder' => '07. Care Coordination (Evidence-Based Rules)', 'title' => 'Evidence-Based Rules Compliance (Member-Level Analysis)', 'iframe_url' => 'https://lookerstudio.google.com/embed/reporting/5ad3029b-4cc1-49b4-b455-76632543b112/page/uKjrF'],
            ['folder' => '07. Care Coordination (Evidence-Based Rules)', 'title' => 'Additional Risk Factors (Member-Level Analysis)', 'iframe_url' => 'https://lookerstudio.google.com/reporting/f141a4d4-4ec4-4334-b4b3-4fc8257595a2/page/BwhrF'],
            ['folder' => '07. Care Coordination (Evidence-Based Rules)', 'title' => 'Overall Compliance to Evidence-Based Rules - Percentage', 'iframe_url' => 'https://lookerstudio.google.com/reporting/0fb3cdff-6fee-4baf-a3fa-8178b009ad09/page/LlcrF'],
            ['folder' => '07. Care Coordination (Evidence-Based Rules)', 'title' => 'Evidence-Based Rules Compliance Summary', 'iframe_url' => 'https://lookerstudio.google.com/reporting/23558699-9a01-4645-b048-4f7a5815ecf0/page/6uZrF'],
            ['folder' => '07. Care Coordination (Evidence-Based Rules)', 'title' => 'Preventive Screening Compliance (Member-Level Analysis)', 'iframe_url' => 'https://lookerstudio.google.com/reporting/b7d03c29-c802-4cb8-b615-d81ab34f4123/page/8rYrF'],
            ['folder' => '08. Client Services', 'title' => 'Demographic & Claims Summary', 'iframe_url' => 'https://lookerstudio.google.com/reporting/7c416038-6a2a-4871-bf0f-59dfb862f0ff/page/OZLrF'],
            ['folder' => '08. Client Services', 'title' => 'Chronic Conditions Summary', 'iframe_url' => 'https://lookerstudio.google.com/embed/reporting/06081a4e-d5c3-4b5a-ac29-4640fcad03a4/page/LD3pF'],
            ['folder' => '08. Client Services', 'title' => 'Claims Analysis Summary (Filter by Calendar Year)', 'iframe_url' => 'https://lookerstudio.google.com/embed/reporting/40a282cd-a638-4d9d-a118-1e8bb3177fcf/page/VxqrF'],
            ['folder' => '08. Client Services', 'title' => 'Quarterly Report', 'iframe_url' => 'https://lookerstudio.google.com/reporting/4a99e41e-559d-4ecc-af28-4d20df8b081e/page/FyDrF'],
            ['folder' => '08. Client Services', 'title' => 'Members with Claims above Average Paid Amount', 'iframe_url' => 'https://lookerstudio.google.com/reporting/dd47879a-9083-4ef7-a32a-40c098f026ec/page/8xrsF'],
            ['folder' => '08. Client Services', 'title' => 'Overall Population Demographic Summary', 'iframe_url' => 'https://lookerstudio.google.com/embed/reporting/0334398c-0f7d-4e3a-9481-1824b8d34ad7/page/HqCrF'],
            ['folder' => '08. Client Services', 'title' => 'Claims Analysis Summary (Filter by Plan Year)', 'iframe_url' => 'https://lookerstudio.google.com/embed/reporting/79d48e3b-2b09-4d39-a446-5ff85e4d6eed/page/DsorF'],
            ['folder' => '08. Client Services', 'title' => 'Referral List (New Eligible Members)', 'iframe_url' => 'https://lookerstudio.google.com/embed/reporting/7267098f-c2c1-4d2e-a89a-eebb14bfdfba/page/OxYrF'],
            ['folder' => '08. Client Services', 'title' => 'Referral List (All Members)', 'iframe_url' => 'https://lookerstudio.google.com/embed/reporting/e64461ae-904c-4858-9c46-96416cd74629/page/W2ErF'],
            ['folder' => '08. Client Services', 'title' => 'Monthly Report - Summary', 'iframe_url' => 'https://lookerstudio.google.com/embed/reporting/e8cc9825-ac8e-4be6-95eb-1c0715cb7a09/page/WivqF'],
            ['folder' => '08. Client Services', 'title' => 'Monthly Report - Member Data', 'iframe_url' => 'https://lookerstudio.google.com/reporting/7a0b5df2-abf6-4be3-b8d2-f72c15cf5b4c/page/V3ntF'],
            ['folder' => '09. Executive Summary Report', 'title' => 'Executive Summary Report', 'iframe_url' => 'https://lookerstudio.google.com/embed/reporting/47063557-d4ce-4759-a35a-f3ad48e7fc3d/page/q2frF'],
            ['folder' => '09. Executive Summary Report', 'title' => 'Executive Summary Report NEW', 'iframe_url' => 'https://lookerstudio.google.com/embed/reporting/38a89819-13fd-4bdb-8798-065d8b192caa/page/q2frF'],
            ['folder' => '10. Clinical', 'title' => 'Member Summary (Additional Details)', 'iframe_url' => 'https://lookerstudio.google.com/embed/reporting/e5b4dcb1-b170-44e8-9ffd-a929cf3d2ee9/page/TRCrF'],
            ['folder' => '10. Clinical', 'title' => 'Risk Groups Migration (Detailed Analysis)', 'iframe_url' => 'https://lookerstudio.google.com/embed/reporting/064898c2-6d58-4e31-8556-8befbd5f21e1/page/kIFrF'],
            ['folder' => '10. Clinical', 'title' => 'Member Data Summary (Filter by Plan Year)', 'iframe_url' => 'https://lookerstudio.google.com/embed/reporting/73c06507-70a6-45bc-a7fa-17e467109d4b/page/xzYrF'],
            ['folder' => '10. Clinical', 'title' => 'Chronic Conditions Summary', 'iframe_url' => 'https://lookerstudio.google.com/reporting/3fe2bdf3-c121-4e19-9f36-dfdaf1648831/page/LodrF'],
            ['folder' => '10. Clinical', 'title' => 'Diabetes - Evidence-Based Rules Compliance', 'iframe_url' => 'https://lookerstudio.google.com/reporting/3fe2bdf3-c121-4e19-9f36-dfdaf1648831/page/LodrF'],
            ['folder' => '10. Clinical', 'title' => 'Heart Disease - Demographic & Economic Insights', 'iframe_url' => 'https://lookerstudio.google.com/reporting/69ea3c08-c9d6-4931-9555-1f841b8d907a/page/Pa5rF'],
            ['folder' => '10. Clinical', 'title' => 'Hypertension - Demographic & Economic Insights', 'iframe_url' => 'https://lookerstudio.google.com/reporting/adbe15cb-290e-462f-ab97-2322c6a121ee/page/RI7rF'],
            ['folder' => '10. Clinical', 'title' => 'Member Data Summary (Filter by Calendar Year)', 'iframe_url' => 'https://lookerstudio.google.com/reporting/a447804d-7d9c-430d-ae13-e5b413f8f3ca/page/TGvsF?s=pNyj_yFAdrk'],
            ['folder' => '11. Operations', 'title' => 'Eligibility History Report', 'iframe_url' => 'https://lookerstudio.google.com/reporting/7b758ca9-c8d5-4a88-867b-b154ec0d5b5d/page/QPMrF'],
            ['folder' => '11. Operations', 'title' => 'Referral Data - Demographic information', 'iframe_url' => 'https://lookerstudio.google.com/reporting/16986e3c-13e1-4318-8845-a333bcbfdd5d/page/aVxqF'],
            ['folder' => 'Health Score & Predictive Reports', 'title' => 'All Disease Variable Trend', 'iframe_url' => 'https://lookerstudio.google.com/reporting/bfec70c9-7cb1-4854-9a1c-b656e9b9382d/page/wjvqF'],
            ['folder' => 'Health Score & Predictive Reports', 'title' => 'Health Score & Risk Group Overview', 'iframe_url' => 'https://lookerstudio.google.com/embed/reporting/3a49c605-dbf8-41c1-b1dd-a85973ddfb37/page/4wvqF'],
            ['folder' => 'Health Score & Predictive Reports', 'title' => 'Health Score Decile & Quartile Analysis', 'iframe_url' => 'https://lookerstudio.google.com/reporting/f0be7979-521b-43bb-b759-6d7f737ffd3b/page/hvrrF'],
            ['folder' => 'Health Score & Predictive Reports', 'title' => 'Data Science Overview', 'iframe_url' => 'https://lookerstudio.google.com/embed/reporting/51d47db0-d0b9-480d-a45f-c01df0c8488e/page/eNoqF'],
            ['folder' => 'Health Score & Predictive Reports', 'title' => 'Data Science Predictive Analysis (Overview & Member-Level Analysis)', 'iframe_url' => 'https://lookerstudio.google.com/reporting/8758e0a2-e25e-46be-afee-841ae094954c/page/wQarF'],
            ['folder' => 'Health Score & Predictive Reports', 'title' => 'Health Score Summary', 'iframe_url' => 'https://lookerstudio.google.com/reporting/4a91a614-142d-4415-9b2f-6eb3ba9dc20e/page/vbqrF'],
            ['folder' => 'Health Score & Predictive Reports', 'title' => 'Health Score (Member-Level Analysis)', 'iframe_url' => 'https://lookerstudio.google.com/embed/reporting/f8cb318d-3ed3-4fc2-aacd-07186782f951/page/QmpqF'],
            ['folder' => 'Health Score & Predictive Reports', 'title' => 'Spend Analysis', 'iframe_url' => 'https://lookerstudio.google.com/reporting/e58aba4a-2ff1-4b38-8d0b-4677eecafe72/page/xzorF'],
            ['folder' => 'Health Score & Predictive Reports', 'title' => 'Evidence-Based Rules Compliance Statistical Analysis', 'iframe_url' => 'https://lookerstudio.google.com/reporting/c9bb5a3b-19cd-41a2-88a6-48dce309150a/page/9TQsF'],
        ];

        foreach ($rows as $idx => $row) {
            StudioDashboard::updateOrCreate(
                ['folder' => $row['folder'], 'title' => $row['title']],
                [
                    'iframe_url' => $row['iframe_url'],
                    'sort_order' => $idx,
                    'is_active' => 1,
                ]
            );
        }
    }
}

