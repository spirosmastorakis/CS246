<?php 

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
	
	echo "\"cols\":";

	// print xaxis column
	$id = $_GET['xaxis_attr'];
	$label = $labelmapping[$id];
	$type = $typemapping[mysqli_fetch_field_direct($result_xaxis, 0)->type];

	$listo = array();
	$arr = array('id'=>$id,'label'=>$label,'pattern'=>'','type'=>'string');
	$listo[] = $arr;

	for ($i = 0; $i < count($result_arr); $i++) {
        $element = $result_arr[$i];

		$id = $data_trend[$i]->trend_attr . "_" . $i;
		$label = $data_trend[$i]->trend_name;
		// $type = $typemapping[mysqli_fetch_field_direct($element, 1)->type];
		$arr = array('id'=>$id,'label'=>$label,'pattern'=>'','type'=>'number');
		$listo[] = $arr;
    }
    echo json_encode($listo);
	error_log(json_encode($listo));
}

function printRows(){
	global $result;
	global $result_arr;
	global $result_xaxis;
	echo  "\"rows\": ";

	$listo = array();

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

// $string = "[{\"trend_attr\":\"dept\",\"trend_name\":\"Computer Science\"}, {\"trend_attr\":\"dept\",\"trend_name\":\"Electrical Engineering\"}, {\"trend_attr\":\"dept\",\"trend_name\":\"Environment\"}]";
$data_trend = json_decode($data_group);


$result;
$result_arr;
$result_xaxis;

$query_str = "SELECT $xaxis_attr
			  FROM Class
			  GROUP BY $xaxis_attr";

$query_arr = array();
foreach ($data_trend as $element) {

	if($element->trend_attr == 'all'){
		$query_arr[] = "SELECT $xaxis_attr, $yaxis_aggr($yaxis_attr)
					  FROM Class
					  GROUP BY $xaxis_attr";
	} else {
		$query_arr[] = "SELECT $xaxis_attr, $yaxis_aggr($yaxis_attr)
					  FROM Class
					  WHERE $element->trend_attr = '$element->trend_name'
					  GROUP BY $xaxis_attr";
	}

}

// Query the xaxis values and store the result variable
$result_xaxis = mysqli_query($db_connection, $query_str);

// Query the data values and store the result variable
$result_arr = array();
foreach ($query_arr as $element){
	error_log($element);
	$result_arr[] = mysqli_query($db_connection, $element);
}

// Print JSON object
if (mysqli_num_rows($result_xaxis) > 0){
	echo "{";
	printCols();
	echo ",";
    printRows();
    echo "}";

}else{
	echo "Error: No results found";
}

// Free result variables 
mysqli_free_result($result_xaxis);
foreach ($result_arr as $element){
	mysqli_free_result($element);
}

databaseClose($db_connection);

?>