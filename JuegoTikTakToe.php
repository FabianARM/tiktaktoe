<?php 

require("BaseDatos.php");

class JuegoTikTakToe{
  
  public $puntosParaGanar = 3;
  public $tablero = array(); 
  public $tamanoTablero = 3; 
  public $tiempoActualSegundos = 10;  // Se tiene que llevar un conteo, de momento, quemado para pruebas
	public $idUsuario = "JUGADOR";
  
  function __construct()
  {
    $this->db = new BaseDatos();
    //Inicializamos el tablero, que es de tamaño 9*9 
    for($indice = 0; $indice < $this->tamanoTablero * $this->tamanoTablero; $indice++){
      $this->tablero[$indice] = "";
    }
  }
  
  function getTablero(){
    return $this->tablero;
  }
  
  function marcarEnTablero($caracter, $coordenadaX, $coordenadaY){
    $this->tablero[$coordenadaX * $this->tamanoTablero + $coordenadaY] = $caracter;
  }
  
  function obtenerValorDePosicion($coordenadaX, $coordenadaY){
    return $this->tablero[$coordenadaX * $this->tamanoTablero + $coordenadaY];
  }
  
  function revisarDiagonales()
  {  
    $coordenadaX = 0; 
    $coordenadaY = 0;
    $contador = 1;
    for($indice = 0; $indice < 2; $indice++){
      while($coordenadaX < 2){
        if($indice == 0){
          if($this->obtenerValorDePosicion($coordenadaX,$coordenadaY) != "" && $this->obtenerValorDePosicion($coordenadaX,$coordenadaY) == $this->obtenerValorDePosicion($coordenadaX + 1,$coordenadaY + 1)) {
            $contador++; 
            if($contador == $this->puntosParaGanar){
              return true; 
            }
          }
          $coordenadaY++;
        }
        else{
          if($this->obtenerValorDePosicion($coordenadaX,$coordenadaY) != "" && $this->obtenerValorDePosicion($coordenadaX,$coordenadaY) == $this->obtenerValorDePosicion($coordenadaX + 1,$coordenadaY - 1)){
            $contador++; 
            if($contador == $this->puntosParaGanar){
              return true; 
            }
          }
          $coordenadaY--; 
        }
        $coordenadaX++; 
      }
      $coordenadaX = 0;
      $coordenadaY = 2; 
      $contador = 0;
    }
    return false; 
  }
  
  function revisarHorizontal(){
    $contador = 1; 
    for($indice = 0; $indice < $this->tamanoTablero - 1; $indice++){
      for($indice2 = 0; $indice2 < $this->tamanoTablero - 1; $indice2++){
         if($this->obtenerValorDePosicion($indice ,$indice2) != "" && $this->obtenerValorDePosicion($indice,$indice2) == $this->obtenerValorDePosicion($indice,$indice2 + 1)){
             $contador++;
             if($contador ==  $this->puntosParaGanar){
               return true;
             }
         }
      }
      $contador = 1; 
    }
  }
  
  function revisarVertical(){
    $contador = 1; 
    for($indice = 0; $indice < $this->tamanoTablero - 1; $indice++){
      for($indice2 = 0; $indice2 < $this->tamanoTablero - 1; $indice2++){
         if($this->obtenerValorDePosicion($indice2, $indice) != "" && $this->obtenerValorDePosicion($indice2,$indice) == $this->obtenerValorDePosicion($indice2 + 1,$indice)){
             $contador++;
             if($contador ==  $this->puntosParaGanar){
               return true;
             }
         }
      }
      $contador = 1; 
    }
  }
  
  function movimientoValido(){
    
  }
  
  function revisarGanador(){
    if($this->revisarDiagonales() || $this->revisarVertical() || $this->revisarHorizontal()){
      return true; 
    }
    return false; 
  }
  
  function marcarXEnTablero($coordenadaX, $coordenadaY){
    $this->marcarEnTablero("X", $coordenadaX, $coordenadaY); 
  }
  
  function marcarOEnElTablero($coordenadaX, $coordenadaY){
    $this->marcarEnTablero("O", $coordenadaX, $coordenadaY);
  }
  
