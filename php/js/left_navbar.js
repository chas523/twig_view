$(document).ready(function(){
    $("#left_circle").click(function($e){
        $e.preventDefault();
      $("div.left_bar").toggleClass("display_left_bar");
    });
  });