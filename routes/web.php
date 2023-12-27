<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DownloadController;
use \App\Http\Controllers\IndexController;
use \App\Http\Controllers\WebhookController;
use \Illuminate\Support\Facades\Artisan;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('/', [IndexController::class, 'index'])->name('login');
Route::get('/index/termuse', 'IndexController@termcon');
Route::get('/index/loginsupport', 'IndexController@loginhelp');
Route::get('/index/reset', 'IndexController@restore');
Route::get('/index/changepass', 'IndexController@changecode');
Route::post('/submit','IndexController@check_login')->name('submit');
Route::post('/resetpass','IndexController@reset_password')->name('resetpass');
Route::post('/changepassword','IndexController@re_password')->name('changepassword');
Route::get('/index/emailauthentication', 'IndexController@auth_email');
Route::post('/submitcode','IndexController@post_code')->name('submitcode');
Route::post('/sendcode','IndexController@give_code')->name('sendcode');
Route::get('/index/firstchangepass', 'IndexController@first_time_pass');
Route::get('/index/changepartner', 'IndexController@partner_swap');
Route::post('/firstchangepasswordsubmit','IndexController@first_pass_post')->name('firstchangepasswordsubmit');
Route::get('/index/logout','IndexController@logout')->name('logout');

////dashboard routes
Route::get('/dashboard/index', 'Dashboard\IndexController@main')->name('dashboard')->middleware(['auth', 'userAuth', 'loginTrack']);
Route::post('/dashboard/update-dashboard', 'Dashboard\IndexController@change_dashboard')->name('update-dashboard')->middleware(['auth', 'userAuth', 'loginTrack']);
Route::get('/dashboard/test-email', 'Dashboard\IndexController@test_email')->name('test-email-helper')->middleware(['auth', 'userAuth', 'loginTrack']);

Route::get('/test-cpanel-api',[\App\Http\Controllers\Dashboard\IndexController::class, 'cpanel_api'])->name('test-cpanel-api')->middleware('auth', 'userAuth', 'loginTrack');
Route::get('/test-route-53',[\App\Http\Controllers\Dashboard\IndexController::class, 'aws_api'])->name('test-route-53')->middleware('auth', 'userAuth', 'loginTrack');
Route::get('/dashboard/profile', 'Dashboard\ProfileController@main')->middleware('auth', 'userAuth', 'loginTrack')->name('my-profile');

Route::get('/dashboard/edit-profile', 'Dashboard\ProfileController@update_profile')->middleware('auth', 'userAuth', 'loginTrack')->name('edit-profile');

Route::post('/profilesubmit','Dashboard\ProfileController@store_profile')->name('profilesubmit')->middleware('auth', 'userAuth', 'loginTrack');
Route::post('/edit-profile-submit','Dashboard\ProfileController@store_edit')->name('edit-profile-submit')->middleware('auth', 'userAuth', 'loginTrack');

Route::get('/dashboard/partnerusers', 'Dashboard\PartnerusersController@main')->name('partner-users')->middleware(['auth', 'userAuth', 'loginTrack']);
Route::get('/dashboard/partnerusers/add', 'Dashboard\PartnerusersController@insert')->middleware('auth', 'userAuth', 'loginTrack')->name('partner-users-add');
Route::get('/dashboard/refresh-session', 'Dashboard\IndexController@session_refresh')->middleware('auth', 'userAuth', 'loginTrack')->name('refresh-session');

Route::post('/partneruserssubmit','Dashboard\PartnerusersController@store_insert')->name('partneruserssubmit')->middleware('auth', 'userAuth', 'loginTrack');
Route::get('/dashboard/partnerusers/ajax','Dashboard\PartnerusersController@feature_ajax')->middleware('auth', 'userAuth', 'loginTrack');
Route::get('/dashboard/partnerusers/edit/{id}','Dashboard\PartnerusersController@change')->name('partner-users-edit')->middleware('auth', 'userAuth', 'loginTrack');
Route::get('/dashboard/partnerusers/delete','Dashboard\PartnerusersController@remove')->name('partner-users-delete')->middleware('auth', 'userAuth', 'loginTrack');
Route::get('/dashboard/partnerusers/exportexcel','Dashboard\PartnerusersController@excel_export')->name('partnerusers-export-excel')->middleware('auth', 'userAuth', 'loginTrack');
Route::get('/dashboard/partnerusers/permission/{id}', [\App\Http\Controllers\Dashboard\PartnerusersController::class, 'permit'])->name('partner-permission')->middleware('auth', 'userAuth', 'loginTrack');
Route::get('/dashboard/partnerusers/exportpdf','Dashboard\PartnerusersController@pdf_export')->middleware('auth', 'userAuth', 'loginTrack');
Route::post('/permissionsubmit','Dashboard\PartnerusersController@permit_store')->name('permissionsubmit')->middleware('auth', 'userAuth', 'loginTrack');
Route::get('/dashboard/partnerusers/fetchaccess','Dashboard\PartnerusersController@get_access')->name('partner-fetchaccess')->middleware('auth', 'userAuth');


