<?php
/**
 * Manejador de idiomas con cookies
 * Detecta el parámetro lang de la URL y lo guarda en una cookie
 * Si no hay parámetro pero hay cookie, redirige con el parámetro
 */

// Idiomas permitidos
$allowed_languages = ['es', 'en', 'pt', 'zh'];
$default_language = 'es';

// Obtener idioma de la URL
$lang_from_url = isset($_GET['lang']) ? $_GET['lang'] : null;

// Obtener idioma de la cookie
$lang_from_cookie = isset($_COOKIE['site_lang']) ? $_COOKIE['site_lang'] : null;

// Determinar el idioma actual
$current_lang = $default_language;

if ($lang_from_url) {
    // Validar que el idioma de la URL sea válido
    if (in_array($lang_from_url, $allowed_languages)) {
        $current_lang = $lang_from_url;
        // Guardar en cookie (30 días de duración)
        setcookie('site_lang', $current_lang, time() + (30 * 24 * 60 * 60), '/');
    }
} elseif ($lang_from_cookie) {
    // Si hay cookie y es válida, usarla
    if (in_array($lang_from_cookie, $allowed_languages)) {
        $current_lang = $lang_from_cookie;
    }
}

// Si no hay parámetro lang en la URL pero hay cookie válida, redirigir con el parámetro
// Solo redirigir si no estamos en una petición AJAX o si no hay otros parámetros importantes
if (!$lang_from_url && $lang_from_cookie && in_array($lang_from_cookie, $allowed_languages)) {
    // Evitar redirección en peticiones AJAX
    $is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    
    if (!$is_ajax) {
        $current_page = basename($_SERVER['PHP_SELF']);
        $query_string = $_SERVER['QUERY_STRING'];
        
        // Remover lang de query string si existe
        parse_str($query_string, $params);
        unset($params['lang']);
        
        // Construir nueva URL
        $new_query = http_build_query($params);
        $redirect_url = $current_page . '?lang=' . $lang_from_cookie;
        if ($new_query) {
            $redirect_url .= '&' . $new_query;
        }
        
        // Redirigir solo si hay cookie y no hay parámetro lang
        header('Location: ' . $redirect_url);
        exit;
    }
}

// Variable global para usar en otros archivos PHP
$GLOBALS['current_lang'] = $current_lang;