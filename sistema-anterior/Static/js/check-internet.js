/* console.log(rxjs)
 */
/* import {
    Observable
} from 'rxjs';
*/

$('#no-connection-message').hide()
var internetObservable = new rxjs.Observable((subscriber) => {
    navigator.connection.onchange = function(e){
        subscriber.next(navigator.onLine)
    }
});

internetObservable.subscribe(res => {
    //console.log("Internet: ", res)
    if(!res){
        $('#no-connection-message').show();
    }else{
        $('#no-connection-message').hide();
    }
})

