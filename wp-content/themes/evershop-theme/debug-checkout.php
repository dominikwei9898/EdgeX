<?php
/**
 * Template Name: Debug Checkout
 * 
 * 访问任意页面，在 URL 后添加 ?debug_checkout=1
 */

// 只在有 debug_checkout 参数时执行
if (!isset($_GET['debug_checkout'])) {
    return;
}

// 必须登录或有购物车
if (!is_user_logged_in() && (!WC()->cart || WC()->cart->is_empty())) {
    return;
}

add_action('wp_footer', function() {
    if (!isset($_GET['debug_checkout'])) {
        return;
    }
    ?>
    <div style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.95); color: #fff; padding: 20px; overflow: auto; z-index: 999999; font-family: monospace; font-size: 12px;">
        <button onclick="this.parentElement.remove()" style="position: fixed; top: 10px; right: 10px; padding: 10px 20px; background: #c00; color: #fff; border: none; cursor: pointer; border-radius: 4px; font-size: 14px;">关闭</button>
        
        <h1 style="color: #0f0; margin-bottom: 20px;">🔍 Checkout 调试信息</h1>
        
        <?php
        echo '<div style="background: #222; padding: 15px; margin: 10px 0; border-left: 3px solid #0f0;">';
        echo '<h2 style="color: #0ff; margin-top: 0;">1. 购物车状态</h2>';
        
        if (WC()->cart) {
            echo '<p>✅ 购物车对象存在</p>';
            echo '<p>购物车是否为空: ' . (WC()->cart->is_empty() ? '<span style="color:#f00;">是</span>' : '<span style="color:#0f0;">否</span>') . '</p>';
            echo '<p>购物车商品数: ' . WC()->cart->get_cart_contents_count() . '</p>';
            echo '<p>购物车总额: ' . WC()->cart->get_total('') . '</p>';
            echo '<p>需要支付: ' . (WC()->cart->needs_payment() ? '<span style="color:#0f0;">是</span>' : '<span style="color:#f00;">否</span>') . '</p>';
        } else {
            echo '<p style="color:#f00;">❌ 购物车对象不存在</p>';
        }
        echo '</div>';
        
        echo '<div style="background: #222; padding: 15px; margin: 10px 0; border-left: 3px solid #ff0;">';
        echo '<h2 style="color: #0ff; margin-top: 0;">2. 支付网关检查</h2>';
        
        if (WC()->payment_gateways()) {
            $all_gateways = WC()->payment_gateways()->payment_gateways();
            echo '<p>✅ 支付网关管理器存在</p>';
            echo '<p>已注册的支付网关数量: ' . count($all_gateways) . '</p>';
            
            echo '<h3 style="color: #ff0;">所有已注册的支付网关：</h3>';
            echo '<table style="width:100%; border-collapse: collapse; background: #333; margin: 10px 0;">';
            echo '<tr style="background: #444;"><th style="padding:8px; text-align:left; border:1px solid #555;">ID</th><th style="padding:8px; text-align:left; border:1px solid #555;">标题</th><th style="padding:8px; text-align:left; border:1px solid #555;">启用</th><th style="padding:8px; text-align:left; border:1px solid #555;">is_available()</th></tr>';
            
            foreach ($all_gateways as $gateway_id => $gateway) {
                $enabled = $gateway->enabled === 'yes' ? '✅ 是' : '❌ 否';
                $is_available = $gateway->is_available() ? '✅ true' : '❌ false';
                echo '<tr>';
                echo '<td style="padding:8px; border:1px solid #555;">' . esc_html($gateway_id) . '</td>';
                echo '<td style="padding:8px; border:1px solid #555;">' . esc_html($gateway->get_title()) . '</td>';
                echo '<td style="padding:8px; border:1px solid #555;">' . $enabled . '</td>';
                echo '<td style="padding:8px; border:1px solid #555;">' . $is_available . '</td>';
                echo '</tr>';
            }
            echo '</table>';
            
            echo '<h3 style="color: #0f0;">get_available_payment_gateways() 结果：</h3>';
            $available = WC()->payment_gateways()->get_available_payment_gateways();
            
            if (empty($available)) {
                echo '<p style="color:#f00; font-size:16px;">❌ get_available_payment_gateways() 返回空数组！</p>';
                
                // 检查可能的原因
                echo '<h4 style="color:#ff0;">可能的原因：</h4>';
                echo '<ul style="margin-left: 20px;">';
                
                if (!WC()->cart || WC()->cart->is_empty()) {
                    echo '<li style="color:#f00;">❌ 购物车为空</li>';
                }
                
                if (WC()->cart && !WC()->cart->needs_payment()) {
                    echo '<li style="color:#f00;">❌ 购物车不需要支付（总额可能为0）</li>';
                }
                
                $has_enabled = false;
                foreach ($all_gateways as $gateway) {
                    if ($gateway->enabled === 'yes') {
                        $has_enabled = true;
                        break;
                    }
                }
                if (!$has_enabled) {
                    echo '<li style="color:#f00;">❌ 没有启用的支付网关</li>';
                }
                
                $has_available = false;
                foreach ($all_gateways as $gateway) {
                    if ($gateway->is_available()) {
                        $has_available = true;
                        break;
                    }
                }
                if (!$has_available) {
                    echo '<li style="color:#f00;">❌ 所有支付网关的 is_available() 都返回 false</li>';
                }
                
                echo '</ul>';
            } else {
                echo '<p style="color:#0f0; font-size:16px;">✅ 找到 ' . count($available) . ' 个可用支付网关</p>';
                echo '<table style="width:100%; border-collapse: collapse; background: #333; margin: 10px 0;">';
                echo '<tr style="background: #444;"><th style="padding:8px; text-align:left; border:1px solid #555;">ID</th><th style="padding:8px; text-align:left; border:1px solid #555;">标题</th></tr>';
                foreach ($available as $gateway_id => $gateway) {
                    echo '<tr>';
                    echo '<td style="padding:8px; border:1px solid #555;">' . esc_html($gateway_id) . '</td>';
                    echo '<td style="padding:8px; border:1px solid #555;">' . esc_html($gateway->get_title()) . '</td>';
                    echo '</tr>';
                }
                echo '</table>';
            }
        } else {
            echo '<p style="color:#f00;">❌ 支付网关管理器不存在</p>';
        }
        echo '</div>';
        
        echo '<div style="background: #222; padding: 15px; margin: 10px 0; border-left: 3px solid #f0f;">';
        echo '<h2 style="color: #0ff; margin-top: 0;">3. Cajipay 配置详情</h2>';
        
        if (class_exists('CajipayWCPaymentGateway')) {
            $cajipay = new CajipayWCPaymentGateway();
            echo '<table style="width:100%; border-collapse: collapse; background: #333; margin: 10px 0;">';
            echo '<tr style="background: #444;"><th style="padding:8px; text-align:left; border:1px solid #555;">配置项</th><th style="padding:8px; text-align:left; border:1px solid #555;">值</th></tr>';
            
            $configs = [
                'enabled' => 'enabled',
                'title' => 'title',
                'cajipay_username' => 'Username',
                'cajipay_key' => 'API Key',
                'cajipay_gatewayUrl' => 'Gateway URL'
            ];
            
            foreach ($configs as $key => $label) {
                $value = $cajipay->get_option($key);
                if ($key === 'cajipay_key' && $value) {
                    $value = '***' . substr($value, -4);
                }
                if ($key === 'cajipay_username' && $value) {
                    $value = '***' . substr($value, -4);
                }
                $status = empty($value) ? '❌' : '✅';
                echo '<tr>';
                echo '<td style="padding:8px; border:1px solid #555;">' . esc_html($label) . '</td>';
                echo '<td style="padding:8px; border:1px solid #555;">' . $status . ' ' . esc_html($value ? $value : '未设置') . '</td>';
                echo '</tr>';
            }
            echo '</table>';
            
            echo '<p>is_available() 返回: ' . ($cajipay->is_available() ? '<span style="color:#0f0;">✅ true</span>' : '<span style="color:#f00;">❌ false</span>') . '</p>';
        } else {
            echo '<p style="color:#f00;">❌ CajipayWCPaymentGateway 类不存在</p>';
        }
        echo '</div>';
        
        echo '<div style="background: #222; padding: 15px; margin: 10px 0; border-left: 3px solid #0ff;">';
        echo '<h2 style="color: #0ff; margin-top: 0;">4. 模拟 payment.php 模板逻辑</h2>';
        
        if (WC()->cart && WC()->cart->needs_payment()) {
            echo '<p>✅ WC()->cart->needs_payment() = true</p>';
            $available_gateways = WC()->payment_gateways()->get_available_payment_gateways();
            
            if (!empty($available_gateways)) {
                echo '<p style="color:#0f0; font-size:16px;">✅ $available_gateways 不为空，应该显示支付方法</p>';
            } else {
                echo '<p style="color:#f00; font-size:16px;">❌ $available_gateways 为空，会显示错误消息</p>';
            }
        } else {
            echo '<p style="color:#f00;">❌ WC()->cart->needs_payment() = false</p>';
            echo '<p>这意味着购物车不需要支付（可能总额为0或购物车为空）</p>';
        }
        echo '</div>';
        
        echo '<div style="background: #222; padding: 15px; margin: 10px 0; border-left: 3px solid #f90;">';
        echo '<h2 style="color: #0ff; margin-top: 0;">5. 建议操作</h2>';
        echo '<ol style="margin-left: 20px; line-height: 1.8;">';
        echo '<li>确保购物车中有商品</li>';
        echo '<li>确保商品总额大于 0</li>';
        echo '<li>在 WooCommerce > 设置 > 付款 中启用超集支付</li>';
        echo '<li>填写所有必需的配置字段</li>';
        echo '<li>清除所有缓存</li>';
        echo '<li>刷新页面</li>';
        echo '</ol>';
        echo '</div>';
        ?>
    </div>
    <?php
}, 999);

