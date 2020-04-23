<html>

<head>
<style type="text/css">
   iframe { display:block; width:100%; border:none; height:100%; overflow: hidden;}
   .wrap {height: 768px;}
 </style>
</head>

<body>
    <div class="wrap">
        <div id="icon-wpml" class="icon32"><br/></div>
        <h2><?php echo __('Translation Analytics', 'wpml-translation-analytics') ?></h2>
        <?php
            $WPML_Translation_Analytics->show_messages();
            $WPML_Translation_Analytics->show_translation_analytics_dashboard();
        ?>
    </div>
</body>
</html>
