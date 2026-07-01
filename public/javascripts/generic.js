

/*  selectbox'ların görünümünü iyileştirmek.*/

var selectBoxCollection;
var selectedItem;
var firstSelectBoxItems;
var selectBoxList;
var selectBoxScroller;
var zindexCollection;
var activeSelectBox = null;

function selectBoxCollection() {

    // Sayfa içinde bulunan tüm selectboxları alıyoruz.
    selectBoxCollection = document.getElementsByTagName("select");
    for (var i = 0; i < selectBoxCollection.length; i++) {
        // Alt alta açılacak selectboxlardaki diğerinin altında kalma problemini kaldırmak için kullanılacak z-index numalarını buluyoruz
        zindexCollection = selectBoxCollection.length;
        zindex = zindexCollection - i;

        // changeSelectBoxView(aktif select box, selecbox'in z-indexi)
        if ($(selectBoxCollection[i]).attr("class") != "multiple") {
            changeSelectBoxView(selectBoxCollection[i], zindex);
        }
    }
}

function changeSelectBoxView(obj, zindex) {

    var activeItem = null;
    var parent = obj.parentNode;

    // Sayfa içinde bulunan tüm selectboxları gizliyoruz.
    obj.className += " hidden";

    // Selectboxtaki ilk itemini al
    firstSelectBoxItems = obj.options[obj.options.selectedIndex].text;

    // Selecbox yerine geçecek olan yeni elementleri oluşturuyoruz.
    // Aşağıdaki işlemler tamamlandığında aşağıdakine benzer bir html formatı oluşacaktır.

    /* <div class="selectBoxContainer">
    <div class="selectArea">Lütfen bir seçim yapın <a class="downArrow"><\/a>
    <div class="selectBoxScroller">
    <ul>
    <li>Seçenekler 1<\/li>
    <li>Seçenekler 2<\/li>
    <\/ul>
    <//div>
    <\/div> */

    var selectArea = document.createElement("div");
    selectArea.className = "selectArea";
    selectArea.onclick = function () {
        showSelectBoxItems(this);
    }

    var selectAreaTextNode = document.createTextNode(firstSelectBoxItems);
    selectArea.appendChild(selectAreaTextNode);

    var selectAreaArrow = document.createElement("a");
    selectAreaArrow.className = "downArrow";
    selectArea.appendChild(selectAreaArrow);

    var ul = document.createElement("ul");
    ul.className = "selectBoxList";

    function changeActiveElement(el) {
        if (activeItem != null) {
            activeItem.className = "selectBoxItem";
            activeItem.selected = false;
        }

        activeItem = el;
        activeItem.className = "selectBoxItem selected";
        activeItem.selected = true;
    }

    // Listeyi oluşturuyoruz
    for (var i = 0; i < obj.length; i++) {

        var li = document.createElement("li");

        // Mouse li elementinin üzerindeyken 
        li.onmouseover = function () {
            if (this.selected != true) {
                this.className = "selectBoxItem hover";
            }
        }

        // Mouse li elementinin dışına çıktığında
        li.onmouseout = function () {
            if (this.selected != true) {
                this.className = "selectBoxItem";
            }
        }

        li.onclick = function () {
            firstSelectBoxItems = document.createTextNode(this.innerHTML);
            var oldChild = selectArea.firstChild;
            selectArea.replaceChild(firstSelectBoxItems, oldChild);
            obj.options[this.index].selected = true;
            $(obj).change();
            // this.className = "selectBoxItem selected";
            changeActiveElement(this);
            activeSelectBox.style.display = "none";
        };

        // Varsayılan olarak verilen bir option değeri varsa
        if (obj.options[i].selected == true) {
            li.selected = true;
            changeActiveElement(li);
        }

        // Varsayılan olarak seçilmiş bir option yoksa
        else {
            li.className = "selectBoxItem";
            li.selected = false;
        }

        var txt = document.createTextNode(obj.options[i].text);
        li.index = i;

        li.appendChild(txt);
        ul.appendChild(li);

    }

    selectBoxScroller = document.createElement("div");
    selectBoxScroller.style.height = "auto";
    selectBoxScroller.style.overflow = "auto";

    selectBoxScroller.className = "selectBoxScroller";
    selectBoxScroller.id = "selectBoxScroller" + zindex;
    selectBoxScroller.appendChild(ul);

    var selectBoxContainer = document.createElement("div");
    selectBoxContainer.className = "selectBoxContainer";
    selectBoxContainer.style.zIndex = zindex;
    selectBoxContainer.appendChild(selectArea);
    selectBoxContainer.appendChild(selectBoxScroller);

    jQuery.data(obj, 'attachedObjectPosition', selectArea);
    parent.insertBefore(selectBoxContainer, obj);

}

function showSelectBoxItems(arg) {

    if (activeSelectBox == null) {
        activeSelectBox = arg.nextSibling;
        activeSelectBox.style.display = "block";

        if (document.attachEvent) {
            document.attachEvent("onmousedown", closeSelectBox)
        }
        else {
            document.addEventListener("mousedown", closeSelectBox, true)
        }
    }

    else {

        if (activeSelectBox == arg.nextSibling) {
            activeSelectBox.style.display = "none";
            activeSelectBox = null;
            if (document.detachEvent) {
                document.detachEvent("onmousedown", closeSelectBox)
            }
            else {
                document.removeEventListener("mousedown", closeSelectBox, true)
            }
        }
        else {
            activeSelectBox.style.display = "none";
            activeSelectBox = null;
            activeSelectBox = arg.nextSibling;
            activeSelectBox.style.display = "block";
        }

    }

}

function closeSelectBox(e) {

    e = e || event;
    // Olayın gerçekleştiği hedef element alınıyor.
    var target = e.target 	// DOM Level-2 destekleyen tarayıcılar için (Firefox, Opera, Safari)
			|| e.srcElement; 	// MsDOM (Internet Explorer)

    if ((target.className == "selectArea") || (target.className == "selectBoxItem hover") || (target.className == "downArrow") || (target.className == "selectBoxScroller")) {
        return true;
    }
    else {
        activeSelectBox.style.display = "none";
        activeSelectBox = null;
        if (document.detachEvent) {
            document.detachEvent("onmousedown", closeSelectBox)
        }
        else {
            document.removeEventListener("mousedown", closeSelectBox, true)
        }
    }

}
window.onload = function () {
    (document.all && !window.print) ? null : selectBoxCollection();
}
	

