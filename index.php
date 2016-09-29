<?php

error_reporting(E_ERROR);
date_default_timezone_set('America/Recife');

include 'connection.php';
include 'query.php';
include 'reserva.php';
include 'notificacao.php';
include 'ocorrencia.php';
include 'fale-conosco.php';
$connection = new Connection();
$query = new Query();
$reserva = new Reserva();
$notificacao = new Notificacao();
$ocorrencia = new Ocorrencia();
$faleConosco = new FaleConosco();
//header('Content-Type: application/json');

if(!isset($_SERVER['HTTP_PATH'])){
  $_SERVER['HTTP_PATH'] = "";
  echo "<center><h2><br/><br/><br/><br/><br/><br/><br/><br/>Você não tem permissão para acessar esta página!</h2></center>";
}

if($_SERVER['REQUEST_METHOD'] == "GET" && base64_decode($_SERVER['HTTP_PATH']) == "areascomuns"){
  $data = base64_decode($_SERVER['HTTP_DATA']);
  $sql = $query->getAreaComumSql($data);
  $result = $connection->Query($sql);
  echo base64_encode($query->getAreasComunsJson($result));
}else if($_SERVER['REQUEST_METHOD'] == "GET" && base64_decode($_SERVER['HTTP_PATH']) == "prestadores"){
  $sql = $query->listarPrestadores;
  $result = $connection->Query($sql);
  echo base64_encode($query->getPrestadoresJson($result)) ;

}else if($_SERVER['REQUEST_METHOD'] == "POST" && base64_decode($_SERVER['HTTP_PATH']) == "login"){
  $login = base64_decode($_SERVER['HTTP_LOGIN']);
  $password = base64_decode($_SERVER['HTTP_PASSWORD']);
  $sql = $query->getUserSql($login, $password);
  $result = $connection->Query($sql);
  echo base64_encode($query->getUserJson($result));

}else if($_SERVER['REQUEST_METHOD'] == "POST" && base64_decode($_SERVER['HTTP_PATH']) == "reservar"){
  $login = base64_decode($_SERVER['HTTP_LOGIN']);
  $dataInicio = base64_decode($_SERVER['HTTP_DATAINICIO']);
  $dataFim = base64_decode($_SERVER['HTTP_DATAFIM']);
  $numero_imovel = base64_decode($_SERVER['HTTP_NUMEROIMOVEL']);
  $id_bloco = base64_decode($_SERVER['HTTP_IDBLOCO']);
  $id_areacomum = base64_decode($_SERVER['HTTP_IDAREACOMUM']);
  $hourStart = base64_decode($_SERVER['HTTP_HOURSTART']);
  $hourEnd = base64_decode($_SERVER['HTTP_HOUREND']);
  $id_area_pai = base64_decode($_SERVER['HTTP_IDAREAPAI']);
  echo base64_encode($reserva->makeBooking($login, $dataInicio, $dataFim, $hourStart, $hourEnd, $numero_imovel, $id_bloco, $id_areacomum, $id_area_pai));

}else if($_SERVER['REQUEST_METHOD'] == "GET" && base64_decode($_SERVER['HTTP_PATH']) == "ocorrencias"){
  $login = base64_decode($_SERVER['HTTP_LOGIN']);
  $numero_imovel = base64_decode($_SERVER['HTTP_NRIMOVEL']);
  $id_bloco = base64_decode($_SERVER['HTTP_IDBLOCO']);
  $group_id_user = base64_decode($_SERVER['HTTP_GROUPIDUSER']);
  $sql = $query->getOcorrenciasSql($numero_imovel, $id_bloco, $group_id_user);
  $result = $connection->Query($sql);
  echo base64_encode($query->getOcorrenciasJson($result));

}else if($_SERVER['REQUEST_METHOD'] == "GET" && base64_decode($_SERVER['HTTP_PATH']) == "consultaReservas"){
  $sql = $query->sqlGetTodasReservas;
  $result = $connection->Query($sql);
  $resultFinal = $query->getConsultasReservaJson($result);
  echo base64_encode($resultFinal);
}else if($_SERVER['REQUEST_METHOD'] == "GET" && base64_decode($_SERVER['HTTP_PATH']) == "minhasReservas"){
  $id_numero_imovel = base64_decode($_SERVER['HTTP_NRIMOVEL']);
  $id_bloco = base64_decode($_SERVER['HTTP_IDBLOCO']);
  $sql = $query->getMinhasReservasSql($id_numero_imovel, $id_bloco);
  $result = $connection->Query($sql);
  $resultFinal = $query->getConsultasReservaJson($result);
  echo base64_encode($resultFinal);
}else if($_SERVER['REQUEST_METHOD'] == "GET" && base64_decode($_SERVER['HTTP_PATH']) == "notificacoes"){
  $numero_imovel = base64_decode($_SERVER['HTTP_NRIMOVEL']);
  $id_bloco = base64_decode($_SERVER['HTTP_IDBLOCO']);
  $resultado = $notificacao->getNotificacoesJson($numero_imovel, $id_bloco);
  echo base64_encode($resultado);
}else if($_SERVER['REQUEST_METHOD'] == "GET" && base64_decode($_SERVER['HTTP_PATH']) == "countNotificacoes"){
  $numero_imovel = base64_decode($_SERVER['HTTP_NRIMOVEL']);
  $id_bloco = base64_decode($_SERVER['HTTP_IDBLOCO']);
  $resultado = $notificacao->getCountNotificacoesJson($numero_imovel, $id_bloco);
  echo base64_encode($resultado);
}else if($_SERVER['REQUEST_METHOD'] == "GET" && base64_decode($_SERVER['HTTP_PATH']) == "getReclamantes"){
  $numero_imovel = base64_decode($_SERVER['HTTP_NRIMOVEL']);
  $id_bloco = base64_decode($_SERVER['HTTP_IDBLOCO']);
  $sql = $ocorrencia->getReclamanteSql($numero_imovel, $id_bloco);
  $result = $connection->Query($sql);
  $resultFinal = $ocorrencia->getReclamanteJson($result);
  echo base64_encode($resultFinal);
}else if($_SERVER['REQUEST_METHOD'] == "GET" && base64_decode($_SERVER['HTTP_PATH']) == "getTipoOcorrencia"){
  $sql = $ocorrencia->sqlTipoOcorrencia;
  $result = $connection->Query($sql);
  $resultFinal = $ocorrencia->getTipoOcorrenciaJson($result);
  echo base64_encode($resultFinal);
}else if($_SERVER['REQUEST_METHOD'] == "POST" && base64_decode($_SERVER['HTTP_PATH']) == "criarNovaOcorrencia"){
  $login = base64_decode($_SERVER['HTTP_LOGIN']);
  $id_morador = base64_decode($_SERVER['HTTP_IDMORADOR']);
  $numero_imovel = base64_decode($_SERVER['HTTP_NRIMOVEL']);
  $id_bloco = base64_decode($_SERVER['HTTP_IDBLOCO']);
  $data_hr_ocorrencia = base64_decode($_SERVER['HTTP_DATAHROCORRENCIA']);
  $tipo_ocorrencia = base64_decode($_SERVER['HTTP_TIPOOCORRENCIA']);
  $de_descricao_ocorrencia = base64_decode($_SERVER['HTTP_DESCRICAOOCORRENCIA']);
  //$img_ocorrencia = $_SERVER['HTTP_IMAGEMOCORRENCIA'];
  $img_ocorrencia = str_replace(' ', '+', $_POST['HTTP_IMAGEMOCORRENCIA']);
  //echo $img_ocorrencia."QUANDO RECEBE NO INDEX";
  $ocorrencia->cadastrarOcorrenciaNova($id_morador, $numero_imovel, $id_bloco, $tipo_ocorrencia,
  $data_hr_ocorrencia, $de_descricao_ocorrencia, $img_ocorrencia);
}else if($_SERVER['REQUEST_METHOD'] == "POST" && ($_SERVER['HTTP_PATH']) == "reservar2"){
  $login = ($_SERVER['HTTP_LOGIN']);
  $dataInicio = ($_SERVER['HTTP_DATAINICIO']);
  $dataFim = ($_SERVER['HTTP_DATAFIM']);
  $numero_imovel = ($_SERVER['HTTP_NUMEROIMOVEL']);
  $id_bloco = ($_SERVER['HTTP_IDBLOCO']);
  $id_areacomum = ($_SERVER['HTTP_IDAREACOMUM']);
  $hourStart = ($_SERVER['HTTP_HOURSTART']);
  $hourEnd = ($_SERVER['HTTP_HOUREND']);
  $id_area_pai = ($_SERVER['HTTP_IDAREAPAI']);
  $reserva->makeBooking($login, $dataInicio, $dataFim, $hourStart, $hourEnd, $numero_imovel, $id_bloco, $id_areacomum, $id_area_pai);
}else if($_SERVER['REQUEST_METHOD'] == "GET" && base64_decode($_SERVER['HTTP_PATH']) == "getFaleConosco"){
  $numero_imovel = base64_decode($_SERVER['HTTP_NRIMOVEL']);
  $id_bloco = base64_decode($_SERVER['HTTP_IDBLOCO']);
  $sql = $faleConosco->getFaleConoscoSql($id_bloco, $numero_imovel);
  $result = $connection->Query($sql);
  $resultFinal = $faleConosco->getFaleConoscoJson($result);
  echo base64_encode($resultFinal);
}else if($_SERVER['REQUEST_METHOD'] == "GET" && base64_decode($_SERVER['HTTP_PATH']) == "getFaleConoscoFeedback"){
  //echo $login = base64_decode($_SERVER['HTTP_LOGIN']);
  $id_bloco = base64_decode($_SERVER['HTTP_IDBLOCO']);
  $numero_imovel = base64_decode($_SERVER['HTTP_NRIMOVEL']);
  $sql = $query->getFaleConoscoFeedbackSql($id_bloco, $numero_imovel);
  $result = $connection->Query($sql);
  $resultFinal = $query->getFaleConoscoFeedbackJson($result);
  echo base64_encode($resultFinal);
}else if($_SERVER['REQUEST_METHOD'] == "GET" && base64_decode($_SERVER['HTTP_PATH']) == "getTipoMsgFaleConosco"){
  $result = $faleConosco->getTipoFaleConoscoJson();
  echo base64_encode($result);
}else if($_SERVER['REQUEST_METHOD'] == "POST" && base64_decode($_SERVER['HTTP_PATH']) == "novoFaleConosco"){
  $dt_mensagem = base64_decode($_SERVER['HTTP_DTMENSAGEM']);
  $id_bloco = base64_decode($_SERVER['HTTP_IDBLOCO']);
  $numero_imovel = base64_decode($_SERVER['HTTP_NRIMOVEL']);
  $email = base64_decode($_SERVER['HTTP_EMAIL']);
  $tp_mensagem = base64_decode($_SERVER['HTTP_TPMENSAGEM']);
  //$de_mensagem = ($_SERVER['HTTP_MENSAGEM']);
  //$st_visto = ($_SERVER['HTTP_STVISTO']);
  //$feedback = ($_SERVER['HTTP_FEEDBACK']);
  //$telefone = ($_SERVER['HTTP_TELEFONE']);
  //$st_ticket = ($_SERVER['HTTP_TICKET']);
  $faleConosco->cadastrarFaleConosco($dt_mensagem, $id_bloco, $numero_imovel, $email, $tp_mensagem);
}


?>
