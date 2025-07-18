/* eslint-disable no-console */
/* eslint-disable promise/catch-or-return */
/* eslint-disable promise/always-return */

define([
    "jquery",
    "core/str",
    "core/ajax",
    "core/notification",
    "core/st_loader",
    "core/url",
    'select2-js',
], function(
    $,
    str,
    ajax,
    notification,
    stLoader,
    moodleurl
) {
    "use strict";
    var apiCredentials = {
        dom: {
            main: null,
            clientIdField: null,
            clientSecretField: null,
            scopeField: null,
            grantTypeField: null,
            saveButton: null,
            updateButton: null,
            form: null,
            togglePassword: null
        },
        lang: {
            somethingWentWrong: null,
            saveSuccess: null,
            updateSuccess: null,
            showPassword: null,
            hidePassword: null
        },
        variables: {
            recordId: 0
        },
        url: {
            vanta: function() {
                return moodleurl.relativeUrl('/local/vanta/training_sync_rules.php');
            }
        },
        action: {
            getstringvalues: function() {
                str.get_strings([
                    {
                        key: "some_thing_went_wrong",
                        component: "local_vanta"
                    },
                    {
                        key: "save_success",
                        component: "local_vanta"
                    },
                    {
                        key: "update_success",
                        component: "local_vanta"
                    },
                    {
                        key: "show_password",
                        component: "local_vanta"
                    },
                    {
                        key: "hide_password",
                        component: "local_vanta"
                    }
                ]).done(function(langstrs) {
                    apiCredentials.lang.somethingWentWrong = langstrs[0];
                    apiCredentials.lang.saveSuccess = langstrs[1];
                    apiCredentials.lang.updateSuccess = langstrs[2];
                    apiCredentials.lang.showPassword = langstrs[3];
                    apiCredentials.lang.hidePassword = langstrs[4];
                    apiCredentials.init();
                }).fail(function() {
                    notification.addNotification({
                        type: "error",
                        message: "Failed to load language strings",
                    });
                });
            },

            validateForm: function() {
                var isValid = true;
                if (apiCredentials.dom.clientIdField.val().trim() === "") {
                    apiCredentials.dom.clientIdField.addClass('is-invalid');
                    isValid = false;
                } else {
                    apiCredentials.dom.clientIdField.removeClass('is-invalid');
                }
                if (apiCredentials.dom.clientSecretField.val().trim() === "") {
                    apiCredentials.dom.clientSecretField.addClass('is-invalid');
                    isValid = false;
                } else {
                    apiCredentials.dom.clientSecretField.removeClass('is-invalid');
                }
                return isValid;
            },

            togglePasswordVisibility: function() {
                var isPasswordVisible = apiCredentials.dom.clientSecretField.attr('type') === 'text';
                var newType = isPasswordVisible ? 'password' : 'text';
                var newIcon = isPasswordVisible ? 'fa-eye' : 'fa-eye-slash';
                var oldIcon = isPasswordVisible ? 'fa-eye-slash' : 'fa-eye';
                var newTitle = isPasswordVisible ? apiCredentials.lang.showPassword : apiCredentials.lang.hidePassword;

                apiCredentials.dom.clientSecretField.attr('type', newType);
                apiCredentials.dom.togglePassword.find('i.fa')
                    .removeClass(oldIcon)
                    .addClass(newIcon);
                apiCredentials.dom.togglePassword.attr('title', newTitle);
            },

            saveCredentials: function() {
                stLoader.showLoader();
                var request = {
                    methodname: 'local_vanta_save_credentials',
                    args: {
                        name: apiCredentials.dom.nameField.val(),
                        clientid: apiCredentials.dom.clientIdField.val(),
                        clientsecret: apiCredentials.dom.clientSecretField.val(),
                        scope: apiCredentials.dom.scopeField.val(),
                        granttype: apiCredentials.dom.grantTypeField.val()
                    }
                };
                ajax.call([request])[0].done(function(response) {
                    stLoader.hideLoader();
                    if (response.success) {
                        apiCredentials.variables.recordId = response.id;
                        notification.addNotification({
                            type: "success",
                            message: apiCredentials.lang.saveSuccess || "API credentials saved successfully"
                        });
                        window.location.href = apiCredentials.url.vanta();
                    } else {
                        notification.addNotification({
                            type: "error",
                            message: response.message || apiCredentials.lang.somethingWentWrong
                        });
                    }
                }).fail(function(error) {
                    stLoader.hideLoader();
                    notification.addNotification({
                        type: "error",
                        message: apiCredentials.lang.somethingWentWrong
                    });
                    console.error(error);
                });
            },

            updateCredentials: function() {
                stLoader.showLoader();
                var request = {
                    methodname: 'local_vanta_update_credentials',   
                    args: {
                        name: apiCredentials.dom.nameField.val(),
                        clientid: apiCredentials.dom.clientIdField.val(),
                        clientsecret: apiCredentials.dom.clientSecretField.val(),
                    }
                };

                ajax.call([request])[0].done(function(response) {
                    stLoader.hideLoader();
                    if (response.success) {
                        notification.addNotification({
                            type: "success",
                            message: apiCredentials.lang.updateSuccess || "API credentials updated successfully"
                        });
                        window.location.href = apiCredentials.url.vanta();
                    } else {
                        notification.addNotification({
                            type: "error",
                            message: response.message || apiCredentials.lang.somethingWentWrong
                        });
                    }
                }).fail(function(error) {
                    stLoader.hideLoader();
                    notification.addNotification({
                        type: "error",
                        message: apiCredentials.lang.somethingWentWrong
                    });
                    console.error(error);
                });
            }
        },
        init: function() {
            apiCredentials.dom.main = $("#local_vanta_api_credentials");
            apiCredentials.dom.form = apiCredentials.dom.main.find("#vanta-config-form");
            apiCredentials.dom.nameField = apiCredentials.dom.main.find("#name");
            apiCredentials.dom.clientIdField = apiCredentials.dom.main.find("#client_id");
            apiCredentials.dom.clientSecretField = apiCredentials.dom.main.find("#client_secret");
            apiCredentials.dom.scopeField = apiCredentials.dom.main.find("#scope");
            apiCredentials.dom.grantTypeField = apiCredentials.dom.main.find("#grant_type");
            apiCredentials.dom.saveButton = apiCredentials.dom.main.find("#save_credentials");
            apiCredentials.dom.updateButton = apiCredentials.dom.main.find("#update_credentials");
            apiCredentials.dom.togglePassword = apiCredentials.dom.main.find(".toggle-password");

            // Initialize password field as password type
            apiCredentials.dom.clientSecretField.attr('type', 'password');

            // Add toggle password event handler
            apiCredentials.dom.togglePassword.on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                apiCredentials.action.togglePasswordVisibility();
            });

            // Form submission handler
            apiCredentials.dom.form.on('submit', function(e) {
                e.preventDefault();
                if (apiCredentials.action.validateForm()) {
                    apiCredentials.action.saveCredentials();
                }
            });

            // Check if we have an existing record ID (for updates)
            apiCredentials.variables.recordId = apiCredentials.dom.form.data('record-id') || 0;

            if (apiCredentials.variables.recordId > 0) {
                // We have an existing record, show update button
                apiCredentials.dom.updateButton.removeClass('d-none');
                apiCredentials.dom.saveButton.addClass('d-none');
            } else {
                // No existing record, show save button
                apiCredentials.dom.saveButton.removeClass('d-none');
                apiCredentials.dom.updateButton.addClass('d-none');
            }
        }
    };
    return {
        init: apiCredentials.action.getstringvalues,
    };
});