<table class="awpcp-table awpcp-transaction-items-table">
    <thead>
        <tr>
            <th class="item"><?php esc_html_e( 'Item', 'another-wordpress-classifieds-plugin' ); ?></th>
            <th class="amount"><?php esc_html_e( 'Amount', 'another-wordpress-classifieds-plugin' ); ?></th>
        </tr>
    </thead>

    <tbody>
    <?php foreach ($transaction->get_items() as $item): ?>

        <tr>
            <td class="item" data-title="<?php esc_attr_e( 'Item', 'another-wordpress-classifieds-plugin' ); ?>">
                <?php echo esc_html( $item->name ); ?><br>
                <?php echo wp_kses_post( $item->description ); ?>
            </td>
            <td class="amount" data-title="<?php esc_attr_e( 'Amount', 'another-wordpress-classifieds-plugin' ); ?>">
            <?php if ( $item->payment_type === AWPCP_Payment_Transaction::PAYMENT_TYPE_MONEY ): ?>
                <?php echo esc_html( awpcp_format_money( $item->amount ) ); ?>
            <?php else: ?>
                <?php echo esc_html( number_format( $item->amount, 0 ) ); ?>
            <?php endif; ?>
            </td>
        </tr>

    <?php endforeach; ?>
    </tbody>

    <tfoot>
        <?php $totals = $transaction->get_totals(); ?>

        <?php if ($show_credits): ?>
        <tr>
            <td class="row-header"><?php esc_html_e( 'Total Amount (credit)', 'another-wordpress-classifieds-plugin' ); ?></td>
            <td class="amount"><?php echo esc_html( number_format( $totals['credits'], 0 ) ); ?></td>
        </tr>
        <?php endif; ?>

        <tr>
            <?php $label = sprintf( '%s (%s)', _x( 'Total Amount', 'transaction items', 'another-wordpress-classifieds-plugin' ), awpcp_get_currency_symbol() ); ?>
            <td class="row-header"><?php echo esc_html( $label ); ?></td>
            <td class="amount"><?php echo esc_html( awpcp_format_money( $totals['money'] ) ); ?></td>
        </tr>
    </tfoot>
</table>
