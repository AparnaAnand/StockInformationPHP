<?php
session_start();
if(!isset($_POST['search']) && !isset($_GET['sym']))
{
	unset($_SESSION["inp"]);
}
if(isset($_POST["search"]))
{
	$_SESSION['inp']=$_POST['inp'];
}
?>
<html>
	<head>
		<style>
		#symTab th
		{
			text-align: left;
		}
		#symTab td
		{
			text-align: center;
		}
		table
		{
			border-collapse: collapse;
		}
		.center
		{
			margin-left:auto;
			margin-right:auto;
		}
		table, th, td
		{
			border-color: #cccccc;
		}
		th
		{
			background-color:#f5f5f0;
		}
		td
		{
			background-color:#fafafa;
		}
		.but
		{
			border-radius:1px;
			background-color:#fefefe;
		}
		</style>
		<script>
		function clearup()
		{
			document.getElementById("inp").value="";
			if(document.getElementById("searchTab"))
			{
				document.getElementById("searchTab").innerHTML="";
			}
			if(document.getElementById("symTab"))
			{
				document.getElementById("symTab").innerHTML="";
			}
		}
		</script>
	</head>
	<body>
		<table class='center' border=1 style="width:400px;height:150px;background-color:#f5f5f0">
			<tr><th style='text-align:center;background-color:#f5f5f0'><font size='5em' style='font-style:italic'>Stock Search</font></th></tr>
			<tr>
				<td style='background-color:#f5f5f0'>
					<form method='post' action='main.php'>
						<table border=0>
							<tr>
								<td style='background-color:#f5f5f0'>Company Name or Symbol:</td>
								<td style='background-color:#f5f5f0'><input type='text' name='inp' id='inp' value="<?php echo isset($_SESSION['inp']) ? $_SESSION['inp'] : ''; ?>" required></input></td>
							</tr>	
							<tr>	
								<td style='background-color:#f5f5f0'></td>
								<td style='background-color:#f5f5f0'><input class="but" type='submit' name='search' value='Search'/>  <input class="but" type='button' value='Clear' onclick="clearup()"/></td>
							</tr>
							<tr>
								<td style='background-color:#f5f5f0;text-align:right' colspan="2"><a href='http://www.markit.com/product/markit-on-demand' target="_blank">Powered by Markit on Demand</a></td>
							</tr>
						</table>
					</form>
				</td>
			</tr>
		</table>
		<br>
	</body>
