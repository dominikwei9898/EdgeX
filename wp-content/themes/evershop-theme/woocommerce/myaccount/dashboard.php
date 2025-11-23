<?php
/**
 * My Account Dashboard
 *
 * Shows the first intro screen on the account dashboard.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/dashboard.php.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 4.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$allowed_html = array(
	'a' => array(
		'href' => array(),
	),
);
?>

<div class="dashboard-wrapper" style="width: 100%;">
    
    <!-- 欢迎区域 -->
    <div class="dashboard-welcome-section" style="
        background: rgba(42, 42, 42, 0.6); 
        backdrop-filter: blur(15px); 
        border-radius: 20px; 
        padding: 2rem; 
        margin-bottom: 2rem; 
        border: 1px solid rgba(255, 255, 255, 0.08); 
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3); 
        position: relative;
        overflow: hidden;
    ">
        <!-- 装饰性背景 -->
        <div style="
            position: absolute; 
            top: -30%; right: -15%; 
            width: 150px; height: 150px; 
            background: radial-gradient(circle, rgba(255, 107, 53, 0.1) 0%, transparent 70%); 
            border-radius: 50%;
        "></div>
        
        <div style="position: relative; z-index: 2;">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                <div>
                    <h2 style="
                        color: #ffffff; 
                        font-size: 1.8rem; 
                        font-weight: 600; 
                        margin: 0 0 0.5rem 0; 
                        letter-spacing: -0.5px;
                    ">
                        <?php
                        $current_user = wp_get_current_user();
                        /* translators: 1: user display name */
                        printf( esc_html__( 'Hello %1$s', 'woocommerce' ), '<span style="color: #ff6b35;">' . esc_html( $current_user->display_name ) . '</span>' );
                        ?>
                    </h2>
                    <p style="
                        color: #888; 
                        margin: 0; 
                        font-size: 1rem;
                    ">
                        <?php esc_html_e( 'Welcome back to your account dashboard', 'woocommerce' ); ?>
                    </p>
                </div>
                
                <div style="display: flex; gap: 1rem; align-items: center;">
                    <!-- <div style="text-align: center;">
                        <p style="color: #888; font-size: 0.8rem; margin: 0; text-transform: uppercase; letter-spacing: 1px;"><?php esc_html_e( 'Membership', 'woocommerce' ); ?></p>
                        <p style="color: #ff6b35; font-weight: 700; font-size: 1.1rem; margin: 0;">VIP</p>
                    </div> -->
                    <a href="<?php echo esc_url( wc_logout_url() ); ?>" style="
                        background: transparent; 
                        color: #888; 
                        border: 2px solid #444; 
                        border-radius: 12px; 
                        padding: 8px 16px; 
                        font-weight: 600; 
                        text-decoration: none; 
                        transition: all 0.3s ease; 
                        font-size: 0.9rem;
                        text-transform: uppercase;
                        letter-spacing: 0.5px;
                    ">
                        <?php esc_html_e( 'Log out', 'woocommerce' ); ?>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 快速统计区域 -->
    <div class="dashboard-stats-grid" style="
        display: grid; 
        grid-template-columns: repeat(auto-fit, minmax(175px, 1fr)); 
        gap: 1rem; 
        margin-bottom: 2rem;
    ">
        <?php
        // 获取用户订单统计
        $customer_orders = wc_get_orders( array(
            'customer' => get_current_user_id(),
            'limit'    => -1,
        ) );
        
        $total_orders = count( $customer_orders );
        $pending_orders = 0;
        $completed_orders = 0;
        $total_spent = 0;
        
        foreach ( $customer_orders as $order ) {
            if ( $order->get_status() === 'pending' ) {
                $pending_orders++;
            } elseif ( $order->get_status() === 'completed' ) {
                $completed_orders++;
            }
            $total_spent += $order->get_total();
        }
        ?>
        
        <!-- 总订单数 -->
        <div class="stat-card" style="
            background: rgba(42, 42, 42, 0.9); 
            backdrop-filter: blur(20px); 
            border-radius: 16px; 
            padding: 1.25rem; 
            border: 1px solid rgba(255, 255, 255, 0.1); 
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3); 
            text-align: center;
            transition: all 0.3s ease;
        ">
            <div style="
                width: 60px; 
                height: 60px; 
                background: linear-gradient(135deg, #ff6b35 0%, #e55a2b 100%); 
                border-radius: 50%; 
                display: flex; 
                align-items: center; 
                justify-content: center; 
                margin: 0 auto 1rem auto;
            ">
                <svg width="24" height="24" fill="#ffffff" viewBox="0 0 24 24">
                    <path d="M7 4V2C7 1.45 7.45 1 8 1H16C16.55 1 17 1.45 17 2V4H20C20.55 4 21 4.45 21 5S20.55 6 20 6H19V19C19 20.1 18.1 21 17 21H7C5.9 21 5 20.1 5 19V6H4C3.45 6 3 5.55 3 5S3.45 4 4 4H7ZM9 3V4H15V3H9ZM7 6V19H17V6H7Z"/>
                </svg>
            </div>
            <h3 style="color: #ffffff; font-size: 2rem; font-weight: 700; margin: 0 0 0.5rem 0;"><?php echo esc_html( $total_orders ); ?></h3>
            <p style="color: #888; margin: 0; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.5px;"><?php esc_html_e( 'Total Orders', 'woocommerce' ); ?></p>
        </div>
        
        <!-- 待处理订单 -->
        <div class="stat-card" style="
            background: rgba(42, 42, 42, 0.9); 
            backdrop-filter: blur(20px); 
            border-radius: 16px; 
            padding: 1.25rem; 
            border: 1px solid rgba(255, 255, 255, 0.1); 
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3); 
            text-align: center;
            transition: all 0.3s ease;
        ">
            <div style="
                width: 60px; 
                height: 60px; 
                background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%); 
                border-radius: 50%; 
                display: flex; 
                align-items: center; 
                justify-content: center; 
                margin: 0 auto 1rem auto;
            ">
                <svg width="24" height="24" fill="#ffffff" viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12S6.48 22 12 22 22 17.52 22 12 17.52 2 12 2ZM13 17H11V15H13V17ZM13 13H11V7H13V13Z"/>
                </svg>
            </div>
            <h3 style="color: #ffffff; font-size: 2rem; font-weight: 700; margin: 0 0 0.5rem 0;"><?php echo esc_html( $pending_orders ); ?></h3>
            <p style="color: #888; margin: 0; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.5px;"><?php esc_html_e( 'Pending', 'woocommerce' ); ?></p>
        </div>
        
        <!-- 总消费 -->
        <div class="stat-card" style="
            background: rgba(42, 42, 42, 0.9); 
            backdrop-filter: blur(20px); 
            border-radius: 16px; 
            padding: 1.25rem; 
            border: 1px solid rgba(255, 255, 255, 0.1); 
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3); 
            text-align: center;
            transition: all 0.3s ease;
        ">
            <div style="
                width: 60px; 
                height: 60px; 
                background: linear-gradient(135deg, #10b981 0%, #059669 100%); 
                border-radius: 50%; 
                display: flex; 
                align-items: center; 
                justify-content: center; 
                margin: 0 auto 1rem auto;
            ">
                <svg width="24" height="24" fill="#ffffff" viewBox="0 0 24 24">
                    <path d="M11.8 10.9C9.53 10.31 8.8 9.7 8.8 8.75C8.8 7.66 9.81 6.9 11.5 6.9C13.28 6.9 13.94 7.75 14 9H16.21C16.14 7.28 15.09 5.7 13 5.19V3H10V5.16C8.06 5.58 6.5 6.84 6.5 8.77C6.5 11.08 8.41 12.23 11.2 12.9C13.7 13.5 14.2 14.38 14.2 15.31C14.2 16 13.71 17.1 11.5 17.1C9.44 17.1 8.63 16.18 8.52 15H6.32C6.44 17.19 8.08 18.42 10 18.83V21H13V18.85C14.95 18.5 16.5 17.35 16.5 15.3C16.5 12.46 14.07 11.5 11.8 10.9Z"/>
                </svg>
            </div>
            <h3 style="color: #ffffff; font-size: 1.6rem; font-weight: 700; margin: 0 0 0.5rem 0;"><?php echo wc_price( $total_spent ); ?></h3>
            <p style="color: #888; margin: 0; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.5px;"><?php esc_html_e( 'Total Spent', 'woocommerce' ); ?></p>
        </div>
    </div>

</div>

<style>
/* 统计卡片悬停效果 */
.stat-card:hover {
    transform: translateY(-4px) !important;
    box-shadow: 0 12px 48px rgba(0, 0, 0, 0.4) !important;
    border-color: rgba(255, 107, 53, 0.3) !important;
}

/* 响应式设计 */
@media (min-width: 768px) {
    .dashboard-content-grid {
        grid-template-columns: 2fr 1fr !important;
    }
}

@media (max-width: 767px) {
    .dashboard-stats-grid {
        grid-template-columns: 1fr !important;
    }
    
    .dashboard-welcome-section > div > div {
        flex-direction: column !important;
        text-align: center !important;
    }
    
    .recent-orders-section,
    .quick-actions-section {
        padding: 1.5rem !important;
    }
}

@media (max-width: 480px) {
    .dashboard-welcome-section {
        padding: 1.5rem !important;
    }
    
    .stat-card {
        padding: 1.25rem !important;
    }
}
</style>

<?php
/**
 * My Account dashboard.
 *
 * @since 2.6.0
 */
do_action( 'woocommerce_account_dashboard' );

/**
 * Deprecated woocommerce_before_my_account action.
 *
 * @deprecated 2.6.0
 */
do_action( 'woocommerce_before_my_account' );

/**
 * Deprecated woocommerce_after_my_account action.
 *
 * @deprecated 2.6.0
 */
do_action( 'woocommerce_after_my_account' );

/* Omit closing PHP tag at the end of PHP files to avoid "headers already sent" issues. */
