(function($) {
    'use strict';

    var COOKIE_NAME = 'evershop_cookie_consent';
    var COOKIE_EXPIRY = 365; // Days

    function setCookie(name, value, days) {
        var expires = "";
        if (days) {
            var date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            expires = "; expires=" + date.toUTCString();
        }
        document.cookie = name + "=" + (value || "") + expires + "; path=/; SameSite=Lax";
    }

    function getCookie(name) {
        var nameEQ = name + "=";
        var ca = document.cookie.split(';');
        for(var i=0;i < ca.length;i++) {
            var c = ca[i];
            while (c.charAt(0)==' ') c = c.substring(1,c.length);
            if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
        }
        return null;
    }

    $(document).ready(function() {
        var $banner = $('#cookie-consent-banner');
        var consent = getCookie(COOKIE_NAME);

        if (!consent) {
            // Delay showing slightly for better UX
            setTimeout(function() {
                $banner.addClass('show');
            }, 1000);
        }

        $('#cookie-accept').on('click', function() {
            setCookie(COOKIE_NAME, 'accepted', COOKIE_EXPIRY);
            $banner.removeClass('show');
            // Here you can initialize tracking scripts if needed
            $(document.body).trigger('cookie_consent_accepted');
        });

        $('#cookie-reject').on('click', function() {
            setCookie(COOKIE_NAME, 'rejected', COOKIE_EXPIRY);
            $banner.removeClass('show');
            // Here you might want to ensure tracking is disabled
            $(document.body).trigger('cookie_consent_rejected');
        });
    });

})(jQuery);

