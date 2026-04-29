<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\SnowflakeService;

/**
 * PHMDataCron – Chart Image Generation (parallel edition)
 *
 * All Snowflake queries fire in parallel via curl_multi so the total wait time
 * equals the slowest single query instead of the sum of all queries.
 *
 * State machine:
 *   PHMCron:         0 → 1 → 2
 *   PHMDataCron:     2 → 3 → 4
 *   DownloadPdfCron: 4 → 5 → 6 (done) / 7 (failed)
 */
class PHMDataCron extends Command
{
    protected $signature   = 'phmdata:cron';
    protected $description = 'PHM chart generation cron – queries Snowflake in parallel and builds chart images';

    private function mc(string $key, string $default): string
    {
        return (string) env($key, $default);
    }

    // =========================================================================
    // handle
    // =========================================================================
    public function handle(): void
    {
        ini_set('max_execution_time', 1800);

        $report = DB::table('report')
            ->select('report.*')
            ->whereNull('report.file_path')
            ->whereIn('report.looks_generated', [1, 2])
            ->where('report.is_active', 1)
            ->orderBy('report.report_id', 'ASC')
            ->first();

        if (!$report) return;

        $id = $report->report_id;

        if ($report->looks_generated === 1) {
            $hasLookRows = DB::table('report_look')->where('report_id', $id)->exists();
            if ($hasLookRows) {
                DB::table('report')->where('report_id', $id)->update(['looks_generated' => 2]);
            } else {
                Log::warning("PHMDataCron: report {$id} is stuck at state 1 with no report_look rows; skipping");
                return;
            }
        }

        DB::table('report')->where('report_id', $id)->update(['looks_generated' => 3]);

        try {
            $success = $this->processReport($report);
            if ($success) {
                DB::table('report')->where('report_id', $id)->update(['looks_generated' => 4]);
            } else {
                DB::table('report')->where('report_id', $id)->update(['looks_generated' => 7]);
            }
        } catch (\Throwable $e) {
            Log::error("PHMDataCron failed for report {$id}: " . $e->getMessage());
            DB::table('report')->where('report_id', $id)->update(['looks_generated' => 7]);
        }

        \Illuminate\Support\Facades\Artisan::call('downloadPdf:cron');
    }

    // =========================================================================
    // processReport
    // =========================================================================
    private function processReport(object $report): bool
    {
        $id = $report->report_id;

        // Resolve schema
        $schema = $report->schema_name ?? null;
        if (!$schema) {
            $cfm    = DB::table('client_folder_mapping')->where('folder_id', $report->phm_folder_id)->first();
            $schema = $cfm ? $cfm->schema_name : null;
        }
        if (!$schema) {
            Log::warning("PHMDataCron: no schema for report {$id}, skipping chart generation");
            return false;
        }

        $medDate   = $report->reporting_year === 'Service' ? 'DIAGNOSIS_DATE' : 'PAID_DATE';
        $pharmDate = $report->reporting_year === 'Service' ? 'DATE_FILLED'    : 'PAID_DATE';

        $cfg = [
            'schema'       => $schema,
            'years'        => rtrim($report->year, ','),
            'medDate'      => $medDate,
            'pharmDate'    => $pharmDate,
            'medAmt'       => $this->mc('PHM_MED_AMOUNT_COL',   'PAID_AMOUNT'),
            'member'       => $this->mc('PHM_MEMBER_COL',        'MEMBER_NO'),
            'gender'       => $this->mc('PHM_GENDER_COL',        'GENDER'),
            'rel'          => $this->mc('PHM_RELATIONSHIP_COL',  'RELATIONSHIP'),
            'pharmAmt'     => $this->mc('PHM_PHARM_AMOUNT_COL',  'PAID_AMOUNT'),
            'brandGeneric' => $this->mc('PHM_BRAND_GENERIC_COL', 'BRAND_GENERIC_IND'),
            'diagCat'      => $this->mc('PHM_DIAG_CATEGORY_COL', 'DIAGNOSIS_CATEGORY'),
            'posCol'       => $this->mc('PHM_POS_COL',           'PLACE_OF_SERVICE'),
        ];

        // Load report_look rows first so we know which section IDs we need
        $reportLooks = DB::table('report_look')
            ->leftJoin('sub_sections', 'report_look.sub_section_id', '=', 'sub_sections.id')
            ->where('report_look.report_id', $id)
            ->get(['report_look.id as rl_id', 'report_look.section_id', 'sub_sections.sub_section_title']);

        // Load section titles directly by the IDs present in report_look
        $secIds = $reportLooks->pluck('section_id')->unique()->filter()->values();
        $sectionTitles = DB::table('sections')
            ->whereIn('id', $secIds)
            ->pluck('section_title', 'id')
            ->map(fn($t) => strtolower(trim($t ?? '')))
            ->toArray();

        if ($reportLooks->isEmpty()) return false;

        // ── 1. Identify all unique query keys needed ──────────────────────────
        $neededKeys = [];
        foreach ($reportLooks as $rl) {
            $key = $this->resolveQueryKey($sectionTitles[$rl->section_id] ?? '');
            if ($key) $neededKeys[$key] = true;
        }

        // ── 2. Fire all Snowflake queries IN PARALLEL ─────────────────────────
        $cache = $this->prefetchParallel(array_keys($neededKeys), $cfg);

        // ── 3. Build chart URLs from cached results ───────────────────────────
        foreach ($reportLooks as $rl) {
            $secTitle = $sectionTitles[$rl->section_id] ?? '';
            $subTitle = strtolower($rl->sub_section_title ?? '');
            $chartUrl = $this->buildChartUrl($secTitle, $subTitle, $cfg, $cache);
            if ($chartUrl) {
                DB::table('report_look')->where('id', $rl->rl_id)->update([
                    'look_url'   => $chartUrl,
                    'embed_url'  => $chartUrl,
                    'chart_type' => 'quickchart',
                ]);
            }
        }

        return true;
    }

