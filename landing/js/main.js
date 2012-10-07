$(document).ready(function() {  
    $(window).load(function(){
        $('.doc-loader').fadeOut('slow');
    });
});

$(window).resize(function () {
    boxHeight();
}).ready(function($){
    // Tweet
    $("#twitter").tweetcycle({ username: "zourbuth", count: 10 });

    // Countdown
    $('#countdown').countdown({
        until: new Date("10/15/2012 00:00"),
        format: "DHMS",
        labels: ['years', 'months', 'weeks', 'days', 'hours', 'minutes', 'seconds'],
        labels1: ['year', 'month', 'week', 'day', 'hour', 'minute', 'second'],
        layout: '<div class="count day">{dn}<span>{dl}</span></div>'+
                '<div class="count hour">{hn}<span>{hl}</span></div>'+
                '<div class="count minute">{mn}<span>{ml}</span></div>'+                        
                '<div class="count second">{sn}<span>{sl}</span></div>'+
                '<div class="clear"></div>'
    });

    // Floating Rocket
    $('.floating-phone').everyTime(10, function () {
        $(".floating-phone").animate({
            marginTop: "+=20",
            marginLeft: "+=10"
        }, 1000, 'linear').animate({
            marginTop: "-=20",
            marginLeft: "-=10"
        }, 1000, 'linear');
    });

});