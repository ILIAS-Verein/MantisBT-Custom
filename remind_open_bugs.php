<?php

include("config_db.php");
include("message_values.php");

$datenow = getdate();
$twentyonedaysago = $datenow[0] - $twentyonedays;

// Create connection
$conn = new mysqli($dbserver, $dbuser, $dbpass, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

//Select developers(55), manager(70), and administrators(90)
$sql = "SELECT * FROM mantis_user_table WHERE access_level='55' OR access_level='70' OR access_level='90'";


$result = $conn->query($sql);
$result_bugs;
if ($result->num_rows > 0) {

    while($row = $result->fetch_assoc()) {


        $sql = "SELECT * FROM mantis_bug_table WHERE handler_id='". $row["id"] . "' AND status='10' AND last_updated>'" . $start_date . "' AND last_updated<'". $twentyonedaysago . "' AND status !='80' AND status != '90'";

       if ($conn->connect_error) {
            die("Connection failed: " . $conn2->connect_error);
        }
        $r=$conn->query($sql);
        if ($r->num_rows > 0 ) {
		$list_of_bugs = "";
                              
 
		while($row_bug=$r->fetch_assoc()){
              
		$list_of_bugs= $list_of_bugs . $url . $row_bug["id"] . "\n";
		}
		$addressee = $row["email"];
                $subject = '[MantisBT] Reminder about open bugs';
                $message = "Hello " . $row["realname"] . "," . $msg_open_bugs_begin . $list_of_bugs . $msg_open_bugs_end;
                $header = 'From: Mantis Bug Tracker <noreply@ilias.de>' . "\r\n" .
                            'Reply-To: info@ilias.de' . "\r\n" .
                            'X-Mailer: PHP/' . phpversion();

                mail($addressee, $subject, $message, $header);
        }
}
} else {
    echo "0 results";
}


$conn->close();
?>

