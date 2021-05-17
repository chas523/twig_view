$(document).ready(function() {
    let forEach=function(t,o,r){if("[object Object]"===Object.prototype.toString.call(t))for(var c in t)Object.prototype.hasOwnProperty.call(t,c)&&o.call(r,t[c],c,t);else for(var e=0,l=t.length;l>e;e++)o.call(r,t[e],e,t)};
    let hamburgers = document.querySelectorAll(".hamburger");
    let nav = document.getElementsByClassName("nav"); 
    if (hamburgers.length > 0) {
      forEach(hamburgers, function(hamburger) {
        hamburger.addEventListener("click", function() {
          this.classList.toggle("is-active");

        }, false);
      });
    }
   
}); 
$('#hamburger').click(function(){
    if ($('.nav').hasClass('display_nav')) {
        $("#nav").removeClass("display_nav");
    } else {
        $("#nav").addClass("display_nav");
    }

    
})