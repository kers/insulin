<?php
	header("Content-Type: text/html;charset=iso-8859-1");
	date_default_timezone_set('Europe/Oslo');
include_once 'sqlclass.php';
$sql = new sqlclass();
$mnd=array(1 => 'Jan','Feb','Mar','Apr','Mai','Jun','Jul','Aug','Sep','Okt','Nov','Des');

$all=$sql->select("*,date_format(dato,'%m.%d') dag",'diabetes');
$inputs=sizeof($all);

if(isset($_GET['dag']))
	$getday=$_GET['dag'];
else
	$getday=date('Y-m-d');
$dagen=$sql->select("*,date_format(dato,'%Y,%m-1,%d,%H,%i,%s,0') klokken, date_format(dato,'%d.%m. %H:%i') stamp",'diabetes',"date(dato)='$getday'",'dato');
$antdag=sizeof($dagen);

if(isset($_GET['uke']))
	$getweek=$_GET['uke'];
else
	$getweek=date('YW');

$uken=$sql->select("*,date_format(dato,'%Y,%m-1,%d,%H,%i,%s,0') klokken, date_format(dato,'%d.%m. %H:%i') stamp",'diabetes',"yearweek(dato,1)='$getweek'",'dato');

$antuke=sizeof($uken);
$avgset=$sql->select("distinct(date_format(dato,'%m.%d')) a,date(dato) dato, date_format(dato,'%m') mnd, date_format(dato,'%d') d,round(sum(verdi)/count(nr),1) avg,date_format(dato,'%Y,%m-1,%d') dag",'diabetes','sikker=1 GROUP BY date(dato)','dato');
$antavg=sizeof($avgset);
$avg=$sql->select('round(avg(verdi),1)','diabetes','sikker=1');
$first=$sql->select("date_format(dato,'%Y,%m-1,%d') dag",'diabetes','dato=(select min(dato) from diabetes)');
$last=$sql->select("date_format(dato,'%Y,%m-1,%d') dag",'diabetes','dato=(select max(dato) from diabetes)');


if(isset($_GET['std']))
	$standard=$_GET['std'];
else
	$standard='6.7';

if(isset($_GET['top']))
	$top=$_GET['top'];
else
	$top='6.7';

if(isset($_GET['bunn']))
	$bunn=$_GET['bunn'];
else
	$bunn='0';

	
	include_once 'Browser.php';
	$browser = new Browser();
	$mob = array('Android');
	$leser=$browser->getBrowser();
	if(in_array($leser,$mob))
		$imagechart=true;
	else
		$imagechart=false;
		
		
	if($imagechart===true)
		$packages="'imageareachart'";
	else
		$packages="'corechart', 'controls'";
