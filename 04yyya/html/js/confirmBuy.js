var ss=GetHrefParameter();
if(ss['error'])alert("错误!");
console.log(ss);

var currentVal =1;
function vals(em){

	
	currentVal = $(em).text().replace(/\D/g,'');
	//if(currentVal.length>0)console.log("长度"+currentVal.length());
	//currentVal = currentVal==''?1:parseInt(currentVal)
	console.log("获取的 "+currentVal);
	$(em).parent().children('.b').empty();
	$(em).parent().children('.b').append(currentVal);
	
/*var e = event.srcElement;
var r =e.createTextRange();
r.moveStart('character',$(em).parent().children('.b').value.length);
r.collapse(true);
r.select();*/

/*	var pointer = $(em).parent().children('b').createTextRange();
	pointer.moveEnd('character',$(em).parent().children('b').value.length);
	*/
	}	
	
	
function changeCopies(em,num){
	currentVal=parseInt(currentVal)
	if(currentVal==1&&num=='-1')num=0;
	currentVal+=num;
	$(em).parent().children('.b').empty();
	$(em).parent().children('.b').append(currentVal);
}