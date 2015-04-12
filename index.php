<?php
	include_once 'sqlclass.php';
	$sql = new sqlclass();
		
	if(isset($_POST['subverdi']))
	{
		$col='';
		$val='';
		foreach($_POST as $k =>$v)
		{
			if($k=='subverdi')
				continue;
				
			if($col=='')
				$col.=$k;
			else
				$col.=','.$k;
			
			if($k=='verdi' || $k=='insulin')
				$v=str_replace(',','.',$v);
				
			if($val=='')
				$val.="'$v'";
			else
				$val.=",'$v'";
		}
		
		if($sql->insert('diabetes',$col,$val))
			header('Location:index.php');
		else
			echo '<p>Failed</p>';
	}


	header("Content-Type: text/html;charset=iso-8859-1");
	date_default_timezone_set('Europe/Oslo');
	if((isset($_POST['verdi'])&&$_POST['verdi']=='diaKim')&&!isset($_COOKIE['diabetes']))
	{
		if(isset($_POST['save']))
		{
			setcookie('diabetes','1',time()+2592000,'/');
		}
	}
?>

<!DOCTYPE html>
<head>
	<meta name="viewport" content="width=device-width, minimum-scale=1, maximum-scale=1">
	<link rel="stylesheet" href="design.css" type="text/css"/>
</head>
<div align="center">
<?php
	
	if((isset($_POST['verdi'])&&$_POST['verdi']=='diaKim')||isset($_COOKIE['diabetes']))
	{
		
	$last=$sql->select("*,DATE_FORMAT(dato,'%d %H:%i') dag",'diabetes','','dato DESC',1);
	$avg=$sql->select('round(avg(verdi),1) avg','diabetes','sikker=1');
	$avgin=$sql->select('insulin avg,count(insulin)','diabetes group by insulin order by count(insulin) desc,dato');

	$arr=array();
	include_once 'Browser.php';
	$browser = new Browser();
	$mob = array('iPhone','iPad','iPod','Android','Opera Mini');
	$leser=$browser->getBrowser();

	if(in_array($leser,$mob) || $browser->isMobile())
		$txt='Måling <input id="verdi" style="width:30px;" name="verdi" type="number" value=""></input>';
	else
		$txt='Måling <input id="verdi" style="width:30px;" name="verdi" type="text" value=""></input>';
	
	$txt.=' <label title="Usikker" for="sikker">U</label><input id="sikker" name="sikker" type="checkbox" value="0"></input>';
	$arr[]=$txt;
	$txt='Insulin <select name="insulin">';
	for($i=0;$i<20;$i++)
	{
		if($i==$avgin[0]['avg'])
			$txt.= "<option selected='selected' value='$i'>$i</option>";
		else
			$txt.= "<option value='$i'>$i</option>";
	}
	$txt.= '</select>';
	$txt.=' <label for="kort">L</label><input id="kort" name="kort" type="checkbox" value="0"></input>';
	$arr[]=$txt;
	$arr[]='Kommentar/Mat';
	$arr[]='<input type="text" name="kommentar"></input>';

	
	$flag=($last[0]['sikker'])?'bullet_disk':'eye';
	
	echo '<div id="last">'.
	' <img src="img/'.$flag.'.png" alt="verdi"/>' .$last[0]['dag'].
	' <img src="img/table.png" alt="verdi"/>'.$last[0]['verdi'].
	' <img src="img/pill.png" alt="insulin"/>'.$last[0]['insulin'].
	' <img src="img/chart_curve.png" alt="avg"/>'.$avg[0]['avg'].
	'</div>';
?>
<p>
		<form action="" autocomplete=off method="post">

		<table id="table">
		<?php
		foreach($arr as $a)
			echo "<tr><td>$a</td></tr>";
		?>
		<tr><td><input name="subverdi" type="submit" value="Lagre"></input></td></tr>
		</table>
		</form>
</p>
<p>
<a href="chart.php">Charts</a>
</p>
	<script type="text/javascript">
	document.getElementById('verdi').focus();
	</script>
<?php
	}
	else
	{
?>
<p>
		<form action="" method="post">
			<input type="password" name="verdi" value=""></input>
			<input type="checkbox" name="save" value="1"></input>
			<input type="submit" name="open" value="send"></input>
		</form>
</p>
<?php
	}
?>
	</div>