?>
	<script type="text/javascript" src="https://www.google.com/jsapi"></script>
	<script type="text/javascript">
      google.load('visualization', '1.1', {packages: [<?php echo $packages;?>]});
	  
	function createComment(tittel,innhold)
	{	
		var tx='<p>'+'<table>';
		for (i=0;i<tittel.length;i++)
			tx+= '<tr>'+'<td>'+''+tittel[i]+':'+'</td>'+'<td><strong>'+innhold[i]+'</strong></td>'+'</tr>';
		
		tx+='</table>'+'</p>';
		
		return tx;
	}

    </script>
	
	<?php
	
	chart($imagechart,'dag',$dagen,"Dag ($getday)",$top,$bunn);
	chart($imagechart,'uke',$uken,"Uke ($getweek)",$top,$bunn);
	
	function chart($imagechart=false,$divname,$data,$tittel,$top='6.7',$bunn='3')
	{
		$ant=sizeof($data);
		if($ant>0){
			$sum=0;
			$sant=0;
			foreach($data as $a)
			{
				if($a['sikker'])
				{
					$sum+=$a['verdi'];
					$sant++;
				}
			}
			$snitt=round($sum/$sant,1);
		}
		else
			$snitt=0;
		echo '<div id="chart_'.$divname.'"></div>';
		
	    echo "<script type='text/javascript'>
      google.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = new google.visualization.DataTable();
        data.addColumn('datetime', 'Dag');";
		
		if($imagechart===true)
		{
			echo "
			data.addColumn('number', 'Verdi (n=$ant)');
			data.addColumn('number', 'Top ($top)');
			";
		}
		else
		{
			echo "data.addColumn('number', 'Snitt ($snitt)');
			data.addColumn('number', 'Verdi (n=$ant)');
			data.addColumn({type:'string', role:'tooltip', p: {html: true}});
			data.addColumn({type:'number', role:'annotation'});
			data.addColumn({type:'string', role:'annotationText', p: {html: true}});
			data.addColumn({type:'boolean',role:'certainty'});
			data.addColumn('number', 'Top ($top)');
			data.addColumn('number', 'Bunn ($bunn)');
			";
		}
		
		foreach($data as $a)
		{
			$s=($a['sikker'])?'true':'false';
			$type=($a['kort']==0)?'Langvarig':'Kortvarig';
			echo 'var date = new Date('.$a['klokken'].');';
			if($imagechart===false)
			{
			echo 'data.addRow([date, '.$snitt.','.$a['verdi'].", createComment(".'Array("Dato","Verdi","Kommentar")'.",".'Array("'.$a['stamp'].'","'.$a['verdi'].'","'.$a['kommentar'].'")'.")".','.$a['insulin'].','."createComment(".'Array("Insulintype")'.",".'Array("'.$type.'")'.")".','.$s.','.$top.','.$bunn.']);';
			}
			else
			{
				echo 'data.addRow([date, '.$a['verdi'].','.$top.']);';
			}
		}
		
		
		if($imagechart===true)
			echo "var chart = new google.visualization.ImageAreaChart(document.getElementById('chart_$divname')).draw(data, {colors:['blue','green'],
                  width: '100%', height: 300, title: '$tittel',
                  vAxis: {minValue: 0},
				  hAxis: {format: 'd. HH:MM'}});";
		else
		{
			echo "var chart = new google.visualization.AreaChart(document.getElementById('chart_$divname'));
			chart.draw(data,  {colors:['#ffffff','blue','green','gray'],
                  width: '100%', height: 300, title: '$tittel',
                  vAxis: {minValue: 0},
				  hAxis: {format: 'd. HH:MM'},
				  tooltip: { isHtml: true }}
				  );";
		}
		
		echo "
      }
    </script>
	";
	
	
	}
	
	if($imagechart===true)
		exit;
	?>

    <script type="text/javascript">
      function drawVisualization() {
        var dashboard = new google.visualization.Dashboard(
             document.getElementById('dashboard'));
      
         var control = new google.visualization.ControlWrapper({
           'controlType': 'ChartRangeFilter',
           'containerId': 'control',
           'options': {
             // Filter by the date axis.
             'filterColumnIndex': 0,
             'ui': {
               'chartType': 'LineChart',
               'chartOptions': {
                 'chartArea': {'width': '80%','height':'50'},
                 'hAxis': {'baselineColor': 'none'}
               },
               // Display a single series that shows the closing value of the stock.
               // Thus, this view has two columns: the date (axis) and the stock value (line series).
               'chartView': {
                 'columns': [0, 2]
               },
				// 1 day in milliseconds = 24 * 60 * 60 * 1000 = 86,400,000
				'minRangeSize': 172800000//86400000
             }
           },
           // Initial range: 2012-02-09 to 2012-03-20.
           'state': {'range': {'start': new Date(<?php echo $first[0]['dag']; ?>), 'end': new Date(<?php echo $last[0]['dag']; ?>)}}
         });
      
         var chart = new google.visualization.ChartWrapper({
           'chartType': 'AreaChart',
           'containerId': 'chart',
           'options': {
             // Use the same chart area width as the control for axis alignment.
             'chartArea': {'height': '300px', 'width': '80%'},
			 colors:['#ffffff','blue','green'],
                  width: '100%', height: 300, title: 'Snitt per dag',
                  vAxis: {minValue: 0},
				  tooltip: { isHtml: true }
           }
         });
      
         var data = new google.visualization.DataTable();
         data.addColumn('date', 'Date');
		 data.addColumn('number', 'Snitt (<?php echo $avg[0][0]; ?>)');
         data.addColumn('number', 'Verdi (n=<?php echo $antavg; ?>)');
		 data.addColumn({type:'string', role:'tooltip', p: {html: true}});
		 data.addColumn('number', 'Std (<?php echo $standard; ?>)');
      <?php
	  foreach($avgset as $u)
	  {
			echo 'var date = new Date('.$u['dag'].');';
			echo 'data.addRow([date, '.$avg[0][0].','.$u['avg'].",createtooltip('".$u['dato']."','".$u['avg']."')".','.$standard.']);';
	  }
	  ?>
      var formatter = new google.visualization.DateFormat({formatType:'short'});
		formatter.format(data, 0);
  
         dashboard.bind(control, chart);
         dashboard.draw(data);
      }
      

      google.setOnLoadCallback(drawVisualization);
	  
	  
	function createtooltip(dato,verdi)
	{
		return '<p>'+
		'<table>'+
		'<tr>'+'<td>'+'Verdi:'+'</td>'+'<td>'+verdi+'</td>'+'</tr>'+
		'<tr>'+'<td>'+'Dato:'+'</td>'+'<td>'+dato+'</td>'+'</tr>'+
		'<tr>'+'<td>'+'Link:'+'</td>'+'<td><a href="?dag='+dato+'">Vis dagen</a></td>'+'</tr>'+
		'</table>'+
		'</p>';
	}
    </script>

    <div id="dashboard">
        <div id="chart" ></div>
        <div id="control" ></div>
    </div>