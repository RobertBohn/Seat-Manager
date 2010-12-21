<?php

    $con = odbc_connect('Prestige - Regression', 'dba', 'cyg-x-1');
    if (!$con) exit;



    $map_src = "";
    $map_width = "";
    $map_height = "";

    // set defaults
    if ($_GET['m'] == "50032") {
        $map_src = "tahoe.gif";
        $map_width = "1277";
        $map_height = "844";
    }
    if ($_GET['m'] == "50075") {
        $map_src = "nugget.gif";
        $map_width = "1077";
        $map_height = "935";
    }
    

    $sql = "select e.seat_id, isnull(u.status,'A'), x.section_desc, r.row_desc, s.seat_desc, isnull(s.location_x,0), isnull(s.location_y,0)
        from event_seating e left
        outer join unavailable_seats u
        on e.event_id = u.event_id and e.seat_id = u.seat_id and u.start_date = '".$_GET['d']."'
        and u.start_time = '".$_GET['t']."',
        seat s, section x, row r
        where e.event_id = ".$_GET['e']."
        and e.seat_id = s.seat_id
        and s.row_id = r.row_id
        and r.section_id = x.section_id
        and s.location_x > 0
        and s.location_y > 0
        order by 1";
  

    echo('    ({    ');
    
    echo('    map_src: \'' . $map_src . '\',');
    echo('    map_width: ' . $map_width . ',');
    echo('    map_height: ' . $map_height . ', ');
    echo('    blocked: \'blocked.gif\',');
    echo('    available: \'available.gif\',');
    echo('    sold: \'sold.gif\',');
    echo('    inactive: \'inactive.gif\',');
    echo('    held: \'held.gif\',');
    echo('    selected: \'selected.gif\',');
    echo('    seat_width: 16,');
    echo('    seat_height: 16,');
    echo('    seats: Array(');





    $first = true;
    $rs = odbc_exec($con,$sql);
    while (odbc_fetch_row($rs)) {
        if (!$first) echo(',');
        echo('{ id:'.odbc_result($rs,1).', x:'.odbc_result($rs,6).', y:'.odbc_result($rs,7).', s:\''.odbc_result($rs,2).'\', d:\''.odbc_result($rs,3). ' '.odbc_result($rs,4).' '.odbc_result($rs,5).'\' }');
        $first = false;
     }


    echo(' )}) ');


?>