server_get = "https://phanluong.t-matsuoka.com/api/get/login.php/";

function login() {
    user = document.getElementById("user").value;
    pass = document.getElementById("pass").value;
    clinic_id = document.getElementById("clinic_id").value;

    const endpoint = `${server_get}/login_clinic?user=${user}&pass=${pass}&clinic_id=${clinic_id}`;
    // console.log(endpoint);
    fetch(endpoint)
        .then((response) => response.json())
        .then((data) => {
            // console.log(data[0].stt);
            localStorage.setItem('clinic', clinic_id);
			localStorage.setItem('acc_clinic', user);
            localStorage.setItem('token_login_clinic', data[0].token);
            window.location = "./clinic.html";
        }).catch((error) => {
            //// console.log("erro");
            document.getElementById("info_erro").innerHTML = "Tài khoản hoặc mật khẩu không chính xác";
        });

    // console.log(user);
}

document.addEventListener("keyup", function (event) {
    if (event.keyCode === 13) {
        login();
    }
});
