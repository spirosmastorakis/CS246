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
	'res' 				=> 'Restriction',
	'enrollment' 		=> 'Enrollment Count',
	'enrollmentcap'		=> 'Enrollment Capacity',
	'waitlist' 			=> 'Waitlist Count',
	'waitlistcap'		=> 'Waitlist Capacity'
	'status'			=> 'Status');

$typemapping = array(
	'VARCHAR' 			=> 'string',
	'INT'				=> 'number', 
	'time'				=> 'time'
	);

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

function printCols($result){
	global $labelmapping;
	global $typemapping;
	echo "{\"cols\": [";
	
	// print xaxis column
	$id = $_GET['xaxis'];
	$label = labelmapping[$id];
	$type = typemapping(mysqli_fetch_field_direct($result, 0));

	$arr = array('id'=>$id,'label'=>$label,'pattern'=>'','type'=>$type);
	echo json_encode($arr).",";
	// echo "{\"id\":\"\",\"label\":\"Day\",\"pattern\":\"\",\"type\":\"number\"},";

	// print yaxis column
	// $data = json_decode(stripslashes($_GET['data']));
	if($_GET['data_group'] == 'ALL'){
		$type = typemapping(mysqli_fetch_field_direct($result, 1));
		$arr = array('id'=>'all','label'=>'All','pattern'=>'','type'=>$type);
		echo json_encode($arr);
	}
	else{
		// handle when there are multiple data groupings
	}

    // echo "{\"id\":\"guardian\",\"label\":\"Guardian of the Galaxy\",\"pattern\":\"\",\"type\":\"number\"},";
    // echo "{\"id\":\"avenger\",\"label\":\"The Avenger\",\"pattern\":\"\",\"type\":\"number\"},";
    // echo "{\"id\":\"transformer\",\"label\":\"Transformers: The Age of Extinction\",\"pattern\":\"\",\"type\":\"number\"}";

    echo "],";
}

function printRows($result){
	echo "{\"c\":[";
    $row = mysqli_fetch_row($result);
    $count = mysql_num_fields($result);
    for($i = 0; $i<$count; $i++){
		echo "{\"v\":\"".$row[$i]."\",\"f\":null}";
		if($i != $count-1){
			echo ",";
		}
	}
	// echo "{\"v\":\"".$row[0]."\",\"f\":null},";
	// echo "{\"v\":\"".$row[1]."\",\"f\":null},";
	// echo "{\"v\":\"".$row[2]."\",\"f\":null},";
	// echo "{\"v\":\"".$row[3]."\",\"f\":null}";
	echo "]}";
}

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

$query_str = "SELECT $xaxis_attr, $yaxis_aggr\($yaxis_attr\)
			  FROM Class
			  GROUP BY $xaxis_attr";

$result = mysqli_query($db_connection, $query_str);

if (mysqli_num_rows($result) > 0){

	// echo dataTable column
	// echo $_GET['id'];

	// Print Columns
	printCols($result);

	// Print Rows
    echo  "\"rows\": [";
    printRows($result);
	while($row = mysqli_fetch_row($result)){
		echo ",";
		printRows($result);
	}
	echo "]}";
	mysqli_free_result($result);
}else{
	echo "Error: No results found";
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