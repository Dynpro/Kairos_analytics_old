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
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

/**
 * GenerateReportController – Studio Edition
 *
 * Manual HTTP trigger equivalents of PHMCron (GET /gen_repo) and
 * DownloadPdfCron (GET /down__pdf).  All Looker API calls have been removed.
 * Studio embed URLs with year parameters are used instead.
 */
class GenerateReportController extends Controller
{
    private function phmGeneratorDriver(): string
    {
        return strtolower((string) env('PHM_GENERATOR_DRIVER', 'php'));
    }

    private function pythonGeneratorEnabled(): bool
    {
        return $this->phmGeneratorDriver() === 'python';
    }

    private function pythonGeneratorDisabledResponse()
    {
        return response()->json([
            'Code' => 409,
            'Status' => 'Disabled',
            'Message' => 'PHP PHM generation is disabled because PHM_GENERATOR_DRIVER=python. Run the Python PHM worker instead.',
        ], 409);
    }

    private function getReportSubSections($id)
    {
        $query = DB::table('report')
            ->join('report_look', 'report.report_id', '=', 'report_look.report_id')
            ->join('sub_sections', 'report_look.sub_section_id', '=', 'sub_sections.id')
            ->join('sections', 'sub_sections.section_id', '=', 'sections.id')
            ->leftJoin('studio_dashboards', function ($join) {
                $join->on('report_look.look_id', '=', 'studio_dashboards.id')
                     ->where('report_look.chart_type', '=', 'studio');
            })
            ->where('report.report_id', $id)
            ->orderBy('sections.section_no', 'ASC')
            ->orderBy('sub_sections.sub_section_no', 'ASC')
            ->select(
                'sub_sections.id',
                'sub_sections.sub_section_title',
                'sub_sections.sub_section_text',
                'sub_sections.sub_section_no',
                'sub_sections.section_id',
                'sub_sections.phm_id',
                'sub_sections.long_table',
                'sections.section_no',
                'report_look.look_url',
                'report_look.look_id',
                'report_look.chart_type',
                'report_look.embed_url'
            );

        if (Schema::hasColumn('studio_dashboards', 'screenshot_url')) {
            $query->addSelect('studio_dashboards.screenshot_url');
        }

        return $query->get();
    }

    // =========================================================================
    // handle() – equivalent of PHMCron::handle()
    // Processes report requests (looks_generated=0, frequency=1)
    // =========================================================================
    public function handle()
    {
        if ($this->pythonGeneratorEnabled()) {
            return $this->pythonGeneratorDisabledResponse();
        }

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

                // Chain immediately into chart generation
                Artisan::call('phmdata:cron');
            }
        }
    }

    // =========================================================================
    // down__pdf() – equivalent of DownloadPdfCron::handle()
    // Processes reports where charts are ready (looks_generated=4, frequency=1)
    // =========================================================================
    public function down__pdf()
    {
        if ($this->pythonGeneratorEnabled()) {
            return $this->pythonGeneratorDisabledResponse();
        }

        $ReportData = \DB::table('report')
            ->select('report.*')
            ->whereNull('report.file_path')
            ->where(['report.looks_generated' => 4])
            ->where(['report.frequency' => 1])
            ->where(['report.is_active' => 1])
            ->limit(1)
            ->get();

        if (!empty($ReportData[0])) {
            $id   = $ReportData[0]->report_id;
            $name = $ReportData[0]->name;

            DB::table('report')->where('report_id', $id)->update(['looks_generated' => 5]);

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

            $SubSecData = $this->getReportSubSections($id);

            // Collect all QuickChart URLs and download in parallel
            $quickchartUrls = [];
            foreach ($SubSecData as $key => $value) {
                if (($value->chart_type ?? '') === 'quickchart' && !empty($value->look_url)) {
                    $quickchartUrls[$key] = $value->look_url;
                }
            }
            $pngCache = $this->downloadPngsParallel($quickchartUrls);

            $SubSectionData = [];

            foreach ($SubSecData as $key => $value) {
                $screenshotUrl = $value->screenshot_url ?? null;

                if (($value->chart_type ?? '') === 'quickchart' && !empty($value->look_url)) {
                    $look_img_url = $pngCache[$key] ?? '';
                } else {
                    $look_img_url = $screenshotUrl ?: ($value->embed_url ?? '');
                }

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
                    'screenshot_url'    => $screenshotUrl,
                    'look_img_url'      => $look_img_url,
                ];
            }

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
                'report.medical_start_date', 'report.medical_end_date',
                'report.pharmacy_start_date', 'report.pharmacy_end_date',
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
                $pdf = PDF::loadView('reports.view', compact('SectionData', 'SubSectionData', 'phmData', 'id', 'ReportData'))
                    ->setPaper('a4', 'portrait')
                    ->setOption('margin-left', 0)
                    ->setOption('margin-right', 20)
                    ->setOption('margin-top', 30)
                    ->setOption('margin-bottom', 30);

                // Generate output once
                $pdfContent = $pdf->output();

                // Ensure local temp directory exists
                $localDir = storage_path('app/public/pdf');
                if (!is_dir($localDir)) {
                    mkdir($localDir, 0755, true);
                }

                $localFile = $localDir . DIRECTORY_SEPARATOR . $name . '_' . time() . '.pdf';
                file_put_contents($localFile, $pdfContent);

                $filePath = 'Generated_PHM/' . $name . '_' . date('mdy') . '.pdf';

                // Try S3 upload; fall back to local public storage if S3 is unavailable
                try {
                    Storage::disk('s3')->put($filePath, $pdfContent);
                } catch (\Exception $s3e) {
                    // S3 not configured — store under public/pdf instead
                    $filePath = 'public/pdf/' . $name . '_' . date('mdy') . '.pdf';
                    Storage::put($filePath, $pdfContent);
                    \Log::warning('PHM S3 upload failed, stored locally: ' . $s3e->getMessage());
                }

                // Remove local temp copy
                if (file_exists($localFile)) {
                    unlink($localFile);
                }

                DB::table('report')
                    ->where('report_id', $id)
                    ->update(['file_path' => $filePath, 'looks_generated' => 6]);
            } catch (\Exception $e) {
                echo 'Message: ' . $e->getMessage();
                DB::table('report')->where('report_id', $id)->update(['looks_generated' => 7]);
            }
        }
    }

    // =========================================================================
    // gen_chart_data() – equivalent of PHMDataCron::handle()
    // Processes reports where Studio URLs are ready (looks_generated=2, frequency=1)
    // =========================================================================
    public function gen_chart_data()
    {
        if ($this->pythonGeneratorEnabled()) {
            return $this->pythonGeneratorDisabledResponse();
        }

        Artisan::call('phmdata:cron');
        return response()->json(['success' => 'PHM chart data + PDF generation triggered']);
    }

    private function downloadPngsParallel(array $urls): array
    {
        if (empty($urls)) return [];
        $mh = curl_multi_init();
        $handles = [];
        foreach ($urls as $key => $url) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => true, CURLOPT_TIMEOUT => 30]);
            $handles[$key] = $ch;
            curl_multi_add_handle($mh, $ch);
        }
        $running = null;
        do { curl_multi_exec($mh, $running); curl_multi_select($mh, 0.5); } while ($running > 0);
        $results = [];
        foreach ($handles as $key => $ch) {
            $data = curl_multi_getcontent($ch);
            $results[$key] = $data ? 'data:image/png;base64,' . base64_encode($data) : '';
            curl_multi_remove_handle($mh, $ch);
            curl_close($ch);
        }
        curl_multi_close($mh);
        return $results;
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
