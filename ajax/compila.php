<?php
	
include('direccionamiento.php');

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
		if ( $fila[1] == "ASR" ) {
				echo "hurraaaa!!!";
				var_dump($resultado_dir);
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
			echo "<td>" . $resultado_dir[3] . "</td>";
		}

		if ( !empty($fila[0]) ) {
			fwrite( $tabsim, $fila[0] . " " . $fila[2] . PHP_EOL);
		}

		echo "<td>$fila[0]</td>";

		echo "<td>$fila[1]</td>";
		
		echo "<td>$fila[2]</td>";
		
		echo "</tr>";

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
    }
}
?>