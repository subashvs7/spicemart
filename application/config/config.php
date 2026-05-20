<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Auto-detect base URL — works at root, in a subdirectory, locally, and behind Hostinger's proxy
if (!empty($_SERVER['HTTP_HOST'])) {
    // Detect HTTPS: covers direct SSL, Hostinger/CDN proxies, and port 443
    $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'
          || !empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https'
          || !empty($_SERVER['HTTP_X_FORWARDED_SSL'])   && $_SERVER['HTTP_X_FORWARDED_SSL']   === 'on'
          || isset($_SERVER['SERVER_PORT'])              && (int)$_SERVER['SERVER_PORT']        === 443;
    $protocol = $https ? 'https' : 'http';
    $host     = $_SERVER['HTTP_HOST'];
    $dir      = dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php');
    $base     = rtrim(str_replace('\\', '/', $dir), '/') . '/';
    $config['base_url'] = $protocol . '://' . $host . $base;
} else {
    $config['base_url'] = 'http://localhost/spicemart/';
}

$config['index_page'] = '';

$config['uri_protocol'] = 'AUTO';

$config['url_suffix'] = '';

$config['language'] = 'english';

$config['charset'] = 'UTF-8';

$config['enable_hooks'] = FALSE;

$config['subclass_prefix'] = 'MY_';

$config['composer_autoload'] = FALSE;

$config['permitted_uri_chars'] = 'a-z 0-9~%.:_\-';

$config['allow_get_array'] = TRUE;

$config['enable_query_strings'] = FALSE;

$config['controller_trigger'] = 'c';

$config['function_trigger'] = 'm';

$config['directory_trigger'] = 'd';

$config['log_threshold'] = 0;

$config['log_path'] = '';

$config['log_file_extension'] = '';

$config['log_file_permissions'] = 0644;

$config['log_date_format'] = 'Y-m-d H:i:s';

$config['error_views_path'] = '';

$config['cache_path'] = '';

$config['cache_query_string'] = FALSE;

$config['encryption_key'] = 'SM_spicemart_secret_key_2024_xyz';

$config['sess_driver'] = 'files';

$config['sess_cookie_name'] = 'sm_session';

$config['sess_expiration'] = 7200;

$config['sess_save_path'] = NULL;

$config['sess_match_ip'] = FALSE;

$config['sess_time_to_update'] = 300;

$config['sess_regenerate_destroy'] = FALSE;

$config['cookie_prefix'] = 'sm_';

$config['cookie_domain'] = '';

$config['cookie_path'] = '/';

$config['cookie_secure'] = FALSE;

$config['cookie_httponly'] = FALSE;

$config['standardize_newlines'] = FALSE;

$config['global_xss_filtering'] = FALSE;

$config['csrf_protection'] = FALSE;

$config['csrf_token_name'] = 'csrf_token_name';

$config['csrf_cookie_name'] = 'csrf_cookie_name';

$config['csrf_expire'] = 7200;

$config['csrf_regenerate'] = TRUE;

$config['csrf_exclude_uris'] = array('cart-ajax');

$config['compress_output'] = FALSE;

$config['time_reference'] = 'local';

$config['rewrite_short_tags'] = FALSE;

$config['proxy_ips'] = '';
