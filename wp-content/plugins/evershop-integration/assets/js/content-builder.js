/**
 * EdgeX Content Builder - Admin Interface
 * 
 * 管理产品内容模块的添加、编辑、排序和删除
 */

(function($) {
    'use strict';
    
    let contentBlocks = [];
    let currentEditingIndex = null;
    let blockTypesConfig = {};
    
    // 初始化
    $(document).ready(function() {
        console.log('[EdgeX Content Builder] 开始初始化...');
        initContentBuilder();
    });
    
    function initContentBuilder() {
        console.log('[EdgeX] initContentBuilder 被调用');
        
        // 加载内容块配置
        const configElement = $('#edgex-block-types-config');
        console.log('[EdgeX] 配置元素:', configElement.length);
        
        if (configElement.length) {
            blockTypesConfig = JSON.parse(configElement.text());
            console.log('[EdgeX] 加载的块类型配置:', blockTypesConfig);
        }
        
        // 加载已保存的内容块
        const savedData = $('#edgex_content_blocks_data').val();
        console.log('[EdgeX] 已保存的数据:', savedData ? '存在' : '不存在');
        
        if (savedData) {
            try {
                contentBlocks = JSON.parse(savedData);
                console.log('[EdgeX] 已加载的内容块:', contentBlocks.length);
            } catch(e) {
                console.error('[EdgeX] 解析内容块失败:', e);
                contentBlocks = [];
            }
        }
        
        // 绑定事件
        bindEvents();
        
        // 初始化拖拽排序
        initSortable();
        
        console.log('[EdgeX] 初始化完成');
    }
    
    function bindEvents() {
        console.log('[EdgeX] bindEvents 被调用');
        
        // 显示/隐藏模块类型选择器
        $(document).on('click', '.edgex-add-block-btn', function() {
            console.log('[EdgeX] 点击添加块按钮');
            $('.edgex-block-type-picker').slideToggle(300);
        });
        
        // 选择模块类型
        $(document).on('click', '.edgex-block-type-card', function() {
            const blockType = $(this).data('block-type');
            console.log('[EdgeX] 选择块类型:', blockType);
            addNewBlock(blockType);
            $('.edgex-block-type-picker').slideUp(300);
        });
        
        // 编辑模块
        $(document).on('click', '.edgex-edit-block', function() {
            const blockIndex = $(this).closest('.edgex-block-item').data('block-index');
            console.log('[EdgeX] 点击编辑块:', blockIndex);
            editBlock(blockIndex);
        });
        
        // 复制模块
        $(document).on('click', '.edgex-duplicate-block', function() {
            const blockIndex = $(this).closest('.edgex-block-item').data('block-index');
            duplicateBlock(blockIndex);
        });
        
        // 删除模块
        $(document).on('click', '.edgex-remove-block', function() {
            if (confirm('确定要删除这个模块吗？')) {
                const blockIndex = $(this).closest('.edgex-block-item').data('block-index');
                removeBlock(blockIndex);
            }
        });
        
        // 模态框关闭
        $(document).on('click', '.edgex-modal-close, .edgex-modal-cancel', function() {
            closeModal();
        });
        
        // 保存模块
        $(document).on('click', '.edgex-modal-save', function() {
            saveBlock();
        });
        
        // 点击模态框外部关闭
        $(document).on('click', '.edgex-block-editor-modal', function(e) {
            if ($(e.target).is('.edgex-block-editor-modal')) {
                closeModal();
            }
        });
        
        // Repeater 可折叠切换
        $(document).on('click', '.edgex-repeater-header', function() {
            const $items = $(this).next('.edgex-repeater-items');
            const $toggle = $(this).find('.edgex-repeater-toggle');
            
            $items.slideToggle(200);
            $toggle.toggleClass('collapsed');
        });
        
        // Repeater 添加项
        $(document).on('click', '.edgex-repeater-add', function() {
            const $wrapper = $(this).prev('.edgex-repeater-wrapper');
            const container = $wrapper.find('.edgex-repeater-items');
            const fieldName = $(this).data('field-name');
            const blockType = currentEditingIndex !== null ? contentBlocks[currentEditingIndex].type : '';
            
            if (blockTypesConfig[blockType]) {
                const fields = findRepeaterFields(blockTypesConfig[blockType].fields, fieldName);
                if (fields) {
                    const itemHtml = generateRepeaterItemHtml(fieldName, fields, container.children('.edgex-repeater-item').length);
                    container.append(itemHtml);
                    
                    // 展开列表（如果是折叠状态）
                    if (!container.is(':visible')) {
                        container.slideDown(200);
                        $wrapper.find('.edgex-repeater-toggle').removeClass('collapsed');
                    }
                    
                    // 更新计数
                    updateRepeaterCount(fieldName);
                    
                    // 为新添加的项加载图片预览
                    loadImagePreviews();
                }
            }
        });
        
        // Repeater 删除项
        $(document).on('click', '.edgex-repeater-remove', function() {
            const $item = $(this).closest('.edgex-repeater-item');
            const $wrapper = $item.closest('.edgex-repeater-wrapper');
            const fieldName = $wrapper.find('.edgex-repeater-header').data('field-name');
            
            $item.slideUp(200, function() {
                $(this).remove();
                // 更新计数
                updateRepeaterCount(fieldName);
            });
        });
        
        // 媒体上传
        $(document).on('click', '.edgex-upload-image-btn', function(e) {
            e.preventDefault();
            
            const button = $(this);
            const inputField = button.prev('input');
            const preview = button.next('.edgex-image-preview');
            
            const mediaUploader = wp.media({
                title: '选择图片',
                button: { text: '使用此图片' },
                multiple: false
            });
            
            mediaUploader.on('select', function() {
                const attachment = mediaUploader.state().get('selection').first().toJSON();
                inputField.val(attachment.id);
                preview.html('<img src="' + attachment.url + '" data-attachment-id="' + attachment.id + '" style="max-width: 200px; height: auto; margin-top: 10px; border-radius: 4px;">');
                
                // 触发条件字段更新（用于背景图片等字段）
                updateConditionalFields();
            });
            
            mediaUploader.open();
        });
        
        // 颜色选择器主输入 - 改变时同步到文本框
        $(document).on('change input', '.edgex-color-primary', function() {
            const fieldName = $(this).data('field-name');
            const selectedColor = $(this).val();
            const $textInput = $('.edgex-color-text-fallback[data-field-name="' + fieldName + '"]');
            
            console.log('[EdgeX] 颜色选择器变化:', fieldName, '=', selectedColor);
            
            if ($textInput.length) {
                const currentTextValue = $textInput.val().trim();
                
                // 同步条件：文本框为空 或 文本框是纯色（不是渐变色等复杂值）
                const isEmptyOrSimpleColor = !currentTextValue || 
                    (!currentTextValue.includes('gradient') && 
                     !currentTextValue.includes('rgb') && 
                     !currentTextValue.includes('hsl'));
                
                if (isEmptyOrSimpleColor) {
                    $textInput.val(selectedColor);
                    console.log('[EdgeX] 已同步到文本框');
                } else {
                    console.log('[EdgeX] 文本框包含复杂颜色值，不同步');
                }
            }
        });
        
        // 文本框输入 - 如果是纯色，同步到颜色选择器
        $(document).on('input blur', '.edgex-color-text-fallback', function() {
            const fieldName = $(this).data('field-name');
            const textValue = $(this).val().trim();
            const $colorPicker = $('.edgex-color-primary[data-field-name="' + fieldName + '"]');
            
            console.log('[EdgeX] 文本框输入:', fieldName, '=', textValue);
            
            // 如果输入的是有效的十六进制颜色（6位或3位），同步到颜色选择器
            if (textValue && textValue.match(/^#[0-9A-Fa-f]{6}$/)) {
                $colorPicker.val(textValue);
                console.log('[EdgeX] 已同步到颜色选择器 (6位)');
            } else if (textValue && textValue.match(/^#[0-9A-Fa-f]{3}$/)) {
                // 3位转6位
                const fullColor = '#' + textValue[1] + textValue[1] + textValue[2] + textValue[2] + textValue[3] + textValue[3];
                $colorPicker.val(fullColor);
                console.log('[EdgeX] 已同步到颜色选择器 (3位转6位):', fullColor);
            }
        });
    }
    
    function initSortable() {
        $('#edgex-blocks-list').sortable({
            handle: '.block-drag-handle',
            placeholder: 'edgex-block-placeholder',
            start: function(e, ui) {
                ui.item.addClass('dragging');
            },
            stop: function(e, ui) {
                ui.item.removeClass('dragging');
                updateBlocksOrder();
            }
        });
    }
    
    function addNewBlock(blockType) {
        console.log('[EdgeX] addNewBlock 被调用, type:', blockType);
        
        const blockConfig = blockTypesConfig[blockType];
        if (!blockConfig) {
            console.error('[EdgeX] 找不到块类型配置:', blockType);
            return;
        }
        
        // 创建新块
        const newBlock = {
            type: blockType,
            data: getDefaultBlockData(blockConfig.fields)
        };
        
        console.log('[EdgeX] 新块数据:', newBlock);
        
        contentBlocks.push(newBlock);
        refreshBlocksList();
        saveToHiddenField();
        
        // 自动打开编辑器
        setTimeout(function() {
            console.log('[EdgeX] 准备打开编辑器...');
            editBlock(contentBlocks.length - 1);
        }, 100);
    }
    
    function getDefaultBlockData(fields) {
        const data = {};
        
        for (const fieldName in fields) {
            const field = fields[fieldName];
            
            // 跳过 section 字段（它们只是 UI 装饰）
            if (field.type === 'section') {
                continue;
            }
            
            if (field.type === 'repeater') {
                data[fieldName] = [];
            } else if (field.default) {
                data[fieldName] = field.default;
            } else {
                data[fieldName] = '';
            }
        }
        
        return data;
    }
    
    function editBlock(index) {
        console.log('[EdgeX] editBlock 被调用, index:', index);
        
        currentEditingIndex = index;
        const block = contentBlocks[index];
        const blockConfig = blockTypesConfig[block.type];
        
        console.log('[EdgeX] 编辑块:', block);
        console.log('[EdgeX] 块配置:', blockConfig);
        
        if (!blockConfig) {
            console.error('[EdgeX] 找不到块配置!');
            return;
        }
        
        // 设置模态框标题
        $('#edgex-modal-title').text('编辑 ' + blockConfig.label);
        
        // 生成表单
        const formHtml = generateBlockForm(blockConfig.fields, block.data);
        console.log('[EdgeX] 生成的表单 HTML 长度:', formHtml.length);
        $('#edgex-modal-body').html(formHtml);
        
        // 显示模态框
        $('#edgex-block-editor-modal').fadeIn(300);
        console.log('[EdgeX] 模态框已显示');
        
        // 解除之前的事件监听（避免重复绑定）
        $('#edgex-modal-body').off('change.conditionalFields');
        
        // 初始化条件显示
        initConditionalFields();
        
        // 监听条件字段的变化（使用命名空间避免重复）
        $('#edgex-modal-body').on('change.conditionalFields', 'select[name], input[name]', function() {
            console.log('[EdgeX] 字段值改变:', $(this).attr('name'), '=', $(this).val());
            updateConditionalFields();
        });
        
        // 加载图片预览
        loadImagePreviews();
    }
    
    /**
     * 加载所有图片字段的预览
     */
    function loadImagePreviews() {
        console.log('[EdgeX] 开始加载图片预览...');
        
        $('.edgex-image-preview img[data-attachment-id]').each(function() {
            const $img = $(this);
            const attachmentId = $img.data('attachment-id');
            
            if (!attachmentId) return;
            
            console.log('[EdgeX] 加载图片 ID:', attachmentId);
            
            // 使用 WordPress Media Library API 获取图片信息
            const attachment = wp.media.attachment(attachmentId);
            
            attachment.fetch().then(function() {
                const url = attachment.get('url');
                if (url) {
                    $img.attr('src', url);
                    console.log('[EdgeX] 图片加载成功:', attachmentId, url);
                } else {
                    console.warn('[EdgeX] 图片URL为空:', attachmentId);
                }
            }).fail(function() {
                console.error('[EdgeX] 加载图片失败:', attachmentId);
                $img.parent().html('<span style="color: #dc3232;">图片加载失败 (ID: ' + attachmentId + ')</span>');
            });
        });
    }
    
    function generateBlockForm(fields, data) {
        let html = '';
        const fieldNames = Object.keys(fields);
        let i = 0;
        
        while (i < fieldNames.length) {
            const fieldName = fieldNames[i];
            const field = fields[fieldName];
            
            // 检查是否是按钮文字、背景色、文字色的三列组合
            if (fieldName === 'button_text') {
                const field2Name = fieldNames[i + 1];
                const field3Name = fieldNames[i + 2];
                
                if (field2Name === 'button_bg_color' && field3Name === 'button_text_color') {
                    html += '<div class="edgex-field-row-3">';
                    html += generateFieldHtml(fieldName, field, data, fields);
                    html += generateFieldHtml(field2Name, fields[field2Name], data, fields);
                    html += generateFieldHtml(field3Name, fields[field3Name], data, fields);
                    html += '</div>';
                    i += 3;
                    continue;
                }
            }
            
            // 检查是否可以与下一个字段组合成2列栅格布局
            const nextFieldName = fieldNames[i + 1];
            const nextField = nextFieldName ? fields[nextFieldName] : null;
            const canGrid = shouldUseGrid(field, nextField, fieldName, nextFieldName);
            
            if (canGrid && nextField) {
                // 使用2列栅格布局
                html += '<div class="edgex-field-row">';
                html += generateFieldHtml(fieldName, field, data, fields);
                html += generateFieldHtml(nextFieldName, nextField, data, fields);
                html += '</div>';
                i += 2;
                continue;
            }
            
            // 单独显示字段
            html += generateFieldHtml(fieldName, field, data, fields);
            i++;
        }
        
        return html;
    }
    
    /**
     * 判断两个字段是否应该使用栅格布局并排显示
     */
    function shouldUseGrid(field1, field2, name1, name2) {
        if (!field2) return false;
        
        // 跳过 section、repeater
        const excludeTypes = ['section', 'repeater'];
        if (excludeTypes.includes(field1.type) || excludeTypes.includes(field2.type)) {
            return false;
        }
        
        // 特殊组合规则
        
        // 1. 主标题 + 标题颜色
        if ((name1 === 'content_title' && name2 === 'title_color') ||
            (name1 === 'title' && name2 === 'title_color')) {
            return true;
        }
        
        // 2. 副标题 + 副标题颜色
        if (name1 === 'content_subtitle' && name2 === 'subtitle_color') {
            return true;
        }
        
        // 3. 正文 + 正文颜色
        if (name1 === 'content_text' && name2 === 'content_color') {
            return true;
        }
        
        // 3. 按钮文字、背景色、文字色会用3列布局，这里跳过
        if (name1 === 'button_text' || name1 === 'button_bg_color' || name1 === 'button_text_color') {
            return false;
        }
        
        // 4. 按钮动作相关字段
        if (name1 === 'button_action' && name2 === 'button_link') {
            return true;
        }
        if (name1 === 'button_action' && name2 === 'scroll_target') {
            return true;
        }
        
        
        // 背景颜色字段单独处理（有特殊布局）
        if (name1 === 'background_color' || name2 === 'background_color') {
            return false;
        }
        
        // 大文本框不并排
        if (field1.type === 'textarea' && (!field1.rows || field1.rows > 2)) {
            return false;
        }
        if (field2.type === 'textarea' && (!field2.rows || field2.rows > 2)) {
            return false;
        }
        
        // 颜色字段可以并排
        if (field1.type === 'color' && field2.type === 'color') {
            return true;
        }
        
        // select和color可以并排
        if ((field1.type === 'select' && field2.type === 'color') ||
            (field1.type === 'color' && field2.type === 'select')) {
            return true;
        }
        
        // 短文本和其他小字段可以并排
        if ((field1.type === 'text' || field1.type === 'url' || field1.type === 'number') &&
            (field2.type === 'text' || field2.type === 'url' || field2.type === 'number' || field2.type === 'select' || field2.type === 'color')) {
            return true;
        }
        
        // select和select可以并排
        if (field1.type === 'select' && field2.type === 'select') {
            return true;
        }
        
        return false;
    }
    
    /**
     * 生成单个字段的HTML
     */
    function generateFieldHtml(fieldName, field, data, allFields) {
        let html = '';
        const value = data[fieldName] || field.default || '';
        
        // 构建条件显示的 data 属性
        let conditionalAttr = '';
        let initialStyle = '';
        
        if (field.show_if) {
                conditionalAttr = ' data-show-if="' + escapeHtml(JSON.stringify(field.show_if)) + '"';
                
                // 检查初始状态是否应该隐藏
                let shouldHide = false;
                for (const conditionField in field.show_if) {
                    const allowedValues = field.show_if[conditionField];
                    // 获取条件字段的当前值（考虑默认值）
                    let currentValue = data[conditionField];
                    if (!currentValue && allFields && allFields[conditionField] && allFields[conditionField].default) {
                        currentValue = allFields[conditionField].default;
                    }
                    if (!currentValue) {
                        currentValue = '';
                    }
                    
                    console.log('[EdgeX] 初始检查 - 字段:', fieldName, '条件:', conditionField, '当前值:', currentValue, '允许值:', allowedValues);
                    
                    // 特殊条件：'!' 表示字段必须有值（不为空）
                    if (allowedValues.length === 1 && allowedValues[0] === '!') {
                        if (!currentValue || currentValue === '' || currentValue === '0') {
                            shouldHide = true;
                            break;
                        }
                    } else {
                        if (!allowedValues.includes(currentValue)) {
                            shouldHide = true;
                            break;
                        }
                    }
                }
                
                if (shouldHide) {
                    initialStyle = ' style="display: none;"';
                    console.log('[EdgeX] 初始隐藏字段:', fieldName);
                }
            }
            
            // 分组标题（Section Header）
            if (field.type === 'section') {
                html += '<div class="edgex-section-header" data-field-name="' + fieldName + '">';
                html += '<h4 class="edgex-section-title">' + field.label + '</h4>';
                html += '</div>';
                return html;
            }
            
            html += '<div class="edgex-field-group"' + conditionalAttr + initialStyle + ' data-field-name="' + fieldName + '">';
            html += '<label class="edgex-field-label">' + field.label + '</label>';
            
            switch (field.type) {
                case 'text':
                case 'url':
                    // 检查是否是背景颜色字段（需要颜色选择器为主 + 文本框为辅）
                    if (fieldName === 'background_color' || (fieldName.includes('background') && fieldName.includes('color'))) {
                        // 提取纯色值或使用默认值
                        let colorValue = field.default || '#ffffff';
                        let textValue = value || '';
                        
                        if (textValue) {
                            // 如果文本框有值且是十六进制颜色，同步到颜色选择器
                            if (textValue.match(/^#[0-9A-Fa-f]{6}$/)) {
                                colorValue = textValue;
                            } else if (textValue.match(/^#[0-9A-Fa-f]{3}$/)) {
                                // 3位十六进制转6位
                                colorValue = '#' + textValue[1] + textValue[1] + textValue[2] + textValue[2] + textValue[3] + textValue[3];
                            }
                            // 如果是渐变色等复杂值，colorValue 保持为默认值，文本框显示复杂值
                        } else {
                            // 如果文本框为空，使用默认颜色
                            textValue = colorValue;
                        }
                        
                        html += '<div class="edgex-color-with-text-horizontal">';
                        html += '<div class="edgex-color-picker-column">';
                        html += '<label class="edgex-inline-label">选择颜色</label>';
                        html += '<input type="color" class="edgex-field-input edgex-color-primary" data-field-name="' + fieldName + '" value="' + colorValue + '">';
                        html += '</div>';
                        html += '<div class="edgex-color-text-column">';
                        html += '<label class="edgex-inline-label">或输入代码（支持渐变）</label>';
                        html += '<input type="text" class="edgex-field-input edgex-color-text-fallback" name="' + fieldName + '" value="' + escapeHtml(textValue) + '" placeholder="' + (field.placeholder || '#ffffff 或 linear-gradient(...)') + '" data-field-name="' + fieldName + '">';
                        html += '</div>';
                        html += '</div>';
                    } else {
                        html += '<input type="' + field.type + '" class="edgex-field-input" name="' + fieldName + '" value="' + escapeHtml(value) + '" placeholder="' + (field.placeholder || '') + '">';
                    }
                    
                    // 添加字段描述
                    if (field.description) {
                        html += '<p class="edgex-field-description">' + field.description + '</p>';
                    }
                    break;
                    
                case 'textarea':
                    const rows = field.rows || 5;
                    html += '<textarea class="edgex-field-textarea" name="' + fieldName + '" rows="' + rows + '" placeholder="' + (field.placeholder || '') + '">' + escapeHtml(value) + '</textarea>';
                    break;
                    
                case 'number':
                    html += '<input type="number" class="edgex-field-input" name="' + fieldName + '" value="' + escapeHtml(value) + '" min="' + (field.min || '') + '" max="' + (field.max || '') + '">';
                    break;
                    
                case 'color':
                    html += '<input type="color" class="edgex-field-input" name="' + fieldName + '" value="' + escapeHtml(value) + '">';
                    break;
                    
                case 'select':
                    html += '<select class="edgex-field-select" name="' + fieldName + '">';
                    for (const optKey in field.options) {
                        const selected = value === optKey ? ' selected' : '';
                        html += '<option value="' + optKey + '"' + selected + '>' + field.options[optKey] + '</option>';
                    }
                    html += '</select>';
                    
                    // 添加字段描述（如果有）
                    if (field.description) {
                        html += '<p class="edgex-field-description">' + field.description + '</p>';
                    }
                    break;
                    
                case 'image':
                    html += '<input type="hidden" class="edgex-field-input" name="' + fieldName + '" value="' + escapeHtml(value) + '">';
                    html += '<button type="button" class="button edgex-upload-image-btn">选择图片</button>';
                    html += '<div class="edgex-image-preview">';
                    if (value) {
                        // 显示预览图（需要通过 AJAX 获取 URL）
                        html += '<img src="" data-attachment-id="' + value + '" style="max-width: 200px; height: auto; margin-top: 10px; border-radius: 4px;">';
                    }
                    html += '</div>';
                    break;
                    
                case 'wysiwyg':
                    html += '<textarea class="edgex-field-textarea" name="' + fieldName + '" rows="8">' + escapeHtml(value) + '</textarea>';
                    html += '<p class="description">支持 HTML 代码</p>';
                    break;
                    
                case 'repeater':
                    const itemCount = Array.isArray(value) ? value.length : 0;
                    html += '<div class="edgex-repeater-wrapper">';
                    html += '<div class="edgex-repeater-header" data-field-name="' + fieldName + '">';
                    html += '<div class="edgex-repeater-title">';
                    html += field.label || '列表项';
                    html += '<span class="edgex-repeater-count">' + itemCount + '</span>';
                    html += '</div>';
                    html += '<span class="edgex-repeater-toggle">▼</span>';
                    html += '</div>';
                    html += '<div class="edgex-repeater-items">';
                    
                    if (Array.isArray(value) && value.length > 0) {
                        value.forEach(function(item, itemIndex) {
                            html += generateRepeaterItemHtml(fieldName, field.fields, itemIndex, item);
                        });
                    }
                    
                    html += '</div>';
                    html += '</div>';
                    html += '<button type="button" class="button edgex-repeater-add" data-field-name="' + fieldName + '">' + (field.button_label || '+ 添加项') + '</button>';
                    break;
            }
            
            html += '</div>';
        
        return html;
    }
    
    function generateRepeaterItemHtml(fieldName, fields, itemIndex, itemData) {
        itemData = itemData || {};
        
        let html = '<div class="edgex-repeater-item" data-item-index="' + itemIndex + '">';
        html += '<div class="edgex-repeater-item-header">';
        html += '<strong>项目 #' + (itemIndex + 1) + '</strong>';
        html += '<button type="button" class="edgex-repeater-remove">删除</button>';
        html += '</div>';
        
        for (const subFieldName in fields) {
            const subField = fields[subFieldName];
            const subValue = itemData[subFieldName] || subField.default || '';
            const fullFieldName = fieldName + '[' + itemIndex + '][' + subFieldName + ']';
            
            html += '<div class="edgex-field-group">';
            html += '<label class="edgex-field-label">' + subField.label + '</label>';
            
            switch (subField.type) {
                case 'text':
                case 'url':
                    html += '<input type="' + subField.type + '" class="edgex-field-input" name="' + fullFieldName + '" value="' + escapeHtml(subValue) + '" placeholder="' + (subField.placeholder || '') + '">';
                    break;
                    
                case 'textarea':
                    html += '<textarea class="edgex-field-textarea" name="' + fullFieldName + '" rows="3">' + escapeHtml(subValue) + '</textarea>';
                    break;
                    
                case 'number':
                    html += '<input type="number" class="edgex-field-input" name="' + fullFieldName + '" value="' + escapeHtml(subValue) + '" min="' + (subField.min || '') + '" max="' + (subField.max || '') + '">';
                    break;
                    
                case 'image':
                    html += '<input type="hidden" class="edgex-field-input" name="' + fullFieldName + '" value="' + escapeHtml(subValue) + '">';
                    html += '<button type="button" class="button edgex-upload-image-btn">选择图片</button>';
                    html += '<div class="edgex-image-preview">';
                    if (subValue) {
                        html += '<img src="" data-attachment-id="' + escapeHtml(subValue) + '" style="max-width: 200px; height: auto; margin-top: 10px; border-radius: 4px;">';
                    }
                    html += '</div>';
                    break;
            }
            
            html += '</div>';
        }
        
        html += '</div>';
        return html;
    }
    
    function findRepeaterFields(fields, fieldName) {
        if (fields[fieldName] && fields[fieldName].type === 'repeater') {
            return fields[fieldName].fields;
        }
        return null;
    }
    
    /**
     * 更新Repeater计数显示
     */
    function updateRepeaterCount(fieldName) {
        const $wrapper = $('.edgex-repeater-header[data-field-name="' + fieldName + '"]').closest('.edgex-repeater-wrapper');
        const count = $wrapper.find('.edgex-repeater-item').length;
        $wrapper.find('.edgex-repeater-count').text(count);
    }
    
    function saveBlock() {
        console.log('[EdgeX] saveBlock 被调用');
        
        if (currentEditingIndex === null) {
            console.error('[EdgeX] currentEditingIndex 为 null');
            return;
        }
        
        const block = contentBlocks[currentEditingIndex];
        const blockConfig = blockTypesConfig[block.type];
        
        console.log('[EdgeX] 正在保存块:', block);
        console.log('[EdgeX] 块配置:', blockConfig);
        
        // 收集表单数据
        const formData = {};
        
        // 遍历所有字段（包括隐藏的条件字段）
        $('#edgex-modal-body .edgex-field-group').each(function() {
            const fieldName = $(this).data('field-name');
            
            if (!fieldName) return;
            
            // 跳过 section 字段（它们只是 UI 装饰）
            if (fieldName.startsWith('_section_')) {
                return;
            }
            
            // 跳过 repeater 字段（下面单独处理）
            if (blockConfig.fields[fieldName] && blockConfig.fields[fieldName].type === 'repeater') {
                return;
            }
            
            // 查找该字段组中的输入元素（优先 name 属性匹配）
            let $input = $(this).find('[name="' + fieldName + '"]');
            
            if ($input.length === 0) {
                // 如果找不到，尝试查找第一个有 name 属性的元素
                $input = $(this).find('input[name], textarea[name], select[name]').first();
            }
            
            if ($input.length > 0) {
                let fieldValue = $input.val();
                
                // 特殊处理：背景颜色字段，如果文本框为空，使用颜色选择器的值
                if ((fieldName === 'background_color' || (fieldName.includes('background') && fieldName.includes('color'))) && 
                    (!fieldValue || fieldValue.trim() === '')) {
                    const $colorPicker = $(this).find('.edgex-color-primary[data-field-name="' + fieldName + '"]');
                    if ($colorPicker.length > 0) {
                        fieldValue = $colorPicker.val();
                        console.log('[EdgeX] 文本框为空，使用颜色选择器的值:', fieldValue);
                    }
                }
                
                formData[fieldName] = fieldValue;
                console.log('[EdgeX] 保存字段:', fieldName, '=', fieldValue);
            }
        });
        
        // 处理 repeater 字段
        for (const fieldName in blockConfig.fields) {
            const field = blockConfig.fields[fieldName];
            
            if (field.type === 'repeater') {
                const items = [];
                const $repeaterContainer = $('#edgex-modal-body').find('[data-field-name="' + fieldName + '"]');
                
                $repeaterContainer.find('.edgex-repeater-item').each(function(itemIndex) {
                    const item = {};
                    
                    $(this).find('input, textarea, select').each(function() {
                        const name = $(this).attr('name');
                        if (!name) return;
                        
                        // 解析 fieldName[index][subFieldName]
                        const matches = name.match(/\[(\d+)\]\[([^\]]+)\]/);
                        if (matches) {
                            const subFieldName = matches[2];
                            item[subFieldName] = $(this).val();
                        }
                    });
                    
                    if (Object.keys(item).length > 0) {
                        items.push(item);
                    }
                });
                
                formData[fieldName] = items;
                console.log('[EdgeX] 保存 repeater 字段:', fieldName, '项数:', items.length);
            }
        }
        
        console.log('[EdgeX] 最终保存的数据:', formData);
        
        // 更新块数据
        block.data = formData;
        
        // 刷新显示
        refreshBlocksList();
        saveToHiddenField();
        closeModal();
        
        console.log('[EdgeX] 块保存完成');
    }
    
    function duplicateBlock(index) {
        const block = JSON.parse(JSON.stringify(contentBlocks[index])); // 深拷贝
        contentBlocks.splice(index + 1, 0, block);
        refreshBlocksList();
        saveToHiddenField();
    }
    
    function removeBlock(index) {
        contentBlocks.splice(index, 1);
        refreshBlocksList();
        saveToHiddenField();
    }
    
    function updateBlocksOrder() {
        const newOrder = [];
        
        $('#edgex-blocks-list .edgex-block-item').each(function() {
            const oldIndex = $(this).data('block-index');
            newOrder.push(contentBlocks[oldIndex]);
        });
        
        contentBlocks = newOrder;
        refreshBlocksList();
        saveToHiddenField();
    }
    
    function refreshBlocksList() {
        const $list = $('#edgex-blocks-list');
        $list.empty();
        
        if (contentBlocks.length === 0) {
            $list.html('<div class="edgex-empty-state"><p>📭 还没有添加任何内容模块</p><p>点击下方按钮添加您的第一个模块</p></div>');
            return;
        }
        
        contentBlocks.forEach(function(block, index) {
            const blockConfig = blockTypesConfig[block.type];
            if (!blockConfig) return;
            
            const blockTitle = block.data.title || '未命名';
            const blockLabel = blockConfig.label;
            const blockIcon = blockConfig.icon;
            
            const $item = $('<div class="edgex-block-item" data-block-index="' + index + '" data-block-type="' + block.type + '">' +
                '<div class="block-item-left">' +
                    '<span class="block-drag-handle">☰</span>' +
                    '<div class="block-item-info">' +
                        '<div class="block-item-title">' + blockIcon + ' ' + escapeHtml(blockTitle || blockLabel) + '</div>' +
                        '<div class="block-item-type">' + blockLabel + '</div>' +
                    '</div>' +
                '</div>' +
                '<div class="block-item-actions">' +
                    '<button type="button" class="button edgex-edit-block">✏️ 编辑</button>' +
                    '<button type="button" class="button edgex-duplicate-block">📋 复制</button>' +
                    '<button type="button" class="button edgex-remove-block">🗑️ 删除</button>' +
                '</div>' +
            '</div>');
            
            $list.append($item);
        });
    }
    
    function saveToHiddenField() {
        $('#edgex_content_blocks_data').val(JSON.stringify(contentBlocks));
    }
    
    function closeModal() {
        $('#edgex-block-editor-modal').fadeOut(300);
        currentEditingIndex = null;
    }
    
    function escapeHtml(text) {
        if (typeof text !== 'string') return text;
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }
    
    /**
     * 初始化条件显示字段
     */
    function initConditionalFields() {
        console.log('[EdgeX] 初始化条件显示字段');
        setTimeout(function() {
            updateConditionalFields();
        }, 100);
    }
    
    /**
     * 更新条件显示字段的可见性
     */
    function updateConditionalFields() {
        const conditionalFieldsCount = $('.edgex-field-group[data-show-if]').length;
        console.log('[EdgeX] 检查条件显示字段，共 ' + conditionalFieldsCount + ' 个');
        
        // 处理所有带条件显示的字段组
        $('.edgex-field-group[data-show-if]').each(function() {
            const $fieldGroup = $(this);
            const showIfData = $fieldGroup.data('show-if');
            
            if (!showIfData) {
                $fieldGroup.show();
                return;
            }
            
            let shouldShow = true;
            
            // 检查每个条件
            for (const conditionField in showIfData) {
                const allowedValues = showIfData[conditionField];
                const $conditionInput = $('#edgex-modal-body').find('[name="' + conditionField + '"]');
                
                if ($conditionInput.length) {
                    const currentValue = $conditionInput.val();
                    
                    console.log('[EdgeX] 字段: ' + $fieldGroup.data('field-name') + 
                                ', 条件: ' + conditionField + '=' + currentValue + 
                                ', 允许值: ' + JSON.stringify(allowedValues));
                    
                    // 特殊条件：'!' 表示字段必须有值（不为空）
                    if (allowedValues.length === 1 && allowedValues[0] === '!') {
                        if (!currentValue || currentValue === '' || currentValue === '0') {
                            shouldShow = false;
                            break;
                        }
                    } else {
                        // 检查当前值是否在允许的值列表中
                        if (!allowedValues.includes(currentValue)) {
                            shouldShow = false;
                            break;
                        }
                    }
                } else {
                    // 如果找不到条件字段，默认隐藏
                    console.log('[EdgeX] 找不到条件字段: ' + conditionField);
                    shouldShow = false;
                    break;
                }
            }
            
            // 显示或隐藏字段（使用动画）
            const isCurrentlyVisible = $fieldGroup.is(':visible');
            
            if (shouldShow && !isCurrentlyVisible) {
                $fieldGroup.slideDown(200);
            } else if (!shouldShow && isCurrentlyVisible) {
                $fieldGroup.slideUp(200);
            } else if (shouldShow && isCurrentlyVisible) {
                // 已经是显示状态，保持显示
                $fieldGroup.show();
            } else if (!shouldShow && !isCurrentlyVisible) {
                // 已经是隐藏状态，保持隐藏
                $fieldGroup.hide();
            }
        });
    }
    
    // 暴露给全局作用域以便调试
    window.EdgeXContentBuilder = {
        testConditionalFields: function() {
            console.log('[EdgeX Test] 开始测试条件显示...');
            console.log('[EdgeX Test] 条件字段数量:', $('.edgex-field-group[data-show-if]').length);
            $('.edgex-field-group[data-show-if]').each(function() {
                console.log('[EdgeX Test] 字段:', $(this).data('field-name'), '显示状态:', $(this).is(':visible'), 'show_if:', $(this).data('show-if'));
            });
        },
        updateFields: function() {
            console.log('[EdgeX Test] 手动触发 updateConditionalFields');
            updateConditionalFields();
        },
        getBlockTypes: function() {
            console.log('[EdgeX Test] 块类型配置:', blockTypesConfig);
            return blockTypesConfig;
        },
        getContentBlocks: function() {
            console.log('[EdgeX Test] 内容块:', contentBlocks);
            return contentBlocks;
        },
        checkModal: function() {
            console.log('[EdgeX Test] 检查模态框...');
            console.log('- 模态框容器:', $('#edgex-block-editor-modal').length);
            console.log('- 模态框显示状态:', $('#edgex-block-editor-modal').css('display'));
            console.log('- 模态框内容:', $('#edgex-block-editor-modal .edgex-modal-content').length);
            console.log('- Header:', $('#edgex-block-editor-modal .edgex-modal-header').length);
            console.log('- Body:', $('#edgex-block-editor-modal .edgex-modal-body').length);
            console.log('- Footer:', $('#edgex-block-editor-modal .edgex-modal-footer').length);
            console.log('- 保存按钮:', $('.edgex-modal-save').length);
            console.log('- 保存按钮可见:', $('.edgex-modal-save').is(':visible'));
            console.log('- 取消按钮:', $('.edgex-modal-cancel').length);
            
            // 检查 CSS
            const $footer = $('.edgex-modal-footer');
            if ($footer.length) {
                console.log('- Footer CSS:');
                console.log('  display:', $footer.css('display'));
                console.log('  position:', $footer.css('position'));
                console.log('  visibility:', $footer.css('visibility'));
                console.log('  height:', $footer.height());
            }
        },
        loadImagePreviews: function() {
            console.log('[EdgeX Test] 手动触发图片预览加载');
            loadImagePreviews();
        },
        checkImages: function() {
            console.log('[EdgeX Test] 检查图片字段...');
            console.log('- 图片预览容器数量:', $('.edgex-image-preview').length);
            console.log('- 带 attachment ID 的图片:', $('.edgex-image-preview img[data-attachment-id]').length);
            $('.edgex-image-preview img[data-attachment-id]').each(function() {
                console.log('  - ID:', $(this).data('attachment-id'), 'src:', $(this).attr('src'));
            });
        }
    };
    
    console.log('[EdgeX] 全局测试函数已注册到 window.EdgeXContentBuilder');
    
})(jQuery);

