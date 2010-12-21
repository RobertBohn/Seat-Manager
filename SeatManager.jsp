<%@ page import="com.cygnus.ceService.ceServiceClient" %>
<%@ page import="com.cygnus.utils.*" %>
<%@ page import="org.w3c.dom.*" %>
<%@ page import="java.util.*" %>
<%@ page import="java.text.*" %>
<%@ page import="java.io.*" %>
<%
    // Regression Constants
    String DBProxyURL = "http://cygnusdevel.prestigeticketing.com:2705";
    String ceTixURL = "http://cygnusdevel.prestigeticketing.com/ceGateway/servlet/gateway";    
    String db_pool = "cetravel2-regression";       

    // Production Constants
    //String DBProxyURL = "http://pt-shop08.prestigeticketing.com:2705";
    //String ceTixURL = "http://secure.prestigeticketing.com/ceGateway/servlet/gateway";    
    //String db_pool = "prestige_production_db";    

    ceServiceClient transactor = new ceServiceClient();

    // input parms
    String request_type = request.getParameter("type");
    String request_user = request.getParameter("user");
    String request_password = request.getParameter("psw");
    String request_merchant = request.getParameter("merchant");
    String request_event = request.getParameter("event");
    String request_date = request.getParameter("date");
    String request_time = request.getParameter("time");
    String request_package = request.getParameter("package");
    String request_hold = request.getParameter("hold");
    String request_seats = request.getParameter("seats");

    /*  ***** Signin ***********

        input:  type=signin  user=username   psw=password
  
        output:    ({
                        merchants: Array(
                            { id:50082, name:'Harrah\'s Tahoe - Master Merchant' },
                            { id:50075, name:'Golden Nugget - Master Merchant' }
                        )
                    })
    */
    if (request_type!=null)
    {
        if (request_type.equals("signin")) {
            String sql = "select m.merchant_id, replace(name,'''','\\''') as name from merchant_agent a, merchant m where a.agent_uid = '"+request_user+"' and a.agent_pwd = '"+request_password+"' and m.merchant_id = a.merchant_id and m.master_merchant_id = m.merchant_id group by  m.merchant_id, m.name order by 2";
            String sqlStr = StringUtils.encodeString(sql);
            NodeList rows = null;
            String json = "({ merchants: Array(";
            if (transactor.ceServiceTransact("<SERVICE request_type='ExecuteQuery' database='"+db_pool+"' sql='"+sqlStr+"'/>",DBProxyURL)) {
                rows = transactor.getResponseRoot().getElementsByTagName("ROW");

                for (int i=0; i<rows.getLength(); i++)
                {
                    Element row = (Element)rows.item(i);
                    if (i!=0) json = json + ",";
                    json = json + "{ id:" + row.getAttribute("merchant_id") + ", name:'" + row.getAttribute("name").trim() + "' }";
                }
            }
            json = json + ")})";
            %><%=json%><%
        }
    }

    /*  ***** Events ***********

        input:  type=events  merchant=merchant_id

        output:    ({
                        events: Array(
                            { id:500110228, package_id:500110621, name:'Emmylou Harris' },
                            { id:500110256, package_id:500110685, name:'Fab Four' }
                        )
                    })
    */
    if (request_type!=null)
    {
        if (request_type.equals("events")) {
            String sql = "select e.event_id, replace(e.event_desc,'''','\\''') as event_desc, min(mp.package_id) as package_id from merchant_package mp, package_event pe, event e, merchant m where mp.package_id = pe.package_id and pe.event_id = e.event_id and mp.start_date >= date(getdate()) and mp.merchant_id = m.merchant_id and m.master_merchant_id = " + request_merchant + " and e.assigned_seating_flag = 1 group by e.event_id, e.event_desc order by 2";
            String sqlStr = StringUtils.encodeString(sql);
            NodeList rows = null;
            String json = "({ events: Array(";
            if (transactor.ceServiceTransact("<SERVICE request_type='ExecuteQuery' database='"+db_pool+"' sql='"+sqlStr+"'/>",DBProxyURL)) {
                rows = transactor.getResponseRoot().getElementsByTagName("ROW");

                for (int i=0; i<rows.getLength(); i++)
                {
                    Element row = (Element)rows.item(i);
                    if (i!=0) json = json + ",";
                    json = json + "{ id:" + row.getAttribute("event_id") + ", package_id:" + row.getAttribute("package_id") + ", name:'" + row.getAttribute("event_desc").trim() + "' }";
                }
            }
            json = json + ")})";
            %><%=json%><%
        }
    }

    /*  ***** Dates & Times ***********

        input:  type=dates  event=event_id

        output:    ({
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
    if (request_type!=null)
    {
        if (request_type.equals("dates")) {
            String sql = "select start_date, start_time, dateformat(start_date,'Mmm dd, yyyy') as long_date, dateformat(start_time,'hh:mm AA') as long_time from merchant_package mp, package_event pe where mp.package_id = pe.package_id and pe.event_id = " + request_event + " and mp.start_date >= date(getdate()) group by start_date, start_time order by 1, 2";
            String sqlStr = StringUtils.encodeString(sql);
            NodeList rows = null;
            String json = "({dates: Array(";
            String current_date = "0";
            if (transactor.ceServiceTransact("<SERVICE request_type='ExecuteQuery' database='"+db_pool+"' sql='"+sqlStr+"'/>",DBProxyURL)) {
                rows = transactor.getResponseRoot().getElementsByTagName("ROW");

                for (int i=0; i<rows.getLength(); i++)
                {
                    Element row = (Element)rows.item(i);
                    if (current_date.equals(row.getAttribute("start_date"))) {
                        json = json + ",";
                    } else {
                        if (!current_date.equals("0")) {
                            json = json + ")},";
                        }
                        json = json + " { id:'" + row.getAttribute("start_date") + "', name:'" + row.getAttribute("long_date") + "',  times: Array(";
                    }
                    current_date = row.getAttribute("start_date");
                    json = json + "{ id:'" +  row.getAttribute("start_time") + "', name:'" + row.getAttribute("long_time") + "'}";
                 }
            }
            json = json + ")})})";
            %><%=json%><%
        }
    }

    /*  ***** Seats ***********

        input:  type=seats merchant=merchant_id  event=event_id  date=start_date  time=start_time

        output:    ({
                        map_src: 'tahoe.gif',
                        map_width: 1277,
                        map_height: 844,

                        blocked: 'blocked.gif',
                        available: 'available.gif',
                        sold: 'sold.gif',
                        inactive: 'inactive.gif',
                        held: 'held.gif',
                        selected: 'selected.gif',
                        seat_width: 16,
                        seat_height: 16,

                        seats: Array(
                            { id:22927, x:542, y:56, s:'S', d:'Section B Row 1 Seat 1' },
                            { id:22928, x:560, y:56, s:'S', d:'Section B Row 1 Seat 2' },
                            { id:22929, x:578, y:56, s:'S', d:'Section B Row 1 Seat 3' }
                        )
                   })
    */
    if (request_type!=null)
    {
        if (request_type.equals("seats")) {
            // setup defaults by merchgant
            String map_src = "";
            String map_width = "";
            String map_height = "";
            String seat_width = "16";
            String seat_height = "16";

            if (request_merchant.equals("50032")) {
                map_src = "tahoe.gif";
                map_width = "1277";
                map_height = "844";
            }
            if (request_merchant.equals("50075")) {
                map_src = "nugget.gif";
                map_width = "1077";
                map_height = "935";
            }
            if (request_merchant.equals("50023")) {
                map_src = "trop.gif";
                map_width = "1091";
                map_height = "624";
            }
            if (request_merchant.equals("2020")) {
                map_src = "fiesta_small.gif";
                map_width = "1008";
                map_height = "900";
            }                                 
            if (request_merchant.equals("50082")) {
                map_src = "hideout.gif";
                map_width = "781";
                map_height = "620";
            }

            // get map info
            String sql = "select mpc.name, mpc.value from package_event pe, merchant_package_characs mpc where pe.package_id = mpc.package_id and pe.event_id = " + request_event + " and mpc.merchant_id = " + request_merchant;
            String sqlStr = StringUtils.encodeString(sql);
            NodeList rows1 = null;
            if (transactor.ceServiceTransact("<SERVICE request_type='ExecuteQuery' database='"+db_pool+"' sql='"+sqlStr+"'/>",DBProxyURL)) {
                rows1 = transactor.getResponseRoot().getElementsByTagName("ROW");
                for (int i=0; i<rows1.getLength(); i++)
                {
                    Element row = (Element)rows1.item(i);		   
                    if (row.getAttribute("name").equals("map_src")) map_src = row.getAttribute("value");
                    if (row.getAttribute("name").equals("map_width")) map_width = row.getAttribute("value");
                    if (row.getAttribute("name").equals("map_height")) map_height = row.getAttribute("value");
                    if (row.getAttribute("name").equals("seat_height")) seat_height = row.getAttribute("value");
                    if (row.getAttribute("name").equals("seat_width")) seat_width = row.getAttribute("value");
                }
            }

            // get seat info
            sql = "select e.seat_id, isnull(u.status,'A') as status, x.section_desc, r.row_desc, s.seat_desc, isnull(s.location_x,0) as location_x, isnull(s.location_y,0) as location_y from event_seating e left outer join unavailable_seats u on e.event_id = u.event_id and e.seat_id = u.seat_id and u.start_date = '" + request_date + "' and u.start_time = '" + request_time + "', seat s, section x, row r where e.event_id = " + request_event + " and e.seat_id = s.seat_id and s.row_id = r.row_id and r.section_id = x.section_id and s.location_x > 0 and s.location_y > 0 order by 1";
            sqlStr = StringUtils.encodeString(sql);
            NodeList rows = null;

            String json = " ({ " +
                          " map_src: '" +  map_src + "'," +
                          " map_width: " + map_width + "," +
                          " map_height: " + map_height + "," +
                          " blocked: 'blocked.gif'," +
                          " available: 'available.gif'," + 
                          " sold: 'sold.gif'," +
                          " inactive: 'inactive.gif'," +
                          " held: 'held.gif'," +
                          " selected: 'selected.gif'," +
                          " seat_width: " + seat_width + "," +
                          " seat_height: " + seat_height + "," +
                          " seats: Array(";

            if (transactor.ceServiceTransact("<SERVICE request_type='ExecuteQuery' database='"+db_pool+"' sql='"+sqlStr+"'/>",DBProxyURL)) {
                rows = transactor.getResponseRoot().getElementsByTagName("ROW");
                for (int i=0; i<rows.getLength(); i++)
                {
                    Element row = (Element)rows.item(i);
                    if (i!=0) json = json + ",";
                    json = json + "{ id:" + row.getAttribute("seat_id") +
                                  ", x:" + row.getAttribute("location_x") +
                                  ", y:" + row.getAttribute("location_y") +
                                  ", s:'" + row.getAttribute("status") +
                                  "', d:'" + row.getAttribute("section_desc") + " " + row.getAttribute("row_desc") + " " + row.getAttribute("seat_desc") + "' }";
                }
            }
            json = json + ")})";
            %><%=json%><%
        }
    }

    /*  ***** Block/Hold Seats ***********

        input:  type=block  merchant=merchant_id  event=event_id  date=start_date  time=start_time
                  package=package_id  hold=B/H   seats=seat_id-seat_id
  
        output:    ({
                        status: 'OK',
                        message: 'error message text'
                   })
    */
    if (request_type!=null)
    {
        if (request_type.equals("block")) {
            String status = "";
            String message = "Unknown Error";

            String xml = "<SERVICE request_type=\"HoldMerchantPackageSeats\" package_id=\""+request_package+"\" start_date=\""+request_date+"\" start_time=\""+request_time+"\" hold_type=\""+request_hold+"\" merchant_id=\""+request_merchant+"\">";
            String[] seats_array = request_seats.split("-");
            for(int i =0; i < seats_array.length ; i++) {
                xml = xml + "<SEAT id=\"" + seats_array[i] + "\"></SEAT>";
            }
            xml = xml + "</SERVICE>";

            transactor.ceServiceTransact(xml,ceTixURL);
            Element row = transactor.getResponseRoot();
            status = row.getAttribute("status"); 
            if (!status.equals("OK"))            
                message = row.getAttribute("error_msg");

            String json = " ({ " + " status: '" + status + "'," + " message: '" + message + "'" + "})";
            %><%=json%><%
        }
    }

    /*  ***** Release Blocked/Held Seats ***********

        input:  type=release  merchant=merchant_id  event=event_id  date=start_date  time=start_time
                  package=package_id  hold=B/H   seats=seat_id-seat_id

        output:    ({
                        status: 'OK',
                        message: 'error message text'
                   })
    */
    if (request_type!=null)
    {
        if (request_type.equals("release")) {
            String status = "";
            String message = "Unknown Error";

            String xml = "<SERVICE request_type=\"ReleaseMerchantPackageHeldSeats\" package_id=\""+request_package+"\" start_date=\""+request_date+"\" start_time=\""+request_time+"\" hold_type=\""+request_hold+"\" merchant_id=\""+request_merchant+"\">";
            String[] seats_array = request_seats.split("-");
            for(int i =0; i < seats_array.length ; i++) {
                xml = xml + "<SEAT id=\"" + seats_array[i] + "\"></SEAT>";
            }
            xml = xml + "</SERVICE>";

            transactor.ceServiceTransact(xml,ceTixURL);
            Element row = transactor.getResponseRoot();
            status = row.getAttribute("status");
            if (!status.equals("OK"))
                message = row.getAttribute("error_msg");

            String json = " ({ " + " status: '" + status + "'," + " message: '" + message + "'" + "})";            
            %><%=json%><%
        }
    }


%>