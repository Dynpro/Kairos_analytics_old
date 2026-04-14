<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Report;
use App\Models\Report_look;
use App\Http\Requests;
use PDF;
use DB;
use File;
use Illuminate\Support\Facades\Log;

/**
 * GenerateReportController – Studio Edition
 *
 * Manual HTTP trigger equivalents of PHMCron (GET /gen_repo) and
 * DownloadPdfCron (GET /down__pdf).  All Looker API calls have been removed.
 * Studio embed URLs with year parameters are used instead.
 */
class GenerateReportController extends Controller
{
    // =========================================================================
    // handle() – equivalent of PHMCron::handle()
    // Processes report requests (looks_generated=0, frequency=1)
    // =========================================================================
    public function handle()
    {
        ini_set('max_execution_time', 1500);
        ini_set("pcre.backtrack_limit", "10000000");

        $ReportData = \DB::table('report')
            ->select('report.*')
            ->where(['report.looks_generated' => 0])
            ->where(['report.frequency' => 1])
            ->where(['report.is_active' => 1])
            ->orderBy('report.report_id', 'DESC')
            ->get();

        if (isset($ReportData) && !empty($ReportData)) {

            if (isset($ReportData[0]->phm_folder_id)) {

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

                        // STUDIO: look_id is now a studio_dashboards.id
                        $studioDash = \DB::table('studio_dashboards')
                            ->where('id', $value->look_id)
                            ->first();

                        if (!$studioDash) {
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

                        $baseUrl   = rtrim($studioDash->embed_url, '&?');
                        $yearParam = json_encode(['ds0.year' => (string) $ReportData[0]->year]);
                        $studioUrl = $baseUrl
                                     . (strpos($baseUrl, '?') === false ? '?' : '&')
                                     . 'params=' . urlencode($yearParam);

                        $SubSectionData[$value->section_id][$key] = [
                            'section_id'        => $value->section_id,
                            'sub_section_title' => $value->sub_section_title,
                            'sub_section_text'  => $value->sub_section_text,
                            'sub_section_no'    => $value->sub_section_no,
                            'section_no'        => $value->section_id,
                            'look_id'           => $value->look_id,
                            'chart_type'        => 'studio',
                            'look_img_url'      => $studioUrl,
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
            }
        }
    }

    // =========================================================================
    // down__pdf() – equivalent of DownloadPdfCron::handle()
    // Processes reports where looks are ready (looks_generated=2, frequency=1)
    // =========================================================================
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
            $id   = $ReportData[0]->report_id;
            $name = $ReportData[0]->name;

            DB::table('report')->where('report_id', $id)->update(['looks_generated' => 3]);

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
                $SubSectionData[$value->section_id][$key] = [
                    'section_id'        => $value->section_id,
                    'sub_section_title' => $value->sub_section_title,
                    'sub_section_text'  => $value->sub_section_text,
                    'sub_section_no'    => $value->sub_section_no,
                    'look_id'           => $value->look_id,
                    'sub_section_id'    => $value->id,
                    'chart_type'        => $value->chart_type,
                    'embed_url'         => $value->embed_url,
                    'long_look'         => $value->long_table,
                    // Studio: use the stored embed URL as the chart reference
                    'look_img_url'      => $value->embed_url ?? '',
                ];
            }

            DB::table('report')->where('report_id', $id)->update(['looks_generated' => 4]);
            $this->generate_pdf($name, $id, $SectionData, $SubSectionData, $phmData);
        }
    }

    public function generate_pdf($name, $id, $SectionData, $SubSectionData, $phmData)
    {
        ini_set('max_execution_time', 2400);
        ini_set("pcre.backtrack_limit", "10000000");

        $ReportData = \DB::table('report')
            ->select(
                'report.year', 'report.phm_folder_id',
                'client_folder_mapping.folder_name', 'client_folder_mapping.phm_logo',
                'users.email', 'users.name', 'report.reporting_year'
            )
            ->join('phm', 'report.phm_folder_id', '=', 'phm.client_id')
            ->join('client_folder_mapping', 'phm.client_id', '=', 'client_folder_mapping.folder_id')
            ->join('users', 'report.user_id', '=', 'users.id')
            ->where(['report.report_id' => $id])
            ->get();

        $SchemaData = \DB::table('client_folder_mapping')
            ->select('client_folder_mapping.schema_name')
            ->where(['client_folder_mapping.folder_id' => $ReportData[0]->phm_folder_id])
            ->get();

        if (!empty($SchemaData)) {
            try {
                $pdf = PDF::loadView('reports.view', compact('SectionData', 'SubSectionData', 'phmData', 'id', 'ReportData'));

                $path = 'public/pdf/' . $name . '_' . time() . '.pdf';
                Storage::put($path, $pdf->output());

                $filePath = 'Generated_PHM/' . $name . '_' . date('mdy') . '.pdf';
                Storage::disk('s3')->put($filePath, $pdf->output());

                unlink(storage_path('app/' . $path));

                DB::table('report')
                    ->where('report_id', $id)
                    ->update(['file_path' => $filePath, 'looks_generated' => 6]);
            } catch (\Exception $e) {
                echo 'Message: ' . $e->getMessage();
                DB::table('report')->where('report_id', $id)->update(['looks_generated' => 7]);
            }
        }
    }

    public function update_flag(Request $request)
    {
        $flag      = $request->flag;
        $report_id = $request->report_id;

        DB::table('report')
            ->where('report_id', $report_id)
            ->update(['looks_generated' => $flag]);

        return response()->json(['success' => 'Data is updated successfully']);
    }
}
