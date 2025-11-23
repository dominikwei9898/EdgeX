/**
 * 可折叠标签页功能
 * Collapsible Tabs Functionality
 */

(function() {
    'use strict';
    
    // 等待 DOM 加载完成
    document.addEventListener('DOMContentLoaded', function() {
        initCollapsibleTabs();
    });
    
    function initCollapsibleTabs() {
        const tabToggles = document.querySelectorAll('.tab-toggle');
        
        if (tabToggles.length === 0) {
            return;
        }
        
        // 默认展开第一个标签页
        const firstToggle = tabToggles[0];
        const firstContent = firstToggle.nextElementSibling;
        if (firstContent && firstContent.classList.contains('tab-content')) {
            firstToggle.classList.add('active');
            firstContent.classList.add('active');
        }
        
        // 为每个切换按钮添加点击事件
        tabToggles.forEach(function(toggle) {
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                toggleTab(this);
            });
        });
    }
    
    function toggleTab(toggle) {
        const content = toggle.nextElementSibling;
        
        if (!content || !content.classList.contains('tab-content')) {
            return;
        }
        
        const isActive = toggle.classList.contains('active');
        
        if (isActive) {
            // 关闭当前标签页
            toggle.classList.remove('active');
            content.classList.remove('active');
        } else {
            // 打开当前标签页
            toggle.classList.add('active');
            content.classList.add('active');
            
            // 平滑滚动到标签页（可选）
            setTimeout(function() {
                const rect = toggle.getBoundingClientRect();
                const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                if (rect.top < 0) {
                    window.scrollTo({
                        top: scrollTop + rect.top - 20,
                        behavior: 'smooth'
                    });
                }
            }, 300);
        }
    }
})();

