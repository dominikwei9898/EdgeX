<?php
/**
 * Cookie Consent Banner Template Part
 */
?>
<div id="cookie-consent-banner" class="cookie-consent-banner">
    <div class="cookie-consent-wrapper">
        <div class="cookie-consent-content">
            <p>
                <?php 
                printf(
                    __('We use cookies to improve your experience. By continuing to use this site, you agree to our %s.', 'evershop-theme'),
                    '<a href="/privacy-policy" target="_blank">' . __('Privacy Policy', 'evershop-theme') . '</a>'
                ); 
                ?>
            </p>
        </div>
        <div class="cookie-consent-actions">
            <button id="cookie-reject" class="cookie-btn cookie-btn-reject">
                <?php _e('Decline', 'evershop-theme'); ?>
            </button>
            <button id="cookie-accept" class="cookie-btn cookie-btn-accept">
                <?php _e('Accept All', 'evershop-theme'); ?>
            </button>
        </div>
    </div>
</div>