    // =========================================================================
    // Query key resolution
    // =========================================================================

    /** Map a section title → a stable cache key string */
    private function resolveQueryKey(string $s): ?string
    {
        if (str_contains($s, 'overall medical') && str_contains($s, 'by year'))      return 'med_by_year';
        if (str_contains($s, 'overall medical') && str_contains($s, 'by quarter'))   return 'med_by_quarter';
        if (str_contains($s, 'employee') && str_contains($s, 'spouse')
            && str_contains($s, 'dependent') && !str_contains($s, 'pharmacy'))       return 'emp_spouse_dep';
        if (str_contains($s, 'gender') && str_contains($s, 'expenditure'))           return 'gender_exp';
        if (str_contains($s, 'diagnostic category'))                                  return 'diag_cat';
        if (str_contains($s, 'pharmacy') && str_contains($s, 'by year'))             return 'pharm_by_year';
        if (str_contains($s, 'pharmacy') && str_contains($s, 'by quarter'))          return 'pharm_by_quarter';
        if (str_contains($s, 'employee') && str_contains($s, 'spouse')
            && str_contains($s, 'pharmacy'))                                          return 'emp_pharm';
        if (str_contains($s, 'brand') && str_contains($s, 'generic'))                return 'brand_generic';
        if (str_contains($s, 'inpatient') || str_contains($s, 'outpatient'))         return 'ip_op_er';
        if (str_contains($s, 'demographic') || (str_contains($s, 'age') && str_contains($s, 'gender'))) return 'demographics';
        return null;
    }

