({    
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
        { id:22929, x:578, y:56, s:'S', d:'Section B Row 1 Seat 3' },
        { id:22930, x:595, y:56, s:'A', d:'Section B Row 1 Seat 4' },
        { id:22931, x:613, y:56, s:'A', d:'Section B Row 1 Seat 5' },
        { id:22932, x:631, y:56, s:'B', d:'Section B Row 1 Seat 6' },
        { id:22933, x:649, y:56, s:'A', d:'Section B Row 1 Seat 7' },
        { id:22934, x:667, y:56, s:'A', d:'Section B Row 1 Seat 8' },
        { id:22935, x:685, y:56, s:'A', d:'Section B Row 1 Seat 9' },
        { id:22936, x:703, y:56, s:'A', d:'Section B Row 1 Seat 10' },
        { id:22937, x:542, y:84, s:'A', d:'Section B Row 2 Seat 1' },
        { id:22938, x:560, y:84, s:'A', d:'Section B Row 2 Seat 2' },
        { id:22939, x:578, y:84, s:'A', d:'Section B Row 2 Seat 3' },
        { id:22940, x:595, y:84, s:'A', d:'Section B Row 2 Seat 4' },
        { id:22941, x:613, y:84, s:'A', d:'Section B Row 2 Seat 5' },
        { id:22942, x:631, y:84, s:'A', d:'Section B Row 2 Seat 6' },
        { id:22943, x:649, y:84, s:'A', d:'Section B Row 2 Seat 7' },
        { id:22944, x:667, y:84, s:'A', d:'Section B Row 2 Seat 8' },
        { id:22945, x:685, y:84, s:'A', d:'Section B Row 2 Seat 9' },
        { id:22946, x:703, y:84, s:'A', d:'Section B Row 2 Seat 10' }


//22937	A	Section B	Row 2	Seat 1	0	0
//22938	A	Section B	Row 2	Seat 2	0	0
//22939	A	Section B	Row 2	Seat 3	0	0
//22940	A	Section B	Row 2	Seat 4	0	0
//22941	A	Section B	Row 2	Seat 5	0	0
//22942	A	Section B	Row 2	Seat 6	0	0
//22943	A	Section B	Row 2	Seat 7	0	0
//22944	A	Section B	Row 2	Seat 8	0	0
//22945	A	Section B	Row 2	Seat 9	0	0
//22946	A	Section B	Row 2	Seat 10	0	0











    )

})

/*
select e.seat_id, isnull(u.status,'A'), x.section_desc, r.row_desc, s.seat_desc, isnull(s.location_x,0), isnull(s.location_y,0)
from event_seating e
left outer join unavailable_seats u
on e.event_id = u.event_id and e.seat_id = u.seat_id and u.start_date = 20101127 and u.start_time = '19:30',
seat s, section x, row r
where e.event_id = 500110256
and e.seat_id = s.seat_id
and s.row_id = r.row_id
and r.section_id = x.section_id
order by 1;
*/