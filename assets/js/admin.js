/**
 * Admin JavaScript for Topbar Countdown Notice
 * Enhanced with improved color management and UI controls
 */

(function ($) {
    'use strict';

    $(document).ready(function () {
        // Initialize color pickers
        if ($.fn.wpColorPicker) {
            $('.tcn-color-picker').wpColorPicker({
                // Reset color when clicking the default button
                defaultColor: true,
                // Hide the color picker controls on load
                hide: true,
                // Set the 'Clear' button text
                clear: function () {
                    $(this).wpColorPicker('color', '');
                }
            });
        }

        // Handle conditional field visibility
        function toggleConditionalFields() {
            var finishAction = $('input[name="tcn_settings[finish_action]"]:checked').val();

            // Show/hide alternative content fields
            var alternativeFields = $(
                '#alternative_text_editor, ' +
                'input[name="tcn_settings[alternative_link]"], ' +
                'input[name="tcn_settings[alternative_link_new_tab]"], ' +
                'input[name="tcn_settings[alternative_colors_mode]"], ' +
                'input[name="tcn_settings[alternative_bg_color]"], ' +
                'input[name="tcn_settings[alternative_text_color]"]'
            ).closest('tr');

            if (finishAction === 'show_alternative') {
                alternativeFields.show();
                toggleAlternativeColorFields();
            } else {
                alternativeFields.hide();
            }

            // Show/hide countdown fields
            var countdownActive = $('input[name="tcn_settings[countdown_active]"]').is(':checked');
            var countdownFields = $(
                'input[name="tcn_settings[countdown_target_mode]"], ' +
                'input[name="tcn_settings[countdown_target_custom]"], ' +
                '#countdown_prefix_editor'
            ).closest('tr');

            if (countdownActive) {
                countdownFields.show();
                toggleCountdownTargetFields();
            } else {
                countdownFields.hide();
            }
        }

        // Show/hide countdown target custom field based on mode
        function toggleCountdownTargetFields() {
            var targetMode = $('input[name="tcn_settings[countdown_target_mode]"][type="radio"]:checked').val();
            var customField = $('input[name="tcn_settings[countdown_target_custom]"]').closest('tr');

            if (targetMode === 'custom') {
                customField.show();
            } else {
                customField.hide();
            }
        }

        // Show/hide alternative color fields based on mode
        function toggleAlternativeColorFields() {
            var colorsMode = $('input[name="tcn_settings[alternative_colors_mode]"][type="radio"]:checked').val();
            var colorFields = $(
                'input[name="tcn_settings[alternative_bg_color]"], ' +
                'input[name="tcn_settings[alternative_text_color]"]'
            ).closest('tr');

            if (colorsMode === 'custom') {
                colorFields.show();
            } else {
                colorFields.hide();
            }
        }

        // Update color previews
        function updateColorPreviews() {
            // Main colors
            var bgColor = $('input[name="tcn_settings[bg_color]"]').val();
            var textColor = $('input[name="tcn_settings[text_color]"]').val();

            // Alternative colors
            var altBgColor = $('input[name="tcn_settings[alternative_bg_color]"]').val();
            var altTextColor = $('input[name="tcn_settings[alternative_text_color]"]').val();

            // Update main color preview
            $('.tcn-color-preview').css({
                'background-color': bgColor || '#f8f9fa',
                'color': textColor || '#212529',
                'border': '1px solid ' + (textColor ? textColor + '40' : 'rgba(33, 37, 41, 0.25)')
            });

            // Update alternative color preview if in custom mode
            if ($('input[name="tcn_settings[alternative_colors_mode]"][value="custom"]').is(':checked')) {
                $('.tcn-alternative-color-preview').css({
                    'background-color': altBgColor || bgColor || '#f8f9fa',
                    'color': altTextColor || textColor || '#212529',
                    'border': '1px solid ' + (altTextColor ? altTextColor + '40' : textColor ? textColor + '40' : 'rgba(33, 37, 41, 0.25)')
                });
            } else {
                // Use main colors for preview when not in custom mode
                $('.tcn-alternative-color-preview').css({
                    'background-color': bgColor || '#f8f9fa',
                    'color': textColor || '#212529',
                    'border': '1px solid ' + (textColor ? textColor + '40' : 'rgba(33, 37, 41, 0.25)')
                });
            }
        }

        // Initial state
        toggleConditionalFields();

        // Initialize color previews
        updateColorPreviews();

        // Listen for changes
        $('input[name="tcn_settings[finish_action]"], ' +
            'input[name="tcn_settings[countdown_active]"]')
            .on('change', toggleConditionalFields);

        $('input[name="tcn_settings[countdown_target_mode]"][type="radio"]')
            .on('change', toggleCountdownTargetFields);

        $('input[name="tcn_settings[alternative_colors_mode]"][type="radio"]')
            .on('change', toggleAlternativeColorFields);

        // Update color previews when color picker values change
        $('.tcn-color-picker').on('change', function () {
            updateColorPreviews();
        });



        // Add color preview elements if they don't exist
        if ($('.tcn-color-preview').length === 0) {
            $('<div class="tcn-color-preview" style="display: inline-block; width: 20px; height: 20px; margin-left: 10px; border-radius: 3px; vertical-align: middle;"></div>')
                .insertAfter('input[name="tcn_settings[bg_color]"]');

            $('<div class="tcn-alternative-color-preview" style="display: inline-block; width: 20px; height: 20px; margin-left: 10px; border-radius: 3px; vertical-align: middle;"></div>')
                .insertAfter('input[name="tcn_settings[alternative_bg_color]"]');

            // Add labels for the previews
            $('<span class="description" style="margin-left: 5px;">Preview</span>')
                .insertAfter('.tcn-color-preview, .tcn-alternative-color-preview');
        }
    });

})(jQuery);
