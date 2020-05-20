import moment from 'moment';

export function fixedZero(val) {
    return val * 1 < 10 ? `0${val}` : val;
}

export function getTimeDistance(type) {
    const now = new Date();
    const oneDay = 1000 * 60 * 60 * 24;

    if (type === 'today') {
        now.setHours(0);
        now.setMinutes(0);
        now.setSeconds(0);
        return [moment(now), moment(now.getTime() + (oneDay - 1000))];
    }

    if (type === 'week') {
        let day = now.getDay();
        now.setHours(0);
        now.setMinutes(0);
        now.setSeconds(0);

        if (day === 0) {
            day = 6;
        } else {
            day -= 1;
        }

        const beginTime = now.getTime() - day * oneDay;

        return [moment(beginTime), moment(beginTime + (7 * oneDay - 1000))];
    }

    if (type === 'month') {
        const year = now.getFullYear();
        const month = now.getMonth();
        const nextDate = moment(now).add(1, 'months');
        const nextYear = nextDate.year();
        const nextMonth = nextDate.month();

        return [
            moment(`${year}-${fixedZero(month + 1)}-01 00:00:00`),
            moment(moment(`${nextYear}-${fixedZero(nextMonth + 1)}-01 00:00:00`).valueOf() - 1000),
        ];
    }

    if (type === 'year') {
        const year = now.getFullYear();

        return [moment(`${year}-01-01 00:00:00`), moment(`${year}-12-31 23:59:59`)];
    }
}

export function getPlainNode(nodeList, parentPath = '') {
    const arr = [];
    nodeList.forEach(node => {
        const item = node;
        item.path = `${parentPath}/${item.path || ''}`.replace(/\/+/g, '/');
        item.exact = true;
        if (item.children && !item.component) {
            arr.push(...getPlainNode(item.children, item.path));
        } else {
            if (item.children && item.component) {
                item.exact = false;
            }
            arr.push(item);
        }
    });
    return arr;
}
/**
 * @description 将数值金额转换成大写金额
 * @date 2019-01-29
 * @export
 * @param {*} n 数值金额
 * @returns string
 */
export function digitUppercase(n) {
    const fraction = ['角', '分'];
    const digit = ['零', '壹', '贰', '叁', '肆', '伍', '陆', '柒', '捌', '玖'];
    const unit = [['元', '万', '亿'], ['', '拾', '佰', '仟']];
    let num = Math.abs(n);
    let s = '';
    fraction.forEach((item, index) => {
        s += (digit[Math.floor(num * 10 * 10 ** index) % 10] + item).replace(/零./, '');
    });
    s = s || '整';
    num = Math.floor(num);
    for (let i = 0; i < unit[0].length && num > 0; i += 1) {
        let p = '';
        for (let j = 0; j < unit[1].length && num > 0; j += 1) {
            p = digit[num % 10] + unit[1][j] + p;
            num = Math.floor(num / 10);
        }
        s = p.replace(/(零.)*零$/, '').replace(/^$/, '零') + unit[0][i] + s;
    }

    return s
        .replace(/(零.)*零元/, '元')
        .replace(/(零.)+/g, '零')
        .replace(/^整$/, '零元整');
}

/**
 * @description 判断两个字符串关系
 * @date 2019-01-29
 * @param {*} str1 
 * @param {*} str2
 * @returns number
 */
function getRelation(str1, str2) {
    if (str1 === str2) {
        console.warn('Two path are equal!'); // eslint-disable-line
    }
    const arr1 = str1.split('/');
    const arr2 = str2.split('/');
    if (arr2.every((item, index) => item === arr1[index])) {
        return 1;
    } else if (arr1.every((item, index) => item === arr2[index])) {
        return 2;
    }
    return 3;
}

function getRenderArr(routes) {
    let renderArr = [];
    renderArr.push(routes[0]);
    for (let i = 1; i < routes.length; i += 1) {
        let isAdd = false;
        // 是否包含
        isAdd = renderArr.every(item => getRelation(item, routes[i]) === 3);
        // 去重
        renderArr = renderArr.filter(item => getRelation(item, routes[i]) !== 1);
        if (isAdd) {
            renderArr.push(routes[i]);
        }
    }
    return renderArr;
}

/**
 * Get router routing configuration
 * { path:{name,...param}}=>Array<{name,path ...param}>
 * @param {string} path
 * @param {routerData} routerData
 */
export function getRoutes(path, routerData) {
    let routes = Object.keys(routerData).filter(
        routePath => routePath.indexOf(path) === 0 && routePath !== path
    );
    // Replace path to '' eg. path='user' /user/name => name
    routes = routes.map(item => item.replace(path, ''));
    // Get the route to be rendered to remove the deep rendering
    const renderArr = getRenderArr(routes);
    // Conversion and stitching parameters
    const renderRoutes = renderArr.map(item => {
        const exact = !routes.some(route => route !== item && getRelation(route, item) === 1);
        return {
            exact,
            ...routerData[`${path}${item}`],
            key: `${path}${item}`,
            path: `${path}${item}`,
        };
    });
    return renderRoutes;
}

