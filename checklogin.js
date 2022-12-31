server_get = "https://phanluong.t-matsuoka.com/api/get/login.php/";
if (localStorage['acc_cskh'] == undefined) {
    window.location = "./login.html";
} else {
	acc_check =  localStorage['acc_cskh'];
	token_check = localStorage["token_login_cskh"];
}
 

const endpoint = `${server_get}/tokenlogin_check?acc=${acc_check}&token=${token_check}`;
		fetch(endpoint)
			.then((response) => response.json())
			.then((data) => {
				if(data == false || data == "false") {
					window.location = "./login.html";
				} else {
					document.getElementById("main").style.display = "block";
				}
			}).catch((error) => {
				alert("Đã xảy ra lỗi !!!");
				window.location = "./login.html";
			});
