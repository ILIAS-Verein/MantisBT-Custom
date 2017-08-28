<?php

function compose_remind_msg($conn, $row, $sql_21_days, $sql_14_days, $msg_begin,
                                $msg_end, $msg_open_bugs_warning_begin, $url){

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

                    $list_of_bugs = $list_of_bugs . $url . $row_bug["id"] ."\n";

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
        return $message;
    }
}

//Returns string of space characters with given length
function getSpaces($length){

    $spaces = "";
    while($length > 0){
        $spaces = $spaces . " ";
        $length--;
    }
    return $spaces;
}

function count_bug_votings( $user_id, $conn, $start_date, $twentyonedaysago ){

    $field_name = "fixing_priority";
    $field_id = 0;
    $votings = "";
    $votings_array;

    $sql = "SELECT * FROM mantis_custom_field_table "
            . "WHERE name='" . $field_name . "'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        if($row = $result->fetch_assoc()) {
            $field_id=$row["id"];
        }
    }

    //select bugs with status not equal to 'closed' or 'resolved'
    $sql_users_bugs = "SELECT * FROM mantis_bug_table WHERE handler_id='". $user_id . "' AND NOT ( status='80' OR status='90' )";

    $result_users_bugs = $conn->query($sql_users_bugs);

    if( $result_users_bugs->num_rows > 0 ){
        while($row_bug=$result_users_bugs->fetch_assoc()){
            $sql_custom_field = "SELECT * FROM mantis_custom_field_string_table WHERE bug_id='". $row_bug["id"] . "' AND field_id='". $field_id . "'";

            $result_custom_field = $conn->query($sql_custom_field);
            if( $result_custom_field->num_rows > 0 ){
                $row_custom_field = $result_custom_field->fetch_assoc();

                if( !isset( $votings_array[$row_custom_field["value"]] ) ){
                    $votings_array[$row_custom_field["value"]] = 0;
                }
                $votings_array[$row_custom_field["value"]] = $votings_array[$row_custom_field["value"]] + 1;
            }
        }
    }
    if( isset($votings_array)){
        ksort($votings_array);
        foreach ($votings_array as $key => $value) {
            if( is_numeric($key)){
                if( intval($key) != 0 ){
                    if($votings==""){
                        $votings = ", Votes:";
                    }
                    $votings = $votings . " " . $value . "x" . intval($key) . "v";
                }
            }
        }
    }
    return $votings;
}