Route::get('/dashboard/weusers','Dashboard\WeusersController@main')->name('we-users')->middleware('auth', 'userAuth', 'loginTrack');
Route::get('/dashboard/weusers/add', 'Dashboard\WeusersController@new')->middleware('auth', 'userAuth')->name('we-users-add');
Route::post('/weuserssubmit','Dashboard\WeusersController@new_store')->name('weuserssubmit')->middleware('auth', 'userAuth', 'loginTrack');
Route::get('/dashboard/weusers/ajax', 'Dashboard\WeusersController@feature_ajax')->middleware('auth', 'userAuth', 'loginTrack');
Route::get('/dashboard/weusers/edit/{id}','Dashboard\WeusersController@change')->name('we-users-edit')->middleware('auth', 'userAuth', 'loginTrack');
Route::get('/dashboard/weusers/delete','Dashboard\WeusersController@remove')->name('we-users-delete')->middleware('auth', 'userAuth', 'loginTrack');
Route::get('/dashboard/weusers/exportexcel','Dashboard\WeusersController@excel_export')->middleware('auth', 'userAuth', 'loginTrack');
Route::get('/dashboard/weusers/exportpdf','Dashboard\WeusersController@pdf_export')->middleware('auth', 'userAuth', 'loginTrack');

Route::get('/dashboard/configuration-email','Dashboard\ConfigemailController@main')->name('configuration-email')->middleware('auth', 'userAuth', 'loginTrack');
Route::get('/dashboard/configemail/ajax', 'Dashboard\ConfigemailController@feature_ajax')->middleware('auth', 'userAuth', 'loginTrack')->name('config-email-ajax');
Route::get('/dashboard/configemail/edit/{id}', 'Dashboard\ConfigemailController@change')->name('configuration-email-edit')->middleware('auth', 'userAuth', 'loginTrack');
Route::post('/configsubmit','Dashboard\ConfigemailController@change_store')->name('configsubmit')->middleware('auth', 'userAuth', 'loginTrack');


Route::get('/dashboard/system-email-logs','Dashboard\SystememaillogsController@main')->name('system-email-logs')->middleware('auth', 'userAuth', 'loginTrack');
Route::get('/dashboard/systememaillogs/ajax', 'Dashboard\SystememaillogsController@feature_ajax')->name('system-email-logs-ajax')->middleware('auth', 'userAuth', 'loginTrack');
Route::get('/dashboard/systememaillogs/delete', 'Dashboard\SystememaillogsController@remove')->middleware('auth', 'userAuth', 'loginTrack')->name('system-email-logs-delete');
Route::get('/dashboard/systememaillogs/edit/{id}', 'Dashboard\SystememaillogsController@change')->name('system-email-logs-edit')->middleware('auth', 'userAuth', 'loginTrack');
Route::post('/emaillogssubmit','Dashboard\SystememaillogsController@change_store')->name('emaillogssubmit')->middleware('auth', 'userAuth', 'loginTrack');

Route::get('/dashboard/myusers','Dashboard\MyusersController@main')->name('my-users')->middleware('auth', 'userAuth', 'loginTrack');

Route::get('/dashboard/myusers/add', 'Dashboard\MyusersController@new')->name('my-user-form')->middleware('auth', 'userAuth', 'loginTrack');
Route::post('/myusersubmit','Dashboard\MyusersController@new_store')->name('myusersubmit')->middleware('auth', 'userAuth', 'loginTrack');
Route::get('/dashboard/myusers/ajax', 'Dashboard\MyusersController@feature_ajax')->middleware('auth', 'userAuth', 'loginTrack');
Route::get('/dashboard/myusers/edit/{id}', 'Dashboard\MyusersController@change')->name('my-user-edit')->middleware('auth', 'userAuth', 'loginTrack');
Route::get('/dashboard/myusers/delete', 'Dashboard\MyusersController@remove')->name('my-user-delete')->middleware('auth', 'userAuth', 'loginTrack');
Route::get('/dashboard/myusers/exportexcel', 'Dashboard\MyusersController@excel_export')->name('my-users-export-excel')->middleware('auth', 'userAuth', 'loginTrack');
Route::get('/dashboard/myusers/exportpdf', 'Dashboard\MyusersController@pdf_export')->name('my-users-export-pdf')->middleware('auth', 'userAuth', 'loginTrack');
Route::get('/dashboard/myusers/permission/{id}', 'Dashboard\MyusersController@permit')->name('my-users-permission')->middleware('auth', 'userAuth', 'loginTrack');
Route::get('/dashboard/myusers/fetchaccess', 'Dashboard\MyusersController@get_access')->name('my-users-fetch-access')->middleware('auth', 'userAuth', 'loginTrack');
Route::post('/mpermissionsubmit','Dashboard\MyusersController@permit_store')->name('mpermissionsubmit')->middleware('auth', 'userAuth', 'loginTrack');

Route::get('/show-mail','Dashboard\PartnerusersController@showMail')->name('show-mail')->middleware('auth', 'userAuth', 'loginTrack');


Route::get('/student', [\App\Http\Controllers\Student\IndexController::class, 'index'])->name('student-list')->middleware('auth', 'userAuth', 'loginTrack');
Route::get('/student/search',[\App\Http\Controllers\Student\IndexController::class, 'search'])->name('student-search')->middleware('auth', 'userAuth', 'loginTrack');
Route::get('/student/enrollment-search',[\App\Http\Controllers\Student\StudentEnrollmentController::class, 'search'])->name('enrollment-search')->middleware('auth', 'userAuth', 'loginTrack');
Route::get('/student/enrollment-export-excel', [\App\Http\Controllers\Student\StudentEnrollmentController::class, 'exportExcel'])->name('enrollment-export-excel')->middleware('auth', 'userAuth', 'loginTrack');
Route::get('/student/enrollment-export-pdf', [\App\Http\Controllers\Student\StudentEnrollmentController::class, 'exportPdf'])->name('enrollment-export-pdf')->middleware('auth', 'userAuth', 'loginTrack');

