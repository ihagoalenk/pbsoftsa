<?php

error_reporting(E_ERROR);
date_default_timezone_set('America/Recife');

class Reserva{

  var $validate = "";

  //Verificar se o imovel esta na tabela de inadimplentes
  function getInadiplenciaUsuario($user){
    $sql = "SELECT st_inadimplente FROM inadimplentes WHERE usuario = '$user'";
    $result = $this->getResultOnly($sql);
    if($result[0] == "N"){
      return false;
    }else{
      return true;
    }
  }

  //USADO PARA RETORNAR O CONJUNTO DE DADOS
  function getResultOnly($query){
    $connection = new Connection();
    $result = $connection->Query($query);
    $resultFinal = mysql_fetch_array($result);
    return $resultFinal;

  }

  //FUNÇÃO PRINCIPAL, ELA QUE REALIZA OS TESTES!
  function makeBooking($login, $dataInicio, $dataFim, $hourStart, $hourEnd, $numero_imovel, $id_bloco, $id_areacomum, $id_area_pai){
    $hoje = date("Y-m-d");
    if((strtotime($dataInicio)) >= (strtotime($hoje)) && (strtotime($hourEnd)) > (strtotime($hourStart)) ){

      $resultInadiplencia = $this->getInadiplenciaUsuario($login);
      if(!$resultInadiplencia){
        $resultMoreBooking = $this->getMoreBookingOneDay($numero_imovel, $dataInicio, $id_bloco);
        if(!$resultMoreBooking){
          $resultSameBookingOneMonth = $this->getSameBookingOneMonth($id_bloco, $numero_imovel, $dataInicio, $id_area_pai);
          if(!$resultSameBookingOneMonth){
            $resultCheckDayAntecipation = $this->checkDayAntecipation($dataInicio);
            if(!$resultCheckDayAntecipation){
              $resultCheckSameDayHour = $this->checkDateSameDay($id_areacomum, $dataInicio, $hourStart, $hourEnd);
              if(!$resultCheckSameDayHour){
                $resultCheckTimeForArea = $this->checkTimeForArea($id_areacomum, $hourStart, $hourEnd);
                if(!$resultCheckTimeForArea){
                  $resultCheckIgnoreQtdy = $this->checkIgnoreQtdy($dataInicio, $id_areacomum, $id_bloco, $numero_imovel);
                  if(!$resultCheckIgnoreQtdy){
                    $resultInsertBooking = $this->finalInsertBooking($dataInicio, $hourStart, $hourEnd, $numero_imovel, $id_bloco, $id_areacomum,
                    "", $login, $id_area_pai);
                    if(!$resultInsertBooking){
                      $resultado[] = array("error" => false, "description" => "Reserva solicitada com sucesso.");
                    }else{
                      $resultado[] = array("error" => true, "description" => "Não foi possível reservar neste momento.");
                    }
                  }else{
                    $resultado[] = array("error" => true, "description" => $GLOBALS['validateIgnoreQtdy']);
                  }
                }else{
                  $resultado[] = array("error" => true, "description" => $GLOBALS['validateTimeForArea']);
                }
              }else{
                $resultado[] = array("error" => true, "description" => "Neste horário a área está reservada. Por favor, escolha outro horário.");
              }
            }else{
              $resultado[] = array("error" => true, "description" => "Não é possível realizar uma reserva com tantos dias de antecedência! Por favor escolha outra data.");
            }
            //$resultado[] = array("error" => false, "description: " => "");
          }else{
            $resultado[] = array("error" => true, "description" => "Desculpe, você já atingiu a quota de reservas do mês para esta área.");
          }
        }else{
          $resultado[] = array("error" => true, "description" => "Desculpe, você não pode fazer essa reserva, pois já fez outra para este dia. Escolha outra data por favor.");
        }
      }else{
        $resultado[] = array("error" => true, "description" => "Desculpe, houve um problema para realizar a reserva. Entre em contato com o administrador.");
      }
    }else{
      $resultado[] = array("error" => true, "description" => "Data e/ou hora da solicitação inválida. Verifique e solicite novamente!");
    }
    return json_encode($resultado);
  }

