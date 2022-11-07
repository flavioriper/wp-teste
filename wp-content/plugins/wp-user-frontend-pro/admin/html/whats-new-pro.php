<?php
$changelog = array(
    array(
        'version'  => 'Version 3.4.11',
        'released' => '2022-03-09',
        'changes'  => [

            [
                'title' => __( 'Phone field added in form builder', 'wpuf-pro' ),
                'type'  => 'Enhancement',
            ],
            [
                'title' => __( 'Time field added in form builder', 'wpuf-pro' ),
                'type'  => 'Enhancement',
            ],
            [
                'title' => __( 'Meta key enhanced for user email notification', 'wpuf-pro' ),
                'type'  => 'Enhancement',
            ],
            [
                'title' => __( 'Container added for instance track', 'wpuf-pro' ),
                'type'  => 'Enhancement',
            ],
            [
                'title' => __( 'Email template enhanced for after activation', 'wpuf-pro' ),
                'type'  => 'Enhancement',
            ],
            [
                'title' => __( 'Activity module enhanced', 'wpuf-pro' ),
                'type'  => 'Enhancement',
            ],
            [
                'title' => __( 'Shortcode field label enhanced', 'wpuf-pro' ),
                'type'  => 'Fix',
            ],
            [
                'title' => __( 'Non decimal currencies handled for stripe', 'wpuf-pro' ),
                'type'  => 'Fix',
            ],
            [
                'title' => __( 'Rollback abuse through draft issue handled', 'wpuf-pro' ),
                'type'  => 'Fix',
            ],
            [
                'title' => __( 'Post expiration not working fixed', 'wpuf-pro' ),
                'type'  => 'Fix',
            ],
            [
                'title' => __( 'Conditional required fields error handled', 'wpuf-pro' ),
                'type'  => 'Fix',
            ],
            [
                'title' => __( 'Profile form preview issue handled', 'wpuf-pro' ),
                'type'  => 'Fix',
            ],
            [
                'title' => __( 'Template override for child theme fixed', 'wpuf-pro' ),
                'type'  => 'Fix',
            ],
            [
                'title' => __( 'Special character password handled for login', 'wpuf-pro' ),
                'type'  => 'Fix',
            ],
            [
                'title' => __( 'Coupon with no value error fixed', 'wpuf-pro' ),
                'type'  => 'Fix',
            ],
            [
                'title' => __( 'Translation related issue handled for admin menu', 'wpuf-pro' ),
                'type'  => 'Fix',
            ],
            [
                'title' => __( 'Conditional radio / checkbox with default value not working handled', 'wpuf-pro' ),
                'type'  => 'Fix',
            ],
            [
                'title' => __( 'PHP 8 compatibility handled', 'wpuf-pro' ),
                'type'  => 'Fix',
            ],
            [
                'title' => __( 'Email template settings issue handled', 'wpuf-pro' ),
                'type'  => 'Fix',
            ],
            [
                'title' => __( 'Private message menu error fixed', 'wpuf-pro' ),
                'type'  => 'Fix',
            ],
            [
                'title' => __( 'Math captcha value to single digit', 'wpuf-pro' ),
                'type'  => 'Fix',
            ],
            [
                'title' => __( 'Invoice logo not found issue handled', 'wpuf-pro' ),
                'type'  => 'Fix',
            ],
            [
                'title' => __( 'fpdf php 8 issue handled by upgrade', 'wpuf-pro' ),
                'type'  => 'Fix',
            ],
        ],
    ),
    array(
        'version'   => 'Version 3.4.10',
        'released'   => '2021-10-28',
        'changes'   => array(
            array(
                'title' => __( 'Featured item for subscriber', 'wpuf-pro' ),
                'type'  => 'Enhancement',
            ),
            array(
                'title' => __( 'Warning added for unsaved form data on frontend', 'wpuf-pro' ),
                'type'  => 'Enhancement',
            ),
            array(
                'title' => __( 'Editor added for registration form email template', 'wpuf-pro' ),
                'type'  => 'Enhancement',
            ),
            array(
                'title' => __( 'User directory admin user profile tab control for admin', 'wpuf-pro' ),
                'type'  => 'Enhancement',
            ),
            array(
                'title' => __( 'User directory frontend reset button, back to listing button added, design improvements', 'wpuf-pro' ),
                'type'  => 'Enhancement',
            ),
            array(
                'title' => __( 'User directory frontend settings profile header, user listing template images updated', 'wpuf-pro' ),
                'type'  => 'Enhancement',
            ),
            array(
                'title' => __( 'User directory frontend media file optimized', 'wpuf-pro' ),
                'type'  => 'Enhancement',
            ),
            array(
                'title' => __( 'Vendor registration form redirect issue handled', 'wpuf-pro' ),
                'type'  => 'Fix',
            ),
            array(
                'title' => __( 'License menu issues fixed', 'wpuf-pro' ),
                'type'  => 'Fix',
            ),
            array(
                'title' => __( 'Content restriction error handled', 'wpuf-pro' ),
                'type'  => 'Fix',
            ),
            array(
                'title' => __( 'Required address field conditional inconsistency fixed', 'wpuf-pro' ),
                'type'  => 'Fix',
            ),
            array(
                'title' => __( 'Tribe events calender data not syncing properly', 'wpuf-pro' ),
                'type'  => 'Fix',
            ),
        ),
    ),
    array(
        'version'   => 'Version 3.4.9',
        'released'   => '2021-09-16',
        'changes'   => array(
            array(
                'title' => __( 'Country wide tax rate option enhanced', 'wpuf-pro' ),
                'type'  => 'Enhancement',
            ),
            array(
                'title' => __( 'Missing state for address field added', 'wpuf-pro' ),
                'type'  => 'Enhancement',
            ),
            array(
                'title' => __( 'Roles in atts added for user-listing shortcode', 'wpuf-pro' ),
                'type'  => 'Enhancement',
            ),
            array(
                'title' => __( 'New option for redirection after pay per post payment in form setting', 'wpuf-pro' ),
                'type'  => 'Enhancement',
            ),
            array(
                'title' => __( 'Controller added for various email notification', 'wpuf-pro' ),
                'type'  => 'Enhancement',
            ),
            array(
                'title' => __( 'Subscription Post expiration option change to input field', 'wpuf-pro' ),
                'type'  => 'Update',
            ),
            array(
                'title' => __( 'Campaign Monitor SDK update to latest version', 'wpuf-pro' ),
                'type'  => 'Update',
            ),
            array(
                'title' => __( 'Subscription expiration notification inconsistency handled', 'wpuf-pro' ),
                'type'  => 'Fix',
            ),
            array(
                'title' => __( 'Invoice address not comply with customizer fixed', 'wpuf-pro' ),
                'type'  => 'Fix',
            ),
            array(
                'title' => __( 'PMpro integration inconsistency handled', 'wpuf-pro' ),
                'type'  => 'Fix',
            ),
        ),
    ),
    array(
        'version'   => 'Version 3.4.8',
        'released'   => '2021-07-06',
        'changes'   => array(
            array(
                'title' => __( 'Google Map field enhanced along with acf google map', 'wpuf-pro' ),
                'type'  => 'Enhancement',
            ),
            array(
                'title' => __( 'Form preview page inconsistency with builder', 'wpuf-pro' ),
                'type'  => 'Fix',
            ),
            array(
                'title' => __( 'TOC field not saving properly for registration form', 'wpuf-pro' ),
                'type'  => 'Fix',
            ),
        ),
    ),
    array(
        'version'   => 'Version 3.4.7',
        'released'   => '2021-06-08',
        'changes'   => array(
            array(
                'title' => __( 'User Directory module redesigned with better UI & UX', 'wpuf-pro' ),
                'type'  => 'Enhancement',
            ),
            array(
                'title' => __( 'Private message module redesigned with better UI,UX & file attachments', 'wpuf-pro' ),
                'type'  => 'Enhancement',
            ),
            array(
                'title' => __( 'Contacts method added for front-end user', 'wpuf-pro' ),
                'type'  => 'Enhancement',
            ),
            array(
                'title' => __( 'Post approval notification option added', 'wpuf-pro' ),
                'type'  => 'Enhancement',
            ),
            array(
                'title' => __( 'Datetime inconsistency with tribes event calender fixed', 'wpuf-pro' ),
                'type'  => 'Fix',
            ),
            array(
                'title' => __( 'Math Field validation inconsistency handled properly', 'wpuf-pro' ),
                'type'  => 'Fix',
            ),
            array(
                'title' => __( 'PMpro module date inconsistency handled', 'wpuf-pro' ),
                'type'  => 'Fix',
            ),
            array(
                'title' => __( 'Buddypress field inconsistency handled with CS fixing', 'wpuf-pro' ),
                'type'  => 'Fix',
            ),
            array(
                'title' => __( 'Registration form inconsistency with multisite fixed', 'wpuf-pro' ),
                'type'  => 'Fix',
            ),
            array(
                'title' => __( 'Tax option not saving on backend fixed', 'wpuf-pro' ),
                'type'  => 'Fix',
            ),
            array(
                'title' => __( 'Datetime year range set to 100 years', 'wpuf-pro' ),
                'type'  => 'Fix',
            ),
        ),
    ),
    array(
        'version'   => 'Version 3.4.6',
        'released'   => '2021-03-08',
        'changes'   => array(
            array(
                'title' => __( 'Multiple repeat field not showing', 'wpuf-pro' ),
                'type'  => 'Fix',
            ),
            array(
                'title' => __( 'Missing semantic description for subscription email body', 'wpuf-pro' ),
                'type'  => 'Fix',
            ),
            array(
                'title' => __( 'Decimal tax value getting floor value', 'wpuf-pro' ),
                'type'  => 'Fix',
            ),
            array(
                'title' => __( 'Dokan vendor registration form custom URL redirection not workin', 'wpuf-pro' ),
                'type'  => 'Fix',
            ),
            array(
                'title' => __( 'Bulk form id settings option on various post tables', 'wpuf-pro' ),
                'type'  => 'Fix',
            ),
        ),
    ),
    array(
        'version'   => 'Version 3.4.5',
        'released'   => '2021-01-12',
        'changes'   => array(
            array(
                'title' => __( 'Stripe cancel doesnt work properly when deleting from stripe', 'wpuf-pro' ),
                'type'  => 'Fix',
            ),
            array(
                'title' => __( 'Register user unable to delete file after login', 'wpuf-pro' ),
                'type'  => 'Fix',
            ),
            array(
                'title' => __( 'BuddyPress integration has issue due to sanitization', 'wpuf-pro' ),
                'type'  => 'Fix',
            ),
            array(
                'title' => __( 'Social login avatar not working', 'wpuf-pro' ),
                'type'  => 'Fix',
            ),
            array(
                'title' => __( 'Dokan vendor registration state field', 'wpuf-pro' ),
                'type'  => 'Fix',
            ),
            array(
                'title' => __( 'QR code doesnt work on guest mode', 'wpuf-pro' ),
                'type'  => 'Fix',
            ),
            array(
                'title' => __( 'WPUF vendor registration redirect with setup wizard', 'wpuf-pro' ),
                'type'  => 'Fix',
            ),
            array(
                'title' => __( 'Sent email after publishing post', 'wpuf-pro' ),
                'type'  => 'Enhancement',
            ),
        ),
    ),
    array(
        'version'   => 'Version 3.4.4',
        'released'   => '2020-12-11',
        'changes'   => array(
            array(
                'title' => __( 'Added partial content restriction', 'wpuf-pro' ),
                'type'  => 'New',
            ),
            array(
                'title' => __( 'Email content formatting was not correct', 'wpuf-pro' ),
                'type'  => 'Fix',
            ),
            array(
                'title' => __( 'Rollback didn\'t work for custom post type', 'wpuf-pro' ),
                'type'  => 'Fix',
            ),
            array(
                'title' => __( 'Date field year and month didn\'t select properly', 'wpuf-pro' ),
                'type'  => 'Fix',
            ),
            array(
                'title' => __( 'Stripe trial didn\'t work properly', 'wpuf-pro' ),
                'type'  => 'Fix',
            ),
            array(
                'title' => __( 'Semantic subscriber email notification', 'wpuf-pro' ),
                'type'  => 'Fix',
            ),
            array(
                'title' => __( 'Undefined offset due to edit_posts capability', 'wpuf-pro' ),
                'type'  => 'Fix',
            ),
            array(
                'title' => __( 'View profile link didn\'t show', 'wpuf-pro' ),
                'type'  => 'Enhancement',
            ),
        ),
    ),
    array(
        'version'   => 'Version 3.4.3',
        'released'   => '2020-11-14',
        'changes'   => array(
            array(
                'title' => __( 'Can\'t edit registration form', 'wpuf-pro' ),
                'type'  => 'Fix',
            ),
        ),
    ),
    array(
        'version'   => 'Version 3.4.2',
        'released'   => '2020-11-11',
        'changes'   => array(
            array(
                'title' => __( 'Optional password fields on edit profile forms', 'wpuf-pro' ),
                'type'  => 'Fix',
            ),
            array(
                'title' => __( 'Post expiration inconsistency', 'wpuf-pro' ),
                'type'  => 'Fix',
            ),
            array(
                'title' => __( 'Column\'s inside fields are not appearing on the BuddyPress mapping option', 'wpuf-pro' ),
                'type'  => 'Fix',
            ),
            array(
                'title' => __( 'Terms and conditions were not saving', 'wpuf-pro' ),
                'type'  => 'Fix',
            ),
            array(
                'title' => __( 'Remove conditional fields from action hook', 'wpuf-pro' ),
                'type'  => 'Fix',
            ),
            array(
                'title' => __( 'Take pack restrictions from pack when purchasing via pmpro plugin', 'wpuf-pro' ),
                'type'  => 'Fix',
            ),
            array(
                'title' => __( 'Revolution slider not working with report module due to chartjs color confilct', 'wpuf-pro' ),
                'type'  => 'Fix',
            ),
            array(
                'title' => __( 'WooCommerce product type form category child Of selection type is not meeting condition', 'wpuf-pro' ),
                'type'  => 'Fix',
            ),
        ),
    ),
    array(
        'version'   => 'Version 3.4.1',
        'released'   => '2020-10-21',
        'changes'   => array(
            array(
                'title' => __( 'Mathematical Captcha field', 'wpuf-pro' ),
                'type'  => 'New',
            ),
            array(
                'title' => __( 'No message is seen after sending the reset password email', 'wpuf-pro' ),
                'type'  => 'Enhancement',
            ),
            array(
                'title' => __( 'Invoice item name doesn\'t show', 'wpuf-pro' ),
                'type'  => 'Fix',
            ),
            array(
                'title' => __( 'Social URL doesn\'t show in user listing directory', 'wpuf-pro' ),
                'type'  => 'Fix',
            ),
            array(
                'title' => __( 'Wrong class name for attachment element', 'wpuf-pro' ),
                'type'  => 'Fix',
            ),
            array(
                'title' => __( 'Couldn\'t send email notification following user settings', 'wpuf-pro' ),
                'type'  => 'Fix',
            ),
            array(
                'title' => __( 'Taxes were not being calculated properly', 'wpuf-pro' ),
                'type'  => 'Fix',
            ),
            array(
                'title' => __( 'Stripe card design doesn\'t look right', 'wpuf-pro' ),
                'type'  => 'Fix',
            ),
        ),
    ),

    array(
        'version'  => 'Version 3.4.0',
        'released' => '2020-08-24',
        'changes' => array(
            array(
                'title' => __( 'Gracefully handle the Google Map field if no Google API key found', 'wpuf-pro' ),
                'type'  => 'Enhancement',
            ),
            array(
                'title' => __( 'Sending email when user status is pending', 'wpuf-pro' ),
                'type'  => 'Fix',
            ),
            array(
                'title' => __( 'Preventing from go to next step in multistep form', 'wpuf-pro' ),
                'type'  => 'Fix',
            ),
            array(
                'title' => __( 'Google API error in form builder in case of no Google admin settings found', 'wpuf-pro' ),
                'type'  => 'Fix',
            ),
            array(
                'title' => __( 'User profile empty field value in user listing module', 'wpuf-pro' ),
                'type'  => 'Fix',
            ),
            array(
                'title' => __( 'Showing uploaded file or image in user listing profile page', 'wpuf-pro' ),
                'type'  => 'Fix',
            ),
            array(
                'title' => __( 'WPUF Pro JS conflict with WordPress v5.5', 'wpuf-pro' ),
                'type'  => 'Fix',
            ),
            array(
                'title' => __( 'Subscription trial period issue for Stripe gateway', 'wpuf-pro' ),
                'type'  => 'Enhancement',
            ),
        ),
    ),
    array(
        'version'  => 'Version 3.3.1',
        'released' => '2020-06-16',
        'changes' => array(
            array(
                'title' => __( 'User role issue when register using social login module', 'wpuf-pro' ),
                'type'  => 'Fix',
            ),
            array(
                'title' => __( 'Do not show social login buttons if module is not enabled in admin settings', 'wpuf-pro' ),
                'type'  => 'Fix',
            ),
            array(
                'title' => __( 'Prevent showing empty labels for fields that have render_field_data method', 'wpuf-pro' ),
                'type'  => 'Fix',
            ),
            array(
                'title' => __( 'Missing colon to numeric field label when render its data', 'wpuf-pro' ),
                'type'  => 'Fix',
            ),
        ),
    ),
    array(
        'version'  => 'Version 3.3.0',
        'released' => '2020-06-11',
        'changes' => array(
            array(
                'title' => __( 'Improve avatar upload to support CDN plugins', 'wpuf-pro' ),
                'type'  => 'Tweak',
            ),
            array(
                'title' => __( 'Make social login button URLs dynamic', 'wpuf-pro' ),
                'type'  => 'Tweak',
            ),
            array(
                'title' => __( 'Allow updating Dokan shop url from admin user edit page', 'wpuf-pro' ),
                'type'  => 'Tweak',
            ),
            array(
                'title' => __( 'Stripe billing amount calculation for greater than 999', 'wpuf-pro' ),
                'type'  => 'Fix',
            ),
            array(
                'title' => __( 'Enqueueing google map javascript file', 'wpuf-pro' ),
                'type'  => 'Fix',
            ),
            array(
                'title' => __( 'Rendering social login icons in multiple form instances', 'wpuf-pro' ),
                'type'  => 'Fix',
            ),
            array(
                'title' => __( 'Menu restriction option showing twice in admin menu editor', 'wpuf-pro' ),
                'type'  => 'Fix',
            ),
            array(
                'title' => __( 'Avatar size display not complying with admin settings size', 'wpuf-pro' ),
                'type'  => 'Fix',
            ),
            array(
                'title' => __( 'Missing billing address in invoice PDF', 'wpuf-pro' ),
                'type'  => 'Fix',
            ),
            array(
                'title' => __( 'Redirect url after frontend form submission', 'wpuf-pro' ),
                'type'  => 'Fix',
            ),
        ),
    ),
    array(
        'version'  => 'Version 3.2.0',
        'released' => '2020-04-14',
        'changes' => array(
            array(
                'title'       => __( 'Add default value for email address and name settings', 'wpuf-pro' ),
                'type'        => 'Improvement',
                'description' => __( 'Add default value for email address and name settings', 'wpuf-pro' ),
            ),
            array(
                'title'       => __( 'Update form builder help text', 'wpuf-pro' ),
                'type'        => 'Improvement',
                'description' => __( 'Update form builder help text', 'wpuf-pro' ),
            ),
            array(
                'title'       => __( 'Update dokan vendor registration template', 'wpuf-pro' ),
                'type'        => 'Improvement',
                'description' => __( 'Update dokan vendor registration template', 'wpuf-pro' ),
            ),
            array(
                'title'       => __( 'Update social login', 'wpuf-pro' ),
                'type'        => 'Improvement',
                'description' => __( 'Update social login', 'wpuf-pro' ),
            ),
            array(
                'title'       => __( 'Update field help text form builder', 'wpuf-pro' ),
                'type'        => 'Improvement',
                'description' => __( 'Update field help text form builder', 'wpuf-pro' ),
            ),
            array(
                'title'       => __( 'Show default error messages for pending and denied user logins', 'wpuf-pro' ),
                'type'        => 'Fix',
                'description' => __( 'Show default error messages for pending and denied user logins', 'wpuf-pro' ),
            ),
            array(
                'title'       => __( 'Error notices after registration', 'wpuf-pro' ),
                'type'        => 'Fix',
                'description' => __( 'Error notices after registration', 'wpuf-pro' ),
            ),
            array(
                'title'       => __( 'Conditional logic for categories loaded by Ajax', 'wpuf-pro' ),
                'type'        => 'Fix',
                'description' => __( 'Conditional logic for categories loaded by Ajax', 'wpuf-pro' ),
            ),
            array(
                'title'       => __( 'Condition to check account page', 'wpuf-pro' ),
                'type'        => 'Fix',
                'description' => __( 'Condition to check account page', 'wpuf-pro' ),
            ),
            array(
                'title'       => __( 'Hybridauth authentication', 'wpuf-pro' ),
                'type'        => 'Fix',
                'description' => __( 'Hybridauth authentication', 'wpuf-pro' ),
            ),
            array(
                'title'       => __( 'Confirm password list attribute.', 'wpuf-pro' ),
                'type'        => 'Fix',
                'description' => __( 'Confirm password list attribute.', 'wpuf-pro' ),
            ),
            array(
                'title'       => __( 'Some tax related warnings', 'wpuf-pro' ),
                'type'        => 'Fix',
                'description' => __( 'Some tax related warnings', 'wpuf-pro' ),
            ),
            array(
                'title'       => __( 'BP date/time field compatibility issue', 'wpuf-pro' ),
                'type'        => 'Fix',
                'description' => __( 'BP date/time field compatibility issue', 'wpuf-pro' ),
            ),
            array(
                'title'       => __( "Not logged in user can't subscribe a pack with payment method stripe", 'wpuf-pro' ),
                'type'        => 'Fix',
                'description' => __( "Not logged in user can't subscribe a pack with payment method stripe", 'wpuf-pro' ),
            ),
            array(
                'title'       => __( 'Event Calendar timezone compatibility issue', 'wpuf-pro' ),
                'type'        => 'Fix',
                'description' => __( 'Event Calendar timezone compatibility issue', 'wpuf-pro' ),
            ),
            array(
                'title'       => __( 'Newly register users notification is not showing the URL clickable', 'wpuf-pro' ),
                'type'        => 'Fix',
                'description' => __( 'Newly register users notification is not showing the URL clickable', 'wpuf-pro' ),
            ),
            array(
                'title'       => __( 'Some warnings in user directory', 'wpuf-pro' ),
                'type'        => 'Fix',
                'description' => __( 'Some warnings in user directory', 'wpuf-pro' ),
            ),
            array(
                'title'       => __( 'Allowing multiple files creates unaccessible files for downloads', 'wpuf-pro' ),
                'type'        => 'Fix',
                'description' => __( 'Allowing multiple files creates unaccessible files for downloads', 'wpuf-pro' ),
            ),
            array(
                'title'       => __( 'Social login rest api issue', 'wpuf-pro' ),
                'type'        => 'Fix',
                'description' => __( 'Social login rest api issue', 'wpuf-pro' ),
            ),
        ),
    ),
    array(
        'version'  => 'Version 3.1.13',
        'released' => '2020-02-03',
        'changes' => array(
            array(
                'title'       => __( 'Added some new styles for profile forms.', 'wpuf-pro' ),
                'type'        => 'New',
                'description' => __( 'Added some new styles for profile forms.', 'wpuf-pro' ),
            ),
            array(
                'title'       => __( 'Fixed stripe issues', 'wpuf-pro' ),
                'type'        => 'Fix',
                'description' => 'Fixed several Stripe issues',
            ),
            array(
                'title'       => __( 'Fixed UserListing table list render problem.', 'wpuf-pro' ),
                'type'        => 'Fix',
                'description' => 'Fixed UserListing table list render problem.',
            ),
            array(
                'title'       => __( 'Fixed some tax settings issue.', 'wpuf-pro' ),
                'type'        => 'Fix',
                'description' => 'Fixed some tax settings issues.',
            ),
        ),
    ),
    array(
        'version'  => 'Version 3.1.11',
        'released' => '2019-10-17',
        'changes' => array(
            array(
                'title'       => __( "Embed Field's meta key is missing in the field settings", 'wpuf-pro' ),
                'type'        => 'Fix',
                'description' => "Embed Field's meta key is missing in the field settings",
            ),
            array(
                'title'       => __( 'Email confirmation link not working with bedrock environment.', 'wpuf-pro' ),
                'type'        => 'Fix',
                'description' => 'Email confirmation link not working with bedrock environment. It was redirecting to 404 page.',
            ),
        ),
    ),
    array(
        'version'  => 'Version 3.1.2',
        'released' => '2019-04-01',
        'changes' => array(
            array(
                'title'       => __( 'Repeat field with more than one column did not render data.', 'wpuf-pro' ),
                'type'        => 'Fix',
                'description' => 'Repeat field with more than one column did not render data, fixed in this version.',
            ),
            array(
                'title'       => __( 'Checkbox and radio field data were not showing properly on user listing page.', 'wpuf-pro' ),
                'type'        => 'Fix',
                'description' => 'Checkbox and radio field data were not showing properly on user listing page, fixed in this version.',
            ),
            array(
                'title'       => __( 'File type meta key in the WPUF User Listing module was not being saved.', 'wpuf-pro' ),
                'type'        => 'Fix',
                'description' => 'File type meta key in the WPUF User Listing module was not being saved from backend settings, you will get it fixed.',
            ),
            array(
                'title'       => __( 'Subscription reminder email was being sent at a wrong time.', 'wpuf-pro' ),
                'type'        => 'Fix',
                'description' => 'Subscription reminder email was being sent at a wrong time, from this version the email will be sent on time.',
            ),
            array(
                'title'       => __( 'Updated Stripe library & set Stripe AppInfo.', 'wpuf-pro' ),
                'type'        => 'Improvement',
                'description' => 'Updated Stripe library & set Stripe AppInfo.',
            ),
        ),
    ),
    array(
        'version'  => 'Version 3.1.0',
        'released' => '2019-01-31',
        'changes' => array(
            array(
                'title'       => __( 'User logged in without activation', 'wpuf-pro' ),
                'type'        => 'Fix',
                'description' => __( 'If <strong>Auto Login After Registration</strong> option is enabled from Login/Registration settings, also admin approves and email verification options are required from the registration form, user get auto logged in after registration. This issue has been fixed in this version.', 'wpuf-pro' ),
            ),
            array(
                'title'       => __( 'Custom field data not showing on the frontend', 'wpuf-pro' ),
                'type'        => 'Fix',
                'description' => __( 'If a user applies multiple conditions in a single field, the field was unable to show the data on the frontend.', 'wpuf-pro' ),
            ),
            array(
                'title'       => __( 'User details not showing on the frontend when user activity module is active', 'wpuf-pro' ),
                'type'        => 'Fix',
                'description' => __( 'User details not showing on the frontend when user activity module is active. You will get it fixed in this version.', 'wpuf-pro' ),
            ),
            array(
                'title'       => __( 'Unable to edit the page where registration form shortcode exists', 'wpuf-pro' ),
                'type'        => 'Fix',
                'description' => __( 'When `Subscription at Registration` option is enabled, it was unable to edit the page where the registration form shortcode exists, it just automatically goes to the frontend subscription page. Fixed in this release.', 'wpuf-pro' ),
            ),
            array(
                'title'       => __( 'Dokan Vendor Registration Form: some fields were not mapping correctly on vendor store page', 'wpuf-pro' ),
                'type'        => 'Fix',
                'description' => __( 'When using `Dokan Vendor Registration Form` following fields were not mapping correctly on the vendor store page: <br><br>- Store location google map <br>- Country field <br>- State field', 'wpuf-pro' ),
            ),
        ),
    ),
    array(
        'version'  => 'Version 2.9.0',
        'released' => '2018-09-20',
        'changes' => array(
            array(
                'title'       => __( 'File upload field - make uploaded audio/video files playable on the frontend', 'wpuf-pro' ),
                'type'        => 'New',
                'description' => __( 'Make Audio/Video files playable - This new option has been added in file upload field advanced options. After enabling this option user uploaded audio/video file will play on the frontend post page.', 'wpuf-pro' ),
            ),
            array(
                'title'       => __( 'Embed field - new custom field', 'wpuf-pro' ),
                'type'        => 'New',
                'description' => __( 'You can allow user to embed a video or another object into a post using this field. User just need to insert URL of the object, WPUF will automatically turn the URL into a related embed and provide a live preview in the visual editor. For supported sites please check <a href="https://codex.wordpress.org/Embeds">Embeds</a> documentation of codex.', 'wpuf-pro' ),
            ),
            array(
                'title'       => __( 'Notification settings in the registration form', 'wpuf-pro' ),
                'type'        => 'New',
                'description' => __( 'Added new notification section under registration form settings tab. Now, admin can enable/disable form specific email notifiations and change the email content.', 'wpuf-pro' ),
            ),
            array(
                'title'       => __( 'Conditional logic option to run/skip MailChimp integration after submission', 'wpuf-pro' ),
                'type'        => 'New',
                'description' => __( 'Now you have more control on Mailchimp integration. You can configure conditional logic with form fields, then MailChimp integration will only run if the configured condition meets by user when registering.', 'wpuf-pro' ),
            ),
            array(
                'title'       => __( 'Reports module', 'wpuf-pro' ),
                'type'        => 'New',
                'description' => __( 'Show various reports (User Reports, Post Reports, Subscription Reports, Transaction Reports). If you have purchased WPUF Pro business package then you can activate this module and check the reports under <b>User Frontend->Reports</b> menu.', 'wpuf-pro' ),
            ),
            array(
                'title'       => __( 'The Events Calendar integration template', 'wpuf-pro' ),
                'type'        => 'New',
                'description' => __( 'One click post form template, The Events Calendar form will allow users to create event from the frontend. Please check the documentation <a href="https://wedevs.com/docs/wp-user-frontend-pro/posting-forms/the-events-calendar-integration-template/">here.</a>', 'wpuf-pro' ),
            ),
        ),
    ),
    array(
        'version'  => 'Version 2.8.2',
        'released' => '2018-07-19',
        'changes' => array(
            array(
                'title'       => __( 'Added content filter feature for Post Title and Post Content', 'wpuf-pro' ),
                'type'        => 'New',
                'description' => __( 'Now you can restrict use of certain words for user submitted posts. You can find this under Content Filter section of WPUF Settings', 'wpuf-pro' ),
            ),
            array(
                'title'       => __( 'Resend activation email', 'wpuf-pro' ),
                'type'        => 'New',
                'description' => __( 'Users can now resend activation email in case they didn\'t  receive the email first time.', 'wpuf-pro' ),
            ),
            array(
                'title'       => __( 'New template for Easy Digital downloads products', 'wpuf-pro' ),
                'type'        => 'New',
                'description' => __( 'Just like WooCommerce product template we have provided a product template for EDD.', 'wpuf-pro' ),
            ),
            array(
                'title'       => __( 'Set custom Edit Profile form on Account page', 'wpuf-pro' ),
                'type'        => 'New',
                'description' => __( 'Now you can override the default Edit Profile Form on my account page. Go to WPUF Settings > My Account and choose a Profile Form', 'wpuf-pro' ),
            ),
            array(
                'title'       => __( 'More options within Customizer to change the look and feel of WPUF components', 'wpuf-pro' ),
                'type'        => 'New',
                'description' => __( 'We have added a new section under WordPress Customizer named WP User Front end , here you can change colors of notices and subscriptions and more', 'wpuf-pro' ),
            ),
            array(
                'title'       => __( 'Facebook social login URL not working issue is fixed', 'wpuf-pro' ),
                'type'        => 'Fix',
                'description' => __( 'Facebook redirect URL was not rendered properly and is fixed now', 'wpuf-pro' ),
            ),
        ),
    ),
    array(
        'version'  => 'Version 2.8.1',
        'released' => '2018-04-15',
        'changes' => array(
            array(
                'title'       => __( 'Added Tax for payments', 'wpuf-pro' ),
                'type'        => 'New',
                'description' => __( 'Now you can setup Tax rates on WPUF payments like Pay Per Post payments and Subscription Pack payments. Check the setup guideline <a href="https://wedevs.com/docs/wp-user-frontend-pro/settings/tax/" target="_blank">here</a>.', 'wpuf-pro' ),
            ),
            array(
                'title'       => __( 'Avatar image size on registration', 'wpuf-pro' ),
                'type'        => 'New',
                'description' => __( 'You can now set avatar size from User Frontend > Settings > Login/Registration > Avatar Size.', 'wpuf-pro' ),
            ),
            array(
                'title'       => __( 'Updated Stripe SDK', 'wpuf-pro' ),
                'type'        => 'Improvement',
                'description' => __( 'Updated Stripe SDK to 6.4.1. <br> Stripe module is now fully compatible with the latest Stripe API. If you are still using old API you should upgrade to latest API version from your  <a href="https://dashboard.stripe.com/developers" target="_blank">Stripe Dashboard</a>. Older API should work fine as well but it\'s recommended that you upgrade soon.', 'wpuf-pro' ),
            ),
            array(
                'title'       => __( 'Registration confirmation URL wasn\'t redirecting to login page', 'wpuf-pro' ),
                'type'        => 'Fix',
                'description' => __( 'Registration confirmation link now redirects users to Login page set in settings.', 'wpuf-pro' ),
            ),
            array(
                'title'       => __( 'Date format in coupon', 'wpuf-pro' ),
                'type'        => 'Fix',
                'description' => __( 'Coupon date format was not compatible with WordPress date format. Now it works with WordPress date format.', 'wpuf-pro' ),
            ),
            array(
                'title'       => __( 'User directory search query', 'wpuf-pro' ),
                'type'        => 'Fix',
                'description' => __( 'User directory search was not working for custom fields is fixed now.', 'wpuf-pro' ),
            ),
            array(
                'title'       => __( 'Showing country code on the frontend instead of country name', 'wpuf-pro' ),
                'type'        => 'Fix',
                'description' => __( 'Country field was showing country code which is irrelevant, now it will show country name on the frontend.', 'wpuf-pro' ),
            ),
            array(
                'title'       => __( 'Fixed google callback in social login', 'wpuf-pro' ),
                'type'        => 'Fix',
                'description' => __( 'Social login with google was not working in some cases.', 'wpuf-pro' ),
            ),
        ),
    ),
    array(
        'version'  => 'Version 2.8.0',
        'released' => '2018-01-02',
        'changes' => array(
            array(
                'title'       => __( 'Introducing New Modules for better Integration and Workflow of your Forms', 'wpuf-pro' ),
                'type'        => 'New',
                'description' => '<ul>
                                    <li style="margin-bottom: 5px"><b><i style="color: #1794CE;">Personal Package </i>: MailPoet 2</b></li>
                                    <li style="margin-bottom: 5px"><b><i style="color: #20C5BA;">Professional Package </i>: MailPoet 3 , Campaign Monitor, GetResponse & HTML Email Templates</b></li>
                                    <li style="margin-bottom: 5px"><b><i style="color: #F16E58">Business Package Exclusive </i> : Private Messaging, Zapier, Convert Kit & User Activity</b></li>
                                  </ul>
                                  <br>
                                  <a href="https://wedevs.com/in/wpuf-v2-8" target="_blank"> Click here to read more </a>',
            ),
            array(
                'title'       => __( 'Admin approval for newly Registered users', 'wpuf-pro' ),
                'type'        => 'New',
                'description' => __( 'A new option added on registration form settings to approve user by admin. You can make a user pending before approved by admin.', 'wpuf-pro' ) .
                '<br><br><iframe width="100%" height="372" src="https://www.youtube.com/embed/jJ05767-Ew4" frameborder="0" gesture="media" allow="encrypted-media" allowfullscreen></iframe>',
            ),
            array(
                'title'       => __( 'Subscription expire notification', 'wpuf-pro' ),
                'type'        => 'New',
                'description' => __( 'Add new notification for subscription expiration. User will get custom email after subscription expiration.', 'wpuf-pro' ) .
                '<br><br><iframe width="100%" height="372" src="https://www.youtube.com/embed/jotTY4FCHsk" frameborder="0" gesture="media" allow="encrypted-media" allowfullscreen></iframe>',
            ),
            array(
                'title'       => __( 'Form submission with Captcha field', 'wpuf-pro' ),
                'type'        => 'Improvement',
                'description' => __( 'Form field validation process updated if form submits with captcha field.', 'wpuf-pro' ),
            ),
            array(
                'title'       => __( 'Confirmation email not sent while email module is deactivated', 'wpuf-pro' ),
                'type'        => 'Fix',
                'description' => __( 'Users were not receiving confirmation email if the email module is deactivated, this issue is fixed now', 'wpuf-pro' ),
            ),
            array(
                'title'       => __( 'Various other bug fixed and improvements are done', 'wpuf-pro' ),
                'type'        => 'Fix',
                'description' => __( 'For more details see the Changelog.', 'wpuf-pro' ),
            ),
        ),
    ),
);