  function turno($coordenadaX, $coordenadaY, $caracter)
  {
    if($caracter == "X")
    {
      $this->marcarEnTablero("X", $coordenadaX, $coordenadaY);
    }
    else
    {
      $this->marcarEnTablero("O", $coordenadaX, $coordenadaY);
    }
    
    if($this->revisarGanador())
    {
      // Ganador, verificar si hay que guardarlo en los puntajes mas altos.
			// OJO: Se tiene que llamar cuando el jugador gane, no el caso de la maquina
      $this->verificarRecord();
    }
    
    
    return $this->revisarGanador(); 
  }

  function verificarRecord()
  {
    $listaRecords = $this->db->leerRecords();
		$insertado = false;

		// Iterar sobre la lista de los tiempos
		for($posicion = 0; $posicion < sizeof($listaRecords); $posicion++)
		{
			$record = $listaRecords[$posicion];

				// Si mi tiempo es menor a alguno
			if($record->tiempo > $this->tiempoActualSegundos)
			{
				// Hacer append en el index actual
				array_splice( $listaRecords, $posicion, 0, array(new RecordModelo($this->idUsuario, $this->tiempoActualSegundos)) );

				// Si el tamanno de la lista es 10, eliminar el elemento onceavo
				if(sizeof($listaRecords) > 10)
				{
					unset($listaRecords[sizeof($listaRecords) - 1]);
				}
				
				$insertado = true;
				break;
			}				
		}
		
		// Si hay menos de 10 elementos y no se inserto nada. Caso en que tiempoActual sea mayor que los de la lista.
		// Si hay mas de 10 elementos, ni siquiera entro al record.
		if(sizeof($listaRecords) < 10 && $insertado == false)
		{
			array_push($listaRecords, new RecordModelo($this->idUsuario, $this->tiempoActualSegundos));
		}
    
    // Limpio el archivo y guardo la lista
		$this->db->guardarRecords($listaRecords);
  }
  
  
  function obtenerRecords()
  {
    $listaRecords = $this->db->leerRecords();
    $resultado = "";
    
    for($posicion = 0; $posicion < sizeof($listaRecords); $posicion++)
    {
      $record = $listaRecords[$posicion];
      
      $resultado = $resultado.$record->nombre.",".$record->tiempo.";";
    }
    
    return $resultado;
  }
  
    
  function armarMatrizDeDecision($caracter){
    //Iniciamos la matrix de decision. 
    $matrixDeDecision = array(); 
    for($indice = 0; $indice < ($this->tamanoTablero + 1) * ($this->tamanoTablero + 1); $indice++){
      $matrizDeDecision[$indice] = "";
    }
    //Se rellena la suma de las columnas
    for($indice = 0; $indice < $this->tamanoTablero; $indice++){
      $contadorDeCaracteres = 0; 
      for($indice2 = 0; $indice2 < $this->tamanoTablero; $indice2++){
        if($this->obtenerValorDePosicion($indice, $indice2) == $caracter){
           $contador++; 
        }
        $matrizDeDecision[3 * $this->tamanoTablero + $indice] = $contador; 
      }
    }
     //Se rellena la suma de las filas.
    for($indice = 0; $indice < $this->tamanoTablero; $indice++){
      $contadorDeCaracteres = 0; 
      for($indice2 = 0; $indice2 < $this->tamanoTablero; $indice2++){
        if($this->obtenerValorDePosicion($indice2, $indice) == $caracter){
           $contador++; 
        }
        $matrizDeDecision[$indice * $this->tamanoTablero + 3] = $contador; 
      }
    }
    $matrixDeDecision[3 * $this->tamanoTablero + 3] = $caracter; 
    return $matrixDeDecision; 
  }

