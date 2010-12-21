    var SM = {        
        show: null,       // show info object
        merchants: null,  // merchants info object
        events: null,     // events info object
        dates: null,      // dates info object
        zoom: 100.0,      // zoom index percentage

        // on window resize
        resize: function() {
            $('#map').css('height', ($(window).height() - 160) + 'px');
            $('#map').css('width', ($(window).width() - 20) + 'px');
        },
        // on window ready
        ready: function() {
            // capture window resize events
            $(window).resize(function() {SM.resize();});
            this.resize();              
            // hook up buttons
            $('#zoomout').click( function() {SM.resizeShow(-12.5);});
            $('#zoomin').click( function() {SM.resizeShow(12.5);});
            $('#merchants').change(SM.merchantSelected);
            $('#events').change(SM.eventSelected);
            $('#dates').change(SM.dateSelected);
            $('#times').change(SM.timeSelected);
            $('#actions').change(SM.actionsSelected);
            $('#submit').click( function() { SM.submitClicked(); });
            $('#signin').click( function() { SM.getMerchants(); });
            $('#username').keypress(function() { if (event.keyCode == '13') { SM.getMerchants(); } });
            $('#password').keypress(function() { if (event.keyCode == '13') { SM.getMerchants(); } });
            $('#from').bind('focusin', function() { if (this.value=='from:') this.value = ''; });
            $('#to').bind('focusin', function() { if (this.value=='to:') this.value = ''; });

            // temp
            if (SMconfig.debug) {

               // Nudge X-Y Seat Position
                $(window).keypress(function() {
                    if (event.keyCode==50 || event.keyCode==52 || event.keyCode==54 || event.keyCode==56) {
                        event.preventDefault();
                        $.getJSON('gates/nudge.php?s=' + SM.current_seat_id + '&x=' + event.keyCode, function(data) {
                            SM.getShow();
                        });
                    }
                });

                SM.next_seat_id = 0;
                SM.next_seat_desc = 0;
                SM.current_seat_id = 0;


                $('#username').val('bbohn');
                $('#password').val('le0z0e');



                SM.getMerchants();
            }
        },
        // resize show
        resizeShow: function(increment) {
            // show must exist
            if (!SM.show) return;
            // limit zoom to 50% - 150%
            if (SM.zoom + increment > 150 || SM.zoom + increment < 50) return;
            // resize show map matt
            SM.zoom += increment;
            $('#matt').css('width', (SM.show.map_width * SM.zoom / 100) + 'px');
            $('#matt').css('height', (SM.show.map_height * SM.zoom / 100) + 'px');
            // resposition & resize seats
            for (i=0; i<SM.show.seats.length; i++) {
                var seat = SM.show.seats[i];
                $('#s' + seat.id).css('left', (seat.x * SM.zoom / 100) + 'px');
                $('#s' + seat.id).css('top', (seat.y * SM.zoom / 100) + 'px');
                $('#s' + seat.id).css('width', (SM.show.seat_width * SM.zoom / 100) + 'px');
                $('#s' + seat.id).css('height', (SM.show.seat_height * SM.zoom / 100) + 'px');
            }
        },
        // get merchant list
        getMerchants: function() {
             SM.clearEvents();
             $.getJSON(SMconfig.json + '?type=signin&user=' + $('#username').val() + '&psw=' + $('#password').val(), function(data) {
                SM.merchants = data;
                var m = SM.merchants.merchants;
                switch (m.length) {
                    case 0:
                        // no merchants - user can't do anything
                        alert('You are not authorized to view any merchants.');
                        break;
                    case 1:
                        // only one - set to default
                        $('#login').hide();
                        $('#container').show();
                        $('#merchants').html('<option value="' + m[0].id + '">' + SM.description_1(m[0].name) + '</option>');
                        SM.merchantSelected();
                        break;
                    default:
                        // multiple - initialize the select drop down
                        $('#login').hide();
                        $('#container').show();
                        $('#merchants').html('<option value="0">Merchants</option>').removeAttr('disabled');
                        for (i=0;i<m.length;i++)
                            $('#merchants').append('<option value="' + m[i].id + '">' + SM.description_1(m[i].name) + '</option>');
                        break;
                }
            });
        },
        // get events list
        getEvents: function() {
            SM.clearDates();
            $.getJSON(SMconfig.json + '?type=events&merchant=' + $('#merchants option:selected').val(), function(data) {
                SM.events = data;
                var e = SM.events.events;
                switch (e.length) {
                    case 0:
                        // no events - user can't do anything
                        alert('There are no upcoming assigned seating events for this merchant.');
                        break;
                    case 1:
                        // only one - set to default
                        $('#events').html('<option value="' + e[0].id + '">' + e[0].name + '</option>');
                        SM.eventSelected();
                        break;
                    default:
                        // multiple - initialize the select drop down
                        $('#events').html('<option value="0">Events</option>').removeAttr('disabled');
                        for (i=0;i<e.length;i++)
                            $('#events').append('<option value="' + e[i].id + '">' + e[i].name + '</option>');
                        break;
                }
            });
        },
        // get dates list
        getDates: function() {
            SM.clearTimes();
            $.getJSON(SMconfig.json + '?type=dates&event=' + $('#events option:selected').val(), function(data) {
                SM.dates = data;
                var d = SM.dates.dates;
                switch (d.length) {
                    case 0:
                        // no dates - user can't do anything
                        alert('There are no upcoming dates for this event.');
                        break;
                    case 1:
                        // only one - set to default
                        $('#dates').html('<option value="' + d[0].id + '">' + d[0].name + '</option>');
                        SM.dateSelected();
                        break;
                    default:
                        // multiple - initialize the select drop down
                        $('#dates').html('<option value="0">Event Dates</option>').removeAttr('disabled');
                        for (i=0;i<d.length;i++)
                            $('#dates').append('<option value="' + d[i].id + '">' + d[i].name + '</option>');
                        break;
                }
            });
        },
        // get times list
        getTimes: function() {
            SM.clearGate();
            var d = SM.dates.dates;
            var t = null;
            for (i=0;i<d.length;i++) {
                if (d[i].id == $('#dates option:selected').val()) {
                    t = d[i].times;
                    break;
                }
            }
            if (!t) return;
            switch (t.length) {
                case 0:
                    // no times - user can't do anything
                    alert('There are no event times for this date.');
                    break;
                case 1:
                    // only one - set to default
                    $('#times').html('<option value="' + t[0].id + '">' + t[0].name + '</option>');
                    SM.timeSelected();
                    break;
                default:
                    // multiple - initialize the select drop down
                    $('#times').html('<option value="0">Event Times</option>').removeAttr('disabled');
                    for (i=0;i<t.length;i++)
                        $('#times').append('<option value="' + t[i].id + '">' + t[i].name + '</option>');
                    break;
            }
        },
        // load show
        getShow: function() {
            $('#submit').attr('disabled', true);
            $.getJSON(SMconfig.json + '?type=seats&merchant=' + $('#merchants option:selected').val()+'&event=' + $('#events option:selected').val()+'&date=' + $('#dates option:selected').val() + '&time=' + $('#times option:selected').val(), function(data) {
                SM.show = data;               
                // display venue matt
                s = '<li><img id="matt" src="images/' + SM.show.map_src + '" style="width:' + SM.show.map_width + 'px; height:' + SM.show.height + 'px;"/></li>';
                // display individual seats
                for (i=0; i<SM.show.seats.length; i++) {
                    var seat = SM.show.seats[i];        
                    seat.selected = false;
                    s += '<li><img id="s' + seat.id + '" title="' + seat.id + ': ' + seat.d + '" src="images/' + SM.getSeatImage(i) + '" onclick="SM.seatClicked(' + i + ');"/></li>';
                }
                $('#gate').html(s);
                SM.resizeShow(0);
                $('#actions').removeAttr('disabled');                
                $('#zoomin').removeAttr('disabled');
                $('#zoomout').removeAttr('disabled');
                $('#submit').removeAttr('disabled');
            });
        },
        // merchant selected
        merchantSelected: function() {
            $('#merchants option[value="0"]').remove();
            $('#merchants').removeAttr('disabled');
            SM.getEvents();
        },
        // event selected
        eventSelected: function() {
            $('#events option[value="0"]').remove();            
            $('#events').removeAttr('disabled');
            SM.getDates();
        },
        // date selected
        dateSelected: function() {
            $('#dates option[value="0"]').remove();
            $('#dates').removeAttr('disabled');
            SM.getTimes();
         },
        // time selected
        timeSelected: function() {
            $('#times option[value="0"]').remove();
            $('#times').removeAttr('disabled');
            SM.getShow();
        },
        // actions selected
        actionsSelected: function() {           
            if ($('#actions option:selected').val()=='7') {
                $('#from').show();
                $('#to').show();
            } else {
                $('#from').hide();
                $('#to').hide();
            }
        },
        // clear out events
        clearEvents: function() {
            $('#events').html('<option value="0">Events</option>').attr('disabled', true);
            SM.clearDates();
        },
        // clear out dates
        clearDates: function() {
            $('#dates').html('<option value="0">Event Dates</option>').attr('disabled', true);
            SM.clearTimes();
        },
        // clear out times
        clearTimes: function() {
            $('#times').html('<option value="0">Event Times</option>').attr('disabled', true);
            SM.clearGate();
        },
        // clear out gate info
        clearGate: function() {
            $('#actions').attr('disabled', true);
            $('#submit').attr('disabled', true);
            $('#zoomin').attr('disabled', true);
            $('#zoomout').attr('disabled', true);
            $('#gate').html('');
        },
        // seat clicked
        seatClicked: function(idx) {


            var seat = SM.show.seats[idx];

            if (SMconfig.debug) {
                SM.current_seat_id = seat.id;
                $('#header h1').html(seat.d);
            }




   //         var seat = SM.show.seats[idx];
     //       seat.selected = (seat.selected) ? false : true;
       //     $('#s' + seat.id).attr('src', 'images/' + SM.getSeatImage(idx));
            // auto fill from & to
            //if ($('#actions option:selected').val()=='7') {
            //    if ($('#from').val()=='from:')
            //        $('#from').val(seat.id);
            //    else if ($('#to').val()=='to:')
            //        $('#to').val(seat.id);
            //}
        },
        // submit clicked
        submitClicked: function() {
            switch ($('#actions option:selected').val()) {
                case '1': // View
                    SM.getShow();
                    break;                
                case '2': // Block Seats
                case '3': // Hold Seats
                    SM.blockSeats(($('#actions option:selected').val()==2) ? 'B' : 'H');
                    break;                
                case '4': // Release Blocked Seats
                case '5': // Release Held Seats                    
                    SM.releaseSeats(($('#actions option:selected').val()==4) ? 'B' : 'H');
                    break;
                case '6': // Change Seats
                    alert('Change Seats is not functional yet.');
                    break;                
                case '7': // Select Seats
                    var from = parseInt( $('#from').val() );
                    var to = parseInt($('#to').val());
                    if (isNaN(from) || isNaN(to)){
                        alert('Please enter valid \'from\' and \'to\' seat numbers.');
                    } else {
                        for (i=0; i<SM.show.seats.length; i++) {
                            var seat = SM.show.seats[i];
                            if (seat.id >= from && seat.id <= to) {
                                seat.selected = true;
                                $('#s' + seat.id).attr('src', 'images/' + SM.show.selected);
                            }
                        }
                    }
                    break;                                    
            }            
        },
        // calculate the proper seat image
        getSeatImage: function(idx) {
            var seat = SM.show.seats[idx];
            var img = SM.show.inactive;
            switch (seat.s) {
                case 'A':img = SM.show.available; break;
                case 'S':img = SM.show.sold; break;
                case 'B':img = SM.show.blocked; break;
                case 'H':img = SM.show.held; break;
            }
            if (seat.selected) img = SM.show.selected;
            return img;
        },
        // block/hold seats
        blockSeats: function(type) {
             // get the package_id given the selected event
            var event_id = $('#events option:selected').val();
            var package_id = 0;
            for (i=0; i<SM.events.events.length; i++) {
                if (SM.events.events[i].id==event_id) {
                    package_id = SM.events.events[i].package_id;
                    break;
                }
            }
            // get the list of seats
            var seats = '';
            for (i=0; i<SM.show.seats.length; i++) {
                // only block/hold selected available seats
                var seat = SM.show.seats[i];
                if (seat.selected && seat.s == 'A') {
                    if (seats!='') seats +='-';
                    seats += seat.id;
                }
            }
            if (seats=='') {
                alert('There are no available seats selected.');
            } else {
                $('#submit').attr('disabled', true);
                $.getJSON(SMconfig.json+'?type=block&hold='+type+'&seats='+seats+'&merchant='+$('#merchants option:selected').val()+'&package=' + package_id +'&date='+$('#dates option:selected').val()+'&time='+$('#times option:selected').val(),function(data) {
                    if (data.status!='OK') {
                        alert(data.message);
                        $('#submit').removeAttr('disabled');
                    } else {
                        SM.getShow();
                    }
                });
            }
        },
        // release blocked/held seats
        releaseSeats: function(type) {
             // get the package_id given the selected event
            var event_id = $('#events option:selected').val();
            var package_id = 0;
            for (i=0; i<SM.events.events.length; i++) {
                if (SM.events.events[i].id==event_id) {
                    package_id = SM.events.events[i].package_id;
                    break;
                }
            }
            // get the list of seats
            var seats = '';
            for (i=0; i<SM.show.seats.length; i++) {
                // only block/hold selected available seats
                var seat = SM.show.seats[i];
                if (seat.selected && seat.s == type) {
                    if (seats!='') seats +='-';
                    seats += seat.id;
                }
            }
            if (seats=='') {
                alert('There are no ' + ((type=='B')?'blocked':'held') + ' seats selected.');
            } else {
                $('#submit').attr('disabled', true);
                $.getJSON(SMconfig.json+'?type=release&hold='+type+'&seats='+seats+'&merchant='+$('#merchants option:selected').val()+'&package=' + package_id +'&date='+$('#dates option:selected').val()+'&time='+$('#times option:selected').val(),function(data) {
                    if (data.status!='OK') {
                        alert(data.message);
                        $('#submit').removeAttr('disabled');
                    } else {
                        SM.getShow();
                    }
                });
            }
        },
        // get description 1 (everything before the first dash '-'
        description_1: function(str) {
            idx = str.indexOf('-');
            if (idx > 0) {
                return $.trim(str.substr(0,idx));
            }
            return str;
        }
        // end
   };