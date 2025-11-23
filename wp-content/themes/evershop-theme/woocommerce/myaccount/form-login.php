<?php
/**
 * Login Form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/form-login.php.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 7.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

do_action( 'woocommerce_before_customer_login_form' ); ?>

<div class="cutler-login-wrapper" style="
    min-height: calc(100vh - 200px);
    background: linear-gradient(135deg, #0f0f0f 0%, #1a1a1a 50%, #0f0f0f 100%);
    padding: 4rem 0;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
">
    <!-- 背景装饰 -->
    <div style="
        position: absolute; 
        top: 0; left: 0; right: 0; bottom: 0; 
        background-image: 
            linear-gradient(45deg, rgba(255,255,255,0.02) 25%, transparent 25%, transparent 75%, rgba(255,255,255,0.02) 75%),
            linear-gradient(45deg, rgba(255,255,255,0.02) 25%, transparent 25%, transparent 75%, rgba(255,255,255,0.02) 75%);
        background-position: 0 0, 10px 10px;
        background-size: 20px 20px;
        pointer-events: none;
    ">
    </div>

    <div class="cutler-login-container" style="
        width: 100%;
        max-width: 480px; 
        margin: 0 auto; 
        padding: 0 1.5rem; 
        position: relative; 
        z-index: 1;
    ">
        
        <div class="login-form-section glass-panel">
            <!-- 装饰性光晕 -->
            <div class="glow-effect"></div>
            
            <div style="position: relative; z-index: 2;">
                
                <!-- 顶部切换标签 -->
                <div class="auth-tabs">
                    <button type="button" class="auth-tab active" data-target="login">
                        <?php esc_html_e( 'Login', 'woocommerce' ); ?>
                    </button>
                    <?php if ( 'yes' === get_option( 'woocommerce_enable_myaccount_registration' ) ) : ?>
                    <button type="button" class="auth-tab" data-target="register">
                        <?php esc_html_e( 'Register', 'woocommerce' ); ?>
                    </button>
                    <?php endif; ?>
                </div>
                
                <!-- 登录表单内容 -->
                <div id="cutler-login-form" class="auth-content">
                    <p style="text-align: center; color: #888; margin-bottom: 2rem; font-size: 0.95rem;">
                        <?php esc_html_e( 'Welcome back! Please login to your account.', 'woocommerce' ); ?>
                    </p>

                    <form class="woocommerce-form woocommerce-form-login login custom-no-border" method="post">

						<?php do_action( 'woocommerce_login_form_start' ); ?>

						<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide" style="margin-bottom: 1.5rem;">
							<label for="username" class="input-label">
                                <?php esc_html_e( 'Username or email address', 'woocommerce' ); ?>&nbsp;<span class="required">*</span>
                            </label>
							<input type="text" class="woocommerce-Input woocommerce-Input--text input-text modern-input" name="username" id="username" autocomplete="username" value="<?php echo ( ! empty( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>" /><?php // @codingStandardsIgnoreLine ?>
						</p>
						<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide" style="margin-bottom: 1.5rem;">
							<label for="password" class="input-label">
                                <?php esc_html_e( 'Password', 'woocommerce' ); ?>&nbsp;<span class="required">*</span>
                            </label>
							<input class="woocommerce-Input woocommerce-Input--text input-text modern-input" type="password" name="password" id="password" autocomplete="current-password" />
						</p>

						<?php do_action( 'woocommerce_login_form' ); ?>

						<p class="form-row login-actions-row">
							<label class="woocommerce-form__label woocommerce-form__label-for-checkbox woocommerce-form-login__rememberme remember-me-label">
								<input class="woocommerce-form__input woocommerce-form__input-checkbox" name="rememberme" type="checkbox" id="rememberme" value="forever" /> 
                                <span><?php esc_html_e( 'Remember me', 'woocommerce' ); ?></span>
							</label>
                            <a href="<?php echo esc_url( wp_lostpassword_url() ); ?>" class="lost-password-link">
                                <?php esc_html_e( 'Lost password?', 'woocommerce' ); ?>
                            </a>
						</p>

                        <div style="margin-top: 2rem;">
                            <button type="submit" class="woocommerce-button button woocommerce-form-login__submit cutler-login-button" name="login" value="<?php esc_attr_e( 'Log in', 'woocommerce' ); ?>">
                                <?php esc_html_e( 'Log in', 'woocommerce' ); ?>
                            </button>
                        </div>
                        
                        <?php wp_nonce_field( 'woocommerce-login', 'woocommerce-login-nonce' ); ?>
                        <input type="hidden" name="redirect" value="<?php echo isset($redirect) ? esc_url( $redirect ) : ''; ?>" />

						<?php do_action( 'woocommerce_login_form_end' ); ?>

					</form>
                </div>
                
                <!-- 注册表单内容 (默认隐藏) -->
                <?php if ( 'yes' === get_option( 'woocommerce_enable_myaccount_registration' ) ) : ?>
                <div id="cutler-register-form" class="auth-content" style="display: none;">
                    <p style="text-align: center; color: #888; margin-bottom: 2rem; font-size: 0.95rem;">
                        <?php esc_html_e( 'Create an account to enjoy personalized services.', 'woocommerce' ); ?>
                    </p>

                    <form method="post" class="woocommerce-form woocommerce-form-register register custom-no-border" <?php do_action( 'woocommerce_register_form_tag' ); ?> >

                        <?php do_action( 'woocommerce_register_form_start' ); ?>

                        <?php if ( 'no' === get_option( 'woocommerce_registration_generate_username' ) ) : ?>
                            <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide" style="margin-bottom: 1.5rem;">
                                <label for="reg_username" class="input-label"><?php esc_html_e( 'Username', 'woocommerce' ); ?>&nbsp;<span class="required">*</span></label>
                                <input type="text" class="woocommerce-Input woocommerce-Input--text input-text modern-input" name="username" id="reg_username" autocomplete="username" value="<?php echo ( ! empty( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>" /><?php // @codingStandardsIgnoreLine ?>
                            </p>
                        <?php endif; ?>

                        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide" style="margin-bottom: 1.5rem;">
                            <label for="reg_email" class="input-label"><?php esc_html_e( 'Email address', 'woocommerce' ); ?>&nbsp;<span class="required">*</span></label>
                            <input type="email" class="woocommerce-Input woocommerce-Input--text input-text modern-input" name="email" id="reg_email" autocomplete="email" value="<?php echo ( ! empty( $_POST['email'] ) ) ? esc_attr( wp_unslash( $_POST['email'] ) ) : ''; ?>" /><?php // @codingStandardsIgnoreLine ?>
                        </p>

                        <?php if ( 'no' === get_option( 'woocommerce_registration_generate_password' ) ) : ?>
                            <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide" style="margin-bottom: 1.5rem;">
                                <label for="reg_password" class="input-label"><?php esc_html_e( 'Password', 'woocommerce' ); ?>&nbsp;<span class="required">*</span></label>
                                <input type="password" class="woocommerce-Input woocommerce-Input--text input-text modern-input" name="password" id="reg_password" autocomplete="new-password" />
                            </p>
                        <?php else : ?>
                            <p style="color: #aaa; font-size: 0.9rem; margin-bottom: 1.5rem;"><?php esc_html_e( 'A link to set a new password will be sent to your email address.', 'woocommerce' ); ?></p>
                        <?php endif; ?>

                        <?php do_action( 'woocommerce_register_form' ); ?>

                        <div style="margin-top: 2rem;">
                            <button type="submit" class="woocommerce-Button woocommerce-button button woocommerce-form-register__submit cutler-login-button" name="register" value="<?php esc_attr_e( 'Register', 'woocommerce' ); ?>">
                                <?php esc_html_e( 'Register', 'woocommerce' ); ?>
                            </button>
                        </div>

                        <?php wp_nonce_field( 'woocommerce-register', 'woocommerce-register-nonce' ); ?>

                        <?php do_action( 'woocommerce_register_form_end' ); ?>

                    </form>
                </div>
                <?php endif; ?>
                
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Tab 切换功能
    $('.auth-tab').on('click', function(e) {
        e.preventDefault();
        var target = $(this).data('target');
        
        // 更新 Tab 状态
        $('.auth-tab').removeClass('active');
        $(this).addClass('active');
        
        // 更新内容显示
        $('.auth-content').hide(); // 直接隐藏，避免 fadeOut 导致的跳动
        if(target === 'login') {
            $('#cutler-login-form').fadeIn(300);
        } else {
            $('#cutler-register-form').fadeIn(300);
        }
    });
    
    // 如果 URL 包含 #register 或者是注册错误，自动切换到注册 Tab
    if (window.location.hash === '#register' || ($('.woocommerce-error').length > 0 && $('form.register').length > 0 && $('.woocommerce-error').prev('form').hasClass('register'))) {
         $('.auth-tab[data-target="register"]').click();
    }
});
</script>

<style>
/* 移除默认 Form 边框和内边距 */
form.custom-no-border {
    border: none !important;
    padding: 0 !important;
    margin: 0 !important;
    border-radius: 0 !important;
}

