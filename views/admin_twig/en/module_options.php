<?php

$aLang = [
    'charset' => 'UTF-8',

    // Migration warning
    'OXSREQUESTLOGGERREMOTE_MIGRATION_REQUIRED_TEXT' => 'The database migrations have not been executed yet. Please run the following command:',

    // Setup workflow
    'OXSREQUESTLOGGERREMOTE_SETUP_TITLE' => 'Setup Workflow',
    'OXSREQUESTLOGGERREMOTE_SETUP_STEP_INSTALL' => 'Module installed',
    'OXSREQUESTLOGGERREMOTE_SETUP_STEP_MIGRATE' => 'Migrations executed',
    'OXSREQUESTLOGGERREMOTE_SETUP_STEP_ACTIVATE' => 'Module activated',
    'OXSREQUESTLOGGERREMOTE_SETUP_STEP_ACTIVATE_WARNING' => 'Module was activated without executing migrations first. Please deactivate, run migrations, and activate again.',
    'OXSREQUESTLOGGERREMOTE_SETUP_STEP_SEND_TOKEN' => 'Send setup token to OXID Support',
    'OXSREQUESTLOGGERREMOTE_SETUP_STEP_SEND_TOKEN_DESC' => 'Copy the token below and send it via email to support@oxid-esales.com',
    'OXSREQUESTLOGGERREMOTE_SETUP_STEP_WAIT_SUPPORT' => 'Wait for OXID Support to activate API access',
    'OXSREQUESTLOGGERREMOTE_SETUP_COPIED' => 'Copied!',
    'OXSREQUESTLOGGERREMOTE_SETUP_COMPLETE_TITLE' => 'Setup Complete',
    'OXSREQUESTLOGGERREMOTE_SETUP_COMPLETE_TEXT' => 'Remote access has been successfully configured. OXID Support can now access the Request Logger settings.',

    // Password Reset Admin Page
    'OXSREQUESTLOGGERREMOTE_PASSWORD_RESET_TITLE' => 'API User Password Reset',
    'OXSREQUESTLOGGERREMOTE_PASSWORD_RESET_MENU' => 'Password Reset',
    'OXSREQUESTLOGGERREMOTE_DANGER_ZONE' => 'DANGER ZONE - Critical Operation',
    'OXSREQUESTLOGGERREMOTE_RESET_DESCRIPTION' => 'This action will reset the Request Logger API user password and generate a new setup token. Only use this if instructed by OXID Support or if you need to re-establish remote access.',
    'OXSREQUESTLOGGERREMOTE_WARNING_1' => 'The current API password will be PERMANENTLY INVALIDATED',
    'OXSREQUESTLOGGERREMOTE_WARNING_2' => 'All existing remote sessions will be terminated immediately',
    'OXSREQUESTLOGGERREMOTE_WARNING_3' => 'OXID Support will lose access until a new password is set',
    'OXSREQUESTLOGGERREMOTE_WARNING_4' => 'You must send the new token to OXID Support to restore access',
    'OXSREQUESTLOGGERREMOTE_CONFIRM_RESET' => 'I understand the consequences and want to reset the password',
    'OXSREQUESTLOGGERREMOTE_CONFIRM_DIALOG' => 'Are you absolutely sure? This will immediately revoke all remote access!',
    'OXSREQUESTLOGGERREMOTE_RESET_BUTTON' => 'Reset Password & Generate New Token',
    'OXSREQUESTLOGGERREMOTE_RESET_SUCCESS' => 'Password has been reset successfully!',
    'OXSREQUESTLOGGERREMOTE_NEW_TOKEN_INFO' => 'A new setup token has been generated. Please send this token to OXID Support:',
    'OXSREQUESTLOGGERREMOTE_TOKEN_COPY_HINT' => 'Copy this token and send it to support@oxid-esales.com',
    'OXSREQUESTLOGGERREMOTE_RESET_ERROR' => 'An error occurred: ',
    'OXSREQUESTLOGGERREMOTE_ERROR_USER_NOT_FOUND' => 'The API user could not be found. Please ensure the module migrations have been executed.',
    'OXSREQUESTLOGGERREMOTE_TOKEN_EXISTS_INFO' => 'A setup token already exists. The password has not been set yet. If you need to generate a new token, use the reset function below.',
];
