/**
 * 
 */
 function addPageQuiz(id,html){
	document.getElementById(id).innerHTML = id;
}
var opened=0;
var wins=[];
function RT_dump_screenshots(){
	//debugger;
	var style = "top=10, left=10, width=400, height=250, status=no, menubar=yes, toolbar=yes scrollbars=yes";
	//debugger;//alert("press F12");debugger;alert("press F12");
	var n=arguments.length-1;
	for (var i = 0; i < n; i++) {
		//debugger;alert("dump_screenshot");
		wins[opened]=window.open(arguments[i], "",style);
		opened++;
		
	};
	/*var xhttp = new XMLHttpRequest();
  	xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
    alert(this.responseText);
    debugger;
    var style = "top=10, left=10, width=400, height=250, status=no, menubar=yes, toolbar=yes scrollbars=yes";
//	var testo = window.open();
	var testo = window.open("", "",style);
	//testo.document.write("<html>\n");
	//testo.document.write(" <head>\n");
	//testo.document.write(" <title>Dump </title>\n");
	//testo.document.write(" </head>\n");
	//testo.document.write("<body>\n");
	testo.document.write(this.responseText);
	//testo.document.write("</body>\n");
	//testo.document.write("</html>\n");

    //eval(this.responseText);
    }
  };
  xhttp.open("GET", url, true);
  xhttp.send();*/
}	
function RT_popwins(){setTimeout(function() {window.close()}, 2000);}//alert("closing nah");}//debugger;*/window.close();opened--;}//wins[opened].close();opened--;