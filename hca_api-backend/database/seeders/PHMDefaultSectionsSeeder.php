<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Creates the master PHM template (is_master = 1) with all 29 standard
 * sections matching the standard PHM report structure.
 *
 * Run via: php artisan db:seed --class=PHMDefaultSectionsSeeder
 * Or via API: POST /api/phm/create_default_template  (Super Admin only)
 *
 * Once seeded, copy this template for a new client using the existing
 * POST /api/copyPHM endpoint with rowToCopy.id = <master PHM id>.
 */
class PHMDefaultSectionsSeeder extends Seeder
{
    /**
     * Standard PHM sections: [section_title, section_text]
     */
    private array $sections = [
        [
            'title' => 'Executive Summary',
            'text'  => '<p>The following report is the result of an analysis of archival medical and pharmacy utilization data. The intent of this analysis is to yield a better understanding of the epidemiology currently influencing this population and to suggest population health management opportunities that can address the specific risk impacting this population.</p>',
        ],
        [
            'title' => 'Demographic Information (Age and Gender)',
            'text'  => '<p>The following section presents a demographic breakdown of the covered population by age group and gender across the reporting period. Understanding the demographic composition of the population is foundational to interpreting expenditure and utilization patterns throughout this report.</p>',
        ],
        [
            'title' => 'Overall Medical Expenditures by Year',
            'text'  => '<p>This section summarizes total medical expenditures across the reporting years. Year-over-year comparisons allow for trend identification and enable proactive planning for future healthcare spend.</p>',
        ],
        [
            'title' => 'Overall Medical Expenditures by Quarter',
            'text'  => '<p>Quarterly medical expenditure data provides a more granular view of spending patterns, highlighting seasonal variations and enabling earlier identification of emerging cost drivers.</p>',
        ],
        [
            'title' => 'Employee / Spouse / Dependent Expenditures',
            'text'  => '<p>This section segments total medical expenditures by member relationship: employee, spouse, and dependent. This segmentation enables targeted wellness and care management interventions for each population group.</p>',
        ],
        [
            'title' => 'Gender Related Expenditures',
            'text'  => '<p>An analysis of medical expenditures segmented by gender is presented below. Gender-specific expenditure patterns can inform the design of gender-appropriate health promotion programs.</p>',
        ],
        [
            'title' => 'Diagnostic Category Expenditures',
            'text'  => '<p>Medical claims were grouped into major diagnostic categories to identify the primary drivers of expenditure within the population. Understanding which diagnostic categories account for the greatest cost provides a framework for prioritizing intervention strategies.</p>',
        ],
        [
            'title' => 'Chronic Disease Expenditures',
            'text'  => '<p>Chronic diseases represent a significant and predictable portion of overall healthcare expenditure. The following analysis quantifies the cost impact of specific chronic conditions and identifies the highest-burden disease states within the population.</p>',
        ],
        [
            'title' => 'Diabetes Expenditures & Related Risk Stratification',
            'text'  => '<p>Diabetes and its associated complications represent one of the most costly and preventable chronic conditions. This section presents expenditure data related to diabetes diagnosis, treatment, and complication management across the reporting period.</p>',
        ],
        [
            'title' => 'Diabetes Non-Compliance to Evidence-Based Medicine',
            'text'  => '<p>An analysis was conducted to determine the number of individuals with diabetes who were compliant with evidence-based guidelines. The following charts identify the proportion of diabetic members who are non-compliant with HEDIS evidence-based medication protocols, including HbA1c testing, retinal exams, and blood pressure control.</p>',
        ],
        [
            'title' => 'Disease Group Risk Stratification',
            'text'  => '<p>The population was stratified into seven disease-risk groups based on chronic disease burden. This stratification reveals the distribution of risk within the population and quantifies the financial impact of moving from lower- to higher-risk groups.</p><ul><li><strong>Group 1:</strong> No chronic disease, &lt;$1,500 medical spend per 12 months</li><li><strong>Group 2:</strong> No chronic disease, ≥$1,500 medical spend per 12 months</li><li><strong>Group 3:</strong> One chronic disease</li><li><strong>Group 4:</strong> Two chronic diseases</li><li><strong>Group 5:</strong> Three chronic diseases</li><li><strong>Group 6:</strong> Four chronic diseases</li><li><strong>Group 7:</strong> Five or more chronic diseases</li></ul>',
        ],
        [
            'title' => 'Expenditures Related to Lifestyle Modifiable & Preventive Utilization',
            'text'  => '<p>Lifestyle-modifiable risk factors — including obesity, tobacco use, physical inactivity, and poor nutrition — contribute significantly to chronic disease incidence and overall healthcare costs. This section quantifies the expenditure attributable to lifestyle-modifiable conditions and preventive service utilization.</p>',
        ],
        [
            'title' => 'Estimated Lost Time & Cost due to Health Disparities',
            'text'  => '<p>Health disparities within the population contribute to avoidable productivity losses and indirect costs. This section provides estimates of lost work time and associated costs attributable to preventable health disparities, including disparities in access to preventive care and chronic disease management.</p>',
        ],
        [
            'title' => 'Preventive Screening Compliance',
            'text'  => '<p>Preventive screenings are among the most cost-effective interventions available. The following analysis compares the population\'s compliance rates for breast cancer, cervical cancer, and colorectal cancer screenings against NCQA HEDIS national benchmarks.</p>',
        ],
        [
            'title' => 'Value of Preventive Screenings',
            'text'  => '<p>This section quantifies the estimated financial and health value of achieving national preventive screening compliance benchmarks within this population. Early detection through screening reduces downstream treatment costs and improves clinical outcomes.</p>',
        ],
        [
            'title' => 'Potentially Work-Related Musculoskeletal Expenditures',
            'text'  => '<p>Musculoskeletal conditions — including back pain, joint disorders, and soft-tissue injuries — are a leading driver of both medical expenditure and workers\' compensation costs. This section identifies the subset of musculoskeletal claims that may be work-related and quantifies their financial impact.</p>',
        ],
        [
            'title' => 'Catastrophic Claims',
            'text'  => '<p>Catastrophic claims — typically defined as individual claims exceeding $50,000 — represent a disproportionate share of total medical spend. This section identifies the occurrence and cost of catastrophic claims within the population during the reporting period.</p>',
        ],
        [
            'title' => 'Inpatient, Outpatient & Emergency Room Expenditures',
            'text'  => '<p>This section segments medical expenditures by care setting: inpatient, outpatient, and emergency room. Understanding utilization patterns across care settings is essential for identifying opportunities to redirect care to more cost-effective settings.</p>',
        ],
        [
            'title' => 'Avoidable Emergency Room Visits',
            'text'  => '<p>A significant proportion of emergency room visits can be managed in less costly primary or urgent care settings. This section estimates the volume and cost of avoidable ER visits within the population and quantifies the potential savings from redirecting this utilization.</p>',
        ],
        [
            'title' => 'Primary Care Physician & Specialty Expenditures',
            'text'  => '<p>Primary care utilization is a strong predictor of overall health outcomes and cost efficiency. This section compares primary care versus specialty expenditures and assesses the population\'s utilization of primary care relative to national benchmarks.</p>',
        ],
        [
            'title' => 'Overall Pharmacy Expenditures by Year',
            'text'  => '<p>This section presents total pharmacy expenditures across the reporting years, enabling year-over-year comparison and identification of trends in drug spend.</p>',
        ],
        [
            'title' => 'Overall Pharmacy Expenditures by Quarter',
            'text'  => '<p>Quarterly pharmacy expenditure data provides a granular view of drug spending patterns, highlighting seasonal fluctuations and enabling proactive formulary and benefit management.</p>',
        ],
        [
            'title' => 'Employee / Spouse / Dependent Pharmacy Expenditures',
            'text'  => '<p>Pharmacy expenditures are segmented by member relationship — employee, spouse, and dependent — to identify population subgroups with the highest drug cost burden and to guide targeted medication management programs.</p>',
        ],
        [
            'title' => 'Medication Compliance',
            'text'  => '<p>Medication non-compliance is a leading cause of preventable hospitalizations and disease complications. This section assesses pharmacy refill compliance rates for chronic condition medications across the population.</p>',
        ],
        [
            'title' => 'Brand vs. Generic Medication Usage',
            'text'  => '<p>Generic medications offer clinically equivalent alternatives to brand-name drugs at significantly lower cost. This section quantifies the current brand-to-generic utilization ratio and estimates the potential savings achievable through increased generic substitution.</p>',
        ],
        [
            'title' => 'Appendix 1: Definition of Terms',
            'text'  => '<p>The following definitions are provided to assist in the interpretation of data presented throughout this report.</p><ul><li><strong>Paid Amount:</strong> The amount paid by the plan for a covered service after deductibles, co-pays, and co-insurance.</li><li><strong>Allowed Amount:</strong> The maximum amount the plan will pay for a covered service.</li><li><strong>Member:</strong> Any covered individual under the health plan, including employees, spouses, and dependents.</li><li><strong>HEDIS:</strong> Healthcare Effectiveness Data and Information Set — a widely used set of performance measures for managed care organizations.</li><li><strong>N:</strong> The number of individuals in a population or subgroup.</li><li><strong>Mean:</strong> The arithmetic average of a set of values.</li></ul>',
        ],
        [
            'title' => 'Appendix 2: Examples of Diagnostic Categories',
            'text'  => '<p>The following table provides representative ICD-10 diagnostic categories and their corresponding disease groupings used in this analysis.</p>',
        ],
        [
            'title' => 'Appendix 3: Examples of Complications of Diabetes',
            'text'  => '<p>The following list provides examples of diabetes-related complications identified through ICD-10 diagnosis codes in the claims data used for this analysis.</p><ul><li>Cardiovascular disease (ICD-10: I00–I99)</li><li>Peripheral vascular disease (ICD-10: I73)</li><li>Neuropathy (ICD-10: G60–G64)</li><li>Nephropathy / Chronic kidney disease (ICD-10: N18)</li><li>Retinopathy (ICD-10: H35)</li><li>Diabetic foot ulcers (ICD-10: L97)</li></ul>',
        ],
        [
            'title' => 'Appendix 4: Preventive Screening Eligibility Definitions',
            'text'  => '<p>The following eligibility definitions were applied when calculating preventive screening compliance rates in this report.</p><ul><li><strong>Breast Cancer Screening:</strong> Women ages 50–74 years who are not identified as having a history of bilateral mastectomy.</li><li><strong>Cervical Cancer Screening:</strong> Women ages 21–64 years who are not identified as having had a hysterectomy.</li><li><strong>Colorectal Cancer Screening:</strong> Members ages 50–75 years who are not identified as having a history of colorectal cancer or total colectomy.</li></ul>',
        ],
    ];