</html>
<?php
if(isset($_POST["search"]))
{
	$_SESSION['inp']=$_POST['inp'];
	$xmlDoc='http://dev.markitondemand.com/MODApis/Api/v2/Lookup/xml?input='.$_POST['inp'];
	$sxe=simplexml_load_file($xmlDoc);
	echo '<table id="searchTab" border=1 width="600px" class="center">';
	if(count($sxe->children())<=0)
	{
		echo '<tr><td style="text-align:center">No records has been found</td></tr>';
	}
	else
	{
		echo '<tr align="left"><th>Name</th><th>Symbol</th><th>Exchange</th><th>Details</th></tr>';
		foreach ($sxe->children() as $child)
		{
			echo'<tr><td>'. $child->Name .'</td><td>'. $child->Symbol .'</td><td>'. $child->Exchange .'</td><td><a href="main.php?sym=' .$child->Symbol. '">More Info</a></td></tr>';
			//print_r($child);
 
		}
	}
	echo '</table>';
}
if(isset($_GET["sym"]))
{
	$jsonDoc='http://dev.markitondemand.com/MODApis/Api/v2/Quote/json?symbol='.$_GET["sym"];
	$jsonCon=file_get_contents($jsonDoc);
	$jDec=json_decode($jsonCon,true);
	echo '<table id="symTab" border=1 width="600px" class="center">';
	$lastprice=0;
	foreach($jDec as $key => $item)
	{
		if($key=="Name")
		{
			echo'<tr><th>'. $key .'</th><td>'. $item .'</td></tr>';
		}
		elseif($key=="Symbol")
		{
			echo'<tr><th>'. $key .'</th><td>'. $item .'</td></tr>';
		}
		elseif($key=="LastPrice")
		{
			$lastprice=$item;
			echo'<tr><th>Last Price</th><td>'. $item .'</td></tr>';
		}
		elseif($key=="Change")
		{
			echo'<tr><th>Change</th><td>';
			echo round($item,2);
			if($item>0)
			{
				echo '<img src="http://cs-server.usc.edu:45678/hw/hw6/images/Green_Arrow_Up.png" height=15px></td></tr>';
			}
			elseif($item<0)
			{
				echo '<img src="http://cs-server.usc.edu:45678/hw/hw6/images/Red_Arrow_Down.png" height=15px></td></tr>';
			}
			else
			{
				echo '</td></tr>';
			}
		}
		elseif($key=="ChangePercent")
		{
			echo'<tr><th>Change Percent</th><td>';
			echo round($item,2);
			echo '%';
			if($item>0)
			{
				echo '<img src="http://cs-server.usc.edu:45678/hw/hw6/images/Green_Arrow_Up.png" height=15px></td></tr>';
			}
			elseif($item<0)
			{
				echo '<img src="http://cs-server.usc.edu:45678/hw/hw6/images/Red_Arrow_Down.png" height=15px></td></tr>';
			}
			else
			{
				echo '</td></tr>';
			}
		}
		elseif($key=="Timestamp")
		{
			//$date=DateTime::createFromFormat('j-M-Y', $key
			date_default_timezone_set("EST");
			echo'<tr><th>Timestamp</th><td>'. date("Y-m-d h:i A e",strtotime($item)).'</td></tr>';
		}
		elseif($key=="Status")
		{
			if($item!="SUCCESS")
			{
				echo '<tr><td style="text-align:center">There is no stock information available</td></tr>';
				break;
			}
		}
		elseif($key=="MarketCap")
		{
			echo'<tr><th>Market Cap</th><td>';
			$mc=$item/1000000000;
			if($mc<0.005)
			{
				echo round(($mc*1000),2).' M</td></tr>';
			}
			else
			{
				echo round($mc,2).' B</td></tr>';
			}
		}
		elseif($key=="Volume")
		{
			echo'<tr><th>Volume</th><td>'. number_format($item).'</td></tr>';
		}
		elseif($key=="ChangeYTD")
		{
			echo'<tr><th>Change YTD</th>';
			if(($lastprice-$item)>0)
			{
				echo '<td>'.round($lastprice-$item,2).'<img src="http://cs-server.usc.edu:45678/hw/hw6/images/Green_Arrow_Up.png" height=15px></td></tr>';
			}
			elseif(($lastprice-$item)<0)
			{
				echo '<td>('.round($lastprice-$item,2).')<img src="http://cs-server.usc.edu:45678/hw/hw6/images/Red_Arrow_Down.png" height=15px></td></tr>';
			}
			else
			{
				echo '<td>'.round($lastprice-$item,2).'</td></tr>';
			}
		}
		elseif($key=="ChangePercentYTD")
		{
			echo'<tr><th>Change Percent YTD</th><td>'.round($item,2).'%';
			if($item>0)
			{
				echo '<img src="http://cs-server.usc.edu:45678/hw/hw6/images/Green_Arrow_Up.png" height=15px></td></tr>';
			}
			elseif($item<0)
			{
				echo '<img src="http://cs-server.usc.edu:45678/hw/hw6/images/Red_Arrow_Down.png" height=15px></td></tr>';
			}
			else
			{
				echo '</td></tr>';
			}
		}
		elseif($key=="High")
		{
			echo'<tr><th>'. $key .'</th><td>'. $item .'</td></tr>';
		}
		elseif($key=="Low")
		{
			echo'<tr><th>'. $key .'</th><td>'. $item .'</td></tr>';
		}
		elseif($key=="Open")
		{
			echo'<tr><th>'. $key .'</th><td>'. $item .'</td></tr>';
		}
		else
		{
		}
	}
	echo '</table>';
}
?> 