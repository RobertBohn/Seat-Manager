<?php

    $con = odbc_connect('Prestige - Regression', 'dba', 'cyg-x-1');
    if (!$con) exit;  

    $sql = "select top 1 e.seat_id, x.section_desc, r.row_desc, s.seat_desc,
        isnull(s.location_x,0) as location_x, isnull(s.location_y,0) as location_y
        from event_seating e, seat s, section x, row r
        where e.event_id = ".$_GET['e']."
        and e.seat_id = s.seat_id
        and s.row_id = r.row_id
        and r.section_id = x.section_id
        and s.location_x is null and s.location_y is null order by 1";

    $rs = odbc_exec($con,$sql);

    if (odbc_fetch_row($rs)) {
        $id = odbc_result($rs,1);
        $desc = odbc_result($rs,2) . ' ' .odbc_result($rs,3) . ' ' .odbc_result($rs,4);

        echo('    ({    ');
        echo('    desc: \'' . $desc . '\',');
        echo('    id: ' . $id);
        echo(' }) ');
    }

?>