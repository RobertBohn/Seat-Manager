<?php

/*
in startup

           if (SMconfig.debug) {
                // Initialize X-Y Seat Position
                //$("#map").click(function(e){
                //    $.getJSON('gates/addseat.php?s=' + SM.next_seat_id + '&x=' + (e.pageX-14) + '&y=' + (e.pageY-153), function(data) {
                //        SM.getShow();
                //    });
                //});


                SM.next_seat_id = 0;
                SM.next_seat_desc = 0;
                SM.current_seat_id = 0;



in getshow
                // Get Next X-Y Seat Position Tool
                //if (SMconfig.debug) {
                //    $.getJSON('gates/next.php?e=' + $('#events option:selected').val(), function(data) {
                //        SM.current_seat_id = SM.next_seat_id;
                //        SM.next_seat_id = data.id;
                //        SM.next_seat_desc = data.desc;
                //        $('#header h1').html('Next: ' + SM.next_seat_id + ': ' + SM.next_seat_desc   );
                //    });
                //}



*/
    $con = odbc_connect('Prestige - Regression', 'dba', 'cyg-x-1');
    if (!$con) exit;  

    $sql = "update seat set location_x = ".$_GET['x'].", location_y = ".$_GET['y']." where seat_id = ".$_GET['s'];

    odbc_exec($con,$sql);

    echo('    ({    ');        
    echo('    id: ' . $_GET['s']);
    echo(' }) ');   

?>