    public function run()
    {
        // Check if a master template already exists
        $existing = DB::table('phm')->where('is_master', 1)->first();
        if ($existing) {
            $this->command->info('Master PHM template already exists (id=' . $existing->id . '). Skipping.');
            return;
        }

        $now = now();

        // Create the master PHM record
        $phmId = DB::table('phm')->insertGetId([
            'name'              => 'Standard PHM Report Template',
            'client_id'         => null,
            'report_type'       => 1,
            'start_date'        => null,
            'end_date'          => null,
            'pharma_start_date' => null,
            'pharma_end_date'   => null,
            'entity_id'         => null,
            'is_master'         => 1,
            'is_active'         => 1,
            'created_at'        => $now,
            'updated_at'        => $now,
        ]);

        foreach ($this->sections as $sectionNo => $section) {
            $sectionId = DB::table('sections')->insertGetId([
                'section_title' => $section['title'],
                'section_text'  => $section['text'],
                'section_no'    => $sectionNo + 1,
                'phm_id'        => $phmId,
                'is_active'     => 1,
                'created_at'    => $now,
                'updated_at'    => $now,
            ]);

            // Each section gets one subsection as a placeholder for the chart
            DB::table('sub_sections')->insert([
                'sub_section_title' => $section['title'],
                'sub_section_text'  => '',
                'sub_section_no'    => 1,
                'section_id'        => $sectionId,
                'phm_id'            => $phmId,
                'look_img_url'      => null,
                'look_id'           => null,
                'is_active'         => 1,
                'created_at'        => $now,
                'updated_at'        => $now,
            ]);
        }

        $this->command->info('Master PHM template created with ' . count($this->sections) . ' sections (phm.id=' . $phmId . ').');
    }
}
