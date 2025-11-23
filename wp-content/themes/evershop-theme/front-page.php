<?php
/**
 * Front Page Template
 * 使用 Widgets 系统渲染首页内容
 */

get_header(); 
?>

<div class="homepage-content bg-black text-white">
    <?php
    // 渲染 Homepage Content Widget 区域
    if (is_active_sidebar('homepage-content')) {
        dynamic_sidebar('homepage-content');
    } else {
        // 如果 Widget 区域为空，显示提示信息
        ?>
        <div class="page-width py-12 text-center">
            <div class="bg-[#1a1a1a] p-8 rounded-lg border border-gray-700">
                <h2 class="text-2xl font-bold mb-4">配置首页内容</h2>
                <p class="text-gray-400 mb-6">
                    前往 <strong>外观 > Widgets > Homepage Content</strong><br>
                    拖拽以下 Widgets 到此区域：
                </p>
                <ul class="text-left max-w-md mx-auto space-y-2 text-gray-300">
                    <li>• <strong>Flash Sale (Countdown)</strong> - 倒计时通告栏</li>
                    <li>• <strong>Hero Banner</strong> - 主视觉横幅</li>
                    <li>• <strong>Product Collection</strong> - 产品橱窗</li>
                </ul>
            </div>
        </div>
        <?php
    }
    ?>
</div>

<?php get_footer(); ?>
