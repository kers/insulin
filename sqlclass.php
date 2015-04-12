<?php

class sqlclass{

private $tilkobling;
private $dbName;
private $dbServer;
private $dbUser;
private $dbPass;

	function sqlclass()
	{
		include_once 'sqlInit.php';
	}	
	
	function connect()
	{
		//Kobler opp 
		$this->tilkobling = mysql_connect($this->dbServer, $this->dbUser, $this->dbPass) or die("Could not connect");
		mysql_select_db($this->dbName,$this->tilkobling);
	}
	
	function close()
	{
		//Avslutter tilkoblingen.
		mysql_close($this->tilkobling);
	}
	
	function sql($sqlq)
	{

		//Kobler opp 
		$tilkobling = mysql_connect($this->dbServer, $this->dbUser, $this->dbPass) or die("Could not connect");
		mysql_select_db($this->dbName,$tilkobling);

		//Spørring
		//$sqlq = "SELECT * FROM cia";

		//Resultat
		$resultat = mysql_query($sqlq,$tilkobling) or die(htmlspecialchars ($this->error(mysql_error()))); //or die(mysql_error())
		
		//Hver rad av resultat
		//$rad = mysql_fetch_array($resultat);
		return $resultat;

		//Fjerne data fra minne.
		mysql_free_result($resultat);
		//Avslutter tilkoblingen.
		mysql_close($tilkobling);

	}
	function test()
	{
		$this->error('Test');
	}
	function error($m)
	{
		echo $melding = $m . ' <br/> '. $_SERVER['SCRIPT_NAME'];
		$headers = 'Content-type: text/html; charset=iso-8859-1' . "\r\n";  
	  
		// Email the error to someone...  
	//	error_log($melding, 1, 'error@', $headers);
	}
	
	
	function select($what='*',$from='',$where='',$order='',$limit='')
	{

		
		$s='';
		if(!empty($what))
			$s.='SELECT '.$what.' ';
		if(!empty($from))
			$s.='FROM '.$from.' ';
		if(!empty($where))
			$s.='WHERE '.$where.' ';
		if(!empty($order))
			$s.='ORDER BY '.$order.' ';
		if(!empty($limit))
			$s.='LIMIT '.$limit;
//echo $s.'<br/>';
		$arr=$this->sql($s);
		$rad=array();
		for($i=0;$i<mysql_num_rows($arr);$i++)
			$rad[] = mysql_fetch_array($arr);
			
		return $rad;
	}
	
	function ant($what,$from,$where='',$limit='',$order='')
	{
		$s='';
		if(!empty($what))
			$s.='SELECT '.$what.' ';
		if(!empty($from))
			$s.='FROM '.$from.' ';
		if(!empty($where))
			$s.='WHERE '.$where.' ';
		if(!empty($order))
			$s.='ORDER BY '.$order.' ';
		if(!empty($limit))
			$s.='LIMIT '.$limit;

		$arr=$this->sql($s);
		
		return mysql_num_rows($arr);
	}

	function check($what,$from,$where='')
	{
		$s='';
		if(!empty($what))
			$s.='SELECT '.$what.' ';
		if(!empty($from))
			$s.='FROM '.$from.' ';
		if(!empty($where))
			$s.='WHERE '.$where;

		$arr=$this->sql($s);
		$tr=mysql_fetch_array($arr);

		if(!empty($tr[0]))
			return true;
		else
			return false;
	}

	function insert($into,$col,$info)
	{

		$s='';
		if(!empty($into))
			$s.='INSERT INTO '.$into.' ';
		if(!empty($col))
			$s.='('.$col.') ';
		if(!empty($info))
			$s.='VALUES ('.$info.')';

		if($this->sql($s))
			return true;
		else
			return false;
	}
	
	function update($into,$col,$info)
	{
	
		$s='';
		if(!empty($into))
			$s.='UPDATE '.$into.' ';
		if(!empty($col))
			$s.='SET '.$col.' ';
		if(!empty($info))
			$s.='WHERE '.$info.' ';

		if($this->sql($s))
			return true;
		else
			return false;
	}

	function del($from,$where='')
	{

		$s='';
		if(!empty($from))
			$s.='DELETE FROM '.$from.' ';
		if(!empty($where))
			$s.='WHERE '.$where;

		if($this->sql($s))
			return true;
		else
			return false;
	}

}

?>