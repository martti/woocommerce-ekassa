<?php

/**
 * @link       https://github.com/martti/woocommerce-ekassa
 * @since      1.0.0
 *
 * @package    Woocommerce_Ekassa
 * @subpackage Woocommerce_Ekassa/admin/partials
 */

$date_begin = isset( $_GET['date_begin'] ) ? sanitize_text_field( $_GET['date_begin'] ) : date( "d.m.Y" );
$woo_products = $this->list_products( $date_begin, true );

?>

<div class="wrap">
<h1 class="wp-heading-inline">e-kassa.fi vienti</h1>

<form id="posts-filter" method="get">
<input type="hidden" name="post_type" class="post_type_page" value="product">
<input type="hidden" name="page" class="page" value="woocommerce-ekassa">
<div class="tablenav top">
	<div class="alignleft actions bulkactions">
	<label><?php _e( "Pvm", $this->plugin_name ); ?></label> <input name="date_begin" id="date_begin" type="text" size="10" value="<?php echo $date_begin ?>" />
	<input type="submit" id="doaction" class="button action" value="Näytä">
	<br class="clear">
	</div>
	<div class="alignright"><span class="displaying-num"><?php echo count($woo_products) ?> kohdetta</span>
	<input type="submit" name="wcekassa-download-csv" id="post-query-submit" class="button" value="Hae .CSV-tiedosto" />
	</div>
</div>
<table class="wp-list-table widefat fixed striped posts">
<thead>
<tr>
	<th scope="col" id="muokattu" class="manage-column" style="width:9ch">Muokattu</th>
	<th scope="col" id="tuotenumero" class="manage-column"><span>Tuotenumero</span></th>
	<th scope="col" id="tuoteryhma" class="manage-column"><span>Tuoteryhmä</span></th>
	<th scope="col" id="viivakoodi" class="manage-column"><span>Viivakoodi</span></th>
	<th scope="col" id="nimi" class="manage-column"><span>Nimi</span></th>
	<th scope="col" id="myynti" class="manage-column column-price" style="text-align:right;width:7ch"><span>Myynti</span></th>
	<th scope="col" id="ale" class="manage-column column-price" style="text-align:right;width:7ch">Ale</th>
	<th scope="col" id="alv" class="manage-column" style="text-align:right;width:5ch"><span>Alv</span></th>
	<th scope="col" id="maara" class="manage-column" style="text-align:right;width:5ch"><span>Määrä</span></th>
	<th scope="col" id="kuvaus" class="manage-column"><span>Kuvaus</span></th>
</tr>
</thead>
<tbody id="the-list">
<?php foreach ( $woo_products as $woo_product ) { ?>
<tr class="type-product hentry">
	<td class="name column-name" data-colname="Muokattu"><?php echo esc_html( $woo_product['Muokattu'] ) ?></td>
	<td class="name column-name" data-colname="Tuotenumero"><?php echo esc_html( $woo_product['Tuotenumero'] ) . (empty($woo_product['Tuotenumero']) ? '<span class="notice notice-error">TYHJÄ</span>' : "") ?></td>
	<td class="name" data-colname="Tuoteryhmä"><span title="<?php echo esc_html( $woo_product['Tuoteryhmä'] ) ?>"><?php echo wp_trim_words(esc_html( $woo_product['Tuoteryhmä'] ), 3) ?></span></td>
	<td class="name" data-colname="Viivakoodi"><?php echo esc_html( $woo_product['Viivakoodi'] ) . (empty($woo_product['Viivakoodi']) ? '<span class="notice notice-warning">TYHJÄ</span>' : "") ?></td>
	<td class="name" data-colname="Nimi"><a title="<?php echo esc_html($woo_product['Nimi']) ?>" href="<?php echo get_edit_post_link($woo_product['Id']) ?>"><?php echo wp_trim_words(esc_html($woo_product['Nimi']), 3) ?></a></td>
	<td style="text-align:right;" class="price column-price"><span class="woocommerce-Price-amount amount"><?php echo esc_html( $woo_product['Myyntihinta'] ) ?> </span><span class="woocommerce-Price-currencySymbol">€</span></td>
	<td style="text-align:right;" class="price column-price"><span class="woocommerce-Price-amount amount"><?php echo esc_html( $woo_product['Alehinta'] ) ?> </span><span class="woocommerce-Price-currencySymbol">€</span></td>
	<td style="text-align:right;" class="name column-name" data-colname="ALV"><?php echo esc_html( $woo_product['ALV'] ) ?></strong></td>
	<td style="text-align:right;" class="name column-name" data-colname="Määrä"><?php echo esc_html( $woo_product['Määrä'] ) ?></strong></td>
	<td class="name column-name" data-colname="Kuvaus"><span title="<?php echo esc_html( $woo_product['Kuvaus'] ) ?>"><?php echo wp_trim_words(esc_html( $woo_product['Kuvaus'] ), 2) ?></span></strong></td>
</tr>
<?php } ?>
</tbody>
</table>
</form>
<br class="clear">

<?php
foreach ( $woo_products as $k => $v ) {
	unset( $woo_products[$k]['Muokattu'] );
	unset( $woo_products[$k]['Id'] );
}
?>

<textarea cols="90" rows="10">
<?php echo esc_textarea( $this->csv( $woo_products ) ) ?>
</textarea>

</div>
