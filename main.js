// Show hidden input div with display style
function unfold_div(unfoldable) {
    var ele = document.getElementById(unfoldable);
    var display = ele.style.display; 
    ele.style.display = (display === 'block' ? 'none' : 'block');;
}

// Array holding the action buttons to create
var actionis = new Array(2);
    actionis[0] = new Array("bought", "remove", "rebuy", "purge");
    actionis[1] = new Array("buyd", "del", "redo", "purge");

// create a form with the Array as buttons and post on click to a GET
function createSelect(divId, actioniArray, name) {
    // first deactive clickability of element
    document.getElementById('src'+divId).onclick = "";

    var my_form = document.createElement('FORM');
    my_form.name='myForm';
    my_form.method='GET';
    my_form.action='';

	for (i = 0; i < actioniArray[0].length; i++) {
	    newButton = document.createElement('BUTTON');
		newButton.setAttribute("value", actioniArray[1][i]); 
		newButton.setAttribute("name", "update");
        newButton.setAttribute("id", "update");
        newButton.setAttribute("class", "subpop btn");
        if (actioniArray[1][i] == "purge" ) {
            newButton.setAttribute("class", "subpop purge btn");
        }
        newButton.innerHTML = actioniArray[0][i];
		my_form.appendChild(newButton); 
	}

    my_hidden=document.createElement('INPUT');
    my_hidden.type='HIDDEN';
    my_hidden.name='prod';
    my_hidden.id='prod';
    my_hidden.value=name;
    my_form.appendChild(my_hidden);
	
	document.getElementById(divId).replaceChild(my_form,document.getElementById('prost'+divId));
};

