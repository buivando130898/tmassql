server = "https://phanluong.t-matsuoka.com/api/set/set_hospital.php/";
server_get = "https://phanluong.t-matsuoka.com/api/get/get_hospital.php/";
clinic = "";
clinic_name = "";
var service_info = {}
var today = new Date();
var today_string = today.getFullYear() + "-" + `${today.getMonth() + 1}` + "-" + today.getDate();
var today_string_new = today.getDate() + "-" + `${today.getMonth() + 1}` + "-" + today.getFullYear();
var service = "";
var clinic_identifier;
token_login = "?tokenlogin=";
click_customer_identifier = "";
time_id = 0;

// console.log(today_string);

function getService2() {
    const endpoint = `${server_get}get_service${token_login}`;
    fetch(endpoint)
        .then((response) => response.json())
        .then((data) => {
            for (i = 0; i < data.length; i++) {
                service_info[data[i].identifier] = data[i].name;
            }
        });
}

function getCustomer_info() {
    customer_info = `                
        <tr>
            <th>Mã khách hàng</th>
            <th class = "mobi">Vip </th>
            <th>Họ Tên</th>
            <th class = "mobi">Ngày sinh</th>
            <th class = "mobi">Giới tính</th>
            <th>Dịch vụ</th>
            <th class = "mobi">Trạng thái</th>
        </tr>`;
    const endpoint2 = `${server_get}get_clinic_customer_info${token_login}clinic_identifier=${clinic_identifier}`;


    fetch(endpoint2)
        .then((response) => response.json())
        .then((data) => {
            // console.log(data);
            for (i = 0; i < data.length; i++) {
                customer_info = customer_info + ` 
                <tr>
                    <td> ${data[i].customer_identifier} </td> 
                    <td class = "mobi"> ${data[i].vip} </td> 
                    <td> ${data[i].name} </td> 
                    <td class = "mobi"> ${data[i].birthday} </td> 
                    <td class = "mobi"> ${data[i].sex}</td> 
                    <td> <button class="btn3" onclick="view_service('${data[i].customer_identifier}')"> view </button> </td>
                    <td class = "mobi"> ${data[i].are}/${data[i].sum} </td> 
                </tr>`;
            }
            document.getElementById("customer_info").innerHTML = customer_info;
        });
}

function close_info_service() {
    document.getElementById("div_customer_info_service").style.display = "none";
}

function view_service(id) {
    click_customer_identifier = id;
    customer_info = `                
        <tr>
            <th  class = "mobi">Mã dịch vụ</th>
            <th>Dịch vụ</th>
            <th  class = "mobi">Phòng khám</th>
            <th>Trạng thái</th>
            <th>Thời gian</th>
            <th>Tùy chỉnh</th>
            
        </tr>`;
    const endpoint2 = `${server_get}get_clinic_customer_info2${token_login}clinic_identifier=${clinic_identifier}&customer_identifier=${id}`;
    // console.log(endpoint2)
    fetch(endpoint2)
        .then((response) => response.json())
        .then((data) => {
            // console.log(data);
            document.getElementById("info_customer").innerHTML = `
            <h3>Mã khách hàng: ${data[0].customer_identifier}</h3>
            <h3>Họ tên: ${data[0].name}   Giới tính:  ${data[0].sex}</h3>
            <h3>Sinh nhật: ${data[0].birthday}</h3>
            `

            for (i = 0; i < data.length; i++) {
                // console.log(i);

                if (data[i].status == "Đã khám") {
                    enter = "Đã khám"
                } else if (data[i].status == "Đang khám") {
                    enter = "Đang khám"
                } else {
                    enter = `<button class="btn3" onclick="enterClinic(${data[i].stt})"> Vào khám</button>`;
                    if(data[i].status == "Chưa khám") {
                        data[i].time_input = "";
                        data[i].clinic = "";
                    }
                }  

                enter = `<button onclick="setting_clinic(${data[i].stt}, 'Chưa khám')"> Chưa khám</button>
                <button onclick="setting_clinic(${data[i].stt}, 'Chờ khám')"> Chờ khám</button>
                <button onclick="setting_clinic(${data[i].stt}, 'Đã khám')"> Đã khám</button>`

                customer_info = customer_info + ` 
            <tr>
                <td  class = "mobi"> ${data[i].service}</td>
                <td> ${data[i].service_name}</td>
                <td  class = "mobi"> ${data[i].clinic} </td> 
                <td> ${data[i].status} </td> 
                <td> ${data[i].time_input.slice(0,5)} </td> 
                <td> ${enter} </td> 
            </tr>`;
            }
            document.getElementById("customer_info_service").innerHTML = customer_info;
            document.getElementById("div_customer_info_service").style.display = "block";
        });

}

