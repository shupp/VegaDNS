$(document).ready(function() {
    setInterval(function(){

        $.post('/vendor/sysinfo/lib/CPU.php', {CPU:'1'}, function(data){
            var cpupercent = data * 100;
            cpupercent = Math.round(cpupercent * 100) / 100;
            $("#cpupercent").css("width",cpupercent+'%');
            $("#cpuusage").html(cpupercent);

        });

        $.post('/vendor/sysinfo/lib/RAM.php', {RAM:"1"}, function(response){

            var responseArray = response.split('/');

            $('#totalram').html(responseArray[0]);
            $('#freeram').html(responseArray[1]);
            var rampercent = Math.round(responseArray[2] * 100) / 100;
            $("#rampercent").css("width",rampercent+'%');
            $("#ramusage").html(rampercent);
        })
    }, 1000)
});