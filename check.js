token_login = "?tokenlogin=";
server_get = "https://phanluong.t-matsuoka.com/ex/api/get/get_hospital.php/";
var service_add = [];

function up_shearch_service() {
	name = document.getElementById("service_name_setting").value;
	document.getElementById("service_stt").value = data_service_stt[name];
	document.getElementById("service_id").value =  data_service_id[name];
	document.getElementById("service_name_el").value =  data_service_name[name];
	document.getElementById("service_group").value = data_service_group_service[name];
	document.getElementById("service_retail_price").value =  data_service_retail_price[name];
	document.getElementById("service_list_price").value = data_service_list_price[name];
	document.getElementById("service_floor_price").value = data_service_floor_price[name];
	document.getElementById("service_cost_price").value = data_service_cost_price[name];
}


function add_service_new() {
    idd= document.getElementById("customer_idd").value;
    customer_name= document.getElementById("customer_name").value;
    customer_date = document.getElementById("customer_date").value ;
    customer_address = document.getElementById("customer_address").value;
    customer_phone = document.getElementById("customer_phone").value;
    customer_ck = document.getElementById("customer_ck").value;
    manager = document.getElementById("manager").value;
    time_input = document.getElementById("time_input").value;

	id = document.getElementById("service_id").value;
	name = document.getElementById("service_name_setting").value ;
	service_name = document.getElementById("service_name_el").value ;
	group_service = document.getElementById("service_group").value ;
	retail_price = document.getElementById("service_retail_price").value ;
	list_price = document.getElementById("service_list_price").value ;
	floor_price = document.getElementById("service_floor_price").value ;
	cost_price = document.getElementById("service_cost_price").value ;

	const endpoint = `${server_get}add_service_new_check${token_login}idd=${idd}&customer_name=${customer_name}&customer_date=${customer_date}&customer_address=${customer_address}&customer_phone=${customer_phone}&customer_ck=${customer_ck}&id=${id}&name=${name}&service_name=${service_name}&group_service=${group_service}&retail_price=${retail_price}&list_price=${list_price}&floor_price=${floor_price}&cost_price=${cost_price}&manager=${manager}&time_input=${time_input}`; 
    console.log(endpoint);
    fetch(endpoint)
        .then((response) => response.json())
        .then((data) => {
            loading_history_info_idd();
            alert("Thành công");
        });
}

function update_service(stt, id, name, service_name, group_service, retail_price, list_price, floor_price, cost_price) {
	document.getElementById("service_stt").value = stt;
	document.getElementById("service_id").value = id.trim();
	document.getElementById("service_name_el").value = service_name;
	document.getElementById("service_name_setting").value = name.trim();
	document.getElementById("service_group").value = group_service.trim();
	document.getElementById("service_retail_price").value = retail_price;
	document.getElementById("service_list_price").value = list_price;
	document.getElementById("service_floor_price").value = floor_price;
	document.getElementById("service_cost_price").value = cost_price;
}

function update_service_new() {
	stt = document.getElementById("service_stt").value;
	id = document.getElementById("service_id").value;
	name = document.getElementById("service_name_setting").value ;
	service_name = document.getElementById("service_name_el").value ;
	group_service = document.getElementById("service_group").value ;
	retail_price = document.getElementById("service_retail_price").value ;
	list_price = document.getElementById("service_list_price").value ;
	floor_price = document.getElementById("service_floor_price").value ;
    cost_price = document.getElementById("service_cost_price").value;

	const endpoint = `${server_get}update_service_new_check${token_login}stt=${stt}&id=${id}&name=${name}&service_name=${service_name}&group_service=${group_service}&retail_price=${retail_price}&list_price=${list_price}&floor_price=${floor_price}&cost_price=${cost_price}`; 
    console.log(endpoint);
    fetch(endpoint)
        .then((response) => response.json())
        .then((data) => {
            //console.log(data);
			// getService_info_view();
            loading_history_info_idd();
            alert("Thành công");
        });
}