Route::get('/student/load-more',[\App\Http\Controllers\Student\IndexController::class, 'loadMore'])->name('student-load-more')->middleware('auth', 'userAuth', 'loginTrack');
Route::get('/student/add', [\App\Http\Controllers\Student\IndexController::class, 'add'])->name('student-add')->middleware('auth', 'userAuth', 'loginTrack');
Route::post('/student/store',[\App\Http\Controllers\Student\IndexController::class, 'store'])->name('student-store')->middleware('auth' , 'userAuth', 'loginTrack');
Route::get('/student/delete', [\App\Http\Controllers\Student\IndexController::class, 'delete'])->name('student-delete')->middleware('auth', 'userAuth', 'loginTrack');
Route::get('/student/export-excel', [\App\Http\Controllers\Student\IndexController::class, 'exportExcel'])->name('student-export-excel')->middleware('auth', 'userAuth', 'loginTrack');
Route::get('/student/export-pdf', [\App\Http\Controllers\Student\IndexController::class, 'exportPdf'])->name('student-export-pdf')->middleware('auth', 'userAuth', 'loginTrack');
Route::get('/get-programs', [\App\Http\Controllers\Student\IndexController::class, 'getPrograms'])->name('get-programs')->middleware('auth', 'userAuth', 'loginTrack');
Route::get('/student/template-download', [\App\Http\Controllers\Student\IndexController::class, 'templatedownload'])->name('student-template-file')->middleware('auth', 'userAuth', 'loginTrack');
//Route::get('/upload-student-sheet', [\App\Http\Controllers\Student\IndexController::class, 'uploadStudent'])->name('upload-student-sheet')->middleware('auth', 'userAuth', 'loginTrack');
Route::get('/student/student-popup', [\App\Http\Controllers\Student\StudentEnrollmentController::class, 'popup'])->name('student-popup')->middleware('auth', 'userAuth', 'loginTrack');
Route::post('/student/requeststore',[\App\Http\Controllers\Student\StudentEnrollmentController::class, 'requestStore'])->name('student-request-store')->middleware('auth' , 'userAuth', 'loginTrack');
Route::post('/student/activity-report-save',[\App\Http\Controllers\Student\StudentEnrollmentController::class, 'activityReportStore'])->name('student-activity-report-save')->middleware('auth' , 'userAuth', 'loginTrack');

Route::post('/student-import-file', [\App\Http\Controllers\Student\StudentimportController::class, 'importFile'])->name('student-import-file')->middleware('auth', 'userAuth', 'loginTrack');

Route::get('/download/{id}', [\App\Http\Controllers\Student\StudentimportController::class, 'downloadImportAudit'])->name('download-import-file')->middleware('auth', 'userAuth', 'loginTrack');

Route::get('/student/import-skipped-record/{list_type}/{id}', [\App\Http\Controllers\Student\StudentimportController::class, 'importSkippedRecord'])->name('import-skipped-record')->middleware('auth', 'userAuth', 'loginTrack');

//Route::get('/student/import-warning-record/{id}', [\App\Http\Controllers\Student\StudentimportController::class, 'importSkippedRecord'])->name('import-warning-record')->middleware('auth', 'userAuth', 'loginTrack');

Route::get('/student/download-voucher/{id}', [\App\Http\Controllers\Student\IndexController::class, 'downloadVoucher'])->name('student-download-voucher')->middleware('auth', 'userAuth', 'loginTrack');
Route::get('/student/detail/{id}', [\App\Http\Controllers\Student\IndexController::class, 'detail'])->name('student-detail')->middleware('auth', 'userAuth', 'loginTrack');





Route::get('/student/leads','Student\LeadsController@index')->name('leads')->middleware('auth', 'userAuth', 'loginTrack');
Route::get('/student/leads/add','Student\LeadsController@add')->name('leads-add')->middleware('auth', 'userAuth', 'loginTrack');
Route::post('/leadssubmit','Student\LeadsController@store')->name('leadssubmit')->middleware('auth', 'userAuth', 'loginTrack');
Route::get('/student/leads/ajax','Student\LeadsController@ajax')->name('leads-search')->middleware('auth', 'userAuth', 'loginTrack');
Route::get('/student/leads/edit/{id}','Student\LeadsController@edit')->name('leads-edit')->middleware('auth', 'userAuth', 'loginTrack');
Route::get('/student/leads/view/{id}','Student\LeadsController@view')->name('leads-view')->middleware('auth', 'userAuth', 'loginTrack');
Route::get('/student/leads/exportpdf','Student\LeadsController@exportpdf')->name('leads-export-to-pdf')->middleware('auth', 'userAuth', 'loginTrack');
Route::get('/student/leads/exportexcel','Student\LeadsController@exportexcel')->name('leads-export-to-excel')->middleware('auth', 'userAuth', 'loginTrack');
Route::get('/student/leads/delete','Student\LeadsController@delete')->name('leads-delete')->middleware('auth', 'userAuth', 'loginTrack');

Route::get('/student/myinstitution','Student\MyinstitutionController@index')->name('myinstitution')->middleware('auth', 'userAuth', 'loginTrack');
Route::get('/student/myinstitution-search','Student\MyinstitutionController@ajax')->name('myinstitution-search')->middleware('auth', 'userAuth', 'loginTrack');
Route::get('/student/myinstitution-export-pdf','Student\MyinstitutionController@exportpdf')->name('myinstitution-export-pdf')->middleware('auth', 'userAuth', 'loginTrack');
Route::get('/student/myinstitution-export-excel','Student\MyinstitutionController@exportexcel')->name('myinstitution-export-excel')->middleware('auth', 'userAuth', 'loginTrack');
Route::post('/student/myinstitution-update','Student\MyinstitutionController@store')->name('myinstitution-update')->middleware('auth', 'userAuth', 'loginTrack');

