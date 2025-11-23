<?php
/**
 * EverShop Authentication API
 * 
 * 提供用户认证相关的 REST API 端点
 * 支持 JWT 令牌认证
 */

if (!defined('ABSPATH')) {
    exit;
}

class EverShop_Auth {
    
    private static $namespace = 'evershop/v1';
    
    public static function init() {
        $instance = new self();
        $instance->setup_hooks();
    }
    
    private function setup_hooks() {
        add_action('rest_api_init', [$this, 'register_routes']);
    }
    
    /**
     * 注册 REST API 路由
     */
    public function register_routes() {
        // 用户登录
        register_rest_route(self::$namespace, '/auth/login', [
            'methods' => 'POST',
            'callback' => [$this, 'login'],
            'permission_callback' => '__return_true'
        ]);
        
        // 用户注册
        register_rest_route(self::$namespace, '/auth/register', [
            'methods' => 'POST',
            'callback' => [$this, 'register'],
            'permission_callback' => '__return_true'
        ]);
        
        // 验证令牌
        register_rest_route(self::$namespace, '/auth/validate', [
            'methods' => ['GET', 'POST'],
            'callback' => [$this, 'validate_token'],
            'permission_callback' => [$this, 'check_jwt_auth']
        ]);
        
        // 刷新令牌
        register_rest_route(self::$namespace, '/auth/refresh', [
            'methods' => 'POST',
            'callback' => [$this, 'refresh_token'],
            'permission_callback' => [$this, 'check_jwt_auth']
        ]);
        
        // 获取当前用户信息
        register_rest_route(self::$namespace, '/auth/me', [
            'methods' => 'GET',
            'callback' => [$this, 'get_current_user'],
            'permission_callback' => [$this, 'check_jwt_auth']
        ]);
        
        // 忘记密码
        register_rest_route(self::$namespace, '/auth/forgot-password', [
            'methods' => 'POST',
            'callback' => [$this, 'forgot_password'],
            'permission_callback' => '__return_true'
        ]);
        
        // 重置密码
        register_rest_route(self::$namespace, '/auth/reset-password', [
            'methods' => 'POST',
            'callback' => [$this, 'reset_password'],
            'permission_callback' => '__return_true'
        ]);
    }
    
    /**
     * 用户登录
     */
    public function login($request) {
        $username = $request->get_param('username');
        $email = $request->get_param('email');
        $password = $request->get_param('password');
        
        // 验证必填字段
        if (empty($password)) {
            return new WP_Error('missing_password', 'Password is required', ['status' => 400]);
        }
        
        if (empty($username) && empty($email)) {
            return new WP_Error('missing_credentials', 'Username or email is required', ['status' => 400]);
        }
        
        // 确定登录标识
        $user_login = $username ?: $email;
        
        // 尝试通过邮箱获取用户名
        if (is_email($user_login)) {
            $user = get_user_by('email', $user_login);
            if ($user) {
                $user_login = $user->user_login;
            }
        }
        
        // 验证用户凭证
        $user = wp_authenticate($user_login, $password);
        
        if (is_wp_error($user)) {
            return new WP_Error('invalid_credentials', 'Invalid username or password', ['status' => 401]);
        }
        
        // 生成 JWT 令牌
        $token = $this->generate_jwt_token($user);
        
        if (is_wp_error($token)) {
            return $token;
        }
        
        // 返回用户信息和令牌
        return rest_ensure_response([
            'success' => true,
            'token' => $token,
            'user' => $this->format_user($user)
        ]);
    }
    
    /**
     * 用户注册
     */
    public function register($request) {
        $username = $request->get_param('username');
        $email = $request->get_param('email');
        $password = $request->get_param('password');
        $first_name = $request->get_param('first_name');
        $last_name = $request->get_param('last_name');
        
        // 验证必填字段
        if (empty($username)) {
            return new WP_Error('missing_username', 'Username is required', ['status' => 400]);
        }
        
        if (empty($email)) {
            return new WP_Error('missing_email', 'Email is required', ['status' => 400]);
        }
        
        if (empty($password)) {
            return new WP_Error('missing_password', 'Password is required', ['status' => 400]);
        }
        
        // 验证邮箱格式
        if (!is_email($email)) {
            return new WP_Error('invalid_email', 'Invalid email address', ['status' => 400]);
        }
        
        // 检查用户名是否已存在
        if (username_exists($username)) {
            return new WP_Error('username_exists', 'Username already exists', ['status' => 400]);
        }
        
        // 检查邮箱是否已存在
        if (email_exists($email)) {
            return new WP_Error('email_exists', 'Email already exists', ['status' => 400]);
        }
        
        // 创建用户
        $user_id = wp_create_user($username, $password, $email);
        
        if (is_wp_error($user_id)) {
            return new WP_Error('registration_failed', $user_id->get_error_message(), ['status' => 500]);
        }
        
        // 更新用户信息
        if ($first_name) {
            update_user_meta($user_id, 'first_name', $first_name);
        }
        
        if ($last_name) {
            update_user_meta($user_id, 'last_name', $last_name);
        }
        
        // 设置用户角色为客户
        $user = new WP_User($user_id);
        $user->set_role('customer');
        
        // 生成 JWT 令牌
        $token = $this->generate_jwt_token($user);
        
        if (is_wp_error($token)) {
            return $token;
        }
        
        // 发送欢迎邮件（可选）
        wp_new_user_notification($user_id, null, 'user');
        
        // 返回用户信息和令牌
        return rest_ensure_response([
            'success' => true,
            'token' => $token,
            'user' => $this->format_user($user)
        ]);
    }
    
