<?php
include 'dbconfig.php';

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 
//echo "test";

if(isset($_GET['user'],$_GET['pass'])){
	if(userAuth($_GET['user'],$_GET['pass'],$conn)){
		switch($_GET['method']){
			case "postin":
				if(isset($_GET['serialnumber'])){in_entry($_GET['serialnumber'],$conn);}
				else{echo "Error: Parameter '".$_GET['method']."' tidak sesuai !";}
			break;
			case "postout":
				if(isset($_GET['serialnumber'])){exit_update($_GET['serialnumber'],$conn);}
				else{echo "Error: Parameter '".$_GET['method']."' tidak sesuai !";}
			break;
			case "lookup":
				if(isset($_GET['serialnumber'])){jsonInfobySN2($_GET['serialnumber'],$conn);}
				else{echo "Error: Parameter '".$_GET['method']."' tidak sesuai !";}
			break;
			case "postloc":
				if(isset($_GET['serialnumber'],$_GET['area'],$_GET['position'])){locating_update($_GET['serialnumber'],$_GET['area'],$_GET['position'],$conn);}
				else{echo "Error: Parameter '".$_GET['method']."' tidak sesuai !";}			
			break;
			case "postreloc":
				if(isset($_GET['serialnumber'],$_GET['area'],$_GET['position'])){relocating_update($_GET['serialnumber'],$_GET['area'],$_GET['position'],$conn);}
				else{echo "Error: Parameter '".$_GET['method']."' tidak sesuai !";}		
			break;
			case "searchbydate":
				if(isset($_GET['startdate'],$_GET['enddate'])){jsonRangebyDate($_GET['startdate'],$_GET['enddate'],$conn);}
				else{echo "Error: Parameter '".$_GET['method']."' tidak sesuai !";}
			break;
			case "searchbyday":
				if(isset($_GET['numdays'])){jsonRangebyDays($_GET['numdays'],$conn);}
				else{echo "Error: Parameter '".$_GET['method']."' tidak sesuai !";}
			break;
			case "query":
				if(isset($_GET['table'],$_GET['args'])){jsonQueryArg($_GET['table'],$_GET['args'],$conn);}
				else{echo "Error: Parameter '".$_GET['method']."' tidak sesuai !";}
			break;
			case "post_io":
				if(isset($_GET['field'],$_GET['value'])){post_io($_GET['field'],$_GET['value'],$conn);}
				else{echo "Error: Parameter '".$_GET['method']."' tidak sesuai !";}
			break;
			case "get_io":
				if(isset($_GET['field'])){get_io($_GET['field'],$conn);}
				else{echo "Error: Parameter '".$_GET['method']."' tidak sesuai !";}
			break;
			case "get_2io":
				get_2io($conn);
			break;
			case "info":
				jsonQueryInfo($conn);
			break;
			case "test":
				jsonInfobySN2("MHMFE74PHJK000558",$conn);
			break;
		}
		
	}
}

function post_io($field, $value, $connection){
	
	// Check connection
	if ($connection->connect_error) {
	    die("Connection failed: " . $connection->connect_error);
	} 
	$sql = "INSERT INTO `tbl_gate_broker` (`field`, `value`, `timestamp`) VALUES ('".$field."', '". $value ."',CURRENT_TIMESTAMP);";
	//echo $sql;
	
	if ($connection->query($sql) === TRUE) {
	    echo "{status:1}";
	} else {
	    echo "{status:-1}" . $connection->error;
	}
}

function get_io($field, $connection){
	
	// Check connection
	if ($connection->connect_error) {
	    die("Connection failed: " . $connection->connect_error);
	} 
	$sql = "SELECT * FROM `tbl_gate_broker` WHERE field='".$field."' ORDER BY ID DESC LIMIT 1";
	//echo $sql;
	
	$result = $connection->query($sql);
	
	if ($result->num_rows > 0) {
		// output data of each row
		while($row = $result->fetch_assoc()) {
			echo " {
						\"status\":1,
						\"field\":\"".$field."\",
						\"value\":\"".$row["VALUE"]."\"
					}";
		}
	} else {
		echo "{status:-1}";
	}
}