    /** Return the SQL string for a given cache key */
    private function getQuerySql(string $key, array $cfg): ?string
    {
        switch ($key) {
            case 'med_by_year':
                return "SELECT YEAR({$cfg['medDate']}) AS yr,
                        SUM({$cfg['medAmt']}) AS total_amt, AVG({$cfg['medAmt']}) AS mean_amt,
                        COUNT(DISTINCT {$cfg['member']}) AS n_count
                        FROM STG_TAB_MEDICAL_DATA
                        WHERE YEAR({$cfg['medDate']}) IN ({$cfg['years']})
                        GROUP BY YEAR({$cfg['medDate']}) ORDER BY yr";

            case 'med_by_quarter':
                return "SELECT CONCAT(YEAR({$cfg['medDate']}),'-Q',QUARTER({$cfg['medDate']})) AS qtr,
                        SUM({$cfg['medAmt']}) AS total_amt, AVG({$cfg['medAmt']}) AS mean_amt,
                        COUNT(DISTINCT {$cfg['member']}) AS n_count
                        FROM STG_TAB_MEDICAL_DATA
                        WHERE YEAR({$cfg['medDate']}) IN ({$cfg['years']})
                        GROUP BY YEAR({$cfg['medDate']}),QUARTER({$cfg['medDate']})
                        ORDER BY YEAR({$cfg['medDate']}),QUARTER({$cfg['medDate']})";

            case 'emp_spouse_dep':
                return "SELECT YEAR({$cfg['medDate']}) AS yr, {$cfg['rel']} AS relationship,
                        SUM({$cfg['medAmt']}) AS total_amt, AVG({$cfg['medAmt']}) AS mean_amt,
                        COUNT(DISTINCT {$cfg['member']}) AS n_count
                        FROM STG_TAB_MEDICAL_DATA
                        WHERE YEAR({$cfg['medDate']}) IN ({$cfg['years']})
                        GROUP BY YEAR({$cfg['medDate']}),{$cfg['rel']} ORDER BY yr,relationship";

            case 'gender_exp':
                return "SELECT YEAR({$cfg['medDate']}) AS yr, {$cfg['gender']} AS gender,
                        SUM({$cfg['medAmt']}) AS total_amt, AVG({$cfg['medAmt']}) AS mean_amt,
                        COUNT(DISTINCT {$cfg['member']}) AS n_count
                        FROM STG_TAB_MEDICAL_DATA
                        WHERE YEAR({$cfg['medDate']}) IN ({$cfg['years']})
                        GROUP BY YEAR({$cfg['medDate']}),{$cfg['gender']} ORDER BY yr,gender";

            case 'diag_cat':
                return "SELECT {$cfg['diagCat']} AS category,
                        SUM({$cfg['medAmt']}) AS total_amt,
                        COUNT(DISTINCT {$cfg['member']}) AS n_count
                        FROM STG_TAB_MEDICAL_DATA
                        WHERE YEAR({$cfg['medDate']}) IN ({$cfg['years']})
                          AND {$cfg['diagCat']} IS NOT NULL
                        GROUP BY {$cfg['diagCat']} ORDER BY total_amt DESC LIMIT 10";

            case 'pharm_by_year':
                return "SELECT YEAR({$cfg['pharmDate']}) AS yr,
                        SUM({$cfg['pharmAmt']}) AS total_amt, AVG({$cfg['pharmAmt']}) AS mean_amt,
                        COUNT(DISTINCT {$cfg['member']}) AS n_count
                        FROM STG_TAB_PHARMACY_DATA
                        WHERE YEAR({$cfg['pharmDate']}) IN ({$cfg['years']})
                        GROUP BY YEAR({$cfg['pharmDate']}) ORDER BY yr";

            case 'pharm_by_quarter':
                return "SELECT CONCAT(YEAR({$cfg['pharmDate']}),'-Q',QUARTER({$cfg['pharmDate']})) AS qtr,
                        SUM({$cfg['pharmAmt']}) AS total_amt, AVG({$cfg['pharmAmt']}) AS mean_amt,
                        COUNT(DISTINCT {$cfg['member']}) AS n_count
                        FROM STG_TAB_PHARMACY_DATA
                        WHERE YEAR({$cfg['pharmDate']}) IN ({$cfg['years']})
                        GROUP BY YEAR({$cfg['pharmDate']}),QUARTER({$cfg['pharmDate']})
                        ORDER BY YEAR({$cfg['pharmDate']}),QUARTER({$cfg['pharmDate']})";

            case 'emp_pharm':
                return "SELECT YEAR({$cfg['pharmDate']}) AS yr, {$cfg['rel']} AS relationship,
                        SUM({$cfg['pharmAmt']}) AS total_amt, AVG({$cfg['pharmAmt']}) AS mean_amt,
                        COUNT(DISTINCT {$cfg['member']}) AS n_count
                        FROM STG_TAB_PHARMACY_DATA
                        WHERE YEAR({$cfg['pharmDate']}) IN ({$cfg['years']})
                        GROUP BY YEAR({$cfg['pharmDate']}),{$cfg['rel']} ORDER BY yr,relationship";

            case 'brand_generic':
                return "SELECT YEAR({$cfg['pharmDate']}) AS yr, {$cfg['brandGeneric']} AS bg,
                        SUM({$cfg['pharmAmt']}) AS total_amt, AVG({$cfg['pharmAmt']}) AS mean_amt,
                        COUNT(DISTINCT {$cfg['member']}) AS n_count
                        FROM STG_TAB_PHARMACY_DATA
                        WHERE YEAR({$cfg['pharmDate']}) IN ({$cfg['years']})
                          AND {$cfg['brandGeneric']} IS NOT NULL
                        GROUP BY YEAR({$cfg['pharmDate']}),{$cfg['brandGeneric']} ORDER BY yr,bg";

            case 'ip_op_er':
                return "SELECT YEAR({$cfg['medDate']}) AS yr, {$cfg['posCol']} AS pos_type,
                        SUM({$cfg['medAmt']}) AS total_amt,
                        COUNT(DISTINCT {$cfg['member']}) AS n_count
                        FROM STG_TAB_MEDICAL_DATA
                        WHERE YEAR({$cfg['medDate']}) IN ({$cfg['years']})
                          AND {$cfg['posCol']} IN ('ER','OUTPATIENT','INPATIENT','Emergency Room','Outpatient','Inpatient')
                        GROUP BY YEAR({$cfg['medDate']}),{$cfg['posCol']} ORDER BY yr,pos_type";

            case 'demographics':
                return "SELECT {$cfg['gender']} AS gender,
                        COUNT(DISTINCT {$cfg['member']}) AS n_count,
                        SUM({$cfg['medAmt']}) AS total_amt
                        FROM STG_TAB_MEDICAL_DATA
                        WHERE YEAR({$cfg['medDate']}) IN ({$cfg['years']})
                          AND {$cfg['gender']} IS NOT NULL
                        GROUP BY {$cfg['gender']} ORDER BY gender";
        }
        return null;
    }

