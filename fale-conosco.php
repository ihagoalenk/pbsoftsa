<?php

error_reporting(E_ERROR);
date_default_timezone_set('America/Recife');

class FaleConosco{

  ## USUÁRIO ###

  function getFaleConoscoSql($id_bloco, $numero_imovel, $login){
    return $sql = "SELECT id_fale_conosco, dt_mensagem, tp_mensagem, de_mensagem, st_visto, st_ticket
                   FROM fale_conosco WHERE id_numero_imovel = '$numero_imovel' AND id_bloco = '$id_bloco' AND login = '$login'
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
        "tp_mensagem" => $consulta[2], "de_mensagem" => $consulta[3], "st_visto" => $consulta[4],
        "st_ticket" => $consulta[5], "error" => false);
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

  //PEGANDO O ÚLTIMO ID DO FALE CONOSCO INSERIDO
  function getUltimoIdSql(){
    $sql = mysql_query("SELECT id_fale_conosco FROM fale_conosco ORDER BY id_fale_conosco DESC LIMIT 1");
    $consulta = mysql_fetch_array($sql);
    return $consulta[0];
  }
  
  ## CADASTRAR FALE CONOSCO ##                                                          
  function cadastrarFaleConosco($id_bloco, $id_numero_imovel, $email, $tp_mensagem, $de_mensagem, $telefone, $login){
    $sqlInsert = mysql_query("INSERT INTO fale_conosco(id_bloco, id_numero_imovel, dt_mensagem, email, tp_mensagem, de_mensagem, telefone, dt_ultima_mensagem) VALUES ('$id_bloco', '$id_numero_imovel', CURDATE(), '$email', '$tp_mensagem', '$de_mensagem', '$telefone', CURDATE())");

    if ($sqlInsert) {
    	//INSERINDO O REGISTRO EM FEEDBACK
    	$ultimoId = $this->getUltimoIdSql();
    	$this->cadastrarFeedbackFaleConosco($ultimoId, $de_mensagem, $login);

        $resultado[] = array("error" => false, "description" => "Sua mensagem foi cadastrada com sucesso.");
    } else {
        $resultado[] = array("error" => true, "description" => "Não foi possível cadastrar sua mensagem.");
    }

    echo base64_encode(json_encode($resultado));
	
  }

  //ATUALIZAR A DATA DA ÚLTIMA MENSAGEM NO FALE CONOSCO
  function atualizarDtUltimaMsgFaleConosco($id_fale_conosco, $dt_ultima_mensagem){
  	$sqlUpdate = mysql_query("UPDATE fale_conosco SET dt_ultima_mensagem = '$dt_ultima_mensagem' WHERE id_fale_conosco = $id_fale_conosco");

  }

  ## CADASTRAR FEEDBACK DO FALE CONOSCO ##                                                          
  function cadastrarFeedbackFaleConosco($id_fale_conosco, $de_mensagem, $login, $dt_ultima_mensagem){
  	
    $sqlInsert = mysql_query("INSERT INTO feedback_fale_conosco(id_fale_conosco, de_mensagem, login, dt_feedback)
    VALUES ('$id_fale_conosco', '$de_mensagem', '$login', CURDATE())");
    $dt_ultima_mensagem = date('Y-m-d');
    //$dt_ultima_mensagem = "2016-10-07";
    if ($sqlInsert) {
    	$this->atualizarDtUltimaMsgFaleConosco($id_fale_conosco, $dt_ultima_mensagem);
        $resultado[] = array("error" => false, "description" => "Sua mensagem foi cadastrada com sucesso.");
    } else {
        $resultado[] = array("error" => true, "description" => "Não foi possível cadastrar sua mensagem.");
    }

    echo base64_encode(json_encode($resultado));
  }

}

?>
