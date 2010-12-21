    var SMconfig = {
        debug: true,        
        json: 'gates/seatmanager.php',
        ready: function() {
            if (this.debug) {
                $('#actions').append('<option value="8">Animation</option>');
                $('#username').val('bbohn');
                $('#password').val('le0z0e');
                SM.getMerchants();
            }
         }
   };