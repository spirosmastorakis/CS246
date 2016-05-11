<?php 

// $string = "[{\"trend_attr\":\"dept\",\"trend_name\":\"Computer Science\"}, {\"trend_attr\":\"dept\",\"trend_name\":\"Electrical Engineering\"}]";
// error_log($string);
// $data_trend = json_decode($string);

// foreach ($data_trend as $element) {
// 	error_log($element->trend_attr);
// 	error_log($element->trend_name);
// }
// exit;

$labelmapping = array(
	'id' 				=> 'ID',
	'cid'				=> 'Course ID',
	'quarter'			=> 'Quarter',
	'year'				=> 'Year',
	'dept'				=> 'Department',
	'cnum'				=> 'Course Number',
	'title'				=> 'Course Title',
	'instructor'		=> 'Instructor',
	'type'				=> 'Course Type',
	'sec'				=> 'Section Number',
	'days'				=> 'Day of Class',
	'start'				=> 'Start Time',
	'stop'				=> 'Finish Time',
	'building'			=> 'Building',
	'room'				=> 'Room',
	'res' 				=> 'Restriction',
	'enrollment' 		=> 'Enrollment Count',
	'enrollmentcap'		=> 'Enrollment Capacity',
	'waitlist' 			=> 'Waitlist Count',
	'waitlistcap'		=> 'Waitlist Capacity',
	'status'			=> 'Status');

$typemapping = array(
	'253' 				=> 'string',
	'246'				=> 'number',
	'3'					=> 'number', 
	'11'				=> 'time');

function databaseConnect(){
	$desired_db = "TEST";

	// Connect to mysql and check for errors
	$db_connection = mysqli_connect("localhost", "lui", "", $desired_db);
	if (!$db_connection) {
	    echo "Error: Unable to connect to MySQL." . PHP_EOL;
	    echo "Debugging errno: " . mysqli_connect_errno() . PHP_EOL;
	    echo "Debugging error: " . mysqli_connect_error() . PHP_EOL;
	    exit;
	}
	// echo "Database connection established";
	return $db_connection;
}
function databaseClose($db_connection){
	mysqli_close($db_connection);
}

function typemapping($domain){
	if (strpos($domain, 'VARCHAR') !== FALSE){
		return 'string';
	} else if ($domain == 'time'){
		return 'time';
	} else if ($domain == 'INT'){
		return 'number';
	} else {
		echo "Error: typemapping domain type not mapped";
		exit;
	}
}

function printCols(){
	global $result;
	global $result_xaxis;
	global $result_arr;
	global $labelmapping;
	global $typemapping;
	global $data_trend;
	

	if($_GET['data_group'] == 'ALL'){
		// echo "\"cols\": [";
		echo "\"cols\": ";
		// print xaxis column
		$id = $_GET['xaxis_attr'];
		$label = $labelmapping[$id];
		$type = $typemapping[mysqli_fetch_field_direct($result, 0)->type];

		$listo = array();

		$arr = array('id'=>$id,'label'=>$label,'pattern'=>'','type'=>$type);
		$listo[] = $arr;
		// echo json_encode($arr).",";

		// echo "{\"id\":\"\",\"label\":\"Day\",\"pattern\":\"\",\"type\":\"number\"},";

		// print yaxis column
		// $data = json_decode(stripslashes($_GET['data']));

		$type = $typemapping[mysqli_fetch_field_direct($result, 1)->type];
		$arr = array('id'=>'all','label'=>'All','pattern'=>'','type'=>$type);
		$listo[] = $arr;
		// echo json_encode($arr);
		// echo "],";
		echo json_encode($listo);
		error_log(json_encode($listo));
		// echo ",";
	}
	else{
		// handle when there are multiple data groupings
		echo "\"cols\":";

		// print xaxis column
		$id = $_GET['xaxis_attr'];
		$label = $labelmapping[$id];
		$type = $typemapping[mysqli_fetch_field_direct($result_xaxis, 0)->type];

		$listo = array();
		$arr = array('id'=>$id,'label'=>$label,'pattern'=>'','type'=>$type);
		$listo[] = $arr;
		// echo json_encode($arr).",";
		// error_log(json_encode($arr));

		for ($i = 0; $i < count($result_arr); $i++) {
	        $element = $result_arr[$i];

			$id = $data_trend[$i]->trend_attr . "_" . $i;
			$label = $data_trend[$i]->trend_name;
			$type = $typemapping[mysqli_fetch_field_direct($element, 1)->type];
			$arr = array('id'=>$id,'label'=>$label,'pattern'=>'','type'=>$type);
			$listo[] = $arr;
			// echo json_encode($arr);
			// error_log(json_encode($arr));
	    }
	    echo json_encode($listo);
		error_log(json_encode($listo));
	    // echo ",";

	}

    
}

