/*!
 * Name: Bootstrap GDPR Cookies
 * Description: jQuery based plugin that shows bootstrap modal with cookie info
 * Version: v1.0
 *
 * Copyright (c) 2018 Aleksander Woźnica
 * Licensed under the MIT license: http://www.opensource.org/licenses/mit-license.php
 * Inspired by: https://github.com/ketanmistry/ihavecookies
 * Cookies Create/Read/Delete from https://www.quirksmode.org/js/cookies.html
 */

(function ($) {

    $.fn.bsgdprcookies = function (event) {

        var $element = $(this);
        var cookiePreferences = readCookie('CookiePreferences');
        if (readCookie('CookieShow') !== null) {
            deleteCookie('CookieShow');
        }

        // Set default settings
        var settings = {
            id: 'bs-gdpr-cookies-modal',
            class: '',
            title: 'Cookies & Privacy Policy',
            backdrop: 'static',
            message: "This site uses cookies in order to provide you with the best experience possible, provide social media " +
                "features, analyze our traffic, and personalize album and photo data.<br/>\n<br/>\n" +
                "Please click 'Accept' to accept this use of your data. Alternatively, you may click 'Customize' to accept (or " +
                "reject) specific categories of data processing.<br/>\n<br/>\n" +
                "For more information on how we process your personal data - or to update your preferences at any time - please " +
                "visit our ",
            messageScrollBar: false,
            messageMaxHeightPercent: 25,
            delay: 1500,
            expireDays: 365,
            moreLinkActive: true,
            moreLinkLabel: 'Privacy Policy',
            moreLinkNewTab: true,
            moreLink: '/Privacy-Policy.php',
            acceptButtonLabel: 'Accept',
            allowAdvancedOptions: true,
            advancedTitle: 'Select which cookies you want to accept',
            advancedButtonLabel: 'Customize',
            advancedCookiesToSelect: [
                {
                    name: 'necessary',
                    title: 'Necessary',
                    description: 'Required for the site to work properly',
                    isFixed: true
                },
                {
                    name: 'preferences',
                    title: 'Site Preferences',
                    description: 'Required for saving your site preferences, e.g. remembering your username etc.',
                },
                {
                    name: 'analytics',
                    title: 'Analytics',
                    description: 'Required to collect site visits, browser types, etc.',
                },
                {
                    name: 'social',
                    title: 'Social Media',
                    description: 'Required to load embedded social media buttons.',
                },
            ],
            OnAccept: function (cookies) {
                // Apply the preference immediately when consent is saved.
                if (cookies !== null && cookies.includes("preferences")) {
                    $('#profile-remember-span').show();
                    $('#login-remember-span').show();
                    $('#forgot-password-remember-span').show();
                } else {
                    $('#profile-remember-span, #login-remember-span, #forgot-password-remember-span').hide();
                    $('#profile-remember, #login-remember, #forgot-password-remember').prop('checked', false);
                }
            }
        };

        if (!cookiePreferences || event === 'reinit') {

            // Make sure that other instances are gone
            DisposeModal(settings.id);

            var modalBody = '';
            var modalButtons = '';
            var moreLink = '';

            // Generate more link
            if (settings.moreLinkActive == true) {
                if (settings.moreLinkNewTab == true) {
                    moreLink = '<a href="' + settings.moreLink + '" target="_blank" rel="noopener noreferrer" id="' + settings.id + '-more-link">' + settings.moreLinkLabel + '</a>';
                } else {
                    moreLink = '<a href="' + settings.moreLink + '" id="' + settings.id + '-more-link">' + settings.moreLinkLabel + '</a>';
                }
            }


            if (settings.allowAdvancedOptions === true) {
                modalButtons = '<button id="' + settings.id + '-advanced-btn" type="button" class="btn btn-secondary">' + settings.advancedButtonLabel + '</button><button id="' + settings.id + '-accept-btn" type="button" class="btn btn-primary">' + settings.acceptButtonLabel + '</button>';

                // Generate list of available advanced settings
                var advancedCookiesToSelectList = '';

                var preferences = ParsePreferences(cookiePreferences);
                $.each(settings.advancedCookiesToSelect, function (index, field) {
                    if (field.name !== '' && field.title !== '') {

                        var cookieDisabledText = '';
                        if (field.isFixed == true) {
                            cookieDisabledText = ' checked="checked" disabled="disabled"';
                        }

                        var cookieDescription = '';
                        if (field.description !== false) {
                            cookieDescription = ' title="' + field.description + '"';
                        }

                        var fieldID = settings.id + '-option-' + field.name;

                        advancedCookiesToSelectList += '<li><input type="checkbox" id="' + fieldID + '" name="bsgdpr[]" value="' + field.name + '" data-auto="on" ' + cookieDisabledText + '> <label name="bsgdpr[]" data-toggle="tooltip" data-placement="right" for="' + fieldID + '"' + cookieDescription + '>' + field.title + '</label></li>';
                    }
                });

                modalBody = '<div id="' + settings.id + '-message">' + settings.message + moreLink + '</div>' + '<div id="' + settings.id + '-advanced-types" class="consent-advanced-options"><h5 id="' + settings.id + '-advanced-title">' + settings.advancedTitle + '</h5><ul class="list-unstyled">' + advancedCookiesToSelectList + '</ul></div>';
            } else {
                modalButtons = '<button id="' + settings.id + '-accept-btn" type="button" class="btn btn-primary">' + settings.acceptButtonLabel + '</button>';

                modalBody = '<div id="' + settings.id + '-message">' + settings.message + moreLink + '</div>';
            }

            var modal = '<div class="modal fade ' + settings.class + '" id="' + settings.id + '" tabindex="-1" role="dialog" aria-labelledby="' + settings.id + '-title" aria-hidden="true"><div class="modal-dialog modal-dialog-centered" role="document"><div class="modal-content"><div class="modal-header"><h5 class="modal-title" id="' + settings.id + '-title">' + settings.title + '</h5></div><div id="' + settings.id + '-body" class="modal-body">' + modalBody + '</div><div class="modal-footer">' + modalButtons + '</div></div></div></div>';

            // Show Modal
            var showDelay = event === 'reinit' ? 0 : settings.delay;
            setTimeout(function () {
                $($element).append(modal);

                if (settings.messageScrollBar == true) {
                    $('#' + settings.id + '-body').css({
                        'overflow-y': 'scroll',
                        'max-height': settings.messageMaxHeightPercent + '%'
                    });
                }

                $('#' + settings.id).modal({keyboard: false, backdrop: settings.backdrop});

                if (event === 'reinit' && settings.allowAdvancedOptions === true) {
                    $('#' + settings.id + '-advanced-btn').trigger('click');
                    $.each(preferences, function (index, field) {
                        $('#' + settings.id + '-option-' + field).prop('checked', true);
                    });
                }
            }, showDelay);

            // When user clicks accept set cookie and close modal
            $('body').off('click.bsgdprcookies', '#' + settings.id + '-accept-btn')
                .on('click.bsgdprcookies', '#' + settings.id + '-accept-btn', function () {

                // If 'data-auto' is set to ON, tick all checkboxes because the user has not chosen any option
                $('input[name="bsgdpr[]"][data-auto="on"]').prop('checked', true);

                // Clear user preferences cookie
                deleteCookie('CookiePreferences');

                // Set user preferences cookie
                var preferences = [];
                $.each($('input[name="bsgdpr[]"]').serializeArray(), function (i, field) {
                    preferences.push(field.value);
                });
                createCookie('CookiePreferences', JSON.stringify(preferences), settings.expireDays);

                // Run callback function
                settings.OnAccept.call(this, preferences);
                $(document).trigger('cookiePreferencesChanged', [preferences]);
                DisposeModal(settings.id);
            });

            // Show advanced options
            $('body').off('click.bsgdprcookies', '#' + settings.id + '-advanced-btn')
                .on('click.bsgdprcookies', '#' + settings.id + '-advanced-btn', function () {
                // Uncheck all checkboxes except for the disabled ones
                $('input[name="bsgdpr[]"]:not(:disabled)').attr('data-auto', 'off').prop('checked', false);

                $('label[name="bsgdpr[]"]').tooltip({offset: '0, 10'});

                // Show advanced checkboxes
                $('#' + settings.id + '-advanced-types').slideDown('fast', function () {
                    $('#' + settings.id + '-advanced-btn').prop('disabled', true);
                });

                // Scroll content to bottom if scrollbar option is active
                if (settings.messageScrollBar == true) {
                    setTimeout(function () {
                        bodyID = settings.id + '-body';
                        var div = document.getElementById(bodyID);
                        $('#' + bodyID).animate({
                            scrollTop: div.scrollHeight - div.clientHeight
                        }, 800);
                    }, 500);
                }
            });
        } else {
            DisposeModal(settings.id);
        }
    }

    /**
     * Returns user preferences saved in cookie
     */
    $.fn.bsgdprcookies.GetUserPreferences = function () {
        var preferences = readCookie('CookiePreferences');
        return JSON.parse(preferences);
    };

    /**
     * Check if user preference exists in cookie
     *
     * @param {string} pref Preference to check
     */
    $.fn.bsgdprcookies.PreferenceExists = function (pref) {
        var preferences = $.fn.bsgdprcookies.GetUserPreferences();

        if (preferences === false || preferences.indexOf(pref) === -1) {
            return false;
        }

        return true;
    };


    /**
     * Hide then delete bs modal
     *
     * @param {string} id Modal ID without '#'
     */
    function DisposeModal(id) {
        var $modal = $('#' + id);
        if ($modal.length === 0) {
            return;
        }
        $modal.one('hidden.bs.modal', function () {
            $(this).removeData('bs.modal').remove();
        });
        $modal.modal('hide');
    }

    function ParsePreferences(cookiePreferences) {
        if (!cookiePreferences) {
            return [];
        }
        try {
            var preferences = JSON.parse(cookiePreferences);
            return Array.isArray(preferences) ? preferences : [];
        } catch (error) {
            // A malformed cookie represents no granted optional consent.
            return [];
        }
    }

}(jQuery));
