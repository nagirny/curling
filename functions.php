<?php
/*register_shutdown_function( function() {
	if( error_get_last() ) 
		var_export(error_get_last());
});*/

// вывод отладочной информации в консоль
function prn( $var, $label = '' ) {
	$str = json_encode(print_r ($var, true));
	echo "<script>console.group('".$label."');console.log('".$str."');console.groupEnd();</script>";
}

// вывод '-' вместо пустоты
function echo_null( $str ) {
	echo is_null($str) ? '-' : sprintf( "%d" , $str);
	return;
}
?>
