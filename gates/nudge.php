<?php

/*
in start up
                // Nudge X-Y Seat Position
                //$(window).keypress(function() {
                //    if (event.keyCode==50 || event.keyCode==52 || event.keyCode==54 || event.keyCode==56) {
                //        event.preventDefault();
                //        $.getJSON('gates/nudge.php?s=' + SM.current_seat_id + '&x=' + event.keyCode, function(data) {
                //            SM.getShow();
                //        });
                //    }
                //});

                SM.next_seat_id = 0;
                SM.next_seat_desc = 0;
                SM.current_seat_id = 0;






in seat selected

             //var seat = SM.show.seats[idx];
            //
            //if (SMconfig.debug) {
            //    SM.current_seat_id = seat.id;
            //    $('#header h1').html(seat.d);
            //}







*/

    $con = odbc_connect('Prestige - Regression', 'dba', 'cyg-x-1');
    if (!$con) exit;  

    //    up=56   down=50   left=52     right=54

    $sql = '';
    if ($_GET['x']==56) $sql = "update seat set location_y = location_y - 1 where seat_id = ".$_GET['s'];
    if ($_GET['x']==50) $sql = "update seat set location_y = location_y + 1 where seat_id = ".$_GET['s'];
    if ($_GET['x']==52) $sql = "update seat set location_x = location_x - 1 where seat_id = ".$_GET['s'];
    if ($_GET['x']==54) $sql = "update seat set location_x = location_x + 1 where seat_id = ".$_GET['s'];
 

    odbc_exec($con,$sql);

    echo('    ({    ');        
    echo('    id: ' . $_GET['s']);
    echo(' }) ');   

?>