/* eslint no-useless-escape:0 */
const reg = /(((^https?:(?:\/\/)?)(?:[-;:&=\+\$,\w]+@)?[A-Za-z0-9.-]+|(?:www.|[-;:&=\+\$,\w]+@)[A-Za-z0-9.-]+)((?:\/[\+~%\/.\w-_]*)?\??(?:[-\+=&;%@.\w_]*)#?(?:[\w]*))?)$/g;
// 验证url是否正确
export function isUrl(path) {
    return reg.test(path);
}

// 正则规则整理
export const regular = {
    // 验证自然数
    naturalNumber: /^(([0-9]*[1-9][0-9]*)|(0+))$/,
    // 英文
    english: /^.[A-Za-z]+$/,
    // 验证是否是座机号
    telephone: /^\d{3}-\d{7,8}|\d{4}-\d{7,8}$/,
    // 手机号
    mobile: /^1[34578]\d{9}$/,
    // 银行卡号码
    bankCard: /^[1-9]\d{9,19}$/,
    // 证件号码
    IDNumber: /^[a-z0-9A-Z]{0,50}$/,
    // 身份证号码,包括15位和18位的
    IDCard: /(^[1-9]\d{5}(18|19|([23]\d))\d{2}((0[1-9])|(10|11|12))(([0-2][1-9])|10|20|30|31)\d{3}[0-9Xx]$)|(^[1-9]\d{7}((0[1-9])|(10|11|12))(([0-2][1-9])|10|20|30|31)\d{3}$)/,
    // QQ号码
    qq: /^[1-9]\d{4,11}$/,
    // 网址, 仅支持http和https开头的
    url: /^(http|https):\/\/[\w\-_]+(\.[\w\-_]+)+([\w\-.,@?^=%&:/~+#]*[\w\-@?^=%&/~+#])?$/,
    // 0到20位的英文字符和数字
    enNum0to20: /^[a-z0-9A-Z]{0,20}$/,
    // 2到100位的中英文字符和空格，可以是中文、英文、空格
    cnEnSpace2to100: /^[a-zA-Z\u4E00-\u9FA5\s*]{2,100}$/,
    // 数字和换行符
    numLinefeed: /^[0-9\n*]+$/,
    // 255位以内的字符
    char0to255: /^.{0,255}$/,
  }


export function getCookie(cookie_name) {
      var allcookies = document.cookie;
      //索引长度，开始索引的位置
      var cookie_pos = allcookies.indexOf(cookie_name);

      // 如果找到了索引，就代表cookie存在,否则不存在
      if (cookie_pos != -1) {
          // 把cookie_pos放在值的开始，只要给值加1即可
          //计算取cookie值得开始索引，加的1为“=”
          cookie_pos = cookie_pos + cookie_name.length + 1;
          //计算取cookie值得结束索引
          var cookie_end = allcookies.indexOf(";", cookie_pos);

          if (cookie_end == -1) {
              cookie_end = allcookies.length;
          }
          //得到想要的cookie的值
          var value = unescape(allcookies.substring(cookie_pos, cookie_end));
      }
      return value;
  }


import CryptoJS from 'crypto-js';

//AES加密
export function encrypt(word, keyStr) {
    keyStr = keyStr ? keyStr : 'easyswoole666666'; //判断是否存在ksy，不存在就用定义好的key
    var key = CryptoJS.enc.Utf8.parse(keyStr);
    var srcs = CryptoJS.enc.Utf8.parse(word);
    var encrypted = CryptoJS.AES.encrypt(srcs, key, { mode: CryptoJS.mode.ECB, padding: CryptoJS.pad.Pkcs7 });
    return encrypted.toString();
}
//AES解密
export function decrypt(word, keyStr) {
    keyStr = keyStr ? keyStr : 'easyswoole666666';
    var key = CryptoJS.enc.Utf8.parse(keyStr);
    var decrypt = CryptoJS.AES.decrypt(word, key, { mode: CryptoJS.mode.ECB, padding: CryptoJS.pad.Pkcs7 });
    return CryptoJS.enc.Utf8.stringify(decrypt).toString();
}

/*
//DES加密
export function encryptDes (message, key) {
    var keyHex = cryptoJs.enc.Utf8.parse(key)
    var option = { mode: cryptoJs.mode.ECB, padding: cryptoJs.pad.Pkcs7 }
    var encrypted = cryptoJs.DES.encrypt(message, keyHex, option)
    return encrypted.ciphertext.toString()
}
//DES解密
export function decryptDes (message, key) {
    var keyHex = cryptoJs.enc.Utf8.parse(key)
    var decrypted = cryptoJs.DES.decrypt(
        {
            ciphertext: cryptoJs.enc.Hex.parse(message)
        },
        keyHex,
        {
            mode: cryptoJs.mode.ECB,
            padding: cryptoJs.pad.Pkcs7
        }
    )
    return decrypted.toString(cryptoJs.enc.Utf8)
}
*/
// 加密请求参数
export function encryptParams (params)
{
    if(params!==null && params!=="" && params!==undefined && typeof(params)!=='undefined')
    {
        params = JSON.stringify(params);
        params = {encrypt: encrypt(params)};
        // 格式化参数形式，不然后端收不到参数
        let Params = null;
        if (params){
            Params = new URLSearchParams()
            let arr = Object.entries(params)
            for (let [k,v] of arr){
                Params.append(k,v)
            }
        }
        return Params
    }
    return null;
}

// 解密响应内容
export function decryptParams (res)
{
    let data = res.data
    if(data!==null && data!=="" && data!==undefined && typeof(data)!=='undefined')
    {
        data = JSON.parse(decrypt(data));
        //console.log( objToArray(res) )
        //return res
    }
    return data;
}

export function objToArray(data) {
    let data2 = [];            // 某种意义上来说，数组也是object

    /*for (let i in data) {
        let o = {};
        o[i] = data[i];
        data2.push(o)
    }*/

    for (let [k,v] of Object.entries(data)) {
        data2.push([k,v])
    }
    console.log(data2)
    return data2
}