function setting_clinic(stt, status) {
    if (confirm("Cảnh báo: Bạn có chắc chắn muốn thay đổi lại trạng thái của khách hàng này không?")) {
        const endpoint = `${server}setting_clinic${token_login}stt=${stt}&status=${status}&clinic_identifier=${clinic_identifier}`;
        // console.log(endpoint);
        fetch(endpoint)
            .then((response) => response.json())
            .then((data) => {
                // console.log(data);
                view_service(`${click_customer_identifier}`)
                getCustomer_info_wait();
                getCustomer_info_are();
                //getCustomer_info();

                alert(data)
            });
    }
}

function getCustomer_info_wait() {

    clearInterval(update_wait);
    update_wait = null;

    customer_info_wait = `                
        <tr>
            <th  class = "mobi">Mã khách hàng</th>
            <th class = "mobi">Vip</th>
            <th>Họ Tên</th>
            <th  class = "mobi">Ngày sinh</th>
            <th  class = "mobi">Giới tính</th>
            <th>Chờ khám </th>
            <th>Dịch vụ</th>
            <th>Tùy chỉnh</th>
        </tr>`;

    const endpoint1 = `${server_get}get_clinic_customer_info_wait${token_login}clinic=${clinic_identifier}`;
    // console.log("___________________________");
    // console.log(endpoint1);
    fetch(endpoint1)
        .then((response) => response.json())
        .then((data) => {
            // console.log(data);
            if (data != null) {
                for (i = 0; i < data.length; i++) {
                    if (data[i].status == "Đã khám") {
                        enter = "Đã khám"
                    } else if (data[i].status == "Đang khám") {
                        enter = "Đang khám"
                    } else {
                        enter = `<button class="btn3" onclick="enterClinic_wait('${data[i].customer_identifier}')"> Vào khám</button>`;
                    }
                    customer_info_wait = customer_info_wait + ` 
                        <tr>
                            <td  class = "mobi"> ${data[i].customer_identifier} </td> 
                            <td class = "mobi"> ${data[i].vip}</td>
                            <td> ${data[i].name} </td> 
                            <td  class = "mobi"> ${data[i].birthday} </td> 
                            <td  class = "mobi"> ${data[i].sex}</td> 
                            <td> ${data[i].time_input.slice(0,5)}</td>
                            <td> <button class="btn3" onclick="view_service('${data[i].customer_identifier}')"> view </button> </td>
                            <td> ${enter} </td> 
                            
                        </tr>`;
                }
            } else {
                customer_info_wait = "";
            }
            document.getElementById("customer_info_wait").innerHTML = customer_info_wait;
            start_update_wait();
        });
}


function getCustomer_info_wait_loading() {
    customer_info_wait = `                
        <tr>
            <th class = "mobi">Mã khách hàng</th>
            <th class = "mobi">Vip</th>
            <th>Họ Tên</th>
            <th class = "mobi">Ngày sinh</th>
            <th  class = "mobi">Giới tính</th>
            <th >Chờ khám </th>
            <th>Dịch vụ</th>
            <th>Tùy chỉnh</th>
        </tr>`;

    const endpoint1 = `${server_get}get_clinic_customer_info_wait${token_login}clinic=${clinic_identifier}`;
    // console.log("___________________________");
    // console.log(endpoint1);
    fetch(endpoint1)
        .then((response) => response.json())
        .then((data) => {
            // console.log(data);
            if (data != null) {
                for (i = 0; i < data.length; i++) {
                    if (data[i].status == "Đã khám") {
                        enter = "Đã khám"
                    } else if (data[i].status == "Đang khám") {
                        enter = "Đang khám"
                    } else {
                        enter = `<button class="btn3" onclick="enterClinic_wait('${data[i].customer_identifier}')"> Vào khám</button>`;
                    }
                    customer_info_wait = customer_info_wait + ` 
                        <tr>
                            <td class = "mobi"> ${data[i].customer_identifier} </td> 
                            <td class = "mobi" > ${data[i].vip}</td>
                            <td> ${data[i].name} </td> 
                            <td  class = "mobi"> ${data[i].birthday} </td> 
                            <td  class = "mobi"> ${data[i].sex}</td> 
                            <td> ${data[i].time_input.slice(0,5)}</td>
                            <td> <button class="btn3" onclick="view_service('${data[i].customer_identifier}')"> view </button> </td>
                            <td> ${enter} </td> 
                            
                        </tr>`;
                }
            } else {
                customer_info_wait = "";
            }
            document.getElementById("customer_info_wait").innerHTML = customer_info_wait;
        });
}



