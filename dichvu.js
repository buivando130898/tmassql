server = "https://phanluong.t-matsuoka.com/api/set/set_hospital.php/";
server_get = "https://phanluong.t-matsuoka.com/api/get/get_hospital.php/";
clinic = "";
clinic_name = "";
token_login = "?tokenlogin=";

var service_info = {}
var today = new Date();
var today_string = today.getFullYear() + "-" + `${today.getMonth() + 1}` + "-" + today.getDate();
var today_string_new = today.getDate() + "-" + `${today.getMonth() + 1}` + "-" + today.getFullYear();
var service = "";
var clinic_identifier;
// console.log(today_string);

function getService() {
    const endpoint = `${server_get}get_service`;
    fetch(endpoint)
        .then((response) => response.json())
        .then((data) => {
            for (i = 0; i < data.length; i++) {
                service_info[data[i].stt] = data[i].name;
            }
        });
}

function clinic_info() {
    const endpoint = `${server_get}getClinic_info${token_login}clinic_id=${clinic}`;
    // console.log(endpoint);
    fetch(endpoint)
        .then((response) => response.json())
        .then((data) => {
            service = data[0].service;
            clinic_identifier = data[0].identifier;
            clinic_service_info();
            document.getElementById("clinic_info_update").innerHTML = `
            <li class="menu_item"   > <a  href="./clinic.html" > ${data[0].identifier} - ${data[0].name} </a></li>
            `;
        });
}

function clinic_service_info() {
    const endpoint = `${server_get}clinic_service_info${token_login}identifier=${clinic_identifier}`;
    // console.log(endpoint);
    fetch(endpoint)
        .then((response) => response.json())
        .then((data) => {
            clinic_service_html = `
                <tr>
                    <th> Mã dịch vụ </th>
                    <th> Tên dịch vụ </th>
                    <th> Nhóm </th>
                    <th> Thời gian khám </th>
                    <th class="mobi"> Ghi chú </th>
                    <th> Trạng thái </th>

                </tr>
            
            `;
            for (i = 0; i < data.length; i++) {
                // console.log(data[i]);
                clinic_service_html = clinic_service_html + `
                <tr>
                    <td> ${data[i].identifier}  </td>
                    <td> ${data[i].name}  </td>
                    <td> ${data[i].group_service}  </td>
                    <td> ${data[i].intendTime}  </td>
                    <td  class="mobi"> ${data[i].note}  </td>
                    <td>
                        <input placeholder="" style="width:60" class="btn3" type="submit" value="${data[i].status}" onclick="updateService_stautus('${data[i].status}', ${data[i].stt})">
                    </td>
                </tr>
                `;
            }
            document.getElementById("service_info").innerHTML = clinic_service_html;
        });
}

{/* <td> <button class="btn3" onclick="update_clinic( ${data[i].stt},'${data[i].identifier}', '${data[i].name}', ${data[i].intendTime}, ${data[i].resultTime}, '${data[i].note}')">   Update  </button>  </td> */ }

function updateService_stautus(status, stt) {
    if (status == "Hoạt động") {
        status = "Offline";
    } else {
        status = "Hoạt động";
    }

    const endpoint = `${server}set_service_status${token_login}stt=${stt}&status=${status}`;
    // console.log(endpoint);
    fetch(endpoint)
        .then((response) => response.json())
        .then((data) => {
            clinic_service_info();
            alert(data);
        }).catch(() => {
            alert("Erro2");
        });

}
function update_clinic(stt, identifier, name, intendTime, resultTime, note) {
    document.getElementById("edit_service").style.display = "block";
    document.getElementById("service_stt").value = stt;
    document.getElementById("name").value = name;
    document.getElementById("identifier").value = identifier;
    document.getElementById("intendTime").value = intendTime;
    document.getElementById("resultTime").value = resultTime;
    document.getElementById("note").value = note;
}

function update_service() {
    stt = document.getElementById("service_stt").value;
    name = document.getElementById("name").value;
    identifier = document.getElementById("identifier").value;
    intendTime = document.getElementById("intendTime").value;
    resultTime = document.getElementById("resultTime").value;
    note = document.getElementById("note").value;

    if (name != "" && identifier != "" && intendTime != "" && resultTime != "") {

        const endpoint = `${server}update_service${token_login}stt=${stt}&name=${name}&identifier=${identifier}&intendTime=${intendTime}&resultTime=${resultTime}&note=${note}`;
        // console.log(endpoint);
        fetch(endpoint)
            .then((response) => response.json())
            .then((data) => {
                // console.log(data);
                document.getElementById("name").value = "";
                document.getElementById("identifier").value = "";
                intendTime = document.getElementById("intendTime").value = "";
                resultTime = document.getElementById("resultTime").value = "";
                document.getElementById("note").value = "";
                clinic_service_info();
                alert("Thành công!!!");
            });
    } else {
        alert("ERRO: Vui lòng điền đầy đủ thông tin!")
    }
}
function display_menu() {
    if (document.getElementById("menu_all").style.display == "block") {
        document.getElementById("menu_all").style.display = "none";
    } else {
        document.getElementById("menu_all").style.display = "block";
    }
}

function login() {

    if (localStorage['clinic'] == undefined) {
        window.location = "./login.html";
    } else {
        // console.log("token:"  + localStorage['clinic']);
        clinic = localStorage['clinic'];
        clinic_name = localStorage['clinic_name'];
        token_login = token_login + localStorage["token_login_clinic"] + "&";
        // console.log(clinic_name);
        getService();
        clinic_info();
    }
}

login();

function logout() {
    localStorage.removeItem('clinic');
    localStorage.removeItem('clinic_name');
    localStorage.removeItem('token_login_clinic');
    window.location = "./login.html";
}

