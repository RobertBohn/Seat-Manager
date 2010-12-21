<?php

    $con = odbc_connect('Prestige - Regression', 'dba', 'cyg-x-1');
    if (!$con) exit;

    $sql = "select m.merchant_id, m.name
            from merchant_agent a, merchant m
            where a.agent_uid = '".$_GET['u']."' and a.agent_pwd = '".$_GET['p']."'
            and m.merchant_id = a.merchant_id
            and m.master_merchant_id = m.merchant_id
            group by  m.merchant_id, m.name
            order by 2";

    echo('({');
//    echo('name: \'merchants\',');
    echo(' merchants: Array(');

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