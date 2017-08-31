<?php

$start_date = strtotime("15 August 2015");
$twentyonedays = 21 * 24 * 60 * 60;
$fourteendays = 14 * 24 * 60 * 60;
$url = "https://www.ilias.de/mantis/view.php?id=";

$msg_open_bugs_begin = "\n\nThe following bugs have been assigned to you 21 days ago (or more) and have still status \"Open\":\n\n";
$msg_open_bugs_end = "\n\nPlease have a look at the bug reports and check for each of the listed reports:\n- Is the assignment of the bug correct? If not, please assign it to the responsible developer or set it to \"Unassigned\" if the maintainer is no longer active.\n- Is it really a bug? If you think it is not a bug but a feature (or a configuration problem), please give a hint to the Feature Wiki (or information about correct configuration) and set the status to \"Closed\".\n- Is the information in the bug report sufficient to reproduce and fix the bug? If not (or unclear), please ask for additional information and set the status of the bug to \"Feedback\" to get this information by the bug reporter.\n- If you can confirm that the reported issue is a bug, please check also if there is a serious reason to postpone a bugfix, if funding is needed for the bugfix or the bug gets in the cue for bugs to be fixed according to priorisation.\n\nThis mail has been sent to you automatically by http://www.ilias.de/mantis. In case of questions, please do not reply to this mail but send a mail to info@ilias.de or add your question to the bug report and set it on the agenda of the next Jour Fixe.\n\nILIAS open source e-Learning e.V.\nwww.ilias.de\n";

$msg_open_bugs_warning_begin = "\n\nThe following bugs have been assigned to you 14 days ago (or more) and have still status \"Open\":\n\n";


$msg_feedback_begin =  "\n\nThe following bugs require your feedback for 21 days or more:\n\n";
$msg_feedback_end = "\n\nThe developer who wants to fix this bug needs additional information from you. Please have a look at the listed bug reports and provide the requested details/data. Otherwise, the bug cannot be fixed and the report will be closed.\n\nThis mail has been sent to you automatically by http://www.ilias.de/mantis. In case of questions, please do not reply to this mail but send a mail to info@ilias.de or add your question to the bug report and set it on the agenda of the next Jour Fixe.\n\nILIAS open source e-Learning e.V. \nwww.ilias.de\n";

$msg_feedback_warning_begin = "\n\nThe following bugs require your feedback for 14 days or more:\n\n";

?>