Route::get('/student/importaudit','Student\ImportauditController@index')->name('import-audit')->middleware('auth', 'userAuth', 'loginTrack');
Route::get('/student/student-enrollment','Student\StudentEnrollmentController@index')->name('student-enrollment')->middleware('auth', 'userAuth', 'loginTrack');
Route::get('/student/activity-log/{enrollment_id}','Student\StudentEnrollmentController@activityLog')->name('student-activity-log')->middleware('auth', 'userAuth', 'loginTrack');
Route::get('/student/activity-progress/{enrollment_id}','Student\StudentEnrollmentController@activityProgress')->name('student-activity-progress')->middleware('auth', 'userAuth', 'loginTrack');
//Route::post('/student/student-activity-report-save','Student\StudentEnrollmentController@userActivityReportSave')->name('student-activity-report-save')->middleware('auth', 'userAuth', 'loginTrack');
Route::get('/student/imported-file-search','Student\ImportauditController@ajax')->name('imported-file-search')->middleware('auth', 'userAuth', 'loginTrack');
Route::get('/student/importaudit-export-excel','Student\ImportauditController@exportexcel')->name('importaudit-export-excel')->middleware('auth', 'userAuth', 'loginTrack');
Route::get('/student/importaudit-export-pdf','Student\ImportauditController@exportpdf')->name('importaudit-export-pdf')->middleware('auth', 'userAuth', 'loginTrack');
Route::get('/student/importaudit-delete','Student\ImportauditController@delete')->name('importaudit-delete')->middleware('auth', 'userAuth', 'loginTrack');

Route::post('/student/set-partner', [\App\Http\Controllers\Student\IndexController::class, 'setPartner'])->name('set-partner')->middleware('auth', 'userAuth', 'loginTrack');

Route::get('/student/map-my-student', [\App\Http\Controllers\Student\IndexController::class, 'mapMyStudent'])->name('map-my-student')->middleware('auth', 'userAuth', 'loginTrack');



Route::get('/dashboard/institute-profile', [\App\Http\Controllers\Dashboard\InstituteProfileController::class, 'index'])->name('institute-profile')->middleware('auth', 'userAuth', 'loginTrack');

Route::post('/dashboard/update-institute-logo', [\App\Http\Controllers\Dashboard\InstituteProfileController::class, 'updateInstituteLogo'])->name('update-institute-logo')->middleware('auth', 'userAuth', 'loginTrack');

Route::post('/dashboard/update-institute-contact', [\App\Http\Controllers\Dashboard\InstituteProfileController::class, 'updateInstituteContact'])->name('update-institute-contact')->middleware('auth', 'userAuth', 'loginTrack');

Route::post('/dashboard/update-institute-address', [\App\Http\Controllers\Dashboard\InstituteProfileController::class, 'updateInstituteAddress'])->name('update-institute-address')->middleware('auth', 'userAuth', 'loginTrack');

Route::get('/catalog/index', [\App\Http\Controllers\CatalogController::class, 'index'])->name('catalog-listing')->middleware('auth', 'userAuth', 'loginTrack');

Route::get('/catalog/search', [\App\Http\Controllers\CatalogController::class, 'search'])->name('catalog-search')->middleware('auth', 'userAuth', 'loginTrack');

Route::post('/catalog/change-status', [\App\Http\Controllers\CatalogController::class, 'changeStatus'])->name('catalog-change-status')->middleware('auth', 'userAuth', 'loginTrack');

Route::get('/catalog/export-excel', [\App\Http\Controllers\CatalogController::class, 'exportExcel'])->name('catalog-export-excel')->middleware('auth', 'userAuth', 'loginTrack');
Route::get('/catalog/export-pdf', [\App\Http\Controllers\CatalogController::class, 'exportPdf'])->name('catalog-export-pdf')->middleware('auth', 'userAuth', 'loginTrack');

Route::get('/marketing/index', [\App\Http\Controllers\Marketing\MarketingController::class, 'index'])->name('marketing-form')->middleware('auth', 'userAuth', 'loginTrack');

Route::get('/marketing/{announce_type}', [\App\Http\Controllers\Marketing\MarketingController::class, 'announce'])->name('announcement')->middleware('auth', 'userAuth', 'loginTrack');

/*Route::get('/marketing/announcement', [\App\Http\Controllers\Marketing\MarketingController::class, 'index'])->name('marketing-announcement')->middleware('auth', 'userAuth', 'announcement');

Route::get('/marketing/updates', [\App\Http\Controllers\Marketing\MarketingController::class, 'updates'])->name('marketing-updates')->middleware('auth', 'userAuth', 'loginTrack');*/

Route::post('/marketing/save', [\App\Http\Controllers\Marketing\MarketingController::class, 'store'])->name('marketing-store')->middleware('auth', 'userAuth', 'loginTrack');

Route::post('/marketing/collateral-save', [\App\Http\Controllers\Marketing\MarketingController::class, 'collateralStore'])->name('marketing-collateral-store')->middleware('auth', 'userAuth', 'loginTrack');

Route::get('/marketing/collateral/add', [\App\Http\Controllers\Marketing\MarketingController::class, 'collateral'])->name('request-collateral')->middleware('auth', 'userAuth', 'loginTrack');