function compose_summary_report_msg($summary_report){

    $message = "<pre>\r\n <b>Open bugs and required feedbacks in core project:</b> \r\n ";
    foreach ($summary_report as $key => $value) {

        if($value["open_bugs"] > 0){
            $vname = $value["name"];
            if($vname == ""){
                $vname = $value["email"];
            }
            $name = "<a href='https://www.ilias.de/mantis/view_user_page.php?id=" . $key . "' target='_blank'>" . $vname . "</a>";

            $spaces = getSpaces(  50 - strlen($vname) );
            if($value["feedback_bugs"] > 0){
                $line = "\n" . $name . $spaces . $value["open_bugs"] . " open bugs(" . $value["mean_age_open"] . " Tage)" . ", " . $value["feedback_bugs"] ." feedbacks required(" . $value["mean_age_feedback"] . " Tage)" . $value["bug_votings"];
                $message = $message . "\n" . $line;
            }
            else{
                $line = "\n" . $name . $spaces . $value["open_bugs"] . " open bugs(" . $value["mean_age_open"] . " Tage)" . $value["bug_votings"];
                $message = $message . "\n" . $line;
            }
        }
        else if($value["feedback_bugs"] > 0){
            $vname = $value["name"];
            if($vname == ""){
                $vname = $value["email"];
            }
            $name = "<a href='https://www.ilias.de/mantis/view_user_page.php?id=" . $key . "' target='_blank'>" . $vname . "</a>";
            $spaces = getSpaces(  50 - strlen($vname) );

            $line = "\n" . $name . $spaces . $value["feedback_bugs"] ." feedbacks required(" . $value["mean_age_feedback"] . " Tage)" . $value["bug_votings"];
            $message = $message . "\n" . $line;
        }
        else if( isset($value["bug_votings"]) ){ #$summary_report[$row["id"]]["bug_votings"]
            #if( count($value["bug_votings"]) > 0 ){
            if( $value["bug_votings"] != "" ){

                $vname = $value["name"];
                echo "" . $vname . ": " . count($value["bug_votings"]) . "\n";
                if($vname == ""){
                    $vname = $value["email"];
                }
                $name = "<a href='https://www.ilias.de/mantis/view_user_page.php?id=" . $key . "' target='_blank'>" . $vname . "</a>";
                $spaces = getSpaces(  50 - strlen($vname) );
                $line = "\n" . $name . $spaces . str_replace(", ", "", $value["bug_votings"]);
                $message = $message . "\n" . $line;
            }
        }
    }
    $message = $message . "\r\n\r\n</pre>";
    return $message;
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

//Select reporter(25), updater(40), developers(55), manager(70), and administrators(90)
$sql = "SELECT * FROM mantis_user_table WHERE access_level='25' OR access_level='40' OR access_level='55' OR access_level='70' OR access_level='90'";

$result = $conn->query($sql);
$summary_report = null;
if ($result->num_rows > 0) {

    while($row = $result->fetch_assoc()) {

        //over 21 days open bugs
        $subject = '[MantisBT] Reminder about open bugs';
        //count bugs of current user with project nr. 1
        if(( $row["access_level"] == 55 || $row["access_level"] == 70  || $row["access_level"] == 90 )){

            $summary_report[$row["id"]]["open_bugs"] = 0;
            $summary_report[$row["id"]]["feedback_bugs"] = 0;
            $summary_report[$row["id"]]["mean_age_open"] = 0;
            $summary_report[$row["id"]]["mean_age_feedback"] = 0;
            $summary_report[$row["id"]]["bug_votings"] = "";

            $summary_report[$row["id"]]["bug_votings"] = count_bug_votings( $row["id"], $conn, $start_date, $twentyonedaysago );

            $sql_21_days_open = "SELECT COUNT(id) AS open_num FROM  mantis_bug_table WHERE handler_id='". $row["id"] . "' AND status='10' AND date_submitted>'" . $start_date . "' AND last_updated<'". $twentyonedaysago . "' AND project_id = 1";
            $result2 = $conn->query($sql_21_days_open);
            $row_count_open = $result2->fetch_assoc();

            $summary_report[$row["id"]]["email"] = $row["email"];
            $summary_report[$row["id"]]["name"] = $row["realname"];
            if( $row_count_open["open_num"] > 0 ){
                $summary_report[$row["id"]]["open_bugs"] = $row_count_open["open_num"];

                $sql_sum_mean_age = "SELECT handler_id, SUM( " . $datenow[0] . " - last_updated ) as 'total_time_passed'  FROM  mantis_bug_table WHERE handler_id='". $row["id"] . "' AND status='10' AND date_submitted>'" . $start_date . "' AND last_updated<'". $twentyonedaysago . "' AND project_id = 1 GROUP BY handler_id";
                $result_mean_age = $conn->query($sql_sum_mean_age);
                $row_mean_age = $result_mean_age->fetch_assoc();
                $mean_age_open = $row_mean_age["total_time_passed"] / $row_count_open["open_num"];
                $mean_age_in_days = round( $mean_age_open / (24*60*60) );
                $summary_report[$row["id"]]["mean_age_open"] = $mean_age_in_days;
            }

            $sql_21_days_feedback = "SELECT COUNT(id) AS feedback_num FROM mantis_bug_table WHERE reporter_id='". $row["id"] . "' AND status='20' AND date_submitted>'" . $start_date . "' AND last_updated<'". $twentyonedaysago . "' AND project_id = 1";
            $result3 = $conn->query($sql_21_days_feedback);
            $row_count_feedback = $result3->fetch_assoc();
            if( $row_count_feedback["feedback_num"] > 0 ){
                $summary_report[$row["id"]]["feedback_bugs"] = $row_count_feedback["feedback_num"];

                $sql_sum_mean_age = "SELECT reporter_id, SUM( " . $datenow[0] . " - last_updated ) as 'total_time_passed'  FROM  mantis_bug_table WHERE reporter_id='". $row["id"] . "' AND status='20' AND date_submitted>'" . $start_date . "' AND last_updated<'". $twentyonedaysago . "' AND project_id = 1 GROUP BY reporter_id";
                $result_mean_age = $conn->query($sql_sum_mean_age);
                $row_mean_age = $result_mean_age->fetch_assoc();
                $mean_age_feedback = $row_mean_age["total_time_passed"] / $row_count_feedback["feedback_num"];
                $mean_age_in_days = round( $mean_age_feedback / (24*60*60) );
                $summary_report[$row["id"]]["mean_age_feedback"] = $mean_age_in_days;

            }
        }

        $addressee = $row["email"];
        $header = 'From: Mantis Bug Tracker <noreply@ilias.de>' . "\r\n" .
            'Reply-To: info@ilias.de' . "\r\n" .
            'X-Mailer: PHP/' . phpversion();

        $subject = '[MantisBT] Reminder about open bugs';
        $sql_21_days = "SELECT * FROM mantis_bug_table WHERE handler_id='". $row["id"] . "' AND status='10' AND date_submitted>'" . $start_date . "' AND last_updated<'". $twentyonedaysago . "'";
        $sql_14_days = "SELECT * FROM mantis_bug_table WHERE handler_id='". $row["id"] . "' AND status='10' AND date_submitted>'" . $start_date . "' AND last_updated<'". $fourteendaysago . "' AND last_updated>'". $twentyonedaysago . "'";
        $message_open = compose_remind_msg($conn, $row, $sql_21_days, $sql_14_days, $msg_open_bugs_begin, $msg_open_bugs_end, $msg_open_bugs_warning_begin, $url);
        if( $message_open != ""){
            mail($addressee, $subject, $message_open, $header);
        }

        $subject = '[MantisBT] Reminder about required feedbacks';
        //over 21 days needed feedbacks
        $sql_21_days = "SELECT * FROM mantis_bug_table WHERE reporter_id='". $row["id"] . "' AND status='20' AND date_submitted>'" . $start_date . "' AND last_updated<'". $twentyonedaysago . "'";
        //over 14 but less than 21 days needed feedbacks
        $sql_14_days = "SELECT * FROM mantis_bug_table WHERE reporter_id='". $row["id"] . "' AND status='20' AND date_submitted>'" . $start_date . "' AND last_updated<'". $fourteendaysago . "' AND last_updated>'". $twentyonedaysago . "'";

        $message_feedback = compose_remind_msg($conn, $row, $sql_21_days, $sql_14_days, $msg_feedback_begin, $msg_feedback_end, $msg_feedback_warning_begin, $url);

        if( $message_feedback != ""){
            mail($addressee, $subject, $message_feedback, $header);
        }
    }

} else {
    echo "0 results";
}

$conn->close();

//send summary report
$addressee = "mkunkel@me.com";
$subject = '[MantisBT] Summary report about open bugs and required feedbacks in core project';

$tmp = compose_summary_report_msg($summary_report);

$header = 'From: Mantis Bug Tracker <noreply@ilias.de>' . "\r\n" .
    "Reply-To: info@ilias.de" . "\r\n" .
    "X-Mailer: PHP/" . phpversion() . "\r\n" .
    "MIME-Version: 1.0\r\n" .
    "Content-type: text/html; charset=utf-8" . "\r\n" .
    "Content-Transfer-Encoding: quoted-printable";

$message = quoted_printable_encode(utf8_encode($tmp));
mail($addressee, $subject, $message, $header);

?> 
