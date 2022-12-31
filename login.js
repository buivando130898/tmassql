server_get = "https://phanluong.t-matsuoka.com/api/get/login.php/";

function login() {
    user = document.getElementById("user").value;
    pass = document.getElementById("pass").value;
    const endpoint = `${server_get}/login_admin?user=${user}&pass=${pass}`;
    // console.log(endpoint);
    fetch(endpoint)
        .then((response) => response.json())
        .then((data) => {
            localStorage.setItem('acc_admin', data[0].acc);
            localStorage.setItem('token_login_admin', data[0].token);
            window.location = "./check.html";
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