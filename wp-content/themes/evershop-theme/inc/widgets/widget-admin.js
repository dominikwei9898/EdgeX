/* Widget 后台图片上传功能 */
jQuery(document).ready(function($) {
    // 绑定图片上传按钮点击事件
    $(document).on('click', '.evershop-upload-image', function(e) {
        e.preventDefault();
        var button = $(this);
        var targetId = button.data('target');
        
        // 创建 WordPress Media Uploader
        var mediaUploader = wp.media({
            title: 'Select Image',
            button: { text: 'Use this image' },
            multiple: false,
            library: {
                type: 'image' // 只显示图片
            }
        });
        
        // 选择图片后的回调
        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            var inputField = $('#' + targetId);
            
            // 设置图片 URL
            inputField.val(attachment.url);
            
            // 触发 change 事件，通知 WordPress Widget 已修改
            inputField.trigger('change');
            
            // 手动触发 Widget 保存状态更新（适用于 Customizer）
            if (typeof wp !== 'undefined' && wp.customize) {
                inputField.closest('.widget-content').find(':input').first().trigger('change');
            }
            
            // 为传统 Widgets 页面标记为已修改
            var widgetContainer = inputField.closest('.widget');
            if (widgetContainer.length) {
                widgetContainer.find('.widget-control-save').prop('disabled', false);
            }
        });
        
        mediaUploader.open();
    });
    
    // 监听 Customizer 中 Widget 的添加事件
    if (typeof wp !== 'undefined' && wp.customize && wp.customize.Widgets) {
        $(document).on('widget-added widget-updated', function(e, widget) {
            // 重新绑定事件（如果需要）
            console.log('Widget updated:', widget);
        });
    }
});
