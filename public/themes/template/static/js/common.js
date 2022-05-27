/**
 * 网络请求(全局使用此方法进行请求接口)
 */
var request = {
	/**
	 * 发送post请求
	 * @param  {String} link   链接
	 * @param  {Object} data  数据集
	 */
	post(link, data, callback = ""){
		$.ajax({
			url: url(link),
			type: 'post',
			dataTyle: 'json',
			data: data,
			success:function(res) {
				var res = typeof res == 'string' ? JSON.parse(res) : res;
				if (res.status === 'login') {
					location.href = url("login/index");
				} else {
					if (callback != "") callback(res);
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
 * 链接
 */
var locationUrl = {
	/**
	 * 获取链接参数
	 * @param  {String} name 参数名
	 */
	get(name) {
	    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i"); 
	    var u = decodeURI(window.location.search.substr(1));
	    var r = u.match(reg); 
	    if (r != null) return unescape(r[2]); 
	    return "";
	},
	/**
	 * 设置链接参数
	 * @param  {String} name  参数名
	 * @param  {String} value 值
	 */
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
 * 公共函数
 */
var common = {
	id(len = 16) {
		var chars = 'ABCDEFGHJKMNPQRSTWXYZabcdefhijkmnprstwxyz2345678';
		var id = '';
		for (i = 0; i < len; i++) {
			id += chars.charAt(Math.floor(Math.random() * chars.length));
		}
		return id;
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
	// 倒计时00:00
	invalidTime(endtime) {
	    var nowtime = new Date();  //获取当前时间
	    var lefttime = (endtime*1000) - nowtime.getTime(); //距离结束时间的毫秒数
        var leftm = Math.floor(lefttime/(1000*60)%60);  //计算分钟数
        var lefts = Math.floor(lefttime/1000%60);  //计算秒数
        if (leftm < 10) {
        	leftm = '0' + leftm;
        }
        if (lefts < 10) {
        	lefts = '0' + lefts;
        }
	    return leftm + ":" + lefts;  //返回倒计时的字符串
	},
	// 友好时间显示
	diaplayTime(str) {
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