/* 玻璃拟态面板通用样式 */
.glass-panel {
    background: rgba(42, 42, 42, 0.9); 
    backdrop-filter: blur(20px); 
    border-radius: 24px; 
    padding: 2.5rem; 
    border: 1px solid rgba(255, 255, 255, 0.08); 
    box-shadow: 
        0 20px 60px rgba(0, 0, 0, 0.4),
        inset 0 0 0 1px rgba(255, 255, 255, 0.05); 
    position: relative; 
    overflow: hidden;
}

/* Tab 样式 */
.auth-tabs {
    display: flex;
    justify-content: center;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    margin-bottom: 2rem;
    padding-bottom: 0;
}

.auth-tab {
    background: transparent;
    border: none;
    color: #888;
    padding: 1rem 2rem;
    font-size: 1.1rem;
    font-weight: 600;
    cursor: pointer;
    border-bottom: 2px solid transparent;
    transition: all 0.3s ease;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: -1px; /* 让边框重叠 */
}

.auth-tab:hover {
    color: #ccc;
}

.auth-tab.active {
    color: #ffffff;
    border-bottom-color: #ff6b35;
}

/* 装饰性光晕 */
.glow-effect {
    position: absolute; 
    top: -50%; right: -20%; 
    width: 250px; height: 250px; 
    background: radial-gradient(circle, rgba(255, 107, 53, 0.08) 0%, transparent 70%); 
    border-radius: 50%;
    pointer-events: none;
}