function get_2io($connection){
	
	// Check connection
	if ($connection->connect_error) {
	    die("Connection failed: " . $connection->connect_error);
	} 
	$sql = "
		SELECT 
			(SELECT tbl_gate_broker.value FROM `tbl_gate_broker` WHERE field = 'gateIn' ORDER BY id DESC LIMIT 1) as Gin, 
		    (SELECT tbl_gate_broker.value FROM `tbl_gate_broker` WHERE field = 'gateOut' ORDER BY id DESC LIMIT 1) as Gou
		FROM `tbl_gate_broker`LIMIT 1
	";
	//echo $sql;
	
	$result = $connection->query($sql);
	
	if ($result->num_rows > 0) {
		// output data of each row
		while($row = $result->fetch_assoc()) {
			echo " {
						\"status\":1,
						\"gateIn\":\"".$row["Gin"]."\",
						\"gateOut\":\"".$row["Gou"]."\"
					}";
		}
	} else {
		echo "{status:-1}";
	}
}


function in_entry($serialnumber,$connector){
	if(isExist($serialnumber,$connector)){
		if(getStatus($serialnumber,$connector)<2){
			echo "Serial Number sudah terdaftar !";
			return false;
		}
		else{
			$sql = "
			UPDATE `tbl_parking` SET `statuscode`=0,`enterdate`=CURRENT_TIMESTAMP,`exitdate`='0000-00-00 00:00:00.000000',`parkingarea`=NULL,`position`=NULL WHERE serialnumber='".$serialnumber."' ;
			";
			
			if ($connector->query($sql) === TRUE) {
				echo "New record created successfully";
			} else {
				echo "Error: " . $sql . "<br>" . $connector->error;
			}
			
			return true;
		}
		
	}
	else{	
		 $sql = "
		 INSERT INTO `tbl_parking` (`id`, `serialnumber`, `statuscode`, `enterdate`, `exitdate`, `parkingarea`, `position`) VALUES (NULL, '". $serialnumber ."', '0', CURRENT_TIMESTAMP, '0000-00-00 00:00:00.000000', NULL, NULL);

			INSERT INTO tbl_gate_broker (field,value) VALUES('gateIn','1');
		 ";
		 //$sql.= "UPDATE `tbl_count` SET tbl_count.count = tbl_count.count + 1 WHERE id=101 ;";
			//echo $sql;
		if ($connector->multi_query($sql) === TRUE) {
			echo "New record created successfully";
		} else {
			echo "Error: " . $sql . "<br>" . $connector->error;
		}
		
		return true; 
	}
	
}

function relocating_update($serialnumber,$area,$position,$connector){
	if(isExist($serialnumber,$connector)){
		
		if(getStatus($serialnumber,$connector)==1){
			
			$sql = "UPDATE `tbl_parking` SET `statuscode`=1,`parkingarea`='".$area."',`position`=".$position." WHERE serialnumber='".$serialnumber."' ;";
			
			if ($connector->query($sql) === TRUE) {
				echo "New record created successfully";
			} else {
				echo "Error: " . $sql . "<br>" . $connector->error;
			}
			
			return true;
		}
		{
			echo "Tidak memiliki nomor parkir ! ";
			return false;
		}
		
	}
	else{
	    echo "Serial Number tidak terdaftar !";
		return false; 
	}
	
}

function locating_update($serialnumber,$area,$position,$connector){
	if(isExist($serialnumber,$connector)){
		
		if(getStatus($serialnumber,$connector)==0){
			
			$sql = "UPDATE `tbl_parking` SET `statuscode`=1,`parkingarea`='".$area."',`position`=".$position." WHERE serialnumber='".$serialnumber."' ;";
			
			if ($connector->query($sql) === TRUE) {
				echo "New record created successfully";
			} else {
				echo "Error: " . $sql . "<br>" . $connector->error;
			}
			
			return true;
		}
		{
			echo "Serial Number telah terdaftar. ";
			return false;
		}
		
	}
	else{	
		echo "Serial Number tidak terdaftar";	
		return false; 
	}
	
}

function exit_update($serialnumber,$connector){
	if(isExist($serialnumber,$connector)){
		
		if(getStatus($serialnumber,$connector)<2){
			
			$sql = "
			UPDATE `tbl_parking` SET `exitdate`=CURRENT_TIMESTAMP WHERE serialnumber='".$serialnumber."' ;
			
			INSERT INTO `tbl_outHistory` select * from `tbl_parking` WHERE serialnumber='".$serialnumber."';
			
			DELETE FROM `tbl_parking` WHERE serialnumber='".$serialnumber."';

			INSERT INTO tbl_gate_broker (field,value) VALUES('gateOut','1');
			";
			//$sql.= "UPDATE `tbl_count` SET tbl_count.count = tbl_count.count + 1 WHERE id=102 ;";
			
			
			if ($connector->multi_query($sql) === TRUE) {
				echo "New record created successfully";
			} else {
				echo "Error: " . $sql . "<br>" . $connector->error;
			}
			
			return true;
		}
		{
			echo "Serial Number telah meninggalkan parkir !";
			return false;
		}
		
	}
	else{	
		echo "Serial Number tidak terdaftar atau telah meninggalkan parkir !";	
		return false; 
	}
	
}

