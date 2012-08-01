<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title>[organization]<?php __(' Invoice #', 'event_espresso'); ?>[registration_code]<?php __(' for ', 'event_espresso'); ?>[name]</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<!-- Base Stylesheet do not change or remove -->
<link rel="stylesheet" type="text/css" href="[base_url]base.css" media="screen" />
<!-- Print Style Sheet -->
<link rel="stylesheet" type="text/css" href="[base_url]css/print/<?php echo str_replace('.css', '',$invoice_css); ?>_print.css" media="print" />
<!-- Primary Style Sheet -->
<link rel="stylesheet" type="text/css" href="[base_url]css/<?php echo $invoice_css; ?>" />
<!-- Make sure the buttons don't print -->
<style type="text/css">
@media print{ .noPrint{display:none!important;height:0!important; width:0!important;} }
@page { width:100%!important; margin:0!important; padding:0!important; }
</style>
</head>
<body>

	<div class="print_button_div">
		<form>
			<input class="print_button noPrint" type="button" value=" Print Invoice " onclick="window.print();return false;" />
		</form>
		<form method="post" action="<?php echo $download_link; ?>" >
			<input class="print_button noPrint" type="submit" value=" Download PDF " />
		</form>
		<div class="clear"></div>
	</div>