  function jugadaMaquina($matrizDeDesicion)
  {
    //Posiciones de interes (3, Y) y (X, 3)
    //$coordenadaX * $this->tamanoTablero + $coordenadaY
    //Heuristica #1  // Si en una fila hay un 2 tiene que marcar porque va a perder o va a ganar. 
    if($matrizDeDecision[3 * $this->tamanoTablero + 0] == 2)
    {
      //Si esta vacio se puede marcar.
      if($this->obtenerValorDePosicion(0, 0) == "")
      { 
        $this->marcarEnTablero($matrizDeDecision[3 * $this->tamanoTablero + 3], 0, 0);
      }
      else
      {
        $this->marcarEnTablero($matrizDeDecision[3 * $this->tamanoTablero + 3], 2, 0);
      }
      return true; 
    }
    else
    {
      if($matrixDeDecision[3 * $this->tamanoTablero + 1] == 2)
      {
        if($this->obtenerValorDePosicion(0, 1) == "")
        { 
          $this->marcarEnTablero($matrizDeDecision[3 * $this->tamanoTablero + 3], 0, 1);
        }
        else
        {
          $this->marcarEnTablero($matrizDeDecision[3 * $this->tamanoTablero + 3], 2, 1);
        }
        return true; 
      }
      else
      {
        if($matrixDeDecision[3 * $this->tamanoTablero + 2] == 2)
        {
          if($this->obtenerValorDePosicion(0, 2) == "")
          { 
            $this->marcarEnTablero($matrizDeDecision[3 * $this->tamanoTablero + 3], 0, 2);
          }
          else
          {
            $this->marcarEnTablero($matrizDeDecision[3 * $this->tamanoTablero + 3], 2, 2);
          }
        }
        return true; 
      }
    }
    //Fin de heuristica #1
    //Inicio segunda heuristica #2 // Si en una columna hay un dos 
    if($matrizDeDecision[0 * $this->tamanoTablero + 3] == 2)
    {
      //Si esta vacio se puede marcar.
      if($this->obtenerValorDePosicion(0, 0) == "")
      { 
        $this->marcarEnTablero($matrizDeDecision[3 * $this->tamanoTablero + 3], 0, 0);
      }
      else
      {
        $this->marcarEnTablero($matrizDeDecision[3 * $this->tamanoTablero + 3], 0, 2);
      }
      return true; 
    }
    else
    {
      if($matrixDeDecision[3 * $this->tamanoTablero + 1] == 2)
      {
        if($this->obtenerValorDePosicion(0, 1) == "")
        { 
          $this->marcarEnTablero($matrizDeDecision[3 * $this->tamanoTablero + 3], 1, 0);
        }
        else
        {
          $this->marcarEnTablero($matrizDeDecision[3 * $this->tamanoTablero + 3], 1, 2);
        }
        return true; 
      }
      else
      {
        if($matrixDeDecision[3 * $this->tamanoTablero + 2] == 2)
        {
          if($this->obtenerValorDePosicion(0, 2) == "")
          { 
            $this->marcarEnTablero($matrizDeDecision[3 * $this->tamanoTablero + 3], 2, 0);
          }
          else
          {
            $this->marcarEnTablero($matrizDeDecision[3 * $this->tamanoTablero + 3], 2, 2);
          }
        }
        return true; 
      }
    }
    //Fin de heuristica #2
    //Inicio de la tercera heuristica #3
    $posicionX = -1;
    $posicionY = -1; 
    for($indice = 0; $indice < 2; $indice++)
    {
      for($indice2 = 0; $indice2 < $this->tamanoTablero; $indice2)
      {
        if($indice == 0 && $matrixDeDecision[3 * $this->tamanoTablero + $indice2] == 0)
        { 
            $posicionY = $indice2; 
        }
        else
        {
          if(indice == 1 && $matrixDeDecision[$indice2 * $this->tamanoTablero + 3] == 0)
          {
            $posicionX = $indice2;
          }
        }
      }
    }
    if($posicionX != -1 && $posicionY != -1)
    {
      $this->marcarEnTablero($matrizDeDecision[3 * $this->tamanoTablero + 3], $posicionX, $posicionY);
    }// Si no cae en ninguna de las heuristicas anteriores que pacha? 
    //fin de la heuristica
  }
  


  function limpiarTablero(){
    for($indice = 0; $indice < $this->tamanoTablero * $this->tamanoTablero; $indice++){
      $this->tablero[$indice] = "";
    }
  }
  
  function immprimirTablero(){
    for($indice = 0; $indice < $this->tamanoTablero; $indice++){
      for($indice2 = 0; $indice2 < $this->tamanoTablero; $indice2++){
        if($this->obtenerValorDePosicion($indice, $indice2) == ""){
          print("_");
        }
        else{
          print($this->obtenerValorDePosicion($indice, $indice2));  
        }
      }
      print(""."\n");
    }
  }
}

//Prueba 

$juego = new JuegoTikTakToe();

/*print($juego->turno(0,0,"X"));
print($juego->turno(1,1,"X"));
print($juego->turno(2,2,"X"));

  
if($juego->turno(0,0,"O") || $juego->turno(1,0,"O") || $juego->turno(2,0,"O"))
{
 // print("Gano\n");
}
else{
 // print("No gano\n");
}
  
//$juego->immprimirTablero();
*/

?>