    // =========================================================================
    // Parallel Snowflake fetch via SnowflakeService
    // =========================================================================
    private function prefetchParallel(array $keys, array $cfg): array
    {
        if (empty($keys)) return [];

        $sf = new SnowflakeService();

        // Build key => sql map
        $queries = [];
        foreach ($keys as $qKey) {
            $sql = $this->getQuerySql($qKey, $cfg);
            if ($sql) $queries[$qKey] = $sql;
        }

        if (empty($queries)) return [];

        // Fire all queries in parallel
        $raw = $sf->queryParallel($queries, $cfg['schema']);

        // Normalise: null results become empty arrays
        $cache = [];
        foreach ($raw as $qKey => $rows) {
            if (is_array($rows) && !empty($rows)) {
                $cache[$qKey] = $rows;
            }
        }

        return $cache;
    }

    // =========================================================================
    // Build chart URL from pre-fetched cache
    // =========================================================================
    private function buildChartUrl(string $secTitle, string $subTitle, array $cfg, array $cache): ?string
    {
        $key = $this->resolveQueryKey($secTitle);
        if (!$key || empty($cache[$key])) return null;

        $rows      = $cache[$key];
        $showMean  = str_contains($subTitle, 'mean');

        switch ($key) {
            case 'med_by_year': {
                $labels = array_column($rows, 'yr');
                $vals   = array_map(fn($r) => round((float)($showMean ? $r['mean_amt'] : $r['total_amt']), 2), $rows);
                $ns     = array_column($rows, 'n_count');
                $title  = $showMean ? 'Mean Amount Paid - Medical' : 'Total Amount Paid - Medical';
                return $this->hBarUrl($labels, [($showMean ? 'Mean $' : 'Total $') => $vals, 'N' => $ns], $title);
            }
            case 'med_by_quarter': {
                $labels = array_column($rows, 'qtr');
                $vals   = array_map(fn($r) => round((float)($showMean ? $r['mean_amt'] : $r['total_amt']), 2), $rows);
                $ns     = array_column($rows, 'n_count');
                $title  = $showMean ? 'Mean Amount Paid - Medical (Quarter)' : 'Total Amount Paid - Medical (Quarter)';
                return $this->hBarUrl($labels, [($showMean ? 'Mean $' : 'Total $') => $vals, 'N' => $ns], $title);
            }
            case 'emp_spouse_dep':
                return $this->groupedBarUrl($rows, 'yr', 'relationship',
                    $showMean ? 'mean_amt' : 'total_amt',
                    $showMean ? 'Mean $ by Relationship' : 'Total $ by Relationship');

            case 'gender_exp':
                return $this->groupedBarUrl($rows, 'yr', 'gender',
                    $showMean ? 'mean_amt' : 'total_amt',
                    $showMean ? 'Mean $ by Gender' : 'Total $ by Gender');

            case 'diag_cat': {
                $labels = array_column($rows, 'category');
                $totals = array_map(fn($r) => round((float)$r['total_amt'], 2), $rows);
                $ns     = array_column($rows, 'n_count');
                return $this->hBarUrl($labels, ['Total $' => $totals, 'N' => $ns], 'Top Diagnostic Categories');
            }
            case 'pharm_by_year': {
                $labels = array_column($rows, 'yr');
                $vals   = array_map(fn($r) => round((float)($showMean ? $r['mean_amt'] : $r['total_amt']), 2), $rows);
                $ns     = array_column($rows, 'n_count');
                $title  = $showMean ? 'Mean Amount Paid - Pharmacy' : 'Total Amount Paid - Pharmacy';
                return $this->hBarUrl($labels, [($showMean ? 'Mean $' : 'Total $') => $vals, 'N' => $ns], $title);
            }
            case 'pharm_by_quarter': {
                $labels = array_column($rows, 'qtr');
                $vals   = array_map(fn($r) => round((float)($showMean ? $r['mean_amt'] : $r['total_amt']), 2), $rows);
                $ns     = array_column($rows, 'n_count');
                $title  = $showMean ? 'Mean Amount Paid - Pharmacy (Quarter)' : 'Total Amount Paid - Pharmacy (Quarter)';
                return $this->hBarUrl($labels, [($showMean ? 'Mean $' : 'Total $') => $vals, 'N' => $ns], $title);
            }
            case 'emp_pharm':
                return $this->groupedBarUrl($rows, 'yr', 'relationship',
                    $showMean ? 'mean_amt' : 'total_amt',
                    $showMean ? 'Mean $ by Relationship (Pharmacy)' : 'Total $ by Relationship (Pharmacy)');

            case 'brand_generic':
                return $this->groupedBarUrl($rows, 'yr', 'bg',
                    $showMean ? 'mean_amt' : 'total_amt',
                    'Brand vs Generic Medication');

            case 'ip_op_er':
                return $this->groupedBarUrl($rows, 'yr', 'pos_type', 'total_amt', 'Inpatient / Outpatient / ER');

            case 'demographics': {
                $labels = array_column($rows, 'gender');
                $ns     = array_column($rows, 'n_count');
                $totals = array_map(fn($r) => round((float)$r['total_amt'], 2), $rows);
                return $this->hBarUrl($labels, ['Members' => $ns, 'Total $' => $totals], 'Demographics by Gender');
            }
        }
        return null;
    }

