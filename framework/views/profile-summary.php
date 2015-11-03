<!-- start profiling summary -->
<table class="ivyLog" width="100%" cellpadding="2" style="border-spacing:1px;font:11px Verdana, Arial, Helvetica, sans-serif;background:#8DC4FA;color:#666666;">
	<tr>
		<th style="background:black;color:white;" colspan="6">
			Profiling Summary Report
			(Time: <?php echo sprintf('%0.5f',\Ivy::logger()->getExecutionTime()); ?>s,
			Memory: <?php echo number_format(\Ivy::logger()->getMemoryUsage()/1024); ?>KB)
		</th>
	</tr>
	<tr style="background-color: #ccc;">
	    <th>Procedure</th>
		<th>Count</th>
		<th>Avg(s)</th>
		<th>Total(s)</th>
	</tr>
<?php
$index=0;
foreach($data as $entry)
{
	$color=($index%2)?'#EBEBEB':'#FFFFFF';
	$proc=$entry['sql'];
	$num=$entry["num"];
	$average=$entry["avg_time"];
	$total=$entry["all_time"];

	echo <<<EOD
	<tr style="background:{$color}">
		<td>{$proc}</td>
		<td align="center">{$num}</td>
		<td align="center">{$average}</td>
		<td align="center">{$total}</td>
	</tr>
EOD;
$index++;
}
?>
</table>
<!-- end of profiling summary -->