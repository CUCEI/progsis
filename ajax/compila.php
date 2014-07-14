<?php
	
include('direccionamiento.php');

if ( $_POST['llamada'] = "compila") {
	//echo "hola";
}
if ($_FILES["file"]["error"] > 0) {
  echo "Error: " . $_FILES["file"]["error"] . "<br>";
} else {
	$archivo = fopen($_FILES["file"]["tmp_name"], "rb");

	echo "Carga: " . $_FILES["file"]["name"] . "<br>";
	echo "Tipo: " . $_FILES["file"]["type"] . "<br>";
	echo "Tamaño: " . ($_FILES["file"]["size"] / 1024) . " kB<br>";
	echo "almacenado en: " . $_FILES["file"]["tmp_name"] . "<br>";
	?>
	<table class="table">	
		<tr>
			<th>Tipo de Direccionamiento</th>
			<th>Código Máquina</th>
			<th>Etiqueta</th>
			<th>Codop</th>
			<th>Operando</th>
		</tr>
	<?php
	while ( ($linea = fgets($archivo)) !== false ) {
		$fila = preg_split('/[\s]+/', trim($linea, " \n"));
		
		if ( ( count($fila) === 1 && empty($fila[0]) ) || ( count($fila) <= 2 && empty($fila[0]) && empty($fila[0]) ) ) {
			continue;
		}

		$resultado_dir = direccionamiento($fila[1], $fila[2]);

		echo "<tr>" . count($fila);
		
		if ( $resultado_dir[0] === "DIRECTIVA" ) {
			echo "<td>" . $resultado_dir[0] . "</td>";
		} else if ( $resultado_dir[0] === "ERROR" ) {
			echo "<td>" . $resultado_dir[0] . " " . $resultado_dir[1] . "</td>";
		} else {
			echo "<td>" . $resultado_dir[0] . ", (" . $resultado_dir[1] . "), " . $resultado_dir[2] . " bytes</td>";
		}
		
		echo "<td>" . $resultado_dir[3] . "</td>";

		echo "<td>$fila[0]</td>";

		echo "<td>$fila[1]</td>";
		
		echo "<td>$fila[2]</td>";
		
		echo "</tr>";

		if ( $fila[1] === "END" ) {
			echo "</table>";
			break;
		}
    }
}
?>