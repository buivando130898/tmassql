server = "https://phanluong.t-matsuoka.com/api/set/set_hospital.php/";
server_get = "https://phanluong.t-matsuoka.com/api/get/get_hospital.php/";
url = window.location.href;
url_home = "https://phanluong.t-matsuoka.com/sp/sp.html?identifier="
identifier = url.replace(url_home, '');
// console.log(identifier);
var service_info = [];
location_color = undefined;
token_login = "?tokenlogin=";
time_id = 1;

var update_customer;
var dem = 0;


var today = new Date();
var today_string = today.getFullYear() + "-" + `${today.getMonth() + 1}` + "-" + today.getDate();
// console.log(today_string);
document.getElementById("time_day").innerHTML = today_string;


function getCustomer_info() {
    const endpoint = `${server_get}get_customer_info${token_login}identifier=${identifier}`;
    // console.log(endpoint);
    fetch(endpoint)
        .then((response) => response.json())
        .then((data) => {
            // console.log(data);
            document.getElementById("name").innerHTML = data[0].name;
            document.getElementById("identifier").innerHTML = data[0].identifier;
            document.getElementById("birthday").innerHTML = data[0].birthday;
            document.getElementById("sex").innerHTML = data[0].sex;
            document.getElementById("phone").innerHTML = data[0].phone;
            document.getElementById("note_customer").innerHTML = data[0].note;
        });
}


function get_clinic_location() {
    const endpoint = `${server_get}get_clinic_location${token_login}`;
    // console.log(endpoint);
    fetch(endpoint)
        .then((response) => response.json())
        .then((data) => {
            // console.log(data);
            location_html = "";
            for (i = 0; i < data.length; i++) {
                location_html = location_html + `<li class="menu_item location" id="location${data[i].location}" > <a style="color: black"  onclick="location_info(${data[i].location})"> Tầng ${data[i].location}</a></li>`;
            }
            document.getElementById('location').innerHTML = location_html;
        });
}

function getService() {
    const endpoint = `${server_get}get_service${token_login}`;
    // console.log(endpoint);
    fetch(endpoint)
        .then((response) => response.json())
        .then((data) => {
            service_value = "";
            // console.log(data);

            for (i = 0; i < data.length; i++) {
                service_info[data[i].identifier] = data[i].name;
            }
        });
}

function medical_diary_was_clinic_begin() {
    const endpoint = `${server}medical_diary_was_clinic_begin${token_login}customer_identifier=${identifier}`;
    // console.log(endpoint);
    fetch(endpoint)
        .then((response) => response.json())
        .then((data) => {
            alert(data);
            location.reload();
        });
}

function medical_diary_was_clinic_begin2(customerId) {
    
    clearInterval(update_customer);
    update_customer = null;
    
    if (confirm("Mời khách hàng đi đến phòng khám?")) {
    const endpoint = `${server}medical_diary_was_clinic_begin${token_login}customer_identifier=${customerId}`;
    fetch(endpoint)
        .then((response) => response.json())
        .then((data) => {
            getCustomer_info_all();
            alert(data);
            update_getCustomer_info_all();
            //location.reload();
        });
    }
}

function check_NULL(data) {
    if (data == null || data == "nan" || data == "null") {
        return "";
    }
    return data;
}

