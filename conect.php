<?php
echo date_default_timezone_get();
$conn = mysqli_connect("localhost", "THRESH", "W1TH:2019!!!");
mysqli_select_db($conn,"with_bro_tool");
$result = mysqli_query($conn,"show tables");
while ($row = mysqli_fetch_object($result)) {
    echo '<pre>'.print_r($row);
}
//mysql_free_result($result);
?>

