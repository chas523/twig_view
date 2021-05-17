$('#password1, #password2').on('keyup', function () {
    if ($('#password1').val() == $('#password2').val()) {
      $('#password2').css('border-bottom', '2px solid green');
    } else 
    $('#password2').css('border-bottom', '2px solid red');
});
function onChange() {
    const password = document.querySelector('input[name=password]');
    const confirm = document.querySelector('input[name=confirm_password]');
   
    if (confirm.value === password.value) {
      confirm.setCustomValidity('');
    } else {
      confirm.setCustomValidity('Passwords do not match');
    } 
}
