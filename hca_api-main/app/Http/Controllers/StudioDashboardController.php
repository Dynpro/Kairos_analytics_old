<?php

namespace App\Http\Controllers;

use App\Models\StudioDashboard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudioDashboardController extends Controller
{
    /**
     * Get all active dashboards (simple list)
     */
    public function index()
    {
        $dashboards = StudioDashboard::query()
            ->where('is_active', 1)
            ->orderBy('folder_name')
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get([
                'id', 'client_id', 'client_name', 'folder_id', 'folder_name',
                'category_id', 'subcategory_id', 'dash_id', 'title', 'embed_url',
                'access_level', 'sort_order'
            ]);

        return response()->json([
            'status' => 'Success',
            'data' => $dashboards,
        ]);
    }

    /**
     * Get single dashboard by ID
     */
    public function show($id)
    {
        $dashboard = StudioDashboard::query()
            ->where('is_active', 1)
            ->findOrFail($id);

        return response()->json([
            'status' => 'Success',
            'data' => $dashboard,
        ]);
    }

    /**
     * Get dashboard embed URL for iframe.
     *
     * FIX 1 (ID Mismatch): The sidebar builds navigation using the legacy
     * `looker_data` table which uses old integer IDs (e.g. 13234). The Studio
     * table stores those same values in `dash_id` and has its own auto-increment
     * `id`. We must look up by `dash_id` first (the value the frontend sends),
     * then fall back to looking up by `id`. The previous code had this logic
     * inside a WHERE clause but the `$byDashId` / `$byId` debug variables were
     * only initialised inside the `elseif ($dashboard_id)` branch, causing an
     * "Undefined variable" 500 error whenever the `!$dashboard` block was
     * reached from the flag == 1 path.
     *
     * FIX 2 (Uninitialised variables / 500): `$byDashId` and `$byId` are now
     * declared at the top of the method so they are always in scope, preventing
     * the 500 that fired when the 404 debug block tried to reference them after
     * a flag == 1 lookup.
     */
    public function getDashboard(Request $request)
    {
        $dashboard_id = $request->input('dashboard_id');
        $client_id    = $request->input('client_id');
        $flag         = $request->input('flag', 0); // 1 = default dashboard, 0 = specific

        $user = auth()->user();

        // Initialise debug helpers so they are always in scope (fixes 500 on
        // the 404 branch when flag == 1 and $dashboard is not found).
        $byDashId = null;
        $byId     = null;

        if ($flag == 1 && $client_id) {
            // Get default dashboard for client (first active one)
            $dashboard = StudioDashboard::query()
                ->where('client_id', $client_id)
                ->where('is_active', 1)
                ->orderBy('sort_order')
                ->first();

        } elseif ($dashboard_id !== null && $dashboard_id !== '') {
            // ---------------------------------------------------------------
            // FIX 1: prefer dash_id match (the legacy ID the sidebar sends),
            // then fall back to the studio table's own auto-increment id.
            // Both lookups are cheap indexed reads and are done separately so
            // we can log which one succeeded.
            // ---------------------------------------------------------------
            $byDashId = StudioDashboard::where('dash_id', $dashboard_id)
                ->where('is_active', 1)
                ->first();

            $byId = $byDashId
                ? null // no need to run the second query if we already found it
                : StudioDashboard::where('id', $dashboard_id)
                    ->where('is_active', 1)
                    ->first();

            $dashboard = $byDashId ?? $byId;

            \Log::info('StudioDashboard lookup', [
                'requested_id'  => $dashboard_id,
                'found_by'      => $byDashId ? 'dash_id' : ($byId ? 'id' : 'none'),
                'user_id'       => $user->id,
                'user_group_id' => $user->user_group_id ?? null,
            ]);

        } else {
            return response()->json([
                'Code'    => 400,
                'Status'  => 'Failed',
                'Message' => 'dashboard_id or client_id required',
            ]);
        }

        if (!$dashboard) {
            $debug = [
                'searched_dashboard_id'  => $dashboard_id,
                'searched_client_id'     => $client_id,
                'flag'                   => $flag,
                'by_dash_id'             => $byDashId ? 'found' : 'not found',
                'by_id'                  => $byId     ? 'found' : 'not found',
                'total_active_dashboards'=> StudioDashboard::where('is_active', 1)->count(),
                'sample_dash_ids'        => StudioDashboard::where('is_active', 1)
                                                ->take(5)->pluck('dash_id')->toArray(),
            ];

            \Log::error('StudioDashboard not found', $debug);

            return response()->json([
                'Code'    => 404,
                'Status'  => 'Failed',
                'Message' => 'Dashboard not found',
                // Remove the 'debug' key before going to production:
                'debug'   => $debug,
            ]);
        }

        // Check user access
        if (!$this->checkUserAccess($user->id, $dashboard->id)) {
            return response()->json([
                'Code'    => 403,
                'Status'  => 'Failed',
                'Message' => 'Access denied',
            ]);
        }

        return response()->json([
            'Code'      => 200,
            'Status'    => 'Success',
            'url'       => $dashboard->embed_url,
            'dashboard' => [
                'id'          => $dashboard->id,
                'dash_id'     => $dashboard->dash_id,
                'title'       => $dashboard->title,
                'client_name' => $dashboard->client_name,
                'folder_name' => $dashboard->folder_name,
            ],
        ]);
    }

    // =========================================================================
    // "All dashboards" endpoints – replacements for LookerDataController
    // methods that returned data from the legacy `looker_data` table.
    // These are now wired to the same URL routes so the frontend needs zero
    // URL changes.
    // =========================================================================

    /**
     * Get all clients (flat array, no user filter).
     * Replaces LookerDataController@getLookerClient  (GET /getLookerClient)
     */
    public function getClientsAll()
    {
        $dataset = StudioDashboard::query()
            ->select('client_primary_id', 'client_id', 'client_name', 'category_id', 'subcategory_id')
            ->where('is_active', 1)
            ->groupBy('client_primary_id', 'client_id', 'client_name', 'category_id', 'subcategory_id')
            ->orderBy('client_name')
            ->get();

        return response()->json(['status' => 'Success', 'data' => $dataset]);
    }

    /**
     * Get folders grouped by client (no user filter).
     * Replaces LookerDataController@getLookerClientFolder  (GET /getLookerClientFolder)
     */
    public function getFoldersAll()
    {
        $dataset = StudioDashboard::query()
            ->select('client_id', 'folder_id', 'folder_name')
            ->where('is_active', 1)
            ->whereNotNull('folder_id')
            ->groupBy('client_id', 'folder_id', 'folder_name')
            ->orderBy('folder_name')
            ->get();

        $result = [];
        foreach ($dataset as $value) {
            $result[$value->client_id][$value->folder_id] = [
                'folder_id'   => $value->folder_id,
                'folder_name' => $value->folder_name,
            ];
        }

        return response()->json(['status' => 'Success', 'data' => $result]);
    }

    /**
     * Get dashboards nested by client+folder (no user filter).
     * Replaces LookerDataController@getLookerFolderDashboard  (GET /getLookerFolderDashboard)
     */
    public function getDashboardsAll()
    {
        $dataset = StudioDashboard::query()
            ->select('client_id', 'folder_id', 'dash_id', 'title', 'embed_url')
            ->where('is_active', 1)
            ->orderBy('client_id')
            ->orderBy('sort_order')
            ->get();

        $result = [];
        foreach ($dataset as $value) {
            $result[$value->client_id][$value->folder_id][$value->dash_id] = [
                'dash_id'   => $value->dash_id,
                'title'     => $value->title,
                'embed_url' => $value->embed_url,
            ];
        }

        return response()->json(['status' => 'Success', 'data' => $result]);
    }

    /**
     * Get clients grouped by subcategory name (no user filter).
     * Replaces LookerDataController@getLookerClientCategoryWiseNew  (GET /getLookerClientCategoryWiseNew)
     * Key = subcategory name string (empty string "" for clients without a subcategory).
     */
    public function getClientCategoryWiseNew()
    {
        $dataset = DB::table('studio_dashboards')
            ->select(
                'studio_dashboards.client_primary_id',
                'studio_dashboards.client_id',
                'studio_dashboards.client_name',
                'studio_dashboards.subcategory_id',
                'client_subcategory.subcategory'
            )
            ->leftJoin('client_subcategory',
                'studio_dashboards.subcategory_id', '=', 'client_subcategory.subcategory_id')
            ->where('studio_dashboards.is_active', 1)
            ->groupBy(
                'studio_dashboards.client_primary_id',
                'studio_dashboards.client_id',
                'studio_dashboards.client_name',
                'studio_dashboards.subcategory_id',
                'client_subcategory.subcategory'
            )
            ->orderBy('studio_dashboards.client_name')
            ->get();

        $finalArray = [];
        foreach ($dataset as $value) {
            $key = ($value->subcategory_id === null) ? '' : ($value->subcategory ?? '');
            $finalArray[$key][] = $value;
        }

        return response()->json(['status' => 'Success', 'data' => $finalArray]);
    }

    /**
     * Get clients grouped by category/subcategory
     * Matches getLookerClientCategoryWiseNew from LookerDataController
     */
    public function getStudioClientCategoryWise()
    {
        return $this->getClientCategoryWiseNew();
    }

    /**
     * Get folders grouped by client
     * Matches getLookerClientFolder from LookerDataController
     */
    public function getStudioClientFolder()
    {
        return $this->getFoldersAll();
    }

    /**
     * Get dashboards grouped by client and folder.
     *
     * FIX 3 (State Persistence / ID Mismatch from Redux cache):
     * The sidebar reads `dash_id` from the Redux store and passes it straight
     * to getDashboard(). This endpoint now returns BOTH `id` (studio PK) and
     * `dash_id` (the legacy ID the sidebar already knows). That means the
     * frontend can always send whichever one it has and getDashboard() will
     * resolve it correctly via the dual-lookup in FIX 1.
     *
     * Because this endpoint also sets Cache-Control: no-store headers, a hard
     * refresh or a call to handleRefreshData() will immediately pick up the
     * correct mapping without requiring a logout/login.
     */
    public function getStudioFolderDashboard()
    {
        $dataset = StudioDashboard::query()
            ->select(
                'id', 'client_id', 'client_name', 'folder_id', 'folder_name',
                'category_id', 'subcategory_id', 'dash_id', 'title', 'embed_url',
                'access_level', 'sort_order'
            )
            ->where('is_active', 1)
            ->orderBy('client_id')
            ->orderBy('folder_id')
            ->orderBy('sort_order')
            ->get();

        $result = [];
        foreach ($dataset as $dashboard) {
            $result[] = [
                'id'           => $dashboard->id,          // Studio auto-increment PK
                'dash_id'      => $dashboard->dash_id,     // Legacy ID (e.g. "13234")
                'client_id'    => $dashboard->client_id,
                'client_name'  => $dashboard->client_name,
                'folder_id'    => $dashboard->folder_id,
                'folder_name'  => $dashboard->folder_name,
                'category_id'  => $dashboard->category_id,
                'subcategory_id' => $dashboard->subcategory_id,
                'title'        => $dashboard->title,
                'embed_url'    => $dashboard->embed_url,
                'access_level' => $dashboard->access_level,
                'sort_order'   => $dashboard->sort_order,
            ];
        }

        return response()
            ->json(['status' => 'Success', 'data' => $result, 'timestamp' => time()])
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    /**
     * Get user-accessible clients grouped by subcategory NAME (not ID).
     * Key = subcategory name string ("" for no-subcategory clients).
     * Each object includes a `subcategory` field for the nav to display.
     *
     * Replaces LookerDataController@getUserAccessClientsCategoryWiseNew
     *   (GET /clientCateWiseUserAccessNew)
     *
     * FIX 3 (State Persistence): Super-admin bypass included.
     */
    public function getUserAccessClients()
    {
        $user   = auth()->user();
        $userId = $user->id;

        $superAdminGroups = ['1001', '1003', '1011'];
        $isSuperAdmin     = in_array($user->user_group_id, $superAdminGroups)
                            || $user->is_admin;

        $baseSelect = [
            'studio_dashboards.client_primary_id',
            'studio_dashboards.client_id',
            'studio_dashboards.client_name',
            'studio_dashboards.subcategory_id',
            'client_subcategory.subcategory',
        ];

        if ($isSuperAdmin) {
            $dataset = DB::table('studio_dashboards')
                ->select($baseSelect)
                ->leftJoin('client_subcategory',
                    'studio_dashboards.subcategory_id', '=', 'client_subcategory.subcategory_id')
                ->where('studio_dashboards.is_active', 1)
                ->groupBy(
                    'studio_dashboards.client_primary_id',
                    'studio_dashboards.client_id',
                    'studio_dashboards.client_name',
                    'studio_dashboards.subcategory_id',
                    'client_subcategory.subcategory'
                )
                ->orderBy('studio_dashboards.client_name')
                ->get();
        } else {
            $dataset = DB::table('studio_dashboard_user_mapping')
                ->select($baseSelect)
                ->join('studio_dashboards',
                    'studio_dashboard_user_mapping.studio_dashboard_id', '=',
                    'studio_dashboards.id')
                ->join('grp_role_usr_mapping',
                    'studio_dashboard_user_mapping.grp_usr_mapping_id', '=',
                    'grp_role_usr_mapping.grp_usr_mapping_id')
                ->leftJoin('client_subcategory',
                    'studio_dashboards.subcategory_id', '=', 'client_subcategory.subcategory_id')
                ->where('grp_role_usr_mapping.user_id', $userId)
                ->where('grp_role_usr_mapping.is_active', 1)
                ->where('studio_dashboards.is_active', 1)
                ->groupBy(
                    'studio_dashboards.client_primary_id',
                    'studio_dashboards.client_id',
                    'studio_dashboards.client_name',
                    'studio_dashboards.subcategory_id',
                    'client_subcategory.subcategory'
                )
                ->orderBy('studio_dashboards.client_name')
                ->get();
        }

        // Key by subcategory name (empty string "" for null), matching the
        // format of LookerDataController@getUserAccessClientsCategoryWiseNew.
        $finalArray = [];
        foreach ($dataset as $value) {
            $key = ($value->subcategory_id === null) ? '' : ($value->subcategory ?? '');
            $finalArray[$key][] = $value;
        }

        return response()->json(['status' => 'Success', 'data' => $finalArray]);
    }

    /**
     * Get user-accessible clients as a flat array (no grouping).
     * Replaces LookerDataController@getUserAccessClients  (GET /getUserAccessClients)
     */
    public function getUserAccessClientsFlat()
    {
        $user   = auth()->user();
        $userId = $user->id;

        $superAdminGroups = ['1001', '1003', '1011'];
        $isSuperAdmin     = in_array($user->user_group_id, $superAdminGroups)
                            || $user->is_admin;

        $baseSelect = [
            'studio_dashboards.client_primary_id',
            'studio_dashboards.client_id',
            'studio_dashboards.client_name',
            'studio_dashboards.category_id',
            'studio_dashboards.subcategory_id',
            'client_subcategory.subcategory',
        ];

        if ($isSuperAdmin) {
            $dataset = DB::table('studio_dashboards')
                ->select($baseSelect)
                ->leftJoin('client_subcategory',
                    'studio_dashboards.subcategory_id', '=', 'client_subcategory.subcategory_id')
                ->where('studio_dashboards.is_active', 1)
                ->groupBy(
                    'studio_dashboards.client_primary_id',
                    'studio_dashboards.client_id',
                    'studio_dashboards.client_name',
                    'studio_dashboards.category_id',
                    'studio_dashboards.subcategory_id',
                    'client_subcategory.subcategory'
                )
                ->orderBy('studio_dashboards.client_name')
                ->get();
        } else {
            $dataset = DB::table('studio_dashboard_user_mapping')
                ->select($baseSelect)
                ->join('studio_dashboards',
                    'studio_dashboard_user_mapping.studio_dashboard_id', '=',
                    'studio_dashboards.id')
                ->join('grp_role_usr_mapping',
                    'studio_dashboard_user_mapping.grp_usr_mapping_id', '=',
                    'grp_role_usr_mapping.grp_usr_mapping_id')
                ->leftJoin('client_subcategory',
                    'studio_dashboards.subcategory_id', '=', 'client_subcategory.subcategory_id')
                ->where('grp_role_usr_mapping.user_id', $userId)
                ->where('grp_role_usr_mapping.is_active', 1)
                ->where('studio_dashboards.is_active', 1)
                ->groupBy(
                    'studio_dashboards.client_primary_id',
                    'studio_dashboards.client_id',
                    'studio_dashboards.client_name',
                    'studio_dashboards.category_id',
                    'studio_dashboards.subcategory_id',
                    'client_subcategory.subcategory'
                )
                ->orderBy('studio_dashboards.client_name')
                ->get();
        }

        return response()->json(['status' => 'Success', 'data' => $dataset]);
    }

    /**
     * Get user-accessible folders.
     * FIX 3: Same super-admin bypass as getUserAccessClients().
     */
    public function getUserAccessFolders()
    {
        $user   = auth()->user();
        $userId = $user->id;

        $superAdminGroups = ['1001', '1003', '1011'];
        $isSuperAdmin     = in_array($user->user_group_id, $superAdminGroups)
                            || $user->is_admin;

        if ($isSuperAdmin) {
            $dataset = StudioDashboard::query()
                ->select('client_id', 'folder_id', 'folder_name')
                ->where('is_active', 1)
                ->whereNotNull('folder_id')
                ->groupBy('client_id', 'folder_id', 'folder_name')
                ->orderBy('folder_name')
                ->get();
        } else {
            $dataset = DB::table('studio_dashboard_user_mapping')
                ->select('studio_dashboards.client_id',
                         'studio_dashboards.folder_id',
                         'studio_dashboards.folder_name')
                ->join('studio_dashboards',
                    'studio_dashboard_user_mapping.studio_dashboard_id', '=',
                    'studio_dashboards.id')
                ->join('grp_role_usr_mapping',
                    'studio_dashboard_user_mapping.grp_usr_mapping_id', '=',
                    'grp_role_usr_mapping.grp_usr_mapping_id')
                ->where('grp_role_usr_mapping.user_id', $userId)
                ->where('grp_role_usr_mapping.is_active', 1)
                ->where('studio_dashboards.is_active', 1)
                ->whereNotNull('folder_id')
                ->groupBy('client_id', 'folder_id', 'folder_name')
                ->orderBy('folder_name')
                ->get();
        }

        $result = [];
        foreach ($dataset as $value) {
            $result[$value->client_id][$value->folder_id] = [
                'folder_id'   => $value->folder_id,
                'folder_name' => $value->folder_name,
            ];
        }

        return response()
            ->json(['status' => 'Success', 'data' => $result, 'timestamp' => time()])
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    /**
     * Get user-accessible dashboards.
     *
     * FIX 1 + FIX 3: Returns BOTH `dash_id` and `studio_id` in the keyed
     * result so the Redux cache always has the legacy ID (for display/routing)
     * and the studio ID (for unambiguous API lookups). Super-admin bypass
     * included.
     */
    public function getUserAccessDashboards()
    {
        $user   = auth()->user();
        $userId = $user->id;

        $superAdminGroups = ['1001', '1003', '1011'];
        $isSuperAdmin     = in_array($user->user_group_id, $superAdminGroups)
                            || $user->is_admin;

        if ($isSuperAdmin) {
            $dataset = StudioDashboard::query()
                ->select('client_id', 'folder_id', 'dash_id', 'title',
                         'embed_url', 'id as studio_id')
                ->where('is_active', 1)
                ->orderBy('sort_order')
                ->get();
        } else {
            $dataset = DB::table('studio_dashboard_user_mapping')
                ->select(
                    'studio_dashboards.client_id',
                    'studio_dashboards.folder_id',
                    'studio_dashboards.dash_id',
                    'studio_dashboards.title',
                    'studio_dashboards.embed_url',
                    'studio_dashboards.id as studio_id'
                )
                ->join('studio_dashboards',
                    'studio_dashboard_user_mapping.studio_dashboard_id', '=',
                    'studio_dashboards.id')
                ->join('grp_role_usr_mapping',
                    'studio_dashboard_user_mapping.grp_usr_mapping_id', '=',
                    'grp_role_usr_mapping.grp_usr_mapping_id')
                ->where('grp_role_usr_mapping.user_id', $userId)
                ->where('grp_role_usr_mapping.is_active', 1)
                ->where('studio_dashboards.is_active', 1)
                ->orderBy('studio_dashboards.sort_order')
                ->get();
        }

        $result = [];
        foreach ($dataset as $value) {
            // Key by dash_id (legacy) so existing sidebar code works unchanged.
            // studio_id is included so the iframe can pass it directly if needed.
            $result[$value->client_id][$value->folder_id][$value->dash_id] = [
                'dash_id'   => $value->dash_id,
                'studio_id' => $value->studio_id,
                'title'     => $value->title,
                'embed_url' => $value->embed_url,
            ];
        }

        return response()
            ->json(['status' => 'Success', 'data' => $result, 'timestamp' => time()])
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    /**
     * Store new dashboard
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'client_primary_id' => 'nullable|string|max:100',
            'client_id'         => 'nullable|string|max:100',
            'client_name'       => 'nullable|string|max:255',
            'folder_id'         => 'nullable|string|max:100',
            'folder_name'       => 'nullable|string|max:255',
            'category_id'       => 'nullable|integer',
            'subcategory_id'    => 'nullable|integer',
            'dash_id'           => 'nullable|string|max:100',
            'title'             => 'required|string|max:255',
            'embed_url'         => 'required|string',
            'report_id'         => 'nullable|string|max:255',
            'access_level'      => 'nullable|string|in:viewer,explorer',
            'sort_order'        => 'nullable|integer',
            'is_active'         => 'nullable|boolean',
        ]);

        $dashboard = StudioDashboard::create($request->all());
        \Cache::forget('studio_dashboards_grouped');

        return response()->json([
            'Code'   => 201,
            'Status' => 'Success',
            'data'   => $dashboard,
        ], 201);
    }

    /**
     * Update dashboard
     */
    public function update(Request $request, $id)
    {
        $dashboard = StudioDashboard::findOrFail($id);
        $dashboard->update($request->all());
        \Cache::forget('studio_dashboards_grouped');

        return response()->json([
            'Code'   => 200,
            'Status' => 'Success',
            'data'   => $dashboard,
        ]);
    }

    /**
     * Delete (soft delete by setting is_active = 0)
     */
    public function destroy($id)
    {
        $dashboard = StudioDashboard::findOrFail($id);
        $dashboard->update(['is_active' => false]);
        \Cache::forget('studio_dashboards_grouped');

        return response()->json([
            'Code'    => 200,
            'Status'  => 'Success',
            'Message' => 'Dashboard deactivated',
        ]);
    }

    /**
     * Bulk import/upsert dashboards
     */
    public function bulkUpsert(Request $request)
    {
        $this->validate($request, [
            'dashboards'             => 'required|array',
            'dashboards.*.client_id' => 'nullable|string|max:100',
            'dashboards.*.dash_id'   => 'nullable|string|max:100',
            'dashboards.*.title'     => 'required|string|max:255',
            'dashboards.*.embed_url' => 'required|string',
        ]);

        $items = $request->input('dashboards', []);
        $count = 0;

        foreach ($items as $idx => $item) {
            StudioDashboard::updateOrCreate(
                [
                    'client_id' => $item['client_id'] ?? null,
                    'dash_id'   => $item['dash_id']   ?? null,
                ],
                array_merge($item, [
                    'sort_order' => (int) ($item['sort_order'] ?? $idx),
                    'is_active'  => $item['is_active'] ?? true,
                ])
            );
            $count++;
        }

        \Cache::forget('studio_dashboards_grouped');

        return response()->json([
            'Code'   => 200,
            'Status' => 'Success',
            'data'   => ['upserted' => $count],
        ]);
    }

    /**
     * Assign dashboard access to user group mapping
     */
    public function assignAccess(Request $request)
    {
        $this->validate($request, [
            'studio_dashboard_id' => 'required|integer|exists:studio_dashboards,id',
            'grp_usr_mapping_id'  => 'required|integer|exists:grp_role_usr_mapping,grp_usr_mapping_id',
        ]);

        DB::table('studio_dashboard_user_mapping')->insert([
            'studio_dashboard_id' => $request->studio_dashboard_id,
            'grp_usr_mapping_id'  => $request->grp_usr_mapping_id,
            'is_active'           => true,
            'created_at'          => now(),
            'updated_at'          => now(),
        ]);

        return response()->json([
            'Code'    => 200,
            'Status'  => 'Success',
            'Message' => 'Access assigned',
        ]);
    }

    /**
     * Grant super admin access to all dashboards
     */
    public function grantSuperAdminAccess(Request $request)
    {
        $this->validate($request, [
            'user_id' => 'required|integer|exists:users,id',
        ]);

        $userId = $request->user_id;

        $mapping = DB::table('grp_role_usr_mapping')
            ->where('user_id', $userId)
            ->where('is_active', 1)
            ->first();

        if (!$mapping) {
            return response()->json([
                'Code'    => 404,
                'Status'  => 'Failed',
                'Message' => 'User mapping not found',
            ]);
        }

        $mappingId  = $mapping->grp_usr_mapping_id;
        $dashboards = DB::table('studio_dashboards')->where('is_active', 1)->get();

        $granted  = 0;
        $existing = 0;

        foreach ($dashboards as $dashboard) {
            $exists = DB::table('studio_dashboard_user_mapping')
                ->where('studio_dashboard_id', $dashboard->id)
                ->where('grp_usr_mapping_id', $mappingId)
                ->exists();

            if ($exists) {
                $existing++;
            } else {
                DB::table('studio_dashboard_user_mapping')->insert([
                    'studio_dashboard_id' => $dashboard->id,
                    'grp_usr_mapping_id'  => $mappingId,
                    'is_active'           => 1,
                    'created_at'          => now(),
                    'updated_at'          => now(),
                ]);
                $granted++;
            }
        }

        return response()->json([
            'Code'    => 200,
            'Status'  => 'Success',
            'Message' => 'Super admin access granted',
            'data'    => [
                'user_id'              => $userId,
                'mapping_id'           => $mappingId,
                'dashboards_granted'   => $granted,
                'already_had_access'   => $existing,
                'total_dashboards'     => count($dashboards),
            ],
        ]);
    }

    /**
     * Download a Looker Studio report as PDF, generated entirely on the server.
     *
     * How it works
     * ────────────
     * 1. Frontend POSTs { embed_url }.
     * 2. We extract the report UUID — this is also the Google Drive file ID.
     * 3. We authenticate as a Google Service Account (JWT → access token).
     * 4. We call the Google Drive Files.export API:
     *      GET /drive/v3/files/{reportId}/export?mimeType=application/pdf
     * 5. Stream the PDF binary back so the browser saves it directly.
     *
     * One-time setup (do this once, not per request)
     * ───────────────────────────────────────────────
     * a) Google Cloud Console → APIs & Services → Credentials → your OAuth client.
     * b) Enable "Google Drive API" for the project.
     * c) Use OAuth Playground (see getGoogleAccessToken docblock) to get a
     *    refresh token for the Google account that owns the reports.
     * d) Add to .env:
     *      GOOGLE_CLIENT_ID=...
     *      GOOGLE_CLIENT_SECRET=...
     *      GOOGLE_REFRESH_TOKEN=...
     * e) In each Looker Studio report → Share → add the Google account used
     *    in step (c) as "Viewer" (it is likely already the owner).
     */
    public function downloadPdf(Request $request)
    {
        // Puppeteer takes 90+ seconds — disable PHP's 30 s execution limit
        set_time_limit(0);

        $embedUrl = trim($request->input('embed_url', ''));

        if (!$embedUrl) {
            return response()->json(['error' => 'embed_url is required'], 400);
        }

        \Log::info('Studio PDF: headless render request', ['url' => $embedUrl]);

        // ── Build a unique temp file path for the PDF output ────────────────
        $tmpFile    = sys_get_temp_dir() . DIRECTORY_SEPARATOR
                    . 'dashboard_' . uniqid('', true) . '.pdf';
        $scriptPath = base_path('scripts/generate-pdf.js');

        // Locate `node` — try PATH first, fall back to common install locations
        $nodeCmd = 'node';
        foreach (['/usr/bin/node', '/usr/local/bin/node'] as $p) {
            if (file_exists($p)) { $nodeCmd = $p; break; }
        }

        $cmd = sprintf(
            '%s %s %s %s 2>&1',
            escapeshellcmd($nodeCmd),
            escapeshellarg($scriptPath),
            escapeshellarg($embedUrl),
            escapeshellarg($tmpFile)
        );

        \Log::info('Studio PDF: running Puppeteer', ['cmd' => $cmd]);

        exec($cmd, $output, $exitCode);

        if ($exitCode !== 0 || !file_exists($tmpFile) || filesize($tmpFile) === 0) {
            $errMsg = implode(' ', $output) ?: 'Unknown Puppeteer error';
            \Log::error('Studio PDF: Puppeteer failed', [
                'exitCode' => $exitCode,
                'output'   => $output,
            ]);
            return response()->json(['error' => 'Could not generate PDF: ' . $errMsg], 500);
        }

        $pdf      = file_get_contents($tmpFile);
        $filename = 'dashboard_' . date('Ymd_His') . '.pdf';

        @unlink($tmpFile);

        return response($pdf, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Content-Length'      => strlen($pdf),
            'Cache-Control'       => 'no-store',
        ]);
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Obtain a short-lived Google OAuth2 access token by exchanging the stored
     * refresh token.
     *
     * Required .env keys
     * ──────────────────
     *   GOOGLE_CLIENT_ID      – OAuth 2.0 client ID (from Google Cloud Console)
     *   GOOGLE_CLIENT_SECRET  – OAuth 2.0 client secret
     *   GOOGLE_REFRESH_TOKEN  – refresh token obtained once via OAuth Playground:
     *     1. https://developers.google.com/oauthplayground/
     *     2. Gear icon → "Use your own OAuth credentials" → paste ID + secret
     *     3. Step 1: Drive API v3 → pick scope drive.readonly → Authorize
     *        (sign in as the Google account that owns the Looker Studio reports)
     *     4. Step 2: Exchange authorization code for tokens → copy "Refresh token"
     *     5. Paste it here as GOOGLE_REFRESH_TOKEN
     *
     * The refresh token never expires unless you revoke it or change the
     * Google account password, so this is a one-time setup.
     */
    private function getGoogleAccessToken(string $scope = 'https://www.googleapis.com/auth/drive.readonly'): ?string
    {
        $clientId     = env('GOOGLE_CLIENT_ID');
        $clientSecret = env('GOOGLE_CLIENT_SECRET');
        $refreshToken = env('GOOGLE_REFRESH_TOKEN');

        if (!$clientId || !$clientSecret || !$refreshToken) {
            \Log::error(
                'Studio PDF: GOOGLE_CLIENT_ID, GOOGLE_CLIENT_SECRET, or '
                . 'GOOGLE_REFRESH_TOKEN is not set in .env'
            );
            return null;
        }

        $ch = curl_init('https://oauth2.googleapis.com/token');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_POSTFIELDS     => http_build_query([
                'client_id'     => $clientId,
                'client_secret' => $clientSecret,
                'refresh_token' => $refreshToken,
                'grant_type'    => 'refresh_token',
            ]),
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
        ]);

        $resp = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($resp, true);

        if (empty($data['access_token'])) {
            \Log::error('Studio PDF: failed to refresh access token', ['response' => $resp]);
            return null;
        }

        return $data['access_token'];
    }

    /**
     * Share every active Looker Studio report in studio_dashboards with a
     * given Google account via the Drive API permissions.create endpoint.
     *
     * One-shot admin operation — call it once after setting up the account.
     *
     * Requires GOOGLE_REFRESH_TOKEN to have been obtained with the
     *   https://www.googleapis.com/auth/drive  scope (full Drive, not readonly)
     * and the authenticated account must be the owner of the reports.
     *
     * POST /api/studio-share-dashboards
     * Body: { "email": "sumanth.m@dynpro.com" }
     */
    public function shareAllDashboards(Request $request)
    {
        $email = trim($request->input('email', ''));
        if (!$email) {
            return response()->json(['error' => 'email is required'], 400);
        }

        // Full Drive scope needed to create permissions
        $accessToken = $this->getGoogleAccessToken('https://www.googleapis.com/auth/drive');
        if (!$accessToken) {
            return response()->json([
                'error' => 'Could not obtain Google access token. '
                         . 'Ensure GOOGLE_CLIENT_ID, GOOGLE_CLIENT_SECRET, and '
                         . 'GOOGLE_REFRESH_TOKEN (with drive scope) are set in .env.',
            ], 500);
        }

        // Collect all unique report UUIDs from the database
        $embedUrls = DB::table('studio_dashboards')
            ->where('is_active', 1)
            ->whereNotNull('embed_url')
            ->pluck('embed_url');

        $reportIds = collect();
        foreach ($embedUrls as $url) {
            if (preg_match('/\/reporting\/([^\/\?#]+)/', $url, $m)) {
                $reportIds->push($m[1]);
            }
        }
        $reportIds = $reportIds->unique()->values();

        $successCount = 0;
        $alreadyCount = 0;
        $failed       = [];

        foreach ($reportIds as $reportId) {
            $ch = curl_init(
                "https://www.googleapis.com/drive/v3/files/{$reportId}/permissions"
                . '?sendNotificationEmail=false'
            );
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_TIMEOUT        => 30,
                CURLOPT_POSTFIELDS     => json_encode([
                    'role'         => 'reader',
                    'type'         => 'user',
                    'emailAddress' => $email,
                ]),
                CURLOPT_HTTPHEADER => [
                    "Authorization: Bearer {$accessToken}",
                    'Content-Type: application/json',
                ],
            ]);

            $body     = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200 || $httpCode === 201) {
                $successCount++;
            } else {
                $apiError = json_decode($body, true);
                $reason   = $apiError['error']['errors'][0]['reason'] ?? null;

                // "sharingNotSupported" or already shared — treat as soft success
                if ($reason === 'sharingNotSupported' || $reason === 'alreadyExists') {
                    $alreadyCount++;
                } else {
                    $failed[$reportId] = $apiError['error']['message'] ?? "HTTP {$httpCode}";
                }
            }
        }

        \Log::info('Studio shareAllDashboards complete', [
            'email'        => $email,
            'total'        => $reportIds->count(),
            'success'      => $successCount,
            'already'      => $alreadyCount,
            'failed_count' => count($failed),
        ]);

        return response()->json([
            'message'       => "Processed {$reportIds->count()} unique reports for {$email}",
            'shared'        => $successCount,
            'already_had'   => $alreadyCount,
            'failed_count'  => count($failed),
            'failed_detail' => $failed,
        ]);
    }

    /**
     * Check if user has access to a studio dashboard by its auto-increment PK.
     */
    private function checkUserAccess($userId, $studioDashboardId): bool
    {
        $user = DB::table('users')->where('id', $userId)->first();

        if (!$user) {
            \Log::warning('User not found in access check', ['userId' => $userId]);
            return false;
        }

        // Super-admin groups bypass the mapping table
        $superAdminGroups = ['1001', '1003', '1011'];
        if (in_array($user->user_group_id, $superAdminGroups)) {
            \Log::info('Super admin access granted', [
                'userId'           => $userId,
                'user_group_id'    => $user->user_group_id,
                'studioDashboardId'=> $studioDashboardId,
            ]);
            return true;
        }

        if ($user->is_admin) {
            \Log::info('Admin access granted via is_admin flag', [
                'userId'           => $userId,
                'studioDashboardId'=> $studioDashboardId,
            ]);
            return true;
        }

        $access = DB::table('studio_dashboard_user_mapping')
            ->join('grp_role_usr_mapping',
                'studio_dashboard_user_mapping.grp_usr_mapping_id', '=',
                'grp_role_usr_mapping.grp_usr_mapping_id')
            ->where('grp_role_usr_mapping.user_id', $userId)
            ->where('studio_dashboard_user_mapping.studio_dashboard_id', $studioDashboardId)
            ->where('studio_dashboard_user_mapping.is_active', 1)
            ->where('grp_role_usr_mapping.is_active', 1)
            ->first();

        $hasAccess = !is_null($access);

        \Log::info('User access check result', [
            'userId'           => $userId,
            'studioDashboardId'=> $studioDashboardId,
            'hasAccess'        => $hasAccess,
        ]);

        return $hasAccess;
    }
}