function getStatus($serialnumber, $connector){
	$sql = "SELECT statuscode FROM tbl_parking WHERE serialnumber='". $serialnumber ."';";
	$result = $connector->query($sql);
	
	
	if ($result->num_rows > 0) 
	{
		while($row = $result->fetch_assoc()) {
			return $row["statuscode"];
		}
	}
	
}

function getID($user,$apikey, $connector){
	$sql = "SELECT id FROM user WHERE user='". $user ."' AND apikey='" . $apikey . "';";
	$result = $connector->query($sql);
	
	
	if ($result->num_rows > 0) 
	{
		while($row = $result->fetch_assoc()) {
			return $row["id"];
		}
	}
	
}

function isExist($serialnumber, $connector){
	$sql = "SELECT * FROM tbl_parking WHERE serialnumber='". $serialnumber ."';";
	$result = $connector->query($sql);

	if ($result->num_rows > 0) {
		return true;
	}
	else{
		return false;
	}
}

function userAuth($user,$pass, $connector){
	$sql = "SELECT * FROM tbl_user WHERE user='". $user ."' AND password=SHA1('" . $pass . "');";
	$result = $connector->query($sql);
	if ($result->num_rows > 0) {
		return true;
	}
	else{
		return false;
	}
}

function jsonInfobySN($serialnumber, $connector){
	$sql = "SELECT * FROM tbl_parking WHERE serialnumber='". $serialnumber ."';";
	$result = $connector->query($sql);
	
	
	if ($result->num_rows > 0) 
	{
		while($row = $result->fetch_assoc()) {
			//print_r($row);
			$json = array(
				"id" => (int)$row["id"],
				"serialnumber" => $row["serialnumber"],
				"statuscode" => (int)$row["statuscode"],
				"enterdate" => $row["enterdate"],
				"parkingarea" => $row["parkingarea"],
				"position" => $row["position"]
				);	
				
				echo json_encode($json);
				return true;
				
		}
		
	}
	else
	{
		return false;
	}
	
}

function jsonInfobySN2($serialnumber, $connector){
	
	$P1 = substr($serialnumber,0,1);
	$P2 = substr($serialnumber,1,1);
	$P3 = substr($serialnumber,2,1);
	$P4 = substr($serialnumber,3,2);
	$P5 = substr($serialnumber,5,2);
	$P6 = substr($serialnumber,7,2);
	$P7 = substr($serialnumber,9,1);
	$P8 = substr($serialnumber,10,1);
	
	$sql = "
	SELECT * ,
	(SELECT tbl_regional_pharse.name FROM tbl_regional_pharse WHERE tbl_regional_pharse.code = '".$P1."' LIMIT 1) AS regional,
	(SELECT tbl_indonesian_sae.name FROM tbl_indonesian_sae WHERE tbl_indonesian_sae.code = '".$P2."' LIMIT 1) AS from_sae,
	(SELECT tbl_vehicle_assembler.name FROM tbl_vehicle_assembler WHERE tbl_vehicle_assembler.code = '".$P3."' LIMIT 1) AS assembler,
	(SELECT tbl_vehicle_series.name FROM tbl_vehicle_series WHERE tbl_vehicle_series.code = '".$P4."' LIMIT 1) AS series,
	(SELECT tbl_body_tipe.name FROM tbl_body_tipe WHERE tbl_body_tipe.code = '".$P5."' LIMIT 1) AS body_tipe,
	(SELECT tbl_engine_type.name FROM tbl_engine_type WHERE tbl_engine_type.code = '".$P6."' LIMIT 1) AS engine_type,
	(SELECT tbl_production_year.name FROM tbl_production_year WHERE tbl_production_year.code = '".$P7."' LIMIT 1) AS production_year,
	(SELECT tbl_mac_location.name FROM tbl_mac_location WHERE tbl_mac_location.code = '".$P8."' LIMIT 1) AS mac_location

	FROM tbl_parking WHERE serialnumber='".$serialnumber."';
	";
	
	//echo $sql;
	$result = $connector->query($sql);
	
	
	if ($result->num_rows > 0) 
	{
		while($row = $result->fetch_assoc()) {
			
			$json = array(
				"id" => (int)$row["id"],
				"serialnumber" => $row["serialnumber"],
				"statuscode" => (int)$row["statuscode"],
				"enterdate" => $row["enterdate"],
				"parkingarea" => $row["parkingarea"],
				"position" => $row["position"],				
				"regional" => $row["regional"],
				"from_sae" => $row["from_sae"],
				"assembler" => $row["assembler"],
				"series" => $row["series"],
				"body_tipe" => $row["body_tipe"],
				"engine_type" => $row["engine_type"],
				"production_year" => $row["production_year"],
				"mac_location" => $row["mac_location"]
				);	
				
				echo json_encode($json);
				return true;
				
		}
		
	}
	else
	{
		return false;
	}
	
}


