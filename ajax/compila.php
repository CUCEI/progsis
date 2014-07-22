<?php
	
include('direccionamiento.php');
include('codigo_maquina_por_direccionamiento.php');

if ( $_POST['llamada'] = "compila") {
	//echo "hola";
}
if ($_FILES["file"]["error"] > 0) {
  echo "Error: " . $_FILES["file"]["error"] . "<br>";
} else {
	$archivo = fopen($_FILES["file"]["tmp_name"], "rb");
	$tabsim = fopen("tabsim.asm", "w+");
	$contador = 0;

	echo "Carga: " . $_FILES["file"]["name"] . "<br>";
	echo "Tipo: " . $_FILES["file"]["type"] . "<br>";
	echo "Tamaño: " . ($_FILES["file"]["size"] / 1024) . " kB<br>";
	echo "almacenado en: " . $_FILES["file"]["tmp_name"] . "<br>";
	
	?>
	<table class="table">	
		<tr>
			<th>Contador</th>
			<th>Código Máquina</th>
			<th>Etiqueta</th>
			<th>Codop</th>
			<th>Operando</th>
		</tr>
	<?php

	while ( ($linea = fgets($archivo)) !== false ) {

		for( $i = 0 ; 4 > ( strlen($contador) ) ; $i++ ){
			$contador = "0" . $contador;
		}

		$fila = preg_split('/[\s]+/', trim($linea, " \n"));
		
		if ( ( count($fila) === 1 && empty($fila[0]) ) || ( count($fila) <= 2 && empty($fila[0]) && empty($fila[0]) ) ) {
			continue;
		}

		$contador_array[] = $contador;

		$resultado_dir = direccionamiento($fila[1], $fila[2]);
		if ( !empty($resultado_dir[4]) ) {
			$fila[2] = $resultado_dir[4];
		}
		switch ( $fila[2][0] ) {
		case '$':
			$operando = hexdec( $fila[2] );
			break;
		
		case '@':
			$operando = octdec( $fila[2] );
			break;
		
		case '%':
			$operando = bindec( $fila[2] );
			break;
		
		case '#':
			if ( $fila[2][1] == '$') {
				$operando = hexdec( $fila[2] );
			} else if ( $fila[2][1] == '@') {
				$operando = octdec( $fila[2] );
			} else if ( $fila[2][1] == '%') {
				$operando = bindec( $fila[2] );
			} else 
			$operando = substr( $fila[2], 1);
			break;

		default:
			$operando = $fila[2];
			break;
		}

		echo "<tr>";
		
		if ( $fila[1] == "EQU" ){
			$equ_val = dechex( $operando );
			$equ_val = strtoupper($equ_val);

			for( $i = 0 ; 4 > ( strlen($equ_val) ) ; $i++ ){
				$equ_val = "0" . $equ_val;
			}

			echo "<td>" . $equ_val . "</td>";
		} else {
			$contador = strtoupper($contador);

			echo "<td>$contador</td>";
		}
		
		if ( substr($resultado_dir[1], 0, 3) == "IMM" || substr($resultado_dir[1], 0, 3) == "DIR" || substr($resultado_dir[1], 0, 3) == "EXT") {
			
			$maquina = dechex($operando);

			for( $i = 0 ; 4 > ( strlen($maquina) ) && strlen($maquina) > 2 ; $i++ ){
				$maquina = "0" . $maquina;
			}if ( strlen($maquina) < 2 ) {
				$maquina = "0" . $maquina;
			}
			if ( strlen($maquina) > 2 ) {
				$maquina =substr($resultado_dir[3], 0, 2) . " " . substr($maquina, 0, 2) . " " . substr($maquina, 2, 4) ;
			}else{
				$maquina =substr($resultado_dir[3], 0, 2) . " " . substr($maquina, 0, 2);
			}
			
			echo "<td>" . $maquina . "</td>";
		} else{
		
			$maquina = $resultado_dir[3];

			call_user_func($resultado_dir[1]);

			echo "<td>" . $maquina . "$resultado_dir[1] </td>";
		}

		if ( !empty($fila[0]) ) {
			fwrite( $tabsim, $fila[0] . " " . $fila[2] . PHP_EOL);
		}

		echo "<td>$fila[0]</td>";

		echo "<td>$fila[1]</td>";
		
		echo "<td>$fila[2]</td>";
		
		echo "</tr>";

		$codop_array[] = $fila[1];

		if ( $fila[1] === "END" ) {
			echo "</table>";
			break;
		} else if ( $fila[1] === "ORG" ) {
			$contador = dechex( $operando );
		} else if( $fila[1] === "DB" || $fila[1] === "DC.B" || $fila[1] === "FCB" ){
			$contador = dechex( hexdec($contador) + 1 );
		} else if( $fila[1] === "DW" || $fila[1] === "DC.W" || $fila[1] === "FDB" ){
			$contador = dechex( hexdec($contador) + 2 );
		} else if ( $fila[1] === "FCC" ) {
			$contador = dechex( strlen( $operando ) -2 + hexdec( $contador ) );
		} else if( $fila[1] === "DS" || $fila[1] === "DS.B" || $fila[1] === "RMB" ){
			$contador = dechex( hexdec($contador) + $operando );
		} else if( $fila[1] === "DS.W" || $fila[1] === "RMW" ){
			$contador = dechex( hexdec($contador) + ($operando*2) );
		} else if( $fila[1] != "EQU" ){
			$contador = $contador = dechex( hexdec($contador) + count( explode( " ", $maquina) ) );
		}
		$maquina_array[] = $maquina;
		
    }

    

    $objeto = fopen("objeto.o", "w+");
    $s0 = "S0 ";
    $i = 0;

    while ( $i < strlen($_FILES["file"]["name"]) ) {
    	$car = $_FILES["file"]["name"][$i];
    	if ( !is_numeric($car) ) {
    		$car = strtoupper($car);
    	}
    	
    	$car = ord($car);

    	if ( ($i + 1) === strlen($_FILES["file"]["name"]) ) {
    		$car = str_pad( $car, 2, "0", STR_PAD_LEFT);
    		$s0 = $s0 . dechex($car);
    	} else {
    		$car = str_pad( $car, 2, "0", STR_PAD_LEFT);
    		$s0 = $s0 . dechex($car) . " ";
    	}
    	$i++;
    }

    $s0_array = explode(" ", $s0);
    $s0_lengt = (count($s0_array) + 2);

    $s0 = substr($s0, 0, 3) . str_pad( $s0_lengt, 2, "0", STR_PAD_LEFT) . " 00 00 " . substr($s0, 3);

    $s0_raw_data = substr($s0, 3);
    $s0_raw_data_array = explode(" ", $s0_raw_data);

    $checksum = hexdec($s0_raw_data_array[0]);
    
    $i = 0;
    foreach ($s0_raw_data_array as $s0_byte) {

    	if ($i == 0) {
    		$i++;
    		continue;
    	}

    	//echo hexdec($s0_byte) . " " ;
    	$checksum = $checksum + hexdec($s0_byte);
    }

    $checksum = hexdec("FFF") - $checksum;
    $checksum = dechex($checksum);
    $checksum = substr($checksum, strlen($checksum) - 2, strlen($checksum));

    //var_dump(dechex($checksum));
    fwrite($objeto, $s0 . "\t\t" . $checksum . "\n");

    $is_next_line = 1;
    $is_more_than_19 = 0;

    for ($i=0; $i < count($contador_array) ; $i++) { 

    	if ( $is_next_line ) {
    		//$s1[$i] = "S1 " . substr($contador_array[$i], 0, 2) . " " . substr($contador_array[$i], 2) " ";
    		$is_next_line = 0;
    		$is_registry_finished = 0;
    		$lng = "00";
    		$dir = $contador_array[$i];
    		$data = "";
    	}

    	$data_lenght = count( explode(" ", $data ) ) + count( explode( " " , $maquina_array[$i] ) );

    	if ( $is_more_than_19 ) {

    		$maquina_array[$i] = $data_left . ( ( empty($maquina_array[$i]) ) ? "" : " " ) . $maquina_array[$i] ;
    		$dir = hexdec($dir) + count( explode(" ", $data_left) );
    		$dir = dechex($dir);
    		$dir = strtoupper($dir);
    		die(var_dump($dir));

    		$data_left = "";
    		$is_more_than_19 = 0;
    	}

    	if ( $data_lenght > 16 ) {
    		$is_more_than_19 = 1;
    		$bytes_left = 17 - count( explode(" ", trim($data) ) );
    		$data_left = substr( $maquina_array[$i] , $bytes_left * 3);
    		$maquina_array[$i] = substr( $maquina_array[$i], 0, ($bytes_left * 3) - 1 );
    	}
    	if ( $data_lenght == 15) {
    		echo "sdfsd";
    		$is_more_than_19 = 0;
    	}

    	$data = $data . " " . $maquina_array[$i] ;

    	if ( 	empty($maquina_array[$i]) || 
    			$i == ( count( $contador_array ) -1 ) || 
    			$is_more_than_19 == 1 ||
    			$data_lenght == 16 ) {

    		$is_next_line = 1;

    		$is_registry_finished = 1;
    		

    	}
    	if ( empty($maquina_array[$i-1]) ) {
    		continue;
    	}
    	if ( $is_registry_finished ) {
    		
    		$data_size = count( explode(" ", trim($data) ) );
    		var_dump($data_size);
    		$lng = $data_size + 3;
    		$lng = dechex( $lng );
    		$lng = str_pad( $lng, 2, "0", STR_PAD_LEFT);
    		$dir = substr( $dir, 0, 2) . " " . substr( $dir, 2);
    		$s1_raw_data = "$lng $dir $data";
    		$s1_raw_data_array = explode(" ", trim($s1_raw_data));

    		$checksum = "";
		     
		    foreach ($s1_raw_data_array as $s1_byte) {

		    	echo hexdec($s1_byte) . " " ;
		    	$checksum = $checksum + hexdec($s1_byte);
		    	
		    	//die(var_dump($checksum));
		    }
		    
		    $checksum = hexdec("FFF") - $checksum;

		    $checksum = dechex($checksum);
		    
		    $checksum = substr($checksum, strlen($checksum) - 2, strlen($checksum));
		    //die(var_dump($checksum));
		    echo "$checksum " . dechex(147);
    		

    		$s1[$i] = "S1 $lng $dir $data $checksum";	
    		echo "<br> $s1[$i] <br>";
    		fwrite($objeto, $s1[$i] . "\n");
    	}
    	//

    	//fwrite($objeto, $s1[$i]);

    }

    fwrite($objeto, "S9 03 00 00\tFC");
}
?>