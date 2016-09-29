<?php

error_reporting(E_ERROR);
date_default_timezone_set('America/Recife');

class FaleConosco{

  ## USUÁRIO ###

  function getFaleConoscoSql($id_bloco, $numero_imovel){
    return $sql = "SELECT id_fale_conosco, dt_mensagem, id_bloco, id_numero_imovel, email, tp_mensagem, de_mensagem, st_visto, feedback,    st_ticket
                   FROM fale_conosco WHERE id_numero_imovel = '$numero_imovel' AND id_bloco = '$id_bloco' 
                   ORDER BY st_ticket asc, st_visto asc, dt_ultima_mensagem desc";
  }

  function getFaleConoscoJson($result) {
    $retorno = mysql_num_rows($result);

    if($retorno == 0 ){
      $resultado[] = array("error" => true, "description: " => "não encontrado!");
      return json_encode($resultado);
    }else{
      while($consulta = mysql_fetch_array($result)) {
        $resultado[] = array("id_fale_conosco" => $consulta[0], "dt_mensagem" => $consulta[1],
        "id_bloco" => $consulta[2], "id_numero_imovel" => $consulta[3], "email" => $consulta[4],
        "tp_mensagem" => $consulta[5], "de_mensagem" => $consulta[6], "st_visto" => $consulta[7],
        "feedback" => $consulta[8], "st_ticket" => $consulta[9], "error" => false);
      }
      return json_encode($resultado);
    }

  }

  function getTipoFaleConoscoJson() {
    $resultado[] = array("tp_mensagem" => 'CANCELAMENTO DE RESERVA', "error" => false);
    $resultado[] = array("tp_mensagem" => 'RECLAMAÇÃO', "error" => false);
    $resultado[] = array("tp_mensagem" => 'DÚVIDA', "error" => false);
    $resultado[] = array("tp_mensagem" => 'SOLICITAÇÃO', "error" => false);
    $resultado[] = array("tp_mensagem" => 'SUGESTÃO', "error" => false);
    $resultado[] = array("tp_mensagem" => 'ELOGIO', "error" => false);
    return json_encode($resultado);
  }
 /* #### FALE CONOSCO FEEDBACK #####

  var $sqlTipoOcorrencia = "SELECT id_tipo_ocorrencia, de_tipo_ocorrencia FROM tipo_ocorrencia";

  function getTipoMensagemJson($result) {
    $retorno = mysql_num_rows($result);

    if($retorno == 0 ){
      $resultado[] = array("error" => true, "description: " => "não encontrado!");
      return json_encode($resultado);
    }else{
      while($consulta = mysql_fetch_array($result)) {
        $resultado[] = array("id_tipo_ocorrencia" => $consulta[0], "de_tipo_ocorrencia" => $consulta[1], "error" => false);
      }
      return json_encode($resultado);
    }

  }*/

  ## CADASTRAR FALE CONOSCO ##                                                          

  function cadastrarFaleConosco($dt_mensagem, $id_bloco, $id_numero_imovel, $email, $tp_mensagem){
    echo $sqlInsert = mysql_query("INSERT INTO fale_conosco(dt_mensagem, id_bloco, id_numero_imovel, email, tp_mensagem) VALUES ('$dt_mensagem', '$id_bloco', '$id_numero_imovel', '$email', '$tp_mensagem')");
	echo "INSERT INTO fale_conosco(dt_mensagem, id_bloco, id_numero_imovel, email, tp_mensagem) VALUES ('$dt_mensagem', '$id_bloco', '$id_numero_imovel', '$email', '$tp_mensagem')";
    if ($sqlInsert) {
        $resultado[] = array("error" => false, "description" => "Sua mensagem foi cadastrada com sucesso.");
    } else {
        $resultado[] = array("error" => true, "description" => "Não foi possível cadastrar sua mensagem.");
    }

    echo base64_encode(json_encode($resultado));
  }

}

?>
