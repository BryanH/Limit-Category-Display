<?php
/* VARIABLES
 * $categories - list of available category terms
 * $musthaves, $canthaves - array of terms
 * $musthaves_flat, $canthaves_flat - comma-separated terms (used for hidden fields)
 */
?>

<h3>Limit Category Display settings</h3>

<form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
<?php
if( function_exists( 'wp_nonce_field' ) ) {
  wp_nonce_field('limit_cats-admin_settings' );
}
?>
<ul>
<li><label for="max_cats">Maximum number of categories to display: </label>
<input id="maxcats" maxlength="2" size="10" name="max_cats" value="<?= $max_cats ?>" /></li>
</ul>
<p><input class="button-primary" type="submit"’ name="Save" value="<?php _e('Save Options'); ?>" id="submitbutton" /></p>
</form>
