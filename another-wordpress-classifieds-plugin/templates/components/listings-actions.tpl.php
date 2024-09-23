<?php
/**
 * @package AWPCP\Templates\Frontend
 */

?><div class="awpcp-listing-actions-component">
<?php
foreach ( $actions as $action ) :
    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    echo $action->render( $listing );
endforeach;
?>
</div>