Route::get('/we-templates', [\App\Http\Controllers\Cms\IndexController::class, 'index'])->name('we-templates')->middleware(['auth', 'userAuth', 'loginTrack']);

Route::get('/course-marketing', [\App\Http\Controllers\Cms\IndexController::class, 'courseMarketing'])->name('course-marketing')->middleware(['auth', 'userAuth', 'loginTrack']);

Route::get('/funding-sources', [\App\Http\Controllers\Cms\IndexController::class, 'fundingSources'])->name('funding-sources')->middleware(['auth', 'userAuth', 'loginTrack']);

Route::get('/social-media', [\App\Http\Controllers\Cms\IndexController::class, 'socialMedia'])->name('social-media')->middleware(['auth', 'userAuth', 'loginTrack']);

Route::get('/course-marketing/{course_marketing}', [\App\Http\Controllers\Cms\IndexController::class, 'courseMarketingCategory'])->name('course-marketing-category')->middleware(['auth', 'userAuth', 'loginTrack']);
Route::post('/store-template', [\App\Http\Controllers\Cms\IndexController::class, 'storeTemplate'])->name('store-template')->middleware(['auth', 'userAuth', 'loginTrack']);

Route::get('/top-selling-programs', [\App\Http\Controllers\Marketing\MarketingController::class, 'topSellingPrograms'])->name('top-selling-programs')->middleware('auth', 'userAuth', 'loginTrack');

Route::get('/marketing-collateral', [\App\Http\Controllers\Marketing\MarketingController::class, 'marketingCollateral'])->name('marketing-collateral')->middleware(['auth', 'userAuth', 'loginTrack']);


Route::get('/marketing-collateral/{course_marketing}', [\App\Http\Controllers\Marketing\MarketingController::class, 'courseMarketing'])->name('marketing-collateral-marketing-category')->middleware(['auth', 'userAuth', 'loginTrack']);

Route::get('/marketing-collateral/course-marketing/{course_marketing}', [\App\Http\Controllers\Marketing\MarketingController::class, 'courseMarketingCategory'])->name('course-marketing-course-marketing-category')->middleware(['auth', 'userAuth', 'loginTrack']);

Route::get('/marketing-collateral-funding-sources', [\App\Http\Controllers\Marketing\MarketingController::class, 'fundingSources'])->name('marketing-collateral-funding-sources')->middleware(['auth', 'userAuth', 'loginTrack']);

Route::get('/marketing-collateral-social-media', [\App\Http\Controllers\Marketing\MarketingController::class, 'socialMedia'])->name('marketing-collateral-social-media')->middleware(['auth', 'userAuth', 'loginTrack']);

Route::get('/add-permission-if-not-exists', [\App\Http\Controllers\Dashboard\MyusersController::class, 'addPermissionIfNotExists'])->middleware(['auth', 'userAuth', 'loginTrack']);


Route::post('/empty/index', 'EmptyController@index')->name("emptyxhr");
Route::get('/notification-detail', [IndexController::class, 'notificationDetail'])->name('notification-detail');
Route::get('/relation-data/{partner_id}/{relation_name}', [IndexController::class, 'relationData'])->name('relation-data');

Route::get('/fetch-api', [IndexController::class, 'fetchRecord'])->name('fetch-api');
Route::get('/fetch-price-book-programs/{price_book_id}', [IndexController::class, 'fetchPriceBookPrograms'])->name('fetch-price-book-programs');

Route::get('/dashboard/user-activity-logs',[\App\Http\Controllers\Dashboard\UserActivityLogController::class, 'index'])->name('user-activity-logs')->middleware('auth', 'userAuth', 'loginTrack');
Route::get('/user-activity-logs/ajax', [\App\Http\Controllers\Dashboard\UserActivityLogController::class, 'ajax'])->name('user-activity-logs-ajax')->middleware('auth', 'userAuth', 'loginTrack');
Route::get('/user-activity-log/view', [\App\Http\Controllers\Dashboard\UserActivityLogController::class, 'view'])->name('user-activity-log-view')->middleware('auth', 'userAuth', 'loginTrack');

Route::get('/import-enrollment', [\App\Http\Controllers\CronController::class, 'importEnrollment'])->name('import-enrollment')->middleware('auth', 'userAuth', 'loginTrack');

Route::get('/enrollment-login', [\App\Http\Controllers\CronController::class, 'enrollmentLogin'])->name('enrollment-login');
Route::post('/auth-enrollment', [\App\Http\Controllers\CronController::class, 'authEnrollment'])->name('auth-enrollment');
Route::get('/fetch-partner', [\App\Http\Controllers\CronController::class, 'fetchPartner'])->name('fetch-partner');
Route::get('/create-partner', [\App\Http\Controllers\CronController::class, 'createPartner'])->name('create-partner');
Route::get('/delete-partner/{id}', [\App\Http\Controllers\CronController::class, 'deletePartner'])->name('delete-partner');

Route::get('/create-contact', [\App\Http\Controllers\CronController::class, 'createContact'])->name('create-contact');
Route::get('/delete-contact/{id}', [\App\Http\Controllers\CronController::class, 'deleteContact'])->name('delete-contact');
Route::get('/delete-deals/{id}', [\App\Http\Controllers\CronController::class, 'deleteDeals'])->name('delete-deals');
Route::get('/delete-leads/{id}', [\App\Http\Controllers\CronController::class, 'deleteLeads'])->name('delete-leads');

Route::post('/leads-header-update', [\App\Http\Controllers\Student\LeadsController::class, 'headerupdate'])->name('leads-view-header-update')->middleware('auth', 'userAuth', 'loginTrack');

