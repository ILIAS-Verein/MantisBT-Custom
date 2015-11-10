<?php

include("/etc/mantis/config_db.php");
include("message_values.php");


$datenow = getdate();
$twentyonedaysago = $datenow[0] - $twentyonedays;

// Create connection
$conn = new mysqli($dbserver, $dbuser, $dbpass, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

//Select reporter(25),updater(40), developers(55), manager(70), and administrators(90)
$sql = "SELECT * FROM mantis_user_table WHERE access_level='25' OR access_level='40' OR access_level='55' OR access_level='70' OR access_level='90'";

$result = $conn->query($sql);
$result_bugs;
if ($result->num_rows > 0) {

    while($row = $result->fetch_assoc()) {


        $sql = "SELECT * FROM mantis_bug_table WHERE reporter_id='". $row["id"] . "' AND status='20' AND date_submitted>'" . $start_date . "' AND last_updated<'". $twentyonedaysago . "'";

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
                $subject = '[MantisBT] Reminder about required feedbacks';
                $message = "Hello " . $row["realname"] . "," . $msg_feedback_begin . $list_of_bugs . $msg_feedback_end;
                $header = 'From: Mantis Bug Tracker <noreply@ilias.de>' . "\r\n" .
                            'Reply-To: info@ilias.de' . "\r\n" .
                            'X-Mailer: PHP/' . phpversion();

                mail($addressee, $subject, $message, $header);

		
		$file = '/var/log/mantis_reminds.log';
		$dat = date('Y/m/d H:i:s', $datenow[0]);
		$line =  $dat . " Mail to " . $row["realname"] . ", " . $row["email"] . ", feedback needed:\n" . $list_of_bugs;
		file_put_contents($file, $line, FILE_APPEND | LOCK_EX);


        }
}
} else {
    echo "0 results";
}


$conn->close();
?>

