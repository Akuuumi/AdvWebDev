document.addEventListener("DOMContentLoaded", function() {
    setCookie = (cName, cValue, expDays) => {
        let date  =  new Date();
        date.setTime(date.getTime()+ (expDays * 24 * 60 * 60 * 1000));
        const expires = "expires=" + date.toUTCString();
        document.cookie = cName + "=" + cValue + "; " + expires + ";path=/";
    }

    getCookie = (cName) => {
        const name = cName + "=";
        const cArr = document.cookie.split("; ");
        let value;
        for(let i = 0; i < cArr.length; i++) {
            if(cArr[i].indexOf(name) === 0) {
                value = cArr[i].substring(name.length);
                break;
            }
        }
        return value? value : false;
    }

    document.querySelector("#cookies-btn").addEventListener("click", () => {
        document.querySelector("#cookies").style.display = "none";
        setCookie("cookie", true, 30);
    })

    cookieMessage = () => {
        if(!getCookie("cookie"))
            document.querySelector("#cookies").style.display = "block";
    }

    window.addEventListener("load", cookieMessage);
});