    // =========================================================================
    // Chart URL builders
    // =========================================================================
    private function hBarUrl(array $labels, array $datasets, string $title): string
    {
        $colors = ['rgba(21,101,192,0.85)', 'rgba(144,202,249,0.85)', 'rgba(255,167,38,0.85)'];
        $ds = [];
        $i  = 0;
        foreach ($datasets as $label => $data) {
            $ds[] = [
                'label'           => $label,
                'data'            => array_values($data),
                'backgroundColor' => $colors[$i % count($colors)],
            ];
            $i++;
        }
        $config = [
            'type' => 'horizontalBar',
            'data' => ['labels' => array_values($labels), 'datasets' => $ds],
            'options' => [
                'title'   => ['display' => true, 'text' => $title, 'fontSize' => 13],
                'legend'  => ['position' => 'bottom'],
                'plugins' => ['datalabels' => ['display' => false]],
                'scales'  => ['xAxes' => [['ticks' => ['beginAtZero' => true]]]],
            ],
        ];
        return 'https://quickchart.io/chart?w=700&h=350&bkg=white&f=png&c='
               . rawurlencode(json_encode($config, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    private function groupedBarUrl(array $rows, string $yearKey, string $groupKey, string $valueKey, string $title): string
    {
        $years  = array_unique(array_column($rows, $yearKey));
        $groups = array_unique(array_column($rows, $groupKey));
        sort($years);
        sort($groups);

        $idx = [];
        foreach ($rows as $row) {
            $idx[$row[$yearKey]][$row[$groupKey]] = round((float)($row[$valueKey] ?? 0), 2);
        }

        $colors = ['rgba(21,101,192,0.85)', 'rgba(239,83,80,0.85)', 'rgba(102,187,106,0.85)', 'rgba(255,167,38,0.85)'];
        $ds = [];
        $gi = 0;
        foreach ($groups as $grp) {
            $data = [];
            foreach ($years as $yr) {
                $data[] = $idx[$yr][$grp] ?? 0;
            }
            $ds[] = [
                'label'           => (string) $grp,
                'data'            => $data,
                'backgroundColor' => $colors[$gi % count($colors)],
            ];
            $gi++;
        }

        $config = [
            'type' => 'horizontalBar',
            'data' => [
                'labels'   => array_values(array_map('strval', $years)),
                'datasets' => $ds,
            ],
            'options' => [
                'title'  => ['display' => true, 'text' => $title, 'fontSize' => 13],
                'legend' => ['position' => 'bottom'],
                'scales' => ['xAxes' => [['ticks' => ['beginAtZero' => true]]]],
            ],
        ];

        return 'https://quickchart.io/chart?w=700&h=350&bkg=white&f=png&c='
               . rawurlencode(json_encode($config, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }
}
