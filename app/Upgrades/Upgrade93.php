<?php

namespace App\Upgrades;

use App\Models\EmailTemplate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class Upgrade93 extends BaseUpgrade
{

    public $versionName = "1.8.41";
    //Runs or migrations to be done on this version
    public function run()
    {

        $this->emailTemplating();

        //migrate the banner migrations
        if (!Schema::hasColumn('banners', 'title')) {
            Artisan::call('migrate', [
                '--path' => "database/migrations/2026_07_22_081739_add_title_description_cta_button_title_to_banners_table.php",
                '--force' => true,
            ]);
        }
    }


    private function emailTemplating()
    {

        if (!Schema::hasTable('email_templates')) {
            Artisan::call('migrate', [
                '--path' => "database/migrations/2026_07_11_000001_create_email_templates_table.php",
                '--force' => true,
            ]);
        }

        Permission::firstOrCreate(['name' => "manage-email-templates"]);
        $adminRole = Role::firstOrCreate([
            'name' => 'admin'
        ], [
            'guard_name' => 'web'
        ]);
        $adminRole->givePermissionTo("manage-email-templates");

        $defaultsPath = resource_path('views/view/emails/defaults');
        $loadDefault = function (string $name) use ($defaultsPath) {
            $file = "{$defaultsPath}/{$name}.html";
            return file_exists($file) ? file_get_contents($file) : '';
        };

        $downloadButtons = $loadDefault('download_buttons');

        $newAccountBody = strtr($loadDefault('new_account'), ['{{download_buttons}}' => $downloadButtons]);
        $otpBody = strtr($loadDefault('otp'), ['{{download_buttons}}' => $downloadButtons]);
        $newVendorBody = $loadDefault('new_vendor');
        $vendorUpdateActiveBody = $loadDefault('vendor_update_active');
        $vendorUpdateDeactivatedBody = $loadDefault('vendor_update_deactivated');
        $documentRequestApprovedBody = $loadDefault('document_request_approved');
        $documentRequestRejectedBody = $loadDefault('document_request_rejected');
        $documentRequestResubmitBody = $loadDefault('document_request_resubmit');

        $templates = [
            [
                'key' => 'new_account',
                'mailable' => 'new_account',
                'label' => 'New Account - Welcome Email',
                'subject' => 'New Account',
                'body' => $newAccountBody,
            ],
            [
                'key' => 'new_vendor',
                'mailable' => 'new_vendor',
                'label' => 'New Vendor - Welcome Email',
                'subject' => 'New Account',
                'body' => $newVendorBody,
            ],
            [
                'key' => 'otp',
                'mailable' => 'otp',
                'label' => 'OTP Verification',
                'subject' => 'OTP Verification',
                'body' => $otpBody,
            ],
            [
                'key' => 'vendor_update_active',
                'mailable' => 'vendor_update',
                'label' => 'Vendor Update - Activated',
                'subject' => 'Account Updated',
                'body' => $vendorUpdateActiveBody,
            ],
            [
                'key' => 'vendor_update_deactivated',
                'mailable' => 'vendor_update',
                'label' => 'Vendor Update - Deactivated',
                'subject' => 'Account Updated',
                'body' => $vendorUpdateDeactivatedBody,
            ],
            [
                'key' => 'document_request_approved',
                'mailable' => 'document_request',
                'label' => 'Document Request - Approved',
                'subject' => 'Document Request',
                'body' => $documentRequestApprovedBody,
            ],
            [
                'key' => 'document_request_rejected',
                'mailable' => 'document_request',
                'label' => 'Document Request - Rejected',
                'subject' => 'Document Request',
                'body' => $documentRequestRejectedBody,
            ],
            [
                'key' => 'document_request_resubmit',
                'mailable' => 'document_request',
                'label' => 'Document Request - Resubmission Needed',
                'subject' => 'Document Request',
                'body' => $documentRequestResubmitBody,
            ],
        ];

        foreach ($templates as $template) {
            EmailTemplate::firstOrCreate(
                ['key' => $template['key']],
                $template
            );
        }
    }
}