function delete_service_check(stt) {
    if(confirm("Bạn có chắc chắn muốn xóa không?")) {
		const endpoint = `${server_get}delete_service_check${token_login}stt=${stt}`; 
		console.log(endpoint);
		fetch(endpoint)
			.then((response) => response.json())
			.then((data) => {
                loading_history_info_idd();
				alert("Thành công");
			});
	}
}


function close_info_service() {
    document.getElementById("div_service_type_info").style.display = "none";
}

function getHistory_info_view_idd(idd, name, bir, address, phone, ck, day, manager) {
    document.getElementById("time_input").value = day;
    document.getElementById("manager").value = manager;
    document.getElementById("customer_idd").value = idd;
    document.getElementById("customer_name").value = name;
    document.getElementById("customer_date").value = bir;
    document.getElementById("customer_address").value = address;
    document.getElementById("customer_phone").value = phone;
    document.getElementById("customer_ck").value = ck;
    document.getElementById("div_service_type_info").style.display = "block";
    history_info = `
    <tr style="display:none">
        <td>Họ và tên</td>
        <td>${document.getElementById("customer_name").value}</td>
        <td></td>
    </tr>
    <tr style="display:none">
        <td>Ngày sinh</td>
        <td>${document.getElementById("customer_date").value}</td>
        <td></td>
    </tr>
    <tr style="display:none">
        <td>Địa chỉ</td>
        <td>${document.getElementById("customer_address").value}</td>
        <td></td>
    </tr>

    <tr style="display:none">
        <td>Số điện thoại</td>
        <td>${document.getElementById("customer_phone").value}</td>
        <td></td>
    </tr>
    <tr style="display:none">
    </tr>
    <tr style="display:none">
    </tr>
    `


    history_info += `                
        <tr>
            <th>Mã dịch vụ</th>
            <th>Tên dịch vụ</th>
            <th>Service name</th>
            <th>Nhóm dịch vụ</th>
            <th>Giá dịch vụ lẻ</th>
            <th>Giá gói</th>
            <th>Giá sàn</th>
            <th>Giá vốn</th>
            <td>Chỉnh sửa</td>
        </tr>`;
    const endpoint = `${server_get}get_history_info_idd${token_login}idd=${idd}`; 
    console.log(endpoint);
    fetch(endpoint)
        .then((response) => response.json())
        .then((data) => {
            //console.log(data);
            sum_dv = 0;
            sun_retail_price = 0;
            sum_list_price = 0;
            sum_floor_price = 0;
            sum_cost_price = 0;

            for(i=0; i < data.length; i++) 
            {
                sum_dv ++;
                sun_retail_price += Number( data[i].retail_price) ;
                sum_list_price   += Number( data[i].list_price);
                sum_floor_price  += Number( data[i].floor_price);
                sum_cost_price   += Number( data[i].cost_price);

                history_info = history_info + ` 
                    <tr>
                        <td>${data[i].service_id}</td>
                        <td>${data[i].service_name}</td>
                        <td>${data[i].service_name_el}</td>
                        <td>${data[i].service_group}</td>
                        <td>${Number( data[i].retail_price).toLocaleString()}</td>
                        <td>${Number( data[i].list_price).toLocaleString()}</td>
                        <td>${Number( data[i].floor_price).toLocaleString()}</td>
                        <td>${Number( data[i].cost_price).toLocaleString()}</td>
                        <td> 
                            <button onclick="update_service(${data[i].stt}, '${data[i].service_id}','${data[i].service_name}', '${data[i].service_name_el}', ' ${data[i].service_group }', ${data[i].retail_price}, ${data[i].list_price},  ${data[i].floor_price}, ${data[i].cost_price})">Cập nhật</button> 
                            <button onclick="delete_service_check(${data[i].stt})">Xóa</button>	
                        </td>

                    </tr>`;
            }
            history_info += `
            <tr>
                <th>Tổng dịch vụ:</th>
                <th>${sum_dv}</th>
                <th></th>
                <th>Tổng tiền:</th>
                <th>${sun_retail_price.toLocaleString()}</th>
                <th>${sum_list_price.toLocaleString()}</th>
                <th>${sum_floor_price.toLocaleString()}</th>
                <th>${sum_cost_price.toLocaleString()}</th>
                <th></th>
            </tr>
        
            <tr>
                <th></th>
                <th></th>
                <th></th>
                <th>Chiết khấu</th>
                <th>${ck}%</th>
                <th>${ck}%</th>
                <th>${ck}%</th>
                <th></th>
                <th></th>
            </tr>
            <tr>
                <th></th>
                <th></th>
                <th></th>
                <th>Sau chiết khấu:</th>
                <th>${(sun_retail_price*(100-ck)/100).toLocaleString()}</th>
                <th>${(sum_list_price*(100-ck)/100).toLocaleString()}</th>
                <th>${(sum_floor_price*(100-ck)/100).toLocaleString()}</th>
                <th>${sum_cost_price.toLocaleString()}</th>
                <th></th>
            </tr>
            `
            document.getElementById("history_info_idd").innerHTML = history_info;
        });
}