function openDate_medical_diary(id) {
    date_medical_diary_value = "";
    date_medical_diary_value_begin = "";

    style = "";
    const endpoint = `${server_get}get_data_medical_diary_date2${token_login}identifier=${identifier}&date=${id}`;
    // console.log("/////////////////////////////////////////////////////");
    // console.log(endpoint);
    fetch(endpoint)
        .then((response) => response.json())
        .then((data) => {
            // console.log(data);
            if (data != "") {
                for (i = 0; i < data.length; i++) {

                    if (data[i].status == "Chờ khám" || data[i].status == "Đang khám") {
                        if (data[i].status == "Đang khám") {
                            date_medical_diary_value_begin = date_medical_diary_value_begin + `
                            <tr style = "background-color: #009bdd">
                                <td  class = "mobi">${data[i].service}</td>
                                <td>${data[i].service_name}</td>
                                <td>${data[i].status}</td>
                                <th>${check_NULL(data[i].time_input)}</th>
                                <td>${check_NULL(data[i].time_out)}</td>
                                <td>${check_NULL(data[i].clinic)}</td>
                                <td>${check_NULL(data[i].note)}</td>
                            </tr>` ;

                        } else {
                            date_medical_diary_value = date_medical_diary_value + `
                            <tr style = "background-color: #9ee2ff">
                                <td  class = "mobi">${data[i].service}</td>
                                <td>${data[i].service_name}</td>
                                <td>${data[i].status}</td>
                                <th class = "mobi">${check_NULL(data[i].time_input)}</th>
                                <td  class = "mobi">${check_NULL(data[i].time_out)}</td>
                                <td>${check_NULL(data[i].clinic)}</td>
                                <td  class = "mobi">${check_NULL(data[i].note)}</td>
                            </tr>` ;
                        }

                    } else {
                        if (data[i].status == "Đã khám") {
                            date_medical_diary_value = date_medical_diary_value + `
                            <tr style = "background-color: #ffffc9">
                            <td  class = "mobi">${data[i].service}</td>
                            <td>${data[i].service_name}</td>
                            <td>${data[i].status}</td>
                            <th  class = "mobi">${check_NULL(data[i].time_input)}</th>
                            <td class = "mobi">${check_NULL(data[i].time_out)}</td>
                            <td>${check_NULL(data[i].clinic)}</td>
                            <td  class = "mobi">${check_NULL(data[i].note)}</td>
                            </tr>` ;
                        } else {
                            date_medical_diary_value = date_medical_diary_value + `
                            <tr style = "background-color: white">
                                <td  class = "mobi">${data[i].service}</td>
                                <td>${data[i].service_name}</td>
                                <td>${data[i].status}</td>
                                <th  class = "mobi">${check_NULL(data[i].time_input)}</th>
                                <td  class = "mobi">${check_NULL(data[i].time_out)}</td>
                                <td>${check_NULL(data[i].clinic)}</td>
                                <td  class = "mobi">${check_NULL(data[i].note)}</td>
                            </tr>` ;
                        }
                    }
                }
                document.getElementById("medical_diary").innerHTML = `
                    <table>
                        <tr>
                            <th  class = "mobi">Mã dịch vụ</th>
                            <th>Dịch vụ khám</th>
                            <th>Trạng thái</th>
                            <th class = "mobi">Chờ khám</th>
                            <th class = "mobi">Khám xong</th>
                            <th>Phòng khám</th>
                            <th  class = "mobi">Ghi chú</th>
                        </tr>
                        ${date_medical_diary_value_begin + date_medical_diary_value}
                    </table>
                `
                    ;
            }

        });
}

function getCustomer_info_all() {
    customer_info = `                
        <tr>
        <th class = "mobi">Mã</th>
        <th class = "mobi">Vip</th>
        <th>Họ Tên</th>
        <th class = "mobi">Ngày sinh</th>
        <th class = "mobi">Giới tính</th>
        <th class = "mobi">Ghi chú</th>
        <th class = "mobi">Đoàn khám</th>
        <th class = "mobi">Đến khám</th>
        <th>Gói khám</th>
        <th>Trạng thái</th>
        <th>Chờ </th>
        <th>Khám</th>
        </tr>`;
    const endpoint = `${server_get}get_customer_date${token_login}`;
    // console.log(endpoint);
    fetch(endpoint)
        .then((response) => response.json())
        .then((data) => {
            // console.log(data);
            for (i = 0; i < data.length; i++) {

                service_type = "";
                for (value of data[i].sevice_type) {
                    service_type += "<p>" + value + "</p>";
                }

                if (data[i].status == "Chưa khám") {
                    status_customer = `<button onclick="medical_diary_was_clinic_begin2('${data[i].identifier}')" > Mời vào khám </button>`;
                    time_wait = "";
                } else if (data[i].status == "Đã khám") {
                    status_customer = "Hoàn thành";
                    time_wait = "";
                } else {
                    status_customer = data[i].clinic + " - " + data[i].status ;
                    time_wait =  data[i].time_wait
                }

                customer_info = customer_info + ` 
                    <tr>
                        <td class = "mobi"> ${data[i].identifier} </td> 
                        <td class = "mobi"> ${data[i].vip} </td> 
                        <td> ${data[i].name} </td> 
                        <td class = "mobi"> ${data[i].birthday}</td> 
                        <td class = "mobi"> ${data[i].sex}</td> 
                        <td class = "mobi"> ${data[i].note}</td> 
                        <td class = "mobi"> ${data[i].group_info}</td> 
                        <td class = "mobi"> ${data[i].time_go}</td> 
                        <td> ${service_type}</td> 
                        <td>${status_customer} </td> 
                        <td style=" text-align: center;">${time_wait} </td> 
                        <td><a style="text-decoration:none; color: blue" href="https://phanluong.t-matsuoka.com/sp/sp.html?identifier=${data[i].identifier}"> Xem </a></td> 
                    </tr>`;

            }
            document.getElementById("customer_info").innerHTML = customer_info;
        });
}