function printRows(){
	global $result;
	global $result_arr;
	global $result_xaxis;
	echo  "\"rows\": ";

	$listo = array();

	if($_GET['data_group'] == 'ALL'){
		while($row = mysqli_fetch_row($result)){
			$list = array();
			$count = mysqli_num_fields($result);
		    for($i = 0; $i<$count; $i++){
				$list[] = array('v'=>$row[$i], 'f'=>null);
			}
			$listo[] = array('c'=>$list);
		}
		error_log(json_encode($listo));
		echo json_encode($listo);
	} else {
		// ensure that if different results have different number of rows, correct values will be matched
		$last = array_fill(0,count($result_arr), 0);
		while($x = mysqli_fetch_row($result_xaxis)){
			
			$list = array();
			$list[] = array('v'=>$x[0], 'f'=>null);	// add xaxis information

			for ($i=0; $i < count($result_arr); $i++) { 

				// if there was not an unused previously fetched value
				if ($last[$i] == 0) {
					$row = mysqli_fetch_row($result_arr[$i]);

					if ($x[0] == $row[0]){			// if the xaxis match
						$list[] = array('v'=>$row[1], 'f'=>null);
					} else {						// if the xaxis dont match
						$last[$i] = $row[1];
						$list[] = array('v'=>0, 'f'=>null);
					}

				} else {		// if there was an unused previously fetched value
					
					if ($x[0] == $last[$i]){			// if the xaxis match
						$list[] = array('v'=>$last[$i], 'f'=>null);
						$last[$i] = 0;
					} else {						// if the xaxis dont match
						$list[] = array('v'=>0, 'f'=>null);
					}
				}
			}
			$listo[] = array('c'=>$list);
		}
		error_log(json_encode($listo));
		echo json_encode($listo);
	}

}

// function printRows(){
// 	global $result;

// 	echo  "\"rows\": [";
//     $row = mysqli_fetch_row($result);
//     printRow($row);
// 	while($row = mysqli_fetch_row($result)){
// 		echo ",";
// 		printRow($row);
// 	}
// 	echo "]";
// }

// function printRow($row){
// 	global $result;

// 	echo "{\"c\":[";
//     $count = mysqli_num_fields($result);
//     for($i = 0; $i<$count; $i++){
// 		echo "{\"v\":\"".$row[$i]."\",\"f\":null}";
// 		if($i != $count-1){
// 			echo ",";
// 		}
// 	}
// 	// echo "{\"v\":\"".$row[0]."\",\"f\":null},";
// 	// echo "{\"v\":\"".$row[1]."\",\"f\":null},";
// 	// echo "{\"v\":\"".$row[2]."\",\"f\":null},";
// 	// echo "{\"v\":\"".$row[3]."\",\"f\":null}";
// 	echo "]}";
// }


// We will receive the following from GET
// *For each yaxis
// *yaxis_num = 1
// yaxis_attr = e.g. value(enrollment), function (e.g. count, avg, max) of a value (enrollment cap)
// yaxis_aggr = e.g. sum
// data_group = e.g. ALL, class = 32, 33, use CUBE BY
// xaxis_attr = e.g. 'year' attribute groupped by (e.g. time). 
//		attribute name as it appears in DB

// data_sel = all or attribute name
// data_val = for each trend
// 		condition e.g. cnum = 'cs31'
//		an array encoded in JSON


// Construct SQL and query for results
// $string = file_get_contents("sampleData.json");
$db_connection = databaseConnect();

