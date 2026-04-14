<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Report;
use App\Models\Report_look;
use App\Http\Requests;
use PHPMailer\PHPMailer;
use PDF;
use DB;
use File;

/**
 * DownloadPdfCron – Studio Edition
 *
 * Processes weekly PHM report requests (frequency=1, looks_generated=2).
 * All Looker API calls have been removed.  Charts are now represented by
 * their Looker Studio embed URLs stored in report_look.embed_url / look_url.
 * The Blade view (reports.view) renders a clickable link / iframe for each
 * chart instead of a PNG image.
 */
class DownloadPdfCron extends Command
{
    protected $signature = 'downloadPdf:cron';
    protected $description = 'PHM PDF generation cron (Looker Studio edition)';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
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
                    // For Studio dashboards there are no PNG images; store the
                    // embed URL so the Blade view can render a link or iframe.
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

        $years = rtrim($ReportData[0]->year, ',');

        if (!empty($SchemaData)) {
            try {
                $pdf = PDF::loadView('reports.view', compact('SectionData', 'SubSectionData', 'phmData', 'id', 'ReportData'))
                    ->setPaper('a4', 'portrait')
                    ->setOption('margin-left', 0)
                    ->setOption('margin-right', 20)
                    ->setOption('margin-top', 30)
                    ->setOption('margin-bottom', 30);

                $path = 'public/pdf/' . $name . '_' . time() . '.pdf';
                Storage::put($path, $pdf->output());

                $filePath = 'Generated_PHM/' . $name . '_' . date('mdy') . '.pdf';
                Storage::disk('s3')->put($filePath, $pdf->output());

                // Remove local copy
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

    public function get_dates($years, $schema, $reporting_year)
    {
        if ($reporting_year == "Service") {
            if ($schema == "SCH_AHC_UPSON_REGIONAL" || $schema == "SCH_AHC_DESOTO_MEMORIAL") {
                $query = "WITH MED AS(SELECT MAX(DIAGNOSIS_DATE) MAX_DATE_MED,MIN(DIAGNOSIS_DATE) MIN_DATE_MED,1 AS ID FROM STG_TAB_MEDICAL_DATA WHERE YEAR(DIAGNOSIS_DATE) IN(" . $years . "))SELECT MED.* FROM MED";
            } else {
                $query = "WITH MED AS(SELECT MAX(DIAGNOSIS_DATE) MAX_DATE_MED,MIN(DIAGNOSIS_DATE) MIN_DATE_MED,1 AS MED_ID FROM STG_TAB_MEDICAL_DATA WHERE YEAR(DIAGNOSIS_DATE) IN(" . $years . ")),PHARMA AS(SELECT MAX(DATE_FILLED) MAX_DATE_PHARMA,MIN(DATE_FILLED) MIN_DATE_PHARMA,1 AS PHR_ID FROM STG_TAB_PHARMACY_DATA WHERE YEAR(DATE_FILLED) IN(" . $years . "))SELECT MED.*,PHARMA.* FROM MED LEFT JOIN PHARMA ON MED.MED_ID=PHARMA.PHR_ID";
            }
        } else {
            if ($schema == "SCH_AHC_UPSON_REGIONAL" || $schema == "SCH_AHC_DESOTO_MEMORIAL") {
                $query = "WITH MED AS(SELECT MAX(PAID_DATE) MAX_DATE_MED,MIN(PAID_DATE) MIN_DATE_MED,1 AS ID FROM STG_TAB_MEDICAL_DATA WHERE YEAR(PAID_DATE) IN(" . $years . "))SELECT MED.* FROM MED";
            } else {
                $query = "WITH MED AS(SELECT MAX(PAID_DATE) MAX_DATE_MED,MIN(PAID_DATE) MIN_DATE_MED,1 AS MED_ID FROM STG_TAB_MEDICAL_DATA WHERE YEAR(PAID_DATE) IN(" . $years . ")),PHARMA AS(SELECT MAX(PAID_DATE) MAX_DATE_PHARMA,MIN(PAID_DATE) MIN_DATE_PHARMA,1 AS PHR_ID FROM STG_TAB_PHARMACY_DATA WHERE YEAR(PAID_DATE) IN(" . $years . "))SELECT MED.*,PHARMA.* FROM MED LEFT JOIN PHARMA ON MED.MED_ID=PHARMA.PHR_ID";
            }
        }

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL            => env('SNOWFLAKE_API_URL'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => '',
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => 'POST',
            CURLOPT_POSTFIELDS     => '{"query":"' . $query . '","schema":"' . $schema . '"}',
            CURLOPT_HTTPHEADER     => [
                'x-api-key: ' . env('SNOWFLAKE_API_KEY'),
                'Content-Type: application/json',
            ],
        ]);
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

    public function send_notification($first_name, $email, $file)
    {
        $text = 'Hi ' . $first_name . ',<br/><br/>';
        $text .= 'Your scheduled PHM Report Generated successfully.<br/>';
        $text .= 'Kindly login to <a href="https://hca.kairosrp.com/">Kairos App</a> to download it.<br/>';
        $text .= 'Thank You,<br/>Team Kairos';

        $mail             = new PHPMailer\PHPMailer();
        $mail->IsSMTP();
        $mail->SMTPAuth   = true;
        $mail->SMTPSecure = 'tls';
        $mail->Host       = 'email-smtp.us-east-1.amazonaws.com';
        $mail->Port       = 587;
        $mail->IsHTML(true);
        $mail->Username   = env('AWS_SES_USERNAME');
        $mail->Password   = env('AWS_SES_PASSWORD');
        $mail->SetFrom('hca@kairosrp.com', 'Kairos App');
        $mail->Subject    = 'Report Generated Successfully';
        $mail->Body       = $text;
        $mail->AddAttachment(storage_path('app/' . $file));
        $mail->AddAddress($email);

        return $mail->Send();
    }
}
