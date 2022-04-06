
$(document).ready(function(){
    //flightbycanto.com/staging.cantoflight.com/canto.com/canto.global/canto.de/cantodemo.com
    $.cantoUC({
        env: "canto.com",
        //extensions: "jpg;jpeg;png;gif"
    }, replaceCantoTagByImage);
});
function calcImageSize(num) {
    var size = Math.round(Number(num)/1024);
    return size < 1024 ? size + "KB" : Math.round(size/1024) + "MB";
}
function replaceCantoTagByImage(id, assetArray){
    var body = $("body");
    var cantoTag = body.find("canto" + "#" + id);
    var imageHtml = "";
    for(var i = 0; i < assetArray.length; i++){
        imageHtml += '<div class="canto-block">';
        imageHtml += '<img class="canto-preview-img" src="' + assetArray[i].previewUri + '">';
        imageHtml += '<div class="canto-preview-name">Name: ' + assetArray[i].displayName + '</div>';
        imageHtml += '<div class="canto-preview-size">Size: ' + calcImageSize(assetArray[i].size) + '</div>';
        imageHtml += '<a class="canto-preview-size" href="' + assetArray[i].directUri + '">Download</a>';
        imageHtml += '</div>';
    }
    cantoTag.replaceWith(imageHtml);
}

