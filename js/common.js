// Begin common.js -->
function RunQuery(tcForm, tcAction)
{
	document.forms[tcForm].action = tcAction;
	document.forms[tcForm].method = "POST";
	document.forms[tcForm].submit();
	return false;
}
// End common.js -->