function update_customer_check() {
    idd= document.getElementById("customer_idd").value;
    customer_name= document.getElementById("customer_name").value;
    customer_date = document.getElementById("customer_date").value ;
    customer_address = document.getElementById("customer_address").value;
    customer_phone = document.getElementById("customer_phone").value;
    customer_ck = document.getElementById("customer_ck").value;

    const endpoint = `${server_get}update_customer_check${token_login}idd=${idd}&customer_name=${customer_name}&customer_date=${customer_date}&customer_address=${customer_address}&customer_phone=${customer_phone}&customer_ck=${customer_ck}`; 
    console.log(endpoint);
    fetch(endpoint)
        .then((response) => response.json())
        .then((data) => {
            loading_history_info_idd();
            alert(data);
        });

}


function loading_history_info_idd() {
    idd = document.getElementById("customer_idd").value;
    ck = document.getElementById("customer_ck").value;
    if(idd) {

        history_info = `                
        <tr>
            <th>Mã dịch vụ</th>
            <th>Tên dịch vụ</th>
            <th>Service name</th>
            <th>Nhóm dịch vụ</th>
            <th>Giá dịch vụ lẻ</th>
            <th>Giá gói</th>
            <th>Giá sàn</th>
            <th>Giá vốn</th>
            <td>Chỉnh sửa</td>
        </tr>`;
        const endpoint = `${server_get}get_history_info_idd${token_login}idd=${idd}`; 
        console.log(endpoint);
        fetch(endpoint)
            .then((response) => response.json())
            .then((data) => {
            //console.log(data);
            sum_dv = 0;
            sun_retail_price = 0;
            sum_list_price = 0;
            sum_floor_price = 0;
            sum_cost_price = 0;

            for(i=0; i < data.length; i++) 
            {
                sum_dv ++;
                sun_retail_price += Number( data[i].retail_price) ;
                sum_list_price   += Number( data[i].list_price);
                sum_floor_price  += Number( data[i].floor_price);
                sum_cost_price   += Number( data[i].cost_price);

                history_info = history_info + ` 
                    <tr>
                        <td>${data[i].service_id}</td>
                        <td>${data[i].service_name}</td>
                        <td>${data[i].service_name_el}</td>
                        <td>${data[i].service_group}</td>
                        <td>${Number( data[i].retail_price).toLocaleString()}</td>
                        <td>${Number( data[i].list_price).toLocaleString()}</td>
                        <td>${Number( data[i].floor_price).toLocaleString()}</td>
                        <td>${Number( data[i].cost_price).toLocaleString()}</td>
                        <td> 
                            <button onclick="update_service(${data[i].stt}, '${data[i].service_id}','${data[i].service_name}', '${data[i].service_name_el}', ' ${data[i].service_group }', ${data[i].retail_price}, ${data[i].list_price},  ${data[i].floor_price}, ${data[i].cost_price})">Cập nhật</button> 
                            <button onclick="delete_service_check(${data[i].stt})">Xóa</button>	
                        </td>

                    </tr>`;
            }
            history_info += `
            <tr>
                <th>Tổng dịch vụ:</th>
                <th>${sum_dv}</th>
                <th></th>
                <th>Tổng tiền:</th>
                <th>${sun_retail_price.toLocaleString()}</th>
                <th>${sum_list_price.toLocaleString()}</th>
                <th>${sum_floor_price.toLocaleString()}</th>
                <th>${sum_cost_price.toLocaleString()}</th>
                <th></th>
            </tr>
        
            <tr>
                <th></th>
                <th></th>
                <th></th>
                <th>Chiết khấu</th>
                <th>${ck}%</th>
                <th>${ck}%</th>
                <th>${ck}%</th>
                <th></th>
                <th></th>
            </tr>
            <tr>
                <th></th>
                <th></th>
                <th></th>
                <th>Sau chiết khấu:</th>
                <th>${(sun_retail_price*(100-ck)/100).toLocaleString()}</th>
                <th>${(sum_list_price*(100-ck)/100).toLocaleString()}</th>
                <th>${(sum_floor_price*(100-ck)/100).toLocaleString()}</th>
                <th>${sum_cost_price.toLocaleString()}</th>
                <th></th>
            </tr>
            `
            document.getElementById("history_info_idd").innerHTML = history_info;
        });
    }
    

}


