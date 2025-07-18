/* eslint-disable no-console */
/* eslint-disable promise/catch-or-return */
/* eslint-disable promise/always-return */

define([
    'jquery',
    'core/str',
    'core/ajax',
    'core/notification',
    'core/templates',
    'core/st_loader',
    'core/url',
    'select2-js'
], function(
    $,
    str,
    ajax,
    notification,
    templates,
    stLoader,
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
                str.get_strings([
                    {
                        key: "some_thing_went_wrong",
                        component: "local_vanta"
                    },
                    {
                        key: "settingssaved",
                        component: "local_vanta"
                    }
                ]).done(function(langstrs) {
                    trainingSyncRules.lang.somethingWentWrong = langstrs[0];
                    trainingSyncRules.lang.saveSuccess = langstrs[1];
                    trainingSyncRules.init();
                }).fail(function() {
                    notification.addNotification({
                        type: "error",
                        message: "Failed to load language strings"
                    });
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
                stLoader.showLoader();

                var formData = {
                    frameworks: trainingSyncRules.variables.selectedFrameworks,
                    courses: trainingSyncRules.variables.selectedCourses,
                    completionmode: trainingSyncRules.dom.completionModeSelect.val(),
                    resourceid: trainingSyncRules.dom.resourceId.val(),
                };

                console.log('Form data:', formData);

                // Use standard Moodle AJAX call to save settings
                ajax.call([{
                    methodname: 'local_vanta_save_sync_rules',
                    args: formData
                }])[0].done(function(response) {
                    stLoader.hideLoader();
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
                }).fail(function(error) {
                    stLoader.hideLoader();
                    console.error('Save failed:', error);
                    notification.addNotification({
                        type: 'error',
                        message: trainingSyncRules.lang.somethingWentWrong
                    });
                });
            },
        },

        init: function() {
            console.log('Initializing training sync rules...');

            // Initialize DOM references with error checking
            trainingSyncRules.dom.main = $('#local_vanta_training_sync_rules');
            trainingSyncRules.dom.form = $('#vanta-sync-rules-form');
            trainingSyncRules.dom.frameworksSelect = $('#frameworks');
            trainingSyncRules.dom.coursesSelect = $('#courses');
            trainingSyncRules.dom.resourceId = $('.resource_id');
            trainingSyncRules.dom.completionModeContainer = $('#completion_mode_container');
            trainingSyncRules.dom.completionModeSelect = $('#completion_mode');
            trainingSyncRules.dom.saveButton = $('.save-sync-rules');


               // Handle save button click with enhanced error handling
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


            // Debug DOM elements
            console.log('DOM elements found:');
            console.log('- Main container:', trainingSyncRules.dom.main.length);
            console.log('- Form:', trainingSyncRules.dom.form.length);
            console.log('- Frameworks select:', trainingSyncRules.dom.frameworksSelect.length);
            console.log('- Courses select:', trainingSyncRules.dom.coursesSelect.length);
            console.log('- Resource ID:', trainingSyncRules.dom.resourceId.length);
            console.log('- Save button:', trainingSyncRules.dom.saveButton.length);

            // Store initial values
            trainingSyncRules.variables.selectedFrameworks = trainingSyncRules.dom.frameworksSelect.val() || [];
            trainingSyncRules.variables.selectedCourses = trainingSyncRules.dom.coursesSelect.val() || [];

            // Initialize completion mode visibility based on initial course selection
            if (trainingSyncRules.variables.selectedCourses.length > 1) {
                trainingSyncRules.dom.completionModeContainer.show();
                if (!trainingSyncRules.dom.completionModeSelect.val()) {
                    trainingSyncRules.dom.completionModeSelect.val('any');
                }
            } else {
                trainingSyncRules.dom.completionModeContainer.hide();
                trainingSyncRules.dom.completionModeSelect.val('any');
            }

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

            // Initialize select2 on courses
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

            // Handle course selection change for conditional display
            trainingSyncRules.dom.coursesSelect.on('change', function() {
                console.log('Course selection changed');
                var selectedCourses = $(this).val();
                trainingSyncRules.variables.selectedCourses = selectedCourses || [];

                // Show/hide completion mode selector based on number of selected courses
                if (selectedCourses && selectedCourses.length > 1) {
                    trainingSyncRules.dom.completionModeContainer.show();
                    // Set default value to 'any' if not already set
                    if (!trainingSyncRules.dom.completionModeSelect.val()) {
                        trainingSyncRules.dom.completionModeSelect.val('any');
                    }
                } else {
                    trainingSyncRules.dom.completionModeContainer.hide();
                    // Reset to 'any' when hiding
                    trainingSyncRules.dom.completionModeSelect.val('any');
                }
            });

            // Handle frameworks selection change
            trainingSyncRules.dom.frameworksSelect.on('change', function() {
                trainingSyncRules.variables.selectedFrameworks = $(this).val() || [];
            });

            // Prevent form submission
            if (trainingSyncRules.dom.form.length > 0) {
                trainingSyncRules.dom.form.on('submit', function(e) {
                    console.log('Form submit prevented');
                    e.preventDefault();
                    return false;
                });
            }

            console.log('Training sync rules initialization complete');
        }
    };
    return {
        init: trainingSyncRules.action.getStringValues
    };
});