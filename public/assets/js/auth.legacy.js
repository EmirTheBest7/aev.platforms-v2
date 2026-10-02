var state = false;

function showPass() {
    if (state) {
        document.getElementById("regpass").setAttribute("type", "password");
        document.getElementById("eye").classList.add("uil-eye-slash");
        document.getElementById("eye").classList.remove("uil-eye");
        state = false;
    } else {
        document.getElementById("regpass").setAttribute("type", "text");
        document.getElementById("eye").classList.add("uil-eye");
        document.getElementById("eye").classList.remove("uil-eye-slash");
        state = true;
    }
}

function checkAvailibility() {
    var validationFile = './helpers/checkValidation.php';
    
    $('#regnick').blur(function () {
        var nickname = $(this).val();

        $.ajax({
            url: validationFile,
            method: "POST",
            data: {
                nick_name: nickname
            },
            success: function (data) {
                if (data != '0') {
                    // Button
                    $("input[name='regnick']").css("border", "1px dashed red")

                    // Availibility Query
                    //$('input[type="submit"]').attr("disabled", true);


                } else {
                    $("input[name='regnick']").css("border", "1px solid transparent")

                    // Availibility Query
                    //$('input[type="submit"]').attr("disabled", false);
                }
            }
        })
    });
    $('#regemail').blur(function () {
        var email = $(this).val();
        $.ajax({
            url: validationFile,
            method: "POST",
            data: {
                mail_check: email
            },
            success: function (data) {
                if (data != '0') {
                    // Button
                    $("input[name='regemail']").css("border", "1px dashed red")

                    // Availibility Query 
                    //$('input[type="submit"]').attr("disabled", true);
                } else {
                    $("input[name='regemail']").css("border", "1px solid transparent")

                    // Availibility Query
                    //$('input[type="submit"]').attr("disabled", false);
                }
            }
        })
    });
};


var x = document.getElementById("login1");
var y = document.getElementById("register1");

$("#ext-sign-in-content").hide();

function register() {
    x.style.left = "-400px";
    y.style.left = "0px";
    $("#ext-sign-in-content").fadeIn(1500);
    
}
function login() {
    x.style.left = "0px";
    y.style.left = "450px";
    $("#ext-sign-in-content").fadeOut();
}


$('.ext-sign-in-btn').click(function(){
    console.log("Button Clicked");
});