/* 输入框样式优化 */
.input-label {
    color: #e0e0e0; 
    font-weight: 600; 
    margin-bottom: 10px; 
    display: block; 
    font-size: 0.85rem; 
    text-transform: uppercase; 
    letter-spacing: 1px;
}

.input-label .required {
    color: #ff6b35;
}

.modern-input {
    background: rgba(255, 255, 255, 0.08) !important; 
    border: 1px solid rgba(255, 255, 255, 0.1) !important;
    border-radius: 12px !important;
    color: #ffffff !important;
    padding: 16px 20px !important;
    font-size: 1rem !important;
    width: 100% !important;
    transition: all 0.3s ease !important; 
    backdrop-filter: blur(10px);
}

.modern-input:focus {
    border-color: #ff6b35 !important;
    outline: none !important;
    box-shadow: 0 0 0 4px rgba(255, 107, 53, 0.15) !important;
    background: rgba(255, 255, 255, 0.12) !important;
}

/* 登录操作行 */
.login-actions-row {
    display: flex; 
    justify-content: space-between; 
    align-items: center; 
    margin-bottom: 1.5rem; 
}

.remember-me-label {
    color: #bbb; 
    font-size: 0.95rem; 
    display: flex; 
    align-items: center; 
    gap: 10px; 
    cursor: pointer;
    padding: 5px 0;
    transition: color 0.2s ease;
}

.remember-me-label:hover {
    color: #fff;
}

.remember-me-label input[type="checkbox"] {
    accent-color: #ff6b35; 
    transform: scale(1.2);
    cursor: pointer;
}

/* 按钮 */
.cutler-login-button {
    background: linear-gradient(135deg, #ff6b35 0%, #e55a2b 100%) !important; 
    color: #ffffff !important; 
    border: none !important; 
    border-radius: 14px !important; 
    padding: 1.25rem 3rem !important; 
    font-weight: 700 !important; 
    font-size: 1.1rem !important; 
    text-transform: uppercase !important; 
    letter-spacing: 1.5px !important; 
    cursor: pointer !important; 
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1) !important; 
    width: 100% !important; 
    max-width: 100% !important; 
    box-shadow: 0 8px 20px rgba(255, 107, 53, 0.25) !important; 
    position: relative !important; 
    overflow: hidden !important; 
}

.cutler-login-button:hover {
    transform: translateY(-2px) !important;
    box-shadow: 0 12px 30px rgba(255, 107, 53, 0.4) !important;
    background: linear-gradient(135deg, #ff7645 0%, #f5501b 100%) !important;
}

/* 忘记密码链接 */
.lost-password-link {
    color: #888 !important; 
    text-decoration: none !important; 
    font-size: 0.9rem !important; 
    transition: all 0.3s ease !important;
    border-bottom: 1px solid transparent;
    padding-bottom: 2px;
}

.lost-password-link:hover {
    color: #ff6b35 !important;
    border-bottom-color: #ff6b35;
}

/* 移动端适配 */
@media (max-width: 767px) {
    .cutler-login-wrapper {
        padding: 2rem 0 !important;
        min-height: auto !important;
    }
    
    .cutler-login-container {
        padding: 0 1rem !important;
    }
    
    .glass-panel {
        padding: 2rem 1.5rem !important;
    }
    
    .auth-tab {
        padding: 1rem;
        font-size: 1rem;
    }
}

/* 全局辅助 */
.cutler-login-wrapper *:focus-visible {
    outline: 2px solid rgba(255, 107, 53, 0.5) !important;
    outline-offset: 2px !important;
}

.cutler-login-wrapper ::selection {
    background: rgba(255, 107, 53, 0.3) !important;
    color: #ffffff !important;
}
</style>

<?php do_action( 'woocommerce_after_customer_login_form' ); ?>