function resultToArray($result) {
    $rows = array();
    while($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    return $rows;
}

function jsonRangebyDate($startDate,$endDate, $connector){
	//select * from tbl_outHistory where exitdate>=FROM_UNIXTIME(1521540000) AND exitdate<=FROM_UNIXTIME(1521560000)
	$sql = "select * from tbl_outHistory where exitdate>='". $startDate ."' AND exitdate<='". $endDate ."';";
	
	$result = $connector->query($sql);
	if($result->num_rows > 0){		
		$data = array();
		$row = resultToArray($result);
		for($i=0;$i<$result->num_rows;$i++){
			$subjson = array(
				"id" => (int)$row[$i]["id"],
				"serialnumber" => $row[$i]["serialnumber"],
				"statuscode" => (int)$row[$i]["statuscode"],
				"enterdate" => $row[$i]["enterdate"],
				"exitdate" => $row[$i]["exitdate"],
				"parkingarea" => $row[$i]["parkingarea"],
				"position" => $row[$i]["position"]
				);	
			array_push($data,$subjson);	
		}
		$json = array(
				"Methode" => $_GET['method'],
				"StartDate" => $startDate,
				"StopDate" => $endDate,
				"Data" => $data
				);	
		echo json_encode($json);
		
		return true;
	}
	else{
		return false;
	}
}

function jsonRangebyDays($LastNumDay, $connector){
	//select * from tbl_outHistory where exitdate>=FROM_UNIXTIME(1521540000) AND exitdate<=FROM_UNIXTIME(1521560000)
	$sql = "select * from tbl_outHistory where exitdate > DATE_SUB(CURDATE(), INTERVAL ". $LastNumDay ." DAY);";
	
	$result = $connector->query($sql);
	if($result->num_rows > 0){		
		$data = array();
		$row = resultToArray($result);
		for($i=0;$i<$result->num_rows;$i++){
			$subjson = array(
				"id" => (int)$row[$i]["id"],
				"serialnumber" => $row[$i]["serialnumber"],
				"statuscode" => (int)$row[$i]["statuscode"],
				"enterdate" => $row[$i]["enterdate"],
				"exitdate" => $row[$i]["exitdate"],
				"parkingarea" => $row[$i]["parkingarea"],
				"position" => $row[$i]["position"]
				);	
			array_push($data,$subjson);	
		}
		$json = array(
				"Methode" => $_GET['method'],
				"NumLastDay" => $LastNumDay,
				"Data" => $data
				);	
		echo json_encode($json);
		
		return true;
	}
	else{
		return false;
	}
}

function jsonRangebyDateLimit($startDate,$endDate,$limit, $connector){
	//select * from tbl_outHistory where exitdate>=FROM_UNIXTIME(1521540000) AND exitdate<=FROM_UNIXTIME(1521560000)
	$sql = "select * from tbl_outHistory where exitdate>=FROM_UNIXTIME(". $startDate .") AND exitdate<=FROM_UNIXTIME(". $endDate .") LIMIT " .$limit. ";";
	
	$result = $connector->query($sql);
	if($result->num_rows > 0){		
		$data = array();
		$row = resultToArray($result);
		for($i=0;$i<$result->num_rows;$i++){
			$subjson = array(
				"id" => (int)$row[$i]["id"],
				"serialnumber" => $row[$i]["serialnumber"],
				"statuscode" => (int)$row[$i]["statuscode"],
				"enterdate" => $row[$i]["enterdate"],
				"exitdate" => $row[$i]["exitdate"],
				"parkingarea" => $row[$i]["parkingarea"],
				"position" => $row[$i]["position"]
				);	
			array_push($data,$subjson);	
		}
		$json = array(
				"Methode" => $_GET['method'],
				"StartDate" => $startDate,
				"StopDate" => $endDate,
				"Data" => $data
				);	
		echo json_encode($json);
		
		return true;
	}
	else{
		return false;
	}
}

function jsonRangebyDaysLimit($LastNumDay, $limit, $connector){
	//select * from tbl_outHistory where exitdate>=FROM_UNIXTIME(1521540000) AND exitdate<=FROM_UNIXTIME(1521560000)
	$sql = "select * from tbl_outHistory where exitdate > DATE_SUB(CURDATE(), INTERVAL ". $LastNumDay ." DAY) LIMIT " .$limit. ";";
	
	$result = $connector->query($sql);
	if($result->num_rows > 0){		
		$data = array();
		$row = resultToArray($result);
		for($i=0;$i<$result->num_rows;$i++){
			$subjson = array(
				"id" => (int)$row[$i]["id"],
				"serialnumber" => $row[$i]["serialnumber"],
				"statuscode" => (int)$row[$i]["statuscode"],
				"enterdate" => $row[$i]["enterdate"],
				"exitdate" => $row[$i]["exitdate"],
				"parkingarea" => $row[$i]["parkingarea"],
				"position" => $row[$i]["position"]
				);	
			array_push($data,$subjson);	
		}
		$json = array(
				"Methode" => $_GET['method'],
				"NumLastDay" => $LastNumDay,
				"Data" => $data
				);	
		echo json_encode($json);
		
		return true;
	}
	else{
		return false;
	}
}

function jsonQueryArg($table,$args, $connector){
	//select * from tbl_outHistory where exitdate>=FROM_UNIXTIME(1521540000) AND exitdate<=FROM_UNIXTIME(1521560000)
	$sql = "select * from ".$table." ". $args .";";
	
	$result = $connector->query($sql);
	if($result->num_rows > 0){		
		$data = array();
		$row = resultToArray($result);
		for($i=0;$i<$result->num_rows;$i++){
			$subjson = array(
				"id" => (int)$row[$i]["id"],
				"serialnumber" => $row[$i]["serialnumber"],
				"statuscode" => (int)$row[$i]["statuscode"],
				"enterdate" => $row[$i]["enterdate"],
				"exitdate" => $row[$i]["exitdate"],
				"parkingarea" => $row[$i]["parkingarea"],
				"position" => $row[$i]["position"]
				);	
			array_push($data,$subjson);	
		}
		$json = array(
				"Methode" => $_GET['method'],
				//"StartDate" => $startDate,
				//"StopDate" => $endDate,
				"Data" => $data
				);	
		echo json_encode($json);
		
		return true;
	}
	else{
		return false;
	}
}

function jsonQueryInfo($connector){
	//select * from tbl_outHistory where exitdate>=FROM_UNIXTIME(1521540000) AND exitdate<=FROM_UNIXTIME(1521560000)
	$sql = "select * from tbl_count;";
	
	$result = $connector->query($sql);
	if($result->num_rows > 0){		
		$data = array();
		$row = resultToArray($result);
		for($i=0;$i<$result->num_rows;$i++){
			$subjson = array(
				"id" => (int)$row[$i]["id"],
				"field" => $row[$i]["field"],
				"count" => (int)$row[$i]["count"],
				"ObjectDesc" => $row[$i]["ObjectDesc"],
				);	
			array_push($data,$subjson);	
		}
		$json = array(
				"Methode" => $_GET['method'],
				"Data" => $data
				);	
		echo json_encode($json);
		
		return true;
	}
	else{
		return false;
	}
}

function getSNDetail($serialnumber, $dictionary, $connector){
	
	$P1 = substr($serialnumber,0,1);
	$P2 = substr($serialnumber,1,1);
	$P3 = substr($serialnumber,2,1);
	$P4 = substr($serialnumber,3,2);
	$P5 = substr($serialnumber,5,2);
	$P6 = substr($serialnumber,7,2);
	$P7 = substr($serialnumber,9,1);
	$P8 = substr($serialnumber,10,1);

	$sql = "SELECT  (
    SELECT COUNT(*)
    FROM   user_table
    ) AS tot_user,
    (
    SELECT COUNT(*)
    FROM   cat_table
    ) AS tot_cat,
    (
    SELECT COUNT(*)
    FROM   course_table
    ) AS tot_course;";
	/* $result = $connector->query($sql);
	
	
	if ($result->num_rows > 0) 
	{
		while($row = $result->fetch_assoc()) {
			
			$json = array(
				"id" => (int)$row["id"],
				"serialnumber" => $row["serialnumber"],
				"statuscode" => (int)$row["statuscode"],
				"enterdate" => $row["enterdate"],
				"parkingarea" => $row["parkingarea"],
				"position" => $row["position"]
				);	
				
				echo json_encode($json);
				return true;
				
		}
		
	}
	else
	{
		return false;
	} */
	
}

$conn->close();


?>
