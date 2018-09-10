if(typeof $=='undefined') {
    var headTag = document.getElementsByTagName("head")[0];
    var jqTag = document.createElement('script');
    jqTag.type = 'text/javascript';
    jqTag.src = '/js/jquery-3.1.1.min.js';
    jqTag.onload = start;
    headTag.appendChild(jqTag);
    /*
     var font_cuprum = document.createElement('link');
     font_cuprum.setAttribute('rel', 'stylesheet');
     font_cuprum.setAttribute("type", "text/css");
     font_cuprum.setAttribute('href', 'http://fonts.googleapis.com/css?family=Cuprum');
     headTag.appendChild(font_cuprum);*/
} else {
    start();
}


function start()
{
    $( document ).ready(function() {
        $("div.navbar[class!=minimal]").before().css('left', $(window).width()-40);
    });

    $(window).resize(function() {
        $("div.navbar").before().css('left', $(window).width()-40);
    });

    var counter =0;
    $("li.navbar-status").on('click',function(){
        dev($("div.navbar"));
    });



    $('body').on('keydown', function(event) {
        if (event.keyCode == 192 && event.ctrlKey == true) {
            dev($("div.navbar"));
        }
    });

    $("#buf").click(function(){
        if($("#polo").css("display")=="none") {
            $("#polo").css("display", "table-cell").slideDown();
        } else {
            $("#polo").css("display", "none").slideUp();
        }
    });


    $("body").on('click', "#ajax_list tbody tr[data-response]",function(){
        if($(this).find("span[name='hidden_list']").hasClass("span_show")!=true){
            $(this).find("span[name='hidden_list']").addClass("span_show");
        }
        else{
            $(this).find("span[name='hidden_list']").removeClass("span_show");
        }
    });

    $("body").on('click', "span.dev_toolbar_plus",function(){
        if($(this).parent().next().hasClass("span_show")!=true){
            $(this).parent().next().addClass("span_show");
            $(this).html("-");
        }
        else{
            $(this).parent().next().removeClass("span_show");
            $(this).html("+");
        }
        /*
         if($(this).next().hasClass("span_show")!=true){
         $(this).next().addClass("span_show");
         $(this).html("-");
         }
         else{
         $(this).next().removeClass("span_show");
         $(this).html("+");
         }

         */
    });


    $( document ).ajaxComplete(function(event,request, settings) {
        var ile = $("#ajax_request").attr("data-count");//$("#ajax_request").text();
        ile++;
        var status = "<span style=\"float: left; display: block;\"><span style='color: #6ac334'>"+ile+" -Status</span>: <span style=\"color: white; \">"+request.status+ "</span> <span style='color: #6ac334'>Url: </span><span style=\"color: white; \">"+ settings.url  + "</span> <span style='color: #6ac334'>Data</span>: <span style=\"color: white; \">" + settings.data + "</span></span>";
        var title = " Response: "+request.responseText;
        //var ile = $("#ajax_requests").data("count");
        $("#ajax_request").addClass("pulse");
        setTimeout(function(){$("#ajax_request").removeClass("pulse");},2000);
        $("#ajax_request").attr("data-count",ile);
        $("#ajax_request").text(ile);
        $("<tr data-response='" + ile + "' ><td>"+status+'<span name="hidden_list" style="display:none; min-width: 100px; min-height: 30px;transition: opacity 3s easy-out; opacity: 0;">'+title+'</span></td></tr>').prependTo("table#ajax_list > tbody");
    });





    function dev(pasek)
    {
        if( $(pasek).css("left") =="0px") {
            $(pasek).animate({ left: $(window).width()-40 }, 300);
            $(pasek).removeClass("full_toolbar");
        }
        else{
            $(pasek).animate({ left: "0px" }, 300);
        }
    }

}
