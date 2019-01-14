if(typeof $=='undefined'  || typeof jQuery=='undefined') {
    var headTag = document.getElementsByTagName("head")[0];
    var jqTag = document.createElement('script');
    jqTag.type = 'text/javascript';
    jqTag.src = 'https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js';
    jqTag.onload = start;
    headTag.appendChild(jqTag);
} else {
    $(document).ready(function() {
        start();
    });
}


function start()
{
    $( document ).ready(function() {
        $("div[class*='toolbar-navbar']").before().css('left', $(window).width()-40);
    });

    $(window).resize(function() {
        $("div[class*='toolbar-navbar']").before().css('left', $(window).width()-40);
    });

    var counter =0;
    $("li.toolbar-navbar-status").on('click',function(){
        dev($("div[class*='toolbar-navbar']"));
    });


    $(window).keydown(function(event){
        if (event.keyCode == 192 && event.ctrlKey == true) {
            dev($("div[class*='toolbar-navbar']"));
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
        if($(this).find("span[name='hidden_list']").hasClass("toolbar-span_show")!=true){
            $(this).find("span[name='hidden_list']").addClass("toolbar-span_show");
        }
        else{
            $(this).find("span[name='hidden_list']").removeClass("toolbar-span_show");
        }
    });

    $("body").on('click', "span.dev_toolbar_plus",function(){
        console.log("click");
        if($(this).parent().next().hasClass("toolbar-span_show")!=true){
            $(this).parent().next().addClass("toolbar-span_show");
            $(this).html("-");
        }
        else{
            $(this).parent().next().removeClass("toolbar-span_show");
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
        }
        else{
            $(pasek).animate({ left: "0px" }, 300);
        }
    }

}
