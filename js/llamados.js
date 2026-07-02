/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

var script_jqu = document.createElement('script');

script_jqu.onload = function () {

    var script_confirm = document.createElement('script');
    
    script_confirm.onload = function () {
        //alert("Script loaded and script_confirm");
    };

    script_confirm.src = "../../js/jquery-confirm.js";
    document.getElementsByTagName('head')[0].appendChild(script_confirm);

    var script_search = document.createElement('script');

    if (location.href.indexOf('view_create_token_hijo.php') != -1) {
        script_search.src = "../../js/ajax/getData_hijo.js";
    } else if (location.href.indexOf('search_course.php') != -1) {
        script_search.src = "../../js/ajax/search_course.js";
    } else if (location.href.indexOf('view_sftp.php') != -1) {
        script_search.src = "../../js/ajax/getConfigSFTP.js";
    }else if (location.href.indexOf('view_s3.php') != -1) {
        script_search.src = "../../js/ajax/config_s3.js";
    }

    document.getElementsByTagName('head')[0].appendChild(script_search);

};

script_jqu.src = "https://code.jquery.com/jquery-3.6.3.min.js";
document.getElementsByTagName('head')[0].appendChild(script_jqu);