if ( ! function_exists( '_wpuf_changelog_content' ) ) {
    function _wpuf_changelog_content( $content ) {
        $content = wpautop( $content, true );

        return $content;
    }
}

?>

<div class="wrap wpuf-whats-new">
    <h1><?php _e( 'What\'s New in WPUF Pro?', 'wpuf' ); ?></h1>

    <div class="wedevs-changelog-wrapper">

        <?php foreach ( $changelog as $release ) { ?>
            <div class="wedevs-changelog">
                <div class="wedevs-changelog-version">
                    <h3><?php echo esc_html( $release['version'] ); ?></h3>
                    <p class="released">
                        (<?php echo human_time_diff( time(), strtotime( $release['released'] ) ); ?> ago)
                    </p>
                </div>
                <div class="wedevs-changelog-history">
                    <ul>
                        <?php foreach ( $release['changes'] as $change ) { ?>
                            <li>
                                <h4>
                                    <span class="title"><?php echo esc_html( $change['title'] ); ?></span>
                                    <span class="label <?php echo strtolower( $change['type'] ); ?>"><?php echo esc_html( $change['type'] ); ?></span>
                                </h4>

                                <?php if ( ! empty( $change['description'] ) ) : ?>
                                    <div class="description">
                                        <?php echo wp_kses_post( _wpuf_changelog_content( $change['description'] ) ); ?>
                                    </div>
                                <?php endif; ?>
                            </li>
                        <?php } ?>
                    </ul>
                </div>
            </div>
        <?php } ?>
    </div>

</div>