// $query_str = "SELECT day, guardian, avenger, transformer FROM movie";
// SELECT year, sum(enrollment) FROM Class GROUP BY year
// yaxis_attr = enrollment
// yaxis_aggr = sum
// xaxis_attr = year
// data_group = ALL
/* 
option 1
	SELECT year, sum(enrollment) FROM
		(SELECT year FROM Class GROUP BY year) AS X 
		LEFT OUTTER JOIN
		(SELECT year, sum(enrollment) FROM Class WHERE dept = 'Computer Science' GROUP BY year);

	SELECT year, sum(enrollment) FROM
		(SELECT year FROM Class GROUP BY year) AS X 
		LEFT OUTTER JOIN
		(SELECT year, sum(enrollment) FROM Class WHERE dept = 'Electrical Engineering' GROUP BY year);

option 2
	SELECT year FROM Class GROUP BY year
	SELECT year, sum(enrollment) FROM Class WHERE dept = 'Computer Science' GROUP BY year
	SELECT year, sum(enrollment) FROM Class WHERE dept = 'Electrical Engineering' GROUP BY year

place into a result set, evaluate each one

*/

$yaxis_attr = $_GET['yaxis_attr'];
$yaxis_aggr = $_GET['yaxis_aggr'];
$xaxis_attr = $_GET['xaxis_attr'];
$data_group = $_GET['data_group'];

// temp
$data_trend1 = "dept = 'Computer Science'";
$data_trend2 = "dept = 'Electrical Engineering'";

// $data_trend = array("dept = 'Computer Science'", "dept = 'Electrical Engineering'");
// $data_trend = array({"trend_attr":"dept","trend_name":"Computer Science"}, {"trend_attr":"dept","trend_name":"Computer Science"}, {"trend_attr":"dept","trend_name":"Mechanical Engineering"});
$string = "[{\"trend_attr\":\"dept\",\"trend_name\":\"Computer Science\"}, {\"trend_attr\":\"dept\",\"trend_name\":\"Electrical Engineering\"}, {\"trend_attr\":\"dept\",\"trend_name\":\"Environment\"}]";
$data_trend = json_decode($string);


$result;
$result_arr;
$result_xaxis;
if($data_group == 'ALL'){
	error_log("Single Trend");

	$query_str = "SELECT $xaxis_attr, $yaxis_aggr($yaxis_attr)
				  FROM Class
				  GROUP BY $xaxis_attr";

	error_log($query_str);
	$result = mysqli_query($db_connection, $query_str);

	if (mysqli_num_rows($result) > 0){

		// echo dataTable column
		// echo $_GET['id'];

		// Print Columns
		echo "{";
		printCols();
		echo ",";
		// Print Rows
	    printRows();
	    echo "}";

		mysqli_free_result($result);
	}else{
		echo "Error: No results found";
	}

} else {
	error_log("Multi Trend");
	
	$query_str = "SELECT $xaxis_attr
				  FROM Class
				  GROUP BY $xaxis_attr";

	$query_arr = array();
	foreach ($data_trend as $element) {
		$query_arr[] = "SELECT $xaxis_attr, $yaxis_aggr($yaxis_attr)
					  FROM Class
					  WHERE $element->trend_attr = '$element->trend_name'
					  GROUP BY $xaxis_attr";
	}

	// $query_arr[] = "SELECT $xaxis_attr, $yaxis_aggr($yaxis_attr)
	// 			  FROM Class
	// 			  WHERE $data_trend2
	// 			  GROUP BY $xaxis_attr";

	// error_log(implode(';', $query_arr));

	$result_xaxis = mysqli_query($db_connection, $query_str);

	$result_arr = array();
	foreach ($query_arr as $element){
		error_log($element);
		$result_arr[] = mysqli_query($db_connection, $element);
	}

	// foreach ($result_arr as $element){
	// 	error_log($element->num_rows);
	// }

	if (mysqli_num_rows($result_xaxis) > 0){
		echo "{";
		printCols();
		echo ",";
	    printRows();
	    echo "}";

	}else{
		echo "Error: No results found";
	}

	mysqli_free_result($result_xaxis);
	foreach ($result_arr as $element){
		mysqli_free_result($element);
	}

}


databaseClose($db_connection);

// dataTable JSON format 
/*
{
  "cols": [
        {"id":"","label":"Topping","pattern":"","type":"string"},
        {"id":"","label":"Slices","pattern":"","type":"number"}
      ],
  "rows": [
        {"c":[{"v":"Mushrooms","f":null},{"v":3,"f":null}]},
        {"c":[{"v":"Onions","f":null},{"v":1,"f":null}]},
        {"c":[{"v":"Olives","f":null},{"v":1,"f":null}]},
        {"c":[{"v":"Zucchini","f":null},{"v":1,"f":null}]},
        {"c":[{"v":"Pepperoni","f":null},{"v":2,"f":null}]}
      ]
  
}
*/




?>