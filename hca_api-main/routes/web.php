<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
 |--------------------------------------------------------------------------
 | Application Routes
 |--------------------------------------------------------------------------
 |
 | Here is where you can register all of the routes for an application.
 | It is a breeze. Simply tell Lumen the URIs it should respond to
 | and give it the Closure to call when that URI is requested.
 |
 */

$router->get('/', function () use ($router) {
    return $router->app->version();
});


$router->group(['prefix' => 'api'], function () use ($router) {
    $router->post('/register', 'AuthController@register');
    $router->post('/update_registration', 'AuthController@update_registration');
    $router->post('/login', 'AuthController@login');
    $router->post('/loginWithOtp', 'AuthController@loginWithOtp');
    $router->post('/login_otp', 'AuthController@login_otp');
    $router->post('/resend_login_otp', 'AuthController@resend_login_otp');
    $router->post('/refresh', 'AuthController@refresh');
    $router->post('/validate_otp', 'AuthController@validateOtp');
    $router->post('/resend_otp', 'AuthController@ResendOtp');
    $router->post('/forget_password', 'AuthController@ForgetPassword');
    $router->post('/forget_password_change', 'AuthController@ForgetPasswordChange');
    $router->post('/change_password', 'AuthController@ChangePassword');
    $router->post('twofa_check', 'AuthController@twofa_check');
    $router->post('ValidateOTP_NewReg', 'AuthController@ValidateOTP_NewReg');
    $router->post('QrCode_NewReg', 'AuthController@QrCode');
    //Get In Touch
    $router->post('/get_in_touch', 'AuthController@get_in_touch');

    $router->group(['middleware' => 'auth'], function () use ($router) {
            //Auth
            $router->post('/logout', 'AuthController@logout');

            //Navigation
            $router->get('/clientCateWiseUserAccess', 'LookerDataController@getUserAccessClientsCategoryWise');
            $router->get('/clientCateWiseUserAccessNew', 'LookerDataController@getUserAccessClientsCategoryWiseNew');
            $router->get('/getUserAccessClients', 'LookerDataController@getUserAccessClients');
            $router->get('/getUserAccessClientsNew', 'LookerDataController@getUserAccessClientsNew');
            $router->get('/getUserAccessFolders', 'LookerDataController@getUserAccessFolders');
            $router->get('/getUserAccessdashboards', 'LookerDataController@getUserAccessdashboards');

            $router->get('/getLookerClientCategoryWise', 'LookerDataController@getLookerClientCategoryWise');
            $router->get('/getLookerClientCategoryWiseNew', 'LookerDataController@getLookerClientCategoryWiseNew');
            $router->get('/getLookerClient', 'LookerDataController@getLookerClient');
            $router->get('/getLookerClientFolder', 'LookerDataController@getLookerClientFolder');
            $router->get('/getLookerFolderDashboard', 'LookerDataController@getLookerFolderDashboard');

            $router->post('getDashboard', 'LookerDataController@getDashboard');

            $router->get('getLookerFolderStructure', 'LookerDataController@getLookerFolderStructure');
            $router->get('getParentPHM', 'LookerDataController@getParentPHM');
            $router->get('getSchema', 'LookerDataController@getSchema');
            $router->get('getLookerGroups', 'LookerDataController@getLookerGroups');

            //User Module
            $router->get('user', 'UserController@index');
            $router->post('user', 'UserController@store');
            $router->get('user/{id}', 'UserController@edit');
            $router->put('user/{id}', 'UserController@update');
            $router->delete('user/{id}', 'UserController@destroy');
            $router->get('change_theme/{flag}', 'UserController@user_theme');

            //Role Module
            $router->get('roles', 'RolesController@index');
            $router->post('roles', 'RolesController@store');
            $router->get('roles/{id}', 'RolesController@edit');
            $router->put('roles/{id}', 'RolesController@update');
            $router->delete('roles/{id}', 'RolesController@destroy');

            //Group Module
            $router->get('groups', 'GroupController@index');
            $router->post('groups', 'GroupController@store');
            $router->get('groups/{group_id}/{role_id}', 'GroupController@edit');
            $router->put('groups/{id}', 'GroupController@update');
            $router->delete('groups/{group_id}/{role_id}', 'GroupController@destroy');
            $router->get('group_role_mapping/{id}', 'GroupController@getGroupRoleMapping');
            $router->get('grouplist', 'GroupController@grouplist');

            //Client Module
            $router->get('clients', 'ClientController@index');
            $router->post('clients', 'ClientController@store');
            $router->get('clients/{id}', 'ClientController@edit');
            $router->post('client_update', 'ClientController@update');
            $router->delete('clients/{id}', 'ClientController@destroy');

            //User Access Control Module
            $router->get('users_access_control', 'UserAccessControlController@index');
            $router->post('users_access_control', 'UserAccessControlController@store');
            $router->get('users_access_control/{id}', 'UserAccessControlController@edit');
            $router->put('users_access_control/{id}', 'UserAccessControlController@update');
            $router->delete('users_access_control/{id}', 'UserAccessControlController@destroy');
            $router->get('getGroupRole', 'UserAccessControlController@getGroupRole');
            $router->get('getGroupRoleUser', 'UserAccessControlController@getGroupRoleUser');

            //Invite user module
            $router->post('invite_user', 'InviteUserController@store');

            //Looker module
            $router->get('looker', 'LookerMasterController@index');
            $router->get('looker/{id}', 'LookerMasterController@edit');
            $router->put('looker/{id}', 'LookerMasterController@update');

            //PHM Automation module
            $router->get('phm_automation_report_list', 'PHMAutomationController@index');
            $router->get('phm_automation_client_list', 'PHMAutomationController@client_list');
            $router->get('phm_automation_year_list', 'PHMAutomationController@year_list');
            $router->post('phm_automation_store', 'PHMAutomationController@store');
            $router->delete('phm_automation_destroy/{id}', 'PHMAutomationController@destroy');
            $router->get('phm_automation_download', 'PHMAutomationController@download');

            //Patient Summary Automation module
            $router->get('ps_automation_report_list', 'PatientSummaryAutomationController@index');
            $router->get('ps_automation_client_list', 'PatientSummaryAutomationController@client_list');
            $router->get('ps_automation_patient_list', 'PatientSummaryAutomationController@get_patient');
            $router->post('ps_automation_store', 'PatientSummaryAutomationController@store');
            $router->get('ps_automation_listof_patient/{id}', 'PatientSummaryAutomationController@list');
            $router->get('ps_automation_download_zip', 'PatientSummaryAutomationController@download_zip');
            $router->delete('ps_automation_destroy/{id}', 'PatientSummaryAutomationController@destroy');

            //Localization
            $router->get('localized_clients', 'LocalizationController@localized_clients');
            $router->get('localized_phm_clients', 'LocalizationController@localized_phm_clients');
            $router->get('localized_all_dashboards', 'LocalizationController@localized_all_dashboards');
            $router->get('pull_looker_clients', 'LocalizationController@pull_looker_clients');
            $router->get('pull_looker_phmfolders', 'LocalizationController@pull_looker_phmfolders');
            $router->get('pull_looker_dashboards', 'LocalizationController@pull_looker_dashboards');

            //Profile
            $router->get('profile', 'ProfileController@profile');
            $router->post('profile_update', 'ProfileController@update');
            $router->get('remove_photo', 'ProfileController@remove_photo');

            $router->get('get_clientCategory', 'ClientController@getclient_categort');
            $router->get('get_subcategory', 'ClientController@getclient_subcategort');

            //PHM Template API
            $router->get('phm', 'PHMController@index');
            $router->get('phm/clients', 'PHMController@PHM_clientlist');
            $router->post('phmStore', 'PHMController@store');
            $router->post('phm/sections', 'PHMController@store_sections');
            $router->post('phm/subsections', 'PHMController@store_subsections');
            $router->post('uploadDoc', 'PHMController@uploadDoc');
            $router->post('downloadFormattedDoc', 'PHMController@DownloadFormattedDoc');
            $router->get('getFormattedCopy', 'PHMController@getFormattedCopy');
            $router->get('getReportFormattedCopy', 'PHMController@getReportFormattedCopy');
            $router->get('getLooks/{id}', 'PHMController@getLooks');
            // $router->post('phm/store_looks', 'PHMController@store_looks');
            $router->get('phm/{id}', 'PHMController@edit');
            $router->put('phm/{id}', 'PHMController@update');
            $router->delete('phm/{id}', 'PHMController@destroy');
            $router->post('copyPHM', 'PHMController@copyPHM');
            // $router->get('phm/sections/{id}', 'PHMController@get_section');
            // $router->get('phm/subsections/{id}', 'PHMController@get_subsections');
            // $router->post('phm/get_looks/{id}', 'PHMController@get_looks');
            // $router->put('phm/{id}', 'PHMController@store');
            // $router->put('phm/sections/{id}', 'PHMController@store_sections');
            // $router->put('phm/subsections/{id}', 'PHMController@store_subsections');
            // $router->put('phm/store_looks/{id}', 'PHMController@store_looks');
            // $router->put('download_word/{id}', 'PHMController@download');
    
            //MFA Routes
            // $router->post('build_qr','MfaController@build_qrcode');
    
            //External API List
            $router->post('referral_uuid', 'ExternalApiController@referral_uuid');
            $router->post('QrCode', 'ExternalApiController@QrCode');
            $router->post('validate_2fa_otp', 'ExternalApiController@ValidateOTP');
            $router->post('disable_2fa', 'ExternalApiController@Disable2fa');


            //User Activity Log
            $router->get('userActivitylogs', 'UserActivityLogController@index');
            $router->post('userActivitylogs', 'UserActivityLogController@store');

            //Localization
            $router->get('fetchfolder', 'LocalizationController@getFolder');
            $router->get('fetchdash', 'LocalizationController@getdash');
            $router->get('parentfolder', 'LocalizationController@parent_folder');
            $router->get('parentphm', 'LocalizationController@parent_phm');
            $router->get('schema', 'LocalizationController@schema');

            $router->get('gen_repo', 'GenerateReportController@handle');
            $router->get('down__pdf', 'GenerateReportController@down__pdf');
            $router->post('direct_download_pdf', 'GenerateReportController@directDownloadByClient');
            $router->post('update_flag', 'GenerateReportController@update_flag');




        }
        );


    });
URL::forceScheme('https');