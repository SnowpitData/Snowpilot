<?php

session_start();

// Functions to validate start and end dates
function validateDates($start, $end){
    // make sure start date is earkire than end data
    if ($start < $end){
		return TRUE;
	}else{
        return FALSE;
    }
}

function updateSessionVars($arr){
	foreach($arr as $key=>$value){
			$_SESSION[$key] = $value;
	}
}

// Define variables and initialize with empty values
$dateErr = "";
$textParam = "";
$dotParam = "ECT";
$lineParam = "";
$observerParam = "All";
$stateParam = "CO";
$show_avs = "Daily count mean";
$startDate = date("Y-m-d", strtotime(date("Y-m-d", strtotime("-14 day"))));
$endDate = date("Y-m-d",time());


// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
	if($_POST["Start_Date"] > $_POST["End_Date"]){
		$dateErr = 'Dates error, please check you dates';
	}
    if (empty($_POST["Start_Date"])){
      $startDate = date("Y-m-d", strtotime(date("Y-m-d", strtotime("-14 day"))));
	} else{
        $startDate = $_POST["Start_Date"];
    }
    if (empty($_POST["End_Date"])){
	    $endDate = date("Y-m-d",time());
    } else{
	    $endDate = $_POST["End_Date"];
    }
	if (empty($_POST["text"])){
		$textParam = "";
	} else{
		$textParam = $_POST["text"];
	}
	if (empty($_POST["dot_color"])){
		$dotParam = "ECT";
	} else{
		$dotParam = $_POST["dot_color"];
	}
	if (empty($_POST["line_color"])){
		$lineParam = "";
	} else{
		$lineParam = $_POST["line_color"];
	}
	if (empty($_POST["observer"])){
		$observerParam = "All";
	} else{
		$observerParam = $_POST["observer"];
	}
	if (empty($_POST["State"])){
		$stateParam = "CO";
	} else{
		$stateParam = $_POST["State"];
	}
	if (isset($_POST["show_avs"])){
		$show_avs = $_POST["show_avs"];
	} else {
		$show_avs = "Daily count mean";
	}

}	
//$result = "CO_base_pit_map.html";
if (empty($dateErr)){
	
	$params = array("start_date"=>$startDate,
	                "end_date"=>$endDate,
					  "state"=>utf8_encode($stateParam),
					  "text_param"=>utf8_encode($textParam),
					  "dot_color_param"=>utf8_encode($dotParam),
					  "line_color_param"=>utf8_encode($lineParam),
					  "observer_group"=>utf8_encode($observerParam),
					  "show_avs"=>utf8_encode($show_avs));
	updateSessionVars($params);
	$command = "python3 /home/ron/scripts/realtime-tools/src/draw_map.py " . "-p " . json_encode($params);
    $result = shell_exec($command ." 2>&1");
  //echo $command . "<br>";
  //echo $result . "<BR>";
  //echo gethostname();
  echo "<br>";
} else {
	echo $dateErr . "<br>";
	
}


?>


<!DOCTYPE html>

<head>    
    <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
	<style>
     a.read_me:link {
                     font-size: 16px;
                    }
     h1 {
          font-weight: bold;
          font-size: 26px;
        }
     form {
           font-size: 12px;
          }
    //body {
    //   transform: scale(0.95);
    //   transform-origin: 0 0;
      //}	
  </style>
</head>
<body>
  <a class="read_me" href="src/ReadMe.pdf" target="_blank">Read Me</a>
	<div style = "float: right; display: inline-block;">You are <?php echo $user_msg;  ?></div>
	<h1 align="center"><b> Snow Pit Viewer</b></h1>

	<form action="realtime-map.php" method="post">
	  <label form="Start Date">Start Date:</label>
	  <input type="date" value="<?php echo $_SESSION["start_date"]; ?>" id="Start_Date" name="Start_Date">
	  &nbsp
	  <label form="End Date">End Date:</label>
      <input type="date" value="<?php echo $_SESSION["end_date"]; ?>" id="End_Date" name="End_Date">
	  &nbsp
	  <label for="Text">Text:</label>
		<select name="text" id="text">
		  <option value="<?php echo $_SESSION["text_param"]; ?>"  selected><?php echo $_SESSION["text_param"]; ?></option>
		  <option value=""></option>
		  <option value="ECT">ECT</option>
		  <option value="PST">PST</option>
		  <option value="CT">CT</option>
		</select>
	  &nbsp
	  <label for="dot_color">Dot Color:</label>
		<select name="dot_color" id="dot_color">
		  <option value="<?php echo $_SESSION["dot_color_param"]; ?>"  selected><?php echo $_SESSION["dot_color_param"]; ?></option>
		  <option value="ECT">ECT</option>
		  <option value="HS">HS</option>
		  <option value="ACTIVITY">ACTIVITY</option>
		  <option value="LEMONS">LEMONS</option>
		</select>
	  &nbsp
	  <label for="line_color">Border Color:</label>
		<select name="line_color" id="line_color">
		  <option value="<?php echo $_SESSION["line_color_param"]; ?>"  selected><?php echo $_SESSION["line_color_param"]; ?></option>
		  <option value=""></option>
		  <option value="ECT">ECT</option>
		  <option value="HS">HS</option>
		  <option value="ACTIVITY">ACTIVITY</option>
		  <option value="LEMONS">LEMONS</option>
		</select>
	  &nbsp
	  <label for="Observer">Observer:</label>
		<select name="observer" id="observer">
		  <option value="<?php echo $_SESSION["observer_group"]; ?>"  selected><?php echo $_SESSION["observer_group"]; ?></option>
		  <option value="All">All</option>
		  <option value="Pro">Pros</option>
		  <option value="CAIC">CAIC</option>
		</select>
	  &nbsp
	  <label for="State">State:</label>
		<select name="State" id="State">
		  <option value="<?php echo $_SESSION["state"]; ?>"  selected><?php echo $_SESSION["state"]; ?></option>
		  <option value="CO">CO</option>
		  <option value="MT">MT</option>
		</select>
	  &nbsp
	  <label for="show_avs">Avalanches:</label>
		<select name="show_avs" id="show_avs">
		  <option value="<?php echo $_SESSION["show_avs"]; ?>"  selected><?php echo $_SESSION["show_avs"]; ?></option>
		  <option value="Daily count mean">Daily count mean</option>
		  <option value="Daily AAI mean">Daily AAI mean</option>
		</select>
	  &nbsp
	  
	  <input type="submit">
	</form>
	
	<hr style="height:5px;border-width:0;color:gray;background-color:gray">
	<?php
	
	if ($user->uid ){
		//var_dump($user);
	?>
    <iframe align="center" src="<?php echo $result; ?>" width=100% height=80%  style="border:1px solid black;"></iframe>
		<?php
			}			
		?>
</body>

</html>