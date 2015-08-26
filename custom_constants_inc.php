<?php
    /* custom_constants_inc.php - Local configuration file
       -----------------------------------------------------
	In this file you can place your custom configuration to define: 
	custom severities and custom resolutions for mantis.
	The file is automatically detected and included by Mantis code.
    */



	# Custom status code
	define ( 'OPEN', 10);
	define ( 'UNASSIGNED', 15 );
        define ( 'FEEDBACK', 20 );
	define ( 'POSTPONED', 25 );
	define ( 'FUNDING_NEEDED', 35 );
//	define ( 'FUNDING NEEDED', 35 );
        define ( 'ASSIGNED', 50 );
	define ( 'FIXING_ACC_TO_PRIO', 60);
//	define ( 'FIXING ACC. TO PRIO', 60);
        define ( 'RESOLVED', 80 );
        define ( 'CLOSED', 90);

/*
	# Custom status code
	define ( 'OPEN', 10);
	define ( 'UNASSIGNED', 15 );
//      define ( 'feedback', 20 );
	define ( 'POSTPONED', 25 );
	define ( 'FUNDING NEEDED', 35 );
//      define ( 'assigned', 50 );
	define ( 'FIXING ACC. TO PRIO', 60);
//      define ( 'resolved', 80 );
//      define ( 'closed', 90);
*/

?>