Route::get('/partner-inquiry-tool', [\App\Http\Controllers\Dashboard\PartnerinquiryController::class, 'add'])->name('partner-inquiry-tool')->middleware('auth', 'userAuth', 'loginTrack');
Route::post('/partner-inquiry-submit', [\App\Http\Controllers\Dashboard\PartnerinquiryController::class, 'submit'])->name('partner-inquiry-submit')->middleware('auth', 'userAuth', 'loginTrack');
Route::get('/partner-inquiry-tool-list', [\App\Http\Controllers\Dashboard\PartnerinquiryController::class, 'index'])->name('partner-inquiry-tool-list')->middleware('auth', 'userAuth', 'loginTrack');
Route::get('/partner-inquiry-search', [\App\Http\Controllers\Dashboard\PartnerinquiryController::class, 'search'])->name('partner-inquiry-search')->middleware('auth', 'userAuth', 'loginTrack');
Route::get('/partner-inquiry-export-to-pdf', [\App\Http\Controllers\Dashboard\PartnerinquiryController::class, 'exportpdf'])->name('partner-inquiry-export-to-pdf')->middleware('auth', 'userAuth', 'loginTrack');
Route::get('/partner-inquiry-export-to-excel', [\App\Http\Controllers\Dashboard\PartnerinquiryController::class, 'exportexcel'])->name('partner-inquiry-export-to-excel')->middleware('auth', 'userAuth', 'loginTrack');
Route::get('/partner-inquiry-delete', [\App\Http\Controllers\Dashboard\PartnerinquiryController::class, 'delete'])->name('partner-inquiry-delete')->middleware('auth', 'userAuth', 'loginTrack');

Route::get('/deleted-activity-logs', [\App\Http\Controllers\Dashboard\DeleteActivityController::class, 'index'])->name('deleted-activity-logs')->middleware('auth', 'userAuth', 'loginTrack');
Route::get('/deleted-activity-logs-ajax', [\App\Http\Controllers\Dashboard\DeleteActivityController::class, 'ajax'])->name('deleted-activity-logs-ajax')->middleware('auth', 'userAuth', 'loginTrack');
Route::get('/delete-deleted-activity-log', [\App\Http\Controllers\Dashboard\DeleteActivityController::class, 'delete'])->name('delete-deleted-activity-log')->middleware('auth', 'userAuth', 'loginTrack');
Route::get('/deleted-activity-log-revert', [\App\Http\Controllers\Dashboard\DeleteActivityController::class, 'revert'])->name('deleted-activity-log-revert')->middleware('auth', 'userAuth', 'loginTrack');
// ZOHO CRM HIT BY WEBHOOK

// ZOHO SalesOrders Module For Dashboard Stats count
// URL: /dashboard/index
Route::post('/webhook/update-enrollment', [WebhookController::class, 'updateEnrollment'])->name('webhook-update-enrollment');
Route::post('/webhook/delete-enrollment', [WebhookController::class, 'deleteEnrollment'])->name('webhook-delete-enrollment');

// ZOHO Products Module
// URL: /catalog/index
Route::post('/webhook/update-program', [WebhookController::class, 'updateProgram'])->name('webhook-update-program');// ZOHO Products Module
Route::post('/webhook/delete-program', [WebhookController::class, 'deleteProgram'])->name('webhook-delete-program');// ZOHO Products Module

//Prod Webhook Testing Purpose Working or Not
Route::get('/webhook/update-program-test/{zoho_id}/{action}', [WebhookController::class, 'updateProgramTest'])->name('webhook-update-program-test');// ZOHO Products Module
Route::get('/webhook/get-zoho-record/{zoho_id}/{module}', [WebhookController::class, 'getZohoRecord'])->name('get-zoho-record');// ZOHO Products Module


// ZOHO Price Book Module
Route::post('/webhook/update-price-book', [WebhookController::class, 'updateProgramPrice'])->name('webhook-update-program-price');// ZOHO Products Module
//Route::post('/webhook/delete-program', [WebhookController::class, 'deleteProgram'])->name('webhook-delete-program');// ZOHO Products Module

// URL: No
Route::post('/webhook/update-product', [WebhookController::class, 'updateProduct'])->name('webhook-update-product');// ZOHO Products Module
Route::post('/webhook/delete-product', [WebhookController::class, 'deleteProduct'])->name('webhook-delete-product');// ZOHO Products Module

// ZOHO Deals Module
// URL: /student/map-my-student
Route::post('/webhook/update-schedule', [WebhookController::class, 'updateSchedule'])->name('webhook-update-schedule'); // ZOHO Deals Module

// ZOHO Deals Module
// URL: /student/leads
Route::post('/webhook/update-lead', [WebhookController::class, 'updateLead'])->name('webhook-update-leads');// ZOHO Leads Module

// ZOHO Leads Module
// URL: /student
Route::post('/webhook/update-lead-schedule', [WebhookController::class, 'updateLeadSchedule'])->name('webhook-update-lead-schedule');

// ZOHO Contacts Module// ZOHO Contacts Module
Route::post('/webhook/update-contact', [WebhookController::class, 'updateContact'])->name('webhook-update-contact');// ZOHO Leads Module

Route::post('/webhook/update-partner', [WebhookController::class, 'updatePartner'])->name('webhook-update-partner');// ZOHO Leads Module
Route::post('/webhook/update-affiliate', [WebhookController::class, 'updateAffiliate'])->name('webhook-update-affiliate');// ZOHO Leads Module
Route::get('/webhook/test-connection', [WebhookController::class, 'testConnection'])->name('test-mysql-connection');// ZOHO Leads Module

