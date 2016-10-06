<?php

error_reporting(E_ERROR);
date_default_timezone_set('America/Recife');

class Query{

  var $getAreasComuns = "SELECT
    id_cadastro_reserva_area_comum,
    de_cadastro_reserva_area_comum,
    id_area_pai,
    nu_valor,
    st_horario_sn
    FROM
    cadastro_area_comum";

   var $listarPrestadores = "SELECT
      ps.id_prestador,
      tp.de_tipo_servico,
      ps.de_prestador,
      email,
      if(ps.nu_telefone1<>0, ps.nu_telefone1, '') as nu_telefone1,
      if(ps.nu_telefone2<>0, ps.nu_telefone2, '') as nu_telefone2,
      ps.de_operadora1,
      if(ps.nu_telefone3<>0, ps.nu_telefone3, '') as nu_telefone3,
      ps.de_operadora2,
      ps.media,
      permissao,
      ps.de_ativo_sn,
      observacao,
      ps.cep,
      ps.de_cidade,
      ps.de_uf,
      ps.de_complemento,
      ps.de_bairro,
      ps.nu_endereco,
      ps.de_logradouro
  FROM
      prestador_servico ps, tipo_prestador_servico tp
  WHERE
    ps.id_tipo_servico = tp.id_tipo_servico
  ORDER BY
    tp.de_tipo_servico ASC";

   var $sqlGetTodasReservas = "SELECT rac.*, crac.de_cadastro_reserva_area_comum FROM reserva_area_comum rac
   JOIN cadastro_area_comum crac ON rac.id_cadastro_reserva_area_comum = crac.id_cadastro_reserva_area_comum
   where status NOT IN ('cancelado','bloqueado') order by idevento desc LIMIT 20";

    function getAreasComunsJson($result) {

      $retorno = mysql_num_rows($result);

      if($retorno == 0 ){
        print("<center>Erro ao carregar as informações !!<br>");
        return 0;
      }else{
        while($consulta = mysql_fetch_array($result)) {
          if($consulta[1] != "TUDO"){
            $resultado[] = array("id" => $consulta[0], "nome" => $consulta[1], "id_area_pai" => $consulta[2], "valor" => $consulta[3], "st_horario" => $consulta[4]);
          }
        }
        return json_encode($resultado);
      }
    }

    function getPrestadoresJson($result) {
      $retorno = mysql_num_rows($result);

      if($retorno == 0 ){
        $resultado[] = array("error" => true, "description: " => "não encontrado!");
        return json_encode($resultado);
      }else{
        while($consulta = mysql_fetch_array($result)) {
          $resultado[] = array("id" => $consulta[0], "tipo_servico" => $consulta[1],
          "nome_prestador" => $consulta[2], "telefone1" => $consulta[4], "telefone2" => $consulta[5],
          "media" => $consulta[9]);
        }
        return json_encode($resultado);
      }
    }

    function getUserJson($result) {
      $retorno = mysql_num_rows($result);

      if($retorno == 0 ){
        $resultado[] = array("error" => true, "description: " => "não encontrado!");
        return json_encode($resultado);
      }else{
        while($consulta = mysql_fetch_array($result)) {
          $resultado[] = array("login" => $consulta[0], "name" => $consulta[1],
          "email" => $consulta[2], "telefone" => $consulta[3], "recebe_sms" => $consulta[4],
          "active" => $consulta[5], "recebe_email" => $consulta[6], "st_veiculo" => $consulta[7],
          "numero_imovel" => $consulta[8], "id_bloco" => $consulta[9], "permission" => $consulta[10],
          "group_id" => $consulta[11]);
        }
        return json_encode($resultado);
      }

    }

    function getUserSql($login, $passwd){
      return $sql = "SELECT seg.login, seg.name, seg.email, seg.telefone, seg.recebe_sms, seg.active, seg.recebe_email,
      seg.st_veiculo, imo.id_numero_imovel, imo.id_bloco, seggroup.description, seggroup.group_id
      FROM seg_users seg
      JOIN imovel imo ON imo.usuario = seg.login
      JOIN seg_users_groups segusergroup ON segusergroup.login = seg.login
      JOIN seg_groups seggroup ON seggroup.group_id = segusergroup.group_id
      WHERE seg.login = '$login' AND seg.pswd = '$passwd'";
    }

    function getAreaComumSql($data){
      return $sql = "SELECT id_cadastro_reserva_area_comum,
      de_cadastro_reserva_area_comum,
      id_area_pai,
      nu_valor,
      st_horario_sn FROM cadastro_area_comum cac WHERE cac.id_cadastro_reserva_area_comum
      NOT IN (SELECT rac.id_cadastro_reserva_area_comum FROM reserva_area_comum rac WHERE rac.dt_data = '$data')";
    }

