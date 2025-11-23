/**
 * Product Variant Display
 * 动态更新产品变体显示（例如当前选中的 Flavor）
 */

jQuery(document).ready(function($) {
    
    /**
     * 更新当前选中的 Flavor 显示
     */
    function updateSelectedFlavorDisplay() {
        // 查找变体选择器
        const $variantSelect = $('.variations select[name^="attribute_"]');
    
        if ($variantSelect.length === 0) {
        return;
    }
    
        // 获取当前选中的值
        const selectedValue = $variantSelect.val();
        const selectedText = $variantSelect.find('option:selected').text();
    
        // 更新显示的 Flavor 文本
        const $flavorDisplay = $('.selected-flavor-text');
        
        if ($flavorDisplay.length > 0) {
            if (selectedValue && selectedText && selectedText !== 'Choose an option') {
                $flavorDisplay.text(selectedText);
                $flavorDisplay.closest('.current-flavor-wrapper').show();
        } else {
            $flavorDisplay.text('-');
        }
        }
    }
    
    /**
     * 监听变体选择器的变化
     */
    $('.variations select').on('change', function() {
        updateSelectedFlavorDisplay();
    });
    
    /**
     * 页面加载时初始化显示
     */
    updateSelectedFlavorDisplay();
    
    /**
     * WooCommerce 变体表单更新后
     */
    $('form.variations_form').on('found_variation', function(event, variation) {
        // 当找到有效的变体时，确保 Flavor 显示更新
        setTimeout(function() {
            updateSelectedFlavorDisplay();
        }, 100);
    });
    
    /**
     * 重置变体时
     */
    $('.reset_variations').on('click', function() {
        setTimeout(function() {
            $('.selected-flavor-text').text('-');
        }, 100);
});

});