Route::get('/import-login-activity', [\App\Http\Controllers\CanvasController::class, 'importLoginActivity'])->name('import-login-activity');
Route::get('/update-login-ip', [\App\Http\Controllers\CanvasController::class, 'updateLoginIP'])->name('update-login-ip');
Route::get('/get-owners', [\App\Http\Controllers\WebhookController::class, 'getOwners'])->name('get-owners');
Route::get('/add-missing-shop-price', [\App\Http\Controllers\WebhookController::class, 'prestashopAddMissingShopPrice'])->name('add-missing-shop-price');


// Prestashop API's

Route::get('/prestashop/optional-menu',[\App\Http\Controllers\Prestashop\MenuController::class, 'index'])->name('prestashop-optional-menu')->middleware('auth', 'userAuth', 'loginTrack');
Route::post('/prestashop/save',[\App\Http\Controllers\Prestashop\MenuController::class, 'save'])->name('prestashop-menu-update')->middleware('auth', 'userAuth', 'loginTrack');

Route::get('/prestashop/migrate-menu/{start}/{limit}',[\App\Http\Controllers\Prestashop\MenuController::class, 'migrateMenu'])->name('prestashop-migrate-menu');
Route::get('/prestashop/add-promotion-menu-ps/{start}/{limit}/{debug?}',[\App\Http\Controllers\Prestashop\MenuController::class, 'addPromotionMenuInPS'])->name('prestashop-migrate-menu');
Route::get('/prestashop/update-promotion-menu-ps/{start}/{limit}/{debug?}',[\App\Http\Controllers\Prestashop\MenuController::class, 'updatePromotionMenuInPS'])->name('prestashop-migrate-menu');
Route::get('/prestashop/add-promotion-menu-pas/{start}/{limit}/{debug?}',[\App\Http\Controllers\Prestashop\MenuController::class, 'addPromotionMenuInPAS'])->name('prestashop-migrate-menu');

Route::get('/prestashop/get-banner',[\App\Http\Controllers\Prestashop\BannerController::class, 'getBannerApi'])->name('prestashop-get-banner');

Route::get('/prestashop/banner',[\App\Http\Controllers\Prestashop\BannerController::class, 'index'])->name('prestashop-banner')->middleware('auth', 'userAuth', 'loginTrack');
Route::get('/prestashop/create',[\App\Http\Controllers\Prestashop\BannerController::class, 'create'])->name('prestashop-banner-create')->middleware('auth', 'userAuth', 'loginTrack');
Route::get('/prestashop/update/{id}',[\App\Http\Controllers\Prestashop\BannerController::class, 'update'])->name('prestashop-banner-update')->middleware('auth', 'userAuth', 'loginTrack');
Route::post('/prestashop/banner-save',[\App\Http\Controllers\Prestashop\BannerController::class, 'save'])->name('prestashop-banner-save')->middleware('auth', 'userAuth', 'loginTrack');

Route::get('/prestashop/get-banner-notification',[\App\Http\Controllers\Prestashop\BannerNotificationController::class, 'getBannerNotificationApi'])->name('prestashop-get-banner-notification');

Route::get('/prestashop/get-course-detail/{zoho_id}',[\App\Http\Controllers\Prestashop\CourseController::class, 'getCourseDetailApi'])->name('prestashop-get-course-detail');

Route::get('/prestashop/banner-notification',[\App\Http\Controllers\Prestashop\BannerNotificationController::class, 'index'])->name('prestashop-banner-notification')->middleware('auth', 'userAuth', 'loginTrack');
Route::post('/prestashop/banner-notification-save',[\App\Http\Controllers\Prestashop\BannerNotificationController::class, 'save'])->name('prestashop-banner-notification-save')->middleware('auth', 'userAuth', 'loginTrack');

Route::get('/prestashop/get-category',[\App\Http\Controllers\Prestashop\CategoryController::class, 'getCategoryApi'])->name('prestashop-get-category');
Route::get('/prestashop/get-featured-programs',[\App\Http\Controllers\Prestashop\ProgramController::class, 'getFeaturedPrograms'])->name('prestashop-get-featured-programs');


Route::get('/prestashop/get-contact-setting',[\App\Http\Controllers\Prestashop\PartnerController::class, 'getContactSettingApi'])->name('prestashop-get-contact-setting');
Route::get('/prestashop/get-partner',[\App\Http\Controllers\Prestashop\PartnerController::class, 'getPartnerDetailApi'])->name('prestashop-get-partner');
Route::get('/prestashop/get-affiliate',[\App\Http\Controllers\Prestashop\PartnerController::class, 'getAffiliateDetailApi'])->name('prestashop-get-affiliate');
Route::get('/prestashop/get-laptop',[\App\Http\Controllers\Prestashop\PartnerController::class, 'getLaptopApi'])->name('prestashop-get-laptop');
Route::get('/prestashop/get-refer',[\App\Http\Controllers\Prestashop\PartnerController::class, 'getReferApi'])->name('prestashop-get-refer');

Route::get('/prestashop/hosted-site',[\App\Http\Controllers\Prestashop\PartnerController::class, 'index'])->name('prestashop-hosted-site')->middleware('auth', 'userAuth', 'loginTrack');
Route::post('/prestashop/hosted-site-save',[\App\Http\Controllers\Prestashop\PartnerController::class, 'save'])->name('prestashop-hosted-site-save')->middleware('auth', 'userAuth', 'loginTrack');