function getCustomer_info_are() {
    customer_info_are = `                
        <tr>
            <th class = "mobi">Mã khách hàng</th>
            <th class = "mobi" >Vip</th>
            <th>Họ Tên</th>
            <th class = "mobi">Ngày sinh</th>
            <th class = "mobi">Giới tính</th>
            <th>Vào khám </th>
            <th>Dịch vụ </th>
            <th>Tùy chỉnh</th>
            <th class = "mobi">Dịch vụ khám</th>
        </tr>`;
    const endpoint3 = `${server_get}get_clinic_customer_info_are${token_login}clinic_identifier=${clinic_identifier}`;
    // console.log(endpoint3);
    fetch(endpoint3)
        .then((response) => response.json())
        .then((data) => {
            if (data != null) {
                // console.log(data);
                for (i = 0; i < data.length; i++) {
                    enter = `<button class="btn3" onclick="wasClinic('${data[i].customer_identifier}')"> Khám xong</button>`;

                    customer_info_are = customer_info_are + ` 
                        <tr>
                            <td class = "mobi"> ${data[i].customer_identifier} </td> 
                            <td class = "mobi">${data[i].vip}</td>
                            <td> ${data[i].name} </td> 
                            <td class = "mobi"> ${data[i].birthday} </td> 
                            <td class = "mobi"> ${data[i].sex}</td> 
                            <td> ${data[i].time_input.slice(0,5)}</td>
                            <td> <button class="btn3" onclick="view_service('${data[i].customer_identifier}')"> view </button> </td>
                            <td> ${enter} </td> 
                            <td class = "mobi">  <a class="btn3"  onclick = "settingClinic('${data[i].customer_identifier}', '${data[i].name}', '${data[i].vip}')"    style="background-color: rgb(225 190 39)"  target="_blank">Chỉnh sửa</a> </td>
                        </tr>`;
                }
            } else {
                customer_info_are = "";
            }

            document.getElementById("customer_info_are").innerHTML = customer_info_are;
        });
}

function settingClinic(identifier, name, vip) {

    document.getElementById("customer_id").value = identifier;
    document.getElementById("customer_name").value = name;
    document.getElementById("vip_setting").value = vip;
    document.getElementById("schedule_setting").value = today_string;
    document.getElementById("status_setting").value = "Chưa khám";
    document.getElementById("location_setting").value = 0;
    schedule = today_string;

    const endpoint = `${server_get}get_data_medical_diary_date${token_login}identifier=${identifier}&date=${schedule}`;
    // console.log(endpoint);
    fetch(endpoint)
        .then((response) => response.json())
        .then((data) => {

            date_medical_diary_value = "";

            if (data != "") {
                for (i = 0; i < data.length; i++) {
                    status_service = data[i].status;
                    // if(status_service == null || status_service =="nan" || status_service =="null") {
                    //     status_service = "";
                    // }

                    clinic_service = data[i].clinic;;
                    // if( clinic_service == null || clinic_service == "null" || clinic_service =="nan") {
                    //     clinic_service = "";
                    // }

                    proviso_service = data[i].proviso;;
                    // if( proviso_service == null || proviso_service == "null" || proviso_service =="nan") {
                    //     proviso_service = "";
                    // }

                    together_service = data[i].together;;
                    // if( together_service == null || together_service == "null" || together_service =="nan") {
                    //     together_service = "";
                    // }



                    date_medical_diary_value = date_medical_diary_value + `
                                <tr>
                                    <td>${data[i].vip}</td>
                                    <td>${data[i].service}</td>
                                    <td>${data[i].service_name}</td>
                                    <td>${status_service}</td>
                                    <td>${clinic_service}</td>
                                    <td>${data[i].intendTime}</td>
                                    <td>${data[i].location}</td>
                                    <td>${proviso_service}</td>
                                    <td>${together_service}</td>
                                    <td> 
                                    <button onclick="update_diary_service('${data[i].stt}', '${data[i].vip}', '${data[i].service}', '${data[i].service_name}', '${schedule}', '${status_service}', '${clinic_service}', '${data[i].intendTime}', '${data[i].location}',  '${data[i].proviso}', '${data[i].together}')">Cập nhật</button>  
                                    <button onclick="delete_diary_service('${data[i].stt}')"> Xóa </button>
                                </td>
                                </tr>` ;
                }
                document.getElementById("service_id_info").innerHTML = `
                            <table style=" padding:15px;">
                                <tr>
                                    <th> Vip </th>
                                    <th>Mã dịch vụ</th>
                                    <th>Dịch vụ</th>
                                    <th>Trạng thái</th>
                                    <th>Phòng khám</th>
                                    <th>Thời gian </th>
                                    <th> Ưu tiên </th>
                                    <th style="width: 130px"> Điều kiện </td>
                                    <th> Khám cùng</th>
                                    <th> Tùy chỉnh</th>
                                </tr>
                                ${date_medical_diary_value}
                            </table>
                    `
                    ;
            }

        });
    document.getElementById("div_service_type_info").style.display = "block";
    // console.log(today_string_new);
    document.getElementById("div_service_type_info").style.display = "block";
}

