<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Report;
use App\Models\Report_look;
use App\Http\Requests;
use PDF;
use DB;
use File;

/**
 * PHMCron – Studio Edition
 *
 * Replaces all Looker API calls with direct Studio embed-URL construction.
 * The sub_sections.look_id field now stores a studio_dashboards.id (integer PK)
 * instead of a Looker Look ID.  Year filtering is done by appending the
 * `params` query-string to the Studio embed URL.
 */
class PHMCron extends Command
{
    protected $signature = 'phm:cron';
    protected $description = 'PHM report generation cron (Looker Studio edition)';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        ini_set('max_execution_time', 1500);
        ini_set("pcre.backtrack_limit", "10000000");

        $ReportData = \DB::table('report')
            ->select('report.*')
            ->where('report.looks_generated', 0)
            ->where('report.is_active', 1)
            ->where(function ($query) {
                $query->whereNull('report.schedule_time')
                      ->orWhere('report.schedule_time', '<=', date('Y-m-d H:i:s'));
            })
            ->orderBy('report.report_id', 'DESC')
            ->get();

        if (isset($ReportData) && !empty($ReportData)) {

            if (isset($ReportData[0]->phm_folder_id)) {

                // Mark as "in progress" immediately to prevent duplicate runs
                DB::table('report')
                    ->where('report_id', $ReportData[0]->report_id)
                    ->update(['looks_generated' => 1]);

                $SectionData = \DB::table('sections')
                    ->select('sections.id', 'sections.section_title', 'sections.section_text', 'sections.section_no')
                    ->join('phm', 'sections.phm_id', '=', 'phm.id')
                    ->where(['phm.client_id' => $ReportData[0]->phm_folder_id])
                    ->where(['phm.is_active' => 1])
                    ->groupBy('sections.id', 'sections.section_title', 'sections.section_text', 'sections.section_no')
                    ->orderBy('section_no')
                    ->get();

                $SubSecData = \DB::table('sub_sections')
                    ->select(
                        'sub_sections.id', 'sub_sections.sub_section_title',
                        'sub_sections.sub_section_text', 'sub_sections.sub_section_no',
                        'sub_sections.section_id', 'sub_sections.look_id'
                    )
                    ->join('sections', 'sub_sections.section_id', '=', 'sections.id')
                    ->join('phm', 'sections.phm_id', '=', 'phm.id')
                    ->where(['phm.client_id' => $ReportData[0]->phm_folder_id])
                    ->where(['phm.is_active' => 1])
                    ->groupBy(
                        'sub_sections.id', 'sub_sections.sub_section_title',
                        'sub_sections.sub_section_text', 'sub_sections.sub_section_no',
                        'sub_sections.section_id', 'sub_sections.look_id'
                    )
                    ->orderBy('section_id')
                    ->get();

                $SubSectionData = [];
                $looks_data     = [];

                foreach ($SubSecData as $key => $value) {

                    if (isset($value->look_id) && !empty($value->look_id)) {

                        // ----------------------------------------------------------------
                        // STUDIO: look_id now holds a studio_dashboards.id
                        // Build a year-filtered embed URL by appending Looker Studio's
                        // `params` query string.  Key names must match the data-source
                        // filter fields configured in the Studio report.
                        // ----------------------------------------------------------------
                        $studioDash = \DB::table('studio_dashboards')
                            ->where('id', $value->look_id)
                            ->first();

                        if (!$studioDash) {
                            // Subsection references a dashboard that no longer exists –
                            // record a placeholder row so the report_look table stays
                            // complete and the PDF can note the missing chart.
                            Report_look::insert([
                                'report_id'      => $ReportData[0]->report_id,
                                'section_id'     => $value->section_id,
                                'sub_section_id' => $value->id,
                                'sub_section_no' => $value->sub_section_no,
                                'look_id'        => $value->look_id,
                                'chart_type'     => 'studio',
                                'embed_url'      => '',
                                'look_url'       => '',
                            ]);

                            $SubSectionData[$value->section_id][$key] = [
                                'section_id'        => $value->section_id,
                                'sub_section_title' => $value->sub_section_title,
                                'sub_section_text'  => $value->sub_section_text,
                                'sub_section_no'    => $value->sub_section_no,
                                'section_no'        => $value->section_id,
                                'look_id'           => $value->look_id,
                                'chart_type'        => 'studio',
                                'look_img_url'      => '',
                                'embed_url'         => '',
                            ];
                            continue;
                        }

                        // Build a Looker Studio parameterised URL.
                        // The `params` value is a JSON object whose keys are the
                        // parameter/filter IDs defined in the Studio report.
                        // Common key patterns: "ds0.year", "ds0.Year", etc.
                        $baseUrl    = rtrim($studioDash->embed_url, '&?');
                        $yearParam  = json_encode(['ds0.year' => (string) $ReportData[0]->year]);
                        $studioUrl  = $baseUrl . (strpos($baseUrl, '?') === false ? '?' : '&')
                                      . 'params=' . urlencode($yearParam);

                        $SubSectionData[$value->section_id][$key] = [
                            'section_id'        => $value->section_id,
                            'sub_section_title' => $value->sub_section_title,
                            'sub_section_text'  => $value->sub_section_text,
                            'sub_section_no'    => $value->sub_section_no,
                            'section_no'        => $value->section_id,
                            'look_id'           => $value->look_id,
                            'chart_type'        => 'studio',
                            'look_img_url'      => $studioUrl,   // Studio URL (no PNG)
                            'embed_url'         => $studioUrl,
                        ];

                        Report_look::insert([
                            'report_id'      => $ReportData[0]->report_id,
                            'section_id'     => $value->section_id,
                            'sub_section_id' => $value->id,
                            'sub_section_no' => $value->sub_section_no,
                            'look_id'        => $value->look_id,
                            'chart_type'     => 'studio',
                            'embed_url'      => $studioUrl,
                            'look_url'       => $studioUrl,
                        ]);

                    } else {
                        Report_look::insert([
                            'report_id'      => $ReportData[0]->report_id,
                            'section_id'     => $value->section_id,
                            'sub_section_id' => $value->id,
                            'sub_section_no' => $value->sub_section_no,
                        ]);

                        $SubSectionData[$value->section_id][$key] = [
                            'section_id'        => $value->section_id,
                            'sub_section_title' => $value->sub_section_title,
                            'sub_section_text'  => $value->sub_section_text,
                            'sub_section_no'    => $value->sub_section_no,
                            'section_no'        => $value->section_id,
                            'look_id'           => null,
                            'chart_type'        => null,
                            'look_img_url'      => '',
                            'embed_url'         => '',
                        ];
                    }
                }

                $id  = $ReportData[0]->report_id;
                $pdf = PDF::loadView('reports.view_look', compact('SectionData', 'SubSectionData', 'id'));
                $path = 'public/pdf/' . $ReportData[0]->name . '_' . time() . '.pdf';

                DB::table('report')
                    ->where('report_id', $ReportData[0]->report_id)
                    ->update(['looks_generated' => 2]);

                Storage::put($path, $pdf->output());

                // Immediately chain into chart generation — no need to wait for next cron tick
                \Illuminate\Support\Facades\Artisan::call('phmdata:cron');
            }
        }
    }
}
