$(function () {

    $("#BtnShowBasketContent").click(MiniBasket.BtnShowBasketContentClick);
    $("#MiniBasket .BasketContent .complateShopping").click(function () {
        document.location = $(this).attr("target").toString();
    });
    //$('div#cartItems').jScrollPane({ verticalDragMaxHeight: 35, verticalDragMinHeight: 35 });        
});

var MiniBasket = {
    BasketPageUrl: '',
    BasketPageFullUrl: '',
    StockOutProductList: null,
    ShowStockOutList: function () {

        $("#DVStockControlItem").html('');
        var html = '<ul class="DVStockControlItems">';

        for (var i = 0; i < MiniBasket.StockOutProductList.length; i++) {
            html += '<li class="stockItemName">';
            html += MiniBasket.StockOutProductList[i].ProductName;
            html += '</li>';
            html += '<li class="stockItemMaxAmount">';
            if (MiniBasket.StockOutProductList[i].MaxAvailableAmount != 0)
                html += 'Üründen en fazla ' + MiniBasket.StockOutProductList[i].MaxAvailableAmount + ' adet satın alabilirsiniz.';
            else
                html += 'Üründen stokta bulunmamaktadır.';
            html += '</li>';
        }
        html += "</ul>";
        $("#DVStockControlItem").html(html);
        $.blockUI({
            message: $('#DVStockControl'),
            css: { border: '1px solid', padding: '10px', '-webkit-border-radius': '10px', '-moz-border-radius': '10px', color: '#000', width: '500px' },
            centerX: true,
            centerY: true
        });
        if (document.location.toString().indexOf(MiniBasket.BasketPageUrl) == -1) {

            $("#btnStockControlAlertOk").click(function () {
                document.location = MiniBasket.BasketPageFullUrl;
            });
        }
    },

    WidgetId: 0,
    Hide: function () {
        $("#MiniBasket .BasketContent").hide();
    },
    UpdateMiniBasket: function (Amount, Price, item, replace) {
        $("#spnMiniBasketAmount").html(Amount);
        $("#spnMiniBasketTotalPrice").html(Price);

        var updated = false;
        var deleted = -1;
        for (var i = 0; i < MiniBasket.BasketItems.length; i++) {
            if (item.Id == MiniBasket.BasketItems[i].Id) {
                if (replace == true)
                    MiniBasket.BasketItems[i].Amount = item.Amount;
                else
                    MiniBasket.BasketItems[i].Amount = parseInt(MiniBasket.BasketItems[i].Amount) + parseInt(item.Amount);

                if (MiniBasket.BasketItems[i].Amount == 0) {
                    deleted = i;
                }
                //alert(item.Amount);
                updated = true;
            }
        }
        if (deleted != -1) {
            MiniBasket.BasketItems.splice(deleted, 1);
        }
        if (updated == false) {
            MiniBasket.BasketItems.push({ Id: item.Id, Name: item.ProductName, Price: item.MinPrice, Amount: item.Amount, Image: item.Image });
        }
        MiniBasket.GenerateMiniBasketItemHtml();


    },

    DeleteItem: function (ItemId) {
        if (typeof (Loading) != undefined) Loading.Show();


        $.ajax({
            url: "SimpleWidget/" + MiniBasket.WidgetId + ".aspx?DeleteProductId=" + ItemId,
            error: function () {
                if (typeof (Loading) != undefined) Loading.Hide();
            },

            success: function (datas) {

                eval("var data=" + datas + ";");

                $("#ShopCartItem" + ItemId).remove();
                $("#BasketListItem" + ItemId).remove();

                $(".BasketSummary .TotalPrice").html(data.Basket.TotalPrice);

                $(".BasketSummary .SubPrice").html(data.Basket.SubPrice);
                $(".BasketSummary .VatPrice").html(data.Basket.VatPrice);

                MiniBasket.UpdateMiniBasket(data.Basket.Amount, data.Basket.TotalPrice, data.Item, true);
                if (typeof (Loading) != undefined) Loading.Hide();
            }
        });
    },



    BtnShowBasketContentClick: function () {
        if (MiniBasket.BasketItems.length == 0)
            return;

        $('div#shopCart').slideToggle();

    },

    GenerateMiniBasketItemHtml: function () {
        var html = "";
        for (var i = 0; i < MiniBasket.BasketItems.length; i++) {
            html += '<li id="ShopCartItem' + MiniBasket.BasketItems[i].Id + '" class="clearfix">';
            html += '<div class="cartItemImage">';
            html += '<img src="';
            html += MiniBasket.BasketItems[i].Image;
            html += '">';
            html += '</div>';
            html += '<div class="cartItemName">';
            html += '<p>';
            html += MiniBasket.BasketItems[i].Name;
            html += '</p>';
            html += '</div>';
            html += '<div class="cartItemQuantity">';
            html += '<p>';
            html += MiniBasket.BasketItems[i].Amount;
            html += '</p>';
            html += '</div>';
            html += '<div class="cartItemPrice">';
            html += '<p>';
            html += MiniBasket.BasketItems[i].Price;
            html += ' TL</p>';
            html += '</div>';
            html += '<div class="cartItemCancel">';
            html += '<a href="javascript:MiniBasket.DeleteItem(' + MiniBasket.BasketItems[i].Id + ');">Cancel</a>';
            html += '</div>';
            html += '</li>';


        }
        html += "";
        //alert(html);
        $("#cartItems ul").html(html);
        
        
    },

    ToggleBasket: function () {
        if ($('div#shopCart').css("display") == "none") {
            $('div#shopCart').slideToggle();
            setTimeout("$('div#shopCart').slideToggle()", 5000);
        }
        //
    },

    BasketItems: [{ Id: '', Name: '', Price: '', Amount: '', Image: ''}]
};