function getHistory_info_view() {

    history_info = `                
        <tr>
            <th style="display:none">Số thời gian</th>
            <th>Thời gian</th>
            <th>Tên</th>
            <th>Ngày sinh </th>
            <th>Địa chỉ</th>
            <th>Liên hệ</th>
            <th>Triết khấu</th>
            <th>Tài khoản xuất </th>
            <th>Chỉnh sửa</th>
        </tr>`;
    const endpoint = `${server_get}get_history_info${token_login}`; 
    console.log(endpoint);
    fetch(endpoint)
        .then((response) => response.json())
        .then((data) => {
            //console.log(data);
            for(i=0; i < data.length; i++) 
            {
                history_info = history_info + ` 
                    <tr>
                        <td  style="display:none"> ${data[i].idd} </td> 
                        <td> ${data[i].day_add} </td> 
                        <td> ${data[i].name} </td> 
                        <td> ${data[i].birthday}</td>
                        <td> ${data[i].address}</td>
                        <td> ${data[i].phone}</td>
                        <td> ${data[i].ck} %</td>
                        <td> ${data[i].manager}</td>
                        <td> 
							<button onclick="getHistory_info_view_idd(${data[i].idd}, '${data[i].name}','${data[i].birthday}',  '${data[i].address}',  '${data[i].phone}',  '${data[i].ck}',  '${data[i].time_input}', '${data[i].manager}')">Xem</button>	
                            <button onclick="delete_idd_admin(${data[i].idd})"> Xóa </button>
                            <button onclick="double_customer(${data[i].idd})"> Nhân bản </button>
						</td> 

                    </tr>`;
            }
            document.getElementById("history_info").innerHTML = history_info;
        }).catch(()=> {
            document.getElementById("history_info").innerHTML = "";
        });
}

function double_customer(idd) {
    if (confirm("Bạn có chắc chắn muốn nhân bản không?")) {
        time_idd = Date.now();
        const endpoint = `${server_get}double_customer${token_login}idd=${idd}&time_idd=${time_idd}`; 
        console.log(endpoint);
        fetch(endpoint)
            .then((response) => response.json())
            .then((data) => {
                console.log(data);
                getHistory_info_view();
            })
    }
}


function delete_idd_admin(idd) {
    if (confirm("Bạn có chắc chắn muốn xóa không?")) {
        const endpoint = `${server_get}delete_idd_admin${token_login}idd=${idd}`; 
        console.log(endpoint);
        fetch(endpoint)
            .then((response) => response.json())
            .then((data) => {
                console.log(data);
                getHistory_info_view();
            })
    }
}


