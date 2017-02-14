<?php

function getSpaces($length){

    $spaces = "";
    while($length > 0){
        $spaces = $spaces . " ";
        $length--;
    }
    return $spaces;
}


function sendMessage($conn, $row, $sql_21_days, $sql_14_days, $msg_begin, $msg_end, $msg_open_bugs_warning_begin, $url, $summary_report, $subject, $feedback_or_open_bugs){

    $datenow = getdate();
    $comb_of_bug_results = 0;
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $result_21_days=$conn->query($sql_21_days);
    $result_14_days=$conn->query($sql_14_days);

    if ( $result_21_days->num_rows > 0 && $result_14_days->num_rows > 0 ){
        $comb_of_bug_results = 3;
    }
    else if( $result_14_days->num_rows > 0 ){
        $comb_of_bug_results = 2;
    }
    else if( $result_21_days->num_rows > 0 ){
        $comb_of_bug_results = 1;
    }
        
    if ( $comb_of_bug_results > 0 ) {
        $list_of_bugs = "";
        $number_of_bugs = 0;
        
        switch ($comb_of_bug_results){
            //no bugs at all;
            case 0: return "";
            //bugs over 21 days only
            case 1: 
                
                while($row_bug=$result_21_days->fetch_assoc()){

                    $list_of_bugs = $list_of_bugs . $url . $row_bug["id"] . "\n";
                    //count core project bugs only
                    if( $row_bug["project_id"]==1) {
                        $number_of_bugs++;
                    }
                }
                $message = "Hello " . $row["realname"] . "," . $msg_begin . $list_of_bugs . $msg_end;

                break;
            //bugs over 14 days only
            case 2: 
                $list_of_bugs_14 = "";
                while($row_bug=$result_14_days->fetch_assoc()){
                    $list_of_bugs_14 = $list_of_bugs_14 . $url . $row_bug["id"] . "\n";
                }

                $message = "Hello " . $row["realname"] . "," . $msg_open_bugs_warning_begin . $list_of_bugs_14  . $msg_end;
                break;
            //bugs over 21 days and over 14 days old
            case 3: 
                $list_of_bugs_14 = "";
                while($row_bug=$result_21_days->fetch_assoc()){

                    $list_of_bugs = $list_of_bugs . $url . $row_bug["id"] . "\n";
                    //count core project bugs only
                    if( $row_bug["project_id"]==1) {
                        $number_of_bugs++;
                    }
                }
                while($row_bug=$result_14_days->fetch_assoc()){
                    $list_of_bugs_14 = $list_of_bugs_14 . $url . $row_bug["id"] . "\n";
                }
                $message = "Hello " . $row["realname"] . "," . $msg_begin . $list_of_bugs . $msg_open_bugs_warning_begin . $list_of_bugs_14  . $msg_end;
                break;
        }

        $addressee = $row["email"];

        $header = 'From: Mantis Bug Tracker <noreply@ilias.de>' . "\r\n" .
            'Reply-To: info@ilias.de' . "\r\n" .
            'X-Mailer: PHP/' . phpversion();

        mail($addressee, $subject, $message, $header);

        $file = '/var/log/mantis_reminds.log';
        $dat = date('Y/m/d H:i:s', $datenow[0]);

        $name = $row["realname"] . "(" . $row["email"] . "): ";
        $spaces = getSpaces(  80 - strlen($name) );
        if( $number_of_bugs > 0 ) {
            //write to log file
            if($feedback_or_open_bugs == 1 ){
            $line =  $dat . " Mail to " . $row["realname"] . ", " . $row["email"] . ", feedbacks required:\n" . $list_of_bugs;

            }
            else{
                $line =  $dat . " Mail to " . $row["realname"] . ", " . $row["email"] . ", open bugs:\n" . $list_of_bugs;
            }
            //file_put_contents($file, $line, FILE_APPEND | LOCK_EX);
            //sum information for report
            if($feedback_or_open_bugs == 1 ){
                if ( array_key_exists($row["id"], $summary_report) )
            {
                $summary_report[$row["id"]] = $summary_report[$row["id"]] . ", " . $number_of_bugs ." feedbacks required";
            }
            else{
                $name = $row["realname"] . "(" . $row["email"] . "): ";
                $spaces = getSpaces(  80 - strlen($name) );
                if( $number_of_bugs > 0 ) {
                    $summary_report[$row["id"]] = "\n" . $name . $spaces . $number_of_bugs . " feedbacks required";
                }
            }
            }
            else{
                $summary_report[$row["id"]] = "\n" . $name . $spaces . $number_of_bugs . " open bugs";
            }            
        }            
    }

    return $summary_report;

}

