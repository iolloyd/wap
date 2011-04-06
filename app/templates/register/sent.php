<?php
ob_start();
iconv_set_encoding('internal_encoding', 'UTF-8');
iconv_set_encoding('output_encoding', 'UTF-8');
?>
<DeliveryResponse ack='true'/>

<?php ob_end_flush(); flush(); ?>

