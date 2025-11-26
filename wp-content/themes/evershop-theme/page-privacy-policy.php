<?php
/**
 * Template Name: Privacy Policy (Auto-Language)
 *
 * This template automatically switches between English and Chinese content based on the site language.
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

// Check current locale
$locale = get_locale();
$is_chinese = (strpos($locale, 'zh') !== false);
?>

<div class="page-width">
    
    <article id="post-<?php the_ID(); ?>" <?php post_class('single-page privacy-policy-page'); ?>>
        
        <header class="entry-header">
            <h1 class="entry-title"><?php the_title(); ?></h1>
        </header>
        
        <div class="entry-content">
            
            <!-- Last Updated -->
            <p class="privacy-policy-updated">
                <?php echo $is_chinese ? '最后更新时间：' : 'Last updated: '; ?>
                <?php echo get_the_modified_date($is_chinese ? 'Y年n月j日' : 'F j, Y'); ?>
            </p>

            <!-- Privacy Policy Content -->
            <div class="gdpr-policy-content">
                
                <?php if ($is_chinese) : ?>
                    <!-- Chinese Content -->
                    <h2>1. 简介</h2>
                    <p>欢迎来到 <strong><?php bloginfo('name'); ?></strong>（以下简称"我们"）。我们致力于保护您的个人信息和隐私权。如果您对本隐私声明或我们关于您个人信息的做法有任何疑问或疑虑，请通过 <a href="mailto:<?php echo get_option('admin_email'); ?>"><?php echo get_option('admin_email'); ?></a> 联系我们。</p>
                    <p>本隐私政策适用于通过我们的网站（例如 <?php echo home_url(); ?>）和/或任何相关服务、销售、营销或活动收集的所有信息。</p>

                    <h2>2. 我们收集的信息</h2>
                    <p>我们会收集您在注册网站、表示有兴趣获取关于我们或我们的产品和服务的信息、参与网站活动或以其他方式联系我们时自愿提供给我们的个人信息。</p>
                    <p>我们收集的个人信息取决于您与我们要和网站互动的背景、您做出的选择以及您使用的产品和功能。我们收集的个人信息可能包括以下内容：</p>
                    <ul class="list-disc pl-5 space-y-1">
                        <li>姓名</li>
                        <li>电子邮件地址</li>
                        <li>邮寄地址</li>
                        <li>电话号码</li>
                        <li>账单地址</li>
                        <li>借记卡/信用卡号码（由支付处理商安全处理）</li>
                    </ul>

                    <h2>3. 我们如何使用您的信息</h2>
                    <p>我们将通过网站收集的个人信息用于下述各种商业目的。我们依据合法的商业利益、为了与您签订或履行合同、征得您的同意和/或为了遵守我们的法律义务而处理您的个人信息。</p>
                    <ul class="list-disc pl-5 space-y-1">
                        <li><strong>促进账户创建和登录过程。</strong></li>
                        <li><strong>向您发送营销和促销通讯。</strong> 您可以随时选择拒收我们的营销邮件。</li>
                        <li><strong>履行和管理您的订单。</strong> 我们可能会使用您的信息来履行和管理您通过网站进行的订单、付款、退货和换货。</li>
                        <li><strong>请求反馈。</strong> 我们可能会使用您的信息来请求反馈，并就您使用我们网站的情况与您联系。</li>
                    </ul>

                    <h2>4. Cookie 和跟踪技术</h2>
                    <p>我们可能会使用 Cookie 和类似的跟踪技术（如网络信标和像素）来访问或存储信息。关于我们如何使用此类技术以及您可以如何拒绝某些 Cookie 的具体信息，请参阅我们的 Cookie 政策。</p>

                    <h2>5. 信息共享</h2>
                    <p>我们要么在征得您的同意、为了遵守法律、为您提供服务、保护您的权利或履行商业义务的情况下共享信息。我们可能会基于以下法律依据处理或共享我们持有的您的数据：</p>
                    <ul class="list-disc pl-5 space-y-1">
                        <li><strong>同意：</strong> 如果您已明确同意我们将您的个人信息用于特定目的，我们可能会处理您的数据。</li>
                        <li><strong>合法利益：</strong> 当为了实现我们的合法商业利益合理必要时，我们可能会处理您的数据。</li>
                        <li><strong>履行合同：</strong> 如果我们要与您签订了合同，我们可能会处理您的个人信息以履行合同条款。</li>
                    </ul>

                    <h2>6. 数据保留</h2>
                    <p>除非法律（如税务、会计或其他法律要求）要求或允许更长的保留期，否则我们要只会在为本隐私声明中规定的目的所必需的时间内保留您的个人信息。</p>

                    <h2>7. 您的隐私权利 (GDPR)</h2>
                    <p>在某些地区（如欧洲经济区），根据适用的数据保护法，您拥有某些权利。这些权利可能包括：(i) 请求访问并获取您个人信息的副本，(ii) 请求更正或删除；(iii) 限制处理您的个人信息；以及 (iv) 如适用，数据可移植性。在某些情况下，您还可能有权反对处理您的个人信息。</p>
                    <p>要提出此类请求，请使用下方提供的联系方式。我们将根据适用的数据保护法考虑并采取行动。</p>

                    <h2>8. 联系我们</h2>
                    <p>如果您对本政策有任何问题或评论，可以通过电子邮件发送至 <a href="mailto:<?php echo get_option('admin_email'); ?>"><?php echo get_option('admin_email'); ?></a> 与我们联系。</p>

                <?php else : ?>
                    <!-- English Content (Default) -->
                    <h2>1. Introduction</h2>
                    <p>Welcome to <strong><?php bloginfo('name'); ?></strong> ("we," "our," or "us"). We are committed to protecting your personal information and your right to privacy. If you have any questions or concerns about this privacy notice or our practices with regard to your personal information, please contact us at <a href="mailto:<?php echo get_option('admin_email'); ?>"><?php echo get_option('admin_email'); ?></a>.</p>
                    <p>This Privacy Policy applies to all information collected through our website (such as <?php echo home_url(); ?>), and/or any related services, sales, marketing, or events.</p>

                    <h2>2. Information We Collect</h2>
                    <p>We collect personal information that you voluntarily provide to us when you register on the Website, express an interest in obtaining information about us or our products and services, when you participate in activities on the Website, or otherwise when you contact us.</p>
                    <p>The personal information that we collect depends on the context of your interactions with us and the Website, the choices you make, and the products and features you use. The personal information we collect may include the following:</p>
                    <ul class="list-disc pl-5 space-y-1">
                        <li>Names</li>
                        <li>Email addresses</li>
                        <li>Mailing addresses</li>
                        <li>Phone numbers</li>
                        <li>Billing addresses</li>
                        <li>Debit/credit card numbers (processed securely by payment processors)</li>
                    </ul>

                    <h2>3. How We Use Your Information</h2>
                    <p>We use personal information collected via our Website for a variety of business purposes described below. We process your personal information for these purposes in reliance on our legitimate business interests, in order to enter into or perform a contract with you, with your consent, and/or for compliance with our legal obligations.</p>
                    <ul class="list-disc pl-5 space-y-1">
                        <li><strong>To facilitate account creation and logon process.</strong></li>
                        <li><strong>To send you marketing and promotional communications.</strong> You can opt-out of our marketing emails at any time.</li>
                        <li><strong>To fulfill and manage your orders.</strong> We may use your information to fulfill and manage your orders, payments, returns, and exchanges made through the Website.</li>
                        <li><strong>To request feedback.</strong> We may use your information to request feedback and to contact you about your use of our Website.</li>
                    </ul>

                    <h2>4. Cookies and Tracking Technologies</h2>
                    <p>We may use cookies and similar tracking technologies (like web beacons and pixels) to access or store information. Specific information about how we use such technologies and how you can refuse certain cookies is set out in our Cookie Policy.</p>

                    <h2>5. Information Sharing</h2>
                    <p>We only share information with your consent, to comply with laws, to provide you with services, to protect your rights, or to fulfill business obligations. We may process or share your data that we hold based on the following legal basis:</p>
                    <ul class="list-disc pl-5 space-y-1">
                        <li><strong>Consent:</strong> We may process your data if you have given us specific consent to use your personal information for a specific purpose.</li>
                        <li><strong>Legitimate Interests:</strong> We may process your data when it is reasonably necessary to achieve our legitimate business interests.</li>
                        <li><strong>Performance of a Contract:</strong> Where we have entered into a contract with you, we may process your personal information to fulfill the terms of our contract.</li>
                    </ul>

                    <h2>6. Data Retention</h2>
                    <p>We will only keep your personal information for as long as it is necessary for the purposes set out in this privacy notice, unless a longer retention period is required or permitted by law (such as tax, accounting, or other legal requirements).</p>

                    <h2>7. Your Privacy Rights (GDPR)</h2>
                    <p>In some regions (like the European Economic Area), you have certain rights under applicable data protection laws. These may include the right (i) to request access and obtain a copy of your personal information, (ii) to request rectification or erasure; (iii) to restrict the processing of your personal information; and (iv) if applicable, to data portability. In certain circumstances, you may also have the right to object to the processing of your personal information.</p>
                    <p>To make such a request, please use the contact details provided below. We will consider and act upon any request in accordance with applicable data protection laws.</p>

                    <h2>8. Contact Us</h2>
                    <p>If you have questions or comments about this policy, you may email us at <a href="mailto:<?php echo get_option('admin_email'); ?>"><?php echo get_option('admin_email'); ?></a>.</p>
                <?php endif; ?>
            </div>
            
        </div>
        
    </article>
    
</div>

<style>
    .privacy-policy-page {
        margin: 0 auto;
        padding: 60px 20px;
    }
    
    .privacy-policy-page .entry-header {
        margin-bottom: 30px;
        text-align: left;
    }

    .privacy-policy-page .entry-title {
        font-size: 2.5rem;
        font-weight: 700;
        color: #000 !important; /* Ensure title is black */
        margin-bottom: 10px;
        line-height: 1.2;
        display: block !important; /* Ensure title is visible */
    }

    .privacy-policy-updated {
        font-size: 0.9rem;
        color: #666;
        margin-bottom: 40px;
        padding-bottom: 20px;
        border-bottom: 1px solid #eee;
    }

    .privacy-policy-page .entry-content {
        font-size: 16px;
        line-height: 1.8;
        color: #333;
    }

    .privacy-policy-page h2 {
        font-size: 1.5rem;
        font-weight: 600;
        margin-top: 2.5rem;
        margin-bottom: 1rem;
        color: #000;
    }
    
    .privacy-policy-page p {
        margin-bottom: 1.2rem;
        color: #444;
    }
    
    .privacy-policy-page a {
        color: #000;
        text-decoration: underline;
        font-weight: 500;
    }

    .privacy-policy-page ul {
        margin-bottom: 1.5rem;
        list-style-type: disc;
        padding-left: 1.5rem;
    }
    
    .privacy-policy-page li {
        margin-bottom: 0.5rem;
        color: #444;
    }
</style>

<?php
get_footer();