  //Verificar se é possível fazer mais de uma reserva no mesmo dia:
  function getMoreBookingOneDay($id_numero_imovel, $dt_data, $id_bloco){
    $sqlReservaDiaConfiguracao = "SELECT perm_varias_reserva_dia FROM configuracao";
    $resultReservaDiaConfiguracao = $this->getResultOnly($sqlReservaDiaConfiguracao);
    if($resultReservaDiaConfiguracao[0] == "N"){
      $sqlVerifyBookingInDate = "SELECT count(idevento) FROM reserva_area_comum WHERE (id_bloco = $id_bloco and
        id_numero_imovel = $id_numero_imovel and dt_data = '$dt_data' and status <> 'cancelado')";
      $resultVerifyBookingInDate = $this->getResultOnly($sqlVerifyBookingInDate);
      return $resultVerifyBookingInDate[0] == 0 ? true : false;
    }else{
      return true;
    }
  }

  //Verificar quantas vezes o imovel fez reserva da mesma area comum no mês
  function getSameBookingOneMonth($id_bloco, $id_numero_imovel, $dt_data, $id_area_pai){
    $mes_reserva = date('m', strtotime($dt_data));
    $ano_reserva = date('Y', strtotime($dt_data));
    $sqlQtyBooking = "SELECT qtd_reserva_mes FROM configuracao";
    $resultQtyBooking = $this->getResultOnly($sqlQtyBooking);
    $sqlSameBookingOneMonth = "SELECT count(idevento) FROM reserva_area_comum
    WHERE id_area_pai = $id_area_pai AND (id_bloco = $id_bloco AND id_numero_imovel = $id_numero_imovel) AND
    (MONTH(dt_data) = '$mes_reserva' AND YEAR(dt_data) = '$ano_reserva')";
    $resultSameBookingOneMonth = $this->getResultOnly($sqlSameBookingOneMonth);
    return $resultSameBookingOneMonth[0] >= $resultQtyBooking[0] ? true : false;
  }

