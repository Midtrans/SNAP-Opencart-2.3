<?php echo $header; ?>
<div class="container">
    <div class="row"><?php echo $column_left; ?>
        <?php if ($column_left && $column_right) { ?>
        <?php $class = 'col-sm-6'; ?>
        <?php } elseif ($column_left || $column_right) { ?>
        <?php $class = 'col-sm-9'; ?>
        <?php } else { ?>
        <?php $class = 'col-sm-12'; ?>
        <?php } ?>

        <div id="content" class="<?php echo $class; ?>"><?php echo $content_top; ?>
            <div class="container"><?php echo $content_top; ?>
                <h1>Order received</h1>
                <p>Thank you. Your order has been received.</p>
                <p>Order Number : <strong><?=$data['order_id']?></strong></p>
                <p>Total : <strong><?=$data['total']?></strong></p>                
                <p>Payment Method : <strong><?=$data['payment_method']?></strong></p>
                <p>Payment Page : <a href="<?=$data['comment']?>" target="blank"><?=$data['comment']?></a></p>
                <div class="buttons">
                    <div class="pull-right"><a href="<?php echo $continue; ?>" class="btn btn-primary"><?php echo $button_continue; ?></a></div>
                </div>
        </div>
            <?php echo $content_bottom; ?>
        </div>
        <?php echo $column_right; ?>
    </div>
</div>
<?php echo $footer; ?>