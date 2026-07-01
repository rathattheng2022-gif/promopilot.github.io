var i = 1;
function iconBar() {
  if (i == 1) {
    document.querySelector('.menu-left').style.left = "0px";
    document.querySelector('.menu-left').style.display= "block";
    i = 0;
  } else {
    document.querySelector('.menu-left').style.left = "-200px";
    document.querySelector('.menu-left').style.display= "none";
    i = 1;
  }
}
function showPopup(code){

    document.getElementById("popupCode").innerHTML = code;

    document.getElementById("popupOverlay").style.display = "flex";

    navigator.clipboard.writeText(code);

}

function closePopup(){

    document.getElementById("popupOverlay").style.display = "none";

}