//Verificando se o usuário está tentando reservar uma área antes do período permitido.
  function checkDayAntecipation($dt_data){
    //Buscando na base de dados a quantidade de dias permitidos pra reservar uma área comum	por antecedência.
    $sqlQtdyDayAntecipation = "select nu_dia_antecedencia from configuracao";
    $resultQtdyDayAntecipation = $this->getResultOnly($sqlQtdyDayAntecipation);
    $current_date = date('Y-m-d');
    $startTimeStamp = strtotime("$dt_data");
    $endTimeStamp = strtotime($current_date);
    $timeDiff = abs($endTimeStamp - $startTimeStamp);
    // 86400 seconds in one day
    $numberDays = $timeDiff/86400;
    // and you might want to convert to integer
    if($numberDays > $resultQtdyDayAntecipation[0]){
      return true;
    }else{
      return false;
    }
  }

  function checkDateSameDay($id_area_comum, $dt_data, $hourStart, $hourEnd){
    $uHourStart = date ('H:i',strtotime($hourStart));
    $uHourEnd = date ('H:i',strtotime($hourEnd));
    $sqlCheckDay = "select r.dt_data
    					from reserva_area_comum r
    					where (r.id_cadastro_reserva_area_comum = '$id_area_comum' AND
    					r.dt_data = '$dt_data' AND
    					(r.dt_hora_inicio <= '$uHourStart' AND r.dt_hora_fim >= '$uHourStart' OR
    					r.dt_hora_inicio <= '$uHourEnd' AND r.dt_hora_fim >= '$uHourEnd' OR
    					r.dt_hora_inicio >= '$uHourStart' AND r.dt_hora_fim <= '$uHourEnd' OR
    					r.dt_hora_inicio <= '$uHourStart' AND r.dt_hora_fim >= '$uHourEnd')) AND
    					(r.status = 'confirmado' OR r.status = 'pendente' OR r.status = 'bloqueado')";
    $resultCheckDay = $this->getResultOnly($sqlCheckDay);
    if($resultCheckDay[0] != null || empty($resultCheckDay[0])){
      return false;
    }else{
      return true;
    }
  }

  //SERVE PARA INSERIR DE FATO, RESPEITAR OS PADRÕES DE HORA
  function finalInsertBooking($dt_data, $hourStart, $hourEnd, $id_numero_imovel, $id_bloco, $id_cadastro_reserva_area_comum,
  $de_observacao, $login, $id_area_pai){

    //$dt_data = '2015-09-25';
    //$hourStart = '08:00';
    $horaInicio = date('H:i:s',strtotime($hourStart));
    //$hourEnd = '23:59';
    $horaFim = date ('H:i:s',strtotime($hourEnd));
    //$id_numero_imovel = 101;
    //$id_bloco = 1;
    //$id_cadastro_reserva_area_comum = 11;
    //$de_observacao = "";
    //$login = "ibz101";

    $sqlValue = "SELECT nu_valor FROM cadastro_area_comum WHERE id_cadastro_reserva_area_comum = $id_cadastro_reserva_area_comum";
    $resultValue = $this->getResultOnly($sqlValue);
    $value = empty($resultValue[0]) ? 0.00 : $resultValue[0];
    $sqlInsert = mysql_query("INSERT INTO reserva_area_comum(idevento,dt_data,dt_hora_inicio,dt_hora_fim,
      id_numero_imovel,id_bloco,id_cadastro_reserva_area_comum, id_area_pai, nu_valor,de_observacao,status,login, dt_hr_solicitacao) VALUES (0, '$dt_data', '$horaInicio',
      '$horaFim', '$id_numero_imovel', '$id_bloco', $id_cadastro_reserva_area_comum, $id_area_pai, $value, '$de_observacao', 'pendente', '$login', now())");

    if (!$sqlInsert) {
        return true;
    } else {
        return false;
    }

  }

  function checkTimeForArea($id_area_comum, $hourStart, $hourEnd){

    //$dt_data = '2015-09-25';
    //$hourStart = '08:00';
    $horaInicio = date('H:i:s',strtotime($hourStart));
    //$hourEnd = '23:59';
    $horaFim = date ('H:i:s',strtotime($hourEnd));
    $duracao = abs(strtotime($hourEnd)-(strtotime($hourStart)))/3600;

    $sqlValue = "SELECT hr_inicio, hr_fim, tmp_duracao FROM cadastro_area_comum WHERE id_cadastro_reserva_area_comum = $id_area_comum";
    $resultValue = $this->getResultOnly($sqlValue);

    $GLOBALS['validateTimeForArea'] = "";

    if($horaInicio < $resultValue[0]){
      $GLOBALS['validateTimeForArea'] = "Desculpe, a área só pode ser reservada a partir das ". substr($resultValue[0], 0, -3)." horas.";

    }else if($horaFim > $resultValue[1]){
      $GLOBALS['validateTimeForArea'] = "Desculpe, a área só pode ser reservada até as ". substr($resultValue[1], 0, -3)." horas.";

    }else if($duracao > $resultValue[2]){
      $GLOBALS['validateTimeForArea'] = "Desculpe, a área só pode ser reserva durante ". $resultValue[2]." horas.";

    }


    if(empty($GLOBALS['validateTimeForArea'])){
      return false;
    }else{
      return true;
    }


  }

  function checkIgnoreQtdy($dt_data, $id_cadastro_reserva_area_comum, $id_bloco, $id_numero_imovel){
    $sqlCheck = "SELECT ignora_qtd_reserva FROM cadastro_area_comum WHERE id_cadastro_reserva_area_comum = $id_cadastro_reserva_area_comum";
    $resultValue = $this->getResultOnly($sqlCheck);
    if(!empty($resultValue[0]) and isset($resultValue[0]) and $resultValue[0] == "N") {

      $sqlDateBooking = "SELECT dt_data FROM reserva_area_comum WHERE id_cadastro_reserva_area_comum = $id_cadastro_reserva_area_comum
      and (id_bloco = $id_bloco and id_numero_imovel = $id_numero_imovel) and status <> 'cancelado' ORDER BY dt_data DESC LIMIT 1";

      $resultDateBooking = $this->getResultOnly($sqlDateBooking);

      $GLOBALS['validateIgnoreQtdy'] = "";

    	if (!empty($resultDateBooking[0]) and isset($resultDateBooking[0])) {
    		$data_reserva = $resultDateBooking[0];
    		//Formatando a data para poder somar os 30 dias
    		$data = $data_reserva;
    		//Separa��o dos valores (dia, m�s e ano)
    		$arr = explode("-", $data);
    		$ano = $arr[0];
    		$mes = $arr[1];
    		$dia = $arr[2];
    		//Somar Data da �ltima reserva + 30 dias
    		$data_inc = date('Y/m/d', mktime(0, 0, 0, $mes, $dia + 30, $ano));
    		//Convertendo o formato da data

        $datetime1 = new DateTime($data_reserva);
        $datetime2 = new DateTime($dt_data);
        $interval = $datetime1->diff($datetime2);
        $qtde_days = $interval->days;
    		// $data_cov = sc_date_conv($data_inc,"db_format","dd/mm/aaaa");
    		// //Verificando se faz menos de 30 dias que a �rea solicitada foi reservada
    		// //Calculando a diferen�a de dias sc_date_dif � uma macro do scriptcase
    		// $qtde_days = sc_date_dif({dt_data}, 'aaaa-mm-dd', $data_reserva, 'aaaa-mm-dd');
        $data_conv = new DateTime($data_inc);
        $data_converted = $data_conv->format('d-m-Y');

    		if($qtde_days < 30){
          $GLOBALS['validateIgnoreQtdy'] = "Não é possível realizar esta reserva, você só poderá reservar essa área a partir do dia $data_converted!";
        }

        if(empty($GLOBALS['validateIgnoreQtdy'])){
          return false;
        }else{
          return true;
        }
    }
  }
}



}
?>
