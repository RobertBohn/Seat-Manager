<?php

/*
 input: e= event_id

 output:
({

    dates: Array(

        {   id:20101123, name:'Sep 8, 2010',
            times: Array(
                { id:'19:30', name:'7:30 PM' }
            )
        },
        {   id:20101121, name:'Sep 9, 2010',
            times: Array(
                { id:'19:30', name:'7:30 PM' },
                { id:'20:00', name:'9:00 PM' }
            )
        }

    )

})

*/

//     http://192.168.1.49/SeatManager/gates/dates.php?e=5130133

    $con = odbc_connect('Prestige - Regression', 'dba', 'cyg-x-1');
    if (!$con) exit;

    $sql = "select start_date, start_time, dateformat(start_date,'Mmm dd, yyyy'), dateformat(start_time,'hh:mm AA')
        from merchant_package mp, package_event pe
        where mp.package_id = pe.package_id
        and pe.event_id = ".$_GET['e']."
        and mp.start_date >= date(getdate())
        group by start_date, start_time
        order by 1, 2";

    echo('({ dates: Array(');

    $current_date = "0";
    $rs = odbc_exec($con,$sql);
    while (odbc_fetch_row($rs)) {

        if ($current_date == odbc_result($rs,1)) {
            // same date, new time
            echo(', ');
        } else {             
            if ($current_date != "0")
                echo(' ) }, ');                       
            
            echo(' { id:\'' . odbc_result($rs,1) . '\', name:\'' . odbc_result($rs,3) . '\',  times: Array(');
        }
        $current_date = odbc_result($rs,1);                
        
        echo('{ id:\'' . odbc_result($rs,2) . '\', name:\'' . odbc_result($rs,4) . '\' }');
     }    
     
     echo(' ) } ) })');
?>