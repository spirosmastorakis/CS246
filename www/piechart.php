<?php


function colsinfo(){
    echo "{\"cols\": [";
    echo "{\"id\":\"\",\"label\":\"Year\",\"pattern\":\"\",\"type\":\"string\"},";
    echo "{\"id\":\"\",\"label\":\"enrollment\",\"pattern\":\"\",\"type\":\"number\"},";
    echo "],";
}

function rowsinfo($result){
    echo  "\"rows\":[";
    $i=0;
    while($row = $result->fetch_assoc()) {
        echo "{\"c\":[";
        echo "{\"v\":\"".$row["year"]."\",\"f\":null},";
        echo "{\"v\":".$row["sum(enrollment)"].",\"f\":null}";
        echo "]}";
        $i = $i + 1;
        if($i!=$result->num_rows)
            echo ",";
    }
    echo "]}";
}
//please change database name, account and password.
$conn = mysqli_connect("localhost", "root", "123456", "CS246");
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 

$query = "SELECT year, sum(enrollment) FROM Class GROUP BY year";
$result =  $conn->query($query);
if ($result->num_rows > 0) {
    // output data of each row
    colsinfo();
    rowsinfo($result);
} else {
    echo "0 results";
}

$conn->close();
?>
