<?php

error_reporting(E_ERROR);
date_default_timezone_set('America/Recife');

class Notificacao{

  function getCorrespondenciaSql($numero_imovel, $id_bloco){
    return $sql = "SELECT * FROM correspondencia corresp
    WHERE corresp.id_numero_imovel = '$numero_imovel' AND corresp.id_bloco = '$id_bloco' ORDER BY id_correspondencia DESC";
  }

  function getCountCorrespondenciaSql($numero_imovel, $id_bloco){
    return $sql = "SELECT count(*) FROM correspondencia corresp
    WHERE corresp.id_numero_imovel = '$numero_imovel' AND corresp.id_bloco = '$id_bloco'";
  }

  function getCountAvisosSql(){
    return $sql = "SELECT count(*) FROM aviso";
  }

  function getAvisosSql(){
    return $sql = "SELECT * FROM aviso ORDER BY id_aviso DESC";
  }

  function getNotificacoesJson($numero_imovel, $id_bloco){
    $result = $this->getResultOnly($this->getCorrespondenciaSql($numero_imovel, $id_bloco));
    $resultAviso = $this->getResultOnly($this->getAvisosSql());
    if($result == false && $resultAviso == false){
      $resultado[] = array("error" => true, "description: " => "não encontrado!");
      return json_encode($resultado);
    }else{
      $retorno = mysql_num_rows($result);
      $retornoAviso = mysql_num_rows($resultAviso);

      if($retorno == 0 && $retornoAviso == 0){
        $resultado[] = array("error" => true, "description: " => "não encontrado!");
        return json_encode($resultado);
      }else{
        while($consulta = mysql_fetch_array($result)) {
          $resultado[] = array("tipoId" => 1, "tipo" => "CORRESPONDÊNCIA", "id" => $consulta[0], "dt_chegada" => $consulta[4], "hr_chegada" => $consulta[5],
          "dt_entrega" => $consulta[6], "hr_entrega" => $consulta[7], "description" => "", "de_remetente" => $consulta[3], "error" => false);
        }
        while($consultaAviso = mysql_fetch_array($resultAviso)) {
          $resultado[] = array("tipoId" => 2, "tipo" => "AVISO", "id" => $consultaAviso[0], "dt_chegada" => $consultaAviso[2], "hr_chegada" => "",
          "dt_entrega" => "", "hr_entrega" => "", "description" => $consultaAviso[3], "de_remetente" => "", "error" => false);
        }
        return json_encode($resultado);
      }
    }
  }

  function getCountNotificacoesJson($numero_imovel, $id_bloco){
    $result = $this->getResultOnly($this->getCorrespondenciaSql($numero_imovel, $id_bloco));
    $resultAviso = $this->getResultOnly($this->getAvisosSql());

    $retorno = 0;
    $retornoAviso = 0;

    if($result != false){
        $retorno = mysql_num_rows($result);
    }

    if($resultAviso != false){
        $retornoAviso = mysql_num_rows($resultAviso);
    }

    $resultCount[] = array("qtdeMensagens" => $retorno+$retornoAviso);
    return json_encode($resultCount);
  }


  //USADO PARA RETORNAR O CONJUNTO DE DADOS
  function getResultOnly($query){
    $connection = new Connection();
    $result = $connection->Query($query);
    return $result;

  }

}
?>