function login()
{
    if(localStorage['ex_admin'] == undefined)
    {
        window.location="./login.html";
    } else {
        //console.log("token:"  + localStorage['ex_admin']);
        acc_admin = localStorage['ex_admin'];
        token_login = token_login + localStorage["token_login"] + "&";
        getHistory_info_view();
        getService_info_view();

    } 
}

function logout()
{
    localStorage.removeItem('ex_admin');
    localStorage.removeItem('token_login');
    window.location="./login.html";
}  


login();



function display_menu() {
    if(document.getElementById("menu_all").style.display == "block") {
        document.getElementById("menu_all").style.display = "none";
    } else {
        document.getElementById("menu_all").style.display = "block";
    }
}



// Tìm kiếm


//<![CDATA[
//global variables
var keyword = $("#service_name_setting");
var filterSelect = $("#filter-select");
var keywordHref = $("#keyword-button").attr("href");
var keywordVal = "";
/*var filters = {
    "aardvark" : { sprite : "" }
}
var filterArray = Object.keys(filters);*/


//Events

keyword.on("focus", function(e){
    e.preventDefault();
    if (keywordVal !== "" && keydownTarget !== 9 && keydownTarget !== 16 && keydownTarget !== 38 && keydownTarget !== 40 && newFilter.length > 1) {
    fillSelect();
    }
});
keyword.on("keyup", function(e) {
    keywordVal = $(this).val();
    keywordVal = keywordVal.toLowerCase();

    //console.log(keywordVal);
    keydownTarget = e.which;
    var ignoreKeys = e.which !== 9 && e.which !== 16 && e.which !== 38 && e.which !== 40;
    if (keywordVal !== "" && ignoreKeys) { 
        newFilter = filterArray.filter(isResult);
    if (newFilter.length === 0) {
        removeListBlur();
        return false;
    }
        //keyword.val(newFilter[0]);
        //keyword[0].setSelectionRange(selectRange, newFilter[0].length);
    }
    
    if (e.which !== 9 && ignoreKeys) {
    fillSelect();
    tabIndex = -1;
    if (newFilter.length === 0) {
        removeListBlur();
        return false;
    }
    
    }
});

keyword.on("keydown", function(e) {
    if (keywordVal !== "") {
    if (e.which === 9) {
    e.preventDefault();
        keydownTarget = e.which;
    if (!e.shiftKey) {
        cycleSelectList("next");
    }
    if (e.shiftKey) { 
        cycleSelectList("previous");
    }
    }
    if (e.which === 38 || e.which === 40) {
        e.preventDefault();
        keydownTarget = e.which;
    }
    if (e.which === 38) {
        cycleSelectList("previous");
    }
    else if (e.which === 40) {
        cycleSelectList("next");
    }
    }
    if (e.which === 13) {
    $("#keyword-button").click()
    }
});
/*use mousedown instead of click because the keyword blur event is firing before this call back can occur*/
$("#filter-select").on("mousedown",".filter-select-list", function(e){
    e.preventDefault();
    var $this = $(this);
    var currentIndex = $this.index();
    tabIndex = currentIndex;
    keydownTarget = 9;
    cycleSelectList("none");
});
keyword.on('blur', removeListBlur);
//helper functions
function isResult(val) {
        //return val.indexOf(keywordVal.toLowerCase()) === 0 
        val = val.toLowerCase()
        return val.indexOf(keywordVal) === 0 

}
function removeListBlur() {
    if (filterSelect.has("li").length) {
    filterSelect.addClass("no-value").children("li").remove();
    }
}
function cycleSelectList(direction) {
    var newList = filterSelect.find("li");
    if (direction === "previous") {
        if (tabIndex <= 0) {
        tabIndex = newList.length;
        }
        tabIndex--;
    }
    else if (direction === "next") {
        tabIndex++;
        if (tabIndex === newList.length) {
        
        tabIndex = 0;
        }
    }
    newList.eq(tabIndex).addClass("list-highlight");
    keyword.val(newList.eq(tabIndex).attr("data-value"));
    newList.not(newList.eq(tabIndex)).removeClass("list-highlight");
    keyword.focus();
    // function
    up_shearch_service();
}
function fillSelect() {
    filterSelect.children("li").remove();
    //filterSelect.attr("size",newFilter.length)
    if (keywordVal !== "") {
    filterSelect.removeClass("no-value");
    for (var i = 0; i < newFilter.length; i++) {
    filterSelect.append("<li class='filter-select-list' data-value='" + newFilter[i] + "'>" + newFilter[i] + "</li>");
    //filters[i].sprite;
    }
    }
    else {
        filterSelect.addClass("no-value");
    }
}
//for demo purposes only
$("#keyword-button").on("click", fillHref)
function fillHref() {
    var newHrefString = keywordHref + keyword.val().replace(/\s+/g, '+');
    var newHref = $("#keyword-button").attr("href", newHrefString);
}


