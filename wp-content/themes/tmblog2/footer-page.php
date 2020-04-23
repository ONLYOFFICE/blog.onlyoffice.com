<?php
    /**
     * The template for displaying the footer.
     *
     * Contains the closing of the id=main div and all content
     * after.  Calls sidebar-footer.php for bottom widgets.
     *
     * @package WordPress
     * @subpackage Twenty_Thirteen
     * @since Twenty Thirteen 1.0
     */
?>
    <div class="basement">
        <footer>
            <div class="narrowfooter">
                <div class="BaseFooter clearFix">
                    <div class="copyReserved">&copy; Ascensio System SIA <?php echo date("Y"); ?>. <?php _e('All rights reserved'); ?></div>
                </div>
            </div>
        </footer>
    </div>

    <?php wp_footer(); ?>
 </body>
</html>