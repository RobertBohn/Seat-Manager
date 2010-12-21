<?php
    require_once 'ServiceRequest.php';
    define('TRIPRES_HOST','http://cygnusdevel.prestigeticketing.com/ceGateway/servlet/gateway');

    $con = odbc_connect('Prestige - Regression', 'dba', 'cyg-x-1');
    if (!$con) exit;


    if ($_GET['type']=="release") {
        //$seats_array = explode('-',$_GET['seats']);
        //$request = '<SERVICE request_type="ReleaseMerchantPackageHeldSeats" package_id="'.$_GET['package'].'" start_date="'.$_GET['date'].'" start_time="'.$_GET['time'].'" hold_type="'.$_GET['hold'].'" merchant_id="'.$_GET['merchant'].'">';
        $seats_array = explode('-',$_POST['seats']);
        $request = '<SERVICE request_type="ReleaseMerchantPackageHeldSeats" package_id="'.$_POST['package'].'" start_date="'.$_POST['date'].'" start_time="'.$_POST['time'].'" hold_type="'.$_POST['hold'].'" merchant_id="'.$_POST['merchant'].'">';

        for ($i=0; $i<count($seats_array); $i++) {
            $request = $request.'<SEAT id="'.$seats_array[$i].'"></SEAT>';
        }
        $request = $request.'</SERVICE>';
        $status = '';
        $error_msg = '';


        $doc = ServiceRequest(TRIPRES_HOST,$request);
        $nodes = $doc->getElementsByTagName('SERVICE');
        $status = $nodes->item(0)->getAttribute('status');
        if ($status!='OK') {
            $error_msg = $nodes->item(0)->getAttribute('error_msg');
        }

        echo('({');
        echo('status:\'' . $status . '\',');
        echo('message:\'' . $error_msg . '\'');
        echo('})');

        //echo ($status=='OK') ? $status : $error_msg;

        exit;
    }




    if ($_GET['type']=="block") {
        //$seats_array = explode('-',$_GET['seats']);
        //$request = '<SERVICE request_type="HoldMerchantPackageSeats" package_id="'.$_GET['package'].'" start_date="'.$_GET['date'].'" start_time="'.$_GET['time'].'" hold_type="'.$_GET['hold'].'" merchant_id="'.$_GET['merchant'].'">';

        $seats_array = explode('-',$_POST['seats']);
        $request = '<SERVICE request_type="HoldMerchantPackageSeats" package_id="'.$_POST['package'].'" start_date="'.$_POST['date'].'" start_time="'.$_POST['time'].'" hold_type="'.$_POST['hold'].'" merchant_id="'.$_POST['merchant'].'">';

        for ($i=0; $i<count($seats_array); $i++) {
            $request = $request.'<SEAT id="'.$seats_array[$i].'"></SEAT>';
        }
        $request = $request.'</SERVICE>';
        $status = '';
        $error_msg = '';

        $doc = ServiceRequest(TRIPRES_HOST,$request);
        $nodes = $doc->getElementsByTagName('SERVICE');
        $status = $nodes->item(0)->getAttribute('status');
        if ($status!='OK') {
            $error_msg = $nodes->item(0)->getAttribute('error_msg');
        }

        echo('({');
        echo('status:\'' . $status . '\',');
        echo('message:\'' . $error_msg . '\'');
        echo('})');

        //echo ($status=='OK') ? $status : $error_msg;

        exit;
    }


    if ($_GET['type']=="signin") {

        $sql = "select m.merchant_id, m.name
            from merchant_agent a, merchant m
            where a.agent_uid = '".$_GET['user']."' and a.agent_pwd = '".$_GET['psw']."'
            and m.merchant_id = a.merchant_id
            and m.master_merchant_id = m.merchant_id
            group by  m.merchant_id, m.name
            order by 2";

        echo('({');
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
        exit;
    }


    if ($_GET['type']=="events") {
        $sql = "select e.event_id,   replace(e.event_desc,'''','\\''') as event_desc, min(mp.package_id) as package_id from
            merchant_package mp, package_event pe, event e, merchant m
            where mp.package_id = pe.package_id
            and pe.event_id = e.event_id
            and mp.start_date >= date(getdate())
            and mp.merchant_id = m.merchant_id
            and m.master_merchant_id = ".$_GET['merchant']."
            and e.assigned_seating_flag = 1
            group by e.event_id, e.event_desc
            order by 2";

        echo('({ events: Array(');
        $first = true;
        $rs = odbc_exec($con,$sql);
        while (odbc_fetch_row($rs)) {
            if (!$first) echo(',');
            echo('{ id:'.odbc_result($rs,1).', package_id:'.odbc_result($rs,3).', name:\''. odbc_result($rs,2) .'\' }');
            $first = false;
        }
        echo(')})');
        exit;
    }



    if ($_GET['type']=="dates") {
        $sql = "select start_date, start_time, dateformat(start_date,'Mmm dd, yyyy'), dateformat(start_time,'hh:mm AA')
            from merchant_package mp, package_event pe
            where mp.package_id = pe.package_id
            and pe.event_id = ".$_GET['event']."
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
        exit;
    }





    if ($_GET['type']=="seats") {
        $map_src = "";
        $map_width = "";
        $map_height = "";
        $seat_width = "16";
        $seat_height = "16";

        // set defaults
        if ($_GET['merchant'] == "50032") {
            $map_src = "tahoe.gif";
            $map_width = "1277";
            $map_height = "844";
        }
        if ($_GET['merchant'] == "50075") {
            $map_src = "nugget.gif";
            $map_width = "1077";
            $map_height = "935";
        }
        if ($_GET['merchant'] == "50023") {
            $map_src = "trop.gif";
            $map_width = "1091";
            $map_height = "624";
        }
        if ($_GET['merchant'] == "2020") {
            $map_src = "fiesta_small.gif";
            $map_width = "1008";
            $map_height = "900";
        }
        if ($_GET['merchant'] == "50082") {
            $map_src = "hideout.gif";
            $map_width = "781";
            $map_height = "620";
        }

        $sql = "select mpc.name, mpc.value from package_event pe, merchant_package_characs mpc
            where  pe.package_id = mpc.package_id
            and pe.event_id = ".$_GET['event']."
            and mpc.merchant_id = ".$_GET['merchant'];

        $rs = odbc_exec($con,$sql);
        while (odbc_fetch_row($rs)) {
            switch (odbc_result($rs,1)) {
                case "map_src" :
                    $map_src = odbc_result($rs,2);
                    break;
                case "map_width" :
                    $map_width = odbc_result($rs,2);
                    break;
                case "map_height" :
                    $map_height = odbc_result($rs,2);
                    break;
                case "seat_width" :
                    $seat_width = odbc_result($rs,2);
                    break;
                case "seat_height" :
                    $seat_height = odbc_result($rs,2);
                    break;
            }
        }

        $sql = "select e.seat_id, isnull(u.status,'A'), x.section_desc, r.row_desc, s.seat_desc, isnull(s.location_x,0), isnull(s.location_y,0)
            from event_seating e left
            outer join unavailable_seats u
            on e.event_id = u.event_id and e.seat_id = u.seat_id and u.start_date = '".$_GET['date']."'
            and u.start_time = '".$_GET['time']."',
            seat s, section x, row r
            where e.event_id = ".$_GET['event']."
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
        echo('    seat_width: '.$seat_width.',');
        echo('    seat_height: '.$seat_height.',');
        echo('    seats: Array(');

        $first = true;
        $rs = odbc_exec($con,$sql);
        while (odbc_fetch_row($rs)) {
            if (!$first) echo(',');
            echo('{ id:'.odbc_result($rs,1).', x:'.odbc_result($rs,6).', y:'.odbc_result($rs,7).', s:\''.odbc_result($rs,2).'\', d:\''.odbc_result($rs,3). ' '.odbc_result($rs,4).' '.odbc_result($rs,5).'\' }');
            $first = false;
        }

        echo(' )}) ');
        exit;
    }

?>