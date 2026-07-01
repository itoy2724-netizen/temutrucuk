
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=.5">

		<title>ÖSYM:::T.C. Ölçme, Seçme ve Yerleştirme Merkezi</title>
		<link rel="icon" href="/public/images/OSYMicon.ico" type="image/x-icon">

		<link type="text/css" rel="stylesheet" href="/public/stylesheets/style.css" />
	
		<link rel="stylesheet" media="screen" href="/public/stylesheets/jquery-ui.custom.min.css">
		<script type="text/javascript" src="/public/javascripts/jquery.js" charset="utf-8"></script>
		<script type="text/javascript" src="/public/javascripts/generic.js" charset="utf-8"></script>
		<script type="text/javascript" src="/public/javascripts/yav.js" charset="utf-8"></script>
		<script type="text/javascript" src="/public/javascripts/yav-config.js" charset="utf-8"></script>
		<script type="text/javascript" src="/public/javascripts/jquery-ui.custom.min.js" charset="utf-8"></script>

		<script type="text/javascript" language="javascript">var dateObject=new Date();</script>

		<script>
	  	function displayMessage(hataMesaj, hataTipi) 
 		{
            document.getElementById('messageDialog').innerHTML = hataMesaj;
            var widgetHeaderTag = "ui-widget-header";
            var widgetHeaderErrorTag = "ui-widget-header";
            if (hataTipi == "Hata")
            {
                widgetHeaderErrorTag = "ui-widget-header-error";
            }
            $("#messageDialog").dialog({
                modal:true,
                width: hataMesaj.length > 60 ? 'auto' : '300' ,
                open: function(event, ui){
                    $(this).parents(".ui-dialog:first").find("."+widgetHeaderTag)
                            .removeClass(widgetHeaderTag).addClass(widgetHeaderErrorTag);
                },
                close: function(event, ui){
                    $(this).parents(".ui-dialog:first").find("."+widgetHeaderErrorTag)
                            .removeClass(widgetHeaderErrorTag).addClass(widgetHeaderTag);
                },
                buttons:{
                    "Tamam":function () {
                        $(this).dialog("close");
                    }
                }
                ,
                title: "Hata"
            });
 		}

	  	function onLoadAction()
        {
            $('#mesaj').attr('href');
            if ("" != '')
            {
                displayMessage("", "");
            }
            if ("false" == 'true')
            {
                $('head').append('<link rel="stylesheet" href="/public/stylesheets/mobil.css" type="text/css" />');
                $('#mesaj a').removeAttr('href');
                $('#mesaj').text(function(index,text){return text.replace('https://ais.osym.gov.tr',' Aday İşlemleri Sistemi ');});
            }
        }
		</script>
	</head>
	<body onload="onLoadAction()">
		<div class="main-header">
			<div class="header-top">
                <a href="https://ais.osym.gov.tr/IslemTakvimi" class="header-calendar-link">ÖSYM İşlem Takvimi</a>
				<span class="header-logo" style="color: #FFFFFF">
					<img src="/public/images/odeme-logo-short.png" /> ÖDEME İŞLEMLERİ SİSTEMİ
				</span>
                <a href="https://www.osym.gov.tr" class="header-homepage-link">ÖSYM Ana Sayfa</a>
            </div>
			<!--<div class="header-bottom"></div>-->
        </div>
        <div class="container">
            <div class="header"></div>
            <div class="content">
                

<span></span>
<div class="timelineayrac">&nbsp;</div>
<table style="border: 0">
	<tr>
		<td style="border: 0">
			
				<img src="public/images/error.png"  class="for-mobile-app">
			
			
		</td>
		<td style="border: 0; padding-left: 10px">
			<span style="font-weight: bold;" class="for-mobile-msg" id="mesaj">Sistemde beklenmedik bir hata oluştu.<br>Lütfen daha sonra yeniden deneyiniz. </span>
		</td>
	</tr>
</table>

            </div>
        </div>
        <div class="footer">
            T.C. Ölçme, Seçme ve Yerleştirme Merkezi ©
            <script type="text/javascript">
                document.write(dateObject.getFullYear());
            </script>
            - Her Hakkı Saklıdır.
        </div>
	</body>
	<div id="messageDialog" title="" style="display:none;"></div>

    <script type="text/javascript">
        function dildegistir(dil)
        {
            /odemecontroller/ode
        }
    </script>

    <script>
        $(document).ready(function() {

            $('.dropdown-item').click(function() {
                var dil = JSON.stringify($(this).attr('id'));
                $.ajax({
                    type: "POST",
                    url: '/dildegistir',
                    data: {dil: JSON.stringify($(this).attr('id'))}, //--> send id of checked checkbox on other page
                    success: function(message) {
                        sessionStorage.setItem('dil', dil);
                        localStorage.setItem('dil', dil);
                        location.reload();
                    },
                    error: function() {
                        // $("#dialog").html("<font color='red'>Görme Engelli Adaylara Sorulmayacak Muaf Soru İşlemi Sırasında Belirsiz Hata</font>");
                        // $("#dialog").dialog({title: "Hata"});
                        // $("#dialog").dialog("open");
                    }
                });
            });
        });

    </script>



</html>