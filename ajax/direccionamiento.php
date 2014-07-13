<?php

function direccionamiento( $codop, $operando){

	$tabop = fopen("tabop.txt", "rb");
	$resultado = array();

	if ( $codop === "ORG" || $codop === "END" || $codop === "EQU" ) {
		$resultado[] = "DIRECTIVA";
		return $resultado;
	}

	while ( ($linea = fgets($tabop)) !== false ) {
		$elementos = explode("|", $linea);

		if ( $elementos[0] === $codop ) {
			$tabsim = fopen("tabsim.asm", "rb");

			while ( ( $etiqueta =fgets($tabsim) ) != false ) {

				$elementos_tabsim = explode(" ", $etiqueta);

				if ( $elementos_tabsim[0] == $operando ) {
					$operando = $elementos_tabsim[1];
					$resultado[] = "Direccionamiento relativo de " . substr( $elementos[1], 3) . "bits";
					$resultado[] = $elementos[1];
					$resultado[] = $elementos[3];
					$resultado[] = $elementos[2];
					$resultado[] = $operando;

					fclose( $tabsim );
				}
			}

			if ( count($resultado) != 0 ) {
				break;
			}
			$dir = tipo_direccionamiento( $operando );
			if ( $codop == "ASR" ) {
				var_dump($dir);
			}
			if ( substr( $elementos[1], 0, 3) === "REL" ) {
			 	$resultado[] = "Direccionamiento relativo de " . substr( $elementos[1], 3) . "bits";
				$resultado[] = $elementos[1];
				$resultado[] = $elementos[3];
				$resultado[] = $elementos[2];
				fclose( $tabsim );
				break;
			 } else if ( substr($dir[0], 0, 3) === substr($elementos[1], 0, 3) ) {
				$resultado[] = $dir[1];
				$resultado[] = $elementos[1];
				$resultado[] = $elementos[3];
				$resultado[] = $elementos[2];
				fclose( $tabsim );
				break;
			} elseif ( $dir[0] == "ERROR" ) {
				$resultado[] = $dir[0];
				$resultado[] = $dir[1];
				fclose( $tabsim );
				break;
			}
			fclose( $tabsim );

		}
	}

	return $resultado;
}


function tipo_direccionamiento( $operando ){

	switch ( $operando[0] ) {
		case '$':
			$op_res = hexdec( $operando );
			break;
		
		case '@':
			$op_res = octdec( $operando );
			break;
		
		case '%':
			$op_res = bindec( $operando );
			break;
		
		default:
			$op_res = substr($operando, 1);
			break;
	}

	if ( empty($operando) ) {
		$resultado[] = "INH";
		$resultado[] = "Direccionamiento inherente";
	} else if ( $operando[0] === '#' ) {
		$resultado[] = "IMM";
		$resultado[] = "Direccionamiento inmediato";
		switch ( $operando[1] ) {
			case '$':
				$op_res = hexdec( $operando );
				break;
			
			case '@':
				$op_res = octdec( $operando );
				break;
			
			case '%':
				$op_res = bindec( $operando );
				break;
			
			default:
				$op_res = substr($operando, 1);
				break;
		}

		if ( $op_res < 255) {
			$resultado[0] = $resultado[0]."8";
		} else if ( $op_res > 255 && $op_res <= 65535 ) {
			$resultado[0] = $resultado[0]."16";
		}

	} else if ( !strpos( $operando, ',') && !strpos( $operando, '[') && !strpos( $operando, ']') ) {
		if ( $op_res <= 255 && $op_res >= 0 ) {
			$resultado[] = "DIR";
			$resultado[] = "Direccionamiento directo";
		} else if ( $op_res <= 65535 && $op_res > 255 ) {
			$resultado[] = "EXT";
			$resultado[] = "Direccionamiento extendido";
		}
		
	
	}else if ( strpos($operando, ',') ) {

		$sub_op = explode(',', $operando);
		$second = explode(']', $sub_op[1]);
		$sub_op_num = substr( $sub_op[0], 1);

		if ( $sub_op[1] != 'X' 
			&& $sub_op[1] != 'Y'
			&& $sub_op[1] != 'SP'
			&& $sub_op[1] != 'PC'
			&& $second[0] != 'X'
			&& $second[0] != 'Y'
			&& $second[0] != 'SP'
			&& $second[0] != 'SP+'
			&& $second[0] != 'SP-'
			&& $second[0] != '+SP'
			&& $second[0] != '-SP'
			&& $second[0] != 'pc' ) {

		 	$resultado[] = "ERROR";
			$resultado[] = "Operador incorrecto";

		} else if ( $sub_op[0][0] === '[' && $sub_op[1][ strlen($sub_op[1])-1 ] === ']' ) {
			if ($sub_op_num === 'D') {
			 	$resultado[] = "IDX";
				$resultado[] = "Direccionamiento indexado ";
			 } else if ( $sub_op_num >= 0 && $sub_op_num[0] <= 65535 ) {
				
				$resultado[] = "[IDX2]";
				$resultado[] = "Direccionamiento indexado de 16bits indirecto";
			}
		
		}else if ( $sub_op[0] >= 1 && $sub_op[0] <= 8 && strpos( $sub_op[1], "SP") >= 0 && strlen( $sub_op ) == 3 ) {
			
			if ( $sub_op[1][0] == '-' ) {

				$resultado[] = "IDX";
				$resultado[] = "Direccionamiento indexado pre decremento";
				
			} else if ( $sub_op[1][0] == '+' ) {
				$resultado[] = "IDX";
				$resultado[] = "Direccionamiento indexado pre incremento";
			} else if ( $sub_op[1][2] == '-' ) {
				$resultado[] = "IDX";
				$resultado[] = "Direccionamiento indexado post decremento";
			} else if ( $sub_op[1][2] == '+' ) {
				$resultado[] = "IDX";
				$resultado[] = "Direccionamiento indexado post incremento";
			}

		} else if ( $sub_op[0] === 'A' || $sub_op[0] === 'B' || $sub_op[0] === 'D' ) {
			$resultado[] = "IDX";
			$resultado[] = "Direccionamiento indexado de acumulador";
		} else if ( $sub_op[0] >= -16 && $sub_op[0] <= 15 ) {
			
			$resultado[] = "IDX";
			$resultado[] = "Direccionamiento indexado de 5bits";

		} else if ( $sub_op[0] >= -256 && $sub_op[0] <= -17 ) {
			$resultado[] = "IDX1";
			$resultado[] = "Direccionamiento indexado de 9bits";
		} else if ( $sub_op[0] >= 256 && $sub_op[0] <= 65535 ) {
			$resultado[] = "IDX2";
			$resultado[] = "Direccionamiento indexado de 16bits";
		}
		
	} else {
		echo "string";
		$resultado[] = "ERROR";
		$resultado[] = "Operador incorrecto";
	}

	return $resultado;
}

?>