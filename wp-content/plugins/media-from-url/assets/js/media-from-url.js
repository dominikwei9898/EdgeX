jQuery(document).ready(function($) {
    
    // 创建模态框 HTML
    var modalHTML = `
        <div id="media-from-url-modal" class="media-from-url-modal" style="display:none;">
            <div class="media-from-url-modal-content">
                <div class="media-from-url-modal-header">
                    <h2>${mediaFromUrlData.strings.title}</h2>
                    <span class="media-from-url-close">&times;</span>
                </div>
                <div class="media-from-url-modal-body">
                    <input type="url" id="media-from-url-input" 
                           placeholder="${mediaFromUrlData.strings.placeholder}" 
                           class="media-from-url-input" />
                    <div class="media-from-url-preview" id="media-from-url-preview" style="display:none;"></div>
                    <div class="media-from-url-message" id="media-from-url-message"></div>
                </div>
                <div class="media-from-url-modal-footer">
                    <button type="button" class="button button-large" id="media-from-url-cancel">
                        ${mediaFromUrlData.strings.cancel}
                    </button>
                    <button type="button" class="button button-primary button-large" id="media-from-url-submit">
                        ${mediaFromUrlData.strings.button}
                    </button>
                </div>
            </div>
        </div>
    `;
    
    // 添加模态框到页面
    $('body').append(modalHTML);
    
    var $modal = $('#media-from-url-modal');
    var $input = $('#media-from-url-input');
    var $preview = $('#media-from-url-preview');
    var $message = $('#media-from-url-message');
    var $submitBtn = $('#media-from-url-submit');
    var $cancelBtn = $('#media-from-url-cancel');
    
    // 打开模态框函数
    function openModal(e) {
        e.preventDefault();
        e.stopPropagation();
        console.log('从 URL 添加按钮被点击');
        $modal.fadeIn(200);
        $input.val('').focus();
        $preview.hide().html('');
        $message.html('').removeClass('error success');
    }
    
    // 打开模态框（支持多个触发按钮）- 使用事件委托
    $(document).on('click', '#media-from-url-btn, #media-from-url-page-btn', openModal);
    
    // 针对 WordPress 媒体模态框的特殊处理
    // 监听媒体模态框的加载事件
    if (typeof wp !== 'undefined' && wp.media) {
        // WordPress 媒体库打开时
        $(document).on('click', '.media-modal-content #media-from-url-btn', openModal);
        
        // 使用 MutationObserver 监听动态添加的按钮
        var observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.addedNodes && mutation.addedNodes.length > 0) {
                    // 检查是否添加了我们的按钮
                    $(mutation.addedNodes).find('#media-from-url-btn').each(function() {
                        $(this).off('click').on('click', openModal);
                    });
                    // 检查添加的节点本身是否是按钮
                    $(mutation.addedNodes).filter('#media-from-url-btn').each(function() {
                        $(this).off('click').on('click', openModal);
                    });
                }
            });
        });
        
        // 开始观察 body 的变化
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }
    
    // 关闭模态框
    $('.media-from-url-close, #media-from-url-cancel').on('click', function() {
        $modal.fadeOut(200);
    });
    
    // 点击模态框外部关闭
    $(window).on('click', function(e) {
        if ($(e.target).is('#media-from-url-modal')) {
            $modal.fadeOut(200);
        }
    });
    
    // URL 输入预览
    $input.on('blur', function() {
        var url = $(this).val().trim();
        if (url && isValidUrl(url)) {
            showPreview(url);
        }
    });
    
    // 提交上传
    $submitBtn.on('click', function() {
        var url = $input.val().trim();
        
        if (!url) {
            showMessage(mediaFromUrlData.strings.invalid_url, 'error');
            return;
        }
        
        if (!isValidUrl(url)) {
            showMessage(mediaFromUrlData.strings.invalid_url, 'error');
            return;
        }
        
        uploadFromUrl(url);
    });
    
    // 验证 URL
    function isValidUrl(string) {
        try {
            new URL(string);
            return true;
        } catch (_) {
            return false;
        }
    }
    
    // 显示预览
    function showPreview(url) {
        var ext = url.split('.').pop().toLowerCase().split('?')[0];
        var imageExts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
        var videoExts = ['mp4', 'webm', 'ogg'];
        
        $preview.html('');
        
        if (imageExts.includes(ext)) {
            $preview.html('<img src="' + url + '" style="max-width: 100%; height: auto;" />');
            $preview.fadeIn(200);
        } else if (videoExts.includes(ext)) {
            $preview.html('<video src="' + url + '" controls style="max-width: 100%; height: auto;"></video>');
            $preview.fadeIn(200);
        }
    }
    
    // 显示消息
    function showMessage(message, type) {
        $message.html(message)
                .removeClass('error success')
                .addClass(type)
                .fadeIn(200);
        
        if (type === 'success') {
            setTimeout(function() {
                $message.fadeOut(200);
            }, 3000);
        }
    }
    
    // 刷新媒体库
    function refreshMediaLibrary(uploadData) {
        console.log('开始刷新媒体库...');
        
        // 方法 1: 如果在 WordPress 媒体模态框中
        if (typeof wp !== 'undefined' && wp.media && wp.media.frame) {
            console.log('检测到 WordPress 媒体模态框');
            
            // 尝试多种刷新方法
            var refreshed = false;
            
            // 方法 1.1: 刷新当前内容视图
            try {
                var frame = wp.media.frame;
                var library = frame.state().get('library');
                
                if (library) {
                    console.log('使用方法 1.1: 刷新 library');
                    // 添加新上传的附件到集合
                    var attachment = wp.media.attachment(uploadData.id);
                    attachment.fetch().done(function() {
                        library.add(attachment);
                        refreshed = true;
                        console.log('媒体库刷新成功 - 添加了新附件');
                    });
                }
            } catch (e) {
                console.log('方法 1.1 失败:', e);
            }
            
            // 方法 1.2: 强制重新获取
            if (!refreshed) {
                try {
                    var content = wp.media.frame.content.get();
                    if (content && content.collection) {
                        console.log('使用方法 1.2: 强制重新获取');
                        content.collection.props.set({ignore: (+ new Date())});
                        content.options.selection.reset();
                        refreshed = true;
                        console.log('媒体库刷新成功 - 强制重新获取');
                    }
                } catch (e) {
                    console.log('方法 1.2 失败:', e);
                }
            }
            
            // 方法 1.3: 直接操作 library props
            if (!refreshed) {
                try {
                    if (wp.media.frame.library) {
                        console.log('使用方法 1.3: 操作 library props');
                        wp.media.frame.library.props.set({ignore: (+ new Date())});
                        refreshed = true;
                        console.log('媒体库刷新成功 - library props');
                    }
                } catch (e) {
                    console.log('方法 1.3 失败:', e);
                }
            }
            
            // 如果所有方法都失败，给出提示但不重新加载页面
            if (!refreshed) {
                console.log('所有刷新方法失败，提示用户手动刷新');
                showMessage('上传成功！请手动刷新媒体库查看新文件', 'success');
            }
        }
        // 方法 2: 如果在媒体库列表页面
        else if (window.location.href.indexOf('upload.php') > -1 || 
                 window.location.href.indexOf('media-new.php') > -1) {
            console.log('检测到媒体库列表页面，即将刷新页面');
            // 直接刷新页面以显示新上传的媒体
            setTimeout(function() {
                location.reload();
            }, 500);
        }
        // 方法 3: 其他页面（可能是文章编辑器）
        else {
            console.log('其他页面环境');
            // 尝试触发自定义事件，让其他脚本知道有新媒体上传
            $(document).trigger('mediaFromUrlUploaded', [uploadData]);
            
            // 如果页面上有 wp.media，尝试刷新其缓存
            if (typeof wp !== 'undefined' && wp.media) {
                try {
                    // 清除 wp.media 的附件缓存
                    if (wp.media.model && wp.media.model.Attachments) {
                        console.log('清除附件缓存');
                        var attachment = wp.media.attachment(uploadData.id);
                        attachment.fetch();
                    }
                } catch (e) {
                    console.log('清除缓存失败:', e);
                }
            }
        }
    }
    
    // 上传文件
    function uploadFromUrl(url) {
        $submitBtn.prop('disabled', true).text(mediaFromUrlData.strings.uploading);
        showMessage('', '');
        
        $.ajax({
            url: mediaFromUrlData.ajaxurl,
            type: 'POST',
            data: {
                action: 'upload_from_url',
                url: url,
                nonce: mediaFromUrlData.nonce
            },
            success: function(response) {
                if (response.success) {
                    showMessage(mediaFromUrlData.strings.success, 'success');
                    
                    console.log('上传成功:', response.data);
                    
                    // 延迟关闭模态框，让用户看到成功消息
                    setTimeout(function() {
                        $modal.fadeOut(200);
                        refreshMediaLibrary(response.data);
                    }, 800);
                } else {
                    showMessage(response.data.message || mediaFromUrlData.strings.error, 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('上传失败:', error);
                showMessage(mediaFromUrlData.strings.error, 'error');
            },
            complete: function() {
                $submitBtn.prop('disabled', false).text(mediaFromUrlData.strings.button);
            }
        });
    }
    
    // ESC 键关闭模态框
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape' && $modal.is(':visible')) {
            $modal.fadeOut(200);
        }
    });
    
    // 回车提交
    $input.on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            $submitBtn.click();
        }
    });
});

