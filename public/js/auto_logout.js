var timer = 0;

function set_interval()
{
//the interval 'timer' is set as soon as the page loads
timer=setInterval("auto_logout()",600000);
// the figure '10000' above indicates how many milliseconds the timer be set to.
//Eg: to set it to 5 mins, calculate 5min= 5x60=300 sec = 300,000 millisec. So set it to 3000000
// We set the auto-logout to ten minutes... perhaps users should be able to set 
// this themselves...
}
 
function reset_interval()
{
//resets the timer. The timer is reset on each of the below events:
// 1. mousemove   2. mouseclick   3. key press 4. scroliing
//first step: clear the existing timer
//console.log("timer was at %d", timer);
clearInterval(timer);
//second step: implement the timer again
timer=setInterval("auto_logout()",600000);
//..completed the reset of the timer
}
 
function auto_logout()
{
//this function will redirect the user to the logout script
window.location="/index.php/account/logout/?auto_logout=true";
}

