/**
 * 网络请求(全局使用此方法进行请求接口)
 */
var request = {
	post(link, data, callback = ""){
		$.ajax({
			url: url(link),
			type: 'post',
			dataTyle: 'json',
			contentType:"application/json;charset=utf-8",
			data: JSON.stringify(data),
			success:function(res) {
				if (res.status === 'login') {
					location.href = url('login/index');
				} else {
					if (callback != "") callback(res)
				}
			},
			error:function(res) {
				res.status  = 'error';
				res.message = res.statusText;
				if (callback != "") callback(res);
			}
		})
	},
}

/**
 * 链接(设置/获取参数)
 */
var locationUrl = {
	// 获取
	get(name) {
	    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i"); 
	    var r = window.location.search.substr(1).match(reg); 
	    if (r != null) return unescape(r[2]); 
	    return ""; 
	},
	// 设置
	set(name, value){
		var loadUrl = location.href;
		var arrUrl  = loadUrl.split('#');
		var url     = arrUrl[0];
		var pattern = name + '=([^&]*)';
	    var replaceText = name+'='+value; 
	    var newUrl = "";
	    if (url.match(pattern)) {
	        var tmp = '/(' + name + '=)([^&]*)/gi';
	        newUrl = url.replace(eval(tmp),replaceText);
	    } else { 
	        if (url.match('[\?]')) { 
	            newUrl = url + '&' + replaceText; 
	        } else {
	            newUrl =  url +'?' + replaceText; 
	        } 
	    }
	    location.href = newUrl+window.location.hash
	}
}

/**
 * 数组转树形数组/树形字符串
 */
var tree = {
	// 树形数组
	convert(arr) {
	    let result = []
	    if (!Array.isArray(arr)) {
	        return result
	    }
	    arr.forEach(item => {
	        delete item.children;
	    });
	    let map = {};
	    arr.forEach(item => {
	        map[item.id] = item;
	    });
	    arr.forEach(item => {
	        let parent = map[item.pid];
	        if(parent) {
	            (parent.children || (parent.children = [])).push(item);
	        } else {
	            result.push(item);
	        }
	    });
	    result = result.filter(ele => ele.pid == 0);
	    return result;
	},
	// 树形字符串
	convertString(arr, pid = 0, format = "└", list = []) {
		arr.forEach( function(v, k) {
			if (v['pid'] == pid) {
				if (pid != 0) {
					v['treeString'] = format;
				} else {
					v['treeString'] = '';
				}
				list.push(v)
				tree.convertString(arr, v['id'], "<span class='el-tree-title'></span>"+format, list);
			}
		});
		return list.length === 0 ? arr : list;
	},
}

/**
 * 公共函数
 */
