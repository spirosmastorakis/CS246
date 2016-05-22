<?php
$column = $_GET['column'];
//please change database name, account and password.
$conn = mysqli_connect("localhost", "root", "root", "TEST");
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 

$query = "select distinct $column from Class order by $column;";
$result =  $conn->query($query);
if ($result->num_rows > 0) {
    echo "[";
    $i = 0;
    while($row = $result->fetch_assoc()) {
        echo "\"";
        echo $row[$column];
        echo "\"";
        $i = $i + 1;
        if($i!=$result->num_rows)
            echo ",";
    }
    echo "]";
} else {
    echo "0 results";
}

$conn->close();
?>