    /**
     * 验证令牌
     */
    public function validate_token($request) {
        $user_id = get_current_user_id();
        
        if (!$user_id) {
            return new WP_Error('invalid_token', 'Invalid or expired token', ['status' => 401]);
        }
        
        $user = get_user_by('id', $user_id);
        
        return rest_ensure_response([
            'valid' => true,
            'user' => $this->format_user($user)
        ]);
    }
    
    /**
     * 刷新令牌
     */
    public function refresh_token($request) {
        $user_id = get_current_user_id();
        
        if (!$user_id) {
            return new WP_Error('invalid_token', 'Invalid or expired token', ['status' => 401]);
        }
        
        $user = get_user_by('id', $user_id);
        $token = $this->generate_jwt_token($user);
        
        if (is_wp_error($token)) {
            return $token;
        }
        
        return rest_ensure_response([
            'success' => true,
            'token' => $token,
            'user' => $this->format_user($user)
        ]);
    }
    
    /**
     * 获取当前用户信息
     */
    public function get_current_user($request) {
        $user_id = get_current_user_id();
        
        if (!$user_id) {
            return new WP_Error('not_logged_in', 'User not logged in', ['status' => 401]);
        }
        
        $user = get_user_by('id', $user_id);
        
        return rest_ensure_response($this->format_user($user));
    }
    
    /**
     * 忘记密码
     */
    public function forgot_password($request) {
        $user_login = $request->get_param('user_login');
        
        if (empty($user_login)) {
            return new WP_Error('missing_user_login', 'Username or email is required', ['status' => 400]);
        }
        
        // 检索密码
        $result = retrieve_password($user_login);
        
        if (is_wp_error($result)) {
            return new WP_Error('forgot_password_failed', $result->get_error_message(), ['status' => 400]);
        }
        
        return rest_ensure_response([
            'success' => true,
            'message' => 'Password reset email has been sent'
        ]);
    }
    
    /**
     * 重置密码
     */
    public function reset_password($request) {
        $key = $request->get_param('key');
        $login = $request->get_param('login');
        $password = $request->get_param('password');
        
        if (empty($key) || empty($login) || empty($password)) {
            return new WP_Error('missing_parameters', 'Key, login, and password are required', ['status' => 400]);
        }
        
        $user = check_password_reset_key($key, $login);
        
        if (is_wp_error($user)) {
            return new WP_Error('invalid_key', 'Invalid or expired reset key', ['status' => 400]);
        }
        
        reset_password($user, $password);
        
        return rest_ensure_response([
            'success' => true,
            'message' => 'Password has been reset successfully'
        ]);
    }
    
    /**
     * 检查 JWT 认证
     */
    public function check_jwt_auth($request) {
        // 如果安装了 JWT 插件，使用其认证
        if (class_exists('Jwt_Auth_Public')) {
            return apply_filters('jwt_auth_token_before_dispatch', $request);
        }
        
        // 简单的 Bearer Token 验证
        $auth_header = $request->get_header('authorization');
        
        if (empty($auth_header)) {
            return false;
        }
        
        list($token) = sscanf($auth_header, 'Bearer %s');
        
        if (empty($token)) {
            return false;
        }
        
        $user_id = $this->validate_jwt_token($token);
        
        if ($user_id) {
            wp_set_current_user($user_id);
            return true;
        }
        
        return false;
    }
    
    /**
     * 生成 JWT 令牌
     */
    private function generate_jwt_token($user) {
        // 如果安装了 JWT 插件，使用其生成令牌
        if (function_exists('jwt_auth_generate_token')) {
            return jwt_auth_generate_token($user);
        }
        
        // 简单的令牌生成（生产环境应使用正式的 JWT 库）
        $issued_at = time();
        $expiration = $issued_at + (DAY_IN_SECONDS * 7); // 7天过期
        
        $payload = [
            'iss' => get_bloginfo('url'),
            'iat' => $issued_at,
            'exp' => $expiration,
            'data' => [
                'user' => [
                    'id' => $user->ID,
                    'username' => $user->user_login,
                    'email' => $user->user_email
                ]
            ]
        ];
        
        // 简单编码（注意：这不是真正的 JWT，只是演示用）
        $token = base64_encode(json_encode($payload));
        
        return $token;
    }
    
    /**
     * 验证 JWT 令牌
     */
    private function validate_jwt_token($token) {
        // 如果安装了 JWT 插件，使用其验证
        if (function_exists('jwt_auth_validate_token')) {
            $result = jwt_auth_validate_token($token);
            return !is_wp_error($result) ? $result->data->user->id : false;
        }
        
        // 简单的令牌验证
        try {
            $decoded = json_decode(base64_decode($token), true);
            
            if (!isset($decoded['exp']) || $decoded['exp'] < time()) {
                return false;
            }
            
            if (!isset($decoded['data']['user']['id'])) {
                return false;
            }
            
            return $decoded['data']['user']['id'];
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * 格式化用户数据
     */
    private function format_user($user) {
        return [
            'id' => $user->ID,
            'username' => $user->user_login,
            'email' => $user->user_email,
            'first_name' => get_user_meta($user->ID, 'first_name', true),
            'last_name' => get_user_meta($user->ID, 'last_name', true),
            'display_name' => $user->display_name,
            'roles' => $user->roles,
            'avatar_url' => get_avatar_url($user->ID)
        ];
    }
}