var common = {
    // 随机字符串
	id(len = 16) {
		var chars = 'ABCDEFGHJKMNPQRSTWXYZabcdefhijkmnprstwxyz2345678';
		var id = '';
		for (i = 0; i < len; i++) {
			id += chars.charAt(Math.floor(Math.random() * chars.length));
		}
		return id;
	},
    // 	json字符串转json对象
	parseJson(jsonStr){
        return JSON.parse(jsonStr, (k, v) => {
            try{
                if (eval(v) instanceof RegExp) {
                    return eval(v);
                }
            }catch(e){
                // nothing
            }
            return v;
        });
    },
    // json对象转json字符串
    stringifyJson(json){
        return JSON.stringify(json, (k, v) => {
            if(v instanceof RegExp){
                return v.toString();
            }
            return v;
        });
    },
	// 二维数组根据某字段返回一维数组
	arrayColumn(arr, name) {
		let val = [];
		arr.forEach(function(item,index) {
		    val.push(item[name]);
		})
		return val;
	},
	// 二维数组根据某元素返回当前下标
	arrayIndex(arr, value, field = "id"){
	    let index = -1;
	    arr.forEach(function(val, key) {
	        if (val[field] == value) {
	            index = key;
	        }
	    });
	    return index;
	},
	// 二维数组根据多字段排序
    arraySort(objArr, keyArr, type) {
		if (type != undefined && type != 'asc' && type != 'desc') {
			return 'error';
		}
		var order = 1;
		if (type != undefined && type == 'desc') {
			order = -1;
		}
		var key = keyArr[0];
		objArr.sort(function (objA, objB) {
			if (objA[key] > objB[key]) {
				return order;
			} else if (objA[key] < objB[key]) {
				return 0 - order;
			} else {
				return 0;
			}
		})
		for (var i = 1; i < keyArr.length; i++) {
			var key = keyArr[i];
			objArr.sort(function (objA, objB) {
				for (var j = 0; j < i; j++) {
					if (objA[keyArr[j]] != objB[keyArr[j]]) {
						return 0;
					}
				}
				if (objA[key] > objB[key]) {
					return order;
				} else if (objA[key] < objB[key]){
					return 0 - order;
				} else {
					return 0;
				}
			})
		}
		return objArr;
    },
	// 自定义字段类型
	formType() {
		return [
	        {label: "文本", is: "el-input", value: "", icon: "icon-danhangshurukuang"},
	        {label: "文本域", is: "el-input", type: "textarea", value: "", icon: "icon-duohangshurukuang"},
	        {label: "编辑器", is: "el-editor", value: "", icon: "icon-fuwenbenbianjiqi_zhonghuaxian"},
	        {label: "链接设置", is: "el-link-select", value: {}, icon: "icon-lianjie"},
	        {label: "自定义数组", is: "el-array", value: {}, icon: "icon-shuzu"},
	        {label: "图片上传", is: "el-file-select", type: "image", value: "", icon: "icon-tupianpic"},
	        {label: "图片列表", is: "el-file-list-select", type:"image", value: [], icon: "icon-huadongduotu"},
	        {label: "文件上传", is: "el-file-select", type: "all", value: "", icon: "icon-a-wenjianjiawenjian"},
	        {label: "文件列表", is: "el-file-list-select", type:"all", value: [], icon: "icon-wenjian1"},
	        {label: "分类编号", is: "el-catalog-select", value: 0, icon: "icon-bianhaodanhao"},
	        {label: "参数设置", is: "el-parameter", value: [], icon: "icon-chanpincanshu"},
	        {label: "颜色选择", is: "el-color-picker", value: "", icon: "icon-yanse1"},
	        {label: "开关", is: "el-switch", value: false, icon: "icon-kaiguan"},
	    ];
	},
	// 日期时间格式
	dateTime(date = new Date()) {
		let year = date.getFullYear(); // 年
		let month = date.getMonth() + 1; // 月
		month = month < 10 ? "0" + month : month; // 如果只有一位，则前面补零
		let day = date.getDate(); // 日
		day = day < 10 ? "0" + day : day; // 如果只有一位，则前面补零
		let hour = date.getHours(); // 时
		hour = hour < 10 ? "0" + hour : hour; // 如果只有一位，则前面补零
		let minute = date.getMinutes(); // 分
		minute = minute < 10 ? "0" + minute : minute; // 如果只有一位，则前面补零
		let second = date.getSeconds(); // 秒
		second = second < 10 ? "0" + second : second; // 如果只有一位，则前面补零
		return `${year}-${month}-${day} ${hour}:${minute}:${second}`;
	},
	// 友好时间显示
	diaplayTime(data) {
        var str = data;
        //将字符串转换成时间格式
        var timePublish = new Date(str);
        var timeNow = new Date();
        var minute = 1000 * 60;
        var hour = minute * 60;
        var day = hour * 24;
        var month = day * 30;
        var diffValue = timeNow - timePublish;
        var diffMonth = diffValue / month;
        var diffWeek = diffValue / (7 * day);
        var diffDay = diffValue / day;
        var diffHour = diffValue / hour;
        var diffMinute = diffValue / minute;
        if (diffValue < 0) {
            result = str;
        } else if (diffMonth > 3) {
            result = str;
        } else if (diffMonth > 1) {
            result = parseInt(diffMonth) + "月前";
        } else if (diffWeek > 1) {
            result = parseInt(diffWeek) + "周前";
        } else if (diffDay > 1) {
            result = parseInt(diffDay) + "天前";
        } else if (diffHour > 1) {
            result = parseInt(diffHour) + "小时前";
        } else if (diffMinute > 1) {
            result = parseInt(diffMinute) + "分钟前";
        } else {
            result = "刚刚";
        }
        return result;
    },
    // 友好时间显示
    stampTime(data) {
        var currentTime = Math.round((new Date()).valueOf());
        var nowTime = new Date(currentTime);
        var provideTime = data * 1000;
        var provideDate = new Date(provideTime);
        // 当前时间转换
        var nowY = nowTime.getFullYear();
        var nowM = nowTime.getMonth() + 1;
        var nowD = nowTime.getDate();
        //获取时间转换
        var proY = provideDate.getFullYear();
        var proM = provideDate.getMonth() + 1;
        var proD = provideDate.getDate();
        // 转换时间样式
        var Y = provideDate.getFullYear() + '-';
        var M = (provideDate.getMonth() + 1 < 10 ? '0' + (provideDate.getMonth() + 1) : provideDate.getMonth() + 1) + '-';
        var D = (provideDate.getDate() < 10 ? '0' + provideDate.getDate() : provideDate.getDate()) + ' ';
        var h = (provideDate.getHours() < 10 ? '0' + provideDate.getHours() : provideDate.getHours()) + ':';
        var m = provideDate.getMinutes() < 10 ? '0' + provideDate.getMinutes() : provideDate.getMinutes();
        var weekend = provideDate.getDay();
        switch (weekend){
            case 1 :
                weekend = "星期一";
                break;
            case 2 :
                weekend = "星期二";
                break;
            case 3 :
                weekend = "星期三";
                break;
            case 4 :
                weekend = "星期四";
                break;
            case 5 :
                weekend = "星期五";
                break;
            case 6 :
                weekend = "星期六";
                break;
            case 0 :
                weekend = "星期日";
                break;
        }
        var time;
        if(currentTime >= provideTime){
            if(nowY <= proY){
                if(nowM <= proM){
                    if(nowD <= proD){
                         time = h + m;
                    }else if(nowD - proD >= 1 && nowD - proD < 2){
                         time = "昨天 " + h + m;
                    }else if(nowD - proD >= 2 && nowD - proD < 7){
                         time = weekend + " " + h + m;
                    }else {
                         time = M + D + h + m;
                    }
                }else {
                    time = M + D + h + m;
                }
            }else {
                time = Y + M + D + h + m;
            }
        }else {
            time = h + m;
        }
        return time;
    }
}