function getCustomer_info_all_shearch() {
    clearInterval(update_customer);
    update_customer = null;
    name_shearch = document.getElementById("name_shearch").value;
    customer_info = `                
        <tr>
        <th class = "mobi">Mã</th>
        <th class = "mobi">Vip</th>
        <th>Họ Tên</th>
        <th class = "mobi">Ngày sinh</th>
        <th class = "mobi">Giới tính</th>
        <th class = "mobi">Ghi chú</th>
        <th class = "mobi">Đoàn khám</th>
        <th class = "mobi">Đến khám</th>
        <th>Gói khám</th>
        <th>Trạng thái</th>
        <th>Chờ </th>
        <th>Khám</th>
        </tr>`;
    const endpoint = `${server_get}get_customer_date_shearch${token_login}name=${name_shearch}`;
    // console.log(endpoint);
    fetch(endpoint)
        .then((response) => response.json())
        .then((data) => {
            // console.log(data);
            for (i = 0; i < data.length; i++) {

                service_type = "";
                for (value of data[i].sevice_type) {
                    service_type += "<p>" + value + "</p>";
                }

                if (data[i].status == "Chưa khám") {
                    status_customer = `<button onclick="medical_diary_was_clinic_begin2('${data[i].identifier}')" > Mời vào khám </button>`;
                    time_wait = "";
                } else if (data[i].status == "Đã khám") {
                    status_customer = "Hoàn thành";
                    time_wait = "";
                } else {
                    status_customer = data[i].clinic + " - " + data[i].status ;
                    time_wait =  data[i].time_wait
                }

                customer_info = customer_info + ` 
                    <tr>
                        <td class = "mobi"> ${data[i].identifier} </td> 
                        <td class = "mobi"> ${data[i].vip} </td> 
                        <td> ${data[i].name} </td> 
                        <td class = "mobi"> ${data[i].birthday}</td> 
                        <td class = "mobi"> ${data[i].sex}</td> 
                        <td class = "mobi"> ${data[i].note}</td> 
                        <td class = "mobi"> ${data[i].group_info}</td> 
                        <td class = "mobi"> ${data[i].time_go}</td> 
                        <td> ${service_type}</td> 
                        <td>${status_customer} </td> 
                        <td style=" text-align: center;">${time_wait} </td> 
                        <td><a style="text-decoration:none; color: blue" href="https://phanluong.t-matsuoka.com/sp/sp.html?identifier=${data[i].identifier}"> Xem </a></td> 
                    </tr>`;

            }
            document.getElementById("customer_info").innerHTML = customer_info;
        });
}

function check_time_id() {
    dem ++;
    console.log(dem);
    if(dem%10 != 0) {
        const endpoint1 = `${server_get}get_timeid_max${token_login}`;
        // console.log(endpoint1);
        fetch(endpoint1)
            .then((response) => response.json())
            .then((data) => {
                // console.log(data);
                data = Number(data);
                if(data > time_id) {
                    time_id = data;
                    getCustomer_info_all();
                }
            });
    } else {
        getCustomer_info_all();
    }
}

function update_getCustomer_info_all() {
    update_customer = setInterval(check_time_id, 1000);
    // console.log(dem);
}

function send_clinic_customer() {
    clearInterval(update_customer);
    update_customer = null;
    clinic = document.getElementById("clinic_send").value
    const endpoint = `${server}send_clinic_customer${token_login}customer_identifier=${identifier}&clinic=${clinic}`;
    // console.log(endpoint);
    fetch(endpoint)
        .then((response) => response.json())
        .then((data) => {
            getCustomer_info_all();
            openDate_medical_diary(today_string);
            alert(data);
            update_getCustomer_info_all();
        }).catch(function (erro) {
            alert("Không thể mời vào phòng này!!!");
            update_getCustomer_info_all();
        });
}

function send_clinic_customer2(customer_id, clinic_new, clinic_id) {
    clearInterval(update_customer);
    update_customer = null;
    const endpoint = `${server}send_clinic_customer${token_login}customer_identifier=${customer_id}&clinic=${clinic_new}`;
    // console.log(endpoint);
    fetch(endpoint)
        .then((response) => response.json())
        .then((data) => {
            getCustomer_info_all();
            openDate_medical_diary(today_string);
            check_clinic_new(clinic_id);
            alert(data);
            //location.reload();
            update_getCustomer_info_all();
        });
}


