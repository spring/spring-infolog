<?
global $Global;
$Global['BaseURL'] = $_SERVER['HTTP_HOST'] . dirname ($_SERVER['REQUEST_URI']);

function DoxTime ()	{
	$Time = microtime ();
	return (strstr ($Time, " ") + str_replace (strstr ($Time, " "), " ", $Time));
}


function DB_Query ($Query)	{
	global $Config;
	global $MySQL_Connection;
	if (!$MySQL_Connection)	{
		$MySQL_Connection = mysql_connect ($Config['MySQL']['host'], $Config['MySQL']['user'], $Config['MySQL']['passwd']);
		mysql_select_db ($Config['MySQL']['db'], $MySQL_Connection);
	}
	$Return = mysql_query ($Query, $MySQL_Connection);
	return ($Return);
}


function GetReport ($ID)	{
	$MySQL_Result = DB_Query ("SELECT * FROM records WHERE id='" . mysql_escape_string ($ID) . "'");
	$Return = mysql_fetch_assoc ($MySQL_Result);
	return ($Return);
}


function GetSettings ($ID)	{
	$MySQL_Result = DB_Query ("SELECT settingsdata.setting, settingsdata.value FROM settings LEFT JOIN settingsdata ON settings.settingid=settingsdata.id WHERE reportid='" . mysql_escape_string ($ID) . "'");
	while ($Data = mysql_fetch_assoc ($MySQL_Result))
		$Return[$Data['setting']] = $Data['Value'];
	return ($Return);
}


function GetStacktrace ($ID)	{
	$MySQL_Result = DB_Query ("SELECT * FROM stacktrace WHERE id='" . mysql_escape_string ($ID) . "'");
	while ($Data = mysql_fetch_assoc ($MySQL_Result))
		$Return[$Data['line']] = $Data;
	return ($Return);
}


function GetCrashes ()	{
	$MySQL_Result = DB_Query ("SELECT COUNT(id) FROM records WHERE crashed='1'");
	$Crashed = join ("", mysql_fetch_assoc ($MySQL_Result));
	$MySQL_Result = DB_Query ("SELECT settingsdata.id, settingsdata.setting, settingsdata.value, COUNT(records.id) AS Crashes FROM records LEFT JOIN settings ON records.id=settings.reportid LEFT JOIN settingsdata on settings.settingid=settingsdata.id WHERE crashed='1' GROUP BY settings.settingid");
	while ($Data = mysql_fetch_assoc ($MySQL_Result))
		$Return['Settings'][$Data['setting']][$Data['value']] = array ("ID" => $Data['id'], "Reports" => $Data['Crashes'], "Percentage" => number_format ($Data['Crashes'] / $Crashed * 100, 1, ".", ""));
	ksort ($Return['Settings']);
	foreach (array_keys ($Return['Settings']) as $Setting)
		ksort ($Return['Settings'][$Setting]);
	return ($Return);
}


function GetCrashes2 ()	{
	$MySQL_Result = DB_Query ("SELECT COUNT(id) FROM records WHERE crashed='1'");
	$Crashed = join ("", mysql_fetch_assoc ($MySQL_Result));
	$MySQL_Result = DB_Query ("SELECT stacktrace.file, stacktrace.functionname, stacktrace.functionat, stacktrace.address, COUNT(records.id) AS Crashes FROM records LEFT JOIN stacktrace ON records.id=stacktrace.id WHERE crashed='1' GROUP BY stacktrace.file, stacktrace.functionname, stacktrace.functionat, stacktrace.address");
	while ($Data = mysql_fetch_assoc ($MySQL_Result))	{
		$Return['Settings'][$Data['file']][$Data['address']]['Reports'] += $Data['Crashes'];
		$Return['Settings'][$Data['file']][$Data['address']]['Percentage'] = number_format ($Return['Settings'][$Data['file']][$Data['address']]['Reports'] / $Crashed * 100, 1, ".", "");
	}
	ksort ($Return['Settings']);
	foreach (array_keys ($Return['Settings']) as $Setting)
		ksort ($Return['Settings'][$Setting]);
	return ($Return);
}
?>