Route::get('/prestashop/zip-code-locator',[\App\Http\Controllers\Prestashop\PartnerController::class, 'zipCodeLocator'])->name('prestashop-zip-code-locator');

Route::get('/prestashop/update-programs/{start}/{limit}/{debug?}',[\App\Http\Controllers\Prestashop\CourseNewController::class, 'updatePrestashopPrograms'])->name('prestashop-update-program');

Route::get('/prestashop/update-shop-program/{id_shop}/{start}/{limit}/{category_sync}',[\App\Http\Controllers\Prestashop\CourseController::class, 'updateShopPrograms'])->name('prestashop-update-shop-program');
Route::get('/prestashop/update-affiliate-program/{id_shop}/{start}/{limit}/{category_sync}',[\App\Http\Controllers\Prestashop\CourseController::class, 'updateAffiliatePrograms'])->name('prestashop-update-affiliate-program');
Route::get('/prestashop/update-affiliate-price/{id_shop}/{debug?}',[\App\Http\Controllers\Prestashop\CourseNewController::class, 'updateAffiliatePrice'])->name('prestashop-update-affiliate-program');
Route::get('/prestashop/add-affiliate-program/{id_shop}/{start}/{limit}',[\App\Http\Controllers\Prestashop\CourseController::class, 'addAffiliatePrograms'])->name('prestashop-update-affiliate-program');
Route::get('/prestashop/update-product-url',[\App\Http\Controllers\Prestashop\CourseController::class, 'updateProductUrl'])->name('prestashop-update-product-url');
Route::get('/prestashop/add-category',[\App\Http\Controllers\Prestashop\CourseController::class, 'addPrestashopProductCategory'])->name('add-prestashop-product-category');
//Route::get('/prestashop/delete-affiliate-program/{id_shop}/{start}/{limit}',[\App\Http\Controllers\Prestashop\CourseController::class, 'deleteAffiliateProgramsIfNotExist'])->name('prestashop-delete-affiliate-program');

Route::match(['GET','POST'], 'hosted-sites-promotions', [\App\Http\Controllers\Prestashop\PartnerController::class, 'sitespromotions'])->name('hosted-sites-promotions')->middleware('auth', 'userAuth', 'loginTrack');
Route::match(['GET','POST'], 'refer-friend', [\App\Http\Controllers\Prestashop\PartnerController::class, 'referfriend'])->name('refer-friend')->middleware('auth', 'userAuth', 'loginTrack');

Route::match(['GET','POST'], 'shop-creator', [\App\Http\Controllers\Prestashop\PartnerController::class, 'shopCreator'])->name('shop-creator')->middleware('auth','userAuth','loginTrack');

Route::get( 'shop-delete/{id_shop}', [\App\Http\Controllers\Prestashop\PartnerController::class, 'shopDelete'])->name('shop-delete')->middleware('auth','userAuth','loginTrack');

Route::match(['GET','POST'], 'check-shop-url', [\App\Http\Controllers\Prestashop\PartnerController::class, 'checkShopUrl'])->name('check-shop-url')->middleware('auth','userAuth','loginTrack');

Route::match(['GET','POST'], 'check-shop-name', [\App\Http\Controllers\Prestashop\PartnerController::class, 'checkShopName'])->name('check-shop-name')->middleware('auth','userAuth','loginTrack');

Route::match(['GET','POST'], 'training-plan-creator', [\App\Http\Controllers\Prestashop\PartnerController::class, 'trainigplancreator'])->name('training-plan-creator')->middleware('auth','userAuth','loginTrack');
Route::match(['GET','POST'], 'mycaa-training-plan-creator', [\App\Http\Controllers\Prestashop\PartnerController::class, 'mycaatrainigplancreator'])->name('mycaa-training-plan-creator')->middleware('auth','userAuth','loginTrack');
Route::get('fetch-program', [\App\Http\Controllers\Prestashop\PartnerController::class, 'fetchprogram'])->name('fetch-program')->middleware('auth','userAuth','loginTrack');
Route::post('download-training', [\App\Http\Controllers\Prestashop\PartnerController::class, 'downloadtraining'])->name('download-training')->middleware('auth','userAuth','loginTrack');
Route::post('download-mycaa-training', [\App\Http\Controllers\Prestashop\PartnerController::class, 'downloadmycaatraining'])->name('download-mycaa-training')->middleware('auth','userAuth','loginTrack');

Route::get('order-create-1/{debug?}', [\App\Http\Controllers\Prestashop\OrderController::class, 'orderCreateOne'])->name('order-create-1')->middleware('auth','userAuth');
Route::get('order-create-2/{debug?}', [\App\Http\Controllers\Prestashop\OrderController::class, 'orderCreateTwo'])->name('order-create-2')->middleware('auth','userAuth');

Route::get('order-send-to-zoho/{order_id}', [\App\Http\Controllers\Prestashop\OrderController::class, 'orderSendToZoho'])->name('order-send-to-zoho')->middleware('auth','userAuth');

Route::post('order-send-to-zoho-submit', [\App\Http\Controllers\Prestashop\OrderController::class, 'orderSendToZohoSubmit'])->name('order-send-to-zoho-submit')->middleware('auth','userAuth');

Route::get('/clear-cache', function() {
    Artisan::call('cache:clear');
    Artisan::call('route:clear');
    Artisan::call('config:clear');
    Artisan::call('view:clear');
    return "Cache is cleared";
});


Route::get('/ps-program-update', function() {
    Artisan::call('importProgram:cron');
    return "Program updated successfully";
});