function login() {
    if (localStorage['acc_cskh'] == undefined) {
        window.location = "./login.html";
    } else {
        // console.log("token:"  + localStorage['acc_cskh']);
        acc_cskh = localStorage['acc_cskh'];
        token_login = token_login + localStorage["token_login_cskh"] + "&";
        if (identifier != "https://phanluong.t-matsuoka.com/sp/sp.html") {
            getCustomer_info();
            getService();
            openDate_medical_diary(today_string);
        } else {
            // console.log("clear");
            document.getElementById("customer_info_div").innerHTML = "";
            document.getElementById("customer_info_div").style.display = "none";
        }
        getCustomer_info_all();
        get_clinic_location();
        update_getCustomer_info_all();
    }
}

function display_menu() {
    if (document.getElementById("menu_all").style.display == "block") {
        document.getElementById("menu_all").style.display = "none";
    } else {
        document.getElementById("menu_all").style.display = "block";
    }
}

login();

function logout() {
    localStorage.removeItem('acc_cskh');
    localStorage.removeItem('token_login_cskh');
    window.location = "./login.html";
}

function location_info(data_location) {
    if (location_color != undefined) {
        document.getElementById(`location${location_color}`).style.background = "#e9e9e9";
    }
    location_color = data_location;
    document.getElementById(`location${location_color}`).style.background = "#c5d3ff";
    const endpoint = `${server_get}get_location_info${token_login}location=${data_location}`;
    // console.log(endpoint);
    fetch(endpoint)
        .then((response) => response.json())
        .then((data) => {
            // console.log(data);
            location_clinic_html = `
            <tr style = "background-color: rgb(143 143 143)">
                <th style = "width:89px" >Mã phòng</th>
                <th style = "width:89px">Trạng thái</th>
                <th style = "width:89px ; text-align: center;" >Đang khám</th>
                <th style = "width:89px ; text-align: center;">Chờ khám</th>
                <th class = "mobi" style = "width:89px ; text-align: center;">Đã khám</th>
                <th class = "mobi" style = "width:300px">Tên phòng</th>
                <th class = "mobi" style = "width:300px">Bác sĩ</th>
                <th> Check </th>
            </tr>
            
            `;
            for (i = 0; i < data.length; i++) {
                color = "white";
                if (data[i].clinic_are_time == "Offline") {
                    color = "#adadad";
                } else {
                    data[i].sl = Number(data[i].sl);
                    if (data[0].sl > 10) {
                        color = "red"
                    } else if (data[i].sl > 5) {
                        color = "yellow";
                    } else if (data[i].sl > 0) {
                        color = "#bfe4f9";
                    } else {
                        color = "white";
                    }
                }

                location_clinic_html = location_clinic_html + `
                <tr style="background: white" >
                    <td style = "text-align: center">${data[i].identifier}</td>
                    <td  style="background: ${color}" >${data[i].clinic_are_time}</td>
                    <td style = "text-align: center">${data[i].clinic_are}</td>
                    <td style = "text-align: center">${data[i].sl - data[i].clinic_are}</td>
                    <td class = "mobi" style = "text-align: center">${data[i].clinic_has}</td>
                    <td class = "mobi">${data[i].name}</td>
                    <td class = "mobi">${data[i].doctor}</td>
                    <td> <div id = "${data[i].identifier}">  <button onclick="check_clinic_new('${data[i].identifier}')"  > Check </button>   </div> </td>
                </tr>
                `
            }
            document.getElementById("location_clinic_table").innerHTML = location_clinic_html;
        })

    if (document.getElementById("menu_all").style.display == "block") {
        document.getElementById("menu_all").style.display = "none";
    }
    document.getElementById("div_clinic_info").style.display = "block";
}


