<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Grp_role_usr_mapping extends Model
{
    protected $table = 'grp_role_usr_mapping';
    protected $primaryKey = 'grp_usr_mapping_id';
    protected $fillable = [ 
        'user_id',
        'group_id',
        'role_id',
        'user_unique_id',
        'role','users',
        'looker',
        'snowflake',
        'group_module',
        'processing',
        'roles',
        'clients',
        'dashboards',
        'reports',
        'user_add',
        'user_edit',
        'user_delete',
        'user_view',
        'client_add',
        'client_edit',
        'client_delete',
        'client_view',
        'is_active',
        'role_add',
        'role_edit',
        'role_delete',
        'role_view',
        'group_add',
        'group_edit',
        'group_delete',
        'group_view',
        'report_add',
        'report_edit',
        'report_delete',
        'report_view',
        'generate_report',
        'generate_report_add',
        'generate_report_edit',
        'generate_report_delete',
        'generate_report_view',
        'invite_user',
        'permission_btn',
        'approval_pending_user',
        'referral',
        'created_by'
        ];
}
