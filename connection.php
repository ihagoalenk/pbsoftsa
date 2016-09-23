<?php

class Connection{
// development
  // var $user = "root";
  // var $password = "qwe123";
  // var $sid = "localhost";
  // var $database = "sindico_ibiza";
//production
  var $user = "sindico_ri";
  var $password = "In@sAB#Wu";
  var $sid = "sindicoamigo.com.br";
  var $database = "sindico_resibiza";

  var $query = "";

	var $link = "";

  function Connection(){
		$this->Connect();
  }

	function Connect(){
		$this->link = mysql_connect($this->sid,$this->user,$this->password);
    mysql_set_charset('UTF8', $this->link);
		if (!$this->link){
			die("Problem in database connection :/");
		}elseif (!mysql_select_db($this->database,$this->link)){
			die("Problem in database connection :/");
		}
	}

  function Disconnect(){
		return mysql_close($this->link);
	}

  function Query($query){
    $this->query = $query;
		if ($result = mysql_query($this->query,$this->link)){
			return $result;
    } else {
			return 0;
		}
	}
}

?>
