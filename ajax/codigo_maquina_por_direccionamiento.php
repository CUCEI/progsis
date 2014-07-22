<?php

	function init_op_var(){
		global $maquina, $fila, $operando_dir, $n, $r, $rr;

		$operando_dir = explode(',', $fila[2] );

		$n = $operando_dir[0];
		$r = $operando_dir[1];

		switch ( $r ) {
			case 'X':
				$rr = "00";
				break;
			case 'Y':
				$rr = "01";
				break;
			case 'SP':
				$rr = "10";
				break;
			case 'PC':
				$rr = "11";
				break;
		}

	}	

	function IDX(){
		global $maquina, $fila, $operando_dir, $n, $r, $rr;
		$postpre = array("X+","X-","+X","-X","Y+","Y-","+Y","-Y","SP+","SP-","+SP","-SP");
		$acumulador = array("A", "B", "D");
		init_op_var();
		
		if( in_array( $r, $postpre ) ){
			
			if ($n > 0) {
				$nnnn = str_pad( decbin( $n-1), 4, "0", STR_PAD_LEFT);
			} else {
				$nnnn = substr( decbin($n), strlen(decbin($n))-4, strlen(decbin($n)));
			}

			if ( strpos($r, '+') == 0 || strpos($r, '-') == 0  ) {
				switch ( $r ) {
					case '-X':
						$rr = "00";
						break;
					case '-Y':
						$rr = "01";
						break;
					case '-SP':
						$rr = "10";
						break;
					case '+X':
						$rr = "00";
						break;
					case '+Y':
						$rr = "01";
						break;
					case '+SP':
						$rr = "10";
						break;
				}
				$p = 0;
			} else {
				switch ( $r ) {
					case '-X':
						$rr = "00";
						break;
					case '-Y':
						$rr = "01";
						break;
					case '-SP':
						$rr = "10";
						break;
					case '+X':
						$rr = "00";
						break;
					case '+Y':
						$rr = "01";
						break;
					case '+SP':
						$rr = "10";
						break;
				}
				$p = 1;
			}

			$xb = $rr . "1" . $p . $nnnn;

		} else if(  in_array( $n, $acumulador ) ){
			
			switch ( $n ) {
				case 'A':
					$nn = "00";
					break;
				case 'B':
					$nn = "01";
					break;
				case 'D':
					$nn = "10";
					break;
			}

			$xb = "111" . $rr . "1" . $nn;

		} else if ( $n >= -16 && $n <= 15 ) {			

			if ($n > 0) {
				$nnnnn = str_pad( decbin( $n), 5, "0", STR_PAD_LEFT);
			} else {
				$nnnnn = substr( decbin($n), strlen(decbin($n))-5, strlen(decbin($n)));
			}

			$xb = $rr . "0" . $nnnnn;
		}

		$xb = dechex( bindec( $xb ) );
		$maquina = str_replace("xb", $xb, $maquina);

	}

	function IDX1(){
		global $maquina, $fila, $operando_dir, $n, $r, $rr;
		init_op_var();

		$z = 0;

		if ($n > 0) {
			$s = 0;
		} else {
			$s = 1;
		}
		
		$xb = "111" . $rr . "0" . $z . $s;
		echo dechex(147) . " /";
		$xb = dechex( bindec( $xb ) );
		$maquina = str_replace("xb", $xb, $maquina);

		$ff = dechex(abs($n));
		if ($s == 1) {
			$ff = substr(decbin($n), strlen(decbin($n)) - 8, strlen(decbin($n)));
			$ff = dechex(bindec($ff));
		}
		$maquina = str_replace("ff", $ff, $maquina);
	}

	function IDX2(){
		init_op_var();
		global $maquina, $fila, $operando_dir, $n, $r, $rr;

		$z = 1;

		$s = 0; 

		$xb = "111" . $rr . "0" . $z . $s;

		$xb = dechex( bindec( $xb ) );
		$maquina = str_replace("xb", $xb, $maquina);

		$eeff = dechex(abs($n));
		$eeff_format = substr($eeff, 0,2) . " " . substr($eeff, 2,4);
		$maquina = str_replace("ee ff", $eeff_format, $maquina);
	}

	function REL8(){
		global $contador, $fila, $maquina;

		switch ( $fila[2][0] ) {
			case '$':
				$op_res = hexdec( $fila[2] );
				break;
			
			case '@':
				$op_res = octdec( $fila[2] );
				break;
			
			case '%':
				$op_res = bindec( $fila[2] );
				break;
			
			default:
				$op_res = $fila[2];
				break;
		}



		$sig_contador = hexdec($contador) + 2;

		$rr = $op_res - $sig_contador;

		echo dechex($rr);
		echo "$maquina";
		$maquina = str_replace("rr", dechex($rr), $maquina);

	}
	function REL16(){
			global $contador, $fila, $maquina;

		switch ( $fila[2][0] ) {
			case '$':
				$op_res = hexdec( $fila[2] );
				break;
			
			case '@':
				$op_res = octdec( $fila[2] );
				break;
			
			case '%':
				$op_res = bindec( $fila[2] );
				break;
			
			default:
				$op_res = $fila[2];
				break;
		}



		$sig_contador = hexdec($contador) + 4;

		$qqrr = $op_res - $sig_contador;
		$qqrr = dechex($qqrr);
		$qqrr = str_pad( $qqrr, 4, "0", STR_PAD_LEFT);
		$qqrr = substr($qqrr, 0,2) . " " . substr($qqrr, 2,4);
		echo "$maquina";
		$maquina = str_replace("qq rr", $qqrr, $maquina);
	}
?>