jQuery(document).ready(function ($) { 
    var data;
    $('.frmReviewCreate').ajaxForm({
        type: 'post',       
        success: function (response) {
            console.log(response);
        },
        error: function (response) {
            console.log(response);
        },
        beforeSubmit: function(arr, $form, options) { 
            //console.log(arr);                     
        }
    });    
});

function starChange(numerator, uniqueId, pluginPath) {
    document.getElementById('numerator'+ uniqueId).value = numerator;
    var star1 =  document.getElementById('imgStar1' + uniqueId);
    var star2 =  document.getElementById('imgStar2'+ uniqueId);
    var star3 =  document.getElementById('imgStar3'+ uniqueId);
    var star4 =  document.getElementById('imgStar4'+ uniqueId);
    var star5 =  document.getElementById('imgStar5'+ uniqueId); 

    if (numerator == 1){      
        star1.src = pluginPath + "/star_full.svg";
        star2.src = pluginPath + "/star_empty.svg";
        star3.src = pluginPath + "/star_empty.svg";
        star4.src = pluginPath + "/star_empty.svg";
        star5.src = pluginPath + "/star_empty.svg";
    } else if (numerator == 2){
        star1.src = pluginPath + "/star_full.svg";
        star2.src = pluginPath + "/star_full.svg";
        star3.src = pluginPath + "/star_empty.svg";
        star4.src = pluginPath + "/star_empty.svg";
        star5.src = pluginPath + "/star_empty.svg";
    } else if (numerator == 3){
        star1.src = pluginPath + "/star_full.svg";
        star2.src = pluginPath + "/star_full.svg";
        star3.src = pluginPath + "/star_full.svg";
        star4.src = pluginPath + "/star_empty.svg";
        star5.src = pluginPath + "/star_empty.svg";
    } else if (numerator == 4){
        star1.src = pluginPath + "/star_full.svg";
        star2.src = pluginPath + "/star_full.svg";
        star3.src = pluginPath + "/star_full.svg";
        star4.src = pluginPath + "/star_full.svg";
        star5.src = pluginPath + "/star_empty.svg";
    } else if (numerator == 5){
        star1.src = pluginPath + "/star_full.svg";
        star2.src = pluginPath + "/star_full.svg";
        star3.src = pluginPath + "/star_full.svg";
        star4.src = pluginPath + "/star_full.svg";
        star5.src = pluginPath + "/star_full.svg";
    }
}

function showDiv(uniqueId) {
    document.getElementById(uniqueId).style.display = 'block';
}

function hideDiv(uniqueId) {
    document.getElementById(uniqueId).style.display = 'none';
}