include("/etc/mantis/config_db.php");
include("message_values.php");

$datenow = getdate();
    $twentyonedaysago = $datenow[0] - $twentyonedays;
    $fourteendaysago = $datenow[0] - $fourteendays;

// Create connection
$conn = new mysqli($dbserver, $dbuser, $dbpass, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

/*
 * Open bug message
 */

//Select developers(55), manager(70), and administrators(90)
$sql = "SELECT * FROM mantis_user_table WHERE access_level='55' OR access_level='70' OR access_level='90'";


$result = $conn->query($sql);
$summary_report = null;
if ($result->num_rows > 0) {


    while($row = $result->fetch_assoc()) {
        
        //over 21 days open bugs
        $subject = '[MantisBT] Reminder about open bugs';
        $sql_21_days = "SELECT * FROM mantis_bug_table WHERE handler_id='". $row["id"] . "' AND status='10' AND date_submitted>'" . $start_date . "' AND last_updated<'". $twentyonedaysago . "'";
        $sql_14_days = "SELECT * FROM mantis_bug_table WHERE handler_id='". $row["id"] . "' AND status='10' AND date_submitted>'" . $start_date . "' AND last_updated<'". $fourteendaysago . "' AND last_updated>'". $twentyonedaysago . "'";
                
        $summary_report = sendMessage($conn, $row, $sql_21_days, $sql_14_days,$msg_open_bugs_begin, $msg_open_bugs_end, $msg_open_bugs_warning_begin, $url, $summary_report, $subject,0);
        
    }
} else {
    echo "0 results";
}

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

/*
 * Feedback message
 */

//Select reporter(25),updater(40), developers(55), manager(70), and administrators(90)
$sql = "SELECT * FROM mantis_user_table WHERE access_level='25' OR access_level='40' OR access_level='55' OR access_level='70' OR access_level='90'";

$result = $conn->query($sql);
$result_bugs;
if ($result->num_rows > 0) {

    while($row = $result->fetch_assoc()) {

        $subject = '[MantisBT] Reminder about required feedbacks';
        //over 21 days needed feedbacks
        $sql_21_days = "SELECT * FROM mantis_bug_table WHERE reporter_id='". $row["id"] . "' AND status='20' AND date_submitted>'" . $start_date . "' AND last_updated<'". $twentyonedaysago . "'";
        //over 14 but less than 21 days needed feedbacks
        $sql_14_days = "SELECT * FROM mantis_bug_table WHERE reporter_id='". $row["id"] . "' AND status='20' AND date_submitted>'" . $start_date . "' AND last_updated<'". $fourteendaysago . "' AND last_updated>'". $twentyonedaysago . "'";
        $summary_report = sendMessage($conn, $row, $sql_21_days, $sql_14_days, $msg_feedback_begin, $msg_feedback_end, $msg_feedback_warning_begin, $url, $summary_report, $subject, 1);
        
    }
} else {
    echo "0 results";
}

$conn->close();


//send summary report
$addressee = "mkunkel@me.com";

$subject = '[MantisBT] Summary report about open bugs and required feedbacks in core project';
$message = "<pre>\r\n <b>Open bugs and required feedbacks in core project:</b> \r\n ";
foreach ($summary_report as $value) {
    $message = $message . "\n" . $value;
}
$message = $message . "\r\n\r\n</pre>";

$header = 'From: Mantis Bug Tracker <noreply@ilias.de>' . "\r\n" .
    "Reply-To: info@ilias.de" . "\r\n" .
    "X-Mailer: PHP/" . phpversion() . "\r\n" .
    "MIME-Version: 1.0\r\n" .
    "Content-type: text/html; charset=utf-8" . "\r\n" .
    "Content-Transfer-Encoding: quoted-printable";

mail($addressee, $subject, utf8_encode($message), $header);

?>
