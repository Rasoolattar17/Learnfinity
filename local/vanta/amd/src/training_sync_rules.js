/* eslint-disable no-console */
/* eslint-disable promise/catch-or-return */
/* eslint-disable promise/always-return */
console.log('Training sync rules AMD module loaded');
define([
    'jquery',
    'core/str',
    'core/ajax',
    'core/notification',
    'core/templates',
    'core/url'
], function(
    $,
    str,
    ajax,
    notification,
) {
    "use strict";
    var trainingSyncRules = {
        dom: {
            main: null,
            frameworksSelect: null,
            coursesSelect: null,
            completionModeContainer: null,
            completionModeSelect: null,
            saveButton: null,
            form: null,
            resourceId: null
        },
        lang: {
            somethingWentWrong: null,
            saveSuccess: null
        },
        variables: {
            vantaId: 0,
            selectedFrameworks: [],
            selectedCourses: []
        },
        action: {
            getStringValues: function() {
                return str.get_strings([
                    {
                        key: "some_thing_went_wrong",
                        component: "local_vanta"
                    },
                    {
                        key: "settingssaved",
                        component: "local_vanta"
                    }
                ]).then(function(langstrs) {
                    trainingSyncRules.lang.somethingWentWrong = langstrs[0];
                    trainingSyncRules.lang.saveSuccess = langstrs[1];
                    return true;
                }).catch(function(error) {
                    console.error('Failed to load language strings:', error);
                    notification.addNotification({
                        type: "error",
                        message: "Failed to load language strings"
                    });
                    return false;
                });
            },
            validation: function() {
                console.log('Running validation...');
                var isValid = true;

                // Validate resource ID
                if (!trainingSyncRules.dom.resourceId || trainingSyncRules.dom.resourceId.val() === '') {
                    console.log('Resource ID validation failed');
                    if (trainingSyncRules.dom.resourceId) {
                        trainingSyncRules.dom.resourceId.addClass('is-invalid');
                    }
                    isValid = false;
                } else {
                    if (trainingSyncRules.dom.resourceId) {
                        trainingSyncRules.dom.resourceId.removeClass('is-invalid');
                    }
                }

                // Validate frameworks
                if (trainingSyncRules.variables.selectedFrameworks.length === 0) {
                    console.log('Frameworks validation failed');
                    if (trainingSyncRules.dom.frameworksSelect) {
                        trainingSyncRules.dom.frameworksSelect.addClass('is-invalid');
                    }
                    isValid = false;
                } else {
                    if (trainingSyncRules.dom.frameworksSelect) {
                        trainingSyncRules.dom.frameworksSelect.removeClass('is-invalid');
                    }
                }

                // Validate courses
                if (trainingSyncRules.variables.selectedCourses.length === 0) {
                    console.log('Courses validation failed');
                    if (trainingSyncRules.dom.coursesSelect) {
                        trainingSyncRules.dom.coursesSelect.addClass('is-invalid');
                    }
                    isValid = false;
                } else {
                    if (trainingSyncRules.dom.coursesSelect) {
                        trainingSyncRules.dom.coursesSelect.removeClass('is-invalid');
                    }
                }

                console.log('Validation result:', isValid);
                return isValid;
            },

            saveSettings: function() {
                console.log('Starting save settings...');

                var formData = {
                    frameworks: trainingSyncRules.variables.selectedFrameworks,
                    courses: trainingSyncRules.variables.selectedCourses,
                    completionmode: trainingSyncRules.dom.completionModeSelect.val(),
                    resourceid: trainingSyncRules.dom.resourceId.val(),
                };

                console.log('Form data:', formData);

                // Use standard Moodle AJAX call to save settings
                return ajax.call([{
                    methodname: 'local_vanta_save_sync_rules',
                    args: formData
                }])[0].then(function(response) {
                    console.log('Save response:', response);

                    if (response.success) {
                        notification.addNotification({
                            type: 'success',
                            message: trainingSyncRules.lang.saveSuccess
                        });
                    } else {
                        notification.addNotification({
                            type: 'error',
                            message: response.message || trainingSyncRules.lang.somethingWentWrong
                        });
                    }
                    return response;
                }).catch(function(error) {
                    console.error('Save failed:', error);
                    notification.addNotification({
                        type: 'error',
                        message: trainingSyncRules.lang.somethingWentWrong
                    });
                    throw error;
                });
            },

            initializeSelects: function() {
                // Initialize select2 with error handling
                if (trainingSyncRules.dom.frameworksSelect.length > 0) {
                    try {
                        trainingSyncRules.dom.frameworksSelect.select2({
                            placeholder: "Select compliance frameworks",
                            allowClear: true,
                            width: '100%'
                        });
                    } catch (e) {
                        console.error('Error initializing frameworks select2:', e);
                    }
                }

                if (trainingSyncRules.dom.coursesSelect.length > 0) {
                    try {
                        trainingSyncRules.dom.coursesSelect.select2({
                            placeholder: "Select courses",
                            allowClear: true,
                            width: '100%'
                        });
                    } catch (e) {
                        console.error('Error initializing courses select2:', e);
                    }
                }
            }
        },

        init: function(config) {
            console.log('Initializing training sync rules with config:', config);

            // Store vantaId from config
            if (config && config.vantaId) {
                trainingSyncRules.variables.vantaId = config.vantaId;
            }

            // Initialize DOM references
            trainingSyncRules.dom.main = $('#local_vanta_training_sync_rules');
            trainingSyncRules.dom.form = $('#vanta-sync-rules-form');
            trainingSyncRules.dom.frameworksSelect = $('#frameworks');
            trainingSyncRules.dom.coursesSelect = $('#courses');
            trainingSyncRules.dom.resourceId = $('.resource_id');
            trainingSyncRules.dom.completionModeContainer = $('#completion_mode_container');
            trainingSyncRules.dom.completionModeSelect = $('#completion_mode');
            trainingSyncRules.dom.saveButton = $('.save-sync-rules');

            // Load language strings first
            return trainingSyncRules.action.getStringValues().then(function() {
                // Initialize select2 dropdowns
                trainingSyncRules.action.initializeSelects();

                // Store initial values
                trainingSyncRules.variables.selectedFrameworks = trainingSyncRules.dom.frameworksSelect.val() || [];
                trainingSyncRules.variables.selectedCourses = trainingSyncRules.dom.coursesSelect.val() || [];

                // Handle save button click
                trainingSyncRules.dom.saveButton.on('click', function(e) {
                    console.log('Save button clicked');
                    e.preventDefault();
                    e.stopPropagation();

                    // Update variables before validation
                    trainingSyncRules.variables.selectedFrameworks = trainingSyncRules.dom.frameworksSelect.val() || [];
                    trainingSyncRules.variables.selectedCourses = trainingSyncRules.dom.coursesSelect.val() || [];

                    if (trainingSyncRules.action.validation()) {
                        trainingSyncRules.action.saveSettings();
                    }
                });

                // Handle select changes
                trainingSyncRules.dom.frameworksSelect.on('change', function() {
                    trainingSyncRules.variables.selectedFrameworks = $(this).val() || [];
                });

                trainingSyncRules.dom.coursesSelect.on('change', function() {
                    trainingSyncRules.variables.selectedCourses = $(this).val() || [];
                });

                return true;
            });
        }
    };

    return {
        init: trainingSyncRules.action.getStringValues
    };
});