/**
 * 文件
 */
var file = {
	// 获取大小
	size(Bytes) {
	    if (null == Bytes || Bytes == '') {
	        return "0 Bytes";
	    }
	    var unitArr = new Array("Bytes","KB","MB","GB","TB","PB","EB","ZB","YB");
	    var index = 0,
	    srcsize = parseFloat(Bytes);
		index = Math.floor(Math.log(srcsize) / Math.log(1024));
	    var size = srcsize / Math.pow(1024,index);
	    size = size.toFixed(2);
	    return size+unitArr[index];
	},
	// 获取类型、名称
	type(url) {
		let arr    = url.split('.');
		let suffix = arr[arr.length-1];
		switch(suffix){
			case ('png'):
			case ('jpg'):
			case ('jpeg'):
			case ('bmp'):
			case ('gif'):
			case ('ico'):
				return {title: '图片', name: 'image'};
				break;
			case ('mp4'):
			case ('m2v'):
			case ('mkv'):
			case ('wmv'):
				return {title: '视频', name: 'video'};
				break;
			case ('mp3'):
			case ('wav'):
				return {title: '音频', name: 'audio'};
				break;
			case ('txt'):
				return {title: '文本', name: 'txt'};
				break;
			case ('xls'):
			case ('csv'):
			case ('xlsx'):
				return {title: 'Excel文件', name: 'xls'};
				break;
			case ('doc'):
			case ('docx'):
				return {title: 'Word文件', name: 'word'};
				break;
			case ('pdf'):
				return {title: 'PDF文件', name: 'pdf'};
				break;
			case ('ppt'):
				return {title: 'PPT文件', name: 'ppt'};
				break;
			case ('rar'):
			case ('zip'):
				return {title: '压缩文件', name: 'zip'};
				break;
			case ('psd'):
				return {title: 'Photoshop文件', name: 'psd'};
				break;
			case ('swf'):
				return {title: 'Flash文件', name: 'swf'};
				break;
			case ('html'):
				return {title: 'html文件', name: 'html'};
				break;
			default:
				return {title: '未知文件', name: 'other'};
				break;
		}
	}
}