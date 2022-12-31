server_get = "http://localhost:8080/hospital/api/get/login.php/";

function login() {
    user = document.getElementById("user").value;
    pass = document.getElementById("pass").value;
	pass_new =  document.getElementById("pass_new").value;
	pass_new2 =  document.getElementById("pass_new2").value;

	check = true;
    var letter = /[^a-zA-Z0-9!@#$%^&*]/;

    if ((pass_new.match(letter) || pass_new.length < 5 ) && check == true ) {
        check = false;
        alert("Lỗi định dạng. Mật khẩu chỉ gồm các kí tự: a->zA->Z0->9!@#$%^&*. Và > 5 kí tự!!!");
    }


	if(pass_new != pass_new2 && check == true) {
		check = false;
		document.getElementById("info_erro").innerHTML = "Mật khẩu mới không trùng nhau";
	}

	if(check) {
		const endpoint = `${server_get}/pass_new?user=${user}&pass=${pass}&pass_new=${pass_new}`;
		// console.log(endpoint);
		fetch(endpoint)
			.then((response) => response.json())
			.then((data) => {
				document.getElementById("info_erro").innerHTML = data;
			}).catch((error) => {
				//// console.log("erro");
				document.getElementById("info_erro").innerHTML = "Đã xảy ra lỗi.";
			});
	}


    // console.log(user);
}

document.addEventListener("keyup", function (event) {
    if (event.keyCode === 13) {
        login();
    }
});