function openDate_medical_diary(schedule) {

    identifier = document.getElementById("customer_id").value;
    const endpoint = `${server_get}get_data_medical_diary_date${token_login}identifier=${identifier}&date=${schedule}`;
    // console.log(endpoint);
    fetch(endpoint)
        .then((response) => response.json())
        .then((data) => {

            date_medical_diary_value = "";

            if (data != "") {
                for (i = 0; i < data.length; i++) {
                    status_service = data[i].status;
                    // if(status_service == null || status_service =="nan" || status_service =="null") {
                    //     status_service = "";
                    // }

                    clinic_service = data[i].clinic;;
                    // if( clinic_service == null || clinic_service == "null" || clinic_service =="nan") {
                    //     clinic_service = "";
                    // }

                    proviso_service = data[i].proviso;;
                    // if( proviso_service == null || proviso_service == "null" || proviso_service =="nan") {
                    //     proviso_service = "";
                    // }

                    together_service = data[i].together;;
                    // if( together_service == null || together_service == "null" || together_service =="nan") {
                    //     together_service = "";
                    // }



                    date_medical_diary_value = date_medical_diary_value + `
                                <tr>
                                    <td>${data[i].vip}</td>
                                    <td>${data[i].service}</td>
                                    <td>${data[i].service_name}</td>
                                    <td>${status_service}</td>
                                    <td>${clinic_service}</td>
                                    <td>${data[i].intendTime}</td>
                                    <td>${data[i].location}</td>
                                    <td>${proviso_service}</td>
                                    <td>${together_service}</td>
                                    <td> 
                                    <button onclick="update_diary_service('${data[i].stt}', '${data[i].vip}', '${data[i].service}', '${data[i].service_name}', '${schedule}', '${status_service}', '${clinic_service}', '${data[i].intendTime}', '${data[i].location}',  '${data[i].proviso}', '${data[i].together}')">Cập nhật</button>  
                                    <button onclick="delete_diary_service('${data[i].stt}')"> Xóa </button>
                                </td>
                                </tr>` ;
                }
                document.getElementById("service_id_info").innerHTML = `
                            <table style=" padding:15px;">
                                <tr>
                                    <th> Vip </th>
                                    <th>Mã dịch vụ</th>
                                    <th>Dịch vụ</th>
                                    <th>Trạng thái</th>
                                    <th>Phòng khám</th>
                                    <th>Thời gian </th>
                                    <th> Ưu tiên </th>
                                    <th style="width: 130px"> Điều kiện </td>
                                    <th> Khám cùng</th>
                                    <th> Tùy chỉnh</th>
                                </tr>
                                ${date_medical_diary_value}
                            </table>
                    `
                    ;
            }

        });
}


function delete_diary_service(stt) {
    if (confirm("Bạn có chắc chắn muốn xóa không?")) {
        schedule = document.getElementById("schedule_setting").value;
        const endpoint = `${server}delete_medical_diary_service${token_login}stt=${stt}`;
        // console.log(endpoint);
        fetch(endpoint)
            .then((response) => response.json())
            .then((data) => {
                alert(data);
                openDate_medical_diary(schedule);
            }).catch(() => {
                alert("Erro2");
            });
    }
}

function update_diary_service(stt, vip, service_id, service_name, schedule, status, clinic, intendTime, data_location, proviso, together) {
    // console.log("update_service");
    document.getElementById("vip_setting").value = vip;
    document.getElementById("service_id_setting").value = service_id;
    document.getElementById("service_name_setting").value = service_name;
    document.getElementById("schedule_setting").value = schedule;
    document.getElementById("status_setting").value = status;
    document.getElementById("status_setting").clinic = clinic;

    document.getElementById("intendTime_setting").value = intendTime;
    document.getElementById("location_setting").value = data_location;
    document.getElementById("proviso_setting").value = proviso;
    document.getElementById("together_setting").value = together;
    document.getElementById("update_diary_service").setAttribute("onclick", `update_diary_service2(${stt})`);
}

function add_diary_service() {
    if (confirm("Bạn có chắn chắn muốn thêm dịch vụ khám?")) {
        schedule = document.getElementById("schedule_setting").value;
        customer_id = document.getElementById("customer_id").value;
        vip = document.getElementById("vip_setting").value;
        service_id = document.getElementById("service_id_setting").value;
        service_name = document.getElementById("service_name_setting").value;
        status = document.getElementById("status_setting").value;
        clinic = document.getElementById("status_setting").clinic;

        intendTime = document.getElementById("intendTime_setting").value;
        location_setting = document.getElementById("location_setting").value;
        proviso = document.getElementById("proviso_setting").value;
        together = document.getElementById("together_setting").value;

        const endpoint = `${server}add_diary_service${token_login}customer_identifier=${customer_id}&vip=${vip}&service=${service_id}&service_name=${service_name}&schedule=${schedule}&status=${status}&clinic=${clinic}&intendTime=${intendTime}&location_setting=${location_setting}&proviso=${proviso}&together=${together}`;
        // console.log(endpoint);
        fetch(endpoint)
            .then((response) => response.json())
            .then((data) => {
                // console.log(data);
                alert(data);
                //updateClinic_type(identifier_setting, identifier_name_setting)
                openDate_medical_diary(schedule);
            }).catch(() => {
                alert("Erro2")
            });
    }
}



function close_info_service_all() {
    document.getElementById("div_service_type_info").style.display = "none";

}


function update_diary_service2(stt) {
    customer_id = document.getElementById("customer_id").value;
    vip = document.getElementById("vip_setting").value;
    service_id = document.getElementById("service_id_setting").value;
    service_name = document.getElementById("service_name_setting").value;
    schedule = document.getElementById("schedule_setting").value;
    status = document.getElementById("status_setting").value;
    clinic = document.getElementById("status_setting").clinic;

    intendTime = document.getElementById("intendTime_setting").value;
    location_setting = document.getElementById("location_setting").value;
    proviso = document.getElementById("proviso_setting").value;
    together = document.getElementById("together_setting").value;

    const endpoint = `${server}update_diary_service2${token_login}stt=${stt}&customer_identifier=${customer_id}&vip=${vip}&service=${service_id}&service_name=${service_name}&schedule=${schedule}&status=${status}&clinic=${clinic}&intendTime=${intendTime}&location_setting=${location_setting}&proviso=${proviso}&together=${together}`;
    // console.log(endpoint);
    fetch(endpoint)
        .then((response) => response.json())
        .then((data) => {
            // console.log(data);
            //updateClinic_type(identifier_setting, identifier_name_setting)
            openDate_medical_diary(schedule);
            alert(data);
        }).catch(() => {
            alert("Erro2");
        });
}



function wasClinic(customer_identifier) {
    const endpoint = `${server}medical_diary_was_clinic${token_login}customer_identifier=${customer_identifier}&clinic=${clinic_identifier}`;
    // console.log(endpoint);
    fetch(endpoint)
        .then((response) => response.json())
        .then((data) => {
            // console.log(data);
            getCustomer_info_are();
            getCustomer_info();
            alert(data)
            // location.reload();
        });

}

function enterClinic(stt) {
    const endpoint = `${server}medical_diary_enter_clinic${token_login}stt=${stt}&clinic_identifier=${clinic_identifier}`;
    // console.log(endpoint);
    fetch(endpoint)
        .then((response) => response.json())
        .then((data) => {
            // console.log(data);
            location.reload();
        });
}