// Beginning of Canto's Universal Connector code:
(function ($, document, window) {
    var cantoUC,
    pluginName = "cantoUC",
    redirectUri = "",
    tokenInfo = {},
    env = "canto.com",  //flightbycanto.com/staging.cantoflight.com/canto.com/canto.global
    appId = "52ff8ed9d6874d48a3bef9621bc1af26",
    callback,
    currentCantoTagID,
    formatDistrict,
    timeStamp;

    cantoUC = $.fn[pluginName] = $[pluginName] = function (options, callback) {
        /*! options.env:   flightbycanto.com/staging.cantoflight.com/canto.com/canto.global
        */
        settings(options);
        callback = callback;
        //loadCantoUCResource();
        // createIframe();
        // addEventListener();
        // initCantoTag();

        window.onmessage=function(event){
            var data = event.data;
            if(data && data.type == "getTokenInfo"){
                var receiver = document.getElementById('cantoUCFrame').contentWindow;
                tokenInfo.formatDistrict = formatDistrict;
                receiver.postMessage(tokenInfo, '*');
            } else if(data && data.type == "cantoLogout"){
                //clear token and close the frame.
                tokenInfo = {};
                $(".canto-uc-iframe-close-btn").trigger("click");

            } else if(data && data.type == "cantoInsertImage"){
                $(".canto-uc-iframe-close-btn").trigger("click");
                // insertImageToCantoTag(cantoURL);
                callback(currentCantoTagID, data.assetList);

            } else if(data){
                verifyCode = data;
                // var cantoContentPage = "https://s3-us-west-2.amazonaws.com/static.dmc/universal/cantoContent.html";
                getTokenByVerifycode(verifyCode);
                
            }

        };
    };
    function settings(options){
        var envObj = {
            "flightbycanto.com":"f5ecd6095ebb469691b7398e4945eb44",
            "staging.cantoflight.com":"f18c8f3b79644b168cad5609ff802085",
            "canto.com":"a9dc81b1bf9d492f8ee3838302d266b2",
            "canto.global":"f87b44d366464dfdb4867ab361683c96",
            "canto.de":"e7135823e3d046468287e835008da493",
            "cantodemo.com":"de5c606732a34b44b99ec20c40f6cb5e"
        };
        env = options.env;
        // appId = envObj[env];
        formatDistrict = options.extensions;
    }
    function loadCantoUCResource() {
        // dynamicLoadJs("./cantoAssets/main.js");
        dynamicLoadCss("base.css");
    }
    function addEventListener() {

        $(document).on('click',".canto-uc-iframe-close-btn", function(e){
            $("#cantoUCPanel").addClass("hidden");
            $("#cantoUCFrame").attr("src", "");
        })
        .on('click', ".canto-pickup-img-btn", function(e){
            currentCantoTagID = $(e.target).closest("canto").attr("id");
            $("#cantoUCPanel").removeClass("hidden");
            // console.log("about to assign timeStamp in add")
            loadIframeContent();
        });
    }
    /*--------------------------load iframe content---------------------------------------*/
    function initCantoTag(){
        var body = $("body");
        var cantoTag = body.find("canto");
        var imageHtml = '<button class="canto-pickup-img-btn">+ Insert Files from Canto</button>';

        cantoTag.append(imageHtml);
    }

    /*--------------------------load iframe content---------------------------------------*/
    function loadIframeContent() {
        timeStamp = new Date().getTime();
        var cantoLoginPage = "https://oauth.canto.com/oauth/api/oauth2/universal2/authorize?response_type=code&app_id=" + "52ff8ed9d6874d48a3bef9621bc1af26" + "&redirect_uri=http://loacalhost:3000&state=abcd" + "&code_challenge=" + timeStamp + "&code_challenge_method=plain";
        //environment.
        /* If you want to deploy this to env, please select one and delete others, include above.*/
        // var cantoLoginPage = "https://oauth.flightbycanto.com/oauth/api/oauth2/universal/authorize?response_type=code&app_id=f5ecd6095ebb469691b7398e4945eb44&redirect_uri=http://loacalhost:3000&state=abcd";
        // var cantoLoginPage = "https://oauth.staging.cantoflight.com/oauth/api/oauth2/universal/authorize?response_type=code&app_id=f18c8f3b79644b168cad5609ff802085&redirect_uri=http://loacalhost:3000&state=abcd";
        // var cantoLoginPage = "https://oauth.canto.com/oauth/api/oauth2/universal/authorize?response_type=code&app_id=a9dc81b1bf9d492f8ee3838302d266b2&redirect_uri=http://loacalhost:3000&state=abcd";
        // var cantoLoginPage = "https://oauth.canto.global/oauth/api/oauth2/universal/authorize?response_type=code&app_id=f87b44d366464dfdb4867ab361683c96&redirect_uri=http://loacalhost:3000&state=abcd";

         // var cantoContentPage = "https://s3-us-west-2.amazonaws.com/static.dmc/universal/cantoContent.html";
       var cantoContentPage = "./cantoAssets/cantoContent.html";
        // $("#cantoUCFrame").attr("src", cantoContentPage);
        if(tokenInfo.accessToken){
            $("#cantoUCFrame").attr("src", cantoContentPage);
        } else {
            $("#cantoUCFrame").attr("src", cantoLoginPage);
        }
    }
    function getTokenByVerifycode(verifyCode) {
        // timeStamp = new Date().getTime();
        console.log("timestamp : " + timeStamp);
        console.log(verifyCode);
        console.log("appId : " + appId);
        $.ajax({type:"POST",
            url: "https://oauth.canto.com/oauth/api/oauth2/universal2/token", 
            dataType:"json", 
            data:{ 
                "app_id": appId,
                "grant_type": "authorization_code",
                "redirect_uri": "http://localhost:8080",
                "code": verifyCode,
                "code_verifier": "1649285048042"
            }, 
            success:function(data){
                tokenInfo = data;
                getTenant(tokenInfo);
                
            },
            error: function(request) {
                console.log("am errnro");
                console.log(request);
                alert("Get token errorz");
            }
        });
    }
    function getTenant(tokenInfo) {
        $.ajax({type:"GET",
            url: "https://oauth." + env + ":443/oauth/api/oauth2/tenant/" + tokenInfo.refreshToken, 
            success:function(data){
                tokenInfo.tenant = data;
                console.log("inside getTenant().success()");
                var cantoContentPage = "./cantoAssets/cantoContent.html"; //universal-dam-integrator
                $("#cantoUCFrame").attr("src", "/admin/universal-dam-integrator/cantoContent.html");
            },
            error: function(request) {
                alert("Get tenant error");
            }
        });
    }
    /*--------------------------add iframe---------------------------------------*/
    function createIframe() {
        var body = $("body");
        var iframeHtml = '<div class="canto-uc-frame hidden" id="cantoUCPanel">';
        iframeHtml += '<div class="header">';
        iframeHtml += '<div class="title">Canto Content</div>';
        iframeHtml += '<div class="close-btn icon-s-closeicon-16px canto-uc-iframe-close-btn"></div>';
        iframeHtml += '</div>';
        iframeHtml += '<iframe id="cantoUCFrame" class="canto-uc-subiframe" src=""></iframe>';
        iframeHtml += '</div>';

        body.append(iframeHtml);
    }
    /*--------------------------load file---------------------------------------*/
    function dynamicLoadJs(url, callback) {
        var head = document.getElementsByTagName('head')[0];
        var script = document.createElement('script');
        script.type = 'text/javascript';
        script.src = url;
        if(typeof(callback)=='function'){
            script.onload = script.onreadystatechange = function () {
                if (!this.readyState || this.readyState === "loaded" || this.readyState === "complete"){
                    callback();
                    script.onload = script.onreadystatechange = null;
                }
            };
        }
        head.appendChild(script);
    }
    function dynamicLoadCss(url) {
        var head = document.getElementsByTagName('head')[0];
        var link = document.createElement('link');
        link.type='text/css';
        link.rel = 'stylesheet';
        link.href = url;
        head.appendChild(link);
    }


}(jQuery, document, window));
// End of Canto's Universal Connector code