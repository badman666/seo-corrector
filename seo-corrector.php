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

/**
 * 301 редирект со старых ссылок после обновения плагина
 * Update 20.07.2020 - BadMan
 */
function SCredirectOldUrl()
{
    $currentUrl = $_SERVER['REQUEST_URI'];
    // метро
    $newUrl = str_replace(
        ['/subway/', '/underground/', '/subways/','/stantsiya-metro/', '/metro/', '/metro-station/', '/st-metro/', '/city-metro/'],
        '/station/',
        $currentUrl
    );
    // дополнительное метро
    $newUrl = str_replace(
        ['/subwayextra/', '/underground-extra/', '/subways-extra/'],
        '/stationextra/',
        $newUrl
    );
    // район
    $newUrl = str_replace(
        ['/area/', '/district/', '/arays/', '/rajon/', '/locations/', '/raion/', '/city-loc/', '/rayon/'],
        '/locate/',
        $newUrl
    );
    // услуги
    $newUrl = str_replace(
        ['/myservices/', '/services-for-sex/', '/sex-services/', '/intim-uslugi/', '/services/', '/types-services/', '/uslugi/', '/girls-services/'],
        '/serv/',
        $newUrl
    );
    // национальность
    $newUrl = str_replace(
        ['/girl-nation/', '/nationality/', '/sex-nations/', '/natsionalnost/', '/nacionalnost/', '/girls-nation/'],
        '/nation/',
        $newUrl
    );
    // цвет волос
    $newUrl = str_replace(
        ['/girl-hair/', '/girls-hair/', '/volosy/', '/hair/', '/volosi/'],
        '/hair-color/',
        $newUrl
    );
    // возраст
    $newUrl = str_replace(
        ['/girl-age/', '/years-old/', '/girls-age/', '/vozrast/', '/age/'],
        '/myage/',
        $newUrl
    );
    // цена
    $newUrl = str_replace(
        ['/girl-price/', '/cash/', '/girls-coast/', '/tseny/', '/price/', '/ceni/', '/girls-price/', '/ceny/'],
        '/myprice/',
        $newUrl
    );

    if ($currentUrl != $newUrl) {
        wp_redirect($newUrl, 301);
        exit();
    }
}
add_action('plugins_loaded', 'SCredirectOldUrl');
