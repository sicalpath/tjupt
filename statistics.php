<?php
/* $Id: mysql_stats.php,v 1.0 2005/06/20 22:52:24 CoLdFuSiOn Exp $ */
// vim: expandtab sw=4 ts=4 sts=4:


require "include/bittorrent.php";
dbconn();
loggedinorreturn();
/**
 * Checks if the user is allowed to do what he tries to...
 */
if (get_user_class() < UC_ADMINISTRATOR)
	stderr("Error", "Permission denied.");

$fields_def = array("registered" => '注册用户', 
"totalonlinetoday" => "访问用户", 
"registered_male" => "男",
"registered_female" => "女", 
"torrents" => "种子数", 
"dead" => "断种数",
"totaltorrentssize" => "种子总大小",
"totalbonus" => "流通魔力" );

if ($_GET['action'] == 'query')
{
	$start_date = strtotime($_POST['start_date']);
	$end_date = strtotime($_POST['end_date']);
	$fields = '`' . implode($_POST['fields'], '`, `') . '`';
	
	foreach ($_POST['fields'] as $field)
	{
		if (!isset($fields_def[$field]))
		{
			die;
		}
	}
	
	$res = sql_query("SELECT `date`,$fields FROM statistics WHERE `date` BETWEEN '$start_date' AND '$end_date' ORDER BY `date` ASC") or die;
	
	$result = array();
	while ($row = mysql_fetch_assoc($res))
	{
		$result[] = $row;
	}
	
?>
	chart = new Highcharts.Chart({
	chart: {
		renderTo: 'graph',
		type: 'line'
	},
	title: {
		text: '<?php echo date('Y-m-d', $start_date);?>到<?php echo date('Y-m-d', $end_date);?>'
	},
	xAxis: {
		type: 'datetime',
		dateTimeLabelFormats: {
			day: '%m/%d',
			week: '%m/%d',
			month: '%y/%m',
			year: '%Y'
		}
	},
	yAxis: {
		title: {
			text: '统计数字'
		},
		min: 0
	},
	tooltip: {
		formatter: function() {
			return '<b>'+ this.series.name +'</b><br/>'+
				new Date(this.x).format("yyyy年MM月dd日 hh:mm") + ' - '+ this.y;
		}
	},
	series: [
<?php
foreach ($_POST['fields'] as $field)
{?>
		{
			name: '<?php echo $fields_def[$field];?>',
			data: [ 
<?php
foreach ($result as $row)
{?>
				[Date.UTC(<?php echo date('Y,m,d,H,i,s', $row['date']);?>), <?php echo $row[$field];?>],
<?php
}?>
			]
		},
<?php
}?>
	]
	});
<?php
	die;
}

stdhead("历史数据统计");
?>
<link href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css" rel="stylesheet" type="text/css"/>
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.5/jquery.min.js"></script>
<script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js"></script>
<script type="text/javascript" src="js/highcharts.js"/></script>
<script type="text/javascript" src="js/highchart.exporting.js"/></script>
<script type="text/javascript">
Date.prototype.format = function(format) { 
    var o = {  
        "M+" :this.getMonth(), // month  
        "d+" :this.getDate(), // day  
        "h+" :this.getHours(), // hour  
        "m+" :this.getMinutes(), // minute  
        "s+" :this.getSeconds(), // second  
        "q+" :Math.floor((this.getMonth() + 3) / 3), // quarter  
        "S" :this.getMilliseconds()  
    // millisecond  
    }  
  
    if (/(y+)/.test(format)) {  
        format = format.replace(RegExp.$1, (this.getFullYear() + "")  
                .substr(4 - RegExp.$1.length));  
    }  
  
    for ( var k in o) {  
        if (new RegExp("(" + k + ")").test(format)) {  
            format = format.replace(RegExp.$1, RegExp.$1.length == 1 ? o[k]  
                    : ("00" + o[k]).substr(("" + o[k]).length));  
        }  
    }  
    return format;  
} 
</script>

<table class="main" width="940" border="0" cellspacing="0" cellpadding="0">
<tr>
<td class="embedded">
<h1 align="center" class="pagetitle">北洋园PT :: 历史数据统计</h1>
<form id="filter_form" method="post">
<table>
<tr>
<td>
起始时间: <br/><input type="text" name="start_date" class="datepicker" value="<?php echo date('m/d/Y', time() - 31*86400);?>"/>
</td>
<td>
结束时间: <br/><input type="text" name="end_date" class="datepicker" value="<?php echo date('m/d/Y', time());?>"/>
</td>
<td>
统计项: <br/>
<input type="checkbox" name="fields[]" value="registered" checked="checked"/>注册用户 
<input type="checkbox" name="fields[]" value="totalonlinetoday" checked="checked"/>访问用户 
<input type="checkbox" name="fields[]" value="registered_male" checked="checked"/>男 
<input type="checkbox" name="fields[]" value="registered_female" checked="checked"/>女 
<input type="checkbox" name="fields[]" value="torrents"/>种子数 
<input type="checkbox" name="fields[]" value="dead"/>断种数 
<input type="checkbox" name="fields[]" value="totaltorrentssize"/>种子总大小 
<input type="checkbox" name="fields[]" value="totalbonus"/>流通魔力 
</td>
<td>
<button type="submit">统计</button>
</td>
</tr>
</table>
</form>
</td>
</tr>
<tr>
<td>
<div id="graph"><span style="font-color: #808080">请选择统计选项，点击“统计”查看图表。</span></div>
</td>
</tr>
</table>
<script type="text/javascript">
$('.datepicker').datepicker();
$('#filter_form').submit(
	function(){
		$('#graph').html('正在统计...');
		$.post('statistics.php?action=query', $('#filter_form').serialize(), function(script){
			eval(script);
		}, 'html');
		return false;
	}
);
</script>
<?php
stdfoot();