    function getOcorrenciasSql($numero_imovel, $id_bloco, $group_id_user){
      return $sql = "SELECT
            om.id_ocorrencia_morador,
            om.id_morador,
            om.id_numero_imovel,
            om.id_bloco,
            om.id_tipo_ocorrencia,
            om.dt_hr_ocorrencia,
            om.de_descricao_ocorrencia,
            om.nu_placa,
            om.de_marca_modelo,
            om.img_ocorrencia,
            om.st_visto,
            om.feedback,
            tpocorrencia.de_tipo_ocorrencia
        FROM
            ocorrencia_morador om
        JOIN tipo_ocorrencia tpocorrencia
        ON om.id_tipo_ocorrencia = tpocorrencia.id_tipo_ocorrencia
        WHERE
        	(id_numero_imovel = $numero_imovel and id_bloco = $id_bloco OR $group_id_user <> 3) 
        ORDER BY
        	om.st_visto";

    }

    function getMinhasReservasSql($id_numero_imovel, $id_bloco) {

      return $sql = "SELECT rac.*, crac.de_cadastro_reserva_area_comum FROM reserva_area_comum rac
      JOIN cadastro_area_comum crac ON rac.id_cadastro_reserva_area_comum = crac.id_cadastro_reserva_area_comum
      WHERE status NOT IN ('cancelado','bloqueado') AND id_numero_imovel = '$id_numero_imovel' and id_bloco = '$id_bloco'
      order by idevento desc LIMIT 20";

    }

    function getOcorrenciasJson($result) {
      $retorno = mysql_num_rows($result);

      if($retorno == 0 ){
        $resultado[] = array("error" => true, "description: " => "não encontrado!");
        return json_encode($resultado);
      }else{
        while($consulta = mysql_fetch_array($result)) {

          // TODO 
          // Verificar se $consulta[9] existe antes de fazer as atribuicoes

          $nomeImagem = $nomeimagemtratada = str_replace(' ', '%20', $consulta[9]);

          //recuperando imagem local
          //$caminhoImagem = "imagens/".$nomeImagem;

          //recuperando imagem do servidor
          $caminhoImagem = "../_lib/file/img/".$nomeImagem;
          
          $imagem_comum = file_get_contents($caminhoImagem);
          $imagem64 = base64_encode($imagem_comum);

          //echo $imagem64;

          $resultado[] = array("numero_imovel" => $consulta[2], "id_bloco" => $consulta[3], "dthr" => $consulta[5],
          "descricao" => $consulta[6], "nu_placa" => $consulta[7], "marca_modelo" => $consulta[8],
          "tipo_ocorrencia" => $consulta[12], "imagem_ocorrencia" => $imagem64, "error" => false);
        }
        // echo "<pre>";
        // var_dump($resultado);
        // echo "</pre>";
        //return json_encode($resultado, JSON_UNESCAPED_SLASHES);
        //return stripslashes(json_encode($resultado));
        return str_replace('\/', '/', json_encode($resultado));
      }

    }

    function getConsultasReservaJson($result){
      $retorno = mysql_num_rows($result);

      if($retorno == 0 ){
        $resultado[] = array("error" => true, "description: " => "não encontrado!");
        return json_encode($resultado);
      }else{
        while($consulta = mysql_fetch_array($result)) {
          $resultado[] = array("idevento" => $consulta[0], "dt_data" => $consulta[1], "dt_hora_inicio" => $consulta[2], "dt_hora_fim" => $consulta[3],
          "id_numero_imovel" => $consulta[4], "id_bloco" => $consulta[5], "id_cadastro_reserva_area_comum" => $consulta[6], "id_area_pai" => $consulta[7],
          "nu_valor" => $consulta[8], "de_observacao" => $consulta[9], "status" => $consulta[10], "login" => $consulta[11], "dt_hr_solicitacao" => $consulta[12], "de_area_comum" => $consulta[13]);
        }
        return json_encode($resultado);
      }

    }

    //FUNCAO PARA PEGAR AS MENSAGENS DO FALE CONOSCO
    function getFeedbackFaleConoscoSql($id_fale_conosco){

      return $sql = "SELECT  
             ff.de_mensagem, 
             ff.dt_feedback
          FROM 
             feedback_fale_conosco  ff
          WHERE id_fale_conosco = $id_fale_conosco
          ORDER BY dt_feedback desc";

    }

    //MENSAGENS DO FEEDBACK DO FALE CONOSCO
    function getFeedbackFaleConoscoJson($result) {
      $retorno = mysql_num_rows($result);

      if($retorno == 0 ){
        $resultado[] = array("error" => true, "description: " => "não encontrado!");
        return json_encode($resultado);
      }else{
        while($consulta = mysql_fetch_array($result)) {
          $resultado[] = array("de_mensagem" => $consulta[0],
          "dt_feedback" => $consulta[1], "error" => false);
        }
        return json_encode($resultado);
      }

    }

}
?>
