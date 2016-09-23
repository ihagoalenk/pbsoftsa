<?php

error_reporting(E_ERROR);
date_default_timezone_set('America/Recife');

class Ocorrencia{

  ## RECLAMANTE ###

  function getReclamanteSql($nr_imovel, $id_bloco){
    return $sql = "SELECT id_morador, de_nome, dt_nascimento,nu_telefone,email,de_sexo,necessidade_especial,
    cadeirante,dt_entrada,dt_saida,st_ativo, tp_morador FROM morador WHERE id_numero_imovel = '$nr_imovel' AND id_bloco = '$id_bloco'";
  }

  function getReclamanteJson($result) {
    $retorno = mysql_num_rows($result);

    if($retorno == 0 ){
      $resultado[] = array("error" => true, "description: " => "não encontrado!");
      return json_encode($resultado);
    }else{
      while($consulta = mysql_fetch_array($result)) {
        $resultado[] = array("id_morador" => $consulta[0], "de_nome" => $consulta[1],
        "dt_nascimento" => $consulta[2], "nu_telefone" => $consulta[3], "email" => $consulta[4],
        "de_sexo" => $consulta[5], "necessidade_especial" => $consulta[6], "cadeirante" => $consulta[7],
        "dt_entrada" => $consulta[8], "dt_saida" => $consulta[9], "st_ativo" => $consulta[10],
        "tp_morador" => $consulta[11], "error" => false);
      }
      return json_encode($resultado);
    }

  }

  #### TIPO OCORRENCIA #####

  var $sqlTipoOcorrencia = "SELECT id_tipo_ocorrencia, de_tipo_ocorrencia FROM tipo_ocorrencia";

  function getTipoOcorrenciaJson($result) {
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

  }

  ## CADASTRAR OCORRENCIA ##

  function cadastrarOcorrenciaNova($id_morador, $id_numero_imovel, $id_bloco, $id_tipo_ocorrencia, $dt_hr_ocorrencia, $de_descricao_ocorrencia, $img_ocorrencia){
    
    if (!empty($img_ocorrencia) and isset($img_ocorrencia)) {
      $imagem = 'data:image/jpeg;base64,'.$img_ocorrencia;
      $novoNome = md5(uniqid()).'.jpg';
      //salvando imagem local
      //copy($imagem, "imagens/$novoNome");

      //salvando imagem no servidor
      copy($imagem, "../_lib/file/img/$novoNome");
    } else {
      $novoNome = NULL;
    }
    $sqlInsert = mysql_query("INSERT INTO ocorrencia_morador(id_morador, id_numero_imovel, id_bloco, id_tipo_ocorrencia, dt_hr_ocorrencia, de_descricao_ocorrencia, st_visto, img_ocorrencia) VALUES ('$id_morador', '$id_numero_imovel', '$id_bloco', '$id_tipo_ocorrencia', '$dt_hr_ocorrencia', '$de_descricao_ocorrencia', 'N', '$novoNome')");

    if ($sqlInsert) {
        $resultado[] = array("error" => false, "description" => "Ocorrência cadastrada com sucesso.");
    } else {
        $resultado[] = array("error" => true, "description" => "Não foi possível cadastrar sua ocorrência.");
    }

    echo base64_encode(json_encode($resultado));
  }

}

?>
