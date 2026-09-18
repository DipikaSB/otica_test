/**
 * @package Magedelight_Ga4 for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */
define([
    'jquery',
    'Magento_Ui/js/modal/alert',
    'jquery/ui',
    'mage/translate'
], function ($, alert) {
    "use strict";

    var GA4API = GA4API || {};

    var optionsForm = $('#config-edit-form');

    var triggerButton = $('#save_gtm_api'),
        triggerJsonGenerateButton = $('#generate_gtm_api_json'),
        accountID = $('#magedelight_ga4_gtag_account_id'),
        containerID = $('#magedelight_ga4_gtag_container_id'),
        uaTrackingID = $('#magedelight_ga4_gtag_tracking_id'),
        ipAnonymization = $('#magedelight_ga4_gtag_ipaddress'),
        displayAdvertising = $('#magedelight_ga4_gtag_display_advertising'),
        jsonExportPublicId = $("#magedelight_ga4_gtag_tracking_id"),
        formKey = $('#api_form_key');


    GA4API.initializeJsonGeneration = function (itemJsonGenerationUrl) {
        var that = this;
        $(triggerJsonGenerateButton).click(function () {
            $('.use-default .checkbox').each(function () {
                if ($(this).is(':checked')) {
                    $(this).trigger('click').addClass('forced-click');
                }
            });
            var validation = that._validateInputs();

            if (!validation.length) {
                validation = that._validateJsonExportInputs();
            }

            if (validation.length) {
                alert({content: validation.join('')});
            } else {
                $.ajax({
                    showLoader: true,
                    url: itemJsonGenerationUrl,
                    data: {
                        'form_key' : formKey.val(),
                        'account_id' : accountID.val().trim(),
                        'container_id' : containerID.val().trim(),
                        'tracking_id' : uaTrackingID.val().trim(),
                        'ip_address' : ipAnonymization.val(),
                        'display_advertising' : displayAdvertising.val(),
                        'public_id' : jsonExportPublicId.val().trim(),
                        'form_data' : optionsForm.serialize()
                    },
                    type: "POST",
                    dataType: 'json'
                }).done(function (data) {
                    alert({content: data.msg.join('<br>')});
                    if (data.jsonUrl) {
                        $('#download_gtm_json').show();
                        $('#generate_gtm_api_json').hide();
                        $('#download_gtm_json').removeClass('disabled');
                        $('#download_gtm_json').removeAttr('disabled');
                    } else {
                        $('#generate_gtm_api_json').show();
                        $('#download_gtm_json').hide();
                    }
                    $('.use-default .checkbox.forced-click').each(function () {
                        $(this).trigger('click').removeClass('forced-click');
                    });
                    $('.use-default .checkbox.forced-click').trigger('click').removeClass('forced-click');
                });
            }
        });
    };


    GA4API.initialize = function (itemPostUrl) {
        var that = this;
        $(triggerButton).click(function () {
            var validation = that._validateInputs();
            if (validation.length) {
                alert({content: validation.join('')});
            } else {
                $.ajax({
                    showLoader: true,
                    url: itemPostUrl,
                    data: {
                        'form_key' : formKey.val(),
                        'account_id' : accountID.val().trim(),
                        'container_id' : containerID.val().trim(),
                        'ua_tracking_id' : uaTrackingID.val().trim(),
                        'ip_anonymization' : ipAnonymization.val(),
                        'display_advertising' : displayAdvertising.val()
                    },
                    type: "POST",
                    dataType: 'json',
                    beforeSend: function () {
                        setTimeout(function () {
                            var progressHtml = '<div id="gtm-progressbar"><div class="gtm-progress-label">Loading...</div></div>';
                            $('.loading-mask').append(progressHtml);
                            $('.loading-mask .popup-inner').append('<span class="long-operation-label">this operation may take up to 5 minutes</span>');
                            var progressbar = $("#gtm-progressbar"),
                                progressLabel = $(".gtm-progress-label");
                            progressbar.progressbar({
                                value: false,
                                change: function () {
                                    progressLabel.text(progressbar.progressbar("value") + "%");
                                }
                            });
                            function progress()
                            {
                                var val = progressbar.progressbar("value") || 0;
                                progressbar.progressbar("value", val + 1);
                                if ( val < 99 ) {
                                    setTimeout(progress, 2300);
                                }
                            }
                            progress();
                        }, 1500);
                    }
                }).done(function (data) {
                    $('#gtm-progressbar').remove();
                    $('.long-operation-label').remove();
                    alert({content: data.join('<br>')});
                });
            }
        });

        var url = window.location.href;
        var newUrl = url.split('?')[0];
        if (url != newUrl) {
            window.history.pushState({}, window.document.title, newUrl);
        }
    };

    GA4API.initializeConversionTracking = function (itemPostUrl) {
        var that = this;
        $(conversionTrackingButton).click(function () {
            var validation = that._validateConversionTrackingInputs();
            if (validation.length) {
                alert({content: validation.join('')});
            } else {
                $.ajax({
                    showLoader: true,
                    url: itemPostUrl,
                    data: {
                        'form_key' : formKey.val(),
                        'account_id' : accountID.val().trim(),
                        'container_id' : containerID.val().trim()
                    },
                    type: "POST",
                    dataType: 'json'
                }).done(function (data) {
                    alert({content: data.join('<br>')});
                });
            }
        });
    };

    GA4API.initializeRemarketing = function (itemPostUrl) {
        var that = this;
        $(remarketingButton).click(function () {
            var validation = that._validateRemarketingInputs();
            if (validation.length) {
                alert({content: validation.join('')});
            } else {
                $.ajax({
                    showLoader: true,
                    url: itemPostUrl,
                    data: {
                        'form_key' : formKey.val(),
                        'account_id' : accountID.val().trim(),
                        'container_id' : containerID.val().trim()
                    },
                    type: "POST",
                    dataType: 'json'
                }).done(function (data) {
                    alert({content: data.join('<br>')});
                });
            }
        });
    };

    GA4API._validateInputs = function () {
        var errors = [];
        if (accountID.val().trim() == '') {
            errors.push($.mage.__('Please specify the Account ID') + '<br>');
        }
        if (containerID.val().trim() == '') {
            errors.push($.mage.__('Please specify the Container ID') + '<br>');
        }
        if (uaTrackingID.val().trim() == '') {
            errors.push($.mage.__('Please specify the Measurement ID') + '<br>');
        }

        return errors;
    };


    GA4API._validateConversionTrackingInputs = function () {
        var errors = [];
        if (accountID.val().trim() == '') {
            errors.push($.mage.__('Please specify the Account ID in GTM API Configuration section') + '<br>');
        }
        if (containerID.val().trim() == '') {
            errors.push($.mage.__('Please specify the Container ID in GTM API Configuration section') + '<br>');
        }

        return errors;
    };

    GA4API._validateJsonExportInputs = function () {
        var errors = [];
        if (jsonExportPublicId.val().trim() == '') {
            errors.push($.mage.__('Please specify the Public Id') + '<br>');
        }
        return errors;
    };


    GA4API._validateRemarketingInputs = function () {
        var errors = [];
        if (accountID.val().trim() == '') {
            errors.push($.mage.__('Please specify the Account ID in GTM API Configuration section') + '<br>');
        }
        if (containerID.val().trim() == '') {
            errors.push($.mage.__('Please specify the Container ID in GTM API Configuration section') + '<br>');
        }
        if (remarketingConversionCode.val().trim() == '') {
            errors.push($.mage.__('Please specify the Conversion Code') + '<br>');
        }
        return errors;
    };

    return GA4API;
});