var filterArray = [];
var newFilter = [];
var tabIndex = -1;
data_service_stt = [];
data_service_id = [];
data_service_name = [];
data_service_group_service = [];
data_service_retail_price = [];
data_service_list_price = [];
data_service_floor_price = [];
data_service_cost_price = [];


function getService_info_view() { 
    const endpoint = `${server_get}get_service${token_login}`; 
    //console.log(endpoint);
    fetch(endpoint)
        .then((response) => response.json())
        .then((data) => {
            //console.log(data);
            for(i=0; i < data.length; i++) 
            {
				filterArray.push(data[i].name); 
                data_service_stt[data[i].name] = data[i].stt;
                data_service_id[data[i].name] = data[i].identifier;
                data_service_name[data[i].name] = data[i].service_name;
                data_service_group_service[data[i].name] = data[i].group_service;
                data_service_retail_price[data[i].name] = data[i].retail_price;
                data_service_list_price[data[i].name] = data[i].list_price;
                data_service_floor_price[data[i].name] = data[i].floor_price;
                data_service_cost_price[data[i].name] = data[i].cost_price;
            }
        });
}

function tableToCSV() {
    name = document.getElementById("customer_name").value;
    bir = document.getElementById("customer_date").value;
    phone = document.getElementById("customer_address").value;
    address = document.getElementById("customer_phone").value;
    ck = document.getElementById("customer_ck").value;

    if(name && bir && phone && address && ck ) {
        // Variable to store the final csv data
        var csv_data = [];

        // Get each row data
        var rows = document.querySelectorAll('#history_info_idd tr');
        for (var i = 0; i < rows.length; i++) {

            // Get each column data
            var cols = rows[i].querySelectorAll('td,th');

            // Stores each csv row data
            var csvrow = [];
            for (var j = 0; j < cols.length - 1 ; j++) {

                // Get the text data of each cell
                // of a row and push it to csvrow
                csvrow.push((cols[j].innerHTML).replace(/,/g,''));
                console.log((cols[j].innerHTML).replace(/,/g,''));
            }

            // Combine each column value with comma
            csv_data.push(csvrow.join(","));
        }

        // Combine each row data with new line character
        csv_data = csv_data.join('\n');

        //console.log(csv_data);
        // Call this function to download csv file
        downloadCSVFile(csv_data);
    } else {
        alert("Vui lòng nhập đủ thông tin!");
    }

}

function downloadCSVFile(csv_data) {
    // save_excel() ;

	// Create CSV file object and feed
	// our csv_data into it
	//console.log(csv_data);

	var csv_data = "\ufeff"+csv_data;

	CSVFile = new Blob([csv_data], {
		type: "text/csv;charset=utf-8"
	});

	// Create to temporary link to initiate
	// download process
	var temp_link = document.createElement('a');

	// Download csv file
	temp_link.download = "service.csv";
	var url = window.URL.createObjectURL(CSVFile);
	temp_link.href = url;

	// This link should not be displayed
	temp_link.style.display = "none";
	document.body.appendChild(temp_link);

	// Automatically click the link to
	// trigger download
	temp_link.click();
	document.body.removeChild(temp_link);
}