function location_info_all(data_location) {
    if (location_color != undefined) {
        document.getElementById(`location${location_color}`).style.background = "#e9e9e9";
    }
    location_color = data_location;
    document.getElementById(`location${location_color}`).style.background = "#c5d3ff";

    const endpoint = `${server_get}get_location_info_all${token_login}`;
    // console.log(endpoint);
    fetch(endpoint)
        .then((response) => response.json())
        .then((data) => {
            // console.log(data);
            location_clinic_html = `
            <tr style = "background-color: #adadad">
                <th style = "width:89px; text-align: center;" >Mã phòng (Tầng ${data[0].location})</th>
                <th style = "width:89px">Trạng thái</th>
                <th style = "width:89px" >Đang khám</th>
                <th style = "width:89px">Chờ khám</th>
                <th class = "mobi" style = "width:89px">Đã khám</th>
                <th class = "mobi" style = "width:300px">Tên phòng</th>
                <th class = "mobi" style = "width:300px">Bác sĩ</th>
                <th> Check </th>

            </tr>
            `;
            for (i = 0; i < data.length; i++) {

                if (i != 0) {
                    if (data[i].location != data[i - 1].location) {
                        location_clinic_html += `
                        <tr style = "background-color: #adadad">
                            <th style = "width:89px ; text-align: center;" >Mã phòng (Tầng ${data[i].location})</th>
                            <th style = "width:89px">Trạng thái</th>
                            <th style = "width:89px ;" >Đang khám</th>
                            <th style = "width:89px ; text-align: center;">Chờ khám</th>
                            <th class = "mobi" style = "width:89px ; text-align: center;">Đã khám</th>
                            <th class = "mobi" style = "width:300px">Tên phòng</th>
                            <th class = "mobi" style = "width:300px">Bác sĩ</th>
                            <th> Check </th>
                        </tr>
                        `;
                    }
                }
                color = "white";
                if (data[i].clinic_are_time == "Offline") {
                    color = "#adadad";
                } else {
                    data[i].sl = Number(data[i].sl);
                    if (data[0].sl > 10) {
                        color = "red"
                    } else if (data[i].sl > 5) {
                        color = "yellow";
                    } else if (data[i].sl > 0) {
                        color = "#bfe4f9";
                    } else {
                        color = "white";
                    }
                }

                location_clinic_html = location_clinic_html + `
                <tr style="background: white" >
                    <td style=" text-align: center;">${data[i].identifier}</td>
                    <td style="background: ${color}">${data[i].clinic_are_time}</td>
                    <td style = "text-align: center">${data[i].clinic_are}</td>
                    <td style = "text-align: center">${data[i].sl - data[i].clinic_are}</td>
                    <td class = "mobi" style = "text-align: center">${data[i].clinic_has}</td>
                    <td class = "mobi" >${data[i].name}</td>
                    <td class = "mobi" >${data[i].doctor}</td>
                    <td> <div id = "${data[i].identifier}">  <button onclick="check_clinic_new('${data[i].identifier}')"  > Check </button>   </div> </td>
                </tr>
                `
            }
            document.getElementById("location_clinic_table").innerHTML = location_clinic_html;
        })

    if (document.getElementById("menu_all").style.display == "block") {
        document.getElementById("menu_all").style.display = "none";
    }
    document.getElementById("div_clinic_info").style.display = "block";
}


function close_clinic_info() {
    document.getElementById("div_clinic_info").style.display = "none";
}

// function reset_mysql() {
//     const endpoint = `${server}reset_mysql${token_login}`;
//     // console.log(endpoint);
//     fetch(endpoint)
//         .then((response) => response.json())
//         .then((data) => {
//             location.reload();
//         });
// }

function check_clinic_new(clinic_id) {
    const endpoint = `${server}check_clinic_new${token_login}clinic_id=${clinic_id}`;
    // console.log(endpoint);
    fetch(endpoint)
        .then((response) => response.json())
        .then((data) => {
            // console.log(data);
            check = `
                <table>
                <tr>
                    <th> Họ tên </th>
                    <th> Chờ </th>
                    <th> Phòng mới </th>
                    <th> Chờ mới </th>
                    <th> Chuyển </th>
                </tr>
        
            `;
            for (value of data) {

                if (value["clinic"]) {
                    check += `<tr>
                        <td> ${value["name"]} 
                        <td> ${value["time_wait"]}</td>    
                        <td> ${value["clinic"]}</td>  
                        <td> ${value["time_wait_new"]}</td>    
                        <td>  <button onclick = "send_clinic_customer2('${value["customer_identifier"]}', '${value["clinic"]}' , ${clinic_id})"> Chuyển</button></td> 
                    </tr>`;
                } else {
                    check += `<tr>
                    <td> ${value["name"]} 
                    <td> ${value["time_wait"]}</td>    
                    <td> </td>  
                    <td> </td>    
                    <td> </td> 
                </tr>`;
                }

            }
            if (check) {
                document.getElementById(clinic_id).innerHTML = check;
            }

        });
}


// setInterval(()=>{
//     getCustomer_info_all();
// }, 10000)
