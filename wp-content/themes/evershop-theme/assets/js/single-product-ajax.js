jQuery(document).ready(function($) {
    /**
     * 单个产品页面的 AJAX Add to Cart
     * 拦截默认表单提交，使用 AJAX 添加，然后打开 Mini Cart
     */
    $('form.cart').on('submit', function(e) {
        var $form = $(this);
        
        // 如果是 simple 或 variable 产品，并且有 add-to-cart 按钮
        if ($form.find('.single_add_to_cart_button').length > 0) {
            e.preventDefault();
            
            var $thisbutton = $form.find('.single_add_to_cart_button');
            var product_id = $form.find('input[name="product_id"]').val() || $thisbutton.val();
            var quantity = $form.find('input[name="quantity"]').val() || 1;
            var variation_id = $form.find('input[name="variation_id"]').val() || 0;
            
            // 禁用按钮防止重复提交
            $thisbutton.prop('disabled', true).addClass('loading');
            $thisbutton.text('Adding...'); // 可选：更改文字
            
            // 收集表单数据
            var data = {
                action: 'woocommerce_ajax_add_to_cart',
                product_id: product_id,
                product_sku: '',
                quantity: quantity,
                variation_id: variation_id,
            };
            
            // 收集表单数据
            // 注意：$form.serialize() 不包含提交按钮的 value (add-to-cart ID)
            // 对于 Simple Product，必须手动添加 add-to-cart 参数
            var formData = $form.serialize();
            
            if (formData.indexOf('add-to-cart=') === -1) {
                formData += '&add-to-cart=' + product_id;
            }
            
            // 触发自定义事件
            $(document.body).trigger('adding_to_cart', [$thisbutton, data]);
            
            // AJAX 请求
            $.ajax({
                type: 'POST',
                url: wc_add_to_cart_params.wc_ajax_url.toString().replace('%%endpoint%%', 'add_to_cart'),
                data: formData, 
                success: function(response) {
                    $thisbutton.removeClass('loading').prop('disabled', false);
                    $thisbutton.text('ADD TO BASKET'); // 恢复文字
                    
                    if (response.error & response.product_url) {
                        window.location = response.product_url;
                        return;
                    }
                    
                    // 触发 fragment 刷新
                    $(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash, $thisbutton]);
                    
                    // 打开 Mini Cart
                    // 假设 Mini Cart 有一个打开的方法，或者我们触发 toggle 按钮的点击
                    var $miniCartTrigger = $('#minicart-trigger');
                    if ($miniCartTrigger.length > 0) {
                        $miniCartTrigger.trigger('click');
                    }
                },
                error: function(error) {
                    console.log(error);
                    $thisbutton.removeClass('loading').prop('disabled', false);
                }
            });
        }
    });
});