var MiniBasketWindow =
{
    show: function (data) {
        $("#fancybox-close").click();

        if (typeof (MiniBasket) != "undefined") {

            MiniBasket.UpdateMiniBasket(data.Basket.Amount, data.Basket.TotalPrice, data.Item, false);

            var html = "<div class='MiniBasketWindow'>" + $("#shopCart").html() + "</div>";

            var height = $("#shopCart").height();

            $.blockUI({ message: html, css: { border: '1px solid', padding: '10px', '-webkit-border-radius': '10px', '-moz-border-radius': '10px', color: '#000',
                width: 400,
                height: height+15,
                top: ($(window).height() - height - 100) / 2,
                cursor: 'default'
            }
            });
            $(".blockOverlay").css("cursor", "pointer");
            $(".blockOverlay").click(function () {
                $.unblockUI();
            });


            $(".MiniBasketWindow .complateShopping").css("margin-left", "180px");
            $(".MiniBasketWindow .complateShopping").css("margin-top", "10px");
            $(".MiniBasketWindow .cartItemCancel").hide();
            $(".MiniBasketWindow .SummaryButtons").html("<a href='javascript:$.unblockUI();' class='ContinueShopping'></a><a href='/Basket.aspx' class='CompleteShopping'></a>");
            $(".CompleteShopping").attr("style", 'background: url("/DesignImages/Main/mini_basket_window/continue.png") no-repeat scroll 0 0 transparent;display: block;height: 37px;margin-top: 10px;text-indent: -9999px;width: 190px;cursor: pointer;float:right;margin-right:25px;');
            $(".ContinueShopping").attr("style", 'background: url("/DesignImages/Main/mini_basket_window/return.png") no-repeat scroll 0 0 transparent;display: block;height: 37px;margin-top: 10px;text-indent: -9999px;width: 172px;cursor: pointer;float:left');
            $(".MiniBasketWindow .SummaryButtons").attr("style", "height:45px;");
            //parent.MiniBasket.ToggleBasket();
        }
    }
}