function enterClinic_wait(customer_identifier) {
    const endpoint = `${server}medical_diary_enter_clinic2${token_login}customer_identifier=${customer_identifier}&clinic_identifier=${clinic_identifier}`;
    // console.log(endpoint);
    fetch(endpoint)
        .then((response) => response.json())
        .then((data) => {
            // console.log(data);
            getCustomer_info_wait();
            getCustomer_info_are();
            alert(data);
        }).catch(() => {
            alert("Erro");
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
            getCustomer_info();
            getCustomer_info_are();
            getCustomer_info_wait();

            document.getElementById("clinic_info_update").innerHTML = `
            <li class="menu_item"  style="background-color: #b8b8b8;" > <a href="https://phanluong.t-matsuoka.com/clinic/clinic.html"> ${data[0].identifier} - ${data[0].name} </a></li>
            <li class="menu_item"   > <a> Ngày: ${today_string_new} </a></li>
            <li class="menu_item"   > 
                <a> 
                    <input class="input__style" style="font-size: 14px; margin-bottom:10px; width:180px; background:white" type="text" value="${data[0].doctor}"  id="doctor_set" >      
                    BS : <button class="btn3" style="padding:3px 13px" onclick="update_doctor_clinic()">update</button>
                </a>
            </li>
            <li class="menu_item"   >  <a id="clinic_status">TT :  <button class="btn3" onclick="updateClinic_stautus('${data[0].status}')" > ${data[0].status} </button>  </a></li>
            <li class="menu_item"  style="border-bottom: 2px solid white;" > <a> Ghi chú: ${data[0].note} </a></li>
            `;
        });
}

function update_doctor_clinic() {
    doctor = document.getElementById("doctor_set").value;
    const endpoint = `${server}update_doctor_clinic${token_login}clinic_identifier=${clinic_identifier}&doctor=${doctor}`;
    // console.log(endpoint);
    fetch(endpoint)
        .then((response) => response.json())
        .then((data) => {
            // console.log(data);
            alert(data);
        }).catch(() => {
            alert("Erro2");
        });

}


function update_intendTime() {
    var intendTime = document.getElementById("intendTime").value;
    const endpoint = `${server}setClinic_intendTime${token_login}acc=${clinic}&intendTime=${intendTime}`;
    // console.log(endpoint);
    fetch(endpoint)
        .then((response) => response.json())
        .then((data) => {
            // console.log(data);
            alert("Thành công!");
            location.reload();
        });
}

function update_resultTime() {
    var resultTime = document.getElementById("resultTime").value;
    const endpoint = `${server}setClinic_resultTime${token_login}acc=${clinic}&resultTime=${resultTime}`;
    // console.log(endpoint);
    fetch(endpoint)
        .then((response) => response.json())
        .then((data) => {
            // console.log(data);
            alert("Update thời gian khám thành công!");
            location.reload();
        });
}

function updateClinic_stautus(status) {
    if (status == "Hoạt động") {
        status = "Offline";
    } else {
        status = "Hoạt động";
    }

    const endpoint = `${server}setClinic_status${token_login}clinic=${clinic}&status=${status}`;
    // console.log(endpoint);
    fetch(endpoint)
        .then((response) => response.json())
        .then((data) => {
            document.getElementById("clinic_status").innerHTML = `TT:  <button class="btn3" onclick="updateClinic_stautus('${status}')" > ${status} </button>`;
            getCustomer_info();
            getCustomer_info_are();
            getCustomer_info_wait();
            alert(data);
        }).catch(() => {
            alert("Erro2");
        });
}


function display_menu() {
    if (document.getElementById("menu_all").style.display == "block") {
        document.getElementById("menu_all").style.display = "none";
    } else {
        document.getElementById("menu_all").style.display = "block";
    }
}


var update_wait;

function check_time_id() {
    const endpoint1 = `${server_get}get_timeid_clinic${token_login}clinic=${clinic_identifier}`;
    // console.log(endpoint1);
    fetch(endpoint1)
        .then((response) => response.json())
        .then((data) => {
            // console.log(data);
            data = Number(data);
            if(data > time_id) {
                time_id = data;
                getCustomer_info_wait_loading();
                // console.log("yes");
            } 
        });
}

function start_update_wait() {
    update_wait = setInterval(check_time_id, 15000);
}

function login() {

    if (localStorage['clinic'] == undefined) {
        window.location = "./login.html";
        // // console.log("test");
    } else {
        // console.log("token:"  + localStorage['clinic']);
        clinic = localStorage['clinic'];
        clinic_name = localStorage['clinic_name'];
        token_login = token_login + localStorage["token_login_clinic"] + "&";
        start_update_wait();
        // console.log(clinic_name);
        clinic_info();
        getService2();
    }
}

login();

function logout() {
	// updateClinic_stautus("Hoạt động");
    localStorage.removeItem('clinic');
    localStorage.removeItem('clinic_name');
    localStorage.removeItem('token_login_clinic');
    window.location = "./login.html";
}