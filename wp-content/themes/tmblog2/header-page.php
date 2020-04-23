<?php
    /**
     * The Header for our theme.
     *
     * Displays all of the <head> section and everything up till <div id="main">
     *
     * @package WordPress
     * @subpackage Twenty_Thirteen
     * @since Twenty Thirteen 1.0
     */
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
    <head>
        <meta content="text/html; charset=<?php bloginfo( 'charset' ); ?>" />
        <title>
            <?php
                /*
                * Print the <title> tag based on what is being viewed.
                * We filter the output of wp_title() a bit -- see
                * tmblog_filter_wp_title() in functions.php.
                */
                wp_title( '-', true, 'right' );
            ?>
        </title>
        <link href='https://fonts.googleapis.com/css?family=Open+Sans:900,800,700,600,500,400,300&subset=latin,cyrillic-ext,cyrillic,latin-ext' rel="stylesheet" type="text/css" />
        <link rel="icon" href="<?php bloginfo( 'template_directory' ); ?>/images/favicon.ico" type="image/x-icon" />
        <link rel="profile" href="http://gmpg.org/xfn/11" />

        <?php add_action( 'wp_enqueue_scripts', 'add_my_theme_stylesheet' ); ?>

        <link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />

        <?php add_action( 'wp_enqueue_scripts', 'add_my_theme_page_js' ); ?>

        <?php wp_head(); ?>
    </head>

    <body <?php body_class(); ?>>
        <div class="BaseSide">
        <header>
            <div class="narrowheader">
                <div class="logo">
                    <a href="<?php echo WEB_ROOT_URL?>"></a>
                </div>
                <nav>
                    <div class="langselector">
                            <div id="LanguageSelector" class="custom-select">
                            <?php language_selector(array("en","de","fr","es","ru","it")); ?>
                        </div>
                    </div>
                </nav>
            </div>
        </header>