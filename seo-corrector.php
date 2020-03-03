<?php
/*
 * Plugin Name: SEO corrector
 * Description: Корректирует СЕО
 * Plugin URI: https://github.com/badman666/seo-corrector
 * Version: 0.0.2
 * Author: BadMan666
*/

/**
 * Описание плагина в панели администратора
 */
function SCAdminContent()
{
?>
    <div class="sc-wrap">
        <div class="sc-content">
            <h2>SEO corrector</h2>
            <p>Что делает данный плагин:</p>
            <ol>
                <li>301 redirect /bez-rubriki/ -> /</li>
                <li>Все канонические ссылки со страниц ведут на главную страницу раздела</li>
                <li>
                    Если в разделе нет контента или раздел с дополнительной станцией метро,
                    то ставится тег <?= htmlspecialchars('<meta name="robots" content="noindex,follow"/>')?>
                </li>
            </ol>
        </div>
    </div>
<?php
}

/**
 * Пункт меню в панели администратора
 */
function SCAdminMenu()
{
    add_menu_page(
        'SEO corrector',
        'SEO corrector',
        8,
        basename(__FILE__),
        'SCAdminContent',
        'dashicons-lightbulb'
    );
}
add_action('admin_menu', 'SCAdminMenu');

/**
 * 301 редирект со страницы "без рубрики"
 */
function SCRedirect()
{
    $path = htmlspecialchars($_SERVER['REQUEST_URI']);
    $search = 'bez-rubriki';
    
    if (strpos($path, $search) !== false) {
        wp_redirect('/', 301);
        exit();
    }
}
add_action('init', 'SCRedirect');

/**
 * Изменение поведения Yoast SEO на хук wpseo_canonical
 * Все канонические ссылки страниц раздела ведут на главную страницу раздела
 * @param $canonical
 * @return string
 */
function SCEditCanonical($canonical)
{
    if (strpos($canonical, 'page/') !== false) {
        $canonical = stristr($canonical, 'page/', true);
    }

    return $canonical;
}
add_filter('wpseo_canonical', 'SCEditCanonical', 10, 1);

/**
 * Изменение поведения Yoast SEO на хук wpseo_robots
 * Если пустой раздел или раздес с дополнительным метро запрещаем индексацию
 * @param $content
 * @return string
 */
function SCBadPage($content)
{
    $extraSubway = false;
    $path = htmlspecialchars($_SERVER['REQUEST_URI']);
    $search = 'extra';

    if (strpos($path, $search) !== false) {
        $extraSubway = true;
    }

    if (!have_posts() || $extraSubway) {
        $content = 'noindex,follow';
    }

    return $content;
}
add_filter('wpseo_robots', 'SCBadPage', 999);
