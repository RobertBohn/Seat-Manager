<?php

    $con = odbc_connect('Prestige - Regression', 'dba', 'cyg-x-1');
    if (!$con) exit;

    $sql = "select e.event_id, e.event_desc from
        merchant_package mp, package_event pe, event e, merchant m
        where mp.package_id = pe.package_id
        and pe.event_id = e.event_id
        and mp.start_date >= date(getdate())
        and mp.merchant_id = m.merchant_id
        and m.master_merchant_id = ".$_GET['m']."
        and e.assigned_seating_flag = 1
        group by e.event_id, e.event_desc
        order by 2";

    echo('({');
//    echo('name: \'events\',');
    echo(' events: Array(');

    $first = true;
    $rs = odbc_exec($con,$sql);
    while (odbc_fetch_row($rs)) {
        if (!$first) echo(',');
        echo('{ id:'.odbc_result($rs,1).', name:\''. str_replace('\'', '\\\'',  odbc_result($rs,2))   .'\' }');
        $first = false;
     }
    echo(